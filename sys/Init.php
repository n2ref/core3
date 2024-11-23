<?php
namespace Core3\Sys;
use Core3\Classes\Cli;
use Core3\Classes\Common;
use Core3\Classes\Registry;
use Core3\Classes\Request;
use Core3\Classes\Response;
use Core3\Classes\Router;
use Core3\Exceptions\AppException;
use Core3\Exceptions\DbException;
use Core3\Exceptions\Exception;
use Core3\Exceptions\HttpException;
use Core3\Mod\Admin;
use Laminas\Cache\Exception\ExceptionInterface;
use Laminas\Permissions;
use Monolog\Handler\MissingExtensionException;


/**
 * @property Admin\Controller $modAdmin
 */
class Init extends Common {

    /**
     * @var Auth|null
     */
    private Auth|null $auth = null;


    /**
     * @throws MissingExtensionException
     */
    public function __construct() {

        parent::__construct();

        if (PHP_SAPI != 'cli') {
            if (empty($_SERVER['HTTPS']) && $this->config?->system?->https) {
                header('Location: https://' . $_SERVER['SERVER_NAME']);
            }
        }

        $tz = $this->config?->system?->timezone;

        if ( ! empty($tz)) {
            date_default_timezone_set($tz);
        }

        $this->registerFatal();
    }


    /**
     * Авторизация пользователя
     * @return bool
     */
    public function auth(): bool {

        if (PHP_SAPI === 'cli') {
            return false;
        }

        $access_token = $this->getAccessToken();

        $this->auth = $access_token
            ? $this->getAuthByToken($access_token)
            : null;

        if ($this->auth) {
            Registry::set('auth', $this->auth);
            return true;
        }

        return false;
    }


    /**
     * Обработка запроса
     * @return string
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \ReflectionException
     */
    public function dispatch(): string {

        return PHP_SAPI === 'cli'
            ? $this->dispatchCli()
            : $this->dispatchHttp();
    }


    /**
     * @throws \Exception
     */
    public function __destruct() {

        if ($this->config?->system?->log?->profile?->on &&
            $this->config?->system?->log?->profile?->file
        ) {
            $sql_queries = $this->db->fetchAll("show profiles");
            $total_time  = 0;
            $max_slow    = [];

            if ( ! empty($sql_queries)) {
                foreach ($sql_queries as $sql_query) {

                    if ( ! empty($sql_query['Duration'])) {
                        $total_time += $sql_query['Duration'];

                        if (empty($max_slow['Duration']) || $max_slow['Duration'] < $sql_query['Duration']) {
                            $max_slow = $sql_query;
                        }
                    }
                }
            }

            $request_method = ! empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'none';
            $query_string   = ! empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';

            if ($total_time >= 1 || count($sql_queries) >= 100 || count($sql_queries) == 0) {
                $function_log = 'warning';
            } else {
                $function_log = 'info';
            }


            $profile_file = $this->config->system->log->profile->file;

            $this->log->file($profile_file)->{$function_log}('request', [$request_method, round($total_time, 5), count($sql_queries), $query_string]);
            $this->log->file($profile_file)->{$function_log}('  | max slow', $max_slow);
            $this->log->file($profile_file)->{$function_log}('  | queries ', $sql_queries);
        }
    }


