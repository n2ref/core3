<?php
namespace Core3\Classes;


/**
 * @property \Zend_Db_Adapter_Abstract $db
 */
abstract class Db extends System {

	private static array $params = [];


    /**
     * @param string $k
     * @return bool
     */
    public function __isset($k) {
        return isset(self::$params[$k]);
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
                $result = $this->initConnection();
            }

            if ( ! is_null($result)) {
                self::$params[$param_name] = $result;

            } else {
                $result = parent::__get($param_name);
            }
        }

        return $result;
	}


    /**
     * @return bool
     */
    public function issetConnection(): bool {

        return isset($this->db);
    }


    /**
     * @return \Zend_Db_Adapter_Abstract
     * @throws \Zend_Db_Exception
     */
    public function initConnection(): \Zend_Db_Adapter_Abstract {

        $settings = $this->config->system->database->toArray();

        return $this->setupConnection($settings);
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
    public function getConnection(string $dbname, string $username, string $password, array $options = []): \Zend_Db_Adapter_Abstract {

        $option_params = $options['params'] ?? [];

        $config = [
            'dbname'   => $dbname,
            'username' => $username,
            'password' => $password,
            'host'     => $option_params['host'] ?? 'localhost',
            'port'     => $option_params['port'] ?? 3306,
            'charset'  => $option_params['charset'] ?? 'utf8',
        ];

        if ( ! empty($options['adapterNamespace'])) {
            $config['adapterNamespace'] = $options['adapterNamespace'];
        }

        if ( ! empty($option_params)) {
            $config = array_merge($config, $option_params);
        }

        $db = \Zend_Db::factory($options['adapter'] ?? 'Pdo_Mysql', $config);
        $db->getConnection();

        return $db;
    }


    /**
     * @param string $module_name
     * @return bool
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    final public function isModuleActive(string $module_name): bool {

        if ($module_name == 'admin') {
            $is_active = true;

        } else {
            $host = $this->config?->system?->database?->params?->dbname;
            $key  = "core2_mod_is_active{$host}_{$module_name}";

            if ( ! $this->cache->test($key)) {
                $is_active = (bool)$this->db->fetchOne("
                    SELECT 1 
                    FROM core_modules 
                    WHERE name = ? 
                      AND is_active_sw = 'Y'
                ", $module_name);

                $this->cache->save($key, $is_active, ['core3_mod']);
            } else {
                $is_active = (bool)$this->cache->load($key);
            }
        }

        return $is_active;
	}


    /**
     * @param string $module_name
     * @return bool
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    final public function isModuleInstalled(string $module_name): bool {

	    if ($module_name == 'admin') {
	        return true;

        } else {
            $host = $this->config?->system?->database?->params?->dbname;
            $key  = "core3_mod_installed_{$host}_{$module_name}";

            if ( ! $this->cache->test($key)) {
                $module_name = trim(strtolower($module_name));

                $is_install = (bool)$this->db->fetchOne("
                    SELECT 1 
                    FROM core_modules 
                    WHERE name = ?
                ", $module_name);

                $this->cache->save($key, $is_install, ['core3_mod']);

            } else {
                $is_install = (bool)$this->cache->load($key);
            }

            return $is_install;
        }
	}


    /**
     * Возврат абсолютного пути до директории в которой находится модуль
     * @param string $module_name
     * @return mixed
     * @throws \Exception
     */
    final public function getModuleLocation(string $module_name): string {

		return DOC_ROOT  . '/' . $this->getModuleFolder($module_name);
	}


    /**
     * Возврат пути до директории в которой находится модуль
     * @param string $module_name
     * @return string
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    final public function getModuleFolder(string $module_name): string {

        $module_name = trim(strtolower($module_name));

        if ( ! $module_name) {
            throw new \Exception($this->_("Не определено название модуля."));
        }

        if ($module_name == 'admin') {
            $folder = "core3/mod/admin";

        } else {
            $host = $this->config?->system?->database?->params?->dbname;
            $key  = "core3_mod_folder_{$host}_{$module_name}";

            if ( ! $this->cache->test($key)) {
                $module = $this->db->fetchRow("
                    SELECT is_system_sw, 
                           version 
                    FROM core_modules 
                    WHERE name = ?
                ", $module_name);

                if ($module) {
                    $folder = $module['is_system_sw'] == "Y"
                        ? "core3/mod/{$module_name}/v{$module['version']}"
                        : "mod/{$module_name}/v{$module['version']}";
                } else {
                    throw new \Exception($this->_("Модуль %s не существует", [$module_name]), 404);
                }

                $this->cache->save($key, $folder, ['core3_mod']);

            } else {
                $folder = $this->cache->load($key);
            }

            return $folder;
        }

        return $folder;
	}


    /**
     * Возврат версии модуля
     * @param string $module_name
     * @return string
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    final public function getModuleVersion(string $module_name): string {

        $host = $this->config?->system?->database?->params?->dbname;
        $key  = "core3_mod_version_{$host}_{$module_name}";

        if ( ! $this->cache->test($key)) {
            $version = $this->db->fetchOne("
                SELECT version
                FROM core_modules
                WHERE name = ?
            ", $module_name);

            $this->cache->save($key, $version, ['core3_mod']);

        } else {
            $version = $this->cache->load($key);
        }

        return $version;
	}


    /**
     * @param array $settings
     * @return \Zend_Db_Adapter_Abstract
     * @throws \Zend_Db_Exception
     * @throws \Exception
     */
    protected function setupConnection(array $settings): \Zend_Db_Adapter_Abstract {

        if (empty($settings['params'])) {
            throw new \Exception($this->_('Не указано название базы данных'));
        }
        if (empty($settings['params']['dbname'])) {
            throw new \Exception($this->_('Не указано название базы данных'));
        }
        if (empty($settings['params']['username'])) {
            throw new \Exception($this->_('Не указано логин для подключения к базе данных'));
        }
        if ( ! isset($settings['params']['password'])) {
            throw new \Exception($this->_('Не указан пароль для подключения базе данных'));
        }

        $dbname   = $settings['params']['dbname'];
        $username = $settings['params']['username'];
        $password = $settings['params']['password'];

        $db = $this->getConnection($dbname, $username, $password, $settings);

        if ($this->config?->system?->timezone) {
            $db->query("SET time_zone = '{$this->config->system->timezone}'");
        }

        if ( ! empty($settings['sql_mode'])) {
            $db->query("SET SESSION sql_mode = ?", $settings['sql_mode']);
        }

        if ($this->config?->log?->profile?->on &&
            $this->config?->log?->profile?->mysql
        ) {
            $db->query("set profiling=1");
            $db->query("set profiling_history_size = 100");
        }


        \Zend_Db_Table::setDefaultAdapter($db);

        Registry::set('db', $db);
        return $db;
    }
}