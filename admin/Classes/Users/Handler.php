<?php
namespace Core3\Mod\Admin\Classes\Users;
use Core3\Classes;
use Core3\Classes\Request;
use Core3\Classes\Response;
use Core3\Classes\Table;
use Core3\Classes\Tools;
use Core3\Exceptions\Exception;
use Core3\Exceptions\HttpException;
use CoreUI\Table\Adapters\Mysql\Search;
use Laminas\Cache\Exception\ExceptionInterface;
use Laminas\Db\RowGateway\AbstractRowGateway;


/**
 *
 */
class Handler extends Classes\Handler {

    private string $base_url = "admin/users";

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getUsers(Request $request): array {

        $content   = [];
        $content[] = $this->getJsModule('admin', 'assets/users/js/admin.users.js');
        $content[] = (new View())->getTable();

        $panel = new \CoreUI\Panel();
        $panel->setContent($content);

        return $panel->toArray();
    }


    /**
     * Сохранение пользователя
     * @param Request $request
     * @param int     $user_id
     * @return Response
     * @throws HttpException
     * @throws Exception
     * @throws ExceptionInterface
     */
	public function saveUser(Request $request, int $user_id): Response {

        $this->checkHttpMethod($request, 'put');
        $this->checkVersion($this->modAdmin->tableUsers, $request);

        $fields = [
            'email'       => 'email: ' . $this->_('Email'),
            'role_id'     => 'req,int(1-): ' . $this->_('Роль'),
            'pass'        => 'string(4-): ' . $this->_('Пароль'),
            'fname'       => 'string(0-255): ' . $this->_('Имя'),
            'lname'       => 'string(0-255): ' . $this->_('Фамилия'),
            'mname'       => 'string(0-255): ' . $this->_('Отчество'),
            'is_admin'    => 'string(1|0): ' . $this->_('Администратор безопасности'),
            'is_active'   => 'string(1|0): ' . $this->_('Активен'),
            'avatar_type' => 'string(none|generate|upload): ' . $this->_('Аватар'),
        ];

        $controls = $request->getJsonContent() ?? [];
        $controls = $this->clearData($controls);

        $files = null;

        if ( ! empty($controls['avatar'])) {
            $files = $controls['avatar'];
            unset($controls['avatar']);
        }

        if ($errors = $this->validateFields($fields, $controls)) {
            return $this->getResponseError($errors);
        }

        if ( ! empty($controls['email']) &&
             ! $this->modAdmin->tableUsers->isUniqueEmail($controls['email'], $user_id)
        ) {
            throw new HttpException(400, $this->_("Пользователь с таким email уже существует."));
        }

        if ( ! empty($controls['pass'])) {
            $controls['pass'] = Tools::passSalt(md5($controls['pass']));

        } elseif (isset($controls['pass'])) {
            unset($controls['pass']);
        }


        $this->db->beginTransaction();
        try {
            $this->modAdmin->modelUsers->update($user_id, $controls);

            if ($controls['avatar_type'] != 'upload') {
                $files = [];
            }

            $this->saveFiles($this->modAdmin->tableUsersFiles, $user_id, $files, 'avatar');

            if ($controls['avatar_type'] == 'generate') {
                $user = $this->modAdmin->tableUsers->getRowById($user_id);
                (new Files())->generateAvatar($user);
            }

            $this->db->commit();

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->getResponseSuccess([
            'id' => $user_id
        ]);
    }


    /**
     * Сохранение пользователя
     * @param Request $request
     * @return Response
     * @throws HttpException
     * @throws Exception
     */
	public function saveUserNew(Request $request): Response {

        $this->checkHttpMethod($request, ['post', 'put']);
        $this->checkVersion($this->modAdmin->tableUsers, $request);

        $fields = [
            'login'        => 'req,string(1-255),chars(alphanumeric|_|\\): Логин',
            'email'        => 'email: Email',
            'role_id'      => 'req,int(1-): Роль',
            'pass'         => 'req,string(4-): Пароль',
            'fname'        => 'string(0-255): Имя',
            'lname'        => 'string(0-255): Фамилия',
            'mname'        => 'string(0-255): Отчество',
            'is_admin'     => 'string(1|0): Администратор безопасности',
            'is_active'    => 'string(1|0): Активен',
            'avatar_type'  => 'string(none|generate|upload): Аватар',
        ];

        $controls = $request->getJsonContent() ?? [];
        $controls = $this->clearData($controls);


        $files = null;

        if ( ! empty($controls['avatar'])) {
            $files = $controls['avatar'];
            unset($controls['avatar']);
        }

        if ($errors = $this->validateFields($fields, $controls)) {
            return $this->getResponseError($errors);
        }

        if ( ! $this->modAdmin->tableUsers->isUniqueLogin($controls['login'])) {
            throw new HttpException(400, $this->_("Пользователь с таким логином уже существует"));
        }

        if ( ! empty($controls['email']) &&
             ! $this->modAdmin->tableUsers->isUniqueEmail($controls['email'])
        ) {
            throw new HttpException(400, $this->_("Пользователь с таким email уже существует"));
        }

        $controls['pass'] = Tools::passSalt(md5($controls['pass']));

        $this->db->beginTransaction();
        try {
            $user_id = $this->modAdmin->modelUsers->create($controls);

            if ($controls['avatar_type'] == 'upload') {
                $this->saveFiles($this->modAdmin->tableUsersFiles, $user_id, $files, 'avatar');

            } elseif ($controls['avatar_type'] == 'generate') {
                $user = $this->modAdmin->tableUsers->getRowById($user_id);
                (new Files())->generateAvatar($user);
            }

            $this->db->commit();

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->getResponseSuccess([
            'id' => $user_id
        ]);
    }


    /**
     * Изменение активности для пользователя
     * @param Request $request
     * @param int     $user_id
     * @return Response
     * @throws Exception
     * @throws \Core3\Exceptions\DbException
     * @throws HttpException
     */
    public function switchUserActive(Request $request, int $user_id): Response {

        $this->checkHttpMethod($request, 'patch');
        $controls = $request->getJsonContent();

        if ( ! in_array($controls['checked'], ['1', '0'])) {
            throw new HttpException(400, $this->_("Некорректные данные запроса"));
        }

        $user = $this->modAdmin->tableUsers->getRowById($user_id);

        if (empty($user)) {
            throw new HttpException(400, $this->_("Указанный пользователь не найден"));
        }

        $user->is_active = $controls['checked'];
        $user->save();

        return $this->getResponseSuccess([
            'status' => 'success'
        ]);
    }


    /**
     * Изменение активности для сессии пользователя
     * @param Request $request
     * @param int     $user_id
     * @param int     $session_id
     * @return Response
     * @throws Exception
     * @throws HttpException
     */
    public function switchUserSession(Request $request, int $user_id, int $session_id): Response {

        $this->checkHttpMethod($request, 'patch');
        $controls = $request->getJsonContent();

        if ( ! in_array($controls['checked'], ['1', '0'])) {
            throw new HttpException(400, $this->_("Некорректные данные запроса"));
        }

        $user_session = $this->modAdmin->tableUsersSession->getRowById($session_id);

        if (empty($user_session)) {
            throw new HttpException(400, $this->_("Указанный пользователь не найден"));
        }

        $user_session->is_active = $controls['checked'];
        $user_session->save();

        return $this->getResponseSuccess([
            'status' => 'success'
        ]);
    }


    /**
     * Изменение активности для пользователя
     * @param Request $request
     * @param int     $user_id
     * @return Response
     * @throws Exception
     * @throws \Core3\Exceptions\DbException
     * @throws HttpException
     */
    public function getAvatarDownload(Request $request, int $user_id): Response {

        $file = $this->modAdmin->tableUsersFiles->getRowsByUser($user_id);

        if ( ! $file) {
            throw new HttpException(404, $this->_('Указанный файл не найден'), 'file_not_found');
        }

        return $this->getFileDownload($file);
    }


    /**
     * Удаление пользователей
     * @param Request $request
     * @return Response
     * @throws Exception
     * @throws HttpException
     */
    public function deleteUsers(Request $request): Response {

        $this->checkHttpMethod($request, 'delete');

        $controls = $request->getJsonContent();

        if (empty($controls['id'])) {
            throw new HttpException(400, $this->_("Не указаны пользователи"));
        }

        if ( ! is_array($controls['id'])) {
            throw new HttpException(400, $this->_("Некорректный список пользователей"));
        }

        foreach ($controls['id'] as $user_id) {
            if ( ! empty($user_id) && is_numeric($user_id)) {
                $this->modAdmin->tableUsers->getRowById((int)$user_id)?->delete();
            }
        }

        return $this->getResponseSuccess([
            'status' => 'success'
        ]);
    }


    /**
     * Данные для таблицы
     * @param Request $request
     * @return Response
     * @throws \CoreUI\Table\Exception
     */
    public function getRecordsUsers(Request $request): Response {

        $table = new Table\Db($request);

        $sort = $request->getQuery('sort');

        if ($sort && is_array($sort)) {
            $table->setSort($sort, [
                'avatar'          => 'u.avatar',
                'login'           => 'u.login',
                'name'            => "CONCAT_WS(' ', u.lname, u.fname, u.mname)",
                'email'           => 'u.email',
                'role_title'      => 'r.title',
                'active_sessions' => '(SELECT COUNT(1) FROM core_users_sessions AS us WHERE u.id = us.user_id AND us.is_active AND us.date_expired > NOW())',
                'date_activity'   => '(SELECT us.date_last_activity FROM core_users_sessions AS us WHERE u.id = us.user_id ORDER BY date_last_activity DESC LIMIT 1)',
                'date_created'    => 'u.date_created',
                'is_active'       => "u.is_active = '1'",
                'is_admin'        => 'u.is_admin',
            ]);
        }


        $search = $request->getQuery('search');

        if ($search && is_array($search)) {
            $table->setSearch($search, [
                'login'           => (new Search\Like())->setField('u.login'),
                'email'           => (new Search\Like())->setField('u.email'),
                'name'            => (new Search\Like())->setField("name"),
                'role'            => (new Search\Equal())->setField('u.role_id'),
                'date_created'    => (new Search\Between())->setField('u.date_created'),
                'date_activity'   => (new Search\Between())->setField('(SELECT us.date_last_activity FROM core_users_sessions AS us WHERE u.id = us.user_id ORDER BY date_last_activity DESC LIMIT 1)'),
                'active_sessions' => (new Search\Between())->setField('(SELECT COUNT(1) FROM core_users_sessions AS us WHERE u.id = us.user_id AND us.is_active AND us.date_expired > NOW())'),
                'is_admin'        => (new Search\In())->setField('u.is_admin'),
                'is_active'       => (new Search\In())->setField('u.is_active'),
            ]);
        }

        $table->setQuery("
            SELECT u.id,
                   u.login,
                   u.email,
                   u.name,
                   u.is_active,
                   u.is_admin,
                   u.date_created,
                   r.title AS role_title,
                   
                   (SELECT COUNT(1)
                    FROM core_users_sessions AS us 
                    WHERE u.id = us.user_id
                      AND us.is_active
                      AND us.date_expired > NOW()) AS active_sessions,
                   
                   (SELECT us.date_last_activity
                    FROM core_users_sessions AS us 
                    WHERE u.id = us.user_id
                    ORDER BY date_last_activity DESC
                    LIMIT 1) AS date_activity
            FROM core_users AS u
                LEFT JOIN core_roles AS r ON u.role_id = r.id
            ORDER BY u.is_active DESC,
                     u.login
        ");

        $records = $table->fetchRecords();

        foreach ($records as $record) {

            $record->login    = ['content' => $record->login, 'url' => "#/admin/users/{$record->id}"];
            $record->avatar   = "sys/user/{$record->id}/avatar";
            $record->is_admin = $record->is_admin
                ? [ 'type' => 'danger', 'text' => $this->_('Да') ]
                : [ 'type' => 'none',   'text' => $this->_('Нет') ];

            $record->active_sessions = $record->active_sessions ?: 0;

            $record->login_user  = [
                'content' => 'Войти',
                'attr'    => ['class' => 'btn btn-sm btn-secondary'],
                'onClick' => "adminUsers.loginUser('{$record->id}');",
            ];
        }

        return $this->getResponseSuccess($table->getResult());
    }


    /**
     * Данные для таблицы сессий
     * @param Request $request
     * @param int $user_id
     * @return Response
     * @throws \CoreUI\Table\Exception
     */
    public function getRecordsSessions(Request $request, int $user_id): Response {

        $table = new Table\Db($request);

        $sort = $request->getQuery('sort');

        if ($sort && is_array($sort)) {
            $table->setSort($sort, [
                'client_ip'          => 'us.client_ip',
                'agent'              => 'IF(us.agent_title IS NOT NULL, us.agent_title, us.agent_name)',
                'count_requests'     => "us.count_requests",
                'date_last_activity' => 'us.date_last_activity',
                'date_expired'       => 'us.date_expired',
                'is_active'          => 'us.is_active',
                'place'              => "CONCAT_WS(' ', us.country_name, us.country_code, us.region, us.city)",
            ]);
        }


        $search = $request->getQuery('search');

        if ($search && is_array($search)) {
            $table->setSearch($search, [
                'search'             => (new Search\Like())->setField("CONCAT_WS('|', CONCAT_WS(' ', us.country_name, us.country_code, us.region, us.city), IF(us.agent_title IS NOT NULL, us.agent_title, us.agent_name), us.client_ip)"),
                'place'              => (new Search\Like())->setField("CONCAT_WS(' ', us.country_name, us.country_code, us.region, us.city)"),
                'agent'              => (new Search\Like())->setField("IF(us.agent_title IS NOT NULL, us.agent_title, us.agent_name)"),
                'client_ip'          => (new Search\Like())->setField("us.client_ip"),
                'date_last_activity' => (new Search\Between())->setField("us.date_last_activity"),
                'date_expired'       => (new Search\Between())->setField("us.date_expired"),
                'count_requests'     => (new Search\Between())->setField("us.count_requests"),
                'is_active'          => (new Search\Custom())->setField("us.is_active = 1 AND us.date_expired > NOW()"),
            ]);
        }

        $table->setQuery("
            SELECT us.id,
                   us.user_id,
                   us.client_ip,
                   IF(us.agent_title IS NOT NULL, us.agent_title, us.agent_name) AS agent,
                   us.count_requests,
                   us.date_last_activity,
                   us.date_expired,
                   us.is_active,
                   CONCAT_WS(' ', us.country_name, us.country_code, us.region, us.city) AS place
            FROM core_users_sessions AS us
            WHERE us.user_id = ?
            ORDER BY date_expired DESC
        ", [
            $user_id
        ]);

        $records = $table->fetchRecords();
        $now     = date('Y-m-d H:i:s');

        foreach ($records as $record) {

            if ($record->date_expired) {
                if ($record->date_expired < $now) {
                    $record->date_expired = date('d.m.Y H:i:s', strtotime($record->date_expired));
                    $record->date_expired = "<span class=\"text-danger\">{$record->date_expired}</span>";

                } else {
                    $record->date_expired = date('d.m.Y H:i:s', strtotime($record->date_expired));
                }
            }
        }

        return $this->getResponseSuccess($table->getResult());
    }


    /**
     * @param Request $request
     * @param int $user_id
     * @param string|null $tab
     * @return array
     * @throws Exception
     * @throws HttpException
     * @throws \Exception
     */
    public function getUserPanel(Request $request, int $user_id, string $tab = null): array {

        $breadcrumb = new \CoreUI\Breadcrumb();
        $breadcrumb->addItem($this->_('Пользователи'), "#/{$this->base_url}");
        $breadcrumb->addItem($this->_('Пользователь'));

        $result   = [];
        $result[] = $breadcrumb->toArray();

        $panel = new \CoreUI\Panel();
        $panel->setContentFit($panel::FIT_MIN);
        $panel->setTabsType($panel::TABS_TYPE_UNDERLINE);

        if ($user_id > 0) {
            $user = $this->modAdmin->tableUsers->getRowById($user_id);

            if (empty($user)) {
                throw new HttpException(404, $this->_('Указанный пользователь не найден'));
            }

            $name   = trim("{$user->lname} {$user->fname} {$user->mname}");
            $avatar = "<img src=\"sys/user/{$user->id}/avatar\" style=\"width: 32px;height: 32px\" class=\"rounded-circle border border-secondary-subtle\"> ";
            $panel->setTitle($avatar . ($name ?: $user->login));


            $sessions_count = $this->modAdmin->tableUsersSession->getCountUser($user_id);

            $panel->addTab($this->_('Пользователь'), 'user')
                ->setUrlWindow("#/{$this->base_url}/{$user_id}/user")
                ->setUrlContent("{$this->base_url}/{$user_id}/user/form");
            $panel->addTab($this->_('Сессии'), 'sessions')
                ->setUrlWindow("#/{$this->base_url}/{$user_id}/sessions")
                ->setUrlContent("{$this->base_url}/{$user_id}/sessions/table")
                ->setCount($sessions_count);

            $panel->setActiveTab($tab ?: 'user');

            switch ($tab) {
                case 'user':
                default:
                    $content[] = $this->getUserForm($request, $user_id);
                    break;

                case 'sessions':
                    $content[] = $this->getUserSessions($request, $user_id);
                    break;
            }

        } else {
            $panel->setTitle($this->_('Добавление пользователя'));
            $content[] = (new View())->getFormNew();
        }

        $panel->setContent($content);
        $result[] = $panel->toArray();

        return $result;
    }


    /**
     * @param Request $request
     * @param int $user_id
     * @return array
     * @throws HttpException
     * @throws Exception
     */
    public function getUserForm(Request $request, int $user_id): array {

        $user = $this->modAdmin->tableUsers->getRowById($user_id);

        if (empty($user)) {
            throw new HttpException(404, $this->_('Указанный пользователь не найден'));
        }

        return (new View())->getForm($user);
    }


    /**
     * @param Request $request
     * @param int $user_id
     * @return array
     * @throws HttpException
     * @throws Exception
     */
    public function getUserSessions(Request $request, int $user_id): array {

        $user = $this->modAdmin->tableUsers->getRowById($user_id);

        if (empty($user)) {
            throw new HttpException(404, $this->_('Указанный пользователь не найден'));
        }

        return (new View())->getTableSessions($user);
    }


    /**
     * Вход под другим пользователем
     * @param Request $request
     * @return Response
     * @throws HttpException
     */
    public function loginUser(Request $request): Response {

        $user_id = $request->getPost()['user_id'] ?? '';

        if (empty($user_id)) {
            throw new HttpException(400, $this->_('Не задан id пользователя'), 'user_id_not_found');
        }

        $user = $this->modAdmin->tableUsers->getRowById($user_id);

        if (empty($user)) {
            throw new HttpException(400, $this->_('Указанный пользователь не найден'), 'user_not_found');
        }

        $session_id = $this->auth->getSessionId();
        $session    = $this->modAdmin->tableUsersSession->getRowById($session_id);

        $session->user_id = $user->id;
        $session->save();

        return $this->getResponseSuccess([
            'status' => 'success'
        ]);
    }
}