<?php
namespace Core3\Mod\Admin;
use Core3\Classes\Response;
use Core3\Exceptions\Exception;
use Core3\Exceptions\AppException;
use Core3\Interfaces\Events;
use Core3\Mod\Admin\Classes;
use Core3\Mod\Admin\Classes\Modules;
use Core3\Mod\Admin\Classes\Users;
use Core3\Mod\Admin\Classes\Index;
use Core3\Mod\Admin\Classes\Roles;
use Core3\Mod\Admin\Classes\Settings;
use Core3\Classes\Common;
use Core3\Classes\Request;
use Core3\Classes\Router;
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
 * @property Tables\Settings        $tableSettings
 * @property Models\Users           $modelUsers
 * @property Models\Settings        $modelSettings
 * @property Models\Roles           $modelRoles
 */
class Controller extends Common implements Events {

    /**
     * @param Request $request
     * @return array|string|Response
     * @throws MissingExtensionException
     * @throws Exception
     */
    public function sectionIndex(Request $request): array|string|Response {

        $router = new Router();
        $router->route('/admin/index')                       ->get([  Index\Handler::class, 'getDashboard']);
        $router->route('/admin/index/system/cache/clear')    ->post([ Index\Handler::class, 'clearCache']);
        $router->route('/admin/index/system/process')        ->get([  Index\Handler::class, 'getSystemProcess']);
        $router->route('/admin/index/system/process/records')->get([  Index\Handler::class, 'getSystemProcessRecords']);
        $router->route('/admin/index/php/info')              ->get([  Index\Handler::class, 'getPhpInfo']);
        $router->route('/admin/index/db/connections')        ->get([  Index\Handler::class, 'getDbConnections']);
        $router->route('/admin/index/db/connections/records')->get([  Index\Handler::class, 'getDbConnectionsRecords']);
        $router->route('/admin/index/db/variables')          ->get([  Index\Handler::class, 'getDbVariables']);

        return $this->runRouterMethod($router, $request);
    }


    /**
     * Модули
     * @param Request $request
     * @return array|Response
     * @throws MissingExtensionException
     * @throws \Exception
     */
    public function sectionModules(Request $request): array|Response {

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
            ->get([   Modules\Handler::class, 'getModule'])
            ->post([  Modules\Handler::class, 'saveModule'])
            ->patch([ Modules\Handler::class, 'switchActiveModule'])
            ->delete([Modules\Handler::class, 'deleteModule']);

        $router->route('/admin/modules/{id:\d+}/module')        ->get([Modules\Handler::class, 'getModule']);
        $router->route('/admin/modules/{id:\d+}/module/content')->get([Modules\Handler::class, 'getModuleContent']);

        $router->route('/admin/modules/{id:\d+}/sections')        ->get([Modules\Handler::class, 'getModuleSections']);
        $router->route('/admin/modules/{id:\d+}/sections/content')->get([Modules\Handler::class, 'getModuleSectionsContent']);
        $router->route('/admin/modules/{id:\d+}/sections/table')
            ->get([   Modules\Handler::class, 'getModuleSections'])
            ->delete([Modules\Handler::class, 'deleteModuleSections']);

        $router->route('/admin/modules/{id:\d+}/sections/{section_id:\d+}')
            ->get([  Modules\Handler::class, 'getModuleSections'])
            ->post([ Modules\Handler::class, 'saveModuleSections'])
            ->patch([Modules\Handler::class, 'switchActiveSection']);

        $router->route('/admin/modules/{id:\d+}/sections/{section_id:\d+}/content')->get([Modules\Handler::class, 'getModuleSectionsContent']);

        $router->route('/admin/modules/{id:\d+}/versions')        ->get([Modules\Handler::class, 'getModuleVersions']);
        $router->route('/admin/modules/{id:\d+}/versions/content')->get([Modules\Handler::class, 'getModuleVersionsContent']);

        return $this->runRouterMethod($router, $request);





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

        }
        elseif ($params = $request->getPathParams('^/admin/modules/available/{module_id:\d+}')) {

        }
        else {
            $count_modules = $this->tableModules->getCount();

            $panel->addTab($this->_("Установленные"), 'installed')->setUrlWindow("{$base_url}/installed")->setUrlContent("{$handler_url}/getTableInstalled")->setCount($count_modules);
            $panel->addTab($this->_("Доступные"),     'available')->setUrlWindow("{$base_url}/available")->setUrlContent("{$handler_url}/getTableAvailable");

            $params = $request->getPathParams('^/admin/modules/{tab}$');
            $tab    = $params['tab'] ?? 'installed';

            $panel->setActiveTab($tab);
            $panel->setUrlContent($panel->getTabById($tab)->getUrlContent());
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
        $router->route('/admin/users/records')
            ->get([Users\Handler::class, 'getUsersRecords'])
            ->delete([Users\Handler::class, 'deleteUsers']);

        $router->route('/admin/users/login')->post([Users\Handler::class, 'loginUser']);
        $router->route('/admin/users/avatar/upload')->post([Users\Handler::class, 'uploadFile']);

        $router->route('/admin/users/{id:\d+}')
            ->get([   Users\Handler::class, 'getUser' ])
            ->put([   Users\Handler::class, 'saveUser' ])
            ->post([  Users\Handler::class, 'saveUserNew' ])
            ->patch([ Users\Handler::class, 'switchUserActive' ]);

        $router->route('/admin/users/{id:\d+}/avatar/download')->get([ Users\Handler::class, 'getAvatarDownload' ]);

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
     * @throws \Exception
     * @return void
     */
    public function sectionMonitoring() {

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