<?php
namespace Core3\Mod\Admin\Classes\Users;
use Core3\Exceptions\Exception;
use Core3\Classes\Common;
use Core3\Classes\Form;
use Core3\Classes\Table;
use CoreUI\Table\Filter;
use CoreUI\Table\Search;
use CoreUI\Table\Column;
use CoreUI\Table\Control as TableControl;
use CoreUI\Form\Field;
use CoreUI\Form\Control;
use Laminas\Db\RowGateway\AbstractRowGateway;


/**
 *
 */
class View extends Common {

    /**
     * таблица с юзерами
     * @param string $base_url
     * @return array
     * @throws Exception
     */
    public function getTable(string $base_url): array {

        $roles = $this->modAdmin->tableRoles->fetchPairs('id', 'title');

        $table = new Table('admin', 'users', 'users');
        $table->setKit(['default', 'search', 'columns', 'add' => "{$base_url}/0", 'delete']);
        $table->setHandler('table');
        $table->setClickUrl("{$base_url}/[id]");


        $table->setHeaderOut($table::LAST)
            ->left([
                (new TableControl\Divider()),
                (new Filter\Text('login'))->setAttributes(['placeholder' => $this->_('Логин / Имя / Email')])->setWidth(200),
                (new Filter\Select('role', $this->_('Роль')))->setWidth(200)->setOptions($roles),
                (new TableControl\FilterClear()),
            ]);

        $table->addSearch([
           (new Search\DatetimeRange('date_created', $this->_('Дата регистрации'))),
           (new Search\Radio('is_admin',             $this->_('Админ')))->setOptions(['1' => $this->_('Да'), '0' => $this->_('Нет')])
        ]);

        $table->addColumns([
            (new Column\Select()),
            $table->getColumnToggle('is_active',    $this->_('Активность'),             45, 'switchActive'),
            (new Column\Image('avatar',             $this->_('Аватар'),                 40))->setShowLabel(false)->setStyle('circle')->setBorder(true)->setImgSize(20, 20),
            (new Column\Link('login',               $this->_('Логин')))->setMinWidth(100),
            (new Column\Text('name',                $this->_('Имя')))->setNoWrap(true)->setMinWidth(150),
            (new Column\Text('email',               $this->_('Email'),                  200)),
            (new Column\Text('role_title',          $this->_('Роль'),                   200)),
            (new Column\Datetime('date_activity',   $this->_('Последняя активность'),   185))->setMinWidth(185),
            (new Column\Datetime('date_created',    $this->_('Дата регистрации'),       155))->setMinWidth(155),
            (new Column\Badge('is_admin',           $this->_('Админ'),                  80))->setMinWidth(80),
            (new Column\Button('login_user',        $this->_('Вход под пользователем'), 1))->setAttr('onclick', 'event.stopPropagation()')->setShowLabel(false),
        ]);

        return $table->toArray();
    }


