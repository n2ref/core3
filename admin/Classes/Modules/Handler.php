<?php
namespace Core3\Mod\Admin\Classes\Modules;
use Core3\Classes\Request;
use Core3\Classes\Response;
use Core3\Classes\Tools;
use Core3\Classes\Table;
use Core3\Classes\Validator;
use Core3\Exceptions\AppException;
use Core3\Exceptions\Exception;
use Core3\Exceptions\HttpException;
use Core3\Mod\Admin\Classes\Users\Files;
use CoreUI\Form\Control;
use CoreUI\Table\Adapters\Mysql\Search;
use CoreUI\Panel;
use Laminas\Cache\Exception\ExceptionInterface;


/**
 *
 */
class Handler extends \Core3\Classes\Handler {

    private string  $base_url = "admin/modules";


    /**
     * @param
     * @return array
     * @throws \CoreUI\Table\Exception
     */
    public function getRecordsAvailable(): array {

        $table = new Table\Db($this->request);

        $sort             = $this->request->getQuery('sort');
        $select_module_id = $this->request->getQuery('select_module_id');

        if ($sort && is_array($sort)) {
            $table->setSort($sort, [
                'title'       => 'ma.title',
                'description' => 'ma.description',
            ]);
        }


        $search = $this->request->getQuery('search');

        if ($search && is_array($search)) {
            $table->setSearch($search, [
                'text' => (new Search\Like())->setField("CONCAT_WS('|', ma.title, ma.name, ma.vendor, ma.version, ma.description)"),
            ]);
        }

        $table->setQuery("
            SELECT ma.id,
                   ma.name,
                   ma.vendor,
                   ma.title,
                   ma.icon,
                   ma.count_downloads,
                   ma.version,
                   ma.description,
                   
                   (SELECT m.version
                    FROM core_modules AS m
                    WHERE m.name = ma.name
                      AND m.vendor = ma.vendor
                    LIMIT 1) AS install_version
            FROM core_modules_available AS ma
            ORDER BY ma.title
        ");


        foreach ($table->fetchRecords() as $record) {

            $is_select_module = $select_module_id == $record->id;

            $record->icon = $record->icon
                ? "<i class=\"" . htmlspecialchars($record->icon). " \"></i>"
                : '<i class="bi bi-plugin"></i>';


            $icon_classes  = 'border p-2 rounded-3 text-center';
            $icon_classes .= $is_select_module
                ? ' border-secondary-subtle'
                : '';

            $record->icon = "<div class=\"{$icon_classes}\">{$record->icon}</div>";


            $description = [];

            if ( ! is_null($record->count_downloads)) {
                $description[] = "<small class=\"text-secondary pe-2\"><i class=\"bi bi-download\"></i> {$record->count_downloads}</small>";
            }

            $description[] = "<small class=\"text-secondary pe-2\">v{$record->version}</small>";
            $description[] = "<small class=\"text-secondary\">{$record->vendor}/{$record->name}</small>";

            $record->title = implode('<br>', [
                "<span class=\"fw-semibold\">{$record->title}</span>",
                implode(' ', $description)
            ]);

            if ($record->install_version) {
                if ($record->install_version < $record->version) {
                    $record->install = [
                        'content' => '<i class="bi bi-arrow-up-short"></i> ' . $this->_('Обновить'),
                        'attr'    => ['class' => 'btn btn-sm btn-outline-success'],
                        'onClick' => "event.cancelBubble = true; adminModules.updateModule('{$record->id}');",
                    ];

                } else {
                    $record->install = [
                        'content' => $this->_('Установлено'),
                        'attr'    => ['class' => 'btn btn-sm btn-dark', 'disabled' => 'disabled'],
                    ];
                }

            } else {
                $record->install = [
                    'content' => $this->_('Установить'),
                    'attr'    => ['class' => 'btn btn-sm btn-outline-success'],
                    'onClick' => "event.cancelBubble = true; adminModules.installModule('{$record->id}');",
                ];
            }

            if ($is_select_module) {
                $record->setAttr('class', 'table-primary');
            }
        }

        return $table->getResult();
    }


    /**
     * @param int $module_id
     * @return array
     * @throws \CoreUI\Table\Exception
     */
    public function getRecordsVersions(int $module_id): array {

        $table = new Table\Db($this->request);

        $sort = $this->request->getQuery('sort');

        if ($sort && is_array($sort)) {
            $table->setSort($sort, [
                'version'    => 'mav.version',
                'repository' => 'mav.repository',
            ]);
        }


        $search = $this->request->getQuery('search');

        if ($search && is_array($search)) {
            $table->setSearch($search, [
                'text' => (new Search\Like())->setField("CONCAT_WS('|', mav.version, mav.repository)"),
            ]);
        }

        $table->setQuery("
            SELECT mav.id,
                   ma.title,
                   ma.vendor,
                   ma.count_downloads,
                   mav.version,
                   mav.file_url
            FROM core_modules AS m
              JOIN core_modules_available AS ma ON m.name = ma.name AND m.vendor = ma.vendor 
              JOIN core_modules_available_versions AS mav ON ma.id = mav.module_id 
            WHERE m.id = ? 
            ORDER BY mav.version DESC,
                     mav.file_url
        ", [
            $module_id
        ]);


        foreach ($table->fetchRecords() as $record) {

            $record->install = [
                'content' => '<i class="bi bi-download"></i> ' . $this->_('Установить'),
                'attr'    => ['class' => 'btn btn-sm btn-outline-secondary', 'title' => $this->_('Установить версию')],
                'onClick' => "adminModules.installVersion('{$record->id}', '{$record->version}');",
            ];
        }

        return $table->getResult();
    }


