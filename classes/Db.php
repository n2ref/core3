<?php
namespace Core3\Classes;
use Core3\Exceptions\DbException;
use Core3\Mod\Admin;
use \Laminas\Db\Adapter\Adapter;
use Laminas\Cache\Exception\ExceptionInterface;


/**
 * @property-read Db\Adapter $db
 */
class Db extends System {


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
    protected function issetConnection(): bool {

        return isset($this->db);
    }


    /**
     * @param string $connection_name
     * @return Adapter
     * @throws DbException
     */
    protected function initConnection(string $connection_name = 'base'): Adapter {

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
    protected function getConnection(string $database, string $username, string $password, array $options = []): Adapter {

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
    final protected function isModuleActive(string $module_name): bool {

        if ($module_name == 'admin') {
            $is_active = true;

        } else {
            $host = $this->config?->system?->db?->base?->params?->database;
            $key  = "core2_mod_is_active{$host}_{$module_name}";

            if ($this->cache->test($key)) {
                $result = $this->cache->load($key);
                $is_active = is_null($result) ? null : (bool)$result;
            }

            if ( ! isset($is_active)) {
                $table  = new Admin\Tables\Modules();
                $module = $table->getRowByName($module_name);

                $is_active = $module->is_active == '1';

                if ($module) {
                    $this->cache->save($key, $is_active, ['core3_mod']);
                }
            }
        }

        return $is_active;
	}


    /**
     * @param string $module_name
     * @return bool
     * @throws ExceptionInterface
     */
    final protected function isModuleInstalled(string $module_name): bool {

	    if ($module_name == 'admin') {
            $is_install = true;

        } else {
            $module_name = trim(strtolower($module_name));
            $host        = $this->config?->system?->db?->base?->params?->database;
            $key         = "core3_mod_installed_{$host}_{$module_name}";

            if ($this->cache->test($key)) {
                $result     = $this->cache->load($key);
                $is_install = is_null($result) ? null : (bool)$result;
            }

            if ( ! isset($is_install)) {
                $table  = new Admin\Tables\Modules();
                $module = $table->getRowByName($module_name);

                $is_install = false;

                if ($module) {
                    $is_install = true;
                    $this->cache->save($key, $is_install, ['core3_mod']);
                }
            }
        }

        return $is_install;
	}


    /**
     * Возврат абсолютного пути до директории в которой находится модуль
     * @param string $module_name
     * @return string
     * @throws DbException
     */
    final protected function getModuleLocation(string $module_name): string {

		return DOC_ROOT  . '/' . $this->getModuleFolder($module_name);
	}


    /**
     * Возврат пути до директории в которой находится модуль
     * @param string $module_name
     * @return string
     * @throws DbException
     */
    final protected function getModuleFolder(string $module_name): string {

        $module_name = trim(strtolower($module_name));

        if ( ! $module_name) {
            throw new DbException($this->_("Не определено название модуля."));
        }

        if ($module_name == 'admin') {
            $folder = "core3/admin";

        } else {
            $folder = "mod/{$module_name}";
        }

        return $folder;
	}


    /**
     * Возврат версии модуля
     * @param string $module_name
     * @return array
     * @throws ExceptionInterface
     */
    final protected function getModuleInfo(string $module_name): array {

        $db_name = $this->config?->system?->db?->base?->params?->database;
        $key     = "core3_mod_{$db_name}_{$module_name}";

        if ($this->cache->test($key)) {
            $result = $this->cache->load($key);
            $module = is_null($result) ? null : (bool)$result;
        }

        if ( ! isset($module)) {
            $table = new Admin\Tables\Modules();
            $module = $table->getRowByName($module_name)?->toArray();

            if ( ! empty($module)) {
                $this->cache->save($key, $module, ['core3_mod']);
            }

            $result = $module;
        }

        return $result;
	}


    /**
     * Получение данных о модуле из файла
     * @param string $module_name
     * @return array
     * @throws DbException
     */
    final protected function getModuleInfoFromFile(string $module_name): array {

        $module_location = $this->getModuleLocation($module_name);
        $module_file     = "{$module_location}/module.json";

        if (file_exists($module_file)) {
            $info_content = file_get_contents($module_file);
            $info         = @json_decode($info_content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $info = [];
            }
        }

        return $info ?? [];
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