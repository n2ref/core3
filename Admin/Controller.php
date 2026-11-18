<?php
namespace Rowan\Admin;
use Rowan\Classes\Response;
use Rowan\Exceptions\Exception;
use Rowan\Exceptions\AppException;
use Rowan\Interfaces\Events;
use Rowan\Admin\Classes\Modules;
use Rowan\Admin\Classes\Users;
use Rowan\Admin\Classes\Index;
use Rowan\Admin\Classes\Roles;
use Rowan\Admin\Classes\Settings;
use Rowan\Admin\Classes\Logs;
use Rowan\Classes\Common;
use Rowan\Classes\Request;
use Rowan\Classes\Router;
use CoreUI\Info;
use CoreUI\Panel;
use CoreUI\Panel\Control;
use Monolog\Handler\MissingExtensionException;


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
     * @throws \Rowan\Exceptions\DbException
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
            '/available/{id:\d+}/content'  => ['get'  => [Modules\Handler::class, 'get']],
            '/available/{id:\d+}/versions' => ['get'  => [Modules\Handler::class, 'get']],

            '/repo'               => ['put'  => [Modules\Handler::class, 'saveRepo']],
            '/repo/upgrade'       => ['post' => [Modules\Handler::class, 'upgradeRepo']],

            '/0/hand'        => [ 'post' => [Modules\Handler::class, 'installHand'], ],
            '/0/file'        => [ 'post' => [Modules\Handler::class, 'installFile'], ],
            '/0/file/upload' => [ 'post' => [Modules\Handler::class, 'uploadFile'], ],


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

        return $this->runRouterMethod($router, $request);
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
                'get'    => [Users\Handler::class, 'getRecordsUsers'],
                'delete' => [Users\Handler::class, 'deleteUsers'],
            ],

            '/{id:\d+}' => [
                'get'   => [Users\Handler::class, 'getUser'],
                'put'   => [Users\Handler::class, 'saveUser'],
                'post'  => [Users\Handler::class, 'saveUserNew'],
                'patch' => [Users\Handler::class, 'switchUserActive'],
            ],

            '/{id:\d+}/short' => [ 'get' => [Users\Handler::class, 'getUserShort'] ],

            '/{id:\d+}/sessions'             => ['get'   => [Users\Handler::class, 'getRecordsSessions']],
            '/{id:\d+}/avatar/download'      => ['get'   => [Users\Handler::class, 'getAvatarDownload']],
            '/\d+/sessions/{session_id:\d+}' => ['patch' => [Users\Handler::class, 'switchUserSession']],

            '/roles'         => [ 'get'  => [Users\Handler::class, 'getRoles'] ],
            '/login'         => [ 'post' => [Users\Handler::class, 'loginUser'] ],
            '/avatar/upload' => [ 'post' => [Users\Handler::class, 'uploadFile'] ],
        ]);

        return $this->runRouterMethod($router, $request);
    }


    /**
     * Настройки
     * @param Request $request
     * @return Response|array|string
     * @throws MissingExtensionException|\Rowan\Exceptions\Exception
     * @throws \Exception
     */
    public function sectionSettings(Request $request): Response|array|string {

        $router = new Router('/admin/settings', [
            '/' => [
                'get'    => [Settings\Handler::class, 'getSettings'],
                'delete' => [Settings\Handler::class, 'deleteSettings'],
            ],

            '/{id:\d+}' => [
                'get'   => [Settings\Handler::class, 'getSetting'],
                'put'   => [Settings\Handler::class, 'saveSetting'],
                'post'  => [Settings\Handler::class, 'saveSettingNew'],
                'patch' => [Settings\Handler::class, 'switchActive'],
            ],

            '/modules' => [ 'get' => [Settings\Handler::class, 'getModules'] ],
        ]);

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

        $router = new Router('/admin/roles', [
            '(|/|/{tab:(|access)})' => [
                'get' => [Roles\Handler::class, 'getRoles'],
            ],

            '/table' => [
                'get'    => [Roles\Handler::class, 'getRolesTable'],
                'delete' => [Roles\Handler::class, 'deleteRoles'],
            ],

            '/access'       => [ 'post' => [Roles\Handler::class, 'setAccess'] ],
            '/access/all'   => [ 'post' => [Roles\Handler::class, 'setAccessAllRole'] ],
            '/access/table' => [ 'get'  => [Roles\Handler::class, 'getAccessTable'] ],

            '/{id:\d+}' => [
                'get'  => [Roles\Handler::class, 'getRole'],
                'post' => [Roles\Handler::class, 'saveRole'],
            ],
        ]);

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