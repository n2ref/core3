<?php
namespace Core3\Classes;
use Core3\Exceptions\DbException;
use Core3\Exceptions\RuntimeException;
use Core3\Mod;
use Laminas\Cache\Exception\ExceptionInterface;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @property Mod\Admin\Controller $modAdmin
 */
abstract class Common extends Acl {

    protected $module   = '';
    protected $section  = '';
    protected $recource = '';

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

        $this->module   = strtolower($child_class_name);
        $this->section  = Registry::has('section') ? Registry::get('section') : null;
        $this->recource = $this->module && $this->section ? "{$this->module}_{$this->section}" : $this->module;
    }


    /**
     * @param string $k
     * @return bool
     */
	public function __isset(string $k) {
		return isset(self::$params[$k]);
	}


    /**
     * @param string $param_name
     * @return Cache|Log|\Laminas\Db\Adapter\Adapter|AbstractTableGateway|mixed|null
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __get(string $param_name) {

        if (array_key_exists($param_name, self::$params)) {
            $result = self::$params[$param_name];

        } else {
            if (strpos($param_name, 'table') === 0) {
                $table_name = substr($param_name, 5);
                $result     = $this->getTable($this->module, $table_name);

            } elseif (strpos($param_name, 'mod') === 0) {
                $module_name = strtolower(substr($param_name, 3));
                $result      = $this->getModuleController($module_name);


            } elseif (strpos($param_name, 'handler') === 0) {
                $module_name = strtolower(substr($param_name, 3));
                $result      = $this->getModuleHandler($module_name);

            } elseif (strpos($param_name, 'worker') === 0) {
                $worker_name = substr($param_name, 6);
                $result      = $this->getTable($this->module, $worker_name);
            }

            if ( ! empty($result)) {
                self::$params[$param_name] = $result;
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
     * @param string $src
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function addJs(string $src): void {

        $src = trim($src);
        $src = Tools::addSrcHash($src);

        if (Registry::has('js')) {
            $js = Registry::get('js');

            if (is_array($js)) {
                $js[] = $src;

                Registry::set('js', $js);

            } else {
                Registry::set('js', [ $src ]);
            }

        } else {
            Registry::set('js', [ $src ]);
        }
    }


    /**
     * @param string $module
     * @param string $src
     * @return void
     * @throws ContainerExceptionInterface
     * @throws DbException
     * @throws ExceptionInterface
     */
    public function addModuleJs(string $module, string $src): void {

        $module_folder = $this->getModuleFolder($module);

        $this->addCss("{$module_folder}/{$src}");
    }


    /**
     * @param string $src
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function addCss(string $src): void {

        $src = trim($src);
        $src = Tools::addSrcHash($src);

        if (Registry::has('css')) {
            $css = Registry::get('css');

            if (is_array($css)) {
                $css[] = $src;

                Registry::set('css', $css);

            } else {
                Registry::set('css', [ $src ]);
            }

        } else {
            Registry::set('css', [ $src ]);
        }
    }


    /**
     * @param string $module
     * @param string $src
     * @return void
     * @throws ContainerExceptionInterface
     * @throws DbException
     * @throws ExceptionInterface
     */
    public function addModuleCss(string $module, string $src): void {

        $module_folder = $this->getModuleFolder($module);

        $this->addCss("{$module_folder}/{$src}");
    }


    /**
     * @param string $module_name
     * @return mixed
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    protected function getModuleController(string $module_name): mixed {

        $location = $this->getModuleLocation($module_name);

        if ( ! $location) {
            throw new RuntimeException($this->_("Модуль \"%s\" не найден", [$module_name]));
        }


        if ($module_name === 'admin') {
            require_once "{$location}/Controller.php";
            $result = new Mod\Admin\Controller();

        } else {
            if ( ! $this->isModuleActive($module_name)) {
                throw new RuntimeException($this->_("Модуль \"%s\" не активен", [$module_name]));
            }

            $controller_file = "{$location}/Controller.php";

            if ( ! file_exists($controller_file)) {
                throw new RuntimeException($this->_("Модуль \"%s\" сломан. Не найден файл контроллера.", [$module_name]));
            }

            $autoload_file = "{$location}/vendor/autoload.php";

            if (file_exists($autoload_file)) {
                require_once $autoload_file;
            }

            require_once $controller_file;

            $module_class_name = "\\Core3\\Mod\\" . ucfirst($module_name) . "\\Controller";

            if ( ! class_exists($module_class_name)) {
                throw new RuntimeException($this->_("Модуль \"%s\" сломан. Не найден класс контроллера.", [$module_name]));
            }

            $result = new $module_class_name();
        }

        return $result;
    }


    /**
     * @param string $module_name
     * @param string $model_name
     * @return AbstractTableGateway
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    protected function getTable(string $module_name, string $model_name): AbstractTableGateway {

        $module_name = strtolower($module_name);
        $model_name  = ucfirst($model_name);

        $location = $this->getModuleLocation($module_name);

        if ( ! $location) {
            throw new RuntimeException($this->_('Модуль "%s" не найден', [$module_name]));
        }

        if ( ! $this->isModuleActive($module_name)) {
            throw new RuntimeException($this->_('Модуль "%s" не активен', [$module_name]));
        }

        $table_class = '\\Core3\\Mod\\' . ucfirst($module_name). '\\Tables\\' . $model_name;
        $table_file  = "{$location}/tables/{$model_name}.php";

        if ( ! file_exists($table_file)) {
            throw new RuntimeException($this->_('Не найден файл таблицы: %s', [$table_file]));
        }

        require_once $table_file;

        if ( ! class_exists($table_class)) {
            throw new RuntimeException($this->_('Не найден класс таблицы %s', [$table_class]));
        }


        if ( ! $this->issetConnection()) {
            $this->initConnection();
        }

        $table_instance = new $table_class();

        if ( ! $table_instance instanceof AbstractTableGateway) {
            throw new RuntimeException($this->_('Некорректный класс таблицы %s', [$table_class]));
        }

        return $table_instance;
    }


    /**
     * Обработчик модуля
     * @param string $module_name
     * @return mixed
     * @throws ExceptionInterface
     * @throws RuntimeException
     * @throws DbException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getModuleHandler(string $module_name): mixed {

        $location = $this->getModuleLocation($module_name);

        if ( ! $location) {
            throw new RuntimeException($this->_("Модуль \"%s\" не найден", [$module_name]));
        }

        if ($module_name === 'admin') {
            require_once "{$location}/Handler.php";
            $result = new Mod\Admin\Handler();

        } else {
            // Подключение файла с обработчиком
            $location         = $this->getModuleLocation($module_name);
            $module_save_path = "{$location}/Handler.php";

            if ( ! file_exists($module_save_path)) {
                throw new RuntimeException($this->_('Не найден файл "%s" в модуле "%s"', [$module_save_path, $module_name]));
            }
            require_once $module_save_path;


            // Инициализация обработчика
            $handler_class_name = __NAMESPACE__ . '\\Mod\\' . ucfirst($module_name) . '\\Handler';
            if ( ! class_exists($handler_class_name)) {
                throw new RuntimeException($this->_('Не найден класс "%s" в модуле "%s"', [$handler_class_name, $module_name]));
            }


            // Выполнение обработчика
            $result = new $handler_class_name();
        }

        return $result;
    }
}