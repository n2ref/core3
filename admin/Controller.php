<?php
namespace Core3\Mod\Admin;
use \Core3\Classes\Common;
use \Core3\Classes\Http\Request;
use \Core3\Classes\Http\Router;
use \Core3\Exceptions\AppException;

require_once 'Classes/autoload.php';


/**
 * @property tables\Modules         $tableModules
 * @property tables\ModulesSections $tableModulesSections
 * @property tables\Roles           $tableRoles
 * @property tables\Users           $tableUsers
 * @property tables\UsersSession    $tableUsersSession
 * @property tables\Enum            $tableEnum
 */
class Controller extends Common {

    /**
     * @param Request $request
     * @return array|string|string[]
     * @throws \Core3\Exceptions\DbException
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     * @throws \Exception
     */
    public function sectionIndex(Request $request) {

        $router = new Router();
        $router->addPath('/cache')->delete('cache_clean');
        $router->addPath('/php_info')->get('get_php_info');
        $router->addPath('/db_connections')->get('get_db_connections');
        $router->addPath('/db_variables')->get('get_db_variables');
        $router->addPath('/system_process_list')->get('get_system_process_list');

        $route = $router->getRoute($request->getMethod(), $request->getPathParam('mod_query'));


        if ($route) {
            switch ($route['action']) {
                case 'cache_clean':
                    $this->cache->clear();
                    return [ 'status' => 'success' ];
                    break;

                case 'get_php_info':
                    return (new Classes\Index\SysInfo\PhpInfo())->getPhpinfo();
                    break;

                case 'get_db_connections':
                    return (new Classes\Index\View())->getTableDbConnections();
                    break;

                case 'get_db_variables':
                    return (new Classes\Index\View())->getTableDbVariables();
                    break;

                case 'get_system_process_list':
                    return (new Classes\Index\View())->getTableProcessList();
                    break;
            }
        }

        $service_info = (new Classes\Index\Model())->getServerInfo();
        $view         = new Classes\Index\View();



        $content = [
            $this->getCssModule('admin', 'assets/index/css/admin.css'),
            $this->getJsModule('admin', 'assets/index/js/admin.index.js'),
        ];

        $panel_admin = new \CoreUI\Panel();
        $panel_admin->setTitle('Общие сведения');
        $panel_admin->setControls('<button class="btn btn-outline-secondary" onclick="coreMenu.reload()"><i class="bi bi-arrow-clockwise"></i></button>');
        $panel_admin->setContent($view->getTableCommon($service_info));
        $content[] = $panel_admin->toArray();




        $layout = new \CoreUI\Layout();
        $layout->justify($layout::JUSTIFY_AROUND);
        $layout->direction($layout::DIRECTION_ROW);
        $layout->addItems()->width(200)->content($view->getChartCpu($service_info));
        $layout->addItems()->width(200)->content($view->getChartMem($service_info));
        $layout->addItems()->width(200)->content($view->getChartSwap($service_info));
        $layout->addItems()->width(200)->content($view->getChartDisks($service_info));

        $panel_system = new \CoreUI\Panel();
        $panel_system->setTitle('Системная информация');
        $panel_system->setControls('<button class="btn btn-outline-secondary" onclick="adminIndex.showSystemProcessList()"><i class="bi bi-list-ul"></i></button>');
        $panel_system->setContent([
            $layout->toArray(),
            '<br><br>',
            $view->getTableSystem($service_info)
        ]);
        $content[] = $panel_system->toArray();






        $panel_php = new \CoreUI\Panel();
        $panel_php->setTitle('Php');
        $panel_php->setControls('<button class="btn btn-outline-secondary" onclick="adminIndex.showPhpInfo()"><i class="bi bi-info"></i></button>');
        $panel_php->setContent($view->getPhp());


        $panel_db = new \CoreUI\Panel();
        $panel_db->setTitle('База данных');
        $panel_db->setControls('
            <button class="btn btn-outline-secondary" onclick="adminIndex.showDbVariablesList()"><i class="bi bi-info"></i></button>
            <button class="btn btn-outline-secondary" onclick="adminIndex.showDbProcessList()"><i class="bi bi-plugin"></i></button>
        ');
        $panel_db->setContent($view->getDbInfo($service_info));



        $layout = new \CoreUI\Layout();
        $item = $layout->addItems();
        $item->widthColumn(12);
        $item->addSize('lg')->widthColumn(6);

        $item->content($panel_php->toArray());

        $item = $layout->addItems();
        $item->widthColumn(12);
        $item->addSize('lg')->widthColumn(6);
        $item->content($panel_db->toArray());

        $content[] = $layout->toArray();




        $panel_disks = new \CoreUI\Panel();
        $panel_disks->setTitle('Использование дисков');
        $panel_disks->setContent($view->getTableDisks($service_info));
        $content[] = $panel_disks->toArray();


        $panel_network = new \CoreUI\Panel();
        $panel_network->setTitle('Сеть');
        $panel_network->setContent($view->getTableNetworks($service_info));
        $content[] = $panel_network->toArray();


        $layout = new \CoreUI\Layout();
        $layout->addSize('sm')->justify($layout::JUSTIFY_START);
        $layout->addSize('md')->justify($layout::JUSTIFY_CENTER);
        $layout->addItems()
            ->width(1024)
            ->maxWidth('100%')
            ->minWidth(400)
            ->content($content);

        return $layout->toArray();
    }


    /**
     * Модули
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function sectionModules(Request $request): array {

        $base_url = "#/admin/modules";
        $panel    = new \CoreUI\Panel('tab');

        try {
            if ($request->getQuery('edit') !== null) {
                if ($request->getQuery('edit')) {
                    $module = $this->modAdmin->tableModules->getRowById($request->getQuery('edit'));

                    if (empty($module)) {
                        throw new AppException($this->_('Указанный модуль не найден'));
                    }

                    $panel->setTitle($module->title, $this->_('Редактирование модуля'), $base_url);

                } else {
                    $panel->setTitle($this->_('Добавление модуля'));
                }

            } else {
                $count_modules = $this->modAdmin->tableModules->getCount();

                $panel->addTab($this->_("Установленные (%s)", [ $count_modules ]), 'installed', "{$base_url}?tab=installed");
                $panel->addTab($this->_("Доступные"),                              'available', "{$base_url}?tab=available");

                $tab = $request->getQuery('tab') ?? 'installed';

                $view = new Classes\Modules\View();

                $panel->setActiveTab($tab);
                switch ($tab) {
                    case 'installed': $panel->setContent($view->getTableInstalled($base_url)); break;
                    case 'available': $panel->setContent($view->getTableAvailable($base_url)); break;
                }
            }

        } catch (AppException $e) {
            $panel->setContent(
                \CoreUI\Info::danger($e->getMessage(), $this->_('Ошибка'))
            );
        }

        return $panel->toArray();
    }


    /**
     * Справочник пользователей системы
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function sectionUsers(Request $request): array {

        $base_url = "#/admin/users";

        $panel = new \CoreUI\Panel();
        $view  = new Classes\Users\View();

        try {
            if ($request->getQuery('edit') !== null) {
                if ($request->getQuery('edit')) {
                    $user = $this->modAdmin->tableUsers->getRowById($request->getQuery('edit'));

                    if (empty($user)) {
                        throw new AppException('Указанный пользователь не найден');
                    }

                    $panel->setTitle("{$user->lname} {$user->fname} {$user->mname}", $this->_('Редактирование пользователя'), $base_url);

                } else {
                    $panel->setTitle($this->_('Добавление пользователя'));
                }

            } else {
                $panel->setContent($view->getTable($base_url));
            }

        } catch (AppException $e) {
            $panel->setContent(
                \CoreUI\Info::danger($e->getMessage(), $this->_('Ошибка'))
            );
        }

        return $panel->toArray();
    }


    /**
     * Конфигурация
     * @return string
     */
    public function sectionSettings () {

        ob_start();
        $url = "/admin/settings";
        $settings = new Settings();

        $panel = new Panel('settings');
        $panel->addTab($this->_("Настройки системы"),        'system',   $url);
        $panel->addTab($this->_("Дополнительные параметры"), 'extra',    $url);
        $panel->addTab($this->_("Персональные параметры"),   'personal', $url);

        $url .= '?settings=' . $panel->getActiveTab();
        switch ($panel->getActiveTab()) {
            case 'system':
                echo $settings->getFormSystem($url, empty($_GET['edit']));
                break;

            case 'extra':
                if (isset($_GET['code'])) {
                    echo $settings->getFormExtra($url, $_GET['code']);
                }
                echo $settings->getTableExtra($url);
                break;

            case 'personal':
                if (isset($_GET['code'])) {
                    echo $settings->getFormPersonal($url, $_GET['code']);
                }
                echo $settings->getTablePersonal($url);
                break;
        }

        $panel->addContent(ob_get_clean());
        return $panel->render();
    }


    /**
     * Роли и доступ
     * @return string
     */
    public function sectionRoles() {

        ob_start();
        $app   = "index.php?module=admin&action=roles";
        $roles = new Roles();
        $panel = new Panel('roles');
        $panel->setTitle($this->_("Роли и доступ"));

        if ( ! empty($_GET['edit'])) {

        } else {

        }

        $panel->addContent(ob_get_clean());
        return $panel->render();
    }



    /**
     * Справочники
     * @return string
     */
    public function sectionEnum() {

        // TODO модифицировать роутер под новую схему с ресурсами /module/section/resource
        // TODO перенести ресурсы секции справочники в свой новый класс и протестировать их работу

        // TODO переделать формирование панелей на js
        // TODO переделать формирование алетов на js
        // TODO переделать формирование таблицы на js
        // TODO переделать формирование форм на js
        // TODO переделать формирование табов на js
        // TODO переделать формирование дерева на js
        // TODO переделать формирование макета на js

        // TODO сделать Модули
        // TODO сделать Мониторинг
        // TODO сделать Справочники
        // TODO сделать Пользователи
        // TODO сделать Роли

        // TODO проверка acl должна происходить по GET параметрам module и action
        // TODO в мобильной верстке при открытии субмодулей они должны налаживаться на модули
        // TODO возможно формы должны быть материальные


        $panel = new Panel('enum');

        // Редактирование справочника
        if (isset($_GET['edit'])) {
            $panel->addContent($this->getModuleTable('admin', 'enum')->formEnum());

            if ($_GET['edit']) {
                $name = $this->db->fetchOne("
                    SELECT name
                    FROM core_enum
                    WHERE id = ?
                ", $_GET['edit']);
                $panel->setTitle($this->_("Редактирование справочника"));

                $panel_value = new Panel('enum_value');
                $panel_value->setTitle(sprintf($this->_("Перечень значений справочника \"%s\""), $name));

                // Редактирование значения
                if ($_GET['edit_value']) {
                    $panel_value->addContent($this->getModuleTable('admin', 'enum')->formValue());
                }

                // Таблица значений
                $panel_value->addContent($this->getModuleTable('admin', 'enum')->tableValues());
                $panel->addContent($panel_value->render());

            } else {
                $panel->setTitle($this->_("Создание нового справочника"));
            }


        // Таблица справочников
        } else {
            $panel->setTitle($this->_("Справочники"));
            $panel->addContent($this->getModuleTable('admin', 'enum')->tableEnums());
        }

        $this->printJs(DOC_PATH . "core3/mod/admin/enum.js");
        return $panel->render();
    }


    /**
     * @throws \Exception
     * @return void
     */
    public function sectionMonitoring() {
        try {
            $app = "index.php?module=admin&action=monitoring&loc=core";
            require_once $this->path . 'monitoring.php';
        } catch (\Exception $e) {
            Alert::danger($e->getMessage());
        }
    }


    /**
     * @return string
     */
    public function sectionAudit() {

        ob_start();
        $app   = "index.php#module=admin&action=audit";
        $panel = new Panel('audit');
        $panel->addTab($this->_("База данных"),          'database',  $app);
        $panel->addTab($this->_("Контроль целостности"), 'integrity', $app);

        switch ($panel->getActiveTab()) {
            case 'database':
                $db_array_file = __DIR__ . "/classes/Audit/db_array.php";
                if ( ! file_exists($db_array_file)) {
                    echo Alert::danger(sprintf($this->_("Не найден файл %s"), $db_array_file));

                } else {
                    $DB_ARRAY = array();
                    require_once $db_array_file;
                    $db_master = new DBMaster($this->config);
                    $a_result  = $db_master->checkCurrentDB($DB_ARRAY);
                    $auditNamespace = new \Zend_Session_Namespace('Audit');
                    // Выполнение обновления
                    if (isset($_GET['db_update_one']) && $_GET['db_update_one'] == 1) {
                        if ($a_result['COM'] > 0 && is_array($auditNamespace->RES)) {
                            $a_tmp = explode('<!--NEW_LINE_FOR_DB_CORRECT_SCRIPT-->', $auditNamespace->RES['SQL'][$_GET['number']]);
                            if ($a_tmp != '') {
                                $db_master->exeSQL($a_tmp[0]);
                            }
                            $a_result = $db_master->checkCurrentDB($DB_ARRAY);
                        }
                    }

                    $auditNamespace->RES = $a_result;

                    // Выполнение всех обновлений
                    if (isset($_GET['db_update']) && $_GET['db_update'] == 1) {
                        if ($a_result['COM'] > 0) {
                            while (list($key, $module) = each($a_result['COM'])) {
                                $a_tmp = explode('<!--NEW_LINE_FOR_DB_CORRECT_SCRIPT-->', $a_result['SQL'][$key]);
                                while (list($k, $v) = each($a_tmp)) {
                                    if ($v != '') {
                                        $db_master->exeSQL($v);
                                    }
                                }
                            }
                            $a_result = $db_master->checkCurrentDB($DB_ARRAY);
                        }
                    }


                    // Ошибки
                    if ( ! empty($a_result['COM'])) {
                        $repair     = $this->_("Исправить");
                        $repair_all = $this->_("Исправить все");

                        foreach ($a_result['COM'] as $key => $error_title) {
                            $sql        = "<i>{$a_result['SQL'][$key]}</i> ";
                            $link       = "index.php?module=admin&action=audit&db_update_one=1&number={$key}";
                            $btn_repair = "<a href=\"javascript:load('{$link}')\">{$repair}</a>";

                            echo Alert::danger($sql . $btn_repair, $error_title);
                        }


                        echo "<input class=\"btn btn-warning\" type=\"button\" value=\"{$repair_all}\" 
                                     onclick=\"load('index.php?module=admin&action=audit&db_update=1')\"/>";

                        if ( ! empty($a_result['WARNING'])) {
                            echo "<h3>" .  $this->_("Предупреждения") . ":</h3>";
                            foreach ($a_result['WARNING'] as $warning) {
                                echo $warning, '<br>';
                            }
                        }
                    } else {
                        echo Alert::success($this->_("Структура базы данных в норме."));
                    }
                }
                break;

            case 'integrity':
                $install = new Modules_Install();

                try {
                    $is_ok       = true;
                    $server      = $this->config->system->host;
                    $admin_email = $this->getSetting('admin_email');
                    $modules     = $this->db->fetchAll("
                        SELECT name, 
                               title 
                        FROM core_modules 
                        WHERE files_hash IS NOT NULL
                    ");

                    if ( ! empty($modules)) {
                        foreach ($modules as $module) {
                            $dirhash    = $install->extractHashForFiles($this->getModuleLocation($module['module_id']));
                            $dbhash     = $install->getFilesHashFromDb($module['module_id']);
                            $compare    = $install->compareFilesHash($dirhash, $dbhash);
                            if ( ! empty($compare)) {
                                $is_ok           = false;
                                $module_problems = array();

                                $br = $install->branchesCompareFilesHash($compare);
                                foreach ($br as $type=>$branch) {
                                    foreach ($branch as $n=>$f) {
                                        if ($type != 'lost') {
                                            $file = $this->getModuleLocation($module['module_id']) . "/" . $f;
                                            $date = date("d.m.Y H:i:s", filemtime($file));
                                            $br[$type][$n] = "{$file} (изменён {$date})";
                                        }
                                    }
                                }

                                $n = 0;
                                if ( ! empty($br['added'])) {
                                    $n += count($br['added']);
                                    $module_problems[]= "Добавленные файлы:<br>&nbsp;&nbsp; - " . implode("<br>&nbsp;&nbsp; - ", $br['added']);
                                }
                                if ( ! empty($br['changed'])) {
                                    $n += count($br['changed']);
                                    $module_problems[]= "Измененные файлы:<br>&nbsp;&nbsp; - " . implode("<br>&nbsp;&nbsp; - ", $br['changed']);
                                }
                                if ( ! empty($br['lost'])) {
                                    $n += count($br['lost']);
                                    $module_problems[]= "Удаленные файлы:<br>&nbsp;&nbsp; - " . implode("<br>&nbsp;&nbsp; - ", $br['lost']);
                                }
                                echo Alert::danger(implode("<br>", $module_problems), sprintf($this->_('Обнаружены изменения в модуле "%s"'), $module['m_name']));

                                //отправка уведомления
                                if ($admin_email && $server) {
                                    $is_send = $this->modAdmin->createEmail()
                                        ->to($admin_email)
                                        ->subject(sprintf($this->_("%s: обнаружены изменения в структуре модуля!"), $server))
                                        ->body("<b>{$server}:</b> Обнаружены изменения в структуре модуля {$module['module_id']}. Обнаружено  {$n} несоответствий.")
                                        ->send();
                                    if ( ! $is_send) {
                                        echo Alert::danger("Уведомление на email не отправлено", "Ошибка");
                                    }
                                }
                            }
                        }
                    }

                    if ($is_ok) {
                        echo Alert::success('Файлы всех модулей соответствуют контрольной сумме', 'Проблем не найдено!');
                    }

                } catch (\Exception $e) {
                    echo Alert::danger($e->getMessage(), "Ошибка");
                }
                break;
        }

        $panel->addContent(ob_get_clean());
        return $panel->render();
    }


    /**
     * Проверяем файлы модулей на изменения
     * @return array
     */
    private function getModulesChanges() {

        $modules_list = $this->db->fetchAll("
            SELECT name
            FROM core_modules 
            WHERE files_hash IS NOT NULL
        ");

        $install = new Modules_Install();
        $modules = [];

        if ( ! empty($modules_list)) {
            $modules = [
                'modules' => [],
                'files'   => [],
                'count'   => 0,
            ];
            foreach ($modules_list as $module) {
                $dirhash    = $install->extractHashForFiles($this->getModuleLocation($module['name']));
                $dbhash     = $install->getFilesHashFromDb($module['name']);
                $compare    = $install->compareFilesHash($dirhash, $dbhash);

                $br = $install->branchesCompareFilesHash($compare);
                if ( ! empty($br['added'])) {
                    $modules['count'] += count($br['added']);
                }
                if ( ! empty($br['changed'])) {
                    $modules['count'] += count($br['changed']);
                }
                if ( ! empty($br['lost'])) {
                    $modules['count'] += count($br['lost']);
                }

                if ( ! empty($compare)) {
                    $modules['modules'][] = $module['name'];

                    foreach ($compare as $file => $item) {
                        $modules['files'][] = $file;
                    }
                }

            }
        }

        return $modules;
    }
}