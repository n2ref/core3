<?php
namespace Core3\Classes\Worker;
use Core3\Classes\Db;
use Core3\Exceptions\DbException;
use Core3\Exceptions\Exception;
use Laminas\Cache\Exception\ExceptionInterface;


/**
 *
 */
class Manager extends Db {

    /**
     * @var resource
     */
    private $socket;

    /**
     * @var string
     */
    private string $address;

    /**
     * @var string
     */
    private string $lock_file;

    /**
     * @var string|null
     */
    private string|null $log_file = null;

    /**
     * @var string
     */
    private string $log_level = 'warning';

    /**
     * @var int
     */
    private int $pool_size;

    /**
     * @var array
     */
    private static array $worker_info = [];

    /**
     * @var int
     */
    private int $job_id = 1;

    /**
     * @var array
     */
    private array $jobs_pending = [];

    /**
     * @var array
     */
    private array $jobs_running = [];

    /**
     * @var int
     */
    private int $jobs_completed = 0;

    /**
     * @var bool
     */
    private bool $is_process_job = false;


    /**
     * @throws Exception
     */
    public function __construct() {

        $lock_file = $this->config?->system?->worker?->lock_file ?: 'core3_worker.php';

        if (str_starts_with($lock_file, '/')) {
            $this->lock_file = $lock_file;
        } else {
            if ( ! $this->config?->system?->tmp) {
                throw new Exception('Error: empty config system.tmp');
            }

            $this->lock_file = "{$this->config->system->tmp}/{$lock_file}";
        }
    }


    /**
     * @return bool
     * @throws Exception
     */
    public function init(): bool {

        if (\PHP_SAPI != 'cli') {
            throw new Exception('Only run in command line mode');
        }

        if ( ! function_exists('pcntl_fork')) {
            throw new Exception('Function pcntl_fork not found');
        }

        if ( ! $this->config?->system?->worker?->address) {
            echo $this->getRedColor('Error') . ": empty config system.worker.address";
            return false;
        }

        $this->address   = $this->config->system->worker->address;
        $this->pool_size = $this->config?->system?->worker?->pool_size && is_numeric($this->config->system->worker->pool_size)
            ? (int)$this->config->system->worker->pool_size
            : 4;

        if ($this->config?->system?->log?->on) {
            $log_file = $this->config?->system?->worker?->log_file ?: $this->config?->system?->log?->file;

            $this->log_file = str_starts_with($log_file, '/')
                ? $log_file
                : "{$this->config->system->log}/{$log_file}";


            $this->log_level = $this->config?->system?->worker?->log_level ?: 'warning';
        }

        if (file_exists($this->lock_file)) {
            $content = file_get_contents($this->lock_file);
            $content = @json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($content['pid'])) {
                if ( ! is_writable($this->lock_file)) {
                    echo $this->getRedColor('Error') . ": lock file not writable {$this->lock_file}n";
                    return false;
                }

                unlink($this->lock_file);

            } elseif (posix_getpgid($content['pid'])) {
                echo $this->getRedColor('Error') . ": other worker process started";
                return false;
            }

        } elseif ( ! is_dir(dirname($this->lock_file)) || ! is_writable(dirname($this->lock_file))) {
            echo $this->getRedColor('Error') . ": lock file not writable {$this->lock_file}";
            return false;
        }