    /**
     * Обработка запроса Cli
     * @return string
     * @throws \ReflectionException
     * @throws \Exception
     * @throws ExceptionInterface
     */
    private function dispatchCli(): string {

        $result  = '';
        $cli     = new Cli();
        $options = $cli->getOptions();

        // Help info
        if (empty($options) || isset($options['h']) || isset($options['help'])) {
            $result = $cli->getHelp();

        // Version info
        } elseif (isset($options['v']) || isset($options['version'])) {
            $result = $this->config?->system?->version ?? '--';

        // Getting information about installed modules
        } elseif (isset($options['n']) || isset($options['modules'])) {
            $result = $cli->getInstalledModules();

        // Control composer
        } elseif (isset($options['c']) || isset($options['composer'])) {
            try {
                $params = $options['param'] ?? (isset($options['p']) ? $options['p'] : false);
                $params = $params === false ? [] : (is_array($params) ? $params : array($params));
                $cli->updateComposer($params);

            } catch (Exception $e) {
                $result = $e->getMessage() . PHP_EOL;
            }

        // Getting information about available system methods
        } elseif (isset($options['l']) || isset($options['cli-methods'])) {
            $result = $cli->getCliMethods();

        // Module run method
        } elseif ((isset($options['m']) || isset($options['module'])) &&
                  (isset($options['e']) || isset($options['method']))
        ) {
            $module = $options['module'] ?? $options['m'];
            $method = $options['method'] ?? $options['e'];

            $params = $options['param'] ?? (isset($options['p']) ? $options['p'] : false);
            $params = $params === false ? [] : (is_array($params) ? $params : array($params));

            $result = $cli->startCliMethod($module, $method, $params);

        // Start daemon worker manager
        } elseif (isset($options['worker-start'])) {
            $is_daemonize = isset($options['d']);
            $cli->startWorkerManager($is_daemonize);

        // Start daemon worker manager
        } elseif (isset($options['worker-stop'])) {
            $params = $options['param'] ?? (isset($options['p']) ? $options['p'] : false);
            $params = $params === false ? [] : (is_array($params) ? $params : array($params));

            $force  = isset($params[0]) && (bool)$params[0];
            $result = $cli->stopWorkerManager($force)
                ? "Worker stopped"
                : "Failed to stop the process";

        // Restart daemon worker manager
        } elseif (isset($options['worker-restart'])) {
            $params = $options['param'] ?? (isset($options['p']) ? $options['p'] : false);
            $params = $params === false ? [] : (is_array($params) ? $params : array($params));

            $force  = isset($params[0]) && (bool)$params[0];
            $result = $cli->restartWorkerManager($force)
                ? "Worker restarted"
                : "Failed restarted the process";
        }

        return $result . PHP_EOL;
    }


    /**
     * Обработка запроса Http
     * @return string
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Exception
     */
    private function dispatchHttp(): string {

        try {
            if ($this->auth) {
                if ( ! $this->auth->isAdmin()) {
                    $acl = $this->getRoleAcl($this->auth->getRoleId());

                    if ($acl) {
                        $this->auth->setAcl($acl);
                    }
                }

                $this->logRequest();
            }


            // Disable
            if ($this->config?->system?->disable?->on && ! $this->auth?->isAdmin()) {
                $result = [
                    'core_type'   => 'disable_page',
                    'title'       => $this->config?->system?->disable?->title ?? $this->_('Система на профилактике'),
                    'description' => $this->config?->system?->disable?->description ?? '',
                ];

            } else {
                ob_start();
                $result = $this->getHandlerResponse();
                $buffer = ob_get_clean();
            }


            if (is_array($result)) {
                if (isset($buffer) && is_string($buffer) && $buffer != '' && ! array_key_exists('_buffer', $result)) {
                    $result['_buffer'] = $buffer;
                }

                $response = new Response();
                $response->setContentTypeJson();
                $response->setContentJson($result);

            } elseif (is_scalar($result)) {
                if (isset($buffer) && is_string($buffer) && $buffer != '') {
                    $result = $buffer . $result;
                }

                $response = new Response();
                $response->setContentTypeHtml();
                $response->setContent($result);

            } elseif ($result instanceof Response) {
                if (isset($buffer) && is_string($buffer) && $buffer != '') {
                    $result->appendContent($buffer);
                }

                $response = $result;

            } else {
                $response = new Response();
                $response->setHeader('Content-Type', 'text/plain');
            }

            if ( ! is_array($result)) {
                if (Registry::has('js')) {
                    foreach (Registry::get('js') as $src) {
                        $response->appendContent("<script type=\"text/javascript\" src=\"{$src}\"></script>'");
                    }
                }

                if (Registry::has('css')) {
                    foreach (Registry::get('css') as $src) {
                        $response->appendContent("<link href=\"{$src}\" type=\"text/css\" rel=\"stylesheet\"/>");
                    }
                }
            }


        } catch (HttpException $e) {
            $response = Response::errorJson(
                $e->getCode(),
                $e->getErrorCode(),
                $e->getMessage(),
                $this->config?->system?->debug?->on ? $e->getTrace() : null
            );

        } catch (AppException $e) {
            $response = Response::errorJson(
                400,
                $e->getCode(),
                $e->getMessage(),
                $this->config?->system?->debug?->on ? $e->getTrace() : null
            );

        } catch (\Exception $e) {
            $response = Response::errorJson(
                500,
                $e->getCode(),
                $e->getMessage(),
                $this->config?->system?->debug?->on ? $e->getTrace() : null
            );
        }

        $this->logResponse($response);

        $response->printHeaders();
        return (string)$response->getContent();
    }