    /**
     * @param int $available_module_id
     * @return array
     */
    public function getPanelAvailModule(int $available_module_id): array {

        return (new View())->getPanelAvailModule($available_module_id);
    }


    /**
     * @param int     $module_id
     * @return Response
     * @throws AppException
     * @throws HttpException
     * @throws \Core3\Exceptions\Exception
     */
    public function switchActiveModule(int $module_id): Response {

        $this->checkHttpMethod('patch');
        $controls = $this->request->getJsonContent();

        if ( ! in_array($controls['checked'], ['1', '0'])) {
            throw new HttpException(400, $this->_("Некорректные данные запроса"));
        }

        $module = $this->modAdmin->tableModules->getRowById($module_id);

        if (empty($module)) {
            throw new HttpException(400, $this->_("Указанный модуль не найден"));
        }

        $module->is_active = $controls['checked'];
        $module->save();

        return $this->getResponseSuccess([
            'status' => 'success'
        ]);
    }


    /**
     * Сохранение модуля
     * @param int $module_id
     * @return Response
     * @throws HttpException
     * @throws Exception
     */
    public function saveModule(int $module_id): Response {

        $this->checkHttpMethod('put');
        $this->checkVersion($this->modAdmin->tableModules, $module_id);

        $validator = new Validator([
            'title'       => ['string(0-255)',   $this->_('Название')],
            'icon'        => ['string(0-255)',   $this->_('Иконка')],
            'description' => ['string(0-10000)', $this->_('Описание')],
            'is_active'   => ['int',             $this->_('Активен')],
        ]);

        $controls = $this->request->getJsonContent() ?? [];
        $controls = $this->clearData($controls);

        if ($errors = $validator->validate($controls)) {
            return $this->getResponseError($errors);
        }


        $this->saveData($this->modAdmin->tableModules, $controls, $module_id);

        return $this->getResponseSuccess([
            'id' => $module_id
        ]);
    }


    /**
     * Сохранение модуля
     * @param int $module_id
     * @return Response
     * @throws HttpException
     * @throws Exception
     */
    public function saveModuleHand(int $module_id): Response {

        $this->checkHttpMethod('put');
        $this->checkVersion($this->modAdmin->tableModules, $module_id);

        $validator = new Validator([
            'title'            => ['req,string(1-255)',                       $this->_('Название')],
            'name'             => ['req,string(1-255),chars(alphanumeric|_)', $this->_('Идентификатор')],
            'vendor'           => ['string(0-255),chars(alphanumeric|_)',     $this->_('Издатель')],
            'icon'             => ['string(0-255)',                           $this->_('Иконка')],
            'version'          => ['req,string(1-100)',                       $this->_('Версия')],
            'description'      => ['string(0-65000)',                         $this->_('Описание')],
            'is_active'        => ['int(0-1)',                                $this->_('Активен')],
            'is_visible'       => ['int(0-1)',                                $this->_('Видимый')],
            'is_visible_index' => ['int(0-1)',                                $this->_('Имеет главную страницу')],
        ]);


        $controls = $this->request->getJsonContent() ?? [];
        $controls = $this->clearData($controls);

        if ($errors = $validator->validate($controls)) {
            return $this->getResponseError($errors);
        }


        if ( ! $this->modAdmin->tableModules->isUniqueName($controls['name'], $module_id)) {
            throw new HttpException(400, $this->_("Модуль с таким идентификатором уже существует"));
        }

        $controls['seq'] = 1 + $this->modAdmin->tableModules->getMaxSeq();


        $this->saveData($this->modAdmin->tableModules, $controls, $module_id);

        return $this->getResponseSuccess([
            'id' => $module_id
        ]);
    }


