<?php
namespace Core3\Mod\Admin\Handlers;
use Core3\Classes\Handler;
use Core3\Classes\Request;
use Core3\Classes\Response;
use Core3\Classes\Table;
use Core3\Classes\Tools;
use Core3\Exceptions\AppException;
use Core3\Exceptions\Exception;
use Core3\Exceptions\HttpException;
use Core3\Mod\Admin\Classes\Modules\View;
use Laminas\Cache\Exception\ExceptionInterface;


/**
 *
 */
class Modules extends Handler {

    /**
     * Сохранение пользователя
     * @param Request $request
     * @return Response
     * @throws AppException
     * @throws HttpException
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function save(Request $request): Response {

        $this->checkHttpMethod($request, ['post', 'put']);
        $this->checkVersion($this->modAdmin->tableModules, $request);

        $fields = [
            'title'       => 'req,string(1-): Название',
            'icon'        => 'string(0-255): Иконка',
            'description' => 'string(0-65000): Описание',
            'group_name'  => 'string(0-255): Название группы',
            'is_active'   => 'int(0-1): Активен',
        ];

        $record_id = $request->getQuery('id');
        $controls  = $request->getFormContent() ?? [];
        $controls  = $this->clearData($controls);


        if ($errors = $this->validateFields($fields, $controls)) {
            return $this->getResponseError($errors);
        }


        $this->db->beginTransaction();
        try {
            $row_old = $this->modAdmin->tableUsers->getRowById($record_id);
            $row     = $this->saveData($this->modAdmin->tableUsers, $controls, $record_id);


            if ($row_old->is_active != $row->is_active) {
                $this->event($this->modAdmin->tableUsers->getTable() . '_active', [
                    'id'        => $row->id,
                    'is_active' => $row->is_active == 1,
                ]);
            }

            $this->db->commit();

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->getResponseSuccess([
            'id' => $row->id
        ]);
    }


    /**
     * Сохранение пользователя
     * @param Request $request
     * @return Response
     * @throws AppException
     * @throws HttpException
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function saveNew(Request $request): Response {

        $this->checkHttpMethod($request, ['post', 'put']);
        $this->checkVersion($this->modAdmin->tableUsers, $request);

        $fields = [
            'login'        => 'req,string(1-255),chars(alphanumeric|_|\\): Логин',
            'email'        => 'email: Email',
            'role_id'      => 'req,int(1-): Роль',
            'pass'         => 'req,string(4-): Пароль',
            'fname'        => 'string(0-255): Имя',
            'lname'        => 'string(0-255): Фамилия',
            'mname'        => 'string(0-255): Отчество',
            'is_admin'     => 'string(1|0): Администратор безопасности',
            'is_active'    => 'string(1|0): Активен',
            'avatar_type'  => 'string(none|generate|upload): Аватар',
        ];

        $controls = $request->getFormContent() ?? [];
        $controls = $this->clearData($controls);


        $avatar = null;

        if ( ! empty($controls['avatar'])) {
            $avatar = $controls['avatar'];
            unset($controls['avatar']);
        }

        if ($errors = $this->validateFields($fields, $controls)) {
            return $this->getResponseError($errors);
        }

        if ( ! $this->modAdmin->tableUsers->isUniqueLogin($controls['login'])) {
            throw new AppException($this->_("Пользователь с таким логином уже существует"));
        }

        if ( ! empty($controls['email']) &&
             ! $this->modAdmin->tableUsers->isUniqueEmail($controls['email'])
        ) {
            throw new AppException($this->_("Пользователь с таким email уже существует"));
        }

        $controls['pass'] = Tools::passSalt(md5($controls['pass']));
        $controls['name'] = trim(implode(' ', [
            $controls['lname'] ?? '',
            $controls['fname'] ?? '',
            $controls['mname'] ?? ''
        ]));


        $this->db->beginTransaction();
        try {
            $row = $this->saveData($this->modAdmin->tableUsers, $controls);

            if ($controls['avatar_type'] == 'upload') {
                $this->saveFiles($this->modAdmin->tableUsersFiles, $row->id, 'avatar', $avatar);

            } elseif ($controls['avatar_type'] == 'generate') {
                $this->generateAvatar($row);
            }

            $this->event($this->modAdmin->tableUsers->getTable() . '_active', [
                'id'        => $row->id,
                'is_active' => $controls['is_active'] == '1',
            ]);

            $this->db->commit();

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->getResponseSuccess([
            'id' => $row->id
        ]);
    }


    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getTableInstalled(Request $request): array {

        $base_url = "#/admin/modules";
        $view     = new View();
        return $view->getTableInstalled($base_url);
    }


    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getTableAvailable(Request $request): array {

        $base_url = "#/admin/modules";
        $view     = new View();
        return $view->getTableAvailable($base_url);
    }


    /**
     * Данные для таблицы
     * @param Request $request
     * @return Response
     * @throws \CoreUI\Table\Exception
     */
    public function tableInstall(Request $request): Response {

        $table = new Table\Db($request);

//        $sort = $request->getQuery('sort');
//
//        if ($sort && is_array($sort)) {
//            $table->setSort($sort, [
//                'avatar'        => 'u.avatar',
//                'login'         => 'u.login',
//                'name'          => "CONCAT_WS(' ', u.lname, u.fname, u.mname)",
//                'email'         => 'u.email',
//                'role_title'    => 'r.title',
//                'date_activity' => '(SELECT us.date_last_activity FROM core_users_sessions AS us WHERE u.id = us.user_id ORDER BY date_last_activity DESC LIMIT 1)',
//                'date_created'  => 'u.date_created',
//                'is_active_sw'  => "u.is_active_sw = 'Y'",
//                'is_admin_sw'   => 'u.is_admin_sw',
//            ]);
//        }


//        $search = $request->getQuery('search');
//
//        if ($search && is_array($search)) {
//            $table->setSearch($search, [
//                'login'        => (new Search\Like())->setField('u.login'),
//                'role'         => (new Search\Equal())->setField('u.role_id'),
//                'date_created' => (new Search\Between())->setField('u.date_created'),
//                'is_admin_sw'  => (new Search\Equal())->setField('u.is_admin_sw'),
//            ]);
//        }

        $table->setQuery("
            SELECT u.id,
                   u.login,
                   u.email,
                   CONCAT_WS(' ', u.lname, u.fname, u.mname) AS name,
                   u.is_active,
                   u.is_admin,
                   u.date_created,
                   r.title AS role_title,
                   
                   (SELECT us.date_last_activity
                    FROM core_users_sessions AS us 
                    WHERE u.id = us.user_id
                    ORDER BY date_last_activity DESC
                    LIMIT 1) AS date_activity
            FROM core_users AS u
                LEFT JOIN core_roles AS r ON u.role_id = r.id
        ");

        $records = $table->fetchRecords();

        foreach ($records as $record) {

            $record->login    = ['content' => $record->login, 'url' => "#/admin/users/{$record->id}"];
            $record->avatar   = "core3/user/{$record->id}/avatar";
            $record->is_admin = $record->is_admin == 1
                ? [ 'type' => 'danger', 'text' => $this->_('Да') ]
                : [ 'type' => 'none',   'text' => $this->_('Нет') ];

            $record->login_user  = [
                'content' => 'Войти',
                'attr'    => ['class' => 'btn btn-sm btn-secondary'],
                'onClick' => "adminUsers.loginUser('{$record->id}');",
            ];
        }

        return $this->getResponseSuccess($table->getResult());
    }


    /**
     * @param $data
     * @return string
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function saveModule($data) {

        $data = $this->filterControls($data);
        if ( ! $this->validateControls($data)) {
            return $this->getResponse();
        }

        $record_id = $this->getRecordId();
        if ( ! $record_id) {
            if (preg_match("~[^a-z0-9]~", $data['name'])) {
                $this->addError($this->_("Идентификатор может состоять только из цифр или маленьких латинских букв"), 'name');
            }
            $module_id = (int)$this->getSessData('module_id');
            $data['module_id']    = $module_id;
            $data['date_created'] = new \Zend_Db_Expr('NOW()');

            $module = $this->db->fetchRow("
                SELECT name,
                       is_active
                FROM core_modules 
                WHERE id = ?
            ", $record_id);

            if ($module['is_active'] != $data['is_active']) {
                // Обработка включения модуля
                if ($data['is_active'] == 1) {
                    if (isset($data['dependencies'])) {
                        $inactive_dependencies = [];
                        foreach ($data['dependencies'] as $dep_module) {
                            $active_dep_module = $this->db->fetchRow("
                                SELECT name, 
                                       title
                                FROM core_modules 
                                WHERE name = ?
                                  AND is_active = 1
                            ", $dep_module);

                            if (empty($active_dep_module)) {
                                $inactive_dependencies[$active_dep_module['name']] = $active_dep_module['title'];
                            }
                        }

                        if ( ! empty($inactive_dependencies)) {
                            $this->addError($this->_("Для активации модуля необходимо активировать модули:") . ' ' . implode(", ", $inactive_dependencies));
                        }
                    }

                // Обработка выключения модуля
                } else {
                    $active_modules = $this->db->fetchAll("
                        SELECT name,
                               title,
                               dependencies 
                        FROM core_modules 
                        WHERE is_active = 1
                    ");
                    $active_dependencies = array();

                    foreach ($active_modules as $module) {
                        if ($module['dependencies']) {
                            $dependencies = json_decode(base64_decode($module['dependencies']));
                            if ( ! empty($dependencies)) {
                                foreach ($dependencies as $dep_module) {
                                    if ($dep_module == $module['name']) {
                                        $active_dependencies[] = $module['title'];
                                    }
                                }
                            }
                        }
                    }

                    if ( ! empty($active_dependencies)) {
                        $this->addError($this->_("Для деактивации модуля необходимо деактивировать зависимые модули:") . ' ' . implode(", ", $active_dependencies));
                    }
                }
            }

            if ($this->isError()) {
                return $this->getResponse();
            }

        } else {
            $module_action = $this->db->fetchRow("
                SELECT a.name, 
                       a.module_id 
                FROM core_modules_sections AS a
                WHERE a.id = ?
            ", $record_id);

            $this->cache->remove($module_action['module_id'] . "_" . $module_action['name']);
            $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('is_active_core_modules'));
        }


        $authNamespace = \Zend_Registry::get('auth');
        $data['lastuser'] = $authNamespace->ID > 0 ? $authNamespace->ID : new \Zend_Db_Expr('NULL');

        if ( ! empty($data['access_default'])) {
            $data['access_default'] = base64_encode(json_encode($data['access_default']));
        }
        if ( ! empty($data['access_add'])) {
            $data['access_add'] = base64_encode(json_encode($data['access_add']));
        }
        if ( ! empty($data['dependencies'])) {
            $data['dependencies'] = base64_encode(json_encode($data['dependencies']));
        }

        $record_id = $this->saveData($data);
        $this->cache->remove($record_id);
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('is_active_core_modules'));

        return $this->getResponse();
    }


    /**
     * @param $data
     * @return string
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
	public function saveAction($data) {

        $data = $this->filterControls($data);
        if ( ! $this->validateControls($data)) {
            return $this->getResponse();
        }

        $record_id = $this->getRecordId();
        if ( ! $record_id) {
            if (preg_match("~[^a-z0-9]~", $data['name'])) {
                $this->addError($this->_("Идентификатор может состоять только из цифр или маленьких латинских букв"), 'name');
            }
            $module_id = (int)$this->getSessData('module_id');
            $data['module_id']    = $module_id;
            $data['date_created'] = new \Zend_Db_Expr('NOW()');

        } else {
            $module_action = $this->db->fetchRow("
                SELECT a.name, 
                       a.module_id 
                FROM core_modules_sections AS a
                WHERE a.id = ?
            ", $record_id);

            $this->cache->remove($module_action['module_id'] . "_" . $module_action['name']);
            $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('is_active_core_modules'));
        }

        $authNamespace = \Zend_Registry::get('auth');
        $data['lastuser'] = $authNamespace->ID > 0 ? $authNamespace->ID : new \Zend_Db_Expr('NULL');

        if ( ! empty($data['access_default'])) {
            $data['access_default'] = base64_encode(json_encode($data['access_default']));
        }
        if ( ! empty($data['access_add'])) {
            $data['access_add'] = base64_encode(json_encode($data['access_add']));
        }

        $this->saveData($data);
        $this->cache->remove("all_settings_" . $this->config->system->database->params->dbname);
        return $this->getResponse();
	}


    /**
     * @param $data
     * @return \Core3\Classes\Cache|\Core3\Classes\Log|\Laminas\Db\Adapter\Adapter|\Laminas\Db\TableGateway\AbstractTableGateway|mixed|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function saveAvail($data) {

        try {
            $sid 			= Zend_Session::getId();
            $upload_dir 	= $this->config->tmp . '/' . $sid;

            $f = explode("###", $data['control']['files|name']);
            $fn = $upload_dir . '/' . $f[0];
            if (!file_exists($fn)) {
                throw new Exception("Файл {$f[0]} не найден");
            }
            $size = filesize($fn);
            if ($size !== (int)$f[1]) {
                throw new Exception("Что-то пошло не так. Размер файла {$f[0]} не совпадает");
            }

            $file_type = mime_content_type($fn);

            if ($file_type == "application/zip") {

                $content = file_get_contents($fn);

                /* Распаковка архива */
                $zip = new ZipArchive();
                $destinationFolder = $upload_dir . '/t_' . uniqid();
                if ($zip->open($fn) === true){
                    /* Распаковка всех файлов архива */
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $zip->extractTo($destinationFolder, $zip->getNameIndex($i));
                    }
                    $zip->close();
                } else {
                    throw new Exception($this->_("Ошибка архива"));
                }

