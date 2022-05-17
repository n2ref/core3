<?php
namespace Core3\Classes;
use Core3\Exceptions\HttpException;
use Core3\Mod\Admin;


/**
 * @property Admin\Controller $modAdmin
 */
class Init extends Acl {

    /**
     * Init constructor.
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __construct() {
        parent::__construct();

        if (empty($_SERVER['HTTPS'])) {
            if (isset($this->config->system) && ! empty($this->config->system->https)) {
                header('Location: https://' . $_SERVER['SERVER_NAME']);
            }
        }

        $tz = $this->config?->system?->timezone;

        if ( ! empty($tz)) {
            date_default_timezone_set($tz);
        }
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function dispatch(): string {

        if (PHP_SAPI === 'cli') {
            return $this->dispatchCli();
        }

        $router = [
            '~^/core/auth/login$~' => [
                'POST' => ['method' => 'login', 'params' => ['$php://input/json']],
            ],

            '~^/core/registration/email~' => [
                'POST' => ['method' => 'registrationEmail', 'params' => ['$php://input/json']],
            ],
            '~^/core/registration/email/check$~' => [
                'POST' => ['method' => 'registrationEmailCheck', 'params' => ['$php://input/json']],
            ],

            '~^/core/restore~' => [
                'POST' => ['method' => 'restorePass', 'params' => ['$php://input/json']],
            ],
            '~^/core/restore/check$~' => [
                'POST' => ['method' => 'restorePassCheck', 'params' => ['$php://input/json']],
            ],
        ];


        try {
            $rout = $this->getRout($router, $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

            if (empty($rout)) {
                throw new HttpException('404 Not found', 'not_found', 404);
            }

            // Обнуление
            $_GET     = [];
            $_POST    = [];
            $_REQUEST = [];
            $_FILES   = [];

            $rest = new Rest();

            if ( ! method_exists($rout['method'], '__call') && ! is_callable([$rest, $rout['method']])) {
                throw new HttpException("Incorrect method", 'incorrect_method', 500);
            }

            ob_start();
            $result = call_user_func_array([$rest, $rout['method']], $rout['params']);
            ob_clean();

            return HttpResponse::dataJson($result);


        } catch (HttpException $e) {
            return HttpResponse::errorJson($e->getMessage(), $e->getErrorCode(), $e->getCode());

        } catch (\Exception $e) {
            return HttpResponse::errorJson($e->getMessage(), $e->getCode(), 500);
        }
    }


    /**
     * The main dispatcher
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function dispatchOLD(): string {

        if (PHP_SAPI === 'cli') {
            return $this->dispatchCli();
        }

        $request = $this->parseRequest();

        if (empty($this->auth)) {
            $is_setup_mail = ! empty($this->config->system->mail) && ! empty($this->config->system->mail->server);

            // Авторизация
            if ($request->isLogin()) {
                $login    = is_string($_POST['login'])    ? $_POST['login']    : '';
                $password = is_string($_POST['password']) ? $_POST['password'] : '';
                $this->authLogin($login, $password);
                return '';

            // Забыли пароль?
            } elseif ($request->isFatgon() && $is_setup_mail) {
                header('Content-Type: text/html; charset=utf-8');
                if ( ! empty($_POST['email'])) {
                    $this->forgotPass($_POST['email']);
                }
                $theme_controller = $this->getThemeController();
                return $theme_controller->getForgotPass();

            // Сброс пароля
            } elseif ($request->isReset() && $is_setup_mail) {
                header('Content-Type: text/html; charset=utf-8');
                $new_password = ! empty($_POST['password'])  ? $_POST['password']  : '';
                $this->resetPass($_GET['token'], $new_password);
                $theme_controller = $this->getThemeController();
                return $theme_controller->getResetPass();

            } else {
                http_response_code(403);
            }
        }

        $this->logRequest($request);

        $result = '';
        ob_start();

        // Выход
        if ($request->isLogout()) {
            $result = $this->auth->logout();

        // Disable
        } elseif ($this->config->system->disable->on && ! $this->auth->isAdmin()) {
            $result = $this->getDisablePage();

        } else {
            // SetupAcl
            $this->auth->setupAcl();

            // Модуль
            if ($request->isModule()) {
                // Обработчики
                if ($request->isHandler()) {
                    $result = $this->handlerModule($request->module, $request->getHandlerMethod());

                } else {
                    if ($request->isMobile()) {
                        $result = $this->getModuleContentMobile($request->module, $request->section);
                    } else {
                        $result = $this->getModuleContent($request->module, $request->section);
                    }
                }

            // Меню
            } else {
                if ($request->isMobile()) {
                    $result = $this->getMenuMobile();

                } else {
                    $result = $this->getMenu();
                }
            }

            if (is_array($result)) {
                // header('Content-type: application/json; charset="utf-8"');
                $result = json_encode($result);
            }
        }


        $output = ob_get_clean() . $result;

        $this->logOutput($output);

        return $output;
    }


    /**
     * @param array  $routes
     * @param string $uri
     * @param string $http_method
     * @return array
     * @throws HttpException
     */
    private function getRout(array $routes, string $uri, string $http_method): array {

        $result = [];

        if ( ! empty($routes)) {
            foreach ($routes as $route_rule => $route) {
                $matches = [];

                if (preg_match($route_rule, $uri, $matches)) {

                    if ( ! is_array($route)) {
                        break;
                    }

                    if ( ! isset($route[$http_method])) {
                        throw new HttpException("Incorrect http method", 'incorrect_http_method', 405);
                    }

                    if (empty($route[$http_method]['method'])) {
                        throw new HttpException("Incorrect method", 'incorrect_method', 500);
                    }

                    $result['method'] = $route[$http_method]['method'];
                    $result['params'] = [];

                    if ( ! empty($route[$http_method]['params']) && is_array($route[$http_method]['params'])) {
                        foreach ($route[$http_method]['params'] as $param) {
                            if (is_int($param)) {
                                if (isset($matches[$param])) {
                                    $result['params'][] = $matches[$param];
                                }

                            } else {
                                switch ($param) {
                                    case '$_GET':
                                        $result['params'][] = $_GET;
                                        break;

                                    case '$_POST':
                                        $result['params'][] = $_POST;
                                        break;

                                    case '$_FILES':
                                        $result['params'][] = $_FILES;
                                        break;

                                    case '$php://input':
                                        $result['params'][] = file_get_contents('php://input', 'r');
                                        break;

                                    case '$php://input/json':
                                        $request_raw = file_get_contents('php://input', 'r');
                                        $request     = @json_decode($request_raw, true);

                                        if (json_last_error() !== JSON_ERROR_NONE) {
                                            throw new HttpException('Incorrect json data', 'incorrect_json_data', 400);
                                        }

                                        $result['params'][] = $request;
                                        break;
                                }
                            }
                        }
                    }

                    break;
                }
            }
        }

        return $result;
    }


