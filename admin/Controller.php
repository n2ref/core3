<?php
namespace Core3\Mod\Admin;
use Core3\Classes\Response;
use Core3\Exceptions\Exception;
use Core3\Exceptions\AppException;
use Core3\Interfaces\Events;
use Core3\Mod\Admin\Classes\Modules;
use Core3\Mod\Admin\Classes\Users;
use Core3\Mod\Admin\Classes\Index;
use Core3\Mod\Admin\Classes\Roles;
use Core3\Mod\Admin\Classes\Settings;
use Core3\Mod\Admin\Classes\Logs;
use Core3\Classes\Common;
use Core3\Classes\Request;
use Core3\Classes\Router;
use CoreUI\Info;
use CoreUI\Panel;
use CoreUI\Panel\Control;
use Monolog\Handler\MissingExtensionException;


require_once 'Classes/autoload.php';


/**
 * @property Tables\Modules                  $tableModules
 * @property Tables\ModulesSections          $tableModulesSections
 * @property Tables\ModulesAvailable         $tableModulesAvailable
 * @property Tables\ModulesAvailableVersions $tableModulesAvailableVersions
 * @property Tables\Roles                    $tableRoles
 * @property Tables\Users                    $tableUsers
 * @property Tables\UsersData                $tableUsersData
 * @property Tables\UsersFiles               $tableUsersFiles
 * @property Tables\UsersSession             $tableUsersSession
 * @property Tables\Controls                 $tableControls
 * @property Tables\Settings                 $tableSettings
 * @property Models\Users                    $modelUsers
 * @property Models\Settings                 $modelSettings
 * @property Models\Roles                    $modelRoles
 */
class Controller extends Common implements Events {

    /**
     * @param Request $request
     * @return string
     * @throws \Core3\Exceptions\DbException
     */
    public function init(Request $request): string {

        $content  = $this->getCssModule('admin', 'assets/dist/admin.min.css');
        $content .= $this->getJsModule('admin', 'assets/dist/admin.min.js');
        $content .= file_get_contents(__DIR__ . '/assets/index.html');

        return $content;
    }


    /**
     * @param Request $request
     * @return array|string|Response
     * @throws MissingExtensionException
     * @throws Exception
     */
    public function sectionIndex(Request $request): array|string|Response {

        $router = new Router('/admin/index', [
            '/'                       => ['get' => [Index\Handler::class, 'getDashboardInfo']],
            '/system/cache/clear'     => ['post' => [Index\Handler::class, 'clearCache']],
            '/system/process'         => ['get' => [Index\Handler::class, 'getSystemProcessRecords']],
            '/system/repo'            => [
                'get' => [Index\Handler::class, 'getSystemRepo'],
                'put' => [Index\Handler::class, 'saveSystemRepo'],
            ],
            '/system/repo/upgrade'    => ['get' => [Index\Handler::class, 'upgradeRepo']],
            '/php/info'               => ['get' => [Index\Handler::class, 'getPhpInfo']],
            '/db/connections'         => ['get' => [Index\Handler::class, 'getDbConnections']],
            '/db/variables'           => ['get' => [Index\Handler::class, 'getDbVariables']],
        ]);

        return $this->runRouterMethod($router, $request);
    }


