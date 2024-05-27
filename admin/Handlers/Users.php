<?php
namespace Core3\Mod\Admin\Handlers;
use Core3\Classes\Handler;
use Core3\Classes\Http\Request;
use Core3\Classes\Http\Response;
use Core3\Classes\Tools;
use Core3\Exceptions\AppException;
use Core3\Exceptions\HttpException;
use Core3\Exceptions\Exception;
use CoreUI\Table;
use CoreUI\Table\Adapters\Mysql\Search;
use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use Laminas\Cache\Exception\ExceptionInterface;
use Laminas\Db\RowGateway\AbstractRowGateway;


/**
 *
 */
class Users extends Handler {


    /**
     * Сохранение пользователя
     * @param Request $request
     * @return Response
     * @throws AppException
     * @throws HttpException
     * @throws Exception
     * @throws ExceptionInterface
     */
	public function save(Request $request): Response {

        $this->checkHttpMethod($request, 'put');
        $this->checkVersion($this->modAdmin->tableUsers, $request);

        $fields = [
            'email'        => 'email: Email',
            'role_id'      => 'req,int(1-): Роль',
            'pass'         => 'string(4-): Пароль',
            'fname'        => 'string(0-255): Имя',
            'lname'        => 'string(0-255): Фамилия',
            'mname'        => 'string(0-255): Отчество',
            'is_admin_sw'  => 'string(Y|N): Администратор безопасности',
            'is_active_sw' => 'string(Y|N): Активен',
            'avatar_type'  => 'string(none|generate|upload): Аватар',
        ];

        $record_id = $request->getQuery('id');
        $controls  = $request->getFormContent() ?? [];
        $controls  = $this->clearData($controls);

        if (isset($controls['login'])) {
            unset($controls['login']);
        }

        $avatar = null;

        if ( ! empty($controls['avatar'])) {
            $avatar = $controls['avatar'];
            unset($controls['avatar']);
        }

        if ($errors = $this->validateFields($fields, $controls, true)) {
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

            if ($row_old->is_active_sw != $row->is_active_sw) {
                $this->event($this->modAdmin->tableUsers->getTable() . '_active', [
                    'id'        => $row->id,
                    'is_active' => $row->is_active_sw == 'Y',
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
	public function saveNew(Request $request): Response {

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
            'is_admin_sw'  => 'string(Y|N): Администратор безопасности',
            'is_active_sw' => 'string(Y|N): Активен',
            'avatar_type'  => 'string(none|generate|upload): Аватар',
        ];

        $controls = $request->getFormContent() ?? [];
        $controls = $this->clearData($controls);


        $avatar = null;

        if ( ! empty($controls['avatar'])) {
            $avatar = $controls['avatar'];
            unset($controls['avatar']);
        }

        if ($errors = $this->validateFields($fields, $controls, true)) {
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
                'is_active' => $controls['is_active_sw'] == 'Y',
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
    public function switchActive(Request $request): Response {

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
    public function delete(Request $request): Response {

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
     * @throws Table\Exception
     */
    public function table(Request $request): Response {

        $table = new Table\Adapters\Mysql();
        $table->setConnection($this->db->getDriver()->getConnection()->getResource());

        $table->setPage($request->getQuery('page') ?? 1, $request->getQuery('count') ?? 25);

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
                'is_admin_sw'   => 'u.is_admin_sw',
            ]);
        }


        $search = $request->getQuery('search');

        if ($search && is_array($search)) {
            $table->setSearch($search, [
                'login'        => (new Search\Like())->setField('u.login'),
                'role'         => (new Search\Equal())->setField('u.role_id'),
                'date_created' => (new Search\Between())->setField('u.date_created'),
                'is_admin_sw'  => (new Search\Equal())->setField('u.is_admin_sw'),
            ]);
        }

        $table->setQuery("
            SELECT u.id,
                   u.login,
                   u.email,
                   CONCAT_WS(' ', u.lname, u.fname, u.mname) AS name,
                   u.is_active_sw,
                   u.is_admin_sw,
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

            $record->login       = ['content' => $record->login, 'url' => "#/admin/users/{$record->id}"];
            $record->avatar      = "<img src=\"core3/user/{$record->id}/avatar\" style=\"width: 20px; height: 20px\" class=\"rounded-circle border border-secondary-subtle\"/>";
            $record->is_admin_sw = $record->is_admin_sw == 'Y' ? '<span class="badge text-bg-danger">Да</span>' : 'Нет';
            $record->login_user  = [
                'content' => 'Войти',
                'attr'    => ['class' => 'btn btn-sm btn-secondary'],
                'onClick' => "adminUsers.loginUser('{$record->id}');",
            ];
        }

        return $this->getResponseSuccess($table->getResult());
    }


    /**
     * Вход под пользователем
     * @param Request $request
     * @return Response
     */
    public function loginUser(Request $request): Response {



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