    /**
     * Cli
     * @return string
     * @throws \Exception
     */
    private function dispatchCli(): string {

        $result  = '';

        $cli = new Cli();
        $options = $cli->getOptions();

        // Help info
        if (empty($options) || isset($options['h']) || isset($options['help'])) {
            $result = $cli->getHelp();

        // Version info
        } elseif (isset($options['v']) || isset($options['version'])) {
            $result = $this->config->system->version;

        // Getting information about installed modules
        } elseif (isset($options['d']) || isset($options['info-installed-modules'])) {
            $result = $cli->getInstalledModules();

        // Control composer
        } elseif (isset($options['c']) || isset($options['composer'])) {
            try {
                $params = isset($options['param']) ? $options['param'] : (isset($options['p']) ? $options['p'] : false);
                $params = $params === false ? [] : (is_array($params) ? $params : array($params));
                $cli->cliComposer($params);

            } catch (\Exception $e) {
                $result = $e->getMessage() . PHP_EOL;
            }

        // Getting information about available system methods
        } elseif (isset($options['n']) || isset($options['scan-cli-methods'])) {
            $result = $cli->getCliMethods();

        // Module run method
        } elseif ((isset($options['m']) || isset($options['module'])) &&
                  (isset($options['e']) || isset($options['method']))
        ) {
            $module = isset($options['module']) ? $options['module'] : $options['m'];
            $method = isset($options['method']) ? $options['method'] : $options['e'];
            $result = $cli->runCliMethod($module, $method);
        }

        return $result . PHP_EOL;
    }



