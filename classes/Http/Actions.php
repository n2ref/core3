<?php
namespace Core3\Classes\Http;
use Core3\Classes\Tools;
use Core3\Exceptions\DbException;
use Core3\Exceptions\HttpException;
use Core3\Exceptions\RuntimeException;
use Laminas\Cache\Exception\ExceptionInterface;
use Laminas\Db\Sql\Expression;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;


/**
 *
 */
class Actions extends Common {


    /**
     * Авторизация по логину или email
     * @param array $params
     * @return array
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function login(array $params): array {

        Validator::testParameters([
            'login'    => 'req,string',
            'password' => 'req,string',
            'fp'       => 'req,string',
        ], $params);

        $user = $this->modAdmin->tableUsers->getRowByLoginEmail($params['login']);

        if ( ! $user) {
            throw new HttpException($this->_('Пользователя с таким логином нет'), 'login_not_found', 400);
        }

        if ($user->is_active_sw == 'N') {
            throw new HttpException($this->_('Этот пользователь деактивирован'), 'user_inactive', 400);
        }

        if ($user->pass != Tools::passSalt($params['password'])) {
            throw new HttpException($this->_('Неверный пароль'), 'pass_incorrect', 400);
        }


        $this->modAdmin->tableUsersSession->insert([
            'user_id'            => $user->id,
            'fingerprint'        => $params['fp'],
            'client_ip'          => $_SERVER['REMOTE_ADDR'] ?? '',
            'agent_name'         => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'date_last_activity' => new Expression('NOW()'),
        ]);
        $session_id = $this->modAdmin->tableUsersSession->getLastInsertValue();

        $refresh_token = $this->getRefreshToken($user->login, $session_id);
        $access_token  = $this->getAccessToken($user->login, $session_id);

        $user_session = $this->modAdmin->tableUsersSession->getRowById($session_id);
        $user_session->token_hash   = crc32($refresh_token->toString());
        $user_session->date_expired = $refresh_token->dateExpired()->format('Y-m-d H:i:s');
        $user_session->save();


        return [
            'refresh_token' => $refresh_token->toString(),
            'access_token'  => $access_token->toString(),
        ];
    }


    /**
     * Выход из системы
     * @return array
     * @throws HttpException
     */
    public function logout(): array {

        if (empty($this->auth)) {
            throw new HttpException($this->_('У вас нет доступа к системе'), 'forbidden', '403');
        }

        $session = $this->modAdmin->tableUsersSession->find($this->auth->getSessionId())->current();
        $session->is_active_sw  = 'N';
        $session->save();

        return [];
    }