    /**
     * Модули
     * @param Request $request
     * @return mixed
     * @throws MissingExtensionException
     * @throws \Exception
     */
    public function sectionModules(Request $request): mixed {

        $router = new Router('/admin/modules', [
            '/available/records'           => ['get'  => [Modules\Handler::class, 'getRecordsAvailable']],
            '/available/{id:\d+}'          => ['post' => [Modules\Handler::class, 'installAvailable']],
            '/available/{id:\d+}/content'  => ['get'  => [Modules\Handler::class, 'getPanelAvailModule']],
            '/available/{id:\d+}/versions' => ['get'  => [Modules\Handler::class, 'getPanelAvailModule']],

            '/repo'               => ['put'  => [Modules\Handler::class, 'saveRepo']],
            '/repo/upgrade'       => ['post' => [Modules\Handler::class, 'upgradeRepo']],

            '/0/hand' => [
                'post' => [Modules\Handler::class, 'installHand'],
            ],
            '/0/file' => [
                'post' => [Modules\Handler::class, 'installFile'],
            ],
            '/0/file/upload' => [
                'post' => [Modules\Handler::class, 'uploadFile'],
            ],


            '/{id:\d+}' => [
                'put'    => [Modules\Handler::class, 'saveModule'],
                'patch'  => [Modules\Handler::class, 'switchActiveModule'],
                'delete' => [Modules\Handler::class, 'deleteModule'],
            ],
            '/{id:\d+}/hand'     => [ 'put'    => [Modules\Handler::class, 'saveModuleHand'], ],
            '/{id:\d+}/sections' => [ 'delete' => [Modules\Handler::class, 'deleteModuleSections'], ],

            '/{id:\d+}/sections/{section_id:\d+}' => [
                'put'   => [Modules\Handler::class, 'saveModuleSection'],
                'patch' => [Modules\Handler::class, 'switchActiveSection'],
            ],

            '/{id:\d+}/versions/records'    => [
                'get'    => [Modules\Handler::class, 'getRecordsVersions'],
                'delete' => [Modules\Handler::class, 'deleteVersions'],
            ],
            '/{id:\d+}/versions/repository' => [
                'put' => [Modules\Handler::class, 'saveModuleRepository'],
            ],

            '/{id:\d+}/versions/0/upload' => ['get' => [Modules\Handler::class, 'uploadFile']],
        ]);


        $route_method = $router->getRouteMethod($request->getMethod(), $request->getUri());

        if ($route_method) {
            //TODO улучшить обработку ошибок
            return $this->runRouterMethod($router, $request);
        }


        $base_url = "admin/modules";
        $result   = [];
        $view     = new Modules\View();
        $panel    = new \CoreUI\Panel('module');
        $panel->setContentFit($panel::FIT);
        $panel->setTabsType($panel::TABS_TYPE_UNDERLINE);


        $result[] = $this->getCssModule('admin', '/assets/modules/css/admin.modules.css');
        $result[] = $this->getJsModule('admin', '/assets/modules/js/admin.modules.js');

        if ($params = $request->getPathParams('^/admin/modules/{module_id:\d+}')) {

            $breadcrumb = new \CoreUI\Breadcrumb();
            $breadcrumb->addItem('Модули',        "#/{$base_url}");
            $breadcrumb->addItem('Установленные', "#/{$base_url}/installed");
            $breadcrumb->addItem('Модуль');
            $result[] = $breadcrumb->toArray();


            if ($params['module_id']) {
                $module = $this->tableModules->getRowById($params['module_id']);

                if (empty($module)) {
                    throw new AppException($this->_('Указанный модуль не найден'));
                }

                $module_available    = $this->tableModulesAvailable->getRowsByNameVendor($module->name)->toArray();
                $module_available_id = array_column($module_available, 'id');

                $sections_count = $this->tableModulesSections->getCountByModuleId($module->id);
                $versions_count = $this->tableModulesAvailableVersions->getCountByModulesId($module_available_id);

                $panel->setTitle($module->title, "{$module->name} / {$module->version}");
                $panel->addControls([
                    (new Control\Button('<i class="bi bi-trash"></i> ' . $this->_('Удалить')))->setAttr('class', 'btn btn-outline-danger'),
                ]);

                $panel->addTab($this->_("Модуль"),  'module',   "#/{$base_url}/{$module->id}/module");
                $panel->addTab($this->_("Разделы"), 'sections', "#/{$base_url}/{$module->id}/sections")->setCount($sections_count);
                $panel->addTab($this->_("Версии"),  'versions', "#/{$base_url}/{$module->id}/versions")->setCount($versions_count);

                if ($module->isset_updates) {
                    $panel->getTabById('versions')->setBadgeDot('danger');
                }

                $params  = $request->getPathParams('^/admin/modules/{module_id}/{tab}');
                $tab     = $params['tab'] ?? 'module';
                $content = [];
                $panel->setActiveTab($tab);

                switch ($tab) {
                    case 'module':
                        if ($module->install_type == 'hand') {
                            $content[] = $view->getFormModuleHand($module);

                        } else {
                            $content[] = $view->getFormModule($module);
                        }
                        break;

                    case 'sections':
                        if ($section_params = $request->getPathParams('^/admin/modules/{module_id}/sections/{section_id:\d+}')) {
                            if ( ! empty($section_params['section_id'])) {
                                $module_section = $this->modAdmin->tableModulesSections->getRowById($section_params['section_id']);

                                if (empty($module_section)) {
                                    throw new AppException($this->_('Указанный раздел модуля не найден'));
                                }
                            }

                            $content[] = $view->getFormSection($base_url, $module, $module_section ?? null);
                            $content[] = '<br><br>';
                        }

                        $content[] = $view->getTableSections($base_url, $module);
                        break;

                    case 'versions':
                        if ($request->isPath('^/admin/modules/{module_id}/versions/0')) {
                            $content[] = $view->getFormVersionsFile($module);
                            $content[] = '<br><br>';
                        }

                        $content[] = $view->getTableVersions($module);
                        break;
                }

                $panel->setContent($content);


            }
            elseif ($params = $request->getPathParams('^/admin/modules/0(|/{tab})$')) {

                $panel->setTitle($this->_('Добавление модуля'));
                $panel->addTab($this->_("Ручная установка"), 'hand', "#/{$base_url}/0/hand");
                $panel->addTab($this->_("Из файла"),         'file', "#/{$base_url}/0/file");
                $panel->addTab($this->_("По ссылке"),        'link', "#/{$base_url}/0/link");

                $content = [];
                $tab     = $params['tab'] ?? 'hand';
                $panel->setActiveTab($tab);

                switch ($tab) {
                    case 'hand': $content[] = $view->getFormInstallHand(); break;
                    case 'file': $content[] = $view->getFormInstallFile(); break;
                    case 'link': $content[] = $view->getFormInstallLink(); break;
                }

                $panel->setContent($content);
            }

        }
        else {
            $count_modules   = $this->tableModules->getCount();
            $count_available = $this->tableModulesAvailable->getCount();

            $panel->addTab($this->_("Установленные"), 'installed', "#/{$base_url}/installed")->setCount($count_modules);
            $panel->addTab($this->_("Доступные"),     'available', "#/{$base_url}/available")->setCount($count_available);
            $panel->addTab($this->_("Репозитории"),   'repo',      "#/{$base_url}/repo")->setCount($count_available);

            $tab = $request->getPathParams('^/admin/modules/{tab}')['tab'] ?? 'installed';

            $panel->setActiveTab($tab);

            switch ($tab) {
                case 'installed': $content[] = $view->getTableInstalled(); break;

                case 'available':
                    $available_params = $request->getPathParams('^/admin/modules/available/{id:\d+}(/{tab}|)');

                    $layout = new \CoreUI\Layout();
                    $layout->setJustify($layout::JUSTIFY_START);
                    $layout->setDirection($layout::DIRECTION_ROW);
                    $layout->setGap(15);
                    $layout->setWrap($layout::NOWRAP);

                    $layout->addItems('available-table')->setWidthMin(500)->setWidth(400)->setContent(
                        $view->getTableAvailable($available_params['id'] ?? null)
                    );

                    $layout_preview = $layout->addItems('available-preview')->setFill(true);

                    if ( ! empty($available_params['id'])) {
                        $available_module = $this->tableModulesAvailable->getRowById($available_params['id']);

                        $content_preview = [];

                        if (empty($available_module)) {
                            $content_preview[] = Info::danger($this->_('Указанный модуль не найден'));

                        } else {
                            $panel_available = new Panel('available');
                            $panel_available->setWrapperType($panel_available::WRAPPER_NONE);
                            $panel_available->setTabsType($panel_available::TABS_TYPE_UNDERLINE);

                            $subtitles = [];

                            if ( ! is_null($available_module->count_downloads)) {
                                $subtitles[0][] = "<small class=\"text-secondary pe-2\"><i class=\"bi bi-download\"></i> {$available_module->count_downloads}</small>";
                            }

                            $subtitles[0][] = "<small class=\"text-secondary pe-2\">v{$available_module->version}</small>";
                            $subtitles[0][] = "<small class=\"text-secondary\">{$available_module->vendor}/{$available_module->name}</small>";


                            if ($available_module->home_page) {
                                $subtitles[1][] = "<a href=\"{$available_module->home_page}\" target=\"_blank\">module homepage</a>";
                            }

                            if ($available_module->tags) {
                                $tags = json_decode($available_module->tags, true);
                                $tags = array_map(function ($tag) {
                                    if (is_string($tag) && $tag) {
                                        return "<span class=\"badge text-bg-secondary\">{$tag}</span>";
                                    }
                                }, $tags);

                                $subtitles[2][] = implode(' ', $tags);
                            }


                            $panel_available->setTitle(
                                $available_module->title,
                                implode('<br>', array_map(function ($items) {
                                    return implode(' ', $items);
                                }, $subtitles))
                            );

                            $panel_available->addControls([
                                (new Control\Button($this->_('Установить')))
                                    ->setAttr('class', 'btn btn-sm btn-outline-success'),
                            ]);

                            $count_available_versions = $this->tableModulesAvailableVersions->getCountByModulesId([$available_module->id]);

                            $panel_available->addTab($this->_('Описание'), 'description');
                            $panel_available->addTab($this->_('Версии'),   'versions')->setCount($count_available_versions);

                            $tab_preview = $available_params['tab'] ?? 'description';
                            $panel_available->setActiveTab($tab_preview);

                            switch ($tab_preview) {
                                case 'description':
                                    $panel_available->setContent(
                                        $view->getAvailableDescription($available_module->description)
                                    );
                                    break;

                                case 'versions':
                                    $panel_available->setContent([]);
                                    break;
                            }

                            $content_preview[] = $panel_available->toArray();
                        }

                        $layout_preview->setContent($content_preview);
                    }

                    $content[] = $layout->toArray();
                    break;

                case 'repo':
                    $layout = new \CoreUI\Layout();
                    $layout->setJustify($layout::JUSTIFY_AROUND);
                    $layout->setDirection($layout::DIRECTION_COLUMN);
                    $layout->setGap(15);

                    $layout->addItems('repo-form')->setContent(
                        $view->getFormRepo((bool)$request->getQuery('edit'))
                    );

                    $layout->addItems('repo-upgrade')->setFill(true)->setContent([
                        // TODO 1111111
                    ]);

                    $content[] = $layout->toArray();
                    break;
            }

            $panel->setContent($content);
        }

        $result[] = $panel->toArray();

        return $result;
    }


