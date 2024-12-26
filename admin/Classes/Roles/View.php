<?php
namespace Core3\Mod\Admin\Classes\Roles;
use Core3\Classes\Common;
use Core3\Classes\Db\Row;
use Core3\Classes\Form;
use Core3\Classes\Table;
use Core3\Classes\Tools;
use Core3\Exceptions\DbException;
use CoreUI\Table\Filter;
use CoreUI\Table\Column;
use CoreUI\Table\Control as TableControl;
use CoreUI\Form\Field;
use Laminas\Db\Sql\Select;


/**
 *
 */
class View extends Common {

    private string $base_url = "admin/roles";

    /**
     * @return array
     */
    public function getTableRoles(): array {

        $table = new Table('admin', 'roles');
        $table->setShowScrollShadow(true);
        $table->setSaveState(true);
        $table->addControlBtnAdd("#/{$this->base_url}/0", 'out');
        $table->addControlBtnDelete("{$this->base_url}/table", 'out');
        $table->setClickUrl("#/{$this->base_url}/[id]");

        $table->setFooterOut()
            ->left([
                new TableControl\Total,
            ]);

        $table->setHeaderOut($table::LAST)
            ->left([
                (new Filter\Text('title'))->setAttributes(['placeholder' => $this->_('Название')])->setWidth(200),
                (new TableControl\FilterClear()),
            ]);

        $table->addColumns([
            (new Column\Select()),
            (new Column\Link('title',           $this->_('Название')))->setMinWidth(100),
            (new Column\Text('description',     $this->_('Примечание')))->setMinWidth(150),
            (new Column\Number('users_count',   $this->_('Пользователей')))->setWidth(170),
            (new Column\Number('modules_count', $this->_('Доступно разделов')))->setWidth(170),
        ]);


        $users = $this->modAdmin->tableUsers->fetchAll()->toArray();
        $roles = $this->modAdmin->tableRoles->fetchAll()->toArray();
        $roles = Tools::arrayMultisort($roles, [ 'title' => 'asc', ]);

        $table->setRecords($roles);

        foreach ($table->getRecords() as $record) {

            $record->title = [
                'content' => $record->title,
                'url'     => "#/{$this->base_url}/{$record->id}",
                'attr'    => ['class' => 'fw-medium']
            ];

            $privileges = $record->privileges
                ? json_decode($record->privileges, true)
                : [];


            $record->users_count = 0;

            foreach ($users as $user) {
                if ($user['role_id'] == $record->id) {
                    $record->users_count++;
                }
            }


            $record->modules_count = 0;

            foreach ($privileges as $access_rules) {

                if (is_array($access_rules) && in_array('access', $access_rules)) {
                    $record->modules_count++;
                }
            }
        }

        $table->limitFields(['id', 'title', 'description', 'users_count', 'modules_count']);

        return $table->toArray();
    }


    /**
     * @return array
     * @throws DbException
     */
    public function getTableAccess(): array {

        $table = new Table('admin', 'roles', 'access');
        $table->setClass('table-hover table-striped table-sm');
        $table->setShowScrollShadow(true);
        $table->setOverflow(true);
        $table->setSaveState(true);
        $table->addControlColumns();

        $table->setHeaderOut($table::LAST)
            ->left([
                (new Filter\Text('title'))->setAttributes(['placeholder' => $this->_('Правило модуля')])->setWidth(200)->setAutoSearch(true),
                (new TableControl\FilterClear()),
            ]);

        $table->addColumns([
            (new Column\Html('title', $this->_('Правило модуля')))->setMinWidth(200)->setFixedLeft()->setSort(false),
        ]);

        $roles = $this->modAdmin->tableRoles->fetchAll()->toArray();
        $roles = Tools::arrayMultisort($roles, ['title' => 'asc',]);

        $icon_on  = '<i class="bi bi-toggle-on"></i> ';
        $icon_off = '<i class="bi bi-toggle-off"></i> ';

        foreach ($roles as $key => $role) {
            $roles[$key]['privileges'] = $role['privileges'] ? json_decode($role['privileges'], true) : [];

            $table->addColumns([
                (new Column\Toggle("role_{$role['id']}", $role['title']))->setMinWidth(140)->setSort(false)
                    ->setOnChange("adminRoles.switchAccess(record, '{$role['id']}', input)")
                    ->showMenuAlways(true)
                    ->addMenuButton($icon_off . $this->_('Выкл. все'), "adminRoles.setRejectAll('{$role['id']}')")
                    ->addMenuButton($icon_on . $this->_('Вкл. все'), "adminRoles.setAccessAll('{$role['id']}')", ['class' => 'text-danger-emphasis'])
            ]);
        }

        $modules_privileges = $this->modAdmin->modelRoles->getModulesPrivileges();
        $records            = [];
        $step               = str_repeat('&nbsp;', 5);

        foreach ($modules_privileges as $module_name => $module) {
            foreach ($module['privileges'] as $privilege_name => $privilege_title) {
                if ($privilege_name === 'access') {
                    $title = $module['title'];

                    if ($module['icon']) {
                        $title = "<i class=\"{$module['icon']}\"></i> {$title}";
                    }

                } else {
                    $title = "{$step}{$privilege_title}";
                }

                $record = [
                    'module'  => $module_name,
                    'section' => '',
                    'name'    => $privilege_name,
                    'title'   => "{$title}",
                ];

                foreach ($roles as $role) {
                    $record["role_{$role['id']}"] = ! empty($role['privileges'][$module_name]) &&
                                                    in_array($privilege_name, $role['privileges'][$module_name])
                        ? 1
                        : 0;
                }

                $records[] = $record;
            }

            foreach ($module['sections'] as $section_name => $section) {
                foreach ($section['privileges'] as $privilege_name => $privilege_title) {

                    $title = $privilege_name === 'access'
                        ? "{$step}{$section['title']}"
                        : "{$step}{$step}{$privilege_title}";

                    $resource_name = "{$module_name}_{$section_name}";

                    $record = [
                        'module'  => $module_name,
                        'section' => $section_name,
                        'name'    => $privilege_name,
                        'title'   => "{$title}",
                    ];

                    foreach ($roles as $role) {
                        $record["role_{$role['id']}"] = ! empty($role['privileges'][$resource_name]) &&
                                                        in_array($privilege_name, $role['privileges'][$resource_name])
                            ? 1
                            : 0;
                    }

                    $records[] = $record;
                }
            }
        }



        $table->setRecords($records);

        foreach ($table->getRecords() as $record) {
            if ($record->name === 'access') {
                $record->setAttr('class', 'table-active');
                $record->cell('title')->setAttr('class', 'fw-medium');
            }
        }

        return $table->toArray();
    }


