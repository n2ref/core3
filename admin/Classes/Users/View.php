<?php
namespace Core3\Mod\Admin\Classes\Users;
use Core3\Mod\Admin\Classes\Users\User;
use Core3\Classes\Common;


/**
 *
 */
class View extends Common {

    /**
     * таблица с юзерами
     * @param string $base_url
     * @return array
     */
    public function getTable(string $base_url): array {

        $users = $this->db->fetchAll("
            SELECT u.id,
                   u.login,
                   u.email,
                   CONCAT_WS(' ', u.lname, u.fname, u.mname) AS name,
                   u.is_active_sw,
                   u.is_admin_sw,
                   u.date_created,
                   r.name AS role_title,
                   
                   (SELECT us.date_last_activity
                    FROM core_users_sessions AS us 
                    WHERE u.id = us.user_id
                    ORDER BY date_last_activity DESC
                    LIMIT 1) AS date_last_activity
            FROM core_users AS u
                LEFT JOIN core_roles AS r ON u.role_id = r.id
        ");

        $records = [];

        foreach ($users as $user) {

            $records[] = [
                'id'            => $user['id'],
                'name'          => $user['name'],
                'login'         => $user['login'],
                'email'         => $user['email'],
                'role_title'    => $user['role_title'],
                'is_admin_sw'   => $user['is_admin_sw'] == 'Y' ? '<span class="text-danger">Да</span>' : 'Нет',
                'is_active_sw'  => $user['is_active_sw'],
                'date_created'  => $user['date_created'],
                'date_activity' => $user_last_session['date_last_activity'] ?? null,
                'actions'       => "<button class=\"btn btn-sm btn-outline-secondary\">Войти</button>",
            ];
        }

        $table = [
            'component' => 'coreui.table',
            'primaryKey' => 'id',
            'show'       => [
                'total' => true,
            ],
            'controls' => [
                [ 'type' => "link",   'content' => "<i class=\"bi bi-plus\"></i> Добавить", 'href' => "{$base_url}?edit=0", 'attr' => [ 'class' => 'btn btn-sm btn-success' ] ],
                [ 'type' => "button", 'content' => "<i class=\"bi bi-trash\"></i> Удалить", 'attr' => [ 'class' => 'btn btn-sm btn-warning' ] ],
            ],
            'onClickUrl' => "{$base_url}?edit=[id]",
            'columns' => [
                [ 'type' => 'numbers', 'width' => 25, 'attr' => [ 'class' => "border-end text-end" ] ],
                [ 'type' => 'select' ],
                [ 'field' => 'is_active_sw',  'label' => '',                       'width' => 60,  'type' => 'switch' ],
                [ 'field' => 'login',         'label' => 'Логин',                     'width' => 130 ],
                [ 'field' => 'name',          'label' => 'ФИО' ],
                [ 'field' => 'email',         'label' => 'Email',                     'width' => 180, 'type' => 'text' ],
                [ 'field' => 'role_title',    'label' => 'Роль',                      'width' => 180, 'type' => 'text' ],
                [ 'field' => 'date_activity', 'label' => 'Дата последней активности', 'width' => 220,  'type' => 'datetime' ],
                [ 'field' => 'date_created',  'label' => 'Дата регистрации',          'width' => 200,  'type' => 'datetime' ],
                [ 'field' => 'is_admin_sw',   'label' => 'Админ',                     'width' => 70,  'type' => 'html' ],
                [ 'field' => 'actions',       'label' => '',                          'width' => 1,  'type' => 'html' ],
            ],
            'records' => $records
        ];

        return $table;
    }


    /**
     * @param string    $app
     * @param User|null $user
     * @return false|string
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Exception
     */
    public function getEdit(string $app, User $user = null) {

        $edit = new \editTable('user');

        $fields = [
            'u_id',
            'u_login',
            'email',
            'role_id',
            'lastname',
            'firstname',
            'middlename',
            'u_pass',
            'certificate',
            'is_email_wrong',
            'is_pass_changed',
            'is_admin_sw',
            'NULL AS send_info_sw'
        ];


        if ( ! $user) {
            $about_email = $this->_("Отправить информацию о пользователе на email");
        } else {
            unset($fields[1]);
            $about_email = $this->_("Отправить информацию об изменении на email");
        }

        $is_auth_certificate_on = $this->core_config->auth && $this->core_config->auth->x509 && $this->core_config->auth->x509->on;


        if ($this->core_config->auth && $this->core_config->auth->pass) {
            $is_auth_pass_on = $this->core_config->auth->pass->on;
        } else {
            $is_auth_pass_on = true;
        }



        if ($this->auth->LDAP) {
            unset($fields[7]);
            unset($fields[8]);
            unset($fields[10]);

        } else {
            if ( ! $is_auth_pass_on) {
                unset($fields[7]);
                unset($fields[10]);
            }
            if ( ! $is_auth_certificate_on) {
                unset($fields[8]);
            }
        }

        $implode_fields = implode(",\n", $fields);

        $edit->SQL = $this->db->quoteInto("
            SELECT {$implode_fields}
            FROM core_users
               LEFT JOIN core_users_profile AS p ON p.user_id = u_id
            WHERE u_id = ?
        ", $user ? $user->u_id : 0);

        $role_list = $this->db->fetchPairs("
            SELECT id, 
                   name 
            FROM core_roles 
            WHERE is_active_sw = 'Y'
            ORDER BY position ASC
        ");



        $certificate = $user
            ? htmlspecialchars($user->certificate)
            : '';

        $description_admin = "<br><small class=\"text-muted\">полный доступ</small>";

        if ( ! $user) {
            $edit->addControl("Логин", "TEXT", "maxlength=\"60\" style=\"width:385px\"", "", "", true);
        }

        $edit->addControl("Email",              "TEXT", "maxlength=\"60\" style=\"width:385px\"", "", "");
        $edit->addControl($this->_("Роль"),     "LIST", "style=\"width:385px\"", "", "", true); $edit->selectSQL[] = ['' => '--'] + $role_list;
        $edit->addControl($this->_("Фамилия"),  "TEXT", "maxlength=\"20\" style=\"width:385px\"", "", "");
        $edit->addControl($this->_("Имя"),      "TEXT", "maxlength=\"20\" style=\"width:385px\"", "", "", true);
        $edit->addControl($this->_("Отчество"), "TEXT", "maxlength=\"20\" style=\"width:385px\"", "", "");

        if ( ! $this->auth->LDAP) {
            if ( ! $this->auth->LDAP && $is_auth_pass_on) {
                $edit->addControl($this->_("Пароль"), "PASSWORD", "", "", "", true);
            }

            if ($is_auth_certificate_on) {
                $cert_desc = '<br><small class="text-muted">x509</small>';
                $edit->addControl($this->_("Сертификат") . $cert_desc, "XFILE_AUTO", "", $this->editCert($certificate), "");
            }
        }

        $edit->addControl($this->_("Неверный email"), "RADIO", "", "", "N", true); $edit->selectSQL[] = ['Y' => 'да', 'N' => 'нет'];

        if ( ! $this->auth->LDAP && $is_auth_pass_on) {
            $edit->addControl($this->_("Предупреждение о смене пароля"), "RADIO", "", "", "N", true); $edit->selectSQL[] = ['N' => 'да', 'Y' => 'нет'];
        }

        $edit->addControl($this->_("Администратор безопасности{$description_admin}"), "RADIO", "", "", "N", true); $edit->selectSQL[] = ['Y' => 'да', 'N' => 'нет'];
        $edit->addControl($about_email,                                               "CHECKBOX", "", "", "0"); $edit->selectSQL[] = ['Y' => ''];

        $is_active_sw = $user
            ? $this->dataUsers->exists("visible = 'Y' AND u_id = ?", $user->u_id)
            : '';

        $edit->addButtonSwitch('visible', $is_active_sw);

        $edit->back = $app;
        $edit->firstColWidth = '200px';
        $edit->addButton($this->_("Вернуться к списку пользователей"), "load('$app')");
        $edit->save("xajax_saveUser(xajax.getFormValues(this.id))");

        return $edit->render();
    }


    /**
     * @param $cert
     * @return string
     */
    private function editCert($cert) {

        $html = "
            <br/>
            <textarea style=\"min-width:385px;max-width:385px;min-height: 150px\" name=\"control[certificate_ta]\" placeholder=\"Формат base64\">{$cert}</textarea>
            <br>
            <label class=\"text-muted\">
                <input type=\"checkbox\" name=\"certificate_parse\" value=\"Y\"> Использовать ФИО из сертификата
            </label>
        ";

        return $html;
    }
}