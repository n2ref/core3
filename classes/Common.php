<?php
namespace Core3\Classes;

require_once 'Acl.php';
require_once 'Translate.php';


/**
 * Class Common
 * @property string $module
 */
class Common extends Acl {

	protected $auth;
	protected $resId;

	private static $_params = [];
	private static $_models = [];


    /**
     * Common constructor.
     */
	public function __construct() {

        $child = get_class($this);
		parent::__construct();

        $reg = \Zend_Registry::getInstance();

        //$this->resId = ! empty($_GET['module']) ? $_GET['module'] : 'admin';
        if ($child) {
            $this->module = strtolower($child);
            if (!$reg->isRegistered('invoker')) {
                $reg->set('invoker', $this->module);
            }
        }
		else {
			$this->module = !empty($context[0]) ? $context[0] : '';
		}

        $this->auth      = $reg->get('auth');
        $this->resId     = $this->module;

		if ( ! empty($context[1]) && $context[1] !== 'index') {
			$this->resId .= '_' . $context[1];
		}
	}

    /**
     * @param string $k
     * @return bool
     */
	public function __isset($k) {
		return isset(self::$_params[$k]);
	}


    /**
     * @return mixed
     * @throws \Zend_Exception
     */
    public function getInvoker() {
        return \Zend_Registry::get('invoker');
    }


    /**
     * Автоматическое подключение других модулей
     * инстансы подключенных объектов хранятся в массиве $_params
     *
     * @param string $param
     * @return Common|null|\Zend_Db_Adapter_Abstract|\Zend_Config_Ini|Mod\Admin\Controller|mixed
     * @throws \Exception
     */
    public function __get($param) {

        //исключение для гетера базы или кеша, выполняется всегда
        if (in_array($param, ['db', 'cache', 'translate', 'log'])) {
            return parent::__get($param);
        }

        $result = null;

        if (array_key_exists($param, self::$_params)) {
            $result = self::$_params[$param];

        } else {
            // Получение экземпляра класса для работы с правами пользователей
            if ($param == 'acl') {
                $result = self::$_params['acl'] = \Zend_Registry::getInstance()->get('acl');

            // Получение конфига модуля
            } elseif ($param != 'config' && strpos($param, 'config') === 0) {
                $module     = strtolower(substr($param, 6));
                $module_loc = self::getModuleLocation($module);
                $conf_file  = "{$module_loc}/conf.ini";

                if (is_file($conf_file)) {
                    $configMod = new \Zend_Config_Ini($conf_file);
                    $extMod    = $configMod->getExtends();
                    $configExt = new \Zend_Config_Ini(DOC_ROOT . "/conf.ini");
                    $ext       = $configExt->getExtends();

                    if ( ! empty($_SERVER['SERVER_NAME']) &&
                        array_key_exists($_SERVER['SERVER_NAME'], $ext) &&
                        array_key_exists($_SERVER['SERVER_NAME'], $extMod)
                    ) {
                        $modConfig = new \Zend_Config_Ini($conf_file, $_SERVER['SERVER_NAME']);
                    } else {
                        $modConfig = new \Zend_Config_Ini($conf_file, 'production');
                    }

                    $modConfig->setReadOnly();
                    $result = self::$_params[$param] = $modConfig;
                } else {
                    throw new \Exception(sprintf($this->_("Не найден конфигурационный файл %s."), $conf_file));
                }

            // Получение экземпляра контроллера указанного модуля
            } elseif (strpos($param, 'mod') === 0) {
                $module = strtolower(substr($param, 3));

                if ($location = $this->getModuleLocation($module)) {
                    if ( ! $this->isModuleActive($module)) {
                        throw new \Exception(sprintf($this->_('Модуль "%s" не активен'), $module));
                    }

                    $cl              = ucfirst($param) . 'Controller';
                    $controller_file = $location . '/' . $cl . '.php';

                    if ( ! file_exists($controller_file)) {
                        throw new \Exception(sprintf($this->_('Не найден файл %s.'), $cl));
                    }

                    require_once($controller_file);

                    if (!class_exists($cl)) {
                        throw new \Exception(sprintf($this->_('Не найден класс %s.'), $cl));
                    }

                    $result         = self::$_params[$param] = new $cl();
                    $result->module = $module;

                } else {
                    $message = $this->_('Модуль "%s" не найден');
                    throw new \Exception(sprintf($message, $module));
                }

            // Получение экземпляра api класса указанного модуля
            } elseif (strpos($param, 'api') === 0) {
                $module     = substr($param, 3);
                if ($param == 'api') $module = $this->module;
                if ($this->isModuleActive($module)) {
                    $location = $module == 'Admin'
                        ? DOC_ROOT . "core3/mod/admin"
                        : $this->getModuleLocation($module);
                    $module = ucfirst($module);
                    $module_api = "Mod{$module}Api";
                    if (!file_exists($location . "/{$module_api}.php")) {
                        return new stdObject();
                    } else {
                        require_once "CommonApi.php";
                        require_once $location . "/{$module_api}.php";
                        $result = $this->{$param} = new $module_api();
                    }
                } else {
                    return new stdObject();
                }

            } else {
                $result = self::$_params[$param] = $this;
            }
        }

        return $result;
	}


    /**
     * @param string $k
     * @param mixed  $v
     * @return $this
     */
	public function __set($k, $v) {
		self::$_params[$k] = $v;
		return $this;
	}


    /**
     * @param string $module
     * @param string $model
     * @return mixed
     * @throws \Exception
     */
	function getModel($module, $model) {

        $module = strtolower($module);
        $model  = ucfirst(strtolower($model));

        if (array_key_exists($module . '-' . $model, self::$_models)) {
            $result = self::$_models[$module . '-' . $model];

        } else {

            if ($location = $this->getModuleLocation($module)) {
                if ( ! $this->isModuleActive($module)) {
                    throw new \Exception(sprintf($this->_('Модуль "%s" не активен'), $module));
                }

                $model_class = 'Core\\Mod\\' . ucfirst($module). '\\' . $model;
                $model_file  = $location . '/resources/' . $model . '.php';

                if ( ! file_exists($model_file)) {
                    throw new \Exception(sprintf($this->_('Не найден файл %s.'), $model_file));
                }

                require_once($model_file);

                if ( ! class_exists($model_class)) {
                    throw new \Exception(sprintf($this->_('Не найден класс %s.'), $model_class));
                }

                $result = self::$_models[$module . '-' . $model] = new $model_class();

            } else {
                $message = $this->_('Модуль "%s" не найден');
                throw new \Exception(sprintf($message, $module));
            }
        }

        return $result;
    }


    /**
	 * Print link to CSS file
	 * @param string $href - CSS filename
	 */
	protected function printCss($href) {
        Tools::printCss($href);
	}


	/**
	 * Print link to CSS file
	 * @param string $module
	 * @param string $href - CSS filename
	 */
	protected function printCssModule($module, $href) {
        $module_src = $this->getModuleSrc($module);
        Tools::printCss($module_src . $href);
	}


	/**
	 * 
	 * Print link to JS file
	 * @param string $src - JS filename
	 * @param bool   $chachable
	 */
	protected function printJs($src, $chachable = false) {
        Tools::printJs($src, $chachable);
	}


	/**
	 *
	 * Print link to JS file
	 * @param string $module
	 * @param string $src - JS filename
	 * @param bool   $chachable
	 */
	protected function printJsModule($module, $src, $chachable = false) {
	    $module_src = $this->getModuleSrc($module);
        Tools::printJs($module_src . $src, $chachable);
	}
}