    /**
     * Таблица с доступами для роли
     * @param Row|null $role
     * @return array
     * @throws DbException
     */
    public function getTableRoleAccess(Row $role = null): array {

        $table = new Table('admin', 'roles', 'role_access');
        $table->setClass('table-hover table-borderless table-striped table-sm');
        $table->setGroupBy('module_title', ['class' => 'fw-medium']);
        $table->setShowHeader(true);
        $table->setNoBorder(true);

        $icon_on  = '<i class="bi bi-toggle-on"></i> ';
        $icon_off = '<i class="bi bi-toggle-off"></i> ';

        $table->addColumns([
            (new Column\Html('title', $this->_('Правила')))->setMinWidth(250)->setSort(false)
                ->setAttrHeader('class', 'text-light-emphasis bg-white'),
            (new Column\Toggle("is_access"))->setMinWidth(50)->setSort(false)
                ->setAttrHeader('class', 'text-end bg-white')
                ->showMenuAlways(true)
                ->addMenuButton($icon_on . $this->_('Вкл. все'), "adminRoles.setAccessRoleAll()", ['class' => 'text-danger-emphasis'])
                ->addMenuButton($icon_off . $this->_('Выкл. все'), "adminRoles.setRejectRoleAll()")
        ]);



        $role = $role?->toArray() ?: [];
        $role['privileges'] = ! empty($role['privileges']) ? json_decode($role['privileges'], true) : [];

        $modules_privileges = $this->modAdmin->modelRoles->getModulesPrivileges();
        $records            = [];
        $step               = str_repeat('&nbsp;', 5);

        foreach ($modules_privileges as $module_name => $module) {
            foreach ($module['privileges'] as $privilege_name => $privilege_title) {
                if ($privilege_name === 'access') {
                    $title = $module['title'];

                    if ($module['icon']) {
                        $title = "<i class=\"{$module['icon']}\"></i> {$title}";
                    }

                } else {
                    $title = "{$step}{$privilege_title}";
                }

                $records[] = [
                    'module'    => $module_name,
                    'section'   => '',
                    'name'      => $privilege_name,
                    'title'     => $title,
                    'is_access' => ! empty($role['privileges'][$module_name]) && in_array($privilege_name, $role['privileges'][$module_name]) ? 1 : 0,
                ];
            }

            foreach ($module['sections'] as $section_name => $section) {
                foreach ($section['privileges'] as $privilege_name => $privilege_title) {

                    $title = $privilege_name === 'access'
                        ? "{$step}{$section['title']}"
                        : "{$step}{$step}{$privilege_title}";

                    $resource_name = "{$module_name}_{$section_name}";

                    $records[] = [
                        'module'    => $module_name,
                        'section'   => $section_name,
                        'name'      => $privilege_name,
                        'title'     => $title,
                        'is_access' => ! empty($role['privileges'][$resource_name]) && in_array($privilege_name, $role['privileges'][$resource_name]) ? 1 : 0,
                    ];
                }
            }
        }

        $table->setRecords($records);

        foreach ($table->getRecords() as $record) {
            if ($record->name === 'access') {
                $record->cell('title')->setAttr('class', 'fw-medium');
            }
        }

        return $table->toArray();
    }


    /**
     * Редактирование роли
     * @param Row|null $role
     * @return array
     * @throws DbException
     */
    public function getForm(Row $role = null): array {

        $form = new Form('admin', 'roles');
        $form->setSuccessLoadUrl("#/{$this->base_url}");
        $form->setSuccessNotice();
        $form->setOnSubmit('adminRoles.onSaveRole(form, data)');
        $form->setWidthLabel(150);
        $form->setHandler("{$this->base_url}/" . ($role?->id ?: 0));
        $form->setTable($this->modAdmin->tableRoles, $role?->id);

        $form->setRecord([
            'title'       => $role?->title,
            'description' => $role?->description,
            'privileges'  => $role?->privileges,
        ]);

        $form->addFields([
            (new Field\Text('title',           $this->_('Название')))->setWidth(300),
            (new Field\Textarea('description', $this->_('Примечание')))->setWidth(300),
            (new Field\Custom('privileges',    $this->_('Доступ к модулям')))->setContent(
                $this->getTableRoleAccess($role)
            ),
        ]);

        $form->addControls([
            $form->getBtnSubmit(),
            $form->getBtnCancel("#/{$this->base_url}")
        ]);

        return $form->toArray();
    }
}