<?php
namespace Core3\Classes;
use Core3\Mod\Admin;
use Core3\Exceptions\HttpException;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


/**
 *
 */
class Rest extends Common {


    /**
     * @return mixed
     * @throws HttpException
     */
    public function dispatch(): mixed {

        $router = [
            '~^/core/auth/login$~' => [
                'POST' => ['method' => 'login', 'params' => ['$php://input/json']],
            ],
            '~^/core/auth/logout$~' => [
                'PUT' => ['method' => 'login', 'params' => ['$php://input/json']],
            ],
            '~^/core/auth/refresh$~' => [
                'POST' => ['method' => 'refreshToken', 'params' => ['$php://input/json']],
            ],

            '~^/core/registration/email$~'        => [
                'POST' => ['method' => 'registrationEmail', 'params' => ['$php://input/json']],
            ],
            '~^/core/registration/email/check$~' => [
                'POST' => ['method' => 'registrationEmailCheck', 'params' => ['$php://input/json']],
            ],

            '~^/core/restore$~'        => [
                'POST' => ['method' => 'restorePass', 'params' => ['$php://input/json']],
            ],
            '~^/core/restore/check$~' => [
                'POST' => ['method' => 'restorePassCheck', 'params' => ['$php://input/json']],
            ],

            '~^/core/cabinet$~' => [
                'GET' => ['method' => 'getCabinet', 'params' => []],
            ],

            '~^/core/mod/([a-z0-9_]+)/([a-z0-9_]+)~' => [
                'GET' => ['method' => 'getModule', 'params' => [1, 2]],
            ],
        ];


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

        return call_user_func_array([$rest, $rout['method']], $rout['params']);
    }


    /**
     * Авторизация по email
     * @param array $params
     * @return array
     * @throws \Exception
     * @throws \Zend_Db_Adapter_Exception|\Zend_Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @OA\Post(
     *   path    = "/client/auth/email",
     *   tags    = { "Доступ" },
     *   summary = "Авторизация по email",
     *   @OA\RequestBody(
     *     description = "Данные для входа",
     *     required    = true,
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(type = "object", example = { "email" = "client@gmail.com", "password" = "197nmy4t70yn3v285v2n30304m3v204304" })
     *     )
     *   ),
     *   @OA\Response(
     *     response    = "200",
     *     description = "Вебтокен клиента",
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(type = "object", example = { "wetoken" = "xxxxxxxxxxxxxx" } )
     *     )
     *   ),
     *   @OA\Response(
     *     response    = "400",
     *     description = "Отправленные данные некорректны",
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(ref = "#/components/schemas/Error")
     *     )
     *   )
     * )
     */
    public function login(array $params): array {

        HttpValidator::testParameters([
            'login'    => 'req,string',
            'password' => 'req,string',
            'fp'       => 'req,string',
        ], $params);

        $user = $this->modAdmin->modelUsers->getRowByLoginEmail($params['login']);

        if ( ! $user) {
            throw new HttpException($this->_('Пользователя с таким логином нет'), 'login_not_found', 400);
        }

        if ($user->is_active_sw == 'N') {
            throw new HttpException($this->_('Этот пользователь деактивирован'), 'user_inactive', 400);
        }

        if ($user->pass != Tools::passSalt($params['password'])) {
            throw new HttpException($this->_('Неверный пароль'), 'pass_incorrect', 400);
        }


        return $this->createSession($user, $params['fp']);
    }


