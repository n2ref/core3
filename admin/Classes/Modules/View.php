<?php
namespace Core3\Mod\Admin\Classes\Modules;
use Core3\Classes\Common;
use Core3\Classes\Tools;
use Laminas\Db\Sql\Select;


/**
 *
 */
class View extends Common {


    /**
     * Таблица с модулями
     * @throws \Exception
     */
    public function getTableInstalled(string $base_url): array {

        $modules = $this->modAdmin->tableModules->fetchAll(function (Select $select) {
            $select->order('seq ASC');
        });

        $records = [];

        foreach ($modules as $module) {

            $records[] = [
                'id'            => $module->id,
                'name'          => $module->name,
                'title'         => $module->title,
                'version'       => $module->version,
                'is_visible_sw' => $module->is_visible_sw == 'Y' ? '<span class="text-success">Да</span>' :  '<span class="text-danger">Нет</span>',
                'is_active_sw'  => $module->is_active_sw,
                'actions'       => "<button class=\"btn btn-sm btn-outline-danger\"><i class=\"bi bi-trash3-fill\"></i></button>",
            ];
        }

        $table_modules = [
            'component' => 'coreui.table',
            'primaryKey' => 'id',
            'show'       => [
                'total' => true,
            ],
            'controls' => [
                [ 'type' => "link",   'content' => "<i class=\"bi bi-plus\"></i> Добавить", 'href' => "{$base_url}?edit=0", 'attr' => [ 'class' => 'btn btn-sm btn-success' ] ],
            ],
            'onClickUrl' => "{$base_url}?edit=[id]",
            'columns' => [
                [ 'type' => 'numbers', 'width' => 25, 'attr' => [ 'class' => "border-end text-end" ] ],
                [ 'field' => 'title',         'label' => 'Название',      'width' => 200 ],
                [ 'field' => 'version',       'label' => 'Версия' ],
                [ 'field' => 'is_visible_sw', 'label' => 'Отображаемый',  'width' => 140, 'type' => 'html' ],
                [ 'field' => 'is_active_sw',  'label' => 'Вкл',           'width' => 50,  'type' => 'switch' ],
                [ 'field' => 'actions',       'label' => '',              'width' => 50,  'type' => 'html' ],
            ],
            'records' => $records
        ];

        return $table_modules;
    }


    /**
     * Таблица с модулями
     * @throws \Exception
     */
    public function getTableAvailable(string $base_url): array {


        return [];
    }
}