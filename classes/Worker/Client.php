<?php
namespace Core3\Classes\Worker;
use Core3\Classes\System;
use Core3\Exceptions\Exception;


/**
 *
 */
class Client extends System {

    /**
     * @var string
     */
    private string $php_path;

    /**
     * @var string
     */
    private string $lock_file;

    /**
     * @var string
     */
    private string $file_index;

    /**
     * @var int|null
     */
    private int|null $timeout_connect;


    /**
     * @throws Exception
     */
    public function __construct() {

        $this->php_path = is_string($this->config?->system?->php_path)
            ? $this->config->system->php_path
            : 'php';

        $lock_file = $this->config?->system?->worker?->lock_file ?: 'core3_worker.lock';

        if (str_starts_with($lock_file, '/')) {
            $this->lock_file = $lock_file;
        } else {
            if ( ! $this->config?->system?->tmp) {
                throw new Exception('Error: empty config system.tmp');
            }

            $this->lock_file = "{$this->config->system->tmp}/{$lock_file}";
        }

        $this->timeout_connect = $this->config?->system?->worker?->timeout_connect
            ? (int)$this->config->system->worker->timeout_connect
            : 10;


        $this->file_index = realpath(__DIR__ . '/../../index.php');
    }


    /**
     * Проверка запущен ли менеджер процессов
     * @return bool
     * @throws \Exception
     */
    public function isStart(): bool {

        $worker_info = $this->getWorkerInfo();

        $pid = $worker_info['pid'] ?? null;

        if ( ! $pid || ! posix_getpgid($pid)) {
            return false;
        }

        return $this->ping();
    }


    /**
     * Запуск менеджера процессов
     * @return bool
     * @throws \Exception
     */
    public function start(): bool {

        if ($this->isStart()) {
            return true;
        }

        $cmd = sprintf('%s %s --worker-start', $this->php_path, $this->file_index);

        if ($this->config->system?->host) {
            $cmd .= sprintf(" -t %s", $this->config->system->host);
        }

        $cmd .= " > /dev/null 2>&1 & echo $!";

        exec($cmd, $out);

        if (isset($out[0]) && is_numeric($out[0])) {
            for ($i = 0; $i < 30; $i++) {
                usleep(200000);
                if ($this->isStart()) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * Остановка менеджера процессов
     * @param bool $force
     * @return bool
     * @throws \Exception
     */
    public function stop(bool $force = false): bool {

        if ( ! $this->isStart()) {
            return true;
        }

        $cmd = sprintf('%s %s --worker-stop', $this->php_path, $this->file_index);

        if ($force) {
            $cmd .= " -p 1";
        }

        if ($this->config->system?->host) {
            $cmd .= sprintf(" -t %s", $this->config->system->host);
        }

        exec($cmd);

        return ! $this->isStart();
    }


    /**
     * Перезапуск менеджера процессов
     * @return bool
     * @throws \Exception
     */
    public function restart(): bool {

        if ( ! $this->stop()) {
            throw new \Exception('Не удалось остановить текущий процесс воркера');
        }

        return $this->start();
    }


    /**
     * Запуск выполнения задачи
     * @param string     $module
     * @param string     $job_name
     * @param array|null $arguments
     * @return int|null
     * @throws \Exception
     */
    public function startJob(string $module, string $job_name, array $arguments = null):? int {

        $response_content = $this->sendCommand('job_start', [
            'module'    => $module,
            'job_name'  => $job_name,
            'arguments' => $arguments,
        ]);

        $job_id = null;

        if ( ! empty($response_content)) {
            $response = @json_decode($response_content, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $job_id = ! empty($response['job_id']) ? (int)$response['job_id'] : null;
            }
        }

        return $job_id;
    }


    /**
     * Запуск выполнения задачи
     * @param string $job_id
     * @return string|null
     * @throws \Exception
     */
    public function stopJob(string $job_id):? string {

        $response_content = $this->sendCommand('job_stop', [
            'job_id' => $job_id,
        ]);

        $job_id = null;

        if ( ! empty($response_content)) {
            $response = json_decode($response_content, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $job_id = $response['job_id'] ?? null;
            }
        }

        return $job_id;
    }


    /**
     * Получение информации о задаче воркера
     * @param int $job_id
     * @return array|null
     * @throws \Exception
     */
    public function getJobInfo(int $job_id):? array {

        $response_content = $this->sendCommand('job_info', [
            'job_id' => $job_id,
        ]);

        $result = null;

        if ( ! empty($response_content)) {
            $response = json_decode($response_content, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $result = is_array($response) ? $response : [];
            }
        }

        return $result;
    }


    /**
     * Получение информации о воркере
     * @return array|null
     * @throws \Exception
     */
    public function getInfo():? array {

        $response_content = $this->sendCommand('info');

        $result = null;

        if ( ! empty($response_content)) {
            $response = json_decode($response_content, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $result = is_array($response) ? $response : [ $response ];
            }
        }

        return $result;
    }


    /**
     * Получение состояния менеджера процессов
     * @return array|null
     * @throws \Exception
     */
    private function status():? array {

        $response = $this->sendCommand("status");

        $response_decode = @json_decode($response, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $response_decode = null;
        }

        return $response_decode;
    }


    /**
     * Проверка активности с помощью отправки сообщения
     * @return bool
     * @throws \Exception
     */
    private function ping(): bool {

        $response = $this->sendCommand("ping");

        return $response === 'pong';
    }


    /**
     * Информация о запущенном воркере
     * @return array|null
     */
    private function getWorkerInfo():? array {

        if (file_exists($this->lock_file) && is_readable($this->lock_file)) {
            $worker_info = @json_decode(file_get_contents($this->lock_file), true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return is_array($worker_info)
                    ? $worker_info
                    : null;
            }
        }

        return null;
    }


    /**
     * @param string     $command
     * @param array|null $params
     * @return string|null
     * @throws \Exception
     */
    private function sendCommand(string $command, array $params = null):? string {

        $worker_info = $this->getWorkerInfo();
        $address     = $worker_info['address'] ?? null;

        if ( ! $address) {
            return null;
        }

        $socket = stream_socket_client($address, $errno, $errstr, $this->timeout_connect);

        if ( ! $socket) {
            $this->log->error("error created socket stream_socket_client: $errstr ($errno)");
            return null;

        } else {
            fwrite($socket, json_encode([
                'command' => $command,
                'params'  => $params,
            ]));

            $response_content = fgets($socket, 8 * 1024);
            fclose($socket);

            return $response_content;
        }
    }
}