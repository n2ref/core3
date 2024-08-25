<?php
namespace Core3\Mod\Admin;
use \Core3\Classes\Common;
use \Core3\Classes\Init\Request;
use \Core3\Exceptions\AppException;
use Core3\Exceptions\DbException;
use \CoreUI\Panel\Control;

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
        $panel_admin->addControls([
            (new Control\Button('<i class="bi bi-arrow-clockwise"></i>'))->setOnClick('Core.menu.reload()')
        ]);
        $panel_admin->setContent($view->getTableCommon($service_info));
        $content[] = $panel_admin->toArray();




        $layout = new \CoreUI\Layout();
        $layout->setJustify($layout::JUSTIFY_AROUND);
        $layout->setDirection($layout::DIRECTION_ROW);
        $layout->addItems()->setWidth(200)->setContent($view->getChartCpu($service_info));
        $layout->addItems()->setWidth(200)->setContent($view->getChartMem($service_info));
        $layout->addItems()->setWidth(200)->setContent($view->getChartSwap($service_info));
        $layout->addItems()->setWidth(200)->setContent($view->getChartDisks($service_info));

        $panel_system = new \CoreUI\Panel();
        $panel_system->setTitle('Системная информация');
        $panel_system->addControls([
            (new Control\Button('<i class="bi bi-list-ul"></i>'))->setOnClick('adminIndex.showSystemProcessList()')
        ]);

        $panel_system->setContent([
            $layout->toArray(),
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



        $layout = new \CoreUI\Layout();
        $item = $layout->addItems();
        $item->setWidthColumn(12);
        $item->addSize('lg')->setWidthColumn(6);
        $item->setContent($panel_php->toArray());

        $item = $layout->addItems();
        $item->setWidthColumn(12);
        $item->addSize('lg')->setWidthColumn(6);
        $item->setContent($panel_db->toArray());

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
     * @return array
     * @throws \Exception
     */
    public function sectionModules(Request $request): array {

        $base_url = "#/admin/modules";
        $load_url = "core3/mod/admin/modules/handler";
        $panel    = new \CoreUI\Panel('tab');
        $view     = new Classes\Modules\View();
        $result   = [];

        try {
            if ($params = $request->match('^/admin/modules/{module_id}', ['[0-9]+'])) {

                $breadcrumb = new \CoreUI\Breadcrumb();
                $breadcrumb->addItem('Модули',        $base_url);
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
                    $panel->addTab($this->_("Модуль"),  'module',   "{$base_url}/$module->id/module");
                    $panel->addTab($this->_("Разделы"), 'sections', "{$base_url}/$module->id/sections")->setCount($sections_count);
                    $panel->addTab($this->_("Версии"),  'versions', "{$base_url}/$module->id/versions")->setCount($sections_count);

                    if ($module->isset_updates) {
                        $panel->getTabById('versions')->setBadgeDot('danger');
                    }

                    $params = $request->match('^/admin/modules/{id}/{tab}', ['\d+', '\w+']);
                    $tab    = $params['tab'] ?? 'module';
                    $panel->setActiveTab($tab);

                    switch ($tab) {
                        case 'module':
                            $panel->setContent($view->getFormModule($base_url, $module));
                            break;

                        case 'sections':
                            $content = [];

                            if ($section_params = $request->match('^/admin/modules/{module_id}/sections/{section_id}', ['[0-9]+', '[0-9]+'])) {
                                if ($section_params['section_id']) {
                                    $module_section = $this->tableModulesSections->getRowById($section_params['section_id']);

                                    if (empty($module_section)) {
                                        throw new AppException($this->_('Указанный раздел модуля не найден'));
                                    }
                                }

                                $content[] = $view->getFormSection($base_url, $module_section ?? null);
                            }

                            $content[] = $view->getTableSections($base_url);

                            $panel->setContent($content);
                            break;

                        case 'versions':
                            $content = [];
                            $content[] = $view->getFormVersions($base_url, $module_section ?? null);
                            $content[] = $view->getTableSections($base_url);

                            $panel->setContent($content);
                            break;
                    }

                } else {
                    $panel->setTitle($this->_('Добавление модуля'));
                    $panel->addTab($this->_("Ручная установка"), 'hand', "{$base_url}/install/hand");
                    $panel->addTab($this->_("Из файла"),         'file', "{$base_url}/install/file");
                    $panel->addTab($this->_("По ссылке"),        'link', "{$base_url}/install/link");


                    $params = $request->match('^/admin/modules/install/{tab}$', ['\w+']);
                    $tab    = $params['tab'] ?? 'hand';
                    $panel->setActiveTab($tab);
                    switch ($tab) {
                        case 'hand': $panel->setContent($view->getFormModuleNew($base_url)); break;
                        case 'file': $panel->setContent($view->getFormModuleNew($base_url)); break;
                        case 'link': $panel->setContent($view->getFormModuleNew($base_url)); break;
                    }
                }

            } else {
                $count_modules = $this->tableModules->getCount();

                $panel->addTab($this->_("Установленные"), 'installed')->setCount($count_modules)
                    ->setUrlContent("{$load_url}/getTableInstalled")
                    ->setUrlWindow("{$base_url}/installed");

                $panel->addTab($this->_("Доступные"),     'available')
                    ->setUrlContent("{$load_url}/getTableAvailable")
                    ->setUrlWindow("{$base_url}/available");


                //$params = $request->getPathParams('^/admin/modules/{tab}$', ['\w+']);
                $params = $request->match('^/admin/modules/{tab}$', ['\w+']);
                $tab    = $params['tab'] ?? 'installed';
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

        } catch (\Exception $e) {
            $this->log->error('admin_module', $e);
            $panel->setContent(
                \CoreUI\Info::danger(
                    $this->config?->system->debug?->on ? $e->getMessage() : $this->_('Обновите страницу или попробуйте позже'),
                    $this->_('Ошибка')
                )
            );
        }

        $result[] = $panel->toArray();

        return $result;
    }


    /**
     * Справочник пользователей системы
     * @param Request $request
     * @return array
     * @throws AppException
     * @throws DbException
     */
    public function sectionUsers(Request $request): array {

        $base_url = "#/admin/users";

        $result = [];
        $panel  = new \CoreUI\Panel();
        $view   = new Classes\Users\View();

        $content   = [];
        $content[] = $this->getJsModule('admin', 'assets/users/js/admin.users.js');

        try {
            if ($request->test('^/admin/users/.+$')) {
                $params = $request->match('^/admin/users/{user_id}$', ['[0-9]+']);

                if ( ! isset($params['user_id'])) {
                    throw new AppException($this->_('Указан некорректный адрес. Вернитесь обратно и попробуйте снова'));
                }

                $breadcrumb = new \CoreUI\Breadcrumb();
                $breadcrumb->addItem('Пользователи', $base_url);
                $breadcrumb->addItem('Пользователь');

                $result[] = $breadcrumb->toArray();

                $panel->setContentFit($panel::FIT_MIN);


                if ( ! empty($params['user_id'])) {
                    $user = $this->tableUsers->getRowById($params['user_id']);

                    if (empty($user)) {
                        throw new AppException('Указанный пользователь не найден');
                    }

                    $name = trim("{$user->lname} {$user->fname} {$user->mname}");
                    $avatar = "<img src=\"core3/user/{$user->id}/avatar\" style=\"width: 32px;height: 32px\" class=\"rounded-circle border border-secondary-subtle\"> ";
                    $panel->setTitle($avatar . ($name ?: $user->login), $this->_('Редактирование пользователя'));

                    $content[] = $view->getForm($base_url, $user);

                } else {
                    $panel->setTitle($this->_('Добавление пользователя'));
                    $content[] = $view->getFormNew($base_url);
                }

            } else {
                $content[] = $view->getTable($base_url);
            }

            $panel->setContent($content);

        } catch (AppException $e) {
            $this->log->info($e->getMessage());
            $panel->setContent(
                \CoreUI\Info::danger($e->getMessage(), $this->_('Ошибка'))
            );

        } catch (\Exception $e) {
            $this->log->error('Admin users', $e);
            $panel->setContent(
                \CoreUI\Info::danger(
                    $this->config?->system->debug?->on
                        ? $e->getMessage()
                        : $this->_('Обновите страницу или попробуйте позже'),
                    $this->_('Ошибка')
                )
            );
        }

        $result[] = $panel->toArray();

        return $result;
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