    /**
     * Получение контента модуля
     * @param string $module
     * @param string $section
     * @return mixed
     * @throws \Exception
     */
    private function getModuleContent(string $module, string $section) {

        if ( ! $this->isModuleInstalled($module)) {
            throw new \Exception(sprintf($this->_("Модуль %s не установлен в системе!"), $module));
        }

        if ( ! $this->checkAcl('mod_' . $module . '_index')) {
            throw new \Exception(sprintf($this->_("У вас нет доступа к модулю %s!"), $module));
        }

        if ( ! $this->checkAcl("mod_{$module}_{$section}")) {
            throw new \Exception(sprintf($this->_("У вас нет доступа к субмодулю %s!"), $section));
        }


        $location        = $this->getModuleLocation($module);
        $controller_path = "{$location}/Controller.php";
        if ( ! file_exists($controller_path)) {
            throw new \Exception(sprintf($this->_("Файл %s не найден"), $controller_path));
        }
        require_once $controller_path;

        $class_name = __NAMESPACE__ . '\\Mod\\' . ucfirst($module) . '\\Controller';
        if ( ! class_exists($class_name)) {
            throw new \Exception(sprintf($this->_("Класс %s не найден"), $class_name));
        }

        $mod_methods    = get_class_methods($class_name);
        $section_method = 'section' . ucfirst($section);
        if (array_search($section_method, $mod_methods) === false) {
            throw new \Exception(sprintf($this->_("В классе %s не найден метод %s"), $class_name, $section_method));
        }


        $controller = new $class_name();
        return $controller->$section_method();
    }


    /**
     * Получение контента модуля
     * @param string $module
     * @param string $section
     * @return mixed
     * @throws \Exception
     */
    private function getModuleContentMobile(string $module, string $section) {

        if ( ! $this->isModuleInstalled($module)) {
            throw new \Exception(sprintf($this->_("Модуль %s не установлен в системе!"), $module));
        }

        if ( ! $this->checkAcl('mod_' . $module . '_index')) {
            throw new \Exception(sprintf($this->_("У вас нет доступа к модулю %s!"), $module));
        }

        if ( ! $this->checkAcl("mod_{$module}_{$section}")) {
            throw new \Exception(sprintf($this->_("У вас нет доступа к субмодулю %s!"), $section));
        }


        $location        = $this->getModuleLocation($module);
        $controller_path = "{$location}/Mobile.php";
        if ( ! file_exists($controller_path)) {
            throw new \Exception(sprintf($this->_("Файл %s не найден"), $controller_path));
        }
        require_once $controller_path;

        $class_name = __NAMESPACE__ . '\\Mod\\' . ucfirst($module) . '\\Mobile';
        if ( ! class_exists($class_name)) {
            throw new \Exception(sprintf($this->_("Класс %s не найден"), $class_name));
        }

        $mod_methods    = get_class_methods($class_name);
        $section_method = 'section' . ucfirst($section);
        if (array_search($section_method, $mod_methods) === false) {
            throw new \Exception(sprintf($this->_("В классе %s не найден метод %s"), $class_name, $section_method));
        }


        $controller = new $class_name();
        return $controller->$section_method();
    }


    /**
     *
     */
    private function getMenu(): array {

        return [];
    }


    /**
     *
     */
    private function getMenuMobile(): array {

        return [];
    }


    /**
     * @return array
     */
    private function getDisablePage(): array {

        $page = [
            'type'        => 'disable_page',
            'title'       => $this->config->system->disable->title,
            'description' => $this->config->system->disable->description,
        ];

        return $page;
    }