    /**
     * Справочник пользователей системы
     * @param Request $request
     * @return array|Response
     * @throws MissingExtensionException
     * @throws \Exception
     */
    public function sectionUsers(Request $request): array|Response {

        $router = new Router('/admin/users', [
            '/' => [
                'get' => function () use ($request) {
                    $request->isPath('');
                },
            ],
            '(|/)'     => ['get' => [Users\Handler::class, 'getPanelUsers']],
            '/records' => [
                'get'    => [Users\Handler::class, 'getRecordsUsers'],
                'delete' => [Users\Handler::class, 'deleteUsers'],
            ],
            '/login'   => [
                'post' => [Users\Handler::class, 'loginUser'],
            ],
            '/avatar/upload' => [
                'post' => [Users\Handler::class, 'uploadFile'],
            ],

            '/{id:\d+}(|/{tab})' => [
                'get'   => [Users\Handler::class, 'getPanelUser'],
                'put'   => [Users\Handler::class, 'saveUser'],
                'post'  => [Users\Handler::class, 'saveUserNew'],
                'patch' => [Users\Handler::class, 'switchUserActive'],
            ],
            '/{id:\d+}/user/form'                 => ['get' => [Users\Handler::class, 'getUserForm']],
            '/{id:\d+}/sessions/{session_id:\d+}' => ['get' => [Users\Handler::class, 'switchUserSession']],
            '/{id:\d+}/sessions/table'            => ['get' => [Users\Handler::class, 'getUserSessions']],
            '/{id:\d+}/sessions/table/records'    => ['get' => [Users\Handler::class, 'getRecordsSessions']],
            '/{id:\d+}/avatar/download'           => ['get' => [Users\Handler::class, 'getAvatarDownload']],
        ]);

        return $this->runRouterMethod($router, $request);
    }