    /**
     * Сохранение раздела модуля
     * @param int      $module_id
     * @param int|null $section_id
     * @return Response
     * @throws Exception
     * @throws HttpException
     */
    public function saveModuleSection(int $module_id, int $section_id = null): Response {

        $this->checkHttpMethod('put');
        $this->checkVersion($this->modAdmin->tableModulesSections, $section_id);

        $validator = new Validator([
            'title'       => ['string(0-255)',   $this->_('Название')],
            'name'        => ['string(0-255)',   $this->_('Идентификатор')],
            'is_active'   => ['int',             $this->_('Активен')],
        ]);

        $controls = $this->request->getJsonContent() ?? [];
        $controls = $this->clearData($controls);

        if ($errors = $validator->validate($controls)) {
            return $this->getResponseError($errors);
        }

        if (empty($section_id)) {
            $controls['module_id'] = $module_id;
        }


        $section = $this->saveData($this->modAdmin->tableModulesSections, $controls, $section_id);

        return $this->getResponseSuccess([
            'id' => $section->id
        ]);
    }


    /**
     * Сохранение репозиториев
     * @return Response
     * @throws HttpException
     * @throws Exception
     */
    public function saveRepo(): Response {

        $this->checkHttpMethod('put');

        $validator = new Validator([
            'repo' => ['req,string(0-10000)', $this->_('Репозитории')],
        ]);

        $controls = $this->request->getJsonContent() ?? [];
        $controls = $this->clearData($controls);

        if ($errors = $validator->validate($controls)) {
            return $this->getResponseError($errors);
        }

        $controls['repo'] = str_replace(["\t", "\n\n"], [' ', "\n"], $controls['repo']);
        $controls['repo'] = preg_replace("~[ ]{2,}~", ' ', $controls['repo']);


        $setting_repo = $this->modAdmin->tableSettings->getRowByCodeModule('modules_repo', 'admin');

        if (empty($setting_repo)) {
            $this->modAdmin->tableSettings->insert([
                'module'        => 'admin',
                'code'          => 'modules_repo',
                'title'         => $this->_('Репозитории'),
                'value'         => $controls['repo'],
                'is_hidden'     => 1,
                'field_type'    => 'textarea',
                'author_modify' => $this->auth->getUserLogin(),
            ]);

        } else {
            $setting_repo->value         = $controls['repo'];
            $setting_repo->author_modify = $this->auth->getUserLogin();
            $setting_repo->save();
        }

        return $this->getResponse()->setHttpCode(204);
    }


    /**
     * Обновление репозиториев
     * @return void
     */
    public function upgradeRepo(): void {

        header( 'Content-type: text/html; charset=utf-8' );
        if (ob_get_level()) {
            ob_end_clean();
        }

        ob_implicit_flush(true);

        /**
         * @param string $line
         * @return void
         */
        function sendLine(string $line): void {
            echo "{$line}\n";
            ob_flush();
            flush();
        }

        // TODo обновление репозиториев

        for ($i = 0; $i < 30; $i++) {
            sendLine("Current Response is {$i}");
            usleep(100000);
        }

        sendLine("<span class=\"text-success\">{$this->_('Готово')}</span>");
    }


    /**
     * Ручное добавление модуля
     * @return Response
     * @throws HttpException
     * @throws Exception
     */
    public function saveHand(): Response {

        $this->checkHttpMethod('post');

        $validator = new Validator([
            'title'            => ['req,string(1-255)',                       $this->_('Название')],
            'name'             => ['req,string(1-255),chars(alphanumeric|_)', $this->_('Идентификатор')],
            'vendor'           => ['string(0-255),chars(alphanumeric|_)',     $this->_('Издатель')],
            'icon'             => ['string(0-255)',                           $this->_('Иконка')],
            'version'          => ['req,string(1-100)',                       $this->_('Версия')],
            'description'      => ['string(0-65000)',                         $this->_('Описание')],
            'is_active'        => ['int(0-1)',                                $this->_('Активен')],
            'is_visible'       => ['int(0-1)',                                $this->_('Видимый')],
            'is_visible_index' => ['int(0-1)',                                $this->_('Имеет главную страницу')],
        ]);


        $controls = $this->request->getJsonContent() ?? [];
        $controls = $this->clearData($controls);

        if ($errors = $validator->validate($controls)) {
            return $this->getResponseError($errors);
        }


        if ( ! $this->modAdmin->tableModules->isUniqueName($controls['name'])) {
            throw new HttpException(400, $this->_("Модуль с таким идентификатором уже существует"));
        }

        $controls['seq'] = 1 + $this->modAdmin->tableModules->getMaxSeq();


        $row = $this->saveData($this->modAdmin->tableModules, $controls);

        return $this->getResponseSuccess([
            'id' => $row->id
        ]);
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
                require_once('admin/Modules_Install.php');

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