<?php
namespace Core3;

use \Zend\Session\Container as SessionContainer;

require_once 'Log.php';
require_once 'Acl.php';
require_once 'Mtpl.php';


/**
 * Class Init
 * @package Core
 */
class Init extends Acl {

    const version = '3.0.0';
    private const RP = '8c1733d4cd0841199aa02ec9362be324';

    /**
     * @var \Zend\Session\Container
     */
    protected $auth;


    /**
     * Init constructor.
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
     * @throws \Zend_Session_Exception
     * @return bool
     */
    public function auth() {

        if (PHP_SAPI === 'cli') {
            return false;
        }

        // проверяем, есть ли в запросе токен
        if (($auth = $this->authToken())) {
            //произошла авторизация по токену
            $this->auth = $auth;
            \Zend_Registry::set('auth', $this->auth);
            return false;
        }

        $this->auth = new SessionContainer('auth');
        if ( ! isset($this->auth->initialized)) {
            //регенерация сессии для предотвращения угона
            $this->auth->getManager()->regenerateId();
            $this->auth->initialized = true;
        }

        // сохранение сессии в реестре
        \Zend_Registry::set('auth', $this->auth);

        if ( ! empty($this->auth->ID) && $this->auth->ID > 0) {
            //is user active right now
            if ($this->isUserActive($this->auth->ID) &&
                isset($this->auth->accept_answer) &&
                $this->auth->accept_answer === true
            ) {
                $session_lifetime = $this->getSetting('session_lifetime');
                if ($session_lifetime) {
                    $this->auth->setExpirationSeconds($session_lifetime, "accept_answer");
                }
                //$this->auth->lock();
            } else {
                $this->closeSession();
                \Zend_Session::destroy();
                //header("Location: /");
            }
            \Zend_Registry::set('auth', $this->auth);
        }

        return true;
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


        // Веб-сервис (REST)
        if ($request['type'] == 'rest') {
            return $this->dispatchRest($request['module'], $request['method'], $request['version']);
        }


        // Веб-сервис (SOAP)
        if ($request['type'] == 'soap' || $request['type'] == 'soap_wsdl') {
            return $this->dispatchSoap($request['module'], $request['type'], $request['version']);
        }


        // Billing
        if ($this->isModuleActive('billing') && ($disable_page = $this->getBillingDisablePage())) {
            return $disable_page;
        }


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
        $options = getopt('m:e:p:t:ndcvh', [
            'module:',
            'method:',
            'param:',
            'host:',
            'scan-cli-methods',
            'info-installed-modules',
            'composer',
            'version',
            'help',
        ]);

        // Help info
        if (empty($options) || isset($options['h']) || isset($options['help'])) {
            $result = implode(PHP_EOL, [
                'Core 3',
                'Usage: php index.php [OPTIONS]',
                '',
                'Optional arguments:',
                " -m    --module                 Module name",
                " -e    --method                 Cli method name",
                " -p    --param                  Parameter in command",
                " -t    --host                   Section name in config file",
                " -n    --scan-cli-methods       Getting information about available system methods",
                " -d    --info-installed-modules Getting information about installed modules",
                " -c    --composer               Control composer",
                " -h    --help                   Help info",
                " -v    --version                Version info",
                '',
                "Examples of usage:",
                "php index.php --module cron --method run",
                "php index.php --module cron --method run --host site.com",
                "php index.php --module cron --method runJob --param 123 --param abc",
                "php index.php --composer --param update",
                "php index.php --composer --param search --param monolog",
                "php index.php --version",
                "php index.php --scan-cli-methods",
            ]);

        // Version info
        } elseif (isset($options['v']) || isset($options['version'])) {
            $result = $this::version;

        // Getting information about installed modules
        } elseif (isset($options['d']) || isset($options['info-installed-modules'])) {
            $modules = $this->db->fetchAll("
                SELECT m.name,
                       m.title,
                       m.version,
                       m.is_visible_sw,
                       m.is_system_sw,
                       m.is_active_sw
                FROM core_modules AS m
                ORDER BY m.seq
            ");

            $result = "Name | Title | Version | Is visible? | Is system? | Is active?" . PHP_EOL . PHP_EOL;
            if ( ! empty($modules)) {
                foreach ($modules as $module) {
                    $result .= implode("\t ",$module) . PHP_EOL;
                }
            }

        // Control composer
        } elseif (isset($options['c']) || isset($options['composer'])) {
            try {
                $params = isset($options['param']) ? $options['param'] : (isset($options['p']) ? $options['p'] : false);
                $params = $params === false ? [] : (is_array($params) ? $params : array($params));
                $this->cliComposer($params);

            } catch (\Exception $e) {
                $result = $e->getMessage() . PHP_EOL;
            }

        // Getting information about available system methods
        } elseif (isset($options['n']) || isset($options['scan-cli-methods'])) {
            $result = $this->cliScanMethods();

        // Module run method
        } elseif ((isset($options['m']) || isset($options['module'])) &&
                  (isset($options['e']) || isset($options['method']))
        ) {
            $module = isset($options['module']) ? $options['module'] : $options['m'];
            $method = isset($options['method']) ? $options['method'] : $options['e'];
            $result = $this->cliModuleRun($module, $method);
        }

        return $result . PHP_EOL;
    }


    /**
     * REST
     * @param string $module_name
     * @param string $method
     * @param float $version
     * @return mixed
     * @throws \Exception
     */
    private function dispatchRest($module_name, $method, $version = 1.0) {

        $this->setContext('webservice');

        // Инициализация модуля вебсервиса
        if ( ! $this->isModuleActive('webservice')) {
            throw new \Exception(sprintf($this->_("Модуль %s не активен"), 'Webservice'), 503);
        }

        $location        = $this->getModuleLocation('webservice');
        $controller_path = $location . '/Rest.php';

        if ( ! file_exists($controller_path)) {
            throw new \Exception(sprintf($this->_("Файл %s не найден"), $controller_path), 500);
        }
        require_once($controller_path);

        $class_name = __NAMESPACE__ . '\\Mod\\Webservice\\Rest';
        if ( ! class_exists($class_name)) {
            throw new \Exception(sprintf($this->_("Класс %s не найден"), $class_name), 500);
        }

        $mod_methods = get_class_methods($class_name);
        if ( ! array_search('dispatchRest', $mod_methods)) {
            throw new \Exception(sprintf($this->_("В классе %s не найден метод %s"), 'dispatchRest', $class_name));
        }

        $webservice_controller = new $class_name();
        return $webservice_controller->dispatchRest($module_name, $method, $version);
    }


    /**
     * SOAP
     * @param string $module_name
     * @param string $type
     * @param float $version
     * @return mixed
     * @throws \Exception
     */
    private function dispatchSoap($module_name, $type, $version = 1.0) {

        $this->setContext('webservice');

        // Инициализация модуля вебсервиса
        if ( ! $this->isModuleActive('webservice')) {
            throw new \Exception($this->_("Модуль Webservice не активен"));
        }


        $location        = $this->getModuleLocation('webservice');
        $controller_path = $location . '/Soap.php';

        if ( ! file_exists($controller_path)) {
            throw new \Exception(sprintf($this->_("Файл %s не найден"), $controller_path));
        }
        require_once($controller_path);

        $class_name = __NAMESPACE__ . '\\Mod\\Webservice\\Soap';
        if ( ! class_exists($class_name)) {
            throw new \Exception(sprintf($this->_("Класс %s не найден"), $class_name));
        }

        $mod_methods = get_class_methods($class_name);
        if ( ! array_search('dispatchSoap', $mod_methods)) {
            throw new \Exception(sprintf($this->_("В классе %s не найден метод %s"), 'dispatchSoap', $class_name));
        }

        $soap_controller = new $class_name();
        return $soap_controller->dispatchSoap($module_name, $type, $version);
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
     * Получение контроллера темы
     * @throws \Exception
     * @return \Core\Theme
     */
    private function getThemeController() {

        $controller_path = __DIR__ . '/../themes/' . $this->config->system->theme . '/Controller.php';

        if ( ! file_exists($controller_path)) {
            throw new \Exception(sprintf($this->_("Файл %s не найден"), $controller_path), 500);
        }
        require_once($controller_path);

        $class_name = __NAMESPACE__ . '\\Theme\\' . ucfirst(strtolower($this->config->system->theme)) .'\\Controller';
        if ( ! class_exists($class_name)) {
            throw new \Exception(sprintf($this->_("Класс %s не найден"), $class_name), 500);
        }

        return new $class_name();
    }


    /**
     * Billing
     * @return string
     * @throws \Exception
     */
    private function getBillingDisablePage() {

        $this->setContext('billing');

        $location             = $this->getModuleLocation('billing');
        $billing_disable_path = $location . '/classes/Billing_Disable.php';

        if ( ! file_exists($billing_disable_path)) {
            throw new \Exception(sprintf($this->_("Файл %s не найден"), $billing_disable_path));
        }
        require_once($billing_disable_path);


        $class_name = __NAMESPACE__ . '\\Mod\\Billing\\Disable';
        if ( ! class_exists($class_name)) {
            throw new \Exception(sprintf($this->_("Класс %s не найден"), $class_name));
        }

        $mod_methods = get_class_methods($class_name);
        if ( ! array_search('isDisable', $mod_methods)) {
            throw new \Exception(sprintf($this->_("В классе %s не найден метод %s"), $class_name, 'isDisable'));
        }

        if ( ! array_search('getDisablePage', $mod_methods)) {
            throw new \Exception(sprintf($this->_("В классе %s не найден метод %s"), $class_name, 'getDisablePage'));
        }


        $billing_disable = new $class_name();
        if ($billing_disable->isDisable()) {
            return $billing_disable->getDisablePage();
        } else {
            return '';
        }
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
     * Установка контекста выполнения скрипта
     * @param string $module
     * @param string $section
     */
    private function setContext($module, $section = 'index') {
        \Zend_Registry::set('context', array($module, $section));
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
     *
     * @return \stdClass|string
     * @throws \Exception
     */
    private function authToken() {

        $token = '';
        if ( ! empty($_SERVER['HTTP_AUTHORIZATION'])) {
            if (strpos('Bearer', $_SERVER['HTTP_AUTHORIZATION']) !== 0) {
                return '';
            }
            $token = $_SERVER['HTTP_AUTHORIZATION'];

        } else if ( ! empty($_SERVER['HTTP_CORE3M'])) {
            $token = $_SERVER['HTTP_CORE3M'];
        }

        if ($token) {
            //Необходимо для правильной работы контроллера
            $this->setContext('webservice');

            if ( ! $this->isModuleActive('webservice')) {
                throw new \Exception(sprintf($this->_('Модуль %s не активен'), 'Webservice'), 503);
            }

            $webservice_controller_path = $this->getModuleLocation('webservice') . '/Controller.php';
            if ( ! file_exists($webservice_controller_path)) {
                throw new \Exception(sprintf($this->_('Файл %s не найден'), 'Webservice'), 500);
            }
            require_once($webservice_controller_path);

            $class_name = __NAMESPACE__ . '\\Mod\\Webservice\\Controller';
            if ( ! class_exists($class_name)) {
                throw new \Exception(sprintf($this->_('Класс %s не найден'), 'Webservice'), 500);
            }

            $webservice_controller = new $class_name();
            return $webservice_controller->dispatchWebToken($token);
        }
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


    /**
     * Авторизация пользователя через форму
     * @param string $login
     * @param string $password
     */
    private function authLogin($login, $password) {

        $namespace_block = new SessionContainer('Block');

        try {
            if ( ! empty($namespace_block->blocked)) {
                throw new \Exception($this->_("Ваш доступ временно заблокирован!"));
            }

            //ldap
            $auth_LDAP = false;
            if ($login === 'root') {
                $user = [
                    'id'    => -1,
                    'login' => 'root',
                    'pass'  => self::RP
                ];

            } elseif ( ! empty($this->config->ldap) && ! empty($this->config->ldap->active)) {
                require_once 'LdapAuth.php';

                $ldapAuth = new LdapAuth();
                $ldapAuth->auth($login, $password);
                $ldap_status = $ldapAuth->getStatus();

                switch ($ldap_status) {
                    case LdapAuth::ST_LDAP_AUTH_SUCCESS :
                        $auth_LDAP = true;
                        $user_data = $ldapAuth->getUserData();
                        $login     = $user_data['login'];

                        if (isset($user_data['root']) && $user_data['root'] === true) {
                            $user = [
                                'id'    => -1,
                                'login' => 'root',
                                'pass'  => self::RP
                            ];

                        } else {
                            $user_id = $this->db->fetchOne("
                                SELECT id
                                FROM core_users
                                WHERE login = ?
                            ", $login);

                            if ( ! $user_id) {
                                //create new user
                                $this->db->insert('core_users', [
                                    'is_active_sw' => 'Y',
                                    'is_admin_sw'  => $user_data['admin'] ? 'Y' : 'N',
                                    'login'        => $login,
                                    'date_created' => new \Zend_Db_Expr('NOW()'),
                                ]);

                            } elseif ($user_data['admin']) {
                                $where = $this->db->quoteInto('id = ?', $user_id);
                                $this->db->update('core_users', array(
                                    'is_admin_sw' => 'Y'
                                ), $where);
                            }
                        }
                        break;

                    case LdapAuth::ST_LDAP_USER_NOT_FOUND :
                        $password = md5($password);
                        break;

                    case LdapAuth::ST_LDAP_INVALID_PASSWORD :
                        throw new \Exception($this->_("Неверный пароль или пользователь отключён"));
                        break;

                    case LdapAuth::ST_ERROR :
                        throw new \Exception($this->_("Ошибка LDAP: ") . $ldapAuth->getMessage());
                        break;

                    default:
                        throw new \Exception($this->_("Неизвестная ошибка авторизации по LDAP"));
                        break;
                }
            }


            if (empty($user)) {
                $user = $this->db->fetchRow("
                    SELECT u.id, 
                           u.login,
                           u.email,
                           u.pass,
                           u.lastname,
                           u.firstname,
                           u.middlename,
                           u.is_admin_sw,
                           r.name AS role,
                           u.role_id
                    FROM core_users AS u
                        LEFT JOIN core_roles AS r ON r.id = u.role_id
                    WHERE u.is_active_sw = 'Y' 
                      AND u.login = ? 
                    LIMIT 1
                ", $login);
            }


            if ($user) {
                if ( ! $auth_LDAP && $user['pass'] !== Tools::pass_salt($password)) {
                    throw new \Exception($this->_("Неверный пароль"));

                } else {
                    $authNamespace = \Zend_Registry::get('auth');
                    $authNamespace->accept_answer = true;

                    if (($session_lifetime = $this->getSetting('session_lifetime'))) {
                        $authNamespace->setExpirationSeconds($session_lifetime, "accept_answer");
                    }

                    if (session_id() == 'deleted') {
                        throw new \Exception($this->_("Ошибка сохранения сессии. Проверьте настройки системного времени."));
                    }

                    $authNamespace->ID     = (int)$user['id'];
                    $authNamespace->LOGIN  = isset($user['login'])      ? $user['login']      : '';
                    $authNamespace->EMAIL  = isset($user['email'])      ? $user['email']      : '';
                    $authNamespace->LN     = isset($user['lastname'])   ? $user['lastname']   : '';
                    $authNamespace->FN     = isset($user['firstname'])  ? $user['firstname']  : '';
                    $authNamespace->MN     = isset($user['middlename']) ? $user['middlename'] : '';
                    $authNamespace->ROLE   = isset($user['role'])       ? $user['role']       : -1;
                    $authNamespace->ROLEID = isset($user['role_id'])    ? $user['role_id']    : -1;


                    if ($authNamespace->LOGIN == 'root') {
                        $authNamespace->ADMIN = true;
                    } else {
                        $authNamespace->ADMIN = isset($user['is_admin_sw']) && $user['is_admin_sw'] == 'Y' ? true : false;
                    }

                    $this->storeSession($authNamespace);

                    $authNamespace->LDAP = $auth_LDAP;
                    $authNamespace->lock();
                }
            } else {
                throw new \Exception($this->_("Нет такого пользователя"));
            }


        } catch(\Exception $e) {
            header("HTTP/1.1 400 Bad Request");

            if (isset($namespace_block->numberOfPageRequests)) {
                $namespace_block->numberOfPageRequests++;
            } else {
                $namespace_block->numberOfPageRequests = 1;
            }

            if ($namespace_block->numberOfPageRequests > 5) {
                $namespace_block->blocked = time();
                $namespace_block->setExpirationSeconds(10);
                $namespace_block->numberOfPageRequests = 1;
            }
        }

        return;
    }


    /**
     * Восстановление пароля
     * @param string $email
     */
    private function forgotPass($email) {

        $namespace_error   = new \Zend_Session_Namespace('Error');
        $namespace_success = new \Zend_Session_Namespace('Success');
        $namespace_block   = new \Zend_Session_Namespace('Block');

        try {
            $user_id = $this->db->fetchOne("
                SELECT id
                FROM core_users
                WHERE is_active_sw = 'Y' 
                  AND email = ? 
                LIMIT 1
            ", $email);


            if ($user_id) {
                $token = md5(uniqid());
                $where = $this->db->quoteInto('id = ?', $user_id);
                $this->db->update('core_users', [
                    'pass_reset_token' => $token,
                    'pass_reset_date'  => new \Zend_Db_Expr('NOW() + INTERVAL 8 HOUR'),
                ], $where);

                $protocol    = $this->config->system->https ? 'https' : 'http';
                $host        = ! empty($this->config->system->host) ? $this->config->system->host : $_SERVER['SERVER_NAME'];
                $reset_link  = "{$protocol}://{$host}" . DOC_PATH . "index.php?page=reset&token={$token}";
                $system_name = isset($this->config->system->name)
                    ? $this->config->system->name
                    : (isset($this->config->system->host) ? $this->config->system->host : $_SERVER['SERVER_NAME']);


                $theme_controller = $this->getThemeController();
                $body             = $theme_controller->getResetPassEmail($reset_link);

                require_once 'Email.php';
                $class_email = new Email();
                $class_email->to($email)
                    ->subject($this->_("Восстановление пароля на сайте {$system_name}"))
                    ->body($body)
                    ->send(true);

                $namespace_success->message = $this->_("На указанный email было отправлено сообщение с информацией по востановлению пароля");
                $namespace_success->email   = $email;
                $namespace_success->setExpirationHops(1);
            } else {
                throw new \Exception($this->_("Пользователь с таким email не найден"));
            }


        } catch(\Exception $e) {
            header("HTTP/1.1 400 Bad Request");
            $namespace_error->message = $e->getMessage();
            $namespace_error->email   = $email;

            if (isset($namespace_error->numberOfPageRequests)) {
                $namespace_error->numberOfPageRequests++;
            } else {
                $namespace_error->numberOfPageRequests = 1;
            }

            if ($namespace_error->numberOfPageRequests > 5) {
                $namespace_block->blocked = time();
                $namespace_block->setExpirationSeconds(10);
                $namespace_error->numberOfPageRequests = 1;
            }
        }
    }


    /**
     * Установка нового пароля
     * @param string $token
     * @param string $new_password
     */
    private function resetPass($token, $new_password = '') {

        $namespace_error   = new \Zend_Session_Namespace('Error');
        $namespace_success = new \Zend_Session_Namespace('Success');
        $namespace_block   = new \Zend_Session_Namespace('Block');

        try {
            $user = $this->db->fetchRow("
                SELECT id,
                       pass_reset_date
                FROM core_users AS u
                WHERE is_active_sw = 'Y' 
                  AND pass_reset_token = ?
                  AND pass_reset_date IS NOT NULL 
                  AND pass_reset_date != '' 
                LIMIT 1
            ", $token);

            if (empty($user)) {
                throw new \Exception($this->_("Ошибка. Возможно ссылка устарела. Повторьте процедуру восстановения пароля."));

            } elseif ($user['pass_reset_date'] <= date('Y-m-d H:i:s')) {
                throw new \Exception($this->_("Ссылка для сброса пароля устарела. Повторьте процедуру восстановения пароля."));

            } elseif ( ! empty($user) && $new_password) {
                $where = $this->db->quoteInto('id = ?', $user['id']);
                $this->db->update('core_users', [
                    'pass'             => Tools::pass_salt($new_password),
                    'pass_reset_token' => new \Zend_Db_Expr('NULL'),
                    'pass_reset_date'  => new \Zend_Db_Expr('NULL')
                ], $where);
                $namespace_success->message = $this->_('Пароль успешно изменен!');
                $namespace_success->setExpirationHops(1);
            }


        } catch(\Exception $e) {
            header("HTTP/1.1 400 Bad Request");
            $namespace_error->message = $e->getMessage();

            if (isset($namespace_error->numberOfPageRequests)) {
                $namespace_error->numberOfPageRequests++;
            } else {
                $namespace_error->numberOfPageRequests = 1;
            }

            if ($namespace_error->numberOfPageRequests > 5) {
                $namespace_block->blocked = time();
                $namespace_block->setExpirationSeconds(10);
                $namespace_error->numberOfPageRequests = 1;
            }
        }
    }


    /**
     * @param array $params
     * @throws \Exception
     */
    private function cliComposer($params) {

        $temp_dir = sys_get_temp_dir();
        if ( ! is_writable($temp_dir)) {
            throw new \Exception(sprintf("Error. Folder %s not writable.", $temp_dir));
        }
        if ( ! is_writable(__DIR__ . '/..')) {
            throw new \Exception(sprintf("Error. Folder %s not writable.", realpath(__DIR__ . '/..')));
        }

        $composer_setup_file = $temp_dir . '/' . uniqid() . '-composer-setup.php';
        echo 'Download composer installer...' . PHP_EOL;

        if ( ! copy('https://getcomposer.org/installer', $composer_setup_file)) {
            throw new \Exception('Fail download composer installer.');
        }

        $composer_signature = trim(file_get_contents('https://composer.github.io/installer.sig'));
        if ( ! $composer_signature) {
            unlink($composer_setup_file);
            throw new \Exception('Fail download composer signature.');
        }

        if (hash_file('SHA384', $composer_setup_file) !== $composer_signature) {
            unlink($composer_setup_file);
            throw new \Exception('Error. Composer installer corrupt.');
        }

        echo 'Composer Installer verified.' . PHP_EOL;
        echo 'Install composer...' . PHP_EOL;


        $old_cwd  = getcwd();
        $php_path = exec('which php') ?: 'php';
        chdir(__DIR__ . '/..');
        echo shell_exec(sprintf('%s %s', $php_path, $composer_setup_file));

        $cmd = sprintf('%s composer.phar %s', $php_path, implode(' ', $params));
        echo 'Run command: ' . $cmd . PHP_EOL . PHP_EOL;
        echo shell_exec($cmd);

        $composer_file = __DIR__ . '/../composer.phar';
        if ( ! unlink($composer_file)) {
            echo sprintf('Warning. Could not delete the file %s', $composer_file) . PHP_EOL;
        }
        if ( ! unlink($composer_setup_file)) {
            echo sprintf('Warning. Could not delete the file %s', $composer_setup_file) . PHP_EOL;
        }

        chdir($old_cwd);
    }


    /**
     * @return string
     */
    private function cliScanMethods() {

        $cli_modules = [];
        $modules     = $this->db->fetchAll("
            SELECT m.name,
                   m.title
            FROM core_modules AS m
            WHERE m.is_active_sw = 'Y'
            ORDER BY m.seq
        ");

        if ( ! empty($modules)) {
            foreach ($modules as $module) {
                $location        = $this->getModuleLocation($module['name']);
                $controller_name = __NAMESPACE__ . '\\Mod\\' . ucfirst($module['name']) . '\\Cli';
                $controller_path = "{$location}/Cli.php";

                if ( ! file_exists($controller_path)) {
                    continue;
                }
                require_once $controller_path;

                if ( ! class_exists($controller_name)) {
                    continue;
                }

                $mod_methods = get_class_methods($controller_name);

                if ( ! empty($mod_methods)) {
                    foreach ($mod_methods as $mod_method) {
                        if (strpos($mod_method, '__') !== 0) {
                            $reflection        = new \ReflectionMethod($controller_name, $mod_method);
                            $reflection_params = $reflection->getParameters();
                            $params = [];
                            if ( ! empty($reflection_params)) {
                                foreach ($reflection_params as $reflection_param) {
                                    $params[] = '$'.$reflection_param->getName();
                                }
                            }
                            $cli_modules[$module['name']][$mod_method] = [
                                'doc'    => $reflection->getDocComment(),
                                'params' => $params
                            ];
                        }
                    }
                }
            }
        }

        $result = "Module | Method | Params | Description" . PHP_EOL . PHP_EOL;
        if ( ! empty($cli_modules)) {
            foreach ($cli_modules as $module_name => $cli_methods) {
                foreach ($cli_methods as $method_name => $method_options) {
                    $params      = implode(', ', $method_options['params']);
                    $description = str_replace(["/**", "*/", "*", "\r\n", "\n"], ' ', $method_options['doc']);
                    $description = preg_replace('~\s{2,}~', ' ', trim($description));
                    $result .= "{$module_name}\t {$method_name}\t {$params}\t {$description}" . PHP_EOL;
                }
            }
        }

        return $result;
    }


    /**
     * @param string $module
     * @param string $method
     * @return string
     * @throws \Exception
     */
    private function cliModuleRun($module, $method) {

        $module = strtolower($module);
        $method = strtolower($method);

        $this->setContext($module, $method);

        $params = isset($options['param']) ? $options['param'] : (isset($options['p']) ? $options['p'] : false);
        $params = $params === false ? [] : (is_array($params) ? $params : array($params));


        if ( ! $this->isModuleInstalled($module)) {
            throw new \Exception(sprintf("Модуль %s не найден", $module));
        }

        if ( ! $this->isModuleActive($module)) {
            throw new \Exception(sprintf("Модуль %s не активен", $module));
        }

        $location        = $this->getModuleLocation($module);
        $controller_name = __NAMESPACE__ . '\\Mod\\' . ucfirst($module) . '\\Cli';
        $controller_path = "{$location}/Cli.php";

        if ( ! file_exists($controller_path)) {
            throw new \Exception(sprintf("Файл %s не найден", $controller_path));
        }
        require_once $controller_path;

        if ( ! class_exists($controller_name)) {
            throw new \Exception(sprintf("Класс %s не найден", $controller_name));
        }

        $mod_methods = get_class_methods($controller_name);
        $cli_method  = ucfirst($method);
        if ( ! array_search($cli_method, $mod_methods)) {
            throw new \Exception(sprintf("В классе %s не найден метод %s", $controller_name, $cli_method));
        }

        $controller = new $controller_name();
        $result     = call_user_func_array(array($controller, $cli_method), $params);

        if (is_scalar($result)) {
            return $result . PHP_EOL;
        }

        return '';
    }
}