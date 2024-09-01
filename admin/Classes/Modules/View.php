<?php
namespace Core3\Mod\Admin\Classes\Modules;
use Core3\Classes\Common;
use Core3\Classes\Db\Row;
use Core3\Classes\Form;
use Core3\Classes\Table;
use CoreUI\Table\Filter;
use CoreUI\Table\Column;
use CoreUI\Table\Control as TableControl;
use CoreUI\Form\Field;
use CoreUI\Form\Control;
use Laminas\Db\RowGateway\AbstractRowGateway;
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

        $table = new Table('admin', 'modules', 'modules_installed');
        $table->setSaveState(true);
        $table->setOverflow(true);
        $table->setShowScrollShadow(true);
        $table->setClickUrl("{$base_url}/[id]");

        $table->setHeaderOut()
            ->left([
                (new TableControl\Columns()),
                (new Filter\Text('title'))->setAttributes(['placeholder' => $this->_('Название / Описание')])->setWidth(250),
                (new TableControl\FilterClear()),
            ])
            ->right([
                (new TableControl\Button('<i class="bi bi-arrow-clockwise"></i> ' . $this->_('Проверить обновления')))->setAttr('class', 'btn btn-outline-secondary'),
                $table->getBtnAdd("{$base_url}/0")
            ]);

        $table->setFooterOut()->left([
            new TableControl\Total
        ]);


        $table->addColumns([
            (new Column\Numbers()),
            $table->getColumnToggle('is_active', $this->_('Активность'),   45, 'switchActive'),
            (new Column\Link('title',            $this->_('Название')))->setMinWidth(200),
            (new Column\Text('Описание',         $this->_('Описание'),     300))->setMinWidth(100),
            (new Column\Text('name',             $this->_('Идентификатор')))->setWidth(200)->setShow(false),
            (new Column\Text('version',          $this->_('Версия'),       100))->setNoWrap(true)->setMinWidth(100),
            (new Column\Badge('is_visible',      $this->_('Отображаемый'), 150)),
            (new Column\Menu('actions',          $this->_('Действия'),     1))->setShowLabel(false)->setAttr('class', 'text-end'),
        ]);


        $modules = $this->modAdmin->tableModules->fetchAll(function (Select $select) {
            $select->order('seq ASC');
        })->toArray();

        $table->setRecords($modules);
        $records = $table->getRecords();

        foreach ($records as $record) {

            $record->title      = ['url' => "{$base_url}/{$record->id}", 'content' => $record->title];
            $record->is_visible = $record->is_visible
                ? ['type' => 'none', 'text' => 'Да']
                : ['type' => 'warning', 'text' => 'Нет'];

            $record->actions = [
                'content'  => '<i class="bi bi-three-dots-vertical"></i><span class="bg-danger border p-1 position-absolute rounded-circle top-0 translate-middle z-1"></span>',
                'attr'     => [
                    'class'   => 'btn btn-outline-secondary rounded-1',
                    'onclick' => 'event.stopPropagation()'
                ],
                'position' => 'end',
                'items'    => [
                    [
                        'type'    => 'button',
                        'content' => '<i class="bi bi-arrow-down-circle"></i> ' . $this->_('Обновить до 1.1.0'),
                        'attr'    => ['class' => 'text-success'],
                        'onClick' => '',
                    ],
                    [
                        'type'    => 'button',
                        'content' => '<i class="bi bi-arrow-clockwise"></i> ' . $this->_('Проверить обновления'),
                        'onClick' => '',
                    ],
                    ['type' => 'divider'],
                    [
                        'type'    => 'button',
                        'content' => '<i class="bi bi-trash"></i> ' . $this->_('Удалить'),
                        'attr'    => ['class' => 'text-danger'],
                        'onClick' => '',
                    ],
                ],
            ];
        }

        return $table->toArray();
    }


    /**
     * Таблица с модулями
     * @throws \Exception
     */
    public function getTableAvailable(string $base_url): array {


        return [];
    }


    /**
     * Таблица с модулями
     * @throws \Exception
     */
    public function getTableSections(string $base_url): array {


        return [];
    }


    /**
     * Форма добавление модуля
     * @throws \Exception
     */
    public function getFormInstallHand(string $base_url): array {

        $form = new Form('admin', 'modules', 'module');
        $form->setHandler('saveHand');
        $form->setSuccessLoadUrl('#/admin/modules');
        $form->setOnSubmitSuccessDefault();

        $form->setRecord([
            'title'            => '',
            'icon'             => '',
            'name'             => '',
            'version'          => '1.0.0',
            'group_name'       => '',
            'description'      => '',
            'is_active'        => true,
            'is_visible'       => true,
            'is_visible_index' => true,
        ]);


        $form->addFields([
            (new Field\Text('title',              $this->_('Название')))->setWidth(350)->setRequired(true),
            (new Field\Text('icon',               $this->_('Иконка')))->setWidth(350)
                ->setDescriptionHelp($this->_('Укажите значение класса из доступных видов иконок: bootstrap, fontawesome, material')),
            (new Field\Text('name',               $this->_('Идентификатор')))->setWidth(150)->setRequired(true)
                ->setValidPattern('[\\da-z]')->setInvalidText('Маленькие латинские буквы или цифры')
                ->setDescriptionHelp($this->_('Уникальное имя модуля, должно быть таким же как и папка в которой он находится')),
            (new Field\Text('version',            $this->_('Версия')))->setWidth(150)->setRequired(true)
                ->setValidPattern('[\\d]+\\.[\\d]\\.[\\d]+(-[a-z0-9]+|)')->setInvalidText('Требуемый формат версии 0.0.0'),
            (new Field\Textarea('description',    $this->_('Описание')))->setWidth(350)->setHeight(50),
            (new Field\Text('group_name',      $this->_('Название группы')))->setWidth(350)
                ->setDescriptionHelp($this->_('Указанное название будет добавлено в меню для группировки модулей')),
            (new Field\Toggle('is_active',        $this->_('Активен'))),
            (new Field\Toggle('is_visible',       $this->_('Видимый')))->setDescriptionHelp($this->_('Будет скрыт из меню, если не активно')),
            (new Field\Toggle('is_visible_index', $this->_('Имеет главную страницу'))),
        ]);


        $form->addControls([
            $form->getBtnSubmit(),
            (new Control\Link('Отмена'))->setUrl($base_url)->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
    }


    /**
     * Форма добавление модуля
     * @throws \Exception
     */
    public function getFormInstallFile(string $base_url): array {

        $form = new Form('admin', 'modules', 'module');
        $form->setHandler('saveHand');
        $form->setSuccessLoadUrl('#/admin/modules');
        $form->setOnSubmitSuccessDefault();

        $form->setRecord([
            'title'            => '',
            'icon'             => '',
            'name'             => '',
            'version'          => '1.0.0',
            'group_name'       => '',
            'description'      => '',
            'is_active'        => true,
            'is_visible'       => true,
            'is_visible_index' => true,
        ]);


        $form->addFields([
            (new Field\Text('title',              $this->_('Название')))->setWidth(350)->setRequired(true),
            (new Field\Text('icon',               $this->_('Иконка')))->setWidth(350)
                ->setDescriptionHelp($this->_('Укажите значение класса из доступных видов иконок: bootstrap, fontawesome, material')),
            (new Field\Text('name',               $this->_('Идентификатор')))->setWidth(150)->setRequired(true)
                ->setValidPattern('[\\da-z]')->setInvalidText('Маленькие латинские буквы или цифры')
                ->setDescriptionHelp($this->_('Уникальное имя модуля, должно быть таким же как и папка в которой он находится')),
            (new Field\Text('version',            $this->_('Версия')))->setWidth(150)->setRequired(true)
                ->setValidPattern('[\\d]+\\.[\\d]\\.[\\d]+(-[a-z0-9]+|)')->setInvalidText('Требуемый формат версии 0.0.0'),
            (new Field\Textarea('description',    $this->_('Описание')))->setWidth(350)->setHeight(50),
            (new Field\Text('group_name',      $this->_('Название группы')))->setWidth(350)
                ->setDescriptionHelp($this->_('Указанное название будет добавлено в меню для группировки модулей')),
            (new Field\Toggle('is_active',        $this->_('Активен'))),
            (new Field\Toggle('is_visible',       $this->_('Видимый')))->setDescriptionHelp($this->_('Будет скрыт из меню, если не активно')),
            (new Field\Toggle('is_visible_index', $this->_('Имеет главную страницу'))),
        ]);


        $form->addControls([
            $form->getBtnSubmit(),
            (new Control\Link('Отмена'))->setUrl($base_url)->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
    }



    /**
     * Форма добавление модуля
     * @throws \Exception
     */
    public function getFormInstallLink(string $base_url): array {

        $form = new Form('admin', 'modules', 'module');
        $form->setHandler('saveHand');
        $form->setSuccessLoadUrl('#/admin/modules');
        $form->setOnSubmitSuccessDefault();

        $form->setRecord([
            'title'            => '',
            'icon'             => '',
            'name'             => '',
            'version'          => '1.0.0',
            'group_name'       => '',
            'description'      => '',
            'is_active'        => true,
            'is_visible'       => true,
            'is_visible_index' => true,
        ]);


        $form->addFields([
            (new Field\Text('title',              $this->_('Название')))->setWidth(350)->setRequired(true),
            (new Field\Text('icon',               $this->_('Иконка')))->setWidth(350)
                ->setDescriptionHelp($this->_('Укажите значение класса из доступных видов иконок: bootstrap, fontawesome, material')),
            (new Field\Text('name',               $this->_('Идентификатор')))->setWidth(150)->setRequired(true)
                ->setValidPattern('[\\da-z]')->setInvalidText('Маленькие латинские буквы или цифры')
                ->setDescriptionHelp($this->_('Уникальное имя модуля, должно быть таким же как и папка в которой он находится')),
            (new Field\Text('version',            $this->_('Версия')))->setWidth(150)->setRequired(true)
                ->setValidPattern('[\\d]+\\.[\\d]\\.[\\d]+(-[a-z0-9]+|)')->setInvalidText('Требуемый формат версии 0.0.0'),
            (new Field\Textarea('description',    $this->_('Описание')))->setWidth(350)->setHeight(50),
            (new Field\Text('group_name',      $this->_('Название группы')))->setWidth(350)
                ->setDescriptionHelp($this->_('Указанное название будет добавлено в меню для группировки модулей')),
            (new Field\Toggle('is_active',        $this->_('Активен'))),
            (new Field\Toggle('is_visible',       $this->_('Видимый')))->setDescriptionHelp($this->_('Будет скрыт из меню, если не активно')),
            (new Field\Toggle('is_visible_index', $this->_('Имеет главную страницу'))),
        ]);


        $form->addControls([
            $form->getBtnSubmit(),
            (new Control\Link('Отмена'))->setUrl($base_url)->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
    }


    /**
     * Форма редактирования модуля
     * @param string $base_url
     * @param Row    $module
     * @return array
     */
    public function getFormModule(string $base_url, Row $module): array {

        $form = new Form('admin', 'modules', 'module');
        $form->setTable($this->modAdmin->tableModules, $module->id);
        $form->setHandler('save', 'put');
        $form->setSuccessLoadUrl('#/admin/modules');
        $form->setOnSubmitSuccessDefault();
        $form->setWidthLabel(160);


        $form->setRecord([
            'title'       => $module->title,
            'icon'        => $module->icon,
            'description' => $module->description,
            'group_name'  => $module->group_name,
            'is_active'   => (string)$module->is_active,
        ]);

        $form->addFields([
            (new Field\Text('title',           $this->_('Название')))->setWidth(350)->setRequired(true),
            (new Field\Text('icon',            $this->_('Иконка')))->setWidth(350)
                ->setDescriptionHelp($this->_('Укажите значение класса из доступных видов иконок: bootstrap, fontawesome, material')),
            (new Field\Textarea('description', $this->_('Описание')))->setWidth(350)->setHeight(50),
            (new Field\Text('group_name',      $this->_('Название группы')))->setWidth(350)
                ->setDescriptionHelp($this->_('Указанное название будет добавлено в меню для группировки модулей')),
            (new Field\Toggle('is_active',     $this->_('Активен')))->setValueY(1)->setValueN(0),

        ]);

        $form->addControls([
            $form->getBtnSubmit(),
            (new Control\Link('Отмена'))->setUrl($base_url)->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
    }


    /**
     * Форма редактирования модуля
     * @param string $base_url
     * @param Row    $module
     * @return array
     */
    public function getFormSection(string $base_url, Row $module): array {

        $form = new Form('admin', 'modules', 'module');
        $form->setHandler('save');
        $form->setSuccessLoadUrl('#/admin/modules');
        $form->setOnSubmitSuccessDefault();


        $form->setRecord([
            'title'            => $module->title,
            'icon'             => $module->icon,
            'name'             => $module->name,
            'version'          => $module->version,
            'description'      => $module->description,
            'is_active'        => (string)$module->is_active,
            'is_visible'       => (string)$module->is_visible,
            'is_visible_index' => (string)$module->is_visible_index,
        ]);

        $form->addFields([
            (new Field\Text('title',              $this->_('Название')))->setWidth(350)->setRequired(true),
            (new Field\Text('icon',               $this->_('Иконка')))->setWidth(350)->setDescriptionHelp($this->_('Укажите значение класса из доступных видов иконок: bootstrap, fontawesome, material')),
            (new Field\Text('name',               $this->_('Идентификатор')))->setNoSend(true)->setReadonly(true),
            (new Field\Text('version',            $this->_('Версия')))->setNoSend(true)->setReadonly(true),
            (new Field\Textarea('description',    $this->_('Описание')))->setWidth(350)->setHeight(50),
            (new Field\Toggle('is_active',        $this->_('Активен')))->setValueY(1)->setValueN(0),
            (new Field\Toggle('is_visible',       $this->_('Видимый')))->setValueY(1)->setValueN(0)->setDescriptionHelp($this->_('Будет скрыт из меню, если не активно')),
            (new Field\Toggle('is_visible_index', $this->_('Имеет главную страницу')))->setValueY(1)->setValueN(0),

        ]);

        $form->addControls([
            $form->getBtnSubmit(),
            (new Control\Link('Отмена'))->setUrl($base_url)->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
    }
}