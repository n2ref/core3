<?php
namespace Core3\Classes;


/**
 *
 */
class Cli extends Db {

    /**
     * Опции указанные в командной строке
     * @return array
     */
    public function getOptions(): array {

        $options = getopt('m:e:p:t:ndcvh', [
            'module:',
            'method:',
            'param:',
            'host:',
            'scan-cli-methods',
            'info-installed-modules',
            'composer',
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
            " -n    --scan-cli-methods       Getting information about available system methods",
            " -d    --info-installed-modules Getting information about installed modules",
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
            "php index.php --version",
            "php index.php --scan-cli-methods",
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
                   m.is_visible_sw,
                   m.is_system_sw,
                   m.is_active_sw
            FROM core_modules AS m
            ORDER BY m.seq
        ");

        $result = "Name | Title | Version | Is visible? | Is system? | Is active?" . PHP_EOL . PHP_EOL;
        if ( ! empty($modules)) {
            foreach ($modules as $module) {
                $result .= implode("\t ",$module) . PHP_EOL;
            }
        }

        return $result;
    }


    /**
     * @param array $params
     * @throws \Exception
     */
    public function cliComposer(array $params) {

        $temp_dir = sys_get_temp_dir();
        if ( ! is_writable($temp_dir)) {
            throw new \Exception(sprintf("Error. Folder %s not writable.", $temp_dir));
        }
        if ( ! is_writable(__DIR__ . '/..')) {
            throw new \Exception(sprintf("Error. Folder %s not writable.", realpath(__DIR__ . '/..')));
        }

        $composer_setup_file = $temp_dir . '/' . uniqid() . '-composer-setup.php';
        echo 'Download composer installer...' . PHP_EOL;

        if ( ! copy('https://getcomposer.org/installer', $composer_setup_file)) {
            throw new \Exception('Fail download composer installer.');
        }

        $composer_signature = trim(file_get_contents('https://composer.github.io/installer.sig'));
        if ( ! $composer_signature) {
            unlink($composer_setup_file);
            throw new \Exception('Fail download composer signature.');
        }

        if (hash_file('SHA384', $composer_setup_file) !== $composer_signature) {
            unlink($composer_setup_file);
            throw new \Exception('Error. Composer installer corrupt.');
        }

        echo 'Composer Installer verified.' . PHP_EOL;
        echo 'Install composer...' . PHP_EOL;


        $old_cwd  = getcwd();
        $php_path = exec('which php') ?: 'php';
        chdir(__DIR__ . '/..');
        echo shell_exec(sprintf('%s %s', $php_path, $composer_setup_file));

        $cmd = sprintf('%s composer.phar %s', $php_path, implode(' ', $params));
        echo 'Run command: ' . $cmd . PHP_EOL . PHP_EOL;
        echo shell_exec($cmd);

        $composer_file = __DIR__ . '/../composer.phar';
        if ( ! unlink($composer_file)) {
            echo sprintf('Warning. Could not delete the file %s', $composer_file) . PHP_EOL;
        }
        if ( ! unlink($composer_setup_file)) {
            echo sprintf('Warning. Could not delete the file %s', $composer_setup_file) . PHP_EOL;
        }

        chdir($old_cwd);
    }


    /**
     * Getting information about available system methods
     * @return string
     * @throws \ReflectionException
     */
    public function getCliMethods(): string {

        $cli_modules = [];
        $modules     = $this->db->fetchAll("
            SELECT m.name,
                   m.title
            FROM core_modules AS m
            WHERE m.is_active_sw = 'Y'
            ORDER BY m.seq
        ");

        if ( ! empty($modules)) {
            foreach ($modules as $module) {
                $location        = $this->getModuleLocation($module['name']);
                $controller_name = __NAMESPACE__ . '\\Mod\\' . ucfirst($module['name']) . '\\Cli';
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
     * Module run method
     * @param string $module
     * @param string $method
     * @return string
     * @throws \Exception
     */
    public function runCliMethod(string $module, string $method): string {

        $module = strtolower($module);
        $method = strtolower($method);

        $this->setContext($module, $method);

        $params = isset($options['param']) ? $options['param'] : (isset($options['p']) ? $options['p'] : false);
        $params = $params === false ? [] : (is_array($params) ? $params : array($params));


        if ( ! $this->isModuleInstalled($module)) {
            throw new \Exception(sprintf("Модуль %s не найден", $module));
        }

        if ( ! $this->isModuleActive($module)) {
            throw new \Exception(sprintf("Модуль %s не активен", $module));
        }

        $location        = $this->getModuleLocation($module);
        $controller_name = __NAMESPACE__ . '\\Mod\\' . ucfirst($module) . '\\Cli';
        $controller_path = "{$location}/Cli.php";

        if ( ! file_exists($controller_path)) {
            throw new \Exception(sprintf("Файл %s не найден", $controller_path));
        }
        require_once $controller_path;

        if ( ! class_exists($controller_name)) {
            throw new \Exception(sprintf("Класс %s не найден", $controller_name));
        }

        $mod_methods = get_class_methods($controller_name);
        $cli_method  = ucfirst($method);
        if ( ! array_search($cli_method, $mod_methods)) {
            throw new \Exception(sprintf("В классе %s не найден метод %s", $controller_name, $cli_method));
        }

        $controller = new $controller_name();
        $result     = call_user_func_array(array($controller, $cli_method), $params);

        if (is_scalar($result)) {
            return $result . PHP_EOL;
        }

        return '';
    }
}