    /**
     * Обновление токена
     * @param array $params
     * @return array
     * @throws HttpException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function refreshToken(array $params): array {

        HttpValidator::testParameters([
            'refresh_token' => 'req,string',
            'fp'            => 'req,string',
        ], $params);


        $sign      = $this->config?->system?->auth?->token_sign ?: '';
        $algorithm = $this->config?->system?->auth?->algorithm ?: 'HS256';

        try {
            $decoded    = JWT::decode($params['refresh_token'], new Key($sign, $algorithm));
            $session_id = $decoded['sid'] ?? 0;
            $token_iss  = $decoded['iss'] ?? 0;
            $token_exp  = $decoded['ext'] ?? 0;

        } catch (\Exception $e) {
            throw new HttpException($this->_('Токен не прошел валидацию'), 'token_invalid', 403);
        }

        if (empty($session_id) || ! is_numeric($session_id)) {
            throw new HttpException($this->_('Некорректный токен'), 'token_incorrect', 400);
        }

        if ($token_exp < time() ||
            $token_iss != $_SERVER['SERVER_NAME']
        ) {
            throw new HttpException($this->_('Эта сессия больше не активна. Войдите заново'), 'session_inactive', 403);
        }



        $session = $this->modAdmin->modelUsersSession->find($session_id)->current();

        if (empty($session)) {
            throw new HttpException($this->_('Сессия не найдена'), 'session_not_found', 400);
        }

        if ($session->fingerprint != $params['fp']) {
            // TODO Добавить оповещение о перехвате токена
            throw new HttpException($this->_('Некорректный отпечаток системы'), 'fingerprint_incorrect', 403);
        }

        if ($session->is_active_sw == 'N' || $session->date_expired < date('Y-m-d H:i:s')) {
            throw new HttpException($this->_('Эта сессия больше не активна. Войдите заново'), 'session_inactive', 403);
        }

        $session->is_active_sw = 'N';
        //$session->date_expired = 'N';
        $session->save();

        $user = $this->modAdmin->modelUsers->find($session->user_id)->current();

        return $this->createSession($user, $params['fp']);
    }


    /**
     * Регистрация с помощью email
     * @param $params
     * @return string[]
     * @throws HttpException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Exception
     *
     * @OA\Post(
     *   path    = "/client/registration/email",
     *   tags    = { "Доступ" },
     *   summary = "Регистрация с помощью email",
     *   @OA\RequestBody(
     *     required = true,
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(type = "object", example =
     *         {
     *           "email": "client@gmail.com",
     *           "lname": "Фамилия",
     *           "code": "100500",
     *           "password": "nty0473vy24t7ynv2304t750vm3t5"
     *         }
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response    = "200",
     *     description = "Успешное выполнение",
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(type = "object", example = { "webtoken" = "xxxxxxxxx" } )
     *     )
     *   ),
     *   @OA\Response(
     *     response    = "400",
     *     description = "Отправленные данные некорректны",
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(ref = "#/components/schemas/Error")
     *     )
     *   )
     * )
     */
    public function registrationEmail(array $params) : array {

        HttpValidator::testParameters([
            'email'    => 'req,string(1-255),email',
            'login'    => 'req,string(1-255)',
            'name'     => 'string(1-255)',
            'password' => 'req,string(1-255)',
        ], $params);

        $params['lname'] = htmlspecialchars($params['lname']);

        $client = $this->modClients->getClientByEmail($params['email']);

        if ($client instanceof Clients\Client) {
            if ($client->status !== 'new') {
                throw new HttpException('Пользователь с таким email уже зарегистрирован', 'email_isset', 400);
            }

            if ( ! $client->reg_code ||
                ! $client->reg_expired ||
                $client->reg_code != $params['code'] ||
                $client->reg_expired <= date('Y-m-d H:i:s')
            ) {
                throw new HttpException('Указан некорректный код, либо его действие закончилось', 'code_incorrect', 400);
            }

        } else {
            throw new HttpException('Введите email и получите код регистрации', 'email_not_found', 400);
        }

        $client->update([
            'status'      => 'active',
            'lastname'    => $params['lname'],
            'pass'        => \Tool::pass_salt($params['password']),
            'reg_code'    => null,
            'reg_expired' => null,
        ]);



        $user_session = $this->modAdmin->dataUsers->createRow([
            'user_id'            => $user->id,
            'refresh_token'      => $refresh_token->toString(),
            'client_ip'          => $_SERVER['REMOTE_ADDR'] ?? '',
            'agent_name'         => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'date_expired'       => date('Y-m-d H:i:s', $exp->getTimestamp()),
            'date_last_activity' => new \Zend_Db_Expr('NOW()'),
        ])->save();


        $refresh_token = $this->createToken($user->id, $user->login);
        $access_token  = $this->getAccessToken($user->id, $user->login);
        $exp           = $refresh_token->claims()->get('exp');

        $user_session = $this->modAdmin->dataUsersSession->createRow([
            'user_id'            => $user->id,
            'refresh_token'      => $refresh_token->toString(),
            'client_ip'          => $_SERVER['REMOTE_ADDR'] ?? '',
            'agent_name'         => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'date_expired'       => date('Y-m-d H:i:s', $exp->getTimestamp()),
            'date_last_activity' => new \Zend_Db_Expr('NOW()'),
        ])->save();

        setcookie("Core-Refresh-Token", $refresh_token, time() + 157680000, '/core', null, false);

        return [
            'refresh_token' => $refresh_token->toString(),
            'access_token'  => $access_token->toString(),
        ];
    }


