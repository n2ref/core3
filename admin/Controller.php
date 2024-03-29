<?php
namespace Core3\Mod\Admin;
use \Core3\Classes\Common;
use \Core3\Classes\Http\Request;
use \Core3\Classes\Http\Router;
use \Core3\Exceptions\AppException;

require_once 'Classes/autoload.php';


/**
 * @property Tables\Modules         $tableModules
 * @property Tables\ModulesSections $tableModulesSections
 * @property Tables\Roles           $tableRoles
 * @property Tables\Users           $tableUsers
 * @property Tables\UsersSession    $tableUsersSession
 * @property Tables\Controls        $tableControls
 * @property Model\Users            $modelUsers
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

        $service_info = (new Classes\Index\Model())->getServerInfo();
        $view         = new Classes\Index\View();


        $content = [
            $this->getCssModule('admin', 'assets/index/css/admin.css'),
            $this->getJsModule('admin', 'assets/index/js/admin.index.js'),
        ];

        $panel_admin = new \CoreUI\Panel();
        $panel_admin->setTitle('Общие сведения');
        $panel_admin->setControls('<button class="btn btn-outline-secondary" onclick="Core.menu.reload()"><i class="bi bi-arrow-clockwise"></i></button>');
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
        $item->addSize('lg');
        $item->widthColumn(6);
        $item->content($panel_php->toArray());

        $item = $layout->addItems();
        $item->widthColumn(12);
        $item->addSize('lg');
        $item->widthColumn(6);
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
                    $module = $this->tableModules->getRowById($request->getQuery('edit'));

                    if (empty($module)) {
                        throw new AppException($this->_('Указанный модуль не найден'));
                    }

                    $panel->setTitle($module->title, $this->_('Редактирование модуля'), $base_url);

                } else {
                    $panel->setTitle($this->_('Добавление модуля'));
                }

            } else {
                $count_modules = $this->tableModules->getCount();

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
     * @throws AppException
     */
    public function sectionUsers(Request $request): array {

        $base_url = "#/admin/users";

        $panel = new \CoreUI\Panel();
        $view  = new Classes\Users\View();

        $content   = [];
        $content[] = $this->getJsModule('admin', 'assets/users/js/admin.users.js');

        try {
            if ($request->getQuery('edit') !== null) {
                if ($request->getQuery('edit')) {
                    $user = $this->tableUsers->getRowById((int)$request->getQuery('edit'));

                    if (empty($user)) {
                        throw new AppException('Указанный пользователь не найден');
                    }

                    $name = trim("{$user->lname} {$user->fname} {$user->mname}");

                    $panel->setTitle($name ?: $user->login, $this->_('Редактирование пользователя'), $base_url);
                    $content[] = $view->getForm($base_url, $user);

                } else {
                    $panel->setTitle($this->_('Добавление пользователя'), null, $base_url);
                    $content[] = $view->getFormNew($base_url);
                }

            } else {
                $content[] = $view->getTable($base_url);
            }

            $panel->setContent($content);

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
     * @return array
     * @throws \Exception|\Laminas\Cache\Exception\ExceptionInterface
     */
    public function sectionRoles(): array {

        $panel = new \CoreUI\Panel();
        $panel->setTitle($this->_("Роли и доступ"));

        if ( ! empty($_GET['edit'])) {

        } else {

        }

        $panel->setContent('');

        $job_id = $this->startWorkerJob('jobName', ['22222']);

        echo '<pre>';
        print_r($this->worker->getInfo());
        echo '</pre>';


//        $this->worker->startJob('admin', 'jobName',  ['param1 data']);
       // $this->worker->stop();
        sleep(1);
        echo '<pre>';
        print_r($this->worker->getJobInfo($job_id));
        echo '</pre>';
//        $this->worker->restart();

        return $panel->toArray();
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
}