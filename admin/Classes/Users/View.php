<?php
namespace Core3\Mod\Admin\Classes\Users;
use Core3\Exceptions\Exception;
use Core3\Classes\Common;
use Core3\Classes\Form;
use Core3\Classes\Table;
use Core3\Classes\Db\Row;
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

    private string $base_url = "admin/users";


    /**
     * таблица с юзерами
     * @return array
     */
    public function getTable(): array {

        $table = new Table('admin', 'users');
        $table->setClass('table-hover table-striped');
        $table->addControlsDefault();
        $table->addControlBtnAdd("#/{$this->base_url}/0");
        $table->addControlBtnDelete("{$this->base_url}/records");
        $table->setRecordsRequest("{$this->base_url}/records");
        $table->setClickUrl("#/{$this->base_url}/[id]");


        $roles = $this->modAdmin->tableRoles->fetchPairs('id', 'title');

        $table->setHeaderOut($table::LAST)
            ->left([
                $table->getControlSearch(),
                $table->getControlColumns(),
                (new TableControl\Divider())->setWidth(30),
                (new Filter\Text('login'))->setAttributes(['placeholder' => $this->_('Логин / Имя / Email')])->setWidth(200),
                (new Filter\Select('role', $this->_('Роль')))->setWidth(200)->setOptions($roles),
                (new TableControl\FilterClear()),
            ]);

        $table->addSearch([
            (new Search\Text('login',                  $this->_('Логин'))),
            (new Search\Text('name',                   $this->_('Имя'))),
            (new Search\Text('email',                  $this->_('Email'))),
            (new Search\DatetimeRange('date_activity', $this->_('Последняя активность'))),
            (new Search\DatetimeRange('date_created',  $this->_('Дата регистрации'))),
            (new Search\Number('active_sessions',      $this->_('Активных сессий'))),
            (new Search\CheckboxBtn('is_admin',        $this->_('Админ')))->setOptions(['1' => $this->_('Да'), '0' => $this->_('Нет')]),
            (new Search\CheckboxBtn('is_active',       $this->_('Активность')))->setOptions(['1' => $this->_('Да'), '0' => $this->_('Нет')]),
        ]);

        $table->addColumns([
            $table->getColumnToggle('is_active',   $this->_('Активность'),             45),
            (new Column\Image('avatar',            $this->_('Аватар'),                 40))->setShowLabel(false)->setStyle('circle')->setBorder(true)->setImgSize(20, 20),
            (new Column\Link('login',              $this->_('Логин')))->setMinWidth(100),
            (new Column\Text('name',               $this->_('Имя')))->setMinWidth(150)->setNoWrap(true),
            (new Column\Text('email',              $this->_('Email'),                  200))->setMinWidth(180)->setNoWrap(true),
            (new Column\Text('role_title',         $this->_('Роль'),                   200))->setMinWidth(150)->setNoWrap(true),
            (new Column\DateHuman('date_activity', $this->_('Последняя активность'),   185))->setMinWidth(185),
            (new Column\Datetime('date_created',   $this->_('Дата регистрации'),       155))->setMinWidth(155)->setShow(false),
            (new Column\Number('active_sessions',  $this->_('Активных сессий'),        145))->setMinWidth(145),
            (new Column\Badge('is_admin',          $this->_('Админ'),                  80))->setMinWidth(80),
            (new Column\Button('login_user',       $this->_('Вход под пользователем'), 1))->setAttr('onclick', 'event.stopPropagation()')->setShowLabel(false),
            (new Column\Select()),
        ]);

        return $table->toArray();
    }


    /**
     * Редактирование пользователя
     * @param AbstractRowGateway $user
     * @return array
     * @throws Exception
     */
    public function getForm(AbstractRowGateway $user): array {

        $form = new Form('admin', 'users');
        $form->setTable($this->modAdmin->tableUsers, $user->id);
        $form->setHandler("{$this->base_url}/{$user->id}", 'put');
        $form->setSuccessLoadUrl("#/{$this->base_url}");
        $form->setSuccessNotice();
        $form->setWidthLabel(160);

        $avatar = [];

        if ($user->avatar_type == 'upload') {
            $avatar = $form->getFiles($this->modAdmin->tableUsersFiles, $user->id, 'avatar', function (array $file) use ($user) {
                $file['urlPreview']  = "/sys/user/{$user->id}/avatar";
                $file['urlDownload'] = "/{$this->base_url}/{$user->id}/avatar/download";

                return $file;
            });
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
            'is_admin'     => $user->is_admin,
            'is_active'    => $user->is_active,
        ]);

        $avatar_types = [
            ['value' => 'generate', 'text' => 'Генерация аватара', 'onchange' => "CoreUI.form.get('admin_users').getField('avatar').hide();" ],
            ['value' => 'upload',   'text' => 'Загрузить',         'onchange' => "CoreUI.form.get('admin_users').getField('avatar').show();" ],
            ['value' => 'none',     'text' => 'Без аватара',       'onchange' => "CoreUI.form.get('admin_users').getField('avatar').hide();" ],
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
            (new Field\RadioBtn('avatar_type',   $this->_('Аватар')))->setOptions($avatar_types),
            (new Field\FileUpload('avatar'))->setAccept('image/*')->setFilesLimit(1)->setSizeLimitServer()->setShow($user->avatar_type == 'upload')->setUrl("/{$this->base_url}/avatar/upload"),
            (new Field\Toggle('is_admin',        $this->_('Администратор')))->setDescription($this->_('Пользователь получит полный доступ к системе. Активируйте с умом.')),
            (new Field\Toggle('is_active',       $this->_('Активен')))->setDescription($this->_('Дает возможность входить и пользоваться системой')),
        ]);

        $form->addControls([
            $form->getBtnSubmit(),
            (new Control\Link('Отмена'))->setUrl("#/{$this->base_url}")->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
    }


    /**
     * Добавление пользователя
     * @return array
     */
    public function getFormNew(): array {

        $form = new Form('admin', 'users');
        $form->setHandler("{$this->base_url}/0");
        $form->setSuccessLoadUrl("#/{$this->base_url}");
        $form->setSuccessNotice();
        $form->setWidthLabel(160);

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
            ['value' => 'generate', 'text' => 'Генерация аватара', 'onchange' => "CoreUI.form.get('admin_users').getField('avatar').hide();" ],
            ['value' => 'upload',   'text' => 'Загрузить',         'onchange' => "CoreUI.form.get('admin_users').getField('avatar').show();" ],
            ['value' => 'none',     'text' => 'Без аватара',       'onchange' => "CoreUI.form.get('admin_users').getField('avatar').hide();" ],
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
            (new Field\RadioBtn('avatar_type',   $this->_('Аватар')))->setOptions($avatar_types),
            (new Field\FileUpload('avatar'))->setAccept('image/*')->setFilesLimit(1)->setSizeLimitServer()->setShow(false)->setUrl("/{$this->base_url}/avatar/upload"),
            (new Field\Toggle('is_admin',        $this->_('Администратор')))->setDescription($this->_('Пользователь получит полный доступ к системе. Активируйте с умом.')),
            (new Field\Toggle('is_active',       $this->_('Активен')))->setDescription($this->_('Дает возможность входить и пользоваться системой')),
        ]);

        $form->addControls([
            $form->getBtnSubmit(),
            (new Control\Link('Отмена'))->setUrl("#/{$this->base_url}")->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
    }


    /**
     * @param Row $user
     * @return array
     */
    public function getTableSessions(Row $user): array {

        $table = new Table('admin', 'users', 'sessions');
        $table->setClass('table-hover table-sm');
        $table->setSaveState(true);
        $table->setSearchLabelWidth(180);
        $table->setTheadTop(-19);
        $table->setRecordsRequest("{$this->base_url}/{$user->id}/sessions/table/records");

        $table->addHeaderOut()
            ->left([
                $table->getControlSearch(),
                $table->getControlColumns(),
                (new TableControl\Divider())->setWidth(30),
                (new Filter\Text('search'))->setAttributes(['placeholder' => $this->_('Устройство / Местоположение / ip')])->setWidth(260),
                (new TableControl\Divider())->setWidth(30),
                (new Filter\Toggle('is_active', $this->_('Только активные'))),
                (new TableControl\FilterClear()),
            ]);


        $table->addFooterOut()
            ->left([
                new TableControl\Total,
                (new TableControl\Pages(10)),
            ])
            ->right([
                new TableControl\PageSize([ 25, 50, 100, 1000 ])
            ]);

        $table->addSearch([
            (new Search\Text('place',                       $this->_('Местоположение'))),
            (new Search\Text('agent',                       $this->_('Устройство'))),
            (new Search\Text('client_ip',                   $this->_('ip'))),
            (new Search\DatetimeRange('date_last_activity', $this->_('Последняя активность'))),
            (new Search\DatetimeRange('date_expired',       $this->_('Окончание сессии'))),
            (new Search\Number('count_requests',            $this->_('Количество запросов'))),
            (new Search\CheckboxBtn('is_active',            $this->_('Активность')))->setOptions(['1' => $this->_('Да'), '0' => $this->_('Нет')]),
        ]);

        $table->addColumns([
            $table->getColumnToggle('is_active',       $this->_('Активность'),           1, "/{$user->id}/sessions/[id]"),
            (new Column\Text('agent',                  $this->_('Устройство')))->setMinWidth(200)->setNoWrap(true)->setNoWrapToggle(true),
            (new Column\Text('place',                  $this->_('Местоположение')))->setMinWidth(200)->setNoWrap(true)->setNoWrapToggle(true),
            (new Column\Text('client_ip',              $this->_('ip'),                   120)),
            (new Column\Number('count_requests',       $this->_('Количество запросов'),  185)),
            (new Column\Datetime('date_last_activity', $this->_('Последняя активность'), 180)),
            (new Column\Datetime('date_created',       $this->_('Дата авторизации'),     150)),
            (new Column\Html('date_expired',           $this->_('Окончание сессии'),     155)),
        ]);

        return $table->toArray();
    }
}