    /**
     * Сохранение данных в модулях
     *
     * @param string   $module
     * @param string   $method
     *
     * @return string
     * @throws \Exception
     */
    private function handlerModule($module, $method) {

        // Подключение файла с обработчиком
        $location = $this->getModuleLocation($module);
        $module_save_path = $location . '/Handler.php';
        if ( ! file_exists($module_save_path)) {
            throw new \Exception(sprintf($this->_('Не найден файл "%s" в модуле "%s"'), $module_save_path, $module));
        }
        require_once $module_save_path;


        // Инифиализация обработчика
        $handler_class_name = __NAMESPACE__ . '\\Mod\\' . ucfirst($module) . '\\Handler';
        if ( ! class_exists($handler_class_name)) {
            throw new \Exception(sprintf($this->_('Не найден класс "%s" в модуле "%s"'), $handler_class_name, $module));
        }


        // Выполнение обработчика
        $handler_class = new $handler_class_name();
        if ( ! method_exists($handler_class, $method)) {
            throw new \Exception(sprintf($this->_('Не найден метод "%s" в классе "%s"'), $method, $handler_class_name));
        }

        if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            $data = array();
            parse_str(file_get_contents('php://input'), $data);
        } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $data = $_GET;
        } else {
            $data = $_POST;
        }

        return $handler_class->$method($data);
    }


    /**
     * @param  string $module
     * @param  string $method
     * @return bool
     * @throws \Exception
     */
    private function issetHandlerModule($module, $method) {

        $location = $this->getModuleLocation($module);

        // Подключение файла с обработчиком
        $module_save_path = $location . '/Handler.php';
        if ( ! file_exists($module_save_path)) {
            return false;
        }
        require_once $module_save_path;


        // Инициализация обработчика
        $handler_class_name = __NAMESPACE__ . '\\Mod\\' . ucfirst($module) . '\\Handler';
        if ( ! class_exists($handler_class_name)) {
            return false;
        }

        return method_exists($handler_class_name, $method);
    }


    /**
     * Основной роутер
     * @return array
     */
    private function parseRequest() {

        $matches = [];
        $result  = [];

        // Веб-сервис (REST)
        if (preg_match('~/api/([a-z0-9_]+)/v([0-9]+\.[0-9]+)/(?:/|)([^?]*?)(?:/|)(?:\?|$)~', $_SERVER['REQUEST_URI'], $matches)) {
            $result['module']  = $matches[1];
            $result['version'] = $matches[2];
            $result['type']    = 'rest';

            if ( ! empty($matches[3])) {
                if (strpos($matches[3], '/')) {
                    $path             = explode('/', $matches[3]);
                    $result['method'] = lcfirst(implode('', array_map('ucfirst', $path)));
                } else {
                    $result['method'] = strtolower($matches[3]);
                }
            } else {
                $result['method'] = 'index';
            }


        // Веб-сервис (SOAP)
        } elseif (preg_match('~/soap/([a-z0-9_]+)/v([0-9]+\.[0-9]+)/(wsdl\.xml|)$~', $_SERVER['REQUEST_URI'], $matches)) {
            $result['module']  = $matches[1];
            $result['version'] = $matches[2];
            $result['type']    = ! empty($matches[3]) ? 'soap_wsdl' : 'soap';


        // Загрузка страницы
        } else {
            $request_uri_path = preg_replace('~\?.*~', '', $_SERVER['REQUEST_URI']);
            $request_uri_path = substr($request_uri_path, 0, strlen(DOC_PATH)) == DOC_PATH
                ? substr($request_uri_path, strlen(DOC_PATH))
                : $request_uri_path;

            $explode_request_uri = explode("/", $request_uri_path);

            $result['module']  = $explode_request_uri[0];
            $result['section'] = ! empty($explode_request_uri[1]) ? strtolower($explode_request_uri[1]) : 'index';
            $result['type']    = 'default';
        }

        return $result;
    }


    /**
     * логирование активности простых пользователей
     * @param array $exclude исключения адресов
     * @throws \Exception
     */
    private function logRequest($exclude = []) {

        if ($exclude && in_array($_SERVER['QUERY_STRING'], $exclude)) {
            return;
        }

        if ( ! empty($this->config->log) &&
            isset($this->config->log->writer) &&
            $this->config->log->writer == 'file'
        ) {
            if ( ! $this->config->log->file) {
                throw new \Exception($this->_('Не задан файл журнала запросов'));
            }

            $log = new Log('access');
            $log->access($this->auth->LOGIN);
        }


        // обновление записи о последней активности
        $where = [
            $this->db->quoteInto("refresh_token = ?", $this->auth->getRefrashToken()),
            $this->db->quoteInto("ip = ?",            $_SERVER['REMOTE_ADDR']),
            $this->db->quoteInto("user_id = ?",       $this->auth->getUser()->id),
        ];
        $this->db->update('core_session', [
            'count_requests'     => new \Zend_Db_Expr('count_requests + 1'),
            'date_last_activity' => new \Zend_Db_Expr('NOW()')
        ], $where);
    }


    /**
     * @param string $output
     * @return bool
     * @throws \Exception
     */
    private function logOutput(string $output): bool {


        if ($this->config->system->log->on &&
            $this->config->system->log->output &&
            $this->config->system->log->writer == 'file'
        ) {
            if ( ! $this->config->log->file) {
                throw new \Exception($this->_('Не задан файл журнала запросов'));
            }

            $log = new Log('access');
            $log->info($output);
        }


        return true;
    }
}