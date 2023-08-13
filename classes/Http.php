<?php
namespace Core3\Classes;
use Core3\Classes\Http\Request;
use Core3\Classes\Http\Response;
use Core3\Classes\Http\Router;
use Core3\Exceptions\HttpException;
use Laminas\Db\Sql\Expression;


/**
 *
 */
class Http extends Common {


    /**
     * @return mixed
     * @throws HttpException
     */
    public function dispatch(): mixed {

        $router = new Router();
        $router->addPath('/core/auth/login')->post('login');
        $router->addPath('/core/auth/logout')->put('logout');
        $router->addPath('/core/auth/refresh')->post('refreshToken');
        $router->addPath('/core/registration/email')->post('registrationEmail');
        $router->addPath('/core/registration/email/check')->post('registrationEmailCheck');
        $router->addPath('/core/restore')->post('restorePass');
        $router->addPath('/core/restore/check')->post('restorePassCheck');
        $router->addPath('/core/conf')->get('getConf');
        $router->addPath('/core/home')->get('getHome');
        $router->addPath('/core/cabinet')->get('getCabinet');
        $router->addPath('/core/mod/{module}/{section}/handler/{handler}', ['[a-z0-9_]+', '[a-z0-9_]+', '[a-z0-9_/]+'])->any('getModHandler');
        $router->addPath('/core/mod/{module}/{section}{mod_query}', ['[a-z0-9_]+', '[a-z0-9_]+', '(?:/[a-z0-9_/]+|)'])->any('getModSection');

        // TODO добавить возможность работы в папке
        $route = $router->getRoute($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

        if (empty($route)) {
            throw new HttpException('404 Not found', 'not_found', 404);
        }

        $actions = new Http\Actions();

        if ( ! is_callable([$actions, $route['action']]) && ! method_exists($route['action'], '__call')) {
            throw new HttpException("Incorrect action", 'incorrect_action', 500);
        }

        return call_user_func_array(
            [$actions, $route['action']],
            [ new Request($route['params']) ]
        );
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

        $auth = $access_token
            ? $this->getAuthByToken($access_token)
            : null;

        if ($auth) {
            $this->auth = $auth;
        }

        return $auth;
    }


    /**
     * Авторизация по токену
     * @param string $access_token
     * @return Auth|null
     */
    private function getAuthByToken(string $access_token): ?Auth {

        try {
            $sign      = $this->config?->system?->auth?->token_sign ?: '';
            $algorithm = $this->config?->system?->auth?->algorithm ?: 'HS256';

            $decoded    = Http\Token::decode($access_token, $sign, $algorithm);

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
                $session->is_active_sw == 'N' ||
                $session->date_expired < date('Y-m-d H:i:s')
            ) {
                return null;
            }


            $user = $this->modAdmin->tableUsers->getRowById($session->user_id);

            if (empty($user) && $user->is_active_sw == 'N') {
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
                        throw new HttpException("Incorrect http method", 'incorrect_http_method', 405);
                    }

                    if (empty($route[$http_method]['action'])) {
                        throw new HttpException("Incorrect action", 'incorrect_action', 500);
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
}