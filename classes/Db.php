<?php
namespace Core3;

require_once "Cache.php";
use Zend\Cache\StorageFactory;


/**
 * Class Db
 * @property \Zend_Db_Adapter_Abstract $db
 * @property Cache                     $cache
 * @property Translate                 $translate
 * @property Log                       $log
 */
class Db {

	protected $frontendOptions = [
		'lifetime'                => 40000,
		'automatic_serialization' => true
	];
	protected $backendOptions = [];
	protected $backend        = 'File';

    /**
     * @var \Zend_Config|object
     */
    protected $config;

    private $_settings      = [];
	private static $_params = [];


    /**
     * Db constructor.
     * @param \Zend_Config|object|null $config
     * @throws \Exception
     */
	public function __construct($config = null) {

	    if (is_null($config)) {
	        // Если выполняется кампойзер, то зенда не будет
            $this->config = class_exists('\Zend_Registry')
                ? \Zend_Registry::get('config')
                : new \stdClass();
		} else {
			$this->config = $config;
		}
	}


	/**
	 * @param string $param
	 * @return mixed|\Zend_Cache_Core|\Zend_Db_Adapter_Abstract|Log|Translate
	 * @throws \Zend_Exception
	 */
	public function __get($param) {

        $result = null;

        if (array_key_exists($param, self::$_params)) {
            $result = self::$_params[$param];

        } else {
            if ($param == 'db') {
                $result = $this->establishConnection($this->config->system->database);
            }

            // Получение указанного кэша
            if ($param == 'cache') {
                $reg = \Zend_Registry::getInstance();

                if ( ! $this->config) {
                    $this->config = $reg->get('config');
                }

                $options = $this->config->system->cache->options ? $this->config->system->cache->options->toArray() : [];
                $adapter = ! empty($this->config->system->cache->adapter) ? $this->config->system->cache->adapter : 'Filesystem';

                if (isset($this->config->system->cache->adapter)) {
                    $adapter = $this->config->system->cache->adapter;
                    $options = $this->config->system->cache->options->toArray();

                } else {
                    if ($adapter == 'Filesystem' && $this->config->system->cache) { //если кеш задан в основном конфиге
                        $options['cache_dir'] = $this->config->system->cache;
                    }
                }

                $options['namespace'] = "Core";
                $sf = StorageFactory::factory([
                    'adapter' => [
                        'name'    => $adapter,
                        'options' => $options,
                        'plugins' => ['serializer'],
                    ],
                ]);
                $sf->addPlugin(StorageFactory::pluginFactory('serializer'));

                $result = new Cache($sf);
            }

            // Получение экземпляра переводчика
            if ($param == 'translate') {
                $result = \Zend_Registry::get('translate');
            }

            // Получение экземпляра логера
            if ($param == 'log') {
                $result = new Log();
            }


            if ( ! is_null($result)) {
                self::$_params[$param] = $result;
            }
        }

        return $result;
	}


    /**
     * @param string $str
     * @return string
     */
    public function _($str) {
        return call_user_func([$this->translate, 'tr'], $str);
	}



    /**
     * Получение экземпляра модели модуля
     * @param string $module
     * @param string $data_name
     * @return \Zend_Db_Table_Abstract
     * @throws \Exception
     */
    public function getData($module, $data_name) {

        $model_key = "data_{$module}_{$data_name}";

        if (array_key_exists($model_key, self::$_params)) {
            $result = self::$_params[$model_key];

        } else {
            $location   = $this->getModuleLocation($module);
            $module     = ucfirst(strtolower($module));
            $data_name  = ucfirst(strtolower($data_name));
            $model_file = $location . "/data/$data_name.php";


            if ( ! file_exists($model_file)) {
                throw new \Exception(sprintf($this->_('Файл %s не найдена.'), $model_file));
            }

            require_once $model_file;

            $class_name = "\\Core\\Mod\\{$module}\\Data\\{$data_name}";
            if ( ! class_exists($class_name)) {
                throw new \Exception(sprintf($this->_("Класс %s не найден"), $class_name));
            }

            $this->db; ////FIXME грязный хак для того чтобы сработал сеттер базы данных. Потому что иногда его здесь еще нет, а для инициализаци модели используется адаптер базы данных по умолчанию
            $result = self::$_params[$model_key] = new $class_name();
        }

        return $result;
	}


