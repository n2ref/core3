<?php
namespace Core3\Mod\Admin;
use Core3\Classes\Response;
use Core3\Mod\Admin\Classes\Modules;
use Core3\Mod\Admin\Classes\Users;
use Core3\Classes\Common;
use Core3\Classes\Request;
use Core3\Classes\Router;
use Core3\Exceptions\AppException;
use Core3\Exceptions\DbException;
use CoreUI\Panel\Control;
use Monolog\Handler\MissingExtensionException;


require_once 'Classes/autoload.php';


/**
 * @property Tables\Modules         $tableModules
 * @property Tables\ModulesSections $tableModulesSections
 * @property Tables\Roles           $tableRoles
 * @property Tables\Users           $tableUsers
 * @property Tables\UsersData       $tableUsersData
 * @property Tables\UsersFiles      $tableUsersFiles
 * @property Tables\UsersSession    $tableUsersSession
 * @property Tables\Controls        $tableControls
 * @property Models\Users           $modelUsers
 */
class Controller extends Common {

    /**
     * @param Request $request
     * @return array
     * @throws DbException
     * @throws \Exception
     */
    public function sectionIndex(Request $request): array {

        $service_info = (new Classes\Index\Model())->getServerInfo();
        $view         = new Classes\Index\View();


        $content = [
            $this->getCssModule('admin', 'assets/index/css/admin.css'),
            $this->getJsModule('admin', 'assets/index/js/admin.index.js'),
        ];

        $panel_admin = new \CoreUI\Panel();
        $panel_admin->setTitle('Общие сведения');
        $panel_admin->addControls([
            (new Control\Button('<i class="bi bi-arrow-clockwise"></i>'))->setOnClick('Core.menu.reload()')
        ]);
        $panel_admin->setContent($view->getTableCommon($service_info));
        $content[] = $panel_admin->toArray();




        $layout_sys = new \CoreUI\Layout();
        $layout_sys->setJustify($layout_sys::JUSTIFY_AROUND);
        $layout_sys->setDirection($layout_sys::DIRECTION_ROW);
        $layout_sys->addItems()->setWidth(200)->setContent($view->getChartCpu($service_info));
        $layout_sys->addItems()->setWidth(200)->setContent($view->getChartMem($service_info));
        $layout_sys->addItems()->setWidth(200)->setContent($view->getChartSwap($service_info));
        $layout_sys->addItems()->setWidth(200)->setContent($view->getChartDisks($service_info));

        $panel_system = new \CoreUI\Panel();
        $panel_system->setTitle('Системная информация');
        $panel_system->addControls([
            (new Control\Button('<i class="bi bi-list-ul"></i>'))->setOnClick('adminIndex.showSystemProcessList()')
        ]);
        $panel_system->setContent([
            $layout_sys->toArray(),
            '<br><br>',
            $view->getTableSystem($service_info)
        ]);

        $content[] = $panel_system->toArray();




        $panel_php = new \CoreUI\Panel();
        $panel_php->setTitle('Php');
        $panel_php->addControls([
            (new Control\Button('<i class="bi bi-info"></i>'))->setOnClick('adminIndex.showPhpInfo()')
        ]);
        $panel_php->setContent($view->getPhp());


        $panel_db = new \CoreUI\Panel();
        $panel_db->setTitle('База данных');
        $panel_db->addControls([
            (new Control\Button('<i class="bi bi-info"></i>'))->setOnClick('adminIndex.showDbVariablesList()'),
            (new Control\Button('<i class="bi bi-plugin"></i>'))->setOnClick('adminIndex.showDbProcessList()')
        ]);

        $panel_db->setContent($view->getDbInfo($service_info));



        $layout_php_db = new \CoreUI\Layout();
        $item = $layout_php_db->addItems();
        $item->setWidthColumn(12);
        $item->addSize('lg')->setWidthColumn(6);
        $item->setContent($panel_php->toArray());

        $item = $layout_php_db->addItems();
        $item->setWidthColumn(12);
        $item->addSize('lg')->setWidthColumn(6);
        $item->setContent($panel_db->toArray());

        $content[] = $layout_php_db->toArray();




        $panel_disks = new \CoreUI\Panel();
        $panel_disks->setTitle('Использование дисков');
        $panel_disks->setContent($view->getTableDisks($service_info));
        $content[] = $panel_disks->toArray();


        $panel_network = new \CoreUI\Panel();
        $panel_network->setTitle('Сеть');
        $panel_network->setContent($view->getTableNetworks($service_info));
        $content[] = $panel_network->toArray();


        $layout = new \CoreUI\Layout();
        $layout->addSize('sm')->setJustify($layout::JUSTIFY_START);
        $layout->addSize('md')->setJustify($layout::JUSTIFY_CENTER);
        $layout->addItems()
            ->setWidth(1024)
            ->setWidthMax('100%')
            ->setWidthMin(400)
            ->setContent($content);

        return $layout->toArray();
    }


