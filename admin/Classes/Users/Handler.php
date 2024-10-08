<?php
namespace Core3\Mod\Admin\Classes\Users;
use Core3\Classes;
use Core3\Classes\Request;
use Core3\Classes\Response;
use Core3\Classes\Table;
use Core3\Classes\Tools;
use Core3\Exceptions\AppException;
use Core3\Exceptions\Exception;
use Core3\Exceptions\HttpException;
use CoreUI\Table\Adapters\Mysql\Search;
use Laminas\Cache\Exception\ExceptionInterface;
use Laminas\Db\RowGateway\AbstractRowGateway;


/**
 *
 */
class Handler extends Classes\Handler {

    private string $base_url = "#/admin/users";

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function getUsers(Request $request): array {

        $content   = [];
        $content[] = $this->getJsModule('admin', 'assets/users/js/admin.users.js');

        $view = new View();
        $content[] = $view->getTable($this->base_url);


        $panel  = new \CoreUI\Panel();
        $panel->setContent($content);

        return $panel->toArray();
    }


    /**
     * Сохранение пользователя
     * @param Request $request
     * @return Response
     * @throws AppException
     * @throws HttpException
     * @throws Exception
     * @throws ExceptionInterface
     */
	public function saveUser(Request $request): Response {

        $this->checkHttpMethod($request, 'put');
        $this->checkVersion($this->modAdmin->tableUsers, $request);

        $fields = [
            'email'        => 'email: Email',
            'role_id'      => 'req,int(1-): Роль',
            'pass'         => 'string(4-): Пароль',
            'fname'        => 'string(0-255): Имя',
            'lname'        => 'string(0-255): Фамилия',
            'mname'        => 'string(0-255): Отчество',
            'is_admin'     => 'string(1|0): Администратор безопасности',
            'is_active'    => 'string(1|0): Активен',
            'avatar_type'  => 'string(none|generate|upload): Аватар',
        ];

        $record_id = $request->getQuery('id');
        $controls  = $request->getFormContent() ?? [];
        $controls  = $this->clearData($controls);

        $avatar = null;

        if ( ! empty($controls['avatar'])) {
            $avatar = $controls['avatar'];
            unset($controls['avatar']);
        }

        if ($errors = $this->validateFields($fields, $controls)) {
            return $this->getResponseError($errors);
        }

        if ( ! empty($controls['email']) &&
             ! $this->modAdmin->tableUsers->isUniqueEmail($controls['email'], $record_id)
        ) {
            throw new AppException($this->_("Пользователь с таким email уже существует."));
        }

        if ( ! empty($controls['pass'])) {
            $controls['pass'] = Tools::passSalt(md5($controls['pass']));

        } elseif (isset($controls['pass'])) {
            unset($controls['pass']);
        }

        $controls['name'] = trim(implode(' ', [
            $controls['lname'] ?? '',
            $controls['fname'] ?? '',
            $controls['mname'] ?? ''
        ]));


        $this->db->beginTransaction();
        try {
            $row_old = $this->modAdmin->tableUsers->getRowById($record_id);
            $row     = $this->saveData($this->modAdmin->tableUsers, $controls, $record_id);

            if ($controls['avatar_type'] != 'upload') {
                $avatar = [];
            }

            $this->saveFiles($this->modAdmin->tableUsersFiles, $row->id, 'avatar', $avatar);

            if ($controls['avatar_type'] == 'generate') {
                $this->generateAvatar($row);
            }

            if ($row_old->is_active != $row->is_active) {
                $this->event($this->modAdmin->tableUsers->getTable() . '_active', [
                    'id'        => $row->id,
                    'is_active' => $row->is_active == '1',
                ]);
            }

            $this->db->commit();

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->getResponseSuccess([
            'id' => $row->id
        ]);
    }


    /**
     * Сохранение пользователя
     * @param Request $request
     * @return Response
     * @throws AppException
     * @throws HttpException
     * @throws Exception
     * @throws ExceptionInterface
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

        $controls = $request->getFormContent() ?? [];
        $controls = $this->clearData($controls);


        $avatar = null;

        if ( ! empty($controls['avatar'])) {
            $avatar = $controls['avatar'];
            unset($controls['avatar']);
        }

        if ($errors = $this->validateFields($fields, $controls)) {
            return $this->getResponseError($errors);
        }

        if ( ! $this->modAdmin->tableUsers->isUniqueLogin($controls['login'])) {
            throw new AppException($this->_("Пользователь с таким логином уже существует"));
        }

        if ( ! empty($controls['email']) &&
             ! $this->modAdmin->tableUsers->isUniqueEmail($controls['email'])
        ) {
            throw new AppException($this->_("Пользователь с таким email уже существует"));
        }

        $controls['pass'] = Tools::passSalt(md5($controls['pass']));
        $controls['name'] = trim(implode(' ', [
            $controls['lname'] ?? '',
            $controls['fname'] ?? '',
            $controls['mname'] ?? ''
        ]));


        $this->db->beginTransaction();
        try {
            $row = $this->saveData($this->modAdmin->tableUsers, $controls);

            if ($controls['avatar_type'] == 'upload') {
                $this->saveFiles($this->modAdmin->tableUsersFiles, $row->id, 'avatar', $avatar);

            } elseif ($controls['avatar_type'] == 'generate') {
                $this->generateAvatar($row);
            }

            $this->event($this->modAdmin->tableUsers->getTable() . '_active', [
                'id'        => $row->id,
                'is_active' => $controls['is_active'] == '1',
            ]);

            $this->db->commit();

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->getResponseSuccess([
            'id' => $row->id
        ]);
    }


    /**
     * Изменение активности для пользователя
     * @param Request $request
     * @return Response
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Core3\Exceptions\DbException
     * @throws AppException
     */
    public function switchUserActive(Request $request): Response {

        $this->checkHttpMethod($request, 'patch');
        $controls = $request->getJsonContent();

        if ( ! in_array($controls['checked'], ['Y', 'N'])) {
            return $this->getResponseError([ $this->_("Некорректные данные запроса") ]);
        }

        $user_id = $request->getQuery('id');

        if ( ! $user_id) {
            return $this->getResponseError([ $this->_("Не указан id пользователя") ]);
        }

        if ( ! is_numeric($user_id)) {
            return $this->getResponseError([ $this->_("Указан некорректный id пользователя") ]);
        }

        $this->modAdmin->modelUsers->switchActive($user_id, $controls['checked'] == 'Y');

        return $this->getResponseSuccess([
            'status' => 'success'
        ]);
    }


    /**
     * Удаление пользователей
     * @param Request $request
     * @return Response
     * @throws Exception
     * @throws ExceptionInterface
     * @throws AppException
     */
    public function deleteUsersTable(Request $request): Response {

        $this->checkHttpMethod($request, 'delete');

        $controls = $request->getJsonContent();

        if (empty($controls['id'])) {
            return $this->getResponseError([ $this->_("Не указаны пользователи") ]);
        }

        if ( ! is_array($controls['id'])) {
            return $this->getResponseError([ $this->_("Некорректный список пользователей") ]);
        }

        foreach ($controls['id'] as $user_id) {
            if ( ! empty($user_id) && is_numeric($user_id)) {
                $this->modAdmin->modelUsers->delete((int)$user_id);
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
    public function getUsersTable(Request $request): Response {

        $table = new Table\Db($request);

        $sort = $request->getQuery('sort');

        if ($sort && is_array($sort)) {
            $table->setSort($sort, [
                'avatar'        => 'u.avatar',
                'login'         => 'u.login',
                'name'          => "CONCAT_WS(' ', u.lname, u.fname, u.mname)",
                'email'         => 'u.email',
                'role_title'    => 'r.title',
                'date_activity' => '(SELECT us.date_last_activity FROM core_users_sessions AS us WHERE u.id = us.user_id ORDER BY date_last_activity DESC LIMIT 1)',
                'date_created'  => 'u.date_created',
                'is_active'     => "u.is_active = '1'",
                'is_admin'      => 'u.is_admin',
            ]);
        }


        $search = $request->getQuery('search');

        if ($search && is_array($search)) {
            $table->setSearch($search, [
                'login'        => (new Search\Like())->setField('u.login'),
                'role'         => (new Search\Equal())->setField('u.role_id'),
                'date_created' => (new Search\Between())->setField('u.date_created'),
                'is_admin'     => (new Search\Equal())->setField('u.is_admin'),
            ]);
        }

        $table->setQuery("
            SELECT u.id,
                   u.login,
                   u.email,
                   CONCAT_WS(' ', u.lname, u.fname, u.mname) AS name,
                   u.is_active,
                   u.is_admin,
                   u.date_created,
                   r.title AS role_title,
                   
                   (SELECT us.date_last_activity
                    FROM core_users_sessions AS us 
                    WHERE u.id = us.user_id
                    ORDER BY date_last_activity DESC
                    LIMIT 1) AS date_activity
            FROM core_users AS u
                LEFT JOIN core_roles AS r ON u.role_id = r.id
        ");

        $records = $table->fetchRecords();

        foreach ($records as $record) {

            $record->login    = ['content' => $record->login, 'url' => "#/admin/users/{$record->id}"];
            $record->avatar   = "sys/user/{$record->id}/avatar";
            $record->is_admin = $record->is_admin
                ? [ 'type' => 'danger', 'text' => $this->_('Да') ]
                : [ 'type' => 'none',   'text' => $this->_('Нет') ];

            $record->login_user  = [
                'content' => 'Войти',
                'attr'    => ['class' => 'btn btn-sm btn-secondary'],
                'onClick' => "adminUsers.loginUser('{$record->id}');",
            ];
        }

        return $this->getResponseSuccess($table->getResult());
    }


    /**
     * @param Request $request
     * @param int     $user_id
     * @return array
     * @throws AppException
     * @throws Exception
     */
    public function getUser(Request $request, int $user_id): array {

        $breadcrumb = new \CoreUI\Breadcrumb();
        $breadcrumb->addItem('Пользователи', $this->base_url);
        $breadcrumb->addItem('Пользователь');

        $result  = [];
        $result[] = $breadcrumb->toArray();


        $view  = new View();
        $panel = new \CoreUI\Panel();
        $panel->setContentFit($panel::FIT_MIN);


        if ( ! empty($user_id)) {
            $user = $this->modAdmin->tableUsers->getRowById($user_id);

            if (empty($user)) {
                throw new AppException('Указанный пользователь не найден');
            }

            $name   = trim("{$user->lname} {$user->fname} {$user->mname}");
            $avatar = "<img src=\"sys/user/{$user->id}/avatar\" style=\"width: 32px;height: 32px\" class=\"rounded-circle border border-secondary-subtle\"> ";
            $panel->setTitle($avatar . ($name ?: $user->login), $this->_('Редактирование пользователя'));

            $content[] = $view->getForm($this->base_url, $user);

        } else {
            $panel->setTitle($this->_('Добавление пользователя'));
            $content[] = $view->getFormNew($this->base_url);
        }

        $panel->setContent($content);
        $result[] = $panel->toArray();

        return $result;
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
            throw new HttpException(400, 'user_id_not_found', $this->_('Не задан id пользователя'));
        }

        $user = $this->modAdmin->tableUsers->getRowById($user_id);

        if (empty($user)) {
            throw new HttpException(400, 'user_not_found', $this->_('Указанный пользователь не найден'));
        }

        $session_id = $this->auth->getSessionId();
        $session    = $this->modAdmin->tableUsersSession->getRowById($session_id);

        $session->user_id = $user->id;
        $session->save();

        return $this->getResponseSuccess([
            'status' => 'success'
        ]);
    }


    /**
     * Формирование аватара
     * @param AbstractRowGateway $user
     * @return void
     */
    private function generateAvatar(AbstractRowGateway $user): void {

        $icon = new \Jdenticon\Identicon();
        $icon->setValue($user->login);
        $icon->setSize(200);

        $file = $icon->getImageData('png');

        $this->modAdmin->tableUsersFiles->insert([
            'ref_id'     => $user->id,
            'file_name'  => 'avatar.png',
            'file_size'  => strlen($file),
            'file_hash'  => md5($file),
            'file_type'  => 'image/png',
            'field_name' => 'avatar',
            'thumb'      => null,
            'content'    => $file,
        ]);
    }
}