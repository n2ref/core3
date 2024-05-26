<?php
namespace Core3\Mod\Admin\Classes\Users;
use Core3\Classes\Common;
use CoreUI\Form;
use CoreUI\Table;
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

        $delete_url = "/core3/mod/admin/users/handler/delete";
        $switch_url = "/core3/mod/admin/users/handler/switch_active?id=[id]";
        $load_url   = "/core3/mod/admin/users/handler/table";


        $roles = $this->modAdmin->tableRoles->fetchPairs('id', 'title');


        $table = new Table('core_users');
        $table->setClass('table-hover table-striped');
        $table->setRecordsRequest($load_url);
        $table->setMaxHeight(800);
        $table->setShowScrollShadow(true);
        $table->setClickUrl("{$base_url}/[id]");

        $table->addHeaderOut()
            ->left([
                (new Table\Control\Link('<i class=\"bi bi-plus\"></i> ' . $this->_('Добавить'), "{$base_url}/0"))->setAttr('class', 'btn btn-success'),
                (new Table\Control\Button('<i class=\"bi bi-trash\"></i> ' . $this->_('Удалить')))
                    ->setOnClick("Core.ui.table.get('core_users').deleteSelected('{$delete_url}', Core.menu.reload)")
                    ->setAttr('class', 'btn btn-warning')
            ]);

        $table->addHeaderOut()
            ->left([
                (new Table\Filter\Text('login'))->setAttributes(['placeholder' => $this->_('Логин / Имя / Email')])->setWidth(200),
                (new Table\Filter\Select('role', $this->_('Роль')))->setWidth(200)->setOptions($roles),
                (new Table\Control\FilterClear()),
            ])
            ->right([
                (new Table\Control\Search()),
                (new Table\Control\Columns())->setButton('<i class="bi bi-layout-three-columns"></i>', ['class' => 'btn btn-secondary']),
            ]);

        $table->addFooterOut()
            ->left([
                (new Table\Control\Pages()),
                (new Table\Control\Total)
            ])
            ->right([
                (new Table\Control\PageSize([ 25, 50, 100, 1000 ]))
            ]);

        $table->addSearch([
           (new Table\Search\DatetimeRange('date_created', $this->_('Дата регистрации'))),
           (new Table\Search\Radio('is_admin_sw',          $this->_('Админ')))->setOptions(['Y' => $this->_('Да'), 'N' => $this->_('Нет')])
        ]);


        $table->addColumns([
            (new Table\Column\Select()),
            (new Table\Column\Toggle('is_active_sw',    $this->_('Активность'),             45))->setOnChange("Core.ui.table.get('core_users').switch('{$switch_url}', checked, id)")->setShowLabel(false),
            (new Table\Column\Html('avatar',            $this->_('Аватар'),                 40))->setSort(true)->setShowLabel(false),
            (new Table\Column\Link('login',             $this->_('Логин')))->setMinWidth(100)->setSort(true),
            (new Table\Column\Text('name',              $this->_('Имя')))->setNoWrap(true)->setMinWidth(150)->setSort(true),
            (new Table\Column\Text('email',             $this->_('Email'),                  200))->setSort(true),
            (new Table\Column\Text('role_title',        $this->_('Роль'),                   200))->setSort(true),
            (new Table\Column\Datetime('date_activity', $this->_('Последняя активность'),   185))->setMinWidth(185)->setSort(true),
            (new Table\Column\Datetime('date_created',  $this->_('Дата регистрации'),       155))->setMinWidth(155)->setSort(true),
            (new Table\Column\Html('is_admin_sw',       $this->_('Админ'),                  80))->setMinWidth(80)->setSort(true),
            (new Table\Column\Button('login_user',      $this->_('Вход под пользователем'), 1))->setAttr('onclick', 'event.stopPropagation()')->setShowLabel(false),
        ]);

        return $table->toArray();
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

        $form = new Form('user');
        $form->setValidate(true);
        $form->setWidthLabel(225);
        $form->setSend("/core3/mod/admin/users/handler/save?id={$user->id}&v={$control->version}", 'put');
        $form->setSuccessLoadUrl('#/admin/users');
        $form->setValidResponseHeaders([ 'Content-Type' => [ 'application/json', 'application/json; charset=utf-8' ] ]);
        $form->setValidResponseType([ 'json' ]);

        $avatar = null;

        if ($user->avatar_type == 'upload') {
            $avatar_row = $this->modAdmin->tableUsersFiles->getRowsByUser($user->id, 'avatar', 1);

            if ($avatar_row) {
                $avatar = [[
                   'id'          => $avatar_row->id,
                   'name'        => $avatar_row->file_name,
                   'size'        => $avatar_row->file_size,
                   'urlPreview'  => "/core3/mod/admin/users/handler/get_avatar_preview?id={$avatar_row->id}",
                   'urlDownload' => "/core3/mod/admin/users/handler/get_avatar_download?id={$avatar_row->id}",
               ]];
            }
        }

        $form->setRecord([
            'login'        => $user->login,
            'email'        => $user->email,
            'pass'         => '***',
            'role_id'      => $user->role_id,
            'fname'        => $user->fname,
            'lname'        => $user->lname,
            'mname'        => $user->mname,
            'avatar_type'  => $user->avatar_type,
            'avatar'       => $avatar,
            'is_admin_sw'  => $user->is_admin_sw,
            'is_active_sw' => $user->is_active_sw,
        ]);

        $avatar_types = [
            ['value' => 'generate', 'text' => 'Генерация аватара', 'onchange' => "CoreUI.form.get('user').getField('avatar').hide();" ],
            ['value' => 'upload',   'text' => 'Загрузить',         'onchange' => "CoreUI.form.get('user').getField('avatar').show();" ],
            ['value' => 'none',     'text' => 'Без аватара',       'onchange' => "CoreUI.form.get('user').getField('avatar').hide();" ],
        ];

        $form->addFields([
            (new Form\Field\Text('login',             $this->_('Логин')))->setWidth(200)->setReadonly(true),
            (new Form\Field\Email('email',            $this->_('Email')))->setWidth(200),
            (new Form\Field\PasswordRepeat('pass',    $this->_('Пароль')))->setWidth(200),
            (new Form\Field\Select('role_id',         $this->_('Роль')))->setWidth(200)->setRequired(true)->setOptions($roles),
            (new Form\Field\Text('lname',             $this->_('Фамилия')))->setWidth(200),
            (new Form\Field\Text('fname',             $this->_('Имя')))->setWidth(200),
            (new Form\Field\Text('mname',             $this->_('Отчество')))->setWidth(200),
            (new Form\Field\Radio('avatar_type',      $this->_('Аватар')))->setOptions($avatar_types),
            (new Form\Field\FileUpload('avatar'))->setAccept('image/*')->setFilesLimit(1)->setSizeLimitServer()->setUrl('/core3/mod/admin/users/handler/upload_avatar')->setShow($user->avatar_type == 'upload'),
            (new Form\Field\Toggle('is_admin_sw',     $this->_('Администратор безопасности')))->setDescription($this->_('полный доступ')),
            (new Form\Field\Toggle('is_active_sw',    $this->_('Активен'))),
        ]);

        $form->addControls([
            (new Form\Control\Submit($this->_('Сохранить'))),
            (new Form\Control\Link('Отмена'))->setUrl($base_url)->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
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
            $roles[] = [ 'value' => $role->id, 'text' => $role->title ];
        }



        $form = new Form('user');
        $form->setValidate(true);
        $form->setWidthLabel(225);
        $form->setSend("/core3/mod/admin/users/handler/save", 'post');
        $form->setSuccessLoadUrl('#/admin/users');
        $form->setValidResponseHeaders([ 'Content-Type' => [ 'application/json', 'application/json; charset=utf-8' ] ]);
        $form->setValidResponseType([ 'json' ]);
        $form->setOnSubmitSuccess("CoreUI.notice.info('Сохранено')");

        $form->setRecord([
            'login'        => '',
            'email'        => '',
            'pass'         => '',
            'role_id'      => '',
            'fname'        => '',
            'lname'        => '',
            'mname'        => '',
            'avatar_type'  => '',
            'avatar'       => '',
            'is_admin_sw'  => 'N',
            'is_active_sw' => 'Y',
        ]);

        $avatar_types = [
            ['value' => 'generate', 'text' => 'Генерация аватара', 'onchange' => "CoreUI.form.get('user').getField('avatar').hide();" ],
            ['value' => 'upload',   'text' => 'Загрузить',         'onchange' => "CoreUI.form.get('user').getField('avatar').show();" ],
            ['value' => '',         'text' => 'Без аватара',       'onchange' => "CoreUI.form.get('user').getField('avatar').hide();" ],
        ];

        $form->addFields([
            (new Form\Field\Text('login',             $this->_('Логин')))->setWidth(200)->setRequired(true),
            (new Form\Field\Email('email',            $this->_('Email')))->setWidth(200),
            (new Form\Field\PasswordRepeat('pass',    $this->_('Пароль')))->setWidth(200)->setRequired(true),
            (new Form\Field\Select('role_id',         $this->_('Роль')))->setWidth(200)->setRequired(true)->setOptions($roles),
            (new Form\Field\Text('lname',             $this->_('Фамилия')))->setWidth(200),
            (new Form\Field\Text('fname',             $this->_('Имя')))->setWidth(200),
            (new Form\Field\Text('mname',             $this->_('Отчество')))->setWidth(200),
            (new Form\Field\Radio('avatar_type',      $this->_('Аватар')))->setOptions($avatar_types),
            (new Form\Field\FileUpload('avatar'))->setAccept('image/*')->setFilesLimit(1)->setSizeLimitServer()->setUrl('/core3/mod/admin/users/handler/upload_avatar')->setShow(false),
            (new Form\Field\Toggle('is_admin_sw',     $this->_('Администратор безопасности')))->setDescription($this->_('полный доступ')),
            (new Form\Field\Toggle('is_active_sw',    $this->_('Активен'))),
        ]);

        $form->addControls([
            (new Form\Control\Submit($this->_('Сохранить'))),
            (new Form\Control\Link('Отмена'))->setUrl($base_url)->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
    }
}