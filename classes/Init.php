<?php
namespace Core3\Classes;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\UnencryptedToken;



/**
 * Class Init
 * @package Core
 */
class Init extends Acl {

    private const RP = '8c1733d4cd0841199aa02ec9362be324';

    /**
     * @var Auth
     */
    protected Auth $auth;


    /**
     * Init constructor.
     * @throws \Exception
     */
    public function __construct() {
        parent::__construct();

        if (empty($_SERVER['HTTPS'])) {
            if (isset($this->config->system) && ! empty($this->config->system->https)) {
                header('Location: https://' . $_SERVER['SERVER_NAME']);
            }
        }

        $tz = ! empty($this->config->system) && ! empty($this->config->system->timezone)
            ? $this->config->system->timezone
            : '';

        if ( ! empty($tz)) {
            date_default_timezone_set($tz);
        }
    }


    /**
     * Общая проверка аутентификации
     * @return bool
     * @throws \Exception
     */
    public function auth(): bool {

        if (PHP_SAPI === 'cli') {
            return false;
        }

        // проверяем, есть ли в запросе токен
        $auth = $this->getAuthByToken();

        if ($auth instanceof Auth) {
            //произошла авторизация по токену
            $this->auth = $auth;
            \Zend_Registry::set('auth', $this->auth);
            return true;
        }

        return false;
    }


    /**
     * The main dispatcher
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function dispatch() {

        if (PHP_SAPI === 'cli') {
            return $this->dispatchCli();
        }

        $request = $this->parseRequest();


        if ( ! empty($this->auth) && ! empty($this->auth->ID) && ! empty($this->auth->LOGIN)) {
            // Выход
            if (isset($_GET['logout'])) {
                $this->closeSession();
                \Zend_Session::destroy();
                header("Location: " . DOC_PATH);
                return '';

            // SetupAcl
            } else {
                if ($this->config->system->disable->on && ! $this->auth->ADMIN) {
                    $theme_controller = $this->getThemeController();
                    return $theme_controller->getDisablePage();
                }

                $this->logActivity(['/profile/index/unread']);
                $this->setupAcl();
            }

        } else {
            $page          = isset($_GET['page']) ? $_GET['page'] : '';
            $is_setup_mail = ! empty($this->config->system->mail) && ! empty($this->config->system->mail->server);

            // Авторизация
            if (isset($_POST['login']) && isset($_POST['password'])) {
                $login    = is_string($_POST['login'])    ? $_POST['login']    : '';
                $password = is_string($_POST['password']) ? $_POST['password'] : '';
                $this->authLogin($login, $password);
                header("Location: " . $_SERVER['REQUEST_URI']);
                return '';

            // Забыли парль?
            } elseif ($page == 'forgot' && $is_setup_mail) {
                header('Content-Type: text/html; charset=utf-8');
                if ( ! empty($_POST['email'])) {
                    $this->forgotPass($_POST['email']);
                }
                $theme_controller = $this->getThemeController();
                return $theme_controller->getForgotPass();

            // Сброс пароля
            } elseif ($page == 'reset' && ! empty($_GET['token']) && $is_setup_mail) {
                header('Content-Type: text/html; charset=utf-8');
                $new_password = ! empty($_POST['password'])  ? $_POST['password']  : '';
                $this->resetPass($_GET['token'], $new_password);
                $theme_controller = $this->getThemeController();
                return $theme_controller->getResetPass();

            // Форма входа
            }  else {
                if ( ! empty($request['module'])) {
                    header('HTTP/1.1 403 Forbidden');
                }
                header('Content-Type: text/html; charset=utf-8');
                $theme_controller = $this->getThemeController();
                return $theme_controller->getLogin();
            }
        }


        $module  = ! empty($request['module'])  ? $request['module']  : 'dashboard';
        $section = ! empty($request['section']) ? $request['section'] : 'index';


        // Обработчики
        $connection = $this->db->getConnection();
        \CoreUI\Registry::setDbConnection($connection);
        \CoreUI\Registry::setLanguage('ru');
        $handler = new \CoreUI\Handlers();
        if ($handler->isHandler()) {

            $process  = $handler->getProcess();
            $resource = $handler->getResource();

            if ( ! empty($module)) {
                $handler_method = $process . ucfirst(strtolower($resource));
                if ($this->issetHandlerModule($module, $handler_method)) {
                    $result = $this->handlerModule($module, $handler_method);
                } else {
                    $handler->process();
                    $result = $handler->getResponse();
                }
            } else {
                $handler->process();
                $result = $handler->getResponse();
            }

            return $result;
        }

        // Меню
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $theme_controller = $this->getThemeController();
            if ($this->auth->MOBILE) {
                header('Content-type: application/json; charset="utf-8"');
                return $theme_controller->getMenuMobile();

            } else {
                header('Content-Type: text/html; charset=utf-8');
                return $theme_controller->getMenu();
            }

        // Модуль
        } else {
            ob_start();
            if ($this->auth->MOBILE) {
                $page_content = $this->getModuleMobileContent($module, $section);
            } else {
                $page_content = $this->getModuleContent($module, $section);
            }

            return ob_get_clean() . implode('', $page_content['content']);
        }
    }


    /**
     * Cli
     * @return string
     * @throws \Exception
     */
    private function dispatchCli() {

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

     * @return string
     * @throws \Exception
     */
    private function getModuleContent($module, $section) {

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

     * @return string
     * @throws \Exception
     */
    private function getModuleMobileContent($module, $section) {

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


        // Инифиализация обработчика
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
     * Проверка наличия токена в запросе
     * @return \stdClass|bool
     * @throws \Exception
     */
    private function getAuthByToken() {

        $token = '';
        if ( ! empty($_SERVER['HTTP_AUTHORIZATION'])) {
            if (strpos('Bearer', $_SERVER['HTTP_AUTHORIZATION']) !== 0) {
                return false;
            }

            $token = $_SERVER['HTTP_AUTHORIZATION'];

        } else if ( ! empty($_SERVER['HTTP_CORE3'])) {
            $token = $_SERVER['HTTP_CORE3'];
        }

        if ($token) {
            try {
                $signer        = new Sha256();
                $sign          = $this->config->system->auth->token->sign;
                $configuration = Configuration::forSymmetricSigner($signer, $sign);

                $token_jwt     = $configuration->parser()->parse((string)$token);
                $token_exp     = $token_jwt->claims()->get('exp');
                $token_user_id = $token_jwt->claims()->get('uid');

                if (empty($token_exp) || empty($token_user_id)) {
                    return false;
                }

                $now = date_create();
                if ($now > $token_exp) {
                    return false;
                }

                $session = $this->modAdmin->getSessionByToken($token);
                $user    = $session->getUser();

                $session->addRequest();

                return $user;


            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }


    /**
     * логирование активности простых пользователей
     * @param array $exclude исключения адресов
     * @throws \Exception
     */
    private function logActivity($exclude = []) {

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
            $this->db->quoteInto("sid = ?",     $this->auth->getManager()->getId()),
            $this->db->quoteInto("ip = ?",      $_SERVER['REMOTE_ADDR']),
            $this->db->quoteInto("user_id = ?", $this->auth->ID),
            'logout_time IS NULL'
        ];
        $this->db->update('core_session', [
            'last_activity' => new \Zend_Db_Expr('NOW()')
        ], $where);
    }
}