    /**
     * Обновление токенов
     * @param array $params
     * @return array
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function refreshToken(array $params): array {

        Validator::testParameters([
            'refresh_token' => 'req,string',
            'fp'            => 'req,string',
        ], $params);


        $sign      = $this->config?->system?->auth?->token_sign ?: '';
        $algorithm = $this->config?->system?->auth?->algorithm ?: 'HS256';

        try {
            $decoded    = Token::decode($params['refresh_token'], $sign, $algorithm);
            $session_id = $decoded['sid'] ?? 0;
            $token_iss  = $decoded['iss'] ?? 0;
            $token_exp  = $decoded['exp'] ?? 0;

        } catch (\Exception $e) {
            throw new HttpException($this->_('Токен не прошел валидацию'), 'token_invalid', 403);
        }

        if (empty($session_id) || ! is_numeric($session_id)) {
            throw new HttpException($this->_('Некорректный токен'), 'token_incorrect', 403);
        }

        if ($token_exp < time() ||
            $token_iss != $_SERVER['SERVER_NAME']
        ) {
            throw new HttpException($this->_('Эта сессия больше не активна. Войдите заново'), 'session_inactive', 403);
        }



        $session = $this->modAdmin->tableUsersSession->getRowById($session_id);

        if (empty($session)) {
            throw new HttpException($this->_('Сессия не найдена'), 'session_not_found', 403);
        }

        if ($session->fingerprint != $params['fp']) {
            // TODO Добавить оповещение о перехвате токена
            throw new HttpException($this->_('Некорректный отпечаток системы'), 'fingerprint_invalid', 403);
        }

        if ($session->token_hash != crc32($params['refresh_token'])) {
            // TODO Добавить оповещение о перехвате токена
            throw new HttpException($this->_('Токен не активен'), 'token_invalid', 403);
        }

        if ($session->is_active_sw == 'N' || $session->date_expired < date('Y-m-d H:i:s')) {
            throw new HttpException($this->_('Эта сессия больше не активна. Войдите заново'), 'session_inactive', 403);
        }


        $user = $this->modAdmin->tableUsers->find($session->user_id)->current();

        if (empty($user)) {
            throw new HttpException($this->_('Пользователь не найден'), 'session_user_not_found', 403);
        }

        $refresh_token = $this->getRefreshToken($user->login, $session_id);
        $access_token  = $this->getAccessToken($user->login, $session_id);

        $session->client_ip    = $_SERVER['REMOTE_ADDR'] ?? null;
        $session->agent_name   = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $session->token_hash   = crc32($refresh_token->toString());
        $session->date_expired = $refresh_token->dateExpired()->format('Y-m-d H:i:s');
        $session->save();



        return [
            'refresh_token' => $refresh_token->toString(),
            'access_token'  => $access_token->toString(),
        ];
    }


    /**
     * Регистрация с помощью email
     * @param array $params
     * @return array
     * @throws HttpException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function registrationEmail(array $params) : array {

        // TODO Доделать
        Validator::testParameters([
            'email'    => 'req,string(1-255),email',
            'login'    => 'req,string(1-255)',
            'name'     => 'string(1-255)',
            'password' => 'req,string(1-255)',
            'fp'       => 'req,string(1-255)',
        ], $params);

        $params['lname'] = htmlspecialchars($params['lname']);

        $user = $this->modAdmin->tableUsers->getRowByEmail($params['email']);

        if ($user instanceof Clients\Client) {
            if ($user->status !== 'new') {
                throw new HttpException('Пользователь с таким email уже зарегистрирован', 'email_isset', 400);
            }

            if ( ! $user->reg_code ||
                ! $user->reg_expired ||
                $user->reg_code != $params['code'] ||
                $user->reg_expired <= date('Y-m-d H:i:s')
            ) {
                throw new HttpException('Указан некорректный код, либо его действие закончилось', 'code_incorrect', 400);
            }

        } else {
            throw new HttpException('Введите email и получите код регистрации', 'email_not_found', 400);
        }

        $user->update([
            'status'      => 'active',
            'lastname'    => $params['lname'],
            'pass'        => Tools::passSalt($params['password']),
            'reg_code'    => null,
            'reg_expired' => null,
        ]);



        $this->modAdmin->tableUsers->insert([
            'user_id'            => $user->id,
            'refresh_token'      => $refresh_token->toString(),
            'client_ip'          => $_SERVER['REMOTE_ADDR'] ?? '',
            'agent_name'         => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'date_expired'       => date('Y-m-d H:i:s', $exp->getTimestamp()),
            'date_last_activity' => new Expression('NOW()'),
        ]);
        
        $refresh_token = $this->createToken($user->id, $user->login);
        $access_token  = $this->getAccessToken($user->id, $user->login);
        $exp           = $refresh_token->claims()->get('exp');

        $this->modAdmin->tableUsersSession->insert([
            'user_id'            => $user->id,
            'refresh_token'      => $refresh_token->toString(),
            'client_ip'          => $_SERVER['REMOTE_ADDR'] ?? '',
            'agent_name'         => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'date_expired'       => date('Y-m-d H:i:s', $exp->getTimestamp()),
            'date_last_activity' => new Expression('NOW()'),
        ]);

        setcookie("Core-Refresh-Token", $refresh_token, time() + 157680000, '/core', null, false);

        return [
            'refresh_token' => $refresh_token->toString(),
            'access_token'  => $access_token->toString(),
        ];
    }


    /**
     * Отправка проверочного кода на email
     * @param array $params
     * @return array
     */
    public function registrationEmailCheck(array $params): array {

        // TODO Доделать
        return [];
    }


    /**
     * Восстановление пароля при помощи email
     * @param array $params
     * @return array
     */
    public function restorePass(array $params): array {

        // TODO Доделать
        return [];
    }


    /**
     * Отправка проверочного кода на email для восстановления пароля
     * @param array $params
     * @return array
     */
    public function restorePassCheck(array $params): array {

        // TODO Доделать
        return [];
    }


    /**
     * Данные о содержимом личного кабинета
     * @return array
     * @throws HttpException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getCabinet(): array {

        if (empty($this->auth)) {
            throw new HttpException($this->_('У вас нет доступа к системе'), 'forbidden', '403');
        }

        $modules = $this->getModules();

        if ($this->auth->isAdmin()) {
            $modules[] = [
                'name'             => "admin",
                'title'            => "Админ",
                'icon'             => "",
                'isset_index_page' => true,
                'sections'         => [
                    ["name" => "modules",  'title' => $this->_("Модули")],
                    ["name" => "settings", 'title' => $this->_("Конфигурация")],
                    ["name" => "enums",    'title' => $this->_("Справочники")],
                    ["name" => "users",    'title' => $this->_("Пользователи")],
                    ["name" => "roles",    'title' => $this->_("Роли")],
                    ["name" => "monitor",  'title' => $this->_("Мониторинг")],
                    ["name" => "audit",    'title' => $this->_("Аудит")],
                ],
            ];
        }

        return [
            'user'    => [
                'id'     => $this->auth->getUserId(),
                'name'   => $this->auth->getUserName(),
                'login'  => $this->auth->getUserLogin(),
                'avatar' => 'https://www.gravatar.com/avatar/9dd10adaa1333208b4cf36935c73bbd7',
            ],
            'system'  => [
                'name' => $this->config?->system?->name ?: ''
            ],
            'modules' => $modules,
        ];
    }


    /**
     * Отправка настроек темы
     * @return array
     */
    public function getTheme(): array {

        // TODO Доделать
        return [
            'logo' => $this->config?->system?->logo,
            'auth' => $this->config?->system?->auth,
            'menu' => $this->config?->system?->menu
        ];
    }