                if (!is_file($destinationFolder . "/install/install.xml")) {
                    throw new Exception($this->_("install.xml не найден."));
                }
                if (is_file($destinationFolder . "/readme.txt")) {
                    $readme = file_get_contents($destinationFolder . "/readme.txt");
                }
                $xmlObj = simplexml_load_file($destinationFolder . "/install/install.xml", 'SimpleXMLElement', LIBXML_NOCDATA);


                //проверяем все SQL и PHP файлы на ошибки
                require_once('core3/mod/admin/Modules_Install.php');

                $inst                          = new InstallModule();
                $mInfo                         = array('install' => array());
                $mInfo['install']['module_id'] = $xmlObj->install->module_id;
                $inst->setMInfo($mInfo);
                $errors    = array();
                $filesList = $inst->getFilesList($destinationFolder);
                //для проверки ошибок в файлах пхп
                $php_path = '';
                if (empty($this->config->php) || empty($this->config->php->path)) {
                    $php_path = $this->config->php->path;
                }
                foreach ($filesList as $path) {
                    $fName = substr($path, strripos($path, '/') + 1);
                    //проверка файлов php
                    if (substr_count($fName, ".php") && !empty($php_path))
                    {
                        $tmp = exec("{$php_path} -l {$path}");

                        if (substr_count($tmp, 'Errors parsing')) {
                            $errors['php'][] = " - Ошибки в '{$fName}': Errors parsing";
                        }
                    }
                }
                //проверка наличия подключаемых файлов
                if (!empty($xmlObj->install->sql)) {
                    $path = $destinationFolder . "/install/" . $xmlObj->install->sql;
                    if (!file_exists($path)) {
                        $errors['sql'][] = ' - Не найден указанный файл в install.xml: ' . $xmlObj->install->sql;
                    }
                }
                if (!empty($xmlObj->uninstall->sql)) {
                    $path = $destinationFolder . "/install/" . $xmlObj->uninstall->sql;
                    if (!file_exists($path)) {
                        $errors['sql'][] = ' - Не найден указанный файл в install.xml: ' . $xmlObj->uninstall->sql;
                    }
                }
                if (!empty($xmlObj->migrate)) {
                    $migrate = $inst->xmlParse($xmlObj->migrate);
                    foreach ($migrate as $m) {
                        //проверка подключаемых файлов php
                        if (!empty($m['php'])) {
                            $path = $destinationFolder . "/install/" . $m['php'];
                            if (!file_exists($path)) {
                                $errors['php'][] = ' - Не найден указанный файл в install.xml: ' . $m['php'];
                            }
                        }
                        //проверка подключаемых файлов sql
                        if (!empty($m['sql'])) {
                            $path = $destinationFolder . "/install/" . $m['sql'];
                            if (!file_exists($path)) {
                                $errors['sql'][] = ' - Не найден указанный файл в install.xml: ' . $m['sql'];
                            }
                        }
                    }
                }
                //проверка подключаемых файлов php
                if (!empty($xmlObj->install->php)) {
                    $path = $destinationFolder . "/install/" . $xmlObj->install->php;
                    if (!file_exists($path)) {
                        $errors['php'][] = ' - Не найден указанный файл в install.xml: ' . $xmlObj->install->php;
                    }
                }
                //ошибки проверки sql и php
                if (!empty($errors)) {
                    $text = (!empty($errors['php']) ? implode('<br>', $errors['php']) : "") . (!empty($errors['sql']) ? ("<br>" . implode('<br>', $errors['sql'])) : "");
                    throw new Exception($text);
                }