        return true;
    }


    /**
     * Запуск обработки запросов
     * @return void
     * @throws \Exception|ExceptionInterface
     */
    public function start(): void {

        cli_set_process_title("Core3 worker {$this->lock_file}");

        try {
            ob_start(null, 10000);

            $socket_server = $this->startListening();
            $sockets_pair  = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

            if ( ! $sockets_pair) {
                $this->logError("Fail start stream_socket_pair");
                return;
            }

            register_shutdown_function(function () {
                $buffer = ob_get_clean();
                $error  = error_get_last();

                if ($error &&
                    in_array($error['type'], [
                        E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR,
                        E_CORE_WARNING, E_COMPILE_WARNING, E_PARSE
                    ])
                ) {
                    $this->logError("Emergency stopped worker process: " . trim($buffer));

                } else {
                    if (trim($buffer)) {
                        $this->logWarning("Buffer data: " . trim($buffer));
                    }
                    if ( ! $this->is_process_job) {
                        $this->logWarning("Stopped worker process");
                    }
                }

                if ( ! $this->is_process_job) {
                    $this->stopListening();
                }
            });


            while (true) {
                pcntl_signal_dispatch();

                $read   = [$socket_server, $sockets_pair[0]];
                $write  = null;
                $except = null;

                // ожидаем сокеты доступные для чтения (без таймаута)
                if (stream_select($read, $write, $except, null) === false) {
                    break;
                }

                // обрабатываем соединения
                foreach ($read as $stream) {

                    if ($stream === $socket_server) {
                        $connect = stream_socket_accept($socket_server, -1, $peer_name);

                        if ( ! is_resource($connect)) {
                            continue;
                        }

                        $is_running = $this->processClient($connect, $sockets_pair);

                        if (is_resource($connect)) {
                            fclose($connect);
                        }

                        if ( ! $is_running) {
                            break 2;
                        }

                    } elseif ($stream === $sockets_pair[0]) {
                        $this->processJob($sockets_pair);
                    }
                }
            }

        } catch (\Exception $e) {
            $this->logError('Exception worker', $e);
            throw $e;
        }
    }


    /**
     * Остановка менеджера задач
     * @param bool $force
     * @return bool
     * @throws Exception
     */
    public function stop(bool $force = false): bool {

        $worker_info = $this->getWorkerInfo();
        $pid         = $worker_info['pid'] ?? null;
        $address     = $worker_info['address'] ?? null;

        $timeout_connect = $this->config?->system?->worker?->timeout_connect
            ? (int)$this->config->system->worker->timeout_connect
            : null;

        if ( ! $pid || ! posix_getpgid($pid)) {
            return true;
        }

        if ( ! $address) {
            return false;
        }

        $fp = stream_socket_client($address, $errno, $errstr, $timeout_connect);

        if ( ! $fp) {
            if ($force) {
                posix_kill($pid, SIGTERM);
            } else {
                throw new Exception("Error connect to worker process: $errstr ($errno)");
            }

        } else {
            fwrite($fp, json_encode([
                'command' => 'stop'
            ]));
            fclose($fp);

            if ($force && posix_getpgid($pid)) {
                for ($i = 0; $i < 10; $i++) {
                    if ( ! posix_getpgid($pid)) {
                        break;
                    } else {
                        usleep(500000);
                    }
                }

                if (posix_getpgid($pid)) {
                    posix_kill($pid, SIGTERM);
                }
            }
        }

        return true;
    }


    /**
     * Run as daemon mode.
     * @throws Exception
     */
    public function daemonize(): void {

        umask(0);
        $pid = pcntl_fork();


        if (-1 === $pid) {
            throw new Exception('Fork fail');

        } elseif ($pid > 0) {
            exit();
        }

        if (-1 === posix_setsid()) {
            throw new Exception("Setsid fail");
        }
    }


    /**
     * @return resource
     * @throws Exception
     * @throws \Exception
     */
    private function startListening() {

        $this->logWarning("Worker started: {$this->address}");

        $socket = stream_socket_server($this->address, $errno, $errstr);

        if ( ! $socket) {
            throw new Exception("Error start listening address: $errstr ($errno)");
        }

        if (file_exists($this->lock_file)) {
            if ( ! is_writable(dirname($this->lock_file))) {
                throw new Exception("Error: lock file not writable {$this->lock_file}");
            }

        } elseif ( ! is_dir(dirname($this->lock_file)) || ! is_writable(dirname($this->lock_file))) {
            throw new Exception("Error: lock file not writable {$this->lock_file}");
        }


        file_put_contents($this->lock_file, json_encode([
            'pid'        => getmypid(),
            'address'    => $this->address,
            'pool_size'  => $this->pool_size,
            'date_start' => date('Y-m-d H:i:s'),
        ]));

        $this->socket = $socket;

        return $socket;
    }


    /**
     * @return void
     * @throws \Exception
     */
    private function stopListening(): void {

        $this->logWarning("Worker stopped: {$this->address}");

        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }

        if (file_exists($this->lock_file)) {
            unlink($this->lock_file);
        }
    }


    /**
     * Обработка подключения
     * @param resource $stream_connect
     * @param array    $stream_pairs
     * @return bool
     * @throws Exception
     * @throws DbException
     * @throws \Exception
     * @throws ExceptionInterface
     */
    private function processClient($stream_connect, array $stream_pairs): bool {

        $content_raw = trim((string)fread($stream_connect, 1024 * 8));
        $content     = @json_decode($content_raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $content = [];
        }

        $command = ! empty($content['command']) && is_string($content['command'])
            ? $content['command']
            : '_undefined_';

        $params = ! empty($content['params']) && is_array($content['params'])
            ? $content['params']
            : [];

        $this->log("Worker command: {$command}", $params);

        switch ($command) {
            case 'ping':
                fwrite($stream_connect, "pong");
                break;

            case 'info':
                $worker_info = $this->getWorkerInfo();

                fwrite($stream_connect, json_encode([
                    'date_start' => $worker_info['date_start'] ?? null,
                    'stats'      => [
                        'pending'   => count($this->jobs_pending),
                        'running'   => count($this->jobs_running),
                        'completed' => $this->jobs_completed,
                    ],
                    "jobs_running" => $this->jobs_running,
                    "jobs_pending" => $this->jobs_pending
                ]));
                break;

            case 'stop':
                return false;
                break;

            case 'job_start':
                $job_id = $this->getNextJobId();

                fwrite($stream_connect, json_encode([ 'job_id' => $job_id ]));
                fclose($stream_connect);

                if (count($this->jobs_running) < $this->pool_size) {
                    $this->startJob($job_id, $stream_pairs, $params);
                } else {
                    $this->jobs_pending[$job_id] = $params;
                }
                break;

            case 'job_info':
                $this->getJobInfo($stream_connect, $params);
                break;

            case 'job_stop':
                $this->stopJob($params);
                break;

            default:
                fwrite($stream_connect, "Error: command not found");
                break;
        }

        return true;
    }


    /**
     * Обработка сообщения из работы
     * @param array $stream_pairs
     * @return void
     * @throws DbException
     * @throws Exception
     * @throws ExceptionInterface
     */
    private function processJob(array $stream_pairs): void {

        $content_raw = trim((string)fread($stream_pairs[0], 1024 * 8));

        if ($content_raw) {
            $content = @json_decode($content_raw, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $content = [];
            }

            $command = ! empty($content['command']) && is_string($content['command'])
                ? $content['command']
                : '_undefined_';


            switch ($command) {
                case 'stop':
                    $job_id = $content['job_id'] ?? '';

                    if ( ! empty($this->jobs_running[$job_id])) {
                        $job_pid = $this->jobs_running[$job_id]['pid'];

                        pcntl_waitpid($job_pid, $status);

                        $this->jobs_completed++;
                        unset($this->jobs_running[$job_id]);
                    }

                    if ( ! empty($this->jobs_pending) &&
                         count($this->jobs_running) < $this->pool_size
                    ) {
                        $job_id      = array_key_first($this->jobs_pending);
                        $job_pending = $this->jobs_pending[$job_id];

                        unset($this->jobs_pending[$job_id]);

                        $this->startJob($job_id, $stream_pairs, $job_pending);
                    }
                    break;

                case 'set_state':
                    $job_pid = $content['job_pid'] ?? '';
                    $job_id  = null;

                    foreach ($this->jobs_running as $id => $job_running) {
                        if ($job_running['pid'] == $job_pid) {
                            $job_id = $id;
                            break;
                        }
                    }

                    if ( ! empty($this->jobs_running[$job_id])) {
                        $this->jobs_running[$job_id]['state'] = $content['state'] ?? '';
                    }
                    break;
            }
        }
    }


    /**
     * Получение следующего индекса задачи
     * @return int
     */
    private function getNextJobId(): int {

        $job_id = $this->job_id;

        if ($this->job_id >= 1000000000) {
            $this->job_id = 1;
        } else {
            $this->job_id++;
        }

        return $job_id;
    }


    /**
     * Запуск задачи
     * @param int   $job_id
     * @param array $socket_pairs
     * @param array $params
     * @return void
     * @throws DbException
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Exception
     */
    private function startJob(int $job_id, array $socket_pairs, array $params): void {

        if (empty($params['module']) ||
            empty($params['job_name']) ||
            ! is_string($params['module']) ||
            ! is_string($params['job_name'])
        ) {
            $this->logError("Incorrect worker job params: " . json_encode($params));
            return;
        }

        $pid = pcntl_fork();

        if ($pid < 0) {
            $this->logError("Fail start pcntl_fork");
            return;

        } elseif ($pid) {
            $this->jobs_running[$job_id] = [
                'pid'        => $pid,
                'module'     => $params['module'],
                'job_name'   => $params['job_name'],
                'time_start' => date('Y-m-d H:i:s'),
                'state'      => '',
            ];

        } else {
            cli_set_process_title("Core3 worker job {$params['module']}->{$params['job_name']}");
            fclose($socket_pairs[0]);
            $this->is_process_job = true;

            ob_start(null, 10000);
            //$job_pid = posix_getpid();

            // Tell the parent that we are done
            register_shutdown_function(function () use ($socket_pairs, $job_id, $params) {
                $buffer = ob_get_clean();
                $error  = error_get_last();

                if (trim($buffer)) {
                    $this->logWarning("Buffer data: " . trim($buffer));
                }

                if ($error &&
                    in_array($error['type'], [
                        E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR,
                        E_CORE_WARNING, E_COMPILE_WARNING, E_PARSE
                    ])
                ) {
                    $this->logError("Emergency stopped worker job {$params['module']} -> {$params['job_name']}: " . json_encode($params['arguments'] ?? []));
                }

                fwrite($socket_pairs[1], json_encode([
                    'command' => 'stop',
                    'job_id'  => $job_id,
                ]) . "\n");
            });

            $module_worker = $this->getModuleWorker($params['module'], $socket_pairs[1]);

            if ( ! is_callable([$module_worker, $params['job_name']])) {
                $this->logError("Error. Not found worker job {$params['module']} -> {$params['job_name']}");
            }

            try {
                call_user_func_array(
                    [$module_worker, $params['job_name']],
                    $params['arguments'] ?? []
                );
            } catch (\Exception $e) {
                $this->logError("Error in worker job process {$params['module']} -> {$params['job_name']}", $e);
            }

            exit(0);
        }
    }


    /**
     * Остановка задачи
     * @param array $params
     * @return void
     * @throws \Exception
     */
    private function stopJob(array $params): void {

        if (empty($params['job_id']) || ! is_numeric($params['job_id'])) {
            $this->logError("Incorrect job_id param, command - job_stop");
            return;
        }


        if (isset($this->jobs_running[$params['job_id']])) {
            $job = $this->jobs_running[$params['job_id']] ?? [];

            if (posix_getpgid($job['pid'])) {
                posix_kill($job['pid'], SIGTERM);
            }

            $this->log("Stopped worker job {$job['module']} -> {$job['job_name']}");

            unset($this->jobs_running[$params['job_id']]);

        } elseif (isset($this->jobs_pending[$params['job_id']])) {
            $job = $this->jobs_pending[$params['job_id']] ?? [];

            $this->log("Deleting a worker job from the queue {$job['module']} -> {$job['job_name']}");

            unset($this->jobs_pending[$params['job_id']]);
        }
    }


    /**
     * Информация о задаче
     * @param resource $stream_connect
     * @param array    $params
     * @return void
     * @throws \Exception
     */
    private function getJobInfo($stream_connect, array $params): void {

        if (empty($params['job_id']) || ! is_numeric($params['job_id'])) {
            $this->logError("Incorrect job_id param, command - job_info");
            return;
        }

        $info = [
            'status'     => 'none',
            'pid'        => 0,
            'module'     => '',
            'job_name'   => '',
            'time_start' => '',
            'state'      => '',
        ];


        if ( ! empty($this->jobs_running[$params['job_id']])) {
            $job = $this->jobs_running[$params['job_id']];

            $info['status']     = 'running';
            $info['pid']        = $job['pid'];
            $info['module']     = $job['module'];
            $info['job_name']   = $job['job_name'];
            $info['time_start'] = $job['time_start'];
            $info['state']      = $job['state'];

        } elseif ( ! empty($this->jobs_pending[$params['job_id']])) {
            $job = $this->jobs_pending[$params['job_id']]['params'] ?? [];

            $info['status']   = 'pending';
            $info['module']   = $job['module'] ?? '';
            $info['job_name'] = $job['job_name'] ?? '';
        }

        fwrite($stream_connect, json_encode($info));
    }


    /**
     * Получение информацией о текущем воркере
     * @return array
     */
    private function getWorkerInfo(): array {

        if (empty(self::$worker_info)) {
            $content = file_get_contents($this->lock_file);
            $content = @json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $content = null;
            }

            self::$worker_info = is_array($content) ? $content : [];
        }


        return self::$worker_info;
    }


    /**
     * @param string   $module_name
     * @param resource $socket
     * @return mixed
     * @throws DbException
     * @throws Exception
     * @throws ExceptionInterface
     */
    private function getModuleWorker(string $module_name, $socket): mixed {

        $module_name = strtolower($module_name);
        $location    = $this->getModuleLocation($module_name);

        if ( ! $location) {
            throw new Exception($this->_("Модуль \"%s\" не найден", [$module_name]));
        }

        if ( ! $this->isModuleActive($module_name)) {
            throw new Exception($this->_("Модуль \"%s\" не активен", [$module_name]));
        }

        $worker_class_name = "Core3\\Mod\\" . ucfirst($module_name) . "\\Worker";
        $worker_file       = "{$location}/Worker.php";

        if ( ! file_exists($worker_file)) {
            throw new Exception($this->_("Модуль \"%s\" сломан. Не найден файл воркера.", [$module_name]));
        }

        $autoload_file = "{$location}/vendor/autoload.php";

        if (file_exists($autoload_file)) {
            require_once $autoload_file;
        }

        require_once $worker_file;

        if ( ! class_exists($worker_class_name)) {
            throw new Exception($this->_('Не найден класс воркера %s', [$worker_class_name]));
        }

        $class_parents = class_parents($worker_class_name);

        if ($class_parents &&
            in_array("Core3\\Classes\\Worker", $class_parents) &&
            is_resource($socket)
        ) {
            return new $worker_class_name($socket);
        } else {
            return new $worker_class_name();
        }
    }


    /**
     * Логирование выполнения
     * @param string $message
     * @param array  $context
     * @return void
     * @throws \Exception
     */
    private function log(string $message, array $context = []): void {

        if ($this->log_level == 'info') {
            $this->log->file($this->log_file)->info($message, $context);
        }
    }


    /**
     * Логирование выполнения
     * @param string $message
     * @param array  $context
     * @return void
     * @throws \Exception
     */
    private function logWarning(string $message, array $context = []): void {

        if (in_array($this->log_level, ['info', 'warning'])) {
            $this->log->file($this->log_file)->warning($message, $context);
        }
    }


    /**
     * Логирование ошибок
     * @param string          $message
     * @param \Exception|null $e
     * @return void
     * @throws \Exception
     */
    private function logError(string $message, \Exception $e = null): void {

        if (in_array($this->log_level, ['info', 'warning', 'error'])) {
            $context = [];

            if ($e instanceof \Exception) {
                $context = [
                    'error_message' => $e->getMessage(),
                    'file'          => $e->getFile(),
                    'file_line'     => $e->getLine(),
                    'trace'         => $e->getTraceAsString(),
                ];
            }

            $this->log->file($this->log_file)->error($message, $context);
        }
    }


    /**
     * Строка с красным текстом
     * @param string $message
     * @return string
     */
    private function getRedColor(string $message): string {

        return "\e[93m{$message}\e[0m";
    }
}