<?php
namespace Core3\Classes\Init;
use Core3\Classes\Auth;
use Core3\Classes\Common;
use Core3\Classes\Init;
use Core3\Exceptions\HttpException;
use Laminas\Db\Sql\Expression;


/**
 *
 */
class Http extends Common {


    /**
     * @return mixed
     * @throws HttpException
     * @throws \Exception
     */
    public function dispatch(): mixed {

        $router = new Router();
        $router->post('^/auth/login',                                                                    [Init\Actions::class, 'login']);
        $router->put('^/auth/logout',                                                                    [Init\Actions::class, 'logout']);
        $router->post('^/auth/refresh',                                                                  [Init\Actions::class, 'refreshToken']);
        $router->post('^/registration/email',                                                            [Init\Actions::class, 'registrationEmail']);
        $router->post('^/registration/email/check',                                                      [Init\Actions::class, 'registrationEmailCheck']);
        $router->post('^/restore',                                                                       [Init\Actions::class, 'restorePass']);
        $router->post('^/restore/check',                                                                 [Init\Actions::class, 'restorePassCheck']);
        $router->get('^/conf',                                                                           [Init\Actions::class, 'getConf']);
        $router->get('^/cabinet',                                                                        [Init\Actions::class, 'getCabinet']);
        $router->get('^/home',                                                                           [Init\Actions::class, 'getHome']);
        $router->get('^/user/{id:[0-9_]+}/avatar',                                                       [Init\Actions::class, 'getUserAvatar']);
        $router->any('^/mod/{module:[a-z0-9_]+}/{section:[a-z0-9_]+}/handler/{method:[a-zA-Z0-9_/]+}',   [Init\Actions::class, 'getModHandler']);
        $router->get('^/mod/{module:[a-z0-9_]+}/{section:[a-z0-9_]+}{mod_query:(?:/[a-zA-Z0-9_/\-]+|)}', [Init\Actions::class, 'getModSection']);

        $uri   = mb_substr($_SERVER['REQUEST_URI'], mb_strlen(DOC_PATH . CORE_FOLDER));
        $route = $router->getRoute($_SERVER['REQUEST_METHOD'], $uri);

        if (empty($route)) {
            throw new HttpException(404, 'not_found', '404 Not found');
        }

        $request = new Request();

        // Обнуление
        $_GET     = [];
        $_POST    = [];
        $_REQUEST = [];
        $_FILES   = [];
        $_COOKIE  = [];

        $params = [ $request ];

        if ($route_params = $route->getParams()) {
            foreach ($route_params as $param) {
                $params[] = $param;
            }
        }

        return $route->run($params);
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

        // проверяем, есть ли в запросе токен
        $access_token = empty($access_token) && ! empty($_COOKIE['Core-Access-Token'])
            ? $_COOKIE['Core-Access-Token']
            : $access_token;

        return $access_token
            ? $this->getAuthByToken($access_token)
            : null;
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
            $decoded   = Init\Token::decode($access_token, $sign, $algorithm);

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
            $session->date_last_activity = new Expression('NOW()');
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

                    $http_method = ! empty($route[$http_method]) ? $http_method : '*';

                    if ( ! isset($route[$http_method])) {
                        throw new HttpException(405, 'incorrect_http_method', "Incorrect http method");
                    }

                    if (empty($route[$http_method]['action'])) {
                        throw new HttpException(500, 'incorrect_action', "Incorrect action");
                    }

                    $result['action'] = $route[$http_method]['action'];
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
                                            throw new HttpException(400, 'incorrect_json_data', 'Incorrect json data');
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
}