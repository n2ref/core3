<?php
namespace Rowan\Classes;
use Rowan\Classes\Worker;
use Rowan\Exceptions\DbException;
use Rowan\Exceptions\Exception;
use Laminas\Cache\Exception\ExceptionInterface;

/**
 *
 */
class Cli extends Common {

    /**
     * Параметры командной строки
     * @return array
     */
    public function getOptions(): array {

        $options = getopt('m:e:p:l:nct:avhd', [
            'module:',
            'method:',
            'param:',
            'cli-methods',
            'worker-start',
            'worker-stop',
            'modules',
            'composer',
            'ipdb',
            'host',
            'version',
            'help',
        ]);

        return $options ?: [];
    }


    /**
     * Help инструкция
     * @return string
     */
    public function getHelp(): string {

        return implode(PHP_EOL, [
            'Core 3',
            'Usage: php index.php [OPTIONS]',
            '',
            'Optional arguments:',
            " -m    --module                 Module name",
            " -e    --method                 Cli method name",
            " -p    --param                  Parameter in command",
            " -t    --host                   Section name in config file",
            " -l    --cli-methods            Getting information about available system methods",
            " -n    --modules                Getting information about installed modules",
            "       --worker-start           Start worker",
            " -d                             Daemonize worker",
            "       --worker-stop            Stop worker",
            "       --ipdb                   Download latest DB-IP city-lite mmdb database",
            " -c    --composer               Control composer",
            " -h    --help                   Help info",
            " -v    --version                Version info",
            '',
            "Examples of usage:",
            "php index.php --module cron --method run",
            "php index.php --module cron --method run --host site.com",
            "php index.php --module cron --method runJob --param 123 --param abc",
            "php index.php --composer --param update",
            "php index.php --composer --param search --param monolog",
            "php index.php --ipdb",
            "php index.php --version",
            "php index.php --cli-methods",
        ]);
    }