                //получаем хэш для файлов модуля
                $files_hash = $inst->extractHashForFiles($destinationFolder);
                if (empty($files_hash)) {
                    throw new Exception($this->_("Не удалось получить хэшь файлов модуля"));
                }

                $is_exist = $this->db->fetchOne(
                    "SELECT id
                       FROM core_available_modules
                      WHERE module_id = ?
                        AND version = ?",
                    array($xmlObj->install->module_id, $xmlObj->install->version)
                );
                if (!empty($is_exist)) {
                    $this->db->update(
                        'core_available_modules',
                        array(
                            'name' 	        => $xmlObj->install->module_name,
                            'module_id' 	=> $xmlObj->install->module_id,
                            'data' 		    => $content,
                            'descr' 	    => $xmlObj->install->description,
                            'version' 	    => $xmlObj->install->version,
                            'install_info'  => serialize($inst->xmlParse($xmlObj)),
                            'readme' 	    => !empty($readme) ? $readme : new Zend_Db_Expr('NULL'),
                            'lastuser' 	    => $this->auth->ID,
                            'files_hash'    => serialize($files_hash)
                        ),
                        "id = '{$is_exist}'"
                    );
                } else {
                    $this->db->insert(
                        'core_available_modules',
                        array(
                            'name' 	        => $xmlObj->install->module_name,
                            'module_id' 	=> $xmlObj->install->module_id,
                            'data' 		    => $content,
                            'descr' 	    => $xmlObj->install->description,
                            'version' 	    => $xmlObj->install->version,
                            'install_info'  => serialize($inst->xmlParse($xmlObj)),
                            'readme' 	    => !empty($readme) ? $readme : new Zend_Db_Expr('NULL'),
                            'lastuser' 	    => $this->auth->ID,
                            'files_hash'    => serialize($files_hash)
                        )
                    );
                }
            }
            else {
                throw new Exception($this->_("Неверный тип архива"));
            }

            $this->done($data);

        } catch (Exception $e) {
            $this->error[] = $e->getMessage();
            $this->displayError($data);
        }

        return $this->response;
    }

}