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
            ];

            $privileges = $record->privileges
                ? json_decode($record->privileges, true)
                : [];


            $record->users_count = 0;

            foreach ($users as $user) {
                if ($user->role_id == $record->id) {
                    $record->users_count++;
                }
            }


            $record->modules_count = 0;

            foreach ($privileges as $access_rules) {

                if (in_array('access', $access_rules)) {
                    $record->modules_count++;
                }
            }
        }

        //$table->limitFields(['id', 'title', 'description', 'privileges']);

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
        $table->setGroupBy('module_title', ['class' => 'fw-medium']);
        $table->addControlColumns();

        $table->setHeaderOut($table::LAST)
            ->left([
                (new Filter\Text('title'))->setAttributes(['placeholder' => $this->_('Правило модуля')])->setWidth(200),
                (new TableControl\FilterClear()),
            ]);

        $table->addColumns([
            (new Column\Text('title', $this->_('Правило модуля')))->setMinWidth(200)->setFixedLeft()->setSort(false),
        ]);

        $roles = $this->modAdmin->tableRoles->fetchAll()->toArray();
        $roles = Tools::arrayMultisort($roles, ['title' => 'asc',]);

        foreach ($roles as $key => $role) {
            $roles[$key]['privileges'] = $role['privileges'] ? json_decode($role['privileges'], true) : [];

            $table->addColumns([
                (new Column\Toggle("role_{$role['id']}", $role['title']))->setMinWidth(140)->setSort(false)
                    ->setOnChange("adminRoles.switchAccess(record, '{$role['id']}', checked)")
//                    ->showMenuAlways(true)
//                    ->addMenuItem($this->_('Включить все'), "adminRoles.setAccessAll('{$role['id']}')", ['class' => 'text-danger-emphasis'])
//                    ->addMenuItem($this->_('Выключить все'), "adminRoles.setRejectAll('{$role['id']}')")
            ]);
        }

        $modules = $this->modAdmin->tableModules->fetchAll(function (Select $select) {
            $select->order('seq');
        });
        $sections = $this->modAdmin->tableModulesSections->fetchAll(function (Select $select) {
            $select->order('seq');
        });

        $records            = [];
        $default_privileges = [
            'access' => $this->_('Доступ'),
            'edit'   => $this->_('Редактирование'),
            'delete' => $this->_('Удаление'),
        ];



        $step = str_repeat('&nbsp;', 5);

        foreach ($modules as $module) {
            $modules_info = $this->getModuleInfoFromFile($module->name);

            $privileges     = $default_privileges;
            $mod_privileges = ! empty($modules_info) && ! empty($modules_info['privileges']) && is_array($modules_info['privileges'])
                ? $modules_info['privileges']
                : [];

            foreach ($mod_privileges as $privilege_name => $privilege_title) {
                $privileges[$privilege_name] = $privilege_title;
            }

            foreach ($privileges as $privilege_name => $privilege_title) {
                $record = [
                    'module_title' => $module->title,
                    'module'       => $module->name,
                    'section'      => '',
                    'name'         => $privilege_name,
                    'title'        => "{$step}{$privilege_title}",
                ];

                foreach ($roles as $role) {
                    $record["role_{$role['id']}"] = ! empty($role['privileges'][$module->name]) &&
                                                    in_array($privilege_name, $role['privileges'][$module->name])
                        ? 1
                        : 0;
                }

                $records[] = $record;
            }





            foreach ($sections as $section) {
                $privileges     = $default_privileges;
                $mod_privileges = ! empty($modules_info) &&
                                  ! empty($modules_info['sections']) &&
                                  ! empty($modules_info['sections'][$section->name]) &&
                                  ! empty($modules_info['sections'][$section->name]) &&
                                  ! empty($modules_info['sections'][$section->name]['privileges']) &&
                                  is_array($modules_info['sections']['privileges'])
                    ? $modules_info['sections'][$section->name]['privileges']
                    : [];

                foreach ($mod_privileges as $privilege_name => $privilege_title) {
                    $privileges[$privilege_name] = $privilege_title;
                }

                foreach ($privileges as $privilege_name => $privilege_title) {
                    $record = [
                        'module_title' => "{$step}{$module->title} / {$section->title}",
                        'module'       => $module->name,
                        'section'      => $section->name,
                        'name'         => $privilege_name,
                        'title'        => "{$step}{$step}{$privilege_title}",
                    ];

                    foreach ($roles as $role) {
                        $resource_name = "{$module->name}_{$section->name}";

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
        $table->setShowHeader(false);
        $table->setNoBorder(true);


        $table->addColumns([
            (new Column\Text('title'))->setMinWidth(250),
        ]);

        $role = $role?->toArray() ?: [];

        $role['privileges'] = ! empty($role['privileges']) ? json_decode($role['privileges'], true) : [];

        $table->addColumns([
            (new Column\Toggle("is_access"))->setMinWidth(50)->setSort(false)
//                    ->showMenuAlways(true)
//                    ->addMenuItem($this->_('Включить все'), "adminRoles.setAccessAll('{$role['id']}')", ['class' => 'text-danger-emphasis'])
//                    ->addMenuItem($this->_('Выключить все'), "adminRoles.setRejectAll('{$role['id']}')")
        ]);

        $modules = $this->modAdmin->tableModules->fetchAll(function (Select $select) {
            $select->order('seq');
        });
        $sections = $this->modAdmin->tableModulesSections->fetchAll(function (Select $select) {
            $select->order('seq');
        });

        $records            = [];
        $default_privileges = [
            'access' => $this->_('Доступ'),
            'edit'   => $this->_('Редактирование'),
            'delete' => $this->_('Удаление'),
        ];


        $step = str_repeat('&nbsp;', 5);

        foreach ($modules as $module) {
            $modules_info = $this->getModuleInfoFromFile($module->name);

            $privileges     = $default_privileges;
            $mod_privileges = ! empty($modules_info) && ! empty($modules_info['privileges']) && is_array($modules_info['privileges'])
                ? $modules_info['privileges']
                : [];

            foreach ($mod_privileges as $privilege_name => $privilege_title) {
                $privileges[$privilege_name] = $privilege_title;
            }

            foreach ($privileges as $privilege_name => $privilege_title) {
                $record = [
                    'module_title' => $module->title,
                    'module'       => $module->name,
                    'section'      => '',
                    'name'         => $privilege_name,
                    'title'        => "{$step}{$privilege_title}",
                ];

                $record["is_access"] = ! empty($role['privileges'][$module->name]) &&
                                       in_array($privilege_name, $role['privileges'][$module->name])
                    ? 1
                    : 0;

                $records[] = $record;
            }




            foreach ($sections as $section) {
                $privileges     = $default_privileges;
                $mod_privileges = ! empty($modules_info) &&
                                  ! empty($modules_info['sections']) &&
                                  ! empty($modules_info['sections'][$section->name]) &&
                                  ! empty($modules_info['sections'][$section->name]) &&
                                  ! empty($modules_info['sections'][$section->name]['privileges']) &&
                                  is_array($modules_info['sections']['privileges'])
                    ? $modules_info['sections'][$section->name]['privileges']
                    : [];

                foreach ($mod_privileges as $privilege_name => $privilege_title) {
                    $privileges[$privilege_name] = $privilege_title;
                }

                foreach ($privileges as $privilege_name => $privilege_title) {
                    $record = [
                        'module_title' => "{$step}{$module->title} / {$section->title}",
                        'module'       => $module->name,
                        'section'      => $section->name,
                        'name'         => $privilege_name,
                        'title'        => "{$step}{$step}{$privilege_title}",
                    ];

                    $resource_name = "{$module->name}_{$section->name}";

                    $record["is_access"] = ! empty($role['privileges'][$resource_name]) &&
                                           in_array($privilege_name, $role['privileges'][$resource_name])
                        ? 1
                        : 0;

                    $records[] = $record;
                }
            }
        }

        $table->setRecords($records);

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
        $form->setHandler("{$this->base_url}/" . ($role?->id ?: 0), 'post');
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