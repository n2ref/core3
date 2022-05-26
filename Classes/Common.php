<?php
namespace Core3\Classes;
use Core3\Mod;

/**
 * @property Mod\Admin\Controller $modAdmin
 */
abstract class Common extends Acl {

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

        $child_class_name = preg_match('~^Core3\\\Mod\\\([A-z0-9\_]+)\\\Controller$~', get_class($this), $match)
            ? $match[1]
            : '';

        $this->module    = strtolower($child_class_name);
        $this->submodule = Registry::has('submodule') ? Registry::get('submodule') : null;
        $this->recource  = $this->module && $this->submodule ? "{$this->module}_{$this->submodule}" : $this->module;
    }


    /**
     * @param string $k
     * @return bool
     */
	public function __isset($k) {
		return isset(self::$params[$k]);
	}


    /**
     * @param string $param_name
     * @return mixed|void|\Zend_Db_Adapter_Abstract|\Zend_Db_Table_Row_Abstract|null
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     * @throws \Zend_Db_Exception
     */
    public function __get(string $param_name) {

        if (array_key_exists($param_name, self::$params)) {
            $result = self::$params[$param_name];

        } else {
            if (strpos($param_name, 'model') === 0) {
                $model_name = substr($param_name, 5);
                $result     = $this->getModel($this->module, $model_name);

            } elseif (strpos($param_name, 'mod') === 0) {
                $module_name = strtolower(substr($param_name, 3));
                $result      = $this->getModuleConstructor($module_name);

            } elseif (strpos($param_name, 'worker') === 0) {
                $worker_name = substr($param_name, 6);
                $result      = $this->getModel($this->module, $worker_name);

            } else {
                $result = parent::__get($param_name);
            }
        }

        return $result;
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
     * @return mixed
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    public function getModuleConstructor(string $module_name): mixed {

        $location = $this->getModuleLocation($module_name);

        if ( ! $location) {
            throw new \Exception($this->_("Модуль \"%s\" не найден", [$module_name]));
        }


        if ($module_name === 'admin') {
            require_once "{$location}/Controller.php";
            $result = new Mod\Admin\Controller();

        } else {
            if ( ! $this->isModuleActive($module_name)) {
                throw new \Exception($this->_("Модуль \"%s\" не активен", [$module_name]));
            }

            $controller_file = "{$location}/Controller.php";

            if ( ! file_exists($controller_file)) {
                throw new \Exception($this->_("Модуль \"%s\" сломан. Не найден файл контроллера.", [$module_name]));
            }

            $autoload_file = "{$location}/vendor/autoload.php";

            if (file_exists($autoload_file)) {
                require_once $autoload_file;
            }

            require_once $controller_file;

            $module_class_name = "\\Core3\\Mod\\" . ucfirst($module_name) . "\\Controller";

            if ( ! class_exists($module_class_name)) {
                throw new \Exception($this->_("Модуль \"%s\" сломан. Не найден класс контроллера.", [$module_name]));
            }

            $result = new $module_class_name();
        }

        return $result;
    }


    /**
     * @param string $module_name
     * @param string $model_name
     * @return \Zend_Db_Table_Abstract
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    function getModel(string $module_name, string $model_name): \Zend_Db_Table_Abstract {

        $module_name = strtolower($module_name);
        $model_name  = ucfirst(strtolower($model_name));

        $location = $this->getModuleLocation($module_name);

        if ( ! $location) {
            throw new \Exception($this->_('Модуль "%s" не найден', [$module_name]));
        }

        if ( ! $this->isModuleActive($module_name)) {
            throw new \Exception($this->_('Модуль "%s" не активен', [$module_name]));
        }

        $model_class = '\\Core3\\Mod\\' . ucfirst($module_name). '\\Model\\' . $model_name;
        $model_file  = "{$location}/Model/{$model_name}.php";

        if ( ! file_exists($model_file)) {
            throw new \Exception($this->_('Не найден файл модели: %s', [$model_file]));
        }

        require_once $model_file;

        if ( ! class_exists($model_class)) {
            throw new \Exception($this->_('Не найден класс модели %s', [$model_class]));
        }


        if ( ! $this->issetConnection()) {
            $this->initConnection();
        }

        $model_instance = new $model_class();

        if ( ! $model_instance instanceof \Zend_Db_Table_Abstract) {
            throw new \Exception($this->_('Некорректный класс модели %s', [$model_class]));
        }

        return $model_instance;
    }
}