    /**
     * Модули
     * @param Request $request
     * @return array|Response
     * @throws MissingExtensionException
     */
    public function sectionModules(Request $request): array|Response {

        try {
            $router = new Router();
            $router->route('/admin/modules')                  ->get([Modules\Handler::class, 'getModules']);
            $router->route('/admin/modules/installed')        ->get([Modules\Handler::class, 'getModules']);
            $router->route('/admin/modules/installed/content')->get([Modules\Handler::class, 'getInstalledContent']);
            $router->route('/admin/modules/installed/table')  ->get([Modules\Handler::class, 'getInstalledTable']);

            $router->route('/admin/modules/installed/hand')
                ->get([Modules\Handler::class, 'getInstalled'])
                ->post([Modules\Handler::class, 'saveInstalledHand']);

            $router->route('/admin/modules/installed/file')
                ->get([Modules\Handler::class, 'getInstalledFile'])
                ->post([Modules\Handler::class, 'saveInstalledFile']);

            $router->route('/admin/modules/installed/link')
                ->get([Modules\Handler::class, 'getInstalledLink'])
                ->post([Modules\Handler::class, 'saveInstalledLink']);

            $router->route('/admin/modules/available')         ->get([Modules\Handler::class, 'getModules']);
            $router->route('/admin/modules/available/content') ->get([Modules\Handler::class, 'getAvailableContent']);
            $router->route('/admin/modules/available/table')   ->get([Modules\Handler::class, 'getAvailableTable']);
            $router->route('/admin/modules/available/{id:\d+}')->get([Modules\Handler::class, 'getAvailableModule']);

            $router->route('/admin/modules/{id:\d+}')
                ->get([Modules\Handler::class, 'getModule'])
                ->post([Modules\Handler::class, 'saveModule'])
                ->delete([Modules\Handler::class, 'deleteModule']);

            $router->route('/admin/modules/{id:\d+}/module')        ->get([Modules\Handler::class, 'getModule']);
            $router->route('/admin/modules/{id:\d+}/module/content')->get([Modules\Handler::class, 'getModuleContent']);

            $router->route('/admin/modules/{id:\d+}/sections')        ->get([Modules\Handler::class, 'getModuleSections']);
            $router->route('/admin/modules/{id:\d+}/sections/content')->get([Modules\Handler::class, 'getModuleSectionsContent']);
            $router->route('/admin/modules/{id:\d+}/sections/table')
                ->get([Modules\Handler::class, 'getModuleSections'])
                ->delete([Modules\Handler::class, 'deleteModuleSections']);

            $router->route('/admin/modules/{id:\d+}/sections/{section_id:\d+}')
                ->get([Modules\Handler::class, 'getModuleSections'])
                ->post([Modules\Handler::class, 'saveModuleSections']);
            $router->route('/admin/modules/{id:\d+}/sections/{section_id:\d+}/content')->get([Modules\Handler::class, 'getModuleSectionsContent']);

            $router->route('/admin/modules/{id:\d+}/versions')        ->get([Modules\Handler::class, 'getModuleVersions']);
            $router->route('/admin/modules/{id:\d+}/versions/content')->get([Modules\Handler::class, 'getModuleVersionsContent']);

            $route_method = $router->getRouteMethod($_SERVER['REQUEST_METHOD'], $request->getUri());

            if ( ! $route_method) {
                $response = new Response();
                $response->setHttpCode(404);
                return $response;
            }


            $route_method->prependParam($request);

            try {
                return $route_method->run();

            } catch (AppException $e) {
                $this->log->info($e->getMessage());
                return \CoreUI\Info::danger($e->getMessage(), $this->_('Ошибка'));

            } catch (\Exception $e) {
                $this->log->error($this->resource, $e);

                return \CoreUI\Info::danger(
                    $this->config?->system->debug?->on
                        ? $e->getMessage()
                        : $this->_('Обновите страницу или попробуйте позже'),
                    $this->_('Ошибка')
                );
            }


            $base_url    = "#/admin/modules";
            $handler_url = "admin/modules/handler";
            $result      = [];
            $panel       = new \CoreUI\Panel('module');



            if ($params = $request->getPathParams('^/admin/modules/{module_id:\d+}')) {

                $breadcrumb = new \CoreUI\Breadcrumb();
                $breadcrumb->addItem('Модули', $base_url);
                $breadcrumb->addItem('Установленные', "{$base_url}/installed");
                $breadcrumb->addItem('Модуль');
                $result[] = $breadcrumb->toArray();


                if ($params['module_id']) {
                    $module = $this->tableModules->getRowById($params['module_id']);

                    if (empty($module)) {
                        throw new AppException($this->_('Указанный модуль не найден'));
                    }

                    $sections_count = $this->tableModulesSections->getCountByModuleId($module->id);

                    $panel->setTitle($module->title, "{$module->name} / {$module->version}");
                    $panel->setContentFit($panel::FIT);
                    $panel->addControls([
                        (new Control\Button('<i class="bi bi-arrow-clockwise"></i> ' . $this->_('Проверить обновления')))->setAttr('class', 'btn btn-outline-secondary'),
                        (new Control\Button('<i class="bi bi-trash"></i> ' . $this->_('Удалить')))->setAttr('class', 'btn btn-outline-danger'),
                    ]);

                    $panel->addTab($this->_("Модуль"), 'module')->setUrlWindow("{$base_url}/{$module->id}/module")->setUrlContent("{$handler_url}/getFormModule?id={$module->id}");
                    $panel->addTab($this->_("Разделы"), 'sections')->setUrlWindow("{$base_url}/{$module->id}/sections")->setUrlContent("{$handler_url}/getTabSections?id={$module->id}")->setCount($sections_count);
                    $panel->addTab($this->_("Версии"), 'versions')->setUrlWindow("{$base_url}/{$module->id}/versions")->setUrlContent("{$handler_url}/getTabVersion?id={$module->id}")->setCount($sections_count);

                    if ($module->isset_updates) {
                        $panel->getTabById('versions')->setBadgeDot('danger');
                    }

                    $params = $request->getPathParams('^/admin/modules/{module_id:\d+}/{tab}');
                    $tab    = $params['tab'] ?? 'module';
                    $panel->setActiveTab($tab);

                    if ($tab === 'sections' && $section_params = $request->getPathParams('^/admin/modules/{module_id:\d+}/sections/{section_id:\d+}')) {
                        $panel->setUrlContent($panel->getTabById('sections')->getUrlContent() . "&section_id={$section_params['section_id']}");
                    } else {
                        $panel->setUrlContent($panel->getTabById($tab)->getUrlContent());
                    }


                } else {
                    $panel->setTitle($this->_('Добавление модуля'));
                    $panel->addTab($this->_("Ручная установка"), 'hand')->setUrlWindow("{$base_url}/0/hand")->setUrlContent("{$handler_url}/getFormInstallHand");
                    $panel->addTab($this->_("Из файла"), 'file')->setUrlWindow("{$base_url}/0/file")->setUrlContent("{$handler_url}/getFormInstallFile");
                    $panel->addTab($this->_("По ссылке"), 'link')->setUrlWindow("{$base_url}/0/link")->setUrlContent("{$handler_url}/getFormInstallLink");


                    $params = $request->getPathParams('^/admin/modules/0/{tab}$');
                    $tab    = $params['tab'] ?? 'hand';

                    $panel->setActiveTab($tab);
                    $panel->setUrlContent($panel->getTabById($tab)->getUrlContent());
                }

            } elseif ($params = $request->getPathParams('^/admin/modules/available/{module_id:\d+}')) {

            } else {
                $count_modules = $this->tableModules->getCount();

                $panel->addTab($this->_("Установленные"), 'installed')->setUrlWindow("{$base_url}/installed")->setUrlContent("{$handler_url}/getTableInstalled")->setCount($count_modules);
                $panel->addTab($this->_("Доступные"),     'available')->setUrlWindow("{$base_url}/available")->setUrlContent("{$handler_url}/getTableAvailable");

                $params = $request->getPathParams('^/admin/modules/{tab}$');
                $tab    = $params['tab'] ?? 'installed';

                $panel->setActiveTab($tab);
                $panel->setUrlContent($panel->getTabById($tab)->getUrlContent());
            }

        } catch (AppException $e) {
            $this->log->info($e->getMessage());
            return [\CoreUI\Info::danger($e->getMessage(), $this->_('Ошибка'))];

        } catch (\Exception $e) {
            $this->log->error($this->resource, $e);
            return [
                \CoreUI\Info::danger(
                    $this->config?->system->debug?->on ? $e->getMessage() : $this->_('Обновите страницу или попробуйте позже'),
                    $this->_('Ошибка')
                )
            ];
        }
    }


