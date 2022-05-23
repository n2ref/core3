<?php
namespace Core3\Classes;

/**
 *
 */
abstract class Common extends Acl {

    protected $auth      = null;
    protected $module    = '';
    protected $submodule = '';
    protected $recource  = '';

	private static array $params = [];


    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
	public function __construct() {
		parent::__construct();

        $child_class_name = preg_match('~^Core3/Mod/([A-z0-9\_]+)/Controller$~', get_class($this), $match)
            ? $match[1]
            : '';


        $this->module    = strtolower($child_class_name);
        $this->submodule = Registry::has('submodule') ? Registry::get('submodule') : null;
        $this->recource  = $this->module && $this->submodule ? "{$this->module}_{$this->submodule}" : $this->module;
        $this->auth      = Registry::has('auth') ? Registry::get('auth') : null;
    }


    /**
     * @param string $k
     * @return bool
     */
	public function __isset($k) {
		return isset(self::$params[$k]);
	}


    /**
     * Автоматическое подключение других модулей
     * инстансы подключенных объектов хранятся в массиве $_params
     *
     * @param string $param_name
     * @return Common|null|\Zend_Db_Adapter_Abstract|\Zend_Config_Ini|Mod\Admin\Controller|mixed
     * @throws \Exception
     */
    public function __get(string $param_name) {

        if (strpos($param_name, 'data') === 0) {
            return parent::__get($param_name . "|" . $this->module);

        } elseif (strpos($param_name, 'worker') === 0) {
            return parent::__get($param_name);
        }

        $param_value = NULL;

        if (array_key_exists($param_name, self::$params)) {
            $param_value = self::$params[$param_name];

        } else {
            // Получение экземпляра класса для работы с правами пользователей
            if ($param_name == 'acl') {
                $param_value = $this->{$param_name} = Registry::get('acl');

            } elseif ($param_name === 'moduleConfig') {

                $module_config = $this->getModuleConfig($this->module);

                if ($module_config === false) {
                    \Core2\Error::Exception($this->_("Не найден конфигурационный файл модуля."), 500);
                } else {
                    $param_value = $this->{$param_name} = $module_config;
                }

            } // Получение экземпляра контроллера указанного модуля
            elseif (strpos($param_name, 'mod') === 0) {
                $module_name = strtolower(substr($param_name, 3));
                $param_value = $this->getModuleConstructor($module_name);

            } // Получение экземпляра плагина для указанного модуля
            elseif (strpos($param_name, 'plugin') === 0) {
                $plugin      = ucfirst(substr($param_name, 6));
                $module      = $this->module;
                $location    = $this->getModuleLocation($this->module);
                $plugin_file = "{$location}/Plugins/{$plugin}.php";
                if ( ! file_exists($plugin_file)) {
                    throw new Exception(sprintf($this->translate->tr("Плагин \"%s\" не найден."), $plugin));
                }
                require_once("CommonPlugin.php");
                require_once($plugin_file);
                $temp = "\\" . $module . "\\Plugins\\" . $plugin;
                $param_value    = $this->{$param_name} = new $temp();
                $param_value->setModule($this->module);
            } // Получение экземпляра api класса указанного модуля
            elseif (strpos($param_name, 'api') === 0) {
                $module = substr($param_name, 3);
                if ($param_name == 'api') $module = $this->module;
                if ($this->isModuleActive($module)) {
                    $location   = $module == 'Admin'
                        ? DOC_ROOT . "core2/mod/admin"
                        : $this->getModuleLocation($module);
                    $module     = ucfirst($module);
                    $module_api = "Mod{$module}Api";
                    if ( ! file_exists($location . "/{$module_api}.php")) {
                        return new stdObject();
                    } else {
                        require_once "CommonApi.php";
                        require_once $location . "/{$module_api}.php";
                        $api = new $module_api();
                        if ( ! is_subclass_of($api, 'CommonApi')) {
                            return new stdObject();
                        }
                        $param_value = $this->{$param_name} = $api;
                    }
                } else {
                    return new stdObject();
                }

            // Получение экземпляра логера
            } elseif ($param_name == 'log') {
                $param_value = new Log();

            } else {
                return parent::__get($param_name);
            }
        }

        return $param_value;
	}


    /**
     * @param string $param_name
     * @param mixed  $param_value
     * @return $this
     */
	public function __set(string $param_name, mixed $param_value) {
        self::$params[$param_name] = $param_value;
		return $this;
	}


