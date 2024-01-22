<?php
namespace Core3\Mod\Admin\Handlers;
use Core3\Classes\Handler;
use Core3\Classes\Http\Request;
use Core3\Classes\Http\Response;
use Core3\Classes\Tools;
use Core3\Exceptions\AppException;
use Core3\Exceptions\HttpException;
use Core3\Exceptions\Exception;
use Laminas\Cache\Exception\ExceptionInterface;


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

        if ( ! in_array($request->getMethod(), ['post', 'put'])) {
            return $this->getResponseError([ $this->_("Некорректный метод запроса. Ожидается POST либо PUT") ]);
        }

        $user_id = $request->getQuery('id');

        if ($user_id) {
            $this->checkVersion($this->modAdmin->tableUsers, $user_id, $request->getQuery('v'));
        }

        $fields = [
            'email'              => 'email: Email',
            'role_id'            => 'req,int(1-): Роль',
            'pass'               => 'string(4-): Пароль',
            'fname'              => 'string(0-255): Имя',
            'lname'              => 'string(0-255): Фамилия',
            'mname'              => 'string(0-255): Отчество',
            'is_pass_changed_sw' => 'string(Y|N): Предупреждение о смене пароля',
            'is_admin_sw'        => 'string(Y|N): Администратор безопасности',
            'is_active_sw'       => 'string(Y|N): Активен',
        ];

        $controls = $request->getFormContent()['control'] ?? [];

        if ( ! $user_id) {
            $fields['login'] = 'req,string(1-255),chars(alphanumeric|_|\\): Логин';

            if (empty($this->config?->system?->ldap?->active)) {
                $fields['pass'] = 'req,string(4-): Пароль';
            }
        }

        $controls = $this->clearData($controls);

        if ($errors = $this->validateFields($fields, $controls, true)) {
            return $this->getResponseError($errors);
        }

        if ( ! empty($controls['login']) && ! $this->modAdmin->tableUsers->isUniqueLogin($controls['login'], $user_id)) {
            throw new AppException($this->_("Пользователь с таким логином уже существует."));
        }

        if (empty($controls['email'])) {
            unset($controls['email']);

        } elseif ( ! $this->modAdmin->tableUsers->isUniqueEmail($controls['email'], $user_id)) {
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

        $user_old = $user_id ? $this->modAdmin->tableUsers->getRowById($user_id) : null;
        $row_id   = $this->saveData($this->modAdmin->tableUsers, $controls, $user_id);


        if ($user_old && $user_old['is_active_sw'] != $controls['is_active_sw']) {
            $this->event($this->modAdmin->tableUsers->getTable() . '_active', [
                'id'        => $row_id,
                'is_active' => $controls['is_active_sw'] == 'Y',
            ]);
        }


        return $this->getResponseSuccess([
            'id' => $row_id
        ]);
    }


    /**
     * Изменение активности для пользователя
     * @param Request $request
     * @return Response
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Core3\Exceptions\DbException
     */
    public function switchActive(Request $request): Response {

        if ($request->getMethod() != 'patch') {
            return $this->getResponseError([ $this->_("Некорректный метод запроса. Ожидается PATCH") ]);
        }

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
     */
    public function delete(Request $request): Response {

        if ($request->getMethod() != 'delete') {
            return $this->getResponseError([ $this->_("Некорректный метод запроса. Ожидается DELETE") ]);
        }

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
     */
    public function table(Request $request): Response {

        $page   = (int)$request->getQuery('page');
        $count  = (int)($request->getQuery('count') ?: 25);
        $offset = (int)(($page - 1) * $count);

        $users = $this->db->fetchAll("
            SELECT SQL_CALC_FOUND_ROWS u.id,
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
                    LIMIT 1) AS date_last_activity
            FROM core_users AS u
                LEFT JOIN core_roles AS r ON u.role_id = r.id
            LIMIT {$offset}, {$count}
        ");

        $total   = (int)$this->db->fetchOne('SELECT FOUND_ROWS()');
        $records = [];

        foreach ($users as $user) {

            $gravatar = md5($user['email'] ?? '');

            $records[] = [
                'id'            => $user['id'],
                'name'          => $user['name'],
                'login'         => $user['login'],
                'email'         => $user['email'],
                'role_title'    => $user['role_title'],
                'avatar'        => "<img src=\"https://www.gravatar.com/avatar/{$gravatar}?&s=20&d=mm\" class=\"rounded-circle\"/>",
                'is_admin_sw'   => $user['is_admin_sw'] == 'Y' ? '<span class="badge text-bg-danger">Да</span>' : 'Нет',
                'is_active_sw'  => $user['is_active_sw'],
                'date_created'  => $user['date_created'],
                'date_activity' => $user['date_last_activity'] ?? null,
                'login_user'    => "<button class=\"btn btn-sm btn-secondary\" onclick=\"adminUsers.loginUser('{$user['id']}')\">Войти</button>",
            ];
        }

        return $this->getResponseSuccess([
            'total'   => $total,
            'records' => $records
        ]);
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
}