	/**
	 * Установка соединения с произвольной базой MySQL
	 * @param string $dbname
	 * @param string $username
	 * @param string $password
	 * @param string $host
	 * @param string $charset
	 * @param string $adapter
	 *
	 * @return \Zend_Db_Adapter_Abstract
	 */
	public function newConnector($dbname, $username, $password, $host = 'localhost', $charset = 'utf8', $adapter = 'Pdo_Mysql') {
		$params = [
			'host'     => $host,
			'username' => $username,
			'password' => $password,
			'dbname'   => $dbname,
			'charset'  => $charset
		];
        $db = \Zend_Db::factory($adapter, $params);
        $db->getConnection();
        return $db;
	}


	/**
	 * @param string $module_id
	 * @return array
	 */
	public function getModuleName($module_id) {

		if ( ! $this->cache->test($module_id . '_name')) {
			$data = explode("_", $module_id);

			if ( ! empty($data[1])) {
				$module = $this->db->fetchRow("
                    SELECT m.title,
                           ma.title AS action_title
                    FROM core_modules AS m
                        INNER JOIN core_modules_actions AS ma ON ma.module_id = m.id
                    WHERE CONCAT(m.name, '_', ma.name) = ?
                ", $module_id);
				$module = array($module['title'], $module['action_title']);

			} else {
				$module = $this->db->fetchRow("
                    SELECT m.title
                    FROM core_modules AS m
                    WHERE m.name = ?
                ", $module_id);
				$module = array($module['title']);
			}

			$this->cache->save($module, $module_id . '_name');

		} else {
			$module = $this->cache->load($module_id . '_name');
		}

		return $module;
	}


    /**
     * @param string $expired
     */
	public function closeSession($expired = 'N') {
		$auth = \Zend_Registry::get('auth');

		if ($auth && $auth->ID && $auth->ID > 0) {
			$where = [
			    $this->db->quoteInto("user_id = ?", $auth->ID),
			    $this->db->quoteInto("sid = ?", \Zend_Session::getId()),
                $this->db->quoteInto("ip = ?", $_SERVER['REMOTE_ADDR'])
            ];
			$this->db->update('core_session', array(
				'logout_time' => new \Zend_Db_Expr('NOW()'),
				'is_expired_sw' => $expired),
                $where
			);
		}
	}


	/**
	 * @param string $code
	 * @return string
	 */
	public function getSetting($code) {
		$this->getAllSettings();
		return isset($this->_settings[$code]) ? $this->_settings[$code]['value'] : false;
	}


	/**
	 * @param string $code
	 * @return string
	 */
	public function getCustomSetting($code) {
		$this->getAllSettings();
		if (isset($this->_settings[$code]) && $this->_settings[$code]['data_group'] == 'custom') {
			return $this->_settings[$code]['value'];
		}
		return false;
	}


	/**
	 * @param string $code
	 * @return string
	 */
	public function getPersonalSetting($code) {
		$this->getAllSettings();
		if (isset($this->_settings[$code]) && $this->_settings[$code]['data_group'] == 'personal') {
			return $this->_settings[$code]['value'];
		}
		return false;
	}


	/**
	 * @param string $global_id
	 * @return array
	 */
	public function getEnumList($global_id) {

		$res = $this->db->fetchAll("
            SELECT id, 
                   name, 
                   custom_field, 
                   is_default_sw
            FROM core_enum
            WHERE is_active_sw = 'Y'
            AND parent_id = (SELECT id 
                             FROM core_enum 
                             WHERE global_id = ? 
                               AND is_active_sw = 'Y')
            ORDER BY seq
        ", $global_id);

		$data = array();
		foreach ($res as $value) {
			$data[$value['id']] = array(
				'value' => $value['name'],
				'is_default' => ($value['is_default_sw'] == 'Y' ? true : false)
			);
			$data[$value['id']]['custom'] = array();
			if ($value['custom_field']) {
				$temp = explode(":::", $value['custom_field']);
				foreach ($temp as $val) {
					$temp2 = explode("::", $val);
					$data[$value['id']]['custom'][$temp2[0]] = isset($temp2[1]) ? $temp2[1] : '';
				}
			}
		}
		return $data;
	}


	/**
	 * Формирует пару ключ=>значение
	 *
	 * @param string $global_id - глобальный идентификатор справочника
	 * @param bool   $empty_first
	 * @return array
	 */
	public function getEnumDropdown($global_id, $empty_first = false) {

		$data = $this->db->fetchPairs("
            SELECT `id`, 
                   `name`
            FROM core_enum
            WHERE is_active_sw = 'Y'
            AND parent_id = (SELECT id 
                             FROM core_enum 
                             WHERE global_id = ? 
                               AND is_active_sw = 'Y')
            ORDER BY seq
        ", $global_id);

		if ($empty_first) {
			$data = ['' => ''] + $data;
		}
		return $data;
	}


	/**
	 * Получает значение справочника по первичному ключу
	 *
	 * @param int $id
	 * @return string
	 */
	public function getEnumValueById($id) {
		$res = $this->db->fetchOne("
            SELECT name 
            FROM core_enum 
            WHERE id = ?
        ", $id);
		return $res;
	}


	/**
	 * @param int $id
	 * @return array
	 */
	public function getEnumById($id) {

		$res = $this->db->fetchRow("
            SELECT id, 
                   name, 
                   custom_field, 
                   is_default_sw
            FROM core_enum
            WHERE is_active_sw = 'Y'
            AND id = ?
        ", $id);

		if ($res['custom_field']) {
			$temp = array();
			$temp2 = explode(":::", $res['custom_field']);
			foreach ($temp2 as $fields) {
				$fields = explode("::", $fields);
				$temp[$fields[0]] = $fields[1];
			}
			$res['custom_field'] = $temp;
		}
		return $res;
	}


	/**
	 * @param int $id
	 * @return bool|string
	 */
	final public function isUserActive($id) {
		if ($id === -1) {
		    return true;
        }
		return $this->db->fetchOne("
            SELECT 1 
            FROM core_users 
            WHERE id = ? 
              AND is_active_sw = 'Y'
        ", $id);
	}


	/**
	 * @param string $name
	 * @return string
	 */
	final public function isModuleActive($name) {

		$key = "is_active_" . $this->config->system->database->params->dbname . "_" . $name;

		if ( ! $this->cache->test($key)) {
			$is = $this->db->fetchOne("
                SELECT 1 
                FROM core_modules 
                WHERE name = ? 
                  AND is_active_sw = 'Y'
            ", $name);
			$this->cache->save($is, $key, array('is_active_core_modules'));
		} else {
			$is = $this->cache->load($key);
		}
		return $is;
	}


	/**
	 * Определяет, является ли субмодуль активным
	 * Если модуль не активен, то все его субмодели НЕ активны, в независимости от значения в БД
	 * @param string $submodule_id
	 * @return string
	 */
	final public function isSubModuleActive($submodule_id) {
		$id = explode("_", $submodule_id);

		if (isset($id[1]) && $this->isModuleActive($id[0])) {
			$is = $this->db->fetchOne("
                SELECT 1 
                FROM core_modules AS m
                    INNER JOIN core_modules_actions AS s ON s.id = m.id
                WHERE m.name = ? 
                  AND s.name = ? 
                  AND s.is_active_sw = 'Y'
            ", $id);
		} else {
			$is = 0;
		}
		return $is;
	}


	/**
	 * Получаем информацию о субмодуле
	 * @param $submodule_id
	 *
	 * @return bool|false|mixed
	 */
	public function getSubModule($submodule_id) {

		$key = "is_active_" . $this->config->system->database->params->dbname . "_" . $submodule_id;
		$id  = explode("_", $submodule_id);

        if (empty($id[1])) {
			return false;
		}

		if ( ! $this->cache->test($key)) {
			$mods = $this->db->fetchRow("
                SELECT m.id, 
                       m.name, 
                       m.title, 
                       m.is_system_sw, 
                       ma.module_id
                FROM core_modules AS m
                    LEFT JOIN core_modules_actions AS ma ON ma.module_id = m.id AND ma.is_active_sw = 'Y'
                WHERE m.is_active_sw = 'Y'
                  AND m.name = ?
                  AND ma.name = ?
                  ORDER BY ma.seq
            ", $id);
			$this->cache->save($mods, $key, array('is_active_core_modules'));
		} else {
			$mods = $this->cache->load($key);
		}

		return $mods;
	}


	/**
	 * @param string $name
	 * @return bool
	 */
	final public function isModuleInstalled($name) {

	    if ($name == 'admin') {
	        return true;
        } else {
            $name = trim(strtolower($name));
            $key  = "is_installed_" . $this->config->system->database->params->dbname . "_" . $name;

            if ( ! $this->cache->test($key)) {
                $is_installed = $this->db->fetchOne("
                    SELECT 1 
                    FROM core_modules 
                    WHERE name = ?
                ", $name);
                $this->cache->save($is_installed, $key, array('is_active_core_modules'));
            } else {
                $is_installed = $this->cache->load($key);
            }
            return $is_installed;
        }
	}


	/**
	 * Возврат абсолютного пути до директории в которой находится модуль
	 *
	 * @param string $module_id
	 * @return mixed
	 */
	final public function getModuleLocation($module_id) {
		return DOC_ROOT  . '/' . $this->getModuleLoc($module_id);
	}


	/**
	 * возврат версии модуля
	 * @param string $name
	 * @return string
	 */
	final public function getModuleVersion($name) {

		return $this->db->fetchOne("
            SELECT version
            FROM core_modules
            WHERE name = ?
        ", $name);
	}


	/**
	 * Получение абсолютного адреса папки модуля
	 * @param  string $module_id
	 * @return string
	 */
	final public function getModuleSrc($module_id) {
		$loc = $this->getModuleLoc($module_id);
		return DOC_PATH . $loc;
	}


	/**
	 * Получение относительного адреса папки модуля
     * @param $name
	 * @return false|mixed|string
	 * @throws \Exception
	 */
	final public function getModuleLoc($name) {

	    $name = trim(strtolower($name));
		if ( ! $name) {
		    throw new \Exception($this->_("Не определен идентификатор модуля."));
        }

		if ( ! $this->cache->test($name)) {
			if ($name == 'admin') {
				$loc = "core3/mod/admin";
			} else {
				$m = $this->db->fetchRow("
                    SELECT is_system_sw, 
                           version 
                    FROM core_modules 
                    WHERE name = ?
                ", $name);

				if ($m) {
					if ($m['is_system_sw'] == "Y") {
						$loc = "core3/mod/{$name}/v{$m['version']}";
					} else {
						$loc = "mod/{$name}/v{$m['version']}";
					}
				} else {
					throw new \Exception($this->_("Модуль не существует"), 404);
				}
			}
			$this->cache->save($loc, $name);
		} else {
			$loc = $this->cache->load($name);
		}
		return $loc;
	}


	/**
	 * @param string $name
	 * @return Log
	 */
	final public function log($name) {

		$log = new Log($name);
		return $log;
	}


    /**
     * @param mixed $database
     * @return \Zend_Db_Adapter_Abstract
     */
    protected function establishConnection($database) {

        $db = \Zend_Db::factory($database);
        \Zend_Db_Table::setDefaultAdapter($db);
        $db->getConnection();
        \Zend_Registry::getInstance()->set('db', $db);

        if ($this->config->system->timezone) {
            $db->query("SET time_zone = '{$this->config->system->timezone}'");
        }

        return $db;
    }


    /**
     * Сохранение информации о входе пользователя
     * @param \Zend_Session_Namespace $auth
     */
    protected function storeSession(\Zend_Session_Namespace $auth) {

        if ($auth && $auth->ID && $auth->ID > 0) {
            $sid        = \Zend_Session::getId();
            $session_id = $this->db->fetchOne("
                SELECT id 
                FROM core_session 
                WHERE logout_time IS NULL 
                  AND user_id = ? 
                  AND sid = ? 
                  AND ip = ? 
                LIMIT 1
            ", [
                $sid,
                $auth->ID,
                $_SERVER['REMOTE_ADDR']
            ]);

            if ( ! $session_id) {
                $this->db->insert('core_session', array(
                    'sid'        => $sid,
                    'login_time' => new \Zend_Db_Expr('NOW()'),
                    'user_id'    => $auth->ID,
                    'ip'         => $_SERVER['REMOTE_ADDR']
                ));
            }
        }
    }


	/**
	 * Получение всех настроек системы
	 */
	final private function getAllSettings() {

		$key = "all_settings_" . $this->config->system->database->params->dbname;

		if ( ! $this->cache->test($key)) {
			$res = $this->db->fetchAll("
                SELECT code, 
                       value, 
                       data_group 
                FROM core_settings 
                WHERE is_active_sw = 'Y'
            ");
			$is = array();
			foreach ($res as $item) {
				$is[$item['code']] = array(
					'value'      => $item['value'],
					'data_group' => $item['data_group'],
				);
			}
			$this->cache->save($is, $key);
		} else {
			$is = $this->cache->load($key);
		}
		$this->_settings = $is;
	}
}