    /**
     * Получение настроенных привилегий
     * @param int $role_id
     * @return Permissions\Acl\Acl|null
     * @throws ExceptionInterface
     * @throws DbException
     */
    private function getRoleAcl(int $role_id):? Permissions\Acl\Acl {

        $cache_key = 'core3_acl_' . $role_id;

        if ($this->cache->test($cache_key)) {
            $acl = $this->cache->load($cache_key);
        }

        if (empty($acl)) {
            $modules  = (new Admin\Tables\Modules())->getRowsByActive();
            $sections = (new Admin\Tables\ModulesSections())->getRowsByActive();
            $role     = (new Admin\Tables\Roles())->getRowById($role_id);

            $acl = new Permissions\Acl\Acl();
            $acl->addRole(new Permissions\Acl\Role\GenericRole($role_id));


            $resources    = [];
            $modules_info = [];

            foreach ($modules as $module) {
                $modules_info[$module->name] = $this->getModuleInfoFromFile($module->name);

                $resources[$module->name] = ! empty($modules_info[$module->name]) &&
                                            ! empty($modules_info[$module->name]['privileges']) &&
                                            is_array($modules_info[$module->name]['privileges'])
                    ? $modules_info[$module->name]['privileges']
                    : [];

                $acl->addResource(new Permissions\Acl\Resource\GenericResource($module->name));


                foreach ($sections as $section) {
                    if ($section->module_id == $module->id) {
                        $resource             = "{$module->name}_{$section->name}";
                        $resources[$resource] = ! empty($modules_info[$module->name]['sections']) &&
                                                ! empty($modules_info[$module->name]['sections'][$section->name]) &&
                                                is_array($modules_info[$module->name]['sections'][$section->name]) &&
                                                ! empty($modules_info[$module->name]['sections'][$section->name]['privileges']) &&
                                                is_array($modules_info[$module->name]['sections'][$section->name]['privileges'])
                            ? $modules_info[$module->name]['sections'][$section->name]['privileges']
                            : [];

                        $acl->addResource(new Permissions\Acl\Resource\GenericResource($resource), $module->name);
                    }
                }
            }


            $role_privileges    = $role->privileges ? json_decode($role->privileges, true) : [];
            $privileges_default = [ 'access', 'edit', 'delete' ];

            if ( ! empty($resources)) {
                foreach ($resources as $resource => $privileges) {

                    // Установка дефолтных привилегий
                    foreach ($privileges_default as $privilege_default) {

                        if ( ! empty($role_privileges[$resource]) &&
                             in_array($privilege_default, $role_privileges[$resource])
                        ) {
                            $acl->allow($role_id, $resource, $privilege_default);
                        } else {
                            $acl->deny($role_id, $resource, $privilege_default);
                        }
                    }


                    // Установка привилегий из модулей
                    foreach ($privileges as $privilege_name) {

                        if (empty($privilege_name)) {
                            continue;
                        }

                        if ( ! empty($role_privileges[$resource]) &&
                             in_array($privilege_name, $role_privileges[$resource])
                        ) {
                            $acl->allow($role_id, $resource, $privilege_name);
                        } else {
                            $acl->deny($role_id, $resource, $privilege_name);
                        }
                    }
                }
            }

            $this->cache->save($cache_key, $acl, ["core3_acl", "core3_acl_" . $role_id]);
        }

        return $acl;
    }


    /**
     * Логирование активности пользователей
     * @throws \Exception|\Psr\Container\ContainerExceptionInterface
     */
    private function logRequest(): void {

        if ($this->config?->system?->log?->on &&
            $this->config?->system?->log?->access_file
        ) {
            $this->log->file($this->config->system->log->access_file)
                ->info($this->auth->getUserLogin(), [
                    'ip'     => $_SERVER['REMOTE_ADDR'] ?? '',
                    'uid'    => $this->auth->getUserId(),
                    'sid'    => $this->auth->getSessionId(),
                    'method' => $_SERVER['REQUEST_METHOD'] ?? '',
                    'uri'    => $_SERVER['REQUEST_URI'] ?? '',
                ]);
        }
    }


    /**
     * @param Response $response
     * @return void
     * @throws \Exception
     */
    private function logResponse(Response $response): void {

        if ($this->config?->system?->log?->on &&
            $this->config?->system?->log?->output_file
        ) {
            $this->log
                ->file($this->config?->system?->log?->output_file)
                ->info($response->getContent());
        }
    }


    /**
     * @return void
     * @throws MissingExtensionException
     */
    private function registerFatal(): void {

        register_shutdown_function(function () {
            $error = error_get_last();

            if ($error &&
                in_array($error['type'], [
                    E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR,
                    E_CORE_WARNING, E_COMPILE_WARNING, E_PARSE
                ])
            ) {
                $this->log->error('Fatal error', debug_backtrace());
            }
        });
    }


