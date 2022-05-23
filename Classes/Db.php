<?php
namespace Core3\Classes;


/**
 * @property \Zend_Db_Adapter_Abstract $db
 * @property Translate                 $translate
 */
abstract class Db {

    /**
     * @var \Zend_Config
     */
    protected mixed $config;

	private static array $params = [];


    /**
     * @param \stdClass|null $config
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
	public function __construct(\stdClass $config = null) {

        $this->config = is_null($config)
            ? Registry::get('config')
            : $config;
	}


    /**
     * @param string $param_name
     * @return mixed|\Zend_Db_Adapter_Abstract|\Zend_Db_Table_Row_Abstract|null
     * @throws \Zend_Db_Exception
     * @throws \Exception
     */
	public function __get(string $param_name) {

        $result = null;

        if (array_key_exists($param_name, self::$params)) {
            $result = self::$params[$param_name];

        } else {
            if ($param_name == 'db') {
                $result = $this->establishConnection($this->config->system->database->toArray());

            } elseif (strpos($param_name, 'data') === 0) {
                $param_name_explode = explode("|", $param_name);
                $model_name         = substr($param_name_explode[0], 4);
                $module_name        = ! empty($param_name_explode[1]) ? $param_name_explode[1] : 'admin';

                $result = $this->getModel($module_name, $model_name);
            }

            if ( ! is_null($result)) {
                self::$params[$param_name] = $result;
            }
        }

        return $result;
	}


    /**
     * Перевод текста
     * @param string $text
     * @param string $module_name
     * @return string|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function _(string $text, string $module_name = 'core3'): ?string {

        $translate = Registry::isRegistered('translate') ? Registry::get('translate') : null;

        return $translate?->tr($text, $module_name);
    }


    /**
     * Установка соединения с произвольной базой MySQL
     * @param string $dbname
     * @param string $username
     * @param string $password
     * @param array  $options
     * @return \Zend_Db_Adapter_Abstract
     * @throws \Zend_Db_Exception
     */
    public function newConnector(string $dbname, string $username, string $password, array $options = []): \Zend_Db_Adapter_Abstract {

        $db = \Zend_Db::factory($options['adapter'] ?? 'Pdo_Mysql', [
            'dbname'           => $dbname,
            'username'         => $username,
            'password'         => $password,
            'host'             => $options['host'] ?? 'localhost',
            'port'             => $options['port'] ?? 3306,
            'charset'          => $options['charset'] ?? 'utf8',
            'adapterNamespace' => $options['adapterNamespace'] ?? 'Core_Db_Adapter'
        ]);

        $db->getConnection();

        return $db;
    }


    /**
     * @param string $module_name
     * @param string $model_name
     * @return \Zend_Db_Table_Row_Abstract
     * @throws \Exception
     */
    function getModel(string $module_name, string $model_name): \Zend_Db_Table_Row_Abstract {

        $module_name = strtolower($module_name);
        $model_name  = ucfirst(strtolower($model_name));
        $model_key   = "{$module_name}-{$model_name}";

        $location = $this->getModuleLocation($module_name);

        if ( ! $location) {
            $message = $this->_('Модуль "%s" не найден');
            throw new \Exception(sprintf($message, $module_name));
        }

        if ( ! $this->isModuleActive($module_name)) {
            throw new \Exception(sprintf($this->_('Модуль "%s" не активен'), $module_name));
        }

        $model_class = '\\Core\\Mod\\' . ucfirst($module_name). '\\' . $model_name;
        $model_file  = "{$location}/Model/{$model_name}.php";

        if ( ! file_exists($model_file)) {
            throw new \Exception(sprintf($this->_('Не найден файл %s.'), $model_file));
        }

        require_once($model_file);

        if ( ! class_exists($model_class)) {
            throw new \Exception(sprintf($this->_('Не найден класс %s.'), $model_class));
        }

        $model_instance = new $model_class();

        if ( ! $model_instance instanceof \Zend_Db_Table_Row_Abstract) {
            throw new \Exception(sprintf($this->_('Некорректный класс модели %s'), $model_class));
        }

        return $model_instance;
    }


    /**
     * @param string $module_name
     * @return bool
     */
	public function isModuleActive(string $module_name): bool {

        if ($module_name == 'admin') {
            $result = true;

        } else {
            $result = (bool)$this->db->fetchOne("
                SELECT 1 
                FROM core_modules 
                WHERE name = ? 
                  AND is_active_sw = 'Y'
            ", $module_name);
        }

        return $result;
	}


    /**
     * @param string $module_name
     * @return bool
     */
	public function isModuleInstalled(string $module_name): bool {

	    if ($module_name == 'admin') {
	        return true;

        } else {
            $module_name = trim(strtolower($module_name));

            $is_installed = $this->db->fetchOne("
                SELECT 1 
                FROM core_modules 
                WHERE name = ?
            ", $module_name);

            return !! $is_installed;
        }
	}


    /**
     * Возврат абсолютного пути до директории в которой находится модуль
     * @param string $module_name
     * @return mixed
     * @throws \Exception
     */
	public function getModuleLocation(string $module_name): string {

		return DOC_ROOT  . '/' . $this->getModuleFolder($module_name);
	}


    /**
     * Возврат пути до директории в которой находится модуль
     * @param string $module_name
     * @return string
     * @throws \Exception
     */
	public function getModuleFolder(string $module_name): string {

        $module_name = trim(strtolower($module_name));

        if ( ! $module_name) {
            throw new \Exception($this->_("Не определено название модуля."));
        }

        if ($module_name == 'admin') {
            $location = "core3/mod/admin";

        } else {
            $module = $this->db->fetchRow("
                SELECT is_system_sw, 
                       version 
                FROM core_modules 
                WHERE name = ?
            ", $module_name);

            if ($module) {
                if ($module['is_system_sw'] == "Y") {
                    $location = "core3/mod/{$module_name}/v{$module['version']}";
                } else {
                    $location = "mod/{$module_name}/v{$module['version']}";
                }
            } else {
                throw new \Exception($this->_("Модуль не существует"), 404);
            }
        }

        return $location;
	}


    /**
     * Возврат версии модуля
     * @param string $module_name
     * @return string
     */
	public function getModuleVersion(string $module_name): string {

		return $this->db->fetchOne("
            SELECT version
            FROM core_modules
            WHERE name = ?
        ", $module_name);
	}


    /**
     * @param array $database
     * @return \Zend_Db_Adapter_Abstract
     * @throws \Zend_Db_Exception
     * @throws \Exception
     */
    protected function establishConnection(array $database): \Zend_Db_Adapter_Abstract {

        if (empty($database['dbname'])) {
            throw new \Exception($this->_('Не указано название базы данных'));
        }
        if (empty($database['username'])) {
            throw new \Exception($this->_('Не указано логин для подключения к базе данных'));
        }
        if ( ! isset($database['password'])) {
            throw new \Exception($this->_('Не указан пароль для подключения базе данных'));
        }

        $db = $this->newConnector($database['dbname'], $database['username'], $database['password'], $database);

        \Zend_Db_Table::setDefaultAdapter($db);

        $db->getConnection();
        Registry::set('db', $db);

        if ($this->config?->system?->timezone) {
            $db->query("SET time_zone = '{$this->config->system->timezone}'");
        }

        return $db;
    }
}