    /**
     * Настройки
     * @param Request $request
     * @return Response|array|string
     * @throws MissingExtensionException|\Core3\Exceptions\Exception
     * @throws \Exception
     */
    public function sectionSettings(Request $request): Response|array|string {

        $router = new Router();
        $router->route('/admin/settings')
            ->get([Settings\Handler::class, 'getSettings'])
            ->delete([Settings\Handler::class, 'deleteSettings']);

        $router->route('/admin/settings/{id:\d+}')
            ->get([  Settings\Handler::class, 'getSetting' ])
            ->put([  Settings\Handler::class, 'saveSetting' ])
            ->post([ Settings\Handler::class, 'saveSettingNew' ])
            ->patch([Settings\Handler::class, 'switchActive']);


        return $this->runRouterMethod($router, $request);
    }


    /**
     * Роли и доступ
     * @param Request $request
     * @return Response|array|null
     * @throws Exception
     * @throws MissingExtensionException
     */
    public function sectionRoles(Request $request): Response|array|null {

        $router = new Router();
        $router->route('/admin/roles(/|){tab:(|access)}')->get([Roles\Handler::class, 'getRoles']);
        $router->route('/admin/roles/table')
            ->get([Roles\Handler::class, 'getRolesTable'])
            ->delete([Roles\Handler::class, 'deleteRoles']);

        $router->route('/admin/roles/access')         ->post([Roles\Handler::class, 'setAccess']);
        $router->route('/admin/roles/access/all')     ->post([Roles\Handler::class, 'setAccessAllRole']);
        $router->route('/admin/roles/access/table')   ->get([ Roles\Handler::class, 'getAccessTable']);
        $router->route('/admin/roles/{id:\d+}')
            ->get([ Roles\Handler::class, 'getRole' ])
            ->post([ Roles\Handler::class, 'saveRole' ]);


        return $this->runRouterMethod($router, $request);
    }


    /**
     * Мониторинг системы
     * @param Request $request
     * @return Response|array|null
     * @throws Exception
     * @throws MissingExtensionException
     */
    public function sectionLogs(Request $request): Response|array|null {

        $router = new Router('/admin/logs', [
            '(|/{file_hash})'       => ['get' => [Logs\Handler::class, 'getPanelLogs']],
            '/{file_hash}/records'  => ['get' => [Logs\Handler::class, 'getRecordsLog']],
            '/{file_hash}/chart'    => ['get' => [Logs\Handler::class, 'getChartLogs']],
            '/{file_hash}/download' => ['get' => [Logs\Handler::class, 'downloadLog']],
        ]);

        return $this->runRouterMethod($router, $request);
    }


    /**
     * Обработка событий системы
     * @param string $module
     * @param string $event
     * @param array $data
     * @return void
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    public function events(string $module, string $event, array $data): void {

        if ($module == 'admin') {
            switch ($event) {
                case 'role_update': (new Classes\Events())->roleUpdate($data); break;
            }
        }
    }
}