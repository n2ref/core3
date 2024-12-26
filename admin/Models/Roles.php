<?php
namespace Core3\Mod\Admin\Models;
use Core3\Classes\Common;
use Core3\Mod\Admin;
use Laminas\Db\Sql\Select;


/**
 * @property Admin\Controller $modAdmin
 */
class Roles extends Common {

    /**
     * @return array
     * @throws \Core3\Exceptions\DbException
     */
    public function getModulesPrivileges(): array {

        $modules = $this->modAdmin->tableModules->fetchAll(function (Select $select) {
            $select->order('seq');
        });
        $sections = $this->modAdmin->tableModulesSections->fetchAll(function (Select $select) {
            $select->order('seq');
        });

        $result             = [];
        $default_privileges = [
            'access' => $this->_('Доступ'),
            'edit'   => $this->_('Редактирование'),
            'delete' => $this->_('Удаление'),
        ];

        foreach ($modules as $module) {
            $modules_info = $this->getModuleInfoFromFile($module->name);

            $privileges     = $default_privileges;
            $mod_privileges = ! empty($modules_info) && ! empty($modules_info['privileges']) && is_array($modules_info['privileges'])
                ? $modules_info['privileges']
                : [];

            foreach ($mod_privileges as $privilege_name => $privilege_title) {
                $privileges[$privilege_name] = $privilege_title;
            }

            $result[$module->name]['title']      = $module->title;
            $result[$module->name]['icon']       = $module->icon;
            $result[$module->name]['privileges'] = $privileges;
            $result[$module->name]['sections']   = [];



            foreach ($sections as $section) {
                if ($module->id == $section->module_id) {

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

                    $result[$module->name]['sections'][$section->name]['title']      = $section->title;
                    $result[$module->name]['sections'][$section->name]['privileges'] = $privileges;
                }
            }
        }

        return $result;
    }
}