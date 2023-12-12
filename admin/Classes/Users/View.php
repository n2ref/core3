<?php
namespace Core3\Mod\Admin\Classes\Users;
use Core3\Mod\Admin\Classes\Users\User;
use Core3\Classes\Common;
use Laminas\Db\RowGateway\AbstractRowGateway;


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

        $delete_url = "/core/mod/admin/users/handler/delete";
        $switch_url = "/core/mod/admin/users/handler/switch_active?id=[id]";
        $load_url   = "/core/mod/admin/users/handler/table";

        $table = [
            'id'             => "core_users",
            'url'            => $load_url,
            'method'         => 'GET',
            'size'           => 'sm',
            'striped'        => true,
            'hover'          => true,
            'class'          => 'table-core3',
            'recordsPerPage' => 25,
            'component'      => 'coreui.table',
            'primaryKey'     => 'id',
            'maxHeight'      => 800,
            'show' => [
                'total'       => true,
                'pages'       => true,
                'pagesJump'   => true,
                'prePageList' => true,
            ],
            'controls' => [
                [ 'type' => "link",   'content' => "<i class=\"bi bi-plus\"></i> Добавить", 'href' => "{$base_url}?edit=0", 'attr' => [ 'class' => 'btn btn-sm btn-success' ] ],
                [ 'type' => "button", 'content' => "<i class=\"bi bi-trash\"></i> Удалить", 'onClick' => "Core.ui.table.get('core_users').deleteSelected('{$delete_url}', Core.menu.reload)", 'attr' => [ 'class' => 'btn btn-sm btn-warning' ] ],
            ],
            'onClickUrl' => "{$base_url}?edit=[id]",
            'columns' => [
                [ 'type' => 'numbers', 'width' => 25, 'attr' => [ 'class' => "border-end text-end" ] ],
                [ 'type' => 'select' ],
                [ 'field' => 'is_active_sw',  'label' => '',                     'width' => 45,  'type' => 'switch', 'onChange' => "Core.ui.table.get('core_users').switch('{$switch_url}', checked, id)"],
                [ 'field' => 'avatar',        'label' => '',                     'width' => 40,  'type' => 'html' ],
                [ 'field' => 'login',         'label' => 'Логин',                'width' => 130 ],
                [ 'field' => 'name',          'label' => 'ФИО' ],
                [ 'field' => 'email',         'label' => 'Email',                'width' => 180, 'type' => 'text' ],
                [ 'field' => 'role_title',    'label' => 'Роль',                 'width' => 180, 'type' => 'text' ],
                [ 'field' => 'date_activity', 'label' => 'Последняя активность', 'width' => 220, 'type' => 'datetime' ],
                [ 'field' => 'date_created',  'label' => 'Дата регистрации',     'width' => 200, 'type' => 'datetime' ],
                [ 'field' => 'is_admin_sw',   'label' => 'Админ',                'width' => 70,  'type' => 'html' ],
                [ 'field' => 'login_user',    'label' => '',                     'width' => 1,   'type' => 'html', 'attr' => [ 'onClick' => 'event.stopPropagation();'] ],
            ]
        ];

        return $table;
    }


    /**
     * @param string             $base_url
     * @param AbstractRowGateway $user
     * @return array
     */
    public function getForm(string $base_url, AbstractRowGateway $user): array {

        $roles_rows = $this->modAdmin->tableRoles->fetchAll();
        $roles      = [
            [ 'value' => '', 'text' => '--' ]
        ];

        foreach ($roles_rows as $role) {
            $roles[] = [
                'value' => $role->id,
                'text'  => $role->title,
            ];
        }

        $control = $this->modAdmin->tableControls->createRow($this->modAdmin->tableUsers->getTable(), $user->id);

        $form = [
            'component'  => 'coreui.form',
            'validate'   => true,
            'lang'       => 'ru',
            'labelWidth' => 225,
            'send'       => [
                'url'    => "/core/mod/admin/users/handler/save?id={$user->id}&v={$control->version}",
                'method' => 'put',
                'format' => 'form',
            ],
            'successLoadUrl' => '#/admin/users',
            'onSubmitSuccess' => "CoreUI.notice.info('Сохранено')",
            'validResponse' => [
                'headers' => [
                    'Content-Type' => [ 'application/json', 'application/json; charset=utf-8' ]
                ],
                'dataType' => [ 'json' ],
            ],
            'record' => [
                'login'                      => $user->login,
                'control[email]'              => $user->email,
                'control[pass]'               => '***',
                'control[role_id]'            => $user->role_id,
                'control[fname]'              => $user->fname,
                'control[lname]'              => $user->lname,
                'control[mname]'              => $user->mname,
                'control[is_pass_changed_sw]' => $user->is_pass_changed_sw,
                'control[is_admin_sw]'        => $user->is_admin_sw,
                'control[is_active_sw]'       => $user->is_active_sw,
            ],
            'fields' => [
                [ 'type' => 'text',           'name' => 'login',                       'label' => 'Логин',                         'width' => 200, 'readonly' => true ],
                [ 'type' => 'email',          'name' => 'control[email]',              'label' => 'Email',                         'width' => 200 ],
                [ 'type' => 'passwordRepeat', 'name' => 'control[pass]',               'label' => 'Пароль',                        'width' => 200, 'required' => true ],
                [ 'type' => 'select',         'name' => 'control[role_id]',            'label' => 'Роль',                          'width' => 200, 'required' => true, 'options' => $roles ],
                [ 'type' => 'text',           'name' => 'control[lname]',              'label' => 'Фамилия',                       'width' => 200 ],
                [ 'type' => 'text',           'name' => 'control[fname]',              'label' => 'Имя',                           'width' => 200 ],
                [ 'type' => 'text',           'name' => 'control[mname]',              'label' => 'Отчество',                      'width' => 200 ],
                [ 'type' => 'switch',         'name' => 'control[is_pass_changed_sw]', 'label' => 'Предупреждение о смене пароля', 'valueY' => 'Y', 'valueN' => 'N', ],
                [ 'type' => 'switch',         'name' => 'control[is_admin_sw]',        'label' => 'Администратор безопасности',    'valueY' => 'Y', 'valueN' => 'N',  'description' => 'полный доступ' ],
                [ 'type' => 'switch',         'name' => 'control[is_active_sw]',       'label' => 'Активен',                       'valueY' => 'Y', 'valueN' => 'N', ],
            ],
            'controls' => [
                [ 'type' => "submit", 'content' => "Сохранить" ],
                [ 'type' => "link",   'content' => "Отмена", 'href' => $base_url, 'attr' => [ 'class' => 'btn btn-sm btn-secondary' ] ],
            ],
        ];

        return $form;
    }


    /**
     * @param string $base_url
     * @return array
     */
    public function getFormNew(string $base_url): array {

        $roles_rows = $this->modAdmin->tableRoles->fetchAll();
        $roles      = [
            [ 'value' => '', 'text' => '--', 'disabled' => 'disabled' ]
        ];

        foreach ($roles_rows as $role) {
            $roles[] = [
                'value' => $role->id,
                'text'  => $role->title
            ];
        }

        $form = [
            'component'  => 'coreui.form',
            'validate'   => true,
            'lang'       => 'ru',
            'labelWidth' => 225,
            'send'       => [
                'url'    => "/core/mod/admin/users/handler/save",
                'method' => 'post',
            ],
            'validResponse' => [
                'headers' => [
                    'Content-Type' => [ 'application/json', 'application/json; charset=utf-8' ]
                ],
                'dataType' => [ 'json' ],
            ],
            'successLoadUrl' => '#/admin/users',
            'onSubmitSuccess' => "CoreUI.notice.info('Сохранено')",
            'record' => [
                'control[login]'              => '',
                'control[email]'              => '',
                'control[pass]'               => "",
                'control[role_id]'            => "",
                'control[fname]'              => "",
                'control[lname]'              => "",
                'control[mname]'              => "",
                'control[is_pass_changed_sw]' => "N",
                'control[is_admin_sw]'        => "N",
                'control[is_active_sw]'       => "Y",
            ],
            'fields' => [
                [ 'type' => 'text',           'name' => 'control[login]',              'label' => 'Логин',                         'width' => 200, 'required' => true ],
                [ 'type' => 'email',          'name' => 'control[email]',              'label' => 'Email',                         'width' => 200 ],
                [ 'type' => 'passwordRepeat', 'name' => 'control[pass]',               'label' => 'Пароль',                        'width' => 200, 'required' => true ],
                [ 'type' => 'select',         'name' => 'control[role_id]',            'label' => 'Роль',                          'width' => 200, 'required' => true, 'options' => $roles ],
                [ 'type' => 'text',           'name' => 'control[lname]',              'label' => 'Фамилия',                       'width' => 200 ],
                [ 'type' => 'text',           'name' => 'control[fname]',              'label' => 'Имя',                           'width' => 200 ],
                [ 'type' => 'text',           'name' => 'control[mname]',              'label' => 'Отчество',                      'width' => 200 ],
                [ 'type' => 'switch',         'name' => 'control[is_pass_changed_sw]', 'label' => 'Предупреждение о смене пароля', ],
                [ 'type' => 'switch',         'name' => 'control[is_admin_sw]',        'label' => 'Администратор безопасности',    'description' => 'полный доступ' ],
                [ 'type' => 'switch',         'name' => 'control[is_active_sw]',       'label' => 'Активен',                       ],
            ],
            'controls' => [
                [ 'type' => "submit", 'content' => "Сохранить" ],
                [ 'type' => "link",   'content' => "Отмена", 'href' => $base_url, 'attr' => [ 'class' => 'btn btn-sm btn-secondary' ] ],
            ],
        ];

        return $form;
    }
}