    /**
     * @param string $module_name
     * @return bool
     */
    final public function isModuleInstalled(string $module_name): bool {

        $key = "core3_mod_installed_{$this->config?->system?->database?->params?->dbname}_{$module_name}";

        if ( ! $this->cache->test($key)) {
            $is_install = parent::isModuleInstalled($module_name);
            $this->cache->save($is_install, $key, ['core3_mod']);

        } else {
            $is_install = $this->cache->load($key);
        }

        return $is_install;
    }


    /**
     * @param string $module_name
     * @return string
     * @throws \Exception
     */
    final public function getModuleLocation(string $module_name): string {

        $key = "core3_mod_location_{$this->config?->system?->database?->params?->dbname}_{$module_name}";

        if ( ! $this->cache->test($key)) {
            $location = parent::getModuleLocation($module_name);
            $this->cache->save($location, $key, ['core3_mod']);

        } else {
            $location = $this->cache->load($key);
        }

        return $location;
    }


    /**
     * Возврат версии модуля
     * @param string $module_name
     * @return string
     */
    final public function getModuleVersion(string $module_name): string {

        $key = "core3_mod_version_{$this->config?->system?->database?->params?->dbname}_{$module_name}";

        if ( ! $this->cache->test($key)) {
            $version = parent::getModuleVersion($module_name);
            $this->cache->save($version, $key, ['core3_mod']);

        } else {
            $version = $this->cache->load($key);
        }

        return $version;
    }


    /**
     * @throws \Exception
     */
    public function getModuleConstructor(string $module_name) {

        if ($module_name === 'admin') {
            require_once(DOC_ROOT . 'core2/inc/CoreController.php');
            $param_value         = $this->modAdmin = new CoreController();
            $param_value->module = $module;


        } elseif ($location = $this->getModuleLocation($module_name)) {
            if ( ! $this->isModuleActive($module)) {
                throw new Exception("Модуль \"{$module}\" не активен");
            }

            $cl              = ucfirst($param_name) . 'Controller';
            $controller_file = $location . '/' . $cl . '.php';

            if ( ! file_exists($controller_file)) {
                throw new Exception(sprintf($this->translate->tr("Модуль \"%s\" сломан. Не найден файл контроллера."), $module));
            }

            $autoload_file = $location . "/vendor/autoload.php";
            if (file_exists($autoload_file)) {
                require_once($autoload_file);
            }

            require_once($controller_file);

            if ( ! class_exists($cl)) {
                throw new Exception(sprintf($this->translate->tr("Модуль \"%s\" сломан. Не найден класс контроллера."), $module));
            }


            $param_value         = $this->{$param_name} = new $cl();
            $param_value->module = $module;

        } else {
            throw new Exception(sprintf($this->translate->tr("Модуль \"%s\" не найден"), $module));
        }
    }


    /**
     * @param string $module_name
     * @return bool
     */
    final public function isModuleActive(string $module_name): bool {

        $key = "core2_mod_is_active{$this->config?->system?->database?->params?->dbname}_{$module_name}";

        if ( ! $this->cache->test($key)) {
            $is_active = parent::isModuleActive($module_name);
            $this->cache->save($is_active, $key, ['core3_mod']);
        } else {
            $is_active = $this->cache->load($key);
        }

        return $is_active;
    }


    /**
     * @param string $name
     * @return Log
     * @throws \Exception
     */
    final public function log($name) {

        $log = new Log($name);
        return $log;
    }


    /**
	 * Print link to CSS file
	 * @param string $href - CSS filename
	 */
	public function printCss($href) {
        Tools::printCss($href);
	}


	/**
	 * Print link to CSS file
	 * @param string $module
	 * @param string $href - CSS filename
	 */
    public function printCssModule($module, $href) {
        $module_src = $this->getModuleFolder($module);
        Tools::printCss($module_src . $href);
	}


	/**
	 * 
	 * Print link to JS file
	 * @param string $src - JS filename
	 * @param bool   $chachable
	 */
    public function printJs($src, $chachable = false) {
        Tools::printJs($src, $chachable);
	}


	/**
	 *
	 * Print link to JS file
	 * @param string $module
	 * @param string $src - JS filename
	 * @param bool   $chachable
	 */
    public function printJsModule($module, $src, $chachable = false) {
	    $module_src = $this->getModuleFolder($module);
        Tools::printJs($module_src . $src, $chachable);
	}
}