    /**
     * Общая проверка аутентификации
     * @return Auth|null
     * @throws \Exception
     */
    public function getAuth():? Auth {

        // проверяем, есть ли в запросе токен
        $access_token = ! empty($_SERVER['HTTP_ACCESS_TOKEN'])
            ? $_SERVER['HTTP_ACCESS_TOKEN']
            : '';

        $auth = $access_token
            ? $this->getAuthByToken($access_token)
            : null;

        if ($auth) {
            $this->auth = $auth;
        }

        return $auth;
    }


    /**
     * @return array[]
     */
    public function getCabinet(): array {

        return [
            'user' => [
                'id'     => $this->auth->getUserId(),
                'name'   => $this->auth->getUserName(),
                'login'   => $this->auth->getUserLogin(),
                'avatar' => '',
            ],
            'system'  => [
                'name' => $this->config?->system?->name ?? ''
            ],
            'modules' => [],
        ];
    }


    /**
     * @param \Zend_Db_Table_Row_Abstract $user
     * @param string                      $fingerprint
     * @return array
     * @throws HttpException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function createSession(\Zend_Db_Table_Row_Abstract $user, string $fingerprint): array {

        $user_session = $this->modAdmin->modelUsersSession->createRow([
            'user_id'            => $user->id,
            'fingerprint'        => $fingerprint,
            'client_ip'          => $_SERVER['REMOTE_ADDR'] ?? '',
            'agent_name'         => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'date_last_activity' => new \Zend_Db_Expr('NOW()'),
        ]);
        $session_id = $user_session->save();


        $refresh_token_exp = $this->config?->system?->auth?->refresh_token?->expiration ?: 5184000; // 90 дней
        $access_token_exp  = $this->config?->system?->auth?->access_token?->expiration  ?: 1800;    // 30 минут

        if ( ! is_numeric($refresh_token_exp)) {
            throw new HttpException($this->_('Система настроена некорректно. Задайте system.auth.refresh_token.expiration'), 'error_refresh_token', 500);
        }
        if ( ! is_numeric($access_token_exp)) {
            throw new HttpException($this->_('Система настроена некорректно. Задайте system.auth.access_token.expiration'), 'error_access_token', 500);
        }


        $date_refresh_token_exp = (new \DateTime())->modify("+{$refresh_token_exp} second");
        $date_access_token_exp  = (new \DateTime())->modify("+{$access_token_exp} second");

        $refresh_token = $this->createToken($user->id, $user->login, $session_id, $date_refresh_token_exp);
        $access_token  = $this->createToken($user->id, $user->login, $session_id, $date_access_token_exp);

        $user_session->date_expired = $date_refresh_token_exp->format('Y-m-d H:i:s');
        $user_session->save();


        return [
            'refresh_token' => $refresh_token,
            'access_token'  => $access_token,
        ];
    }


    /**
     * Авторизация по токену
     * @param string $access_token
     * @return Auth|null
     * @throws HttpException
     */
    private function getAuthByToken(string $access_token): ?Auth {

        try {
            $sign      = $this->config?->system?->auth?->token_sign ?: '';
            $algorithm = $this->config?->system?->auth?->algorithm ?: 'HS256';

            $decoded    = JWT::decode($access_token, new Key($sign, $algorithm));
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



            $session = $this->modAdmin->modelUsersSession->find($session_id)->current();

            if (empty($session) ||
                $session->is_active_sw == 'N' ||
                $session->date_expired < date('Y-m-d H:i:s')
            ) {
                return null;
            }


            $user = $this->modAdmin->modelUsers->find($session->user_id)->current();

            if (empty($user) && $user->is_active_sw == 'N') {
                return null;
            }

            $session->date_last_activity = new \Zend_Db_Expr('NOW()');
            $session->save();

            return new Auth($user->toArray(), $session->toArray());

        } catch (\Exception $e) {
            // ignore
        }

        return null;
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
     * @param int       $user_id
     * @param string    $user_login
     * @param int       $session_id
     * @param \DateTime $date_expired
     * @return string
     */
    private function createToken(int $user_id, string $user_login, int $session_id, \DateTime $date_expired): string {

        $sign      = $this->config?->system?->auth?->token_sign ?: '';
        $algorithm = $this->config?->system?->auth?->algorithm ?: 'HS256';

        return JWT::encode([
            'iss' => $_SERVER['SERVER_NAME'] ?? '',
            'aud' => $user_login,
            'uid' => $user_id,
            'sid' => $session_id,
            'iat' => time(),
            'nbf' => time(),
            'exp' => $date_expired->getTimestamp(),
        ], $sign, $algorithm);
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

        if ( ! $this->isAllowed("{$module}_index")) {
            throw new \Exception(sprintf($this->_("У вас нет доступа к модулю %s!"), $module));
        }

        if ( ! $this->isAllowed("{$module}_{$section}")) {
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
}