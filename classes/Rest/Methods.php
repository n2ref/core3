<?php
namespace Core3\Classes\Rest;
use Core3\Classes\Tools;
use Core3\Classes\HttpValidator;
use Core3\Exceptions\HttpException;
use OpenApi\Annotations as OA;

/**
 * @url http://zircote.github.io/swagger-php/Getting-started.html
 *
 * @OA\Info(
 *   title       = "Core3",
 *   version     = "3.0",
 *   description = "Система управления"
 * )
 * @OA\Server(
 *   url = ""
 * )
 * @OA\Tag(
 *   name        = "Доступ",
 *   description = "Вход и регистрация"
 * )
 * @OA\Tag(
 *   name        = "Кабинет",
 *   description = "Управление личным кабинетом"
 * )
 * @OA\Schema(
 *   schema   = "Error",
 *   title    = "Ошибка",
 *   required = { "status", "error_message", "error_code"},
 *   @OA\Property(property = "status", type = "string"),
 *   @OA\Property(property = "error_message", type = "string"),
 *   @OA\Property(property = "error_code", type = "string")
 * )
 */


/**
 *
 */
class Methods extends Common {


    /**
     * Авторизация по логину или email
     * @param array $params
     * @return array
     * @throws \Exception
     * @throws \Zend_Db_Adapter_Exception|\Zend_Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @OA\Post(
     *   path    = "/core/auth/login",
     *   tags    = { "Получение данных для выхода" },
     *   summary = "Авторизация по логину или email",
     *   @OA\RequestBody(
     *     description = "Данные для входа",
     *     required    = true,
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(type = "object", example = { "login" = "client@gmail.com", "password" = "197nmy4t70yn3v285v2n30304m3v204304", "fp" = "983r834jtyr0923v84ty0v234tmy"})
     *     )
     *   ),
     *   @OA\Response(
     *     response    = "200",
     *     description = "Токены для использования системы",
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(type = "object", example = { "refresh_token" = "xxxxxxxxxxxxxx", "access_token" = "xxxxxxxxxxxxxx" } )
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


        $user_session = $this->modAdmin->modelUsersSession->createRow([
            'user_id'            => $user->id,
            'fingerprint'        => $params['fp'],
            'client_ip'          => $_SERVER['REMOTE_ADDR'] ?? '',
            'agent_name'         => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'date_last_activity' => new \Zend_Db_Expr('NOW()'),
        ]);
        $session_id = $user_session->save();


        $refresh_token = $this->getRefreshToken($user->login, $session_id);
        $access_token  = $this->getAccessToken($user->login, $session_id);

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

        $session = $this->modAdmin->modelUsersSession->find($this->auth->getSessionId())->current();
        $session->is_active_sw  = 'N';
        $session->save();

        return [];
    }


    /**
     * Обновление токенов
     * @param array $params
     * @return array
     * @throws \Exception
     * @throws \Zend_Db_Adapter_Exception|\Zend_Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @OA\Post(
     *   path    = "/core/auth/refresh",
     *   tags    = { "Обновление ключей данных для использования системы" },
     *   summary = "Авторизация по логину или email",
     *   @OA\RequestBody(
     *     description = "Данные для входа",
     *     required    = true,
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(type = "object", example = { "refresh_token" = "197nmy4t70yn3v285v2n30304m3v204304", "fp" = "983r834jtyr0923v84ty0v234tmy"})
     *     )
     *   ),
     *   @OA\Response(
     *     response    = "200",
     *     description = "Токены для использования системы",
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(type = "object", example = { "refresh_token" = "xxxxxxxxxxxxxx", "access_token" = "xxxxxxxxxxxxxx" } )
     *     )
     *   ),
     *   @OA\Response(
     *     response    = "400",
     *     description = "Отправленные данные некорректны",
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(ref = "#/components/schemas/Error")
     *     )
     *   ),
     *   @OA\Response(
     *     response    = "403",
     *     description = "Отправленные данные не прошли валидацию",
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(ref = "#/components/schemas/Error")
     *     )
     *   )
     * )
     */
    public function refreshToken(array $params): array {

        HttpValidator::testParameters([
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



        $session = $this->modAdmin->modelUsersSession->find($session_id)->current();

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


        $user = $this->modAdmin->modelUsers->find($session->user_id)->current();

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
     * @OA\Post(
     *   path    = "/core/registration/email",
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
     *           "password": "nty0473vy24t7ynv2304t750vm3t5",
     *           "fp": "n7rtvy2tyv023tmyv3434"
     *         }
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response    = "200",
     *     description = "Токены для использования системы",
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(type = "object", example = { "refresh_token" = "xxxxxxxxxxxxxx", "access_token" = "xxxxxxxxxxxxxx" } )
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

        // TODO Доделать
        HttpValidator::testParameters([
            'email'    => 'req,string(1-255),email',
            'login'    => 'req,string(1-255)',
            'name'     => 'string(1-255)',
            'password' => 'req,string(1-255)',
            'fp'       => 'req,string(1-255)',
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
     * @OA\Get(
     *   path    = "/core/cabinet",
     *   tags    = { "Кабинет" },
     *   summary = "Запрос получения данных о содержимом личного кабинета",
     *   @OA\Parameter(
     *     in = "header",
     *     required = true,
     *     name = "Access-token",
     *     description = "Токен доступа",
     *     @OA\Schema(type = "string")
     *   ),
     *   @OA\Response(
     *     response    = "200",
     *     description = "Токены для использования системы",
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(type = "object", example =
     *          {
     *              "user"    = { "id" = "", "name" = "", "login" = "", "avatar" = "", },
     *              "system"  = { "name" = "NAME" },
     *              "modules" = [ { "name" = "", "title" = "" } ]
     *          }
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response    = "403",
     *     description = "Отправленные данные не прошли валидацию",
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(ref = "#/components/schemas/Error")
     *     )
     *   )
     * )
     */
    public function getCabinet(): array {

        if (empty($this->auth)) {
            throw new HttpException($this->_('У вас нет доступа к системе'), 'forbidden', '403');
        }

        $modules = $this->getModules();

        if ($this->auth->isAdmin()) {
            $modules[] = [
                'name'            => "admin",
                'title'           => "Админ",
                'icon'            => "",
                'isset_home_page' => true,
                'sections'        => [
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
     * Домашняя страница
     * @return array
     * @throws HttpException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getHome(): array {

        if (empty($this->auth)) {
            throw new HttpException($this->_('У вас нет доступа к системе'), 'forbidden', '403');
        }

        // TODO Доделать
        return ['home'];
    }


    /**
     * Данные о разделе модуля
     * @param string $module_name
     * @param string $section_name
     * @return array
     * @throws HttpException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @OA\Get(
     *   path    = "/core/mod/{name}/{section}",
     *   tags    = { "Кабинет" },
     *   summary = "Запрос получения данных раздела модуля",
     *   @OA\Parameter(
     *     in = "header",
     *     required = true,
     *     name = "Access-token",
     *     description = "Токен доступа",
     *     @OA\Schema(type = "string")
     *   ),
     *   @OA\Parameter(
     *     in = "path",
     *     required = true,
     *     name = "name",
     *     description = "Название модуля",
     *     @OA\Schema(type = "string")
     *   ),
     *   @OA\Parameter(
     *     in = "path",
     *     required = true,
     *     name = "section",
     *     description = "Название раздела",
     *     @OA\Schema(type = "string")
     *   ),
     *   @OA\Response(
     *     response    = "200",
     *     description = "Данные модуля",
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(type = "object", example = { } )
     *     )
     *   ),
     *   @OA\Response(
     *     response    = "403",
     *     description = "Отправленные данные не прошли валидацию",
     *     @OA\MediaType(
     *       mediaType = "application/json",
     *       @OA\Schema(ref = "#/components/schemas/Error")
     *     )
     *   )
     * )
     */
    public function getModuleSection(string $module_name, string $section_name): array {

        if (empty($this->auth)) {
            throw new HttpException($this->_('У вас нет доступа к системе'), 'forbidden', '403');
        }

        if ( ! $this->isAllowed($module_name)) {
            throw new HttpException($this->_("У вас нет доступа к модулю %s!", [$module_name]), 'forbidden', 403);
        }

        if ( ! $this->isAllowed("{$module_name}_{$section_name}")) {
            throw new HttpException($this->_("У вас нет доступа к разделу %s!", [$section_name]), 'forbidden', 403);
        }

        // TODO Доделать
        return [11111];
    }


    /**
     * Вызов метода для обработки данных
     * @param array $params
     * @return array
     */
    public function getModuleHandler(array $params): array {

        // TODO Доделать
        return [];
    }


    /**
     * @return array
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function getModules(): array {

        $modules     = [];
        $modules_raw = $this->db->fetchAll("
  			SELECT m.name,
  			       m.title,
  			       m.is_home_page_sw,
				   sm.name AS section_name,
				   sm.title AS section_title
			FROM core_modules AS m
				LEFT JOIN core_modules_sections AS sm ON m.id = sm.module_id AND sm.is_active_sw = 'Y'
			WHERE m.is_active_sw = 'Y'
			  AND m.is_visible_sw = 'Y'
			ORDER BY m.seq, 
			         sm.seq
        ");

        foreach ($modules_raw as $module) {
            if ( ! $this->isAllowed($module['name'], self::PRIVILEGE_READ)) {
                continue;
            }

            $sections = [];

            foreach ($modules_raw as $module_sections) {
                if ( ! $module_sections['section_name'] ||
                    $module_sections['name'] != $module['name'] ||
                    ! $this->isAllowed("{$module['name']}_{$module_sections['section_name']}", self::PRIVILEGE_READ)
                ) {
                    continue;
                }

                $sections[] = [
                    'name'  => $module_sections['section_name'],
                    'title' => $module_sections['section_title'],
                ];
            }

            $modules[] = [
                'name'            => $module['name'],
                'title'           => $module['title'],
                'icon'            => 'text_snippet',
                'isset_home_page' => $module['is_home_page_sw'] == 'Y',
                'sections'        => $sections,
            ];
        }

        return $modules;
    }
}