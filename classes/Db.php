<?php
namespace Core3\Classes;
use Core3\Exceptions\DbException;
use \Laminas\Db\Adapter\Adapter;
use Laminas\Cache\Exception\ExceptionInterface;


/**
 * @property Db\Adapter $db
 */
abstract class Db extends System {


    /**
     * @param string $param_name
     * @return Cache|Log|Adapter|mixed|null
     * @throws DbException
     * @throws ExceptionInterface
     */
	public function __get(string $param_name) {

        $result = null;

        if ($this->hasStaticCache($param_name)) {
            $result = $this->getStaticCache($param_name);

        } else {
            if (str_starts_with($param_name, 'db')) {
                if (strlen($param_name) > 2) {
                    $connection_name = strtolower(substr($param_name, 2));
                    $result          = $this->initConnection($connection_name);
                } else {
                    $result = $this->initConnection();
                }
            }

            if ( ! is_null($result)) {
                $this->setStaticCache($param_name, $result);

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
     * @param string $connection_name
     * @return Adapter
     * @throws DbException
     */
    public function initConnection(string $connection_name = 'base'): Adapter {

        $settings = $this->config?->system?->db?->{$connection_name}?->toArray();

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
            $host = $this->config?->system?->db?->base?->params?->database;
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
            $host        = $this->config?->system?->db?->base?->params?->database;
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
     * @throws DbException
     */
    final public function getModuleLocation(string $module_name): string {

		return DOC_ROOT  . '/' . $this->getModuleFolder($module_name);
	}


    /**
     * Возврат пути до директории в которой находится модуль
     * @param string $module_name
     * @return string
     * @throws DbException
     */
    final public function getModuleFolder(string $module_name): string {

        $module_name = trim(strtolower($module_name));

        if ( ! $module_name) {
            throw new DbException($this->_("Не определено название модуля."));
        }

        if ($module_name == 'admin') {
            $folder = "core3/admin";

        } else {
            $host = $this->config?->system?->db?->base?->params?->database;
            $key  = "core3_mod_folder_{$host}_{$module_name}";

            if ( ! $this->cache->test($key)) {
                $module_version = $this->db->fetchOne("
                    SELECT version 
                    FROM core_modules 
                    WHERE name = ?
                ", $module_name);

                if ($module_version) {
                    $folder = "mod/{$module_name}/v{$module_version}";
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
     */
    final public function getModuleVersion(string $module_name): string {

        $host = $this->config?->system?->db?->base?->params?->database;
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
     * Получение конфигурации модуля
     * @param string $module_name
     * @return Config|null
     * @throws DbException
     * @throws \Exception
     */
    final protected function getModuleConfig(string $module_name):? Config {

        $key_name = "conf_mod_{$module_name}";

        if ($this->hasStaticCache($key_name)) {
            $result = $this->getStaticCache($key_name);

        } else {
            $module_loc = $this->getModuleLocation($module_name);
            $conf_file  = "{$module_loc}/conf.ini";

            if (is_file($conf_file)) {
                $config = new Config();
                $config->addFileIni($conf_file);

                if (isset($config->{$_SERVER['SERVER_NAME']})) {
                    $config = $config->{$_SERVER['SERVER_NAME']};

                } elseif (isset($config->production)) {
                    $config = $config->production;
                }

                $config->setReadOnly();

                $result = $config;

            } else {
                $result = null;
            }

            $this->setStaticCache($key_name, $result);
        }

        return $result;
    }


    /**
     * @param array $settings
     * @return Adapter
     * @throws DbException
     */
    private function setupConnection(array $settings): Adapter {

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