    /**
     * Справочник пользователей системы
     * @param Request $request
     * @return array|Response
     * @throws MissingExtensionException
     * @throws \Exception
     */
    public function sectionUsers(Request $request): array|Response {

        $router = new Router();
        $router->route('/admin/users')->get([Users\Handler::class, 'getUsers']);
        $router->route('/admin/users/table')
            ->get([   Users\Handler::class, 'getUsersTable'])
            ->delete([Users\Handler::class, 'deleteUsersTable']);

        $router->route('/admin/users/login')->post([Users\Handler::class, 'loginUser']);

        $router->route('/admin/users/{id:\d+}')
            ->get([  Users\Handler::class, 'getUser' ])
            ->post([ Users\Handler::class, 'saveUser' ])
            ->put([  Users\Handler::class, 'saveUserNew' ]);

        $router->route('/admin/users/{id:\d+}/switch')         ->patch([ Users\Handler::class, 'switchUserActive' ]);
        $router->route('/admin/users/{id:\d+}/avatar/preview') ->get([   Users\Handler::class, 'getFilePreview' ]);
        $router->route('/admin/users/{id:\d+}/avatar/download')->get([   Users\Handler::class, 'getFileDownload' ]);

        $route_method = $router->getRouteMethod($_SERVER['REQUEST_METHOD'], $request->getUri());

        if ( ! $route_method) {
            $response = new Response();
            $response->setHttpCode(404);
            return $response;
        }

        $route_method->prependParam($request);


        try {
            return $route_method->run();

        } catch (AppException $e) {
            $this->log->info($e->getMessage());
            return \CoreUI\Info::danger($e->getMessage(), $this->_('Ошибка'));

        } catch (\Exception $e) {
            $this->log->error($this->resource, $e);

            return \CoreUI\Info::danger(
                $this->config?->system->debug?->on
                    ? $e->getMessage()
                    : $this->_('Обновите страницу или попробуйте позже'),
                $this->_('Ошибка')
            );
        }
    }


    /**
     * Конфигурация
     * @return string
     */
    public function sectionSettings (): string {

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