    /**
     * @return mixed
     * @throws HttpException
     * @throws \Exception
     */
    private function getHandlerResponse(): mixed {

        $router = new Router();
        $router->route('/sys/auth/login')              ->post([Handler::class, 'login']);
        $router->route('/sys/auth/refresh')            ->post([Handler::class, 'refreshToken']);
        $router->route('/sys/registration/email')      ->post([Handler::class, 'registrationEmail']);
        $router->route('/sys/registration/email/check')->post([Handler::class, 'registrationEmailCheck']);
        $router->route('/sys/restore')                 ->post([Handler::class, 'restorePass']);
        $router->route('/sys/restore/check')           ->post([Handler::class, 'restorePassCheck']);
        $router->route('/sys/conf')                    ->get([ Handler::class, 'getConf']);
        $router->route('/api/.*')                      ->get([ Handler::class, 'processApi']);

        if ($this->auth) {
            $router->route('/sys/auth/logout')                                                           ->put([Handler::class, 'logout']);
            $router->route('/sys/cabinet')                                                               ->get([Handler::class, 'getCabinet']);
            $router->route('/sys/error')                                                                 ->post([Handler::class, 'logError']);
            $router->route('/sys/home')                                                                  ->get([Handler::class, 'getHome']);
            $router->route('/sys/user/{id:\d+}/avatar')                                                  ->get([Handler::class, 'getUserAvatar']);
            $router->route('/{module:[a-z0-9_]+}/{section:[a-z0-9_]+}{mod_query:(?:/[a-zA-Z0-9_/\-]+|)}')->any([Handler::class, 'getModSection']);
        }

        $uri          = mb_substr($_SERVER['REQUEST_URI'], mb_strlen(rtrim(DOC_PATH, '/')));
        $route_method = $router->getRouteMethod($_SERVER['REQUEST_METHOD'], $uri);

        if ($route_method) {
            $request = new Request();

            // Обнуление
            $_GET     = [];
            $_POST    = [];
            $_REQUEST = [];
            $_FILES   = [];
            $_COOKIE  = [];

            $route_method->prependParam($request);
            return $route_method->run();

        } elseif ($uri !== DOC_PATH) {
            $response = new Response();
            $response->setHttpCode(404);
            $response->setHeader('Location', DOC_PATH);
            return $response;
        }

        $index = file_get_contents(__DIR__ . '/../front/index.html');
        return str_replace('[PATH]', DOC_PATH . CORE_FOLDER, $index);
    }


    /**
     * Получение токена из запроса
     * @return string
     */
    private function getAccessToken(): string {

        // проверяем, есть ли в запросе токен
        $access_token = ! empty($_SERVER['HTTP_ACCESS_TOKEN'])
            ? $_SERVER['HTTP_ACCESS_TOKEN']
            : '';

        // проверяем, есть ли в запросе токен
        return empty($access_token) && ! empty($_COOKIE['Core-Access-Token'])
            ? $_COOKIE['Core-Access-Token']
            : $access_token;
    }


    /**
     * Авторизация по токену
     * @param string $access_token
     * @return Auth|null
     */
    private function getAuthByToken(string $access_token): ?Auth {

        try {
            $sign      = $this->config?->system?->auth?->token_sign ?: 'gyctmn34ycrr0471yc4r';
            $algorithm = $this->config?->system?->auth?->algorithm ?: 'HS256';
            $decoded   = Token::decode($access_token, $sign, $algorithm);

            $session_id = $decoded['sid'] ?? 0;
            $token_iss  = $decoded['iss'] ?? 0;
            $token_exp  = $decoded['exp'] ?? 0;


            if (empty($token_exp) ||
                empty($session_id) ||
                ! is_numeric($session_id) ||
                $token_exp < time() ||
                $token_iss != $_SERVER['SERVER_NAME']
            ) {
                return null;
            }



            $session = $this->modAdmin->tableUsersSession->getRowById($session_id);

            if (empty($session) ||
                $session->is_active == '0' ||
                $session->date_expired < date('Y-m-d H:i:s')
            ) {
                return null;
            }


            $user = $this->modAdmin->tableUsers->getRowById($session->user_id);

            if (empty($user) && $user->is_active == '0') {
                return null;
            }

            $session->count_requests     = (int)$session->count_requests + 1;
            $session->date_last_activity = date('Y-m-d H:i:s');
            $session->save();

            return new Auth($user->toArray(), $session->toArray());

        } catch (\Exception $e) {
            $this->log->error('Error auth by token', $e);
        }

        return null;
    }
}