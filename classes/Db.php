<?php
namespace Core3\Classes;
use Core3\Exceptions\DbException;
use \Laminas\Db\Adapter\Adapter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Laminas\Cache\Exception\ExceptionInterface;


/**
 * @property Db\Adapter $db
 */
abstract class Db extends System {

    private static array $params = [];


    /**
     * @param string $k
     * @return bool
     */
    public function __isset(string $k) {
        return isset(self::$params[$k]);
    }


    /**
     * @param string $param_name
     * @return Cache|Log|Adapter|mixed|null
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface|DbException
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
     * @return Adapter
     * @throws ContainerExceptionInterface
     * @throws DbException
     */
    public function initConnection(): Adapter {

        $settings = $this->config?->system?->database?->toArray();

        return $this->setupConnection((array)$settings);
    }


    /**
     * Установка соединения с произвольной базой MySQL
     * @param string $database
     * @param string $username
     * @param string $password
     * @param array  $options
     * @return Adapter
     */
    public function getConnection(string $database, string $username, string $password, array $options = []): Adapter {

        $option_params = $options['params'] ?? [];

        $config = [
            'driver'   => $options['adapter'] ?? 'Pdo_Mysql',
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'hostname' => $option_params['host'] ?? 'localhost',
            'port'     => $option_params['port'] ?? 3306,
            'charset'  => $option_params['charset'] ?? 'utf8',
        ];

        if ( ! empty($option_params)) {
            $config = array_merge($config, $option_params);
        }

        return new Db\Adapter($config);
    }


    /**
     * @param string $module_name
     * @return bool
     * @throws ExceptionInterface
     */
    final public function isModuleActive(string $module_name): bool {

        if ($module_name == 'admin') {
            $is_active = true;

        } else {
            $host = $this->config?->system?->database?->params?->database;
            $key  = "core2_mod_is_active{$host}_{$module_name}";

            if ( ! $this->cache->test($key)) {
                $is_active_sw = $this->db->fetchOne("
                    SELECT is_active_sw 
                    FROM core_modules 
                    WHERE name = ? 
                ", $module_name);

                if ( ! empty($is_active_sw)) {
                    $this->cache->save($key, $is_active_sw == 'Y', ['core3_mod']);
                }

                $is_active = $is_active_sw == 'Y';

            } else {
                $is_active = (bool)$this->cache->load($key);
            }
        }

        return $is_active;
	}


    /**
     * @param string $module_name
     * @return bool
     * @throws ExceptionInterface
     */
    final public function isModuleInstalled(string $module_name): bool {

	    if ($module_name == 'admin') {
	        return true;

        } else {
            $module_name = trim(strtolower($module_name));
            $host        = $this->config?->system?->database?->params?->database;
            $key         = "core3_mod_installed_{$host}_{$module_name}";

            if ( ! $this->cache->test($key)) {
                $is_install = (bool)$this->db->fetchOne("
                    SELECT 1
                    FROM core_modules 
                    WHERE name = ?
                ", $module_name);

                if ($is_install) {
                    $this->cache->save($key, $is_install, ['core3_mod']);
                }

            } else {
                $is_install = (bool)$this->cache->load($key);
            }

            return $is_install;
        }
	}


    /**
     * Возврат абсолютного пути до директории в которой находится модуль
     * @param string $module_name
     * @return string
     * @throws ContainerExceptionInterface
     * @throws DbException
     * @throws ExceptionInterface
     */
    final public function getModuleLocation(string $module_name): string {

		return DOC_ROOT  . '/' . $this->getModuleFolder($module_name);
	}


    /**
     * Возврат пути до директории в которой находится модуль
     * @param string $module_name
     * @return string
     * @throws DbException
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     */
    final public function getModuleFolder(string $module_name): string {

        $module_name = trim(strtolower($module_name));

        if ( ! $module_name) {
            throw new DbException($this->_("Не определено название модуля."));
        }

        if ($module_name == 'admin') {
            $folder = "core3/mod/admin";

        } else {
            $host = $this->config?->system?->database?->params?->database;
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
                    throw new DbException($this->_("Модуль %s не существует", [$module_name]), 404);
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
     * @throws ExceptionInterface
     */
    final public function getModuleVersion(string $module_name): string {

        $host = $this->config?->system?->database?->params?->database;
        $key  = "core3_mod_version_{$host}_{$module_name}";

        if ( ! $this->cache->test($key)) {
            $module = $this->db->fetchRow("
                SELECT version
                FROM core_modules
                WHERE name = ?
            ", $module_name);

            if ( ! empty($module['version'])) {
                $this->cache->save($key, $module['version'], ['core3_mod']);
            }

            $version = $module['version'] ?? '';

        } else {
            $version = $this->cache->load($key);
        }

        return $version;
	}


    /**
     * @return void
     */
    public function beginTransaction(): void {

        $this->db->driver->getConnection()->beginTransaction();
    }


    /**
     * @return void
     */
    public function commit(): void {

        $this->db->driver->getConnection()->commit();
    }


    /**
     * @return void
     */
    public function rollback(): void {

        $this->db->driver->getConnection()->rollback();
    }


    /**
     * @param array $settings
     * @return Adapter
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DbException
     */
    protected function setupConnection(array $settings): Adapter {

        if (empty($settings['params'])) {
            throw new DbException($this->_('Не указано название базы данных'));
        }
        if (empty($settings['params']['database'])) {
            throw new DbException($this->_('Не указано название базы данных'));
        }
        if (empty($settings['params']['username'])) {
            throw new DbException($this->_('Не указано логин для подключения к базе данных'));
        }
        if ( ! isset($settings['params']['password'])) {
            throw new DbException($this->_('Не указан пароль для подключения базе данных'));
        }

        $database = $settings['params']['database'];
        $username = $settings['params']['username'];
        $password = $settings['params']['password'];

        $db = $this->getConnection($database, $username, $password, $settings);

        if ($this->config?->system?->timezone) {
            $db->query("SET time_zone = ?", [$this->config->system->timezone]);
        }

        if ( ! empty($settings['sql_mode'])) {
            $db->query("SET SESSION sql_mode = ?", [$settings['sql_mode']]);
        }

        if ($this->config?->log?->profile?->on &&
            $this->config?->log?->profile?->mysql
        ) {
            $db->query("set profiling = 1");
            $db->query("set profiling_history_size = 100");
        }

        // elsewhere in code, in a bootstrap
        \Laminas\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($db);
        Registry::set('db', $db);

        return $db;
    }
}