    /**
     * Домашняя страница
     * @return array
     * @throws HttpException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    public function getHome(): mixed {

        if (empty($this->auth)) {
            throw new HttpException($this->_('У вас нет доступа к системе'), 'forbidden', '403');
        }

        $location        = DOC_ROOT . "/mod/home";
        $controller_file = "{$location}/Controller.php";

        if ( ! file_exists($controller_file)) {
            throw new HttpException($this->_("Модуль \"%s\" сломан. Не найден файл контроллера.", ['home']));
        }

        $autoload_file = "{$location}/vendor/autoload.php";

        if (file_exists($autoload_file)) {
            require_once $autoload_file;
        }

        require_once $controller_file;

        $module_class_name = "\\Core3\\Mod\\Home\\Controller";

        if ( ! class_exists($module_class_name)) {
            throw new HttpException($this->_("Модуль \"%s\" сломан. Не найден класс контроллера.", ['home']));
        }

        $controller = new $module_class_name();

        if ( ! method_exists($controller, 'index')) {
            throw new HttpException($this->_("Модуль \"%s\" сломан. Не найден метод index.", ['home']));
        }

        return $controller->index();
    }


    /**
     * Данные о разделе модуля
     * @param string      $module_name
     * @param string      $section_name
     * @param string|null $query
     * @return mixed
     * @throws HttpException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    public function getModSection(string $module_name, string $section_name, string $query = null): mixed {

        if (empty($this->auth)) {
            throw new HttpException($this->_('У вас нет доступа к системе'), 'forbidden', '403');
        }

        if ( ! $this->isAllowed($module_name)) {
            throw new HttpException($this->_("У вас нет доступа к модулю %s!", [$module_name]), 'forbidden', 403);
        }

        if ( ! $this->isAllowed("{$module_name}_{$section_name}")) {
            throw new HttpException($this->_("У вас нет доступа к разделу %s!", [$section_name]), 'forbidden', 403);
        }


        $section_name = ucfirst(strtolower($section_name));
        $controller   = $this->getModuleController($module_name);

        if ( ! is_callable([$controller, "section{$section_name}"])) {
            throw new HttpException($this->_("Ошибка. Не найден метод управления разделом %s!", [$section_name]), 'broken_section', 500);
        }

        $request = new Request([
            'mod_query' => $query
        ]);

        // Обнуление
        $_GET     = [];
        $_POST    = [];
        $_REQUEST = [];
        $_FILES   = [];
        $_COOKIE  = [];

        return $controller->{"section{$section_name}"}($request);
    }


    /**
     * Вызов метода для обработки данных
     * @param string $module_name
     * @param string $section_name
     * @param string $method_name
     * @return array
     * @throws DbException
     * @throws HttpException
     * @throws RuntimeException
     * @throws ExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getModHandler(string $module_name, string $section_name, string $method_name): array {

        if (empty($this->auth)) {
            throw new HttpException($this->_('У вас нет доступа к системе'), 'forbidden', '403');
        }

        if ( ! $this->isAllowed($module_name)) {
            throw new HttpException($this->_("У вас нет доступа к модулю %s!", [$module_name]), 'forbidden', 403);
        }

        if ( ! $this->isAllowed("{$module_name}_{$section_name}")) {
            throw new HttpException($this->_("У вас нет доступа к разделу %s!", [$section_name]), 'forbidden', 403);
        }

        $handler        = $this->getModuleHandler($module_name);
        $handler_method = $section_name . ucfirst($method_name);


        if ( ! is_callable([$handler, $handler_method]) ||
             ! in_array($method_name, get_class_methods($handler_method))
        ) {
            throw new HttpException($this->_("Ошибка. Не найден метод обработчика %s!", [$method_name]), 'incorrect_handler_method', 403);
        }

        $request = new Request();

        // Обнуление
        $_GET     = [];
        $_POST    = [];
        $_REQUEST = [];
        $_FILES   = [];
        $_COOKIE  = [];

        return $handler->$handler_method($request);
    }


    /**
     * @return array
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function getModules(): array {

        $modules      = [];
        $module_rows  = $this->modAdmin->tableModules->getRowsByActiveVisible();
        $section_rows = $this->modAdmin->tableModulesSections->getRowsByActive();

        foreach ($module_rows as $module_row) {
            if ( ! $this->isAllowed($module_row->name, self::PRIVILEGE_READ)) {
                continue;
            }

            $sections = [];

            foreach ($section_rows as $section_row) {
                if ( ! $section_row->title ||
                    $section_row->module_id != $module_row->id ||
                    ! $this->isAllowed("{$module_row->name}_{$section_row->name}", self::PRIVILEGE_READ)
                ) {
                    continue;
                }

                $sections[] = [
                    'name'  => $section_row->name,
                    'title' => $section_row->title,
                ];
            }

            $modules[] = [
                'name'             => $module_row->name,
                'title'            => $module_row->title,
                'icon'             => 'fa-solid fa-file-lines',
                'isset_index_page' => $module_row->is_index_page_sw == 'Y',
                'sections'         => $sections,
            ];
        }

        return $modules;
    }
}