    /**
     * Редактирование пользователя
     * @param string             $base_url
     * @param AbstractRowGateway $user
     * @return array
     * @throws Exception
     */
    public function getForm(string $base_url, AbstractRowGateway $user): array {

        $form = new Form('admin', 'users', 'user');
        $form->setTable($this->modAdmin->tableUsers, $user->id);
        $form->setHandler('save', 'put');
        $form->setSuccessLoadUrl('#/admin/users');
        $form->setOnSubmitSuccessDefault();

        $avatar = $user->avatar_type == 'upload'
            ? $form->getFiles($this->modAdmin->tableUsersFiles, 'avatar', $user->id)
            : [];

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
            'is_admin'     => $user->is_admin,
            'is_active'    => $user->is_active,
        ]);

        $avatar_types = [
            ['value' => 'generate', 'text' => 'Генерация аватара', 'onchange' => "CoreUI.form.get('user').getField('avatar').hide();" ],
            ['value' => 'upload',   'text' => 'Загрузить',         'onchange' => "CoreUI.form.get('user').getField('avatar').show();" ],
            ['value' => 'none',     'text' => 'Без аватара',       'onchange' => "CoreUI.form.get('user').getField('avatar').hide();" ],
        ];

        $roles_rows = $this->modAdmin->tableRoles->fetchAll();
        $roles      = [
            [ 'value' => '', 'text' => '--', 'disabled' => 'disabled' ]
        ];

        foreach ($roles_rows as $role) {
            $roles[] = [ 'value' => $role->id, 'text' => $role->title ];
        }

        $form->addFields([
            (new Field\Text('login',             $this->_('Логин')))->setWidth(200)->setReadonly(true)->setNoSend(true),
            (new Field\Email('email',            $this->_('Email')))->setWidth(200),
            (new Field\PasswordRepeat('pass',    $this->_('Пароль')))->setWidth(200),
            (new Field\Select('role_id',         $this->_('Роль')))->setWidth(200)->setRequired(true)->setOptions($roles),
            (new Field\Text('lname',             $this->_('Фамилия')))->setWidth(200),
            (new Field\Text('fname',             $this->_('Имя')))->setWidth(200),
            (new Field\Text('mname',             $this->_('Отчество')))->setWidth(200),
            (new Field\Radio('avatar_type',      $this->_('Аватар')))->setOptions($avatar_types),
            (new Field\FileUpload('avatar'))->setAccept('image/*')->setFilesLimit(1)->setSizeLimitServer()->setShow($user->avatar_type == 'upload'),
            (new Field\Toggle('is_admin',        $this->_('Администратор безопасности')))->setDescription($this->_('полный доступ')),
            (new Field\Toggle('is_active',       $this->_('Активен'))),
        ]);

        $form->addControls([
            $form->getBtnSubmit(),
            (new Control\Link('Отмена'))->setUrl($base_url)->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
    }


    /**
     * Добавление пользователя
     * @param string $base_url
     * @return array
     */
    public function getFormNew(string $base_url): array {

        $form = new Form('admin', 'users', 'user');
        $form->setHandler('saveNew');
        $form->setSuccessLoadUrl('#/admin/users');
        $form->setOnSubmitSuccessDefault();

        $form->setRecord([
            'login'        => '',
            'email'        => '',
            'pass'         => '',
            'role_id'      => '',
            'fname'        => '',
            'lname'        => '',
            'mname'        => '',
            'avatar_type'  => 'none',
            'avatar'       => '',
            'is_admin'     => '0',
            'is_active'    => '1',
        ]);

        $avatar_types = [
            ['value' => 'generate', 'text' => 'Генерация аватара', 'onchange' => "CoreUI.form.get('user').getField('avatar').hide();" ],
            ['value' => 'upload',   'text' => 'Загрузить',         'onchange' => "CoreUI.form.get('user').getField('avatar').show();" ],
            ['value' => 'none',     'text' => 'Без аватара',       'onchange' => "CoreUI.form.get('user').getField('avatar').hide();" ],
        ];

        $roles_rows = $this->modAdmin->tableRoles->fetchAll();
        $roles      = [
            [ 'value' => '', 'text' => '--', 'disabled' => 'disabled' ]
        ];

        foreach ($roles_rows as $role) {
            $roles[] = [ 'value' => $role->id, 'text' => $role->title ];
        }

        $form->addFields([
            (new Field\Text('login',             $this->_('Логин')))->setWidth(200)->setRequired(true),
            (new Field\Email('email',            $this->_('Email')))->setWidth(200),
            (new Field\PasswordRepeat('pass',    $this->_('Пароль')))->setWidth(200)->setRequired(true),
            (new Field\Select('role_id',         $this->_('Роль')))->setWidth(200)->setRequired(true)->setOptions($roles),
            (new Field\Text('lname',             $this->_('Фамилия')))->setWidth(200),
            (new Field\Text('fname',             $this->_('Имя')))->setWidth(200),
            (new Field\Text('mname',             $this->_('Отчество')))->setWidth(200),
            (new Field\Radio('avatar_type',      $this->_('Аватар')))->setOptions($avatar_types),
            (new Field\FileUpload('avatar'))->setAccept('image/*')->setFilesLimit(1)->setSizeLimitServer()->setShow(false),
            (new Field\Toggle('is_admin',        $this->_('Администратор безопасности')))->setDescription($this->_('полный доступ')),
            (new Field\Toggle('is_active',       $this->_('Активен'))),
        ]);

        $form->addControls([
            $form->getBtnSubmit(),
            (new Control\Link('Отмена'))->setUrl($base_url)->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
    }
}