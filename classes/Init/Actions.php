<?php
namespace Core3\Classes\Init;
use Core3\Classes\Tools;
use Core3\Classes\Validator;
use Core3\Exceptions\DbException;
use Core3\Exceptions\Exception;
use Core3\Exceptions\HttpException;
use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
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
     * @param Request $request
     * @return array
     * @throws ContainerExceptionInterface
     * @throws HttpException
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function login(Request $request): array {

        $params = $request->getBody('json');

        $fields = [
            'login'    => 'req,string',
            'password' => 'req,string',
            'fp'       => 'req,string',
        ];

        if ($errors = Validator::validateFields($fields, $params)) {
            throw new HttpException(400, 'invalid_param', current($errors));
        }

        $user = $this->modAdmin->tableUsers->getRowByLoginEmail($params['login']);

        if ( ! $user) {
            throw new HttpException(400, 'login_not_found', $this->_('Пользователя с таким логином нет'));
        }

        if ($user->is_active == '0') {
            throw new HttpException(400, 'user_inactive', $this->_('Этот пользователь деактивирован'));
        }

        if ($user->pass != Tools::passSalt($params['password'])) {
            throw new HttpException(400, 'pass_incorrect', $this->_('Неверный пароль') . Tools::passSalt($params['password']));
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

        if ($this->auth) {
            $session            = $this->modAdmin->tableUsersSession->find($this->auth->getSessionId())->current();
            $session->is_active = 0;
            $session->save();
        }

        return [];
    }


    /**
     * Обновление токенов
     * @param Request $request
     * @return array
     * @throws ContainerExceptionInterface
     * @throws HttpException
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function refreshToken(Request $request): array {

        $params = $request->getBody('json');
        $fields = [
            'refresh_token' => 'req,string',
            'fp'            => 'req,string',
        ];

        if ($errors = Validator::validateFields($fields, $params)) {
            throw new HttpException(400, 'invalid_param', current($errors));
        }


        $sign      = $this->config?->system?->auth?->token_sign ?: 'gyctmn34ycrr0471yc4r';
        $algorithm = $this->config?->system?->auth?->algorithm ?: 'HS256';

        try {
            $decoded    = Token::decode($params['refresh_token'], $sign, $algorithm);
            $session_id = $decoded['sid'] ?? 0;
            $token_iss  = $decoded['iss'] ?? 0;
            $token_exp  = $decoded['exp'] ?? 0;

        } catch (\Exception $e) {
            throw new HttpException(403, 'token_invalid', $this->_('Токен не прошел валидацию'));
        }

        if (empty($session_id) || ! is_numeric($session_id)) {
            throw new HttpException(403, 'token_incorrect', $this->_('Некорректный токен'));
        }

        if ($token_exp < time() ||
            $token_iss != $_SERVER['SERVER_NAME']
        ) {
            throw new HttpException(403, 'session_inactive', $this->_('Эта сессия больше не активна. Войдите заново'));
        }



        $session = $this->modAdmin->tableUsersSession->getRowById($session_id);

        if (empty($session)) {
            throw new HttpException(403, 'session_not_found', $this->_('Сессия не найдена'));
        }

        if ($session->fingerprint != $params['fp']) {
            // TODO Добавить оповещение о перехвате токена
            throw new HttpException(403, 'fingerprint_invalid', $this->_('Некорректный отпечаток системы'));
        }

        if ($session->token_hash != crc32($params['refresh_token'])) {
            // TODO Добавить оповещение о перехвате токена
            throw new HttpException(403, 'token_invalid', $this->_('Токен не активен'));
        }

        if ($session->is_active == 0 || $session->date_expired < date('Y-m-d H:i:s')) {
            throw new HttpException(403, 'session_inactive', $this->_('Эта сессия больше не активна. Войдите заново'));
        }


        $user = $this->modAdmin->tableUsers->find($session->user_id)->current();

        if (empty($user)) {
            throw new HttpException(403, 'session_user_not_found', $this->_('Пользователь не найден'));
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
    public function registrationEmail(Request $request) : array {

        $params = $request->getBody('json');
        // TODO Доделать
        $fields = [
            'email'    => 'req,string(1-255),email',
            'login'    => 'req,string(1-255)',
            'name'     => 'string(1-255)',
            'password' => 'req,string(1-255)',
            'fp'       => 'req,string(1-255)',
        ];

        if ($errors = Validator::validateFields($fields, $params)) {
            throw new HttpException(400, 'invalid_param', current($errors));
        }

        $params['lname'] = htmlspecialchars($params['lname']);

        $user = $this->modAdmin->tableUsers->getRowByEmail($params['email']);

        if ($user instanceof Clients\Client) {
            if ($user->status !== 'new') {
                throw new HttpException(400, 'email_isset', 'Пользователь с таким email уже зарегистрирован');
            }

            if ( ! $user->reg_code ||
                ! $user->reg_expired ||
                $user->reg_code != $params['code'] ||
                $user->reg_expired <= date('Y-m-d H:i:s')
            ) {
                throw new HttpException(400, 'code_incorrect', 'Указан некорректный код, либо его действие закончилось');
            }

        } else {
            throw new HttpException(400, 'email_not_found', 'Введите email и получите код регистрации');
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
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function registrationEmailCheck(Request $request): array {

        $params = $request->getBody('json');

        // TODO Доделать
        return [];
    }


    /**
     * Восстановление пароля при помощи email
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function restorePass(Request $request): array {

        $params = $request->getBody('json');
        // TODO Доделать
        return [];
    }


    /**
     * Отправка проверочного кода на email для восстановления пароля
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function restorePassCheck(Request $request): Response {

        $params = $request->getBody('json');

        // TODO Доделать
        return [];
    }


    /**
     * Получение файла аватара
     * @param Request $request
     * @return Response
     * @throws HttpException
     * @throws ImageResizeException
     * @throws Exception
     */
    public function getUserAvatar(Request $request): Response {

        $user_id = $request->getPathParam('id');

        if ( ! $user_id) {
            throw new HttpException(400, 'empty_user_id', $this->_('Не указан id пользователя'));
        }

        $user = $this->modAdmin->tableUsers->getRowById($user_id);

        if ( ! $user) {
            throw new HttpException(400, 'user_not_found', $this->_('Указанный пользователь не найден'));
        }

        if ($user->avatar_type === 'none') {
            return $this->getAvatarDefault();

        } else {
            return $this->getAvatarUser($user_id);
        }
    }


    /**
     * Данные о содержимом личного кабинета
     * @return array
     * @throws HttpException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getCabinet(): array {

        if ( ! $this->auth) {
            throw new HttpException(403, 'forbidden', $this->_('У вас нет доступа к системе'));
        }

        $modules = $this->getModules();

        if ($this->auth->isAdmin()) {
            $modules[] = [
                'name'             => "admin",
                'title'            => "Админ",
                'icon'             => "",
                'is_visible_index' => true,
                'sections'         => [
                    ["name" => "modules",  'title' => $this->_("Модули")],
                    ["name" => "settings", 'title' => $this->_("Конфигурация")],
                    ["name" => "users",    'title' => $this->_("Пользователи")],
                    ["name" => "roles",    'title' => $this->_("Роли")],
                    ["name" => "monitor",  'title' => $this->_("Мониторинг")],
                ],
            ];
        }

        $user_id = $this->auth->getUserId();

        return [
            'user'    => [
                'id'     => $user_id,
                'name'   => $this->auth->getUserName(),
                'login'  => $this->auth->getUserLogin(),
                'avatar' => "core3/user/{$user_id}/avatar",
            ],
            'system'  => [
                'name' => $this->config?->system?->name ?: '',
                'conf' => $this->getConf(),
            ],
            'modules' => $modules
        ];
    }


    /**
     * Отправка настроек
     * @return array
     */
    public function getConf(): array {

        return [
            'name'  => $this->config?->system?->name,
            'logo'  => $this->config?->system?->logo,
            'lang'  => $this->config?->system?->lang,
            'theme' => $this->config?->system?->theme?->toArray(),
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

        if ( ! $this->auth) {
            throw new HttpException(403, 'forbidden', $this->_('У вас нет доступа к системе'));
        }

        $location        = DOC_ROOT . "/mod/home";
        $controller_file = "{$location}/Controller.php";

        if ( ! file_exists($controller_file)) {
            throw new HttpException(500, 'broken', $this->_("Модуль \"%s\" сломан. Не найден файл контроллера.", ['home']));
        }

        $autoload_file = "{$location}/vendor/autoload.php";

        if (file_exists($autoload_file)) {
            require_once $autoload_file;
        }

        require_once $controller_file;

        $module_class_name = "\\Core3\\Mod\\Home\\Controller";

        if ( ! class_exists($module_class_name)) {
            throw new HttpException(500, 'broken', $this->_("Модуль \"%s\" сломан. Не найден класс контроллера.", ['home']));
        }

        $controller = new $module_class_name();

        if ( ! method_exists($controller, 'index')) {
            throw new HttpException(500, 'broken', $this->_("Модуль \"%s\" сломан. Не найден метод index.", ['home']));
        }

        return $controller->index();
    }


    /**
     * Данные о разделе модуля
     * @param Request $request
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws ExceptionInterface
     * @throws HttpException
     * @throws NotFoundExceptionInterface
     */
    public function getModSection(Request $request): mixed {

        $module_name  = $request->getPathParam('module');
        $section_name = $request->getPathParam('section');

        if ( ! $this->auth) {
            throw new HttpException(403, 'forbidden', $this->_('У вас нет доступа к системе'));
        }

        if ( ! $this->auth->isAllowed($module_name)) {
            throw new HttpException(403, 'forbidden', $this->_("У вас нет доступа к модулю %s!", [$module_name]));
        }

        if ( ! $this->auth->isAllowed("{$module_name}_{$section_name}")) {
            throw new HttpException(403, 'forbidden', $this->_("У вас нет доступа к разделу %s!", [$section_name]));
        }


        $section_name = ucfirst(strtolower($section_name));
        $controller   = $this->getModuleController($module_name);

        if ( ! is_callable([$controller, "section{$section_name}"])) {
            throw new HttpException(404, 'broken_section', $this->_("Ошибка. Не найден метод управления разделом %s!", [$section_name]));
        }

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
     * @throws Exception
     * @throws ExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getModHandler(Request $request): mixed {

        $module_name  = $request->getPathParam('module');
        $section_name = $request->getPathParam('section');
        $method_name  = $request->getPathParam('method');

        if ( ! $this->auth) {
            throw new HttpException(403, 'forbidden', $this->_('У вас нет доступа к системе'));
        }

        if ( ! $this->auth->isAllowed($module_name)) {
            throw new HttpException(403, 'forbidden', $this->_("У вас нет доступа к модулю %s!", [$module_name]));
        }

        if ( ! $this->auth->isAllowed("{$module_name}_{$section_name}")) {
            throw new HttpException(403, 'forbidden', $this->_("У вас нет доступа к разделу %s!", [$section_name]));
        }

        if (strpos($method_name, '_') !== false) {
            $method_name_parts = explode('_', $method_name);
            $method_name       = implode('', array_map('ucfirst', $method_name_parts));
            $method_name       = lcfirst($method_name);
        }

        $handler = $this->getModuleHandler($module_name, $section_name);

        if ( ! is_callable([$handler, $method_name]) ||
             ! in_array($method_name, get_class_methods($handler))
        ) {
            throw new HttpException(403, 'incorrect_handler_method', $this->_("Ошибка. Не найден метод обработчика: %s", [$method_name]));
        }

        return $handler->$method_name($request);
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
            if ( ! $this->auth->isAllowed($module_row->name)) {
                continue;
            }

            $sections = [];

            foreach ($section_rows as $section_row) {
                if ( ! $section_row->title ||
                    $section_row->module_id != $module_row->id ||
                    ! $this->auth->isAllowed("{$module_row->name}_{$section_row->name}")
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
                'is_visible_index' => (bool)$module_row->is_visible_index,
                'sections'         => $sections,
            ];
        }

        return $modules;
    }


    /**
     * @return Response
     * @throws HttpException
     * @throws Exception
     */
    private function getAvatarDefault(): Response {

        $file_path = DOC_ROOT. '/core3/front/src/img/default.png';

        if ( ! is_file($file_path)) {
            throw new HttpException(404, 'file_not_found', $this->_('Указанный файл не найден'));
        }

        $file_content = file_get_contents($file_path);
        $file_type    = mime_content_type($file_path);
        $file_hash    = md5($file_content);

        if ( ! $file_type || ! preg_match('~^image/.*~', $file_type)) {
            throw new HttpException(404, 'file_is_not_image', $this->_('Указанный файл не является картинкой'));
        }

        $response = new Response();
        $response->setHeader('Content-Type',        $file_type);
        $response->setHeader('Content-Length',      strlen($file_content));
        $response->setHeader('Content-Disposition', "filename=\"avatar.png\"");

        if ($file_hash) {
            $etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

            $response->setHeader('Etag',          $file_hash);
            $response->setHeader('Cache-Control', 'public');

            //check if page has changed. If not, send 304 and exit
            if ($etagHeader == $file_hash) {
                $response->setHttpCode(304);
                return $response;
            }
        }

        $response->setContent($file_content);

        return $response;
    }


    /**
     * @param int $user_id
     * @return Response
     * @throws Exception
     * @throws HttpException
     * @throws ImageResizeException
     */
    private function getAvatarUser(int $user_id): Response {

        $file = $this->modAdmin->tableUsersFiles->getRowsByUser($user_id, 'avatar', 1);

        if ( ! $file) {
            throw new HttpException(404, 'file_not_found', $this->_('Указанный файл не найден'));
        }

        if ( ! $file->content) {
            throw new HttpException(500, 'file_broken', $this->_('Указанный файл сломан'));
        }

        if ( ! $file->thumb && ( ! $file->file_type || ! preg_match('~^image/.*~', $file->file_type))) {
            throw new HttpException(404, 'file_is_not_image', $this->_('Указанный файл не является картинкой'));
        }

        $response = new Response();

        if ($file->file_type) {
            $response->setHeader('Content-Type', $file->file_type);
        }

        if ($file->file_name) {
            $filename_encode = rawurlencode($file->file_name);
            $response->setHeader('Content-Disposition', "filename=\"{$file->file_name}\"; filename*=utf-8''{$filename_encode}\"");
        }


        if ($file->file_hash) {
            $etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

            $response->setHeader('Etag',          $file->file_hash);
            $response->setHeader('Cache-Control', 'public');

            //check if page has changed. If not, send 304 and exit
            if ($etagHeader == $file->file_hash) {
                $response->setHttpCode(304);
                return $response;
            }
        }

        if ( ! $file->thumb) {
            $image = ImageResize::createFromString($file->content);
            $image->resizeToBestFit(80, 80);

            $file->thumb = $image->getImageAsString(IMAGETYPE_PNG);
            $file->save();
        }

        $response->setHeader('Content-Length', strlen($file->thumb));
        $response->setContent($file->thumb);

        return $response;
    }


    /**
     * Обработчик модуля
     * @param string $module_name
     * @param string $section_name
     * @return mixed
     * @throws ExceptionInterface
     * @throws Exception
     * @throws DbException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getModuleHandler(string $module_name, string $section_name): mixed {

        $location = $this->getModuleLocation($module_name);

        if ( ! $location) {
            throw new Exception($this->_("Модуль \"%s\" не найден", [$module_name]));
        }

        if ( ! $this->isModuleActive($module_name)) {
            throw new Exception($this->_('Модуль "%s" не активен', [$module_name]));
        }

        // Подключение файла с обработчиком
        $section_name = ucfirst($section_name);
        $file_handler = "{$location}/Handlers/{$section_name}.php";

        if ( ! file_exists($file_handler)) {
            throw new Exception($this->_('Не найден файл "%s" в модуле "%s"', [$file_handler, $module_name]));
        }

        $autoload_file = "{$location}/vendor/autoload.php";

        if (file_exists($autoload_file)) {
            require_once $autoload_file;
        }

        require_once $file_handler;

        $handler_class_name = '\\Core3\\Mod\\' . ucfirst($module_name) . '\\Handlers\\' . $section_name;

        // Инициализация обработчика
        if ( ! class_exists($handler_class_name)) {
            throw new Exception($this->_('Не найден класс "%s" в модуле "%s"', [$handler_class_name, $module_name]));
        }


        // Выполнение обработчика
        return new $handler_class_name();
    }
}