    /**
     * Getting information about installed modules
     * @return string
     */
    public function getInstalledModules(): string {

        $modules = $this->db->fetchAll("
            SELECT m.name,
                   m.title,
                   m.version,
                   m.is_visible,
                   m.is_active
            FROM core_modules AS m
            ORDER BY m.seq
        ");

        $modules_result = [];
        if ( ! empty($modules)) {
            foreach ($modules as $module) {
                $modules_result[] = [
                    'Name'       => $module['name'],
                    'Title'      => $module['title'],
                    'Version'    => $module['version'],
                    'Is visible' => $module['is_visible_sw'],
                    'Is active'  => $module['is_active'],
                ];
            }
        }

        $table = new AsciiTable();
        return $table->makeTable($modules_result);
    }


    /**
     * @param array $params
     * @throws Exception
     */
    public function updateComposer(array $params): void {

        $temp_dir = sys_get_temp_dir();
        if ( ! is_writable($temp_dir)) {
            throw new Exception($this->_('Error. Folder %s not writable.', [$temp_dir]));
        }
        if ( ! is_writable(__DIR__ . '/..')) {
            throw new Exception($this->_('Error. Folder %s not writable.', [realpath(__DIR__ . '/..')]));
        }

        $composer_setup_file = $temp_dir . '/' . uniqid() . '-composer-setup.php';
        echo $this->_('Download composer installer...') . PHP_EOL;

        if ( ! copy('https://getcomposer.org/installer', $composer_setup_file)) {
            throw new Exception($this->_('Fail download composer installer.'));
        }

        $composer_signature = trim(file_get_contents('https://composer.github.io/installer.sig'));
        if ( ! $composer_signature) {
            unlink($composer_setup_file);
            throw new Exception($this->_('Fail download composer signature.'));
        }

        if (hash_file('SHA384', $composer_setup_file) !== $composer_signature) {
            unlink($composer_setup_file);
            throw new Exception($this->_('Error. Composer installer corrupt.'));
        }

        echo $this->_('Composer Installer verified.') . PHP_EOL;
        echo $this->_('Install composer...') . PHP_EOL;


        $old_cwd  = getcwd();
        $php_path = exec('which php') ?: 'php';
        chdir(__DIR__ . '/..');
        echo shell_exec(sprintf('%s %s', $php_path, $composer_setup_file));

        $cmd = sprintf('%s composer.phar %s', $php_path, implode(' ', $params));
        echo $this->_('Run command: %s', [$cmd]) . PHP_EOL . PHP_EOL;
        echo shell_exec($cmd);

        $composer_file = __DIR__ . '/../composer.phar';
        if ( ! unlink($composer_file)) {
            echo $this->_('Warning. Could not delete the file %s', [$composer_file]) . PHP_EOL;
        }
        if ( ! unlink($composer_setup_file)) {
            echo $this->_('Warning. Could not delete the file %s', [$composer_setup_file]) . PHP_EOL;
        }

        chdir($old_cwd);
    }


    /**
     * Загрузка актуальной базы IP-геолокации DB-IP City Lite.
     *
     * Ссылка на базу содержит месяц выпуска (YYYY-MM), поэтому месяц
     * вычисляется динамически. Если база за текущий месяц ещё не опубликована
     * (обновление выходит обычно 1-го числа), используется предыдущий месяц.
     *
     * @return string
     * @throws Exception
     */
    public function downloadIpDb(): string {

        $ipdb = new Ipdb();
        $path = $ipdb->resolveDestination();

        if ($path === '') {
            return $this->_('Не указан system.root — загрузка базы IP пропущена.');
        }

        clearstatcache(true, $path);

        // Если база уже скачана и файл корректного размера, повторно не загружаем
        if (is_file($path) && (int)filesize($path) >= Ipdb::MIN_DATABASE_SIZE) {
            return $this->_('База IP-геолокации уже скачана: %s (%s)', [
                $path,
                $ipdb->formatBytes((int)filesize($path)),
            ]);
        }

        return $ipdb->download($path);
    }


    /**
     * Getting information about available system methods
     * @return string
     * @throws \ReflectionException|DbException
     */
    public function getCliMethods(): string {

        $cli_modules = [];
        $modules     = $this->db->fetchAll("
            SELECT m.name,
                   m.title
            FROM core_modules AS m
            WHERE m.is_active = 1
            ORDER BY m.seq
        ");

        if ( ! empty($modules)) {
            foreach ($modules as $module) {
                $location        = $this->getModuleLocation($module['name']);
                $controller_name = '\\Rowan\\Mod\\' . ucfirst($module['name']) . '\\Cli';
                $controller_path = "{$location}/Cli.php";

                if ( ! file_exists($controller_path)) {
                    continue;
                }
                require_once $controller_path;

                if ( ! class_exists($controller_name)) {
                    continue;
                }

                $mod_methods = get_class_methods($controller_name);

                if ( ! empty($mod_methods)) {
                    foreach ($mod_methods as $mod_method) {
                        if (strpos($mod_method, '__') !== 0) {
                            $reflection        = new \ReflectionMethod($controller_name, $mod_method);
                            $reflection_params = $reflection->getParameters();
                            $params = [];
                            if ( ! empty($reflection_params)) {
                                foreach ($reflection_params as $reflection_param) {
                                    $params[] = '$'.$reflection_param->getName();
                                }
                            }
                            $cli_modules[$module['name']][$mod_method] = [
                                'doc'    => $reflection->getDocComment(),
                                'params' => $params
                            ];
                        }
                    }
                }
            }
        }

        $result = "Module | Method | Params | Description" . PHP_EOL . PHP_EOL;
        if ( ! empty($cli_modules)) {
            foreach ($cli_modules as $module_name => $cli_methods) {
                foreach ($cli_methods as $method_name => $method_options) {
                    $params      = implode(', ', $method_options['params']);
                    $description = str_replace(["/**", "*/", "*", "\r\n", "\n"], ' ', $method_options['doc']);
                    $description = preg_replace('~\s{2,}~', ' ', trim($description));
                    $result .= "{$module_name}\t {$method_name}\t {$params}\t {$description}" . PHP_EOL;
                }
            }
        }

        return $result;
    }


    /**
     * Запуск менеджера задач
     * @param bool $is_daemonize
     * @return void
     * @throws Exception|ExceptionInterface
     */
    public function startWorkerManager(bool $is_daemonize = false): void {

        $manager = new Worker\Manager();
        if ($manager->init()) {
            if ($is_daemonize) {
                $manager->daemonize();
            }

            $manager->start();
        }
    }


    /**
     * Остановка менеджера задач
     * @param bool $force
     * @return bool
     * @throws Exception
     */
    public function stopWorkerManager(bool $force = false): bool {

        $manager = new Worker\Manager();
        return $manager->stop($force);
    }


    /**
     * Перезапуск менеджера задач
     * @param bool $force
     * @return bool
     * @throws Exception|ExceptionInterface
     */
    public function restartWorkerManager(bool $force = false): bool {

        $manager = new Worker\Manager();

        if ($manager->stop($force)) {
            if ($manager->init()) {
                $manager->daemonize();
                $manager->start();

                return true;
            }
        }

        return false;
    }


    /**
     * Module run method
     * @param string $module
     * @param string $method
     * @param array  $params
     * @return string
     * @throws DbException
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function startCliMethod(string $module, string $method, array $params = []): string {

        $module = strtolower($module);
        $method = strtolower($method);


        if ( ! $this->isModuleInstalled($module)) {
            throw new Exception(sprintf($this->_("Модуль %s не найден"), $module));
        }

        if ( ! $this->isModuleActive($module)) {
            throw new Exception(sprintf($this->_("Модуль %s не активен"), $module));
        }

        $location        = $this->getModuleLocation($module);
        $controller_name = '\\Rowan\\Mod\\' . ucfirst($module) . '\\Cli';
        $controller_path = "{$location}/Cli.php";

        if ( ! file_exists($controller_path)) {
            throw new Exception(sprintf($this->_("Файл %s не найден"), $controller_path));
        }

        $autoload_file = "{$location}/vendor/autoload.php";

        if (file_exists($autoload_file)) {
            require_once $autoload_file;
        }

        require_once $controller_path;

        if ( ! class_exists($controller_name)) {
            throw new Exception(sprintf($this->_("Класс %s не найден"), $controller_name));
        }

        $mod_methods = get_class_methods($controller_name);
        $cli_method  = ucfirst($method);
        if ( ! array_search($cli_method, $mod_methods)) {
            throw new Exception(sprintf($this->_("В классе %s не найден метод %s"), $controller_name, $cli_method));
        }

        $controller = new $controller_name();
        $result     = call_user_func_array([$controller, $cli_method], $params);

        if (is_scalar($result)) {
            return $result . PHP_EOL;
        }

        return '';
    }
}