<?php
namespace Core3\Mod\Admin\Classes\Modules;
use Core3\Classes\Common;
use Core3\Classes\Db\Row;
use Core3\Classes\Form;
use Core3\Classes\Table;
use CoreUI\Table\Filter;
use CoreUI\Table\Column;
use CoreUI\Table\Search;
use CoreUI\Table\Control as TableControl;
use CoreUI\Form\Field;
use CoreUI\Form\Control;
use Laminas\Db\Sql\Select;


/**
 *
 */
class View extends Common {

    private string $base_url = "admin/modules";

    /**
     * Таблица с модулями
     * @throws \Exception
     */
    public function getTableInstalled(): array {

        $table = new Table('admin', 'modules', 'modules_installed');
        $table->setSaveState(true);
        $table->setOverflow(true);
        $table->setClickUrl("#/{$this->base_url}/[id]");

        $dropdown = (new TableControl\Dropdown('<i class="bi bi-plus"></i> ' . $this->_('Добавить')))->setAttr('class', 'btn btn-success dropdown-toggle');
        $dropdown->addLink('<i class="bi bi-person-fill-gear"></i> ' . $this->_('Ручная установка'),    "#/{$this->base_url}/0/hand");
        $dropdown->addLink('<i class="bi bi-file-earmark-zip"></i> ' . $this->_('Установка из файла'), "#/{$this->base_url}/0/file");
        $dropdown->addLink('<i class="bi bi-link-45deg"></i> ' . $this->_('Установка по ссылке'),   "#/{$this->base_url}/0/link");


        $table->setHeaderOut()
            ->left([
                $table->getControlColumns(),
                (new TableControl\Divider())->setWidth(30),
                (new Filter\Text('title'))->setAttributes(['placeholder' => $this->_('Название / Описание')])->setWidth(250)->setAutoSearch(true),
                (new TableControl\FilterClear()),
            ])
            ->right([
                $dropdown,
            ]);

        $table->setFooterOut()->left([
            new TableControl\Total
        ]);


        $table->addColumns([
            $table->getColumnToggle('is_active', $this->_('Активность'),   45),
            (new Column\Link('title',            $this->_('Название')))->setMinWidth(200),
            (new Column\Text('description',      $this->_('Описание'),     300))->setMinWidth(100),
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

            $icon = $record->icon
                ? "<i class=\"{$record->icon}\"></i> "
                : '';

            $record->title      = ['url' => "#/{$this->base_url}/{$record->id}", 'content' => $icon . $record->title];
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


            $record->cell('actions')->setAttr('onclick', 'event.cancelBubble = true;');
        }

        return $table->toArray();
    }


    /**
     * Таблица с модулями
     * @param int|null $select_module_id
     * @return array
     */
    public function getTableAvailable(int $select_module_id = null): array {

        $table = new Table('admin', 'modules');
        $table->setTheme();
        $table->setClass('table-hover table-striped');
        $table->setRecordsRequest("{$this->base_url}/available/records?select_module_id={$select_module_id}");
        $table->setClickUrl("#/{$this->base_url}/available/[id]");
        $table->setShowHeader(false);

        $table->setHeaderOut($table::LAST)
            ->left([
                (new Filter\Text('text'))->setAttributes(['placeholder' => $this->_('Поиск')])
                    ->setWidth(200)->setAutoSearch(true),
                (new TableControl\FilterClear()),
            ]);


        $table->addColumns([
            (new Column\Html('icon'))->setWidth(50)->setSort(false),
            (new Column\Html('title')),
            (new Column\Button('install'))->setWidth(100)->setAttr('class', 'text-end'),
        ]);

        return $table->toArray();
    }


    /**
     * Таблица с разделами модуля
     * @param string $base_url
     * @param Row    $module
     * @return array
     */
    public function getTableSections(string $base_url, Row $module): array {

        $content_url = "admin/modules/{$module->id}/sections";
        $window_url  = "#/{$base_url}/{$module->id}/sections";

        $table = new Table('admin', 'modules', 'module_sections');
        $table->setSaveState(true);
        $table->setOverflow(true);
        $table->setClickUrl("#{$base_url}/{$module->id}/sections/[id]");

        $table->setHeaderOut()
            ->left([
                (new Filter\Text('title'))->setAttributes(['placeholder' => $this->_('Название / Идентификатор')])->setWidth(250)->setAutoSearch(true),
                (new TableControl\FilterClear()),
            ]);

        $table->setFooterOut()->left([
            new TableControl\Total
        ]);


        $table->addColumns([
            $table->getColumnToggle('is_active', $this->_('Активность'),   45),
            (new Column\Link('title',            $this->_('Название')))->setMinWidth(200),
            (new Column\Text('name',             $this->_('Идентификатор'), 140)),
        ]);


        if ($module->install_type == 'hand') {
            $table->setHeaderOut($table::FIRST)->right([
                $table->getBtnAdd("{$window_url}/0"),
                $table->getBtnDelete("{$content_url}/delete")
            ]);

            $table->addColumns([
                (new Column\Select()),
            ]);
        }


        $module_sections = $this->modAdmin->tableModulesSections->fetchAll(function (Select $select) use ($module) {
            $select
                ->where(['module_id' => $module->id])
                ->order('seq ASC');
        })->toArray();

        $table->setRecords($module_sections);

        foreach ($table->getRecords() as $record) {

            $record->title = [
                'url'     => "#/{$base_url}/{$module->id}/sections/{$record->id}",
                'content' => $record->title,
            ];
        }

        return $table->toArray();
    }


    /**
     * Таблица с разделами модуля
     * @param Row $module
     * @return array
     */
    public function getTableVersions(Row $module): array {

        $table = new Table('admin', 'modules', 'module_versions');
        $table->setSaveState(true);
        $table->setOverflow(true);
        $table->setRecordsRequest("{$this->base_url}/{$module->id}/versions/records");

        $table->setHeaderOut()
            ->left([
                (new Filter\Text('text'))->setAttributes(['placeholder' => $this->_('Версия / Репозиторий')])->setWidth(250)->setAutoSearch(true),
                (new TableControl\FilterClear()),
            ])
            ->right([
                $table->getBtnAdd("#/{$this->base_url}/{$module->id}/versions/0"),
                $table->getBtnDelete("{$this->base_url}/{$module->id}/versions/records")
            ]);

        $table->setFooterOut()->left([
            new TableControl\Total
        ]);



        $table->addColumns([
            (new Column\Button('install',  $this->_('Установка'), 110))->setShowLabel(false)->setSort(false),
            (new Column\Text('version',    $this->_('Версия'),    150)),
            (new Column\Text('vendor',     $this->_('Издатель'),  150)),
            (new Column\Text('file_url',   $this->_('Репозиторий'))),
            (new Column\Button('file',     '', 1))->setSort(false),
            (new Column\Select()),
        ]);

        return $table->toArray();
    }


    /**
     * Таблица с разделами модуля
     * @param Row $available_module
     * @return array
     */
    public function getTableAvailableVersions(Row $available_module): array {

        $table = new Table('admin', 'modules', 'available_versions');
        $table->setSaveState(true);
        $table->setOverflow(true);
        $table->setRecordsRequest("{$this->base_url}/available/{$available_module->id}/versions");

        $table->setHeaderOut()
            ->left([
                (new Filter\Text('text'))->setAttributes(['placeholder' => $this->_('Версия / Репозиторий')])->setWidth(250)->setAutoSearch(true),
                (new TableControl\FilterClear()),
            ])
            ->right([
                $table->getBtnAdd("#/{$this->base_url}/{$available_module->id}/versions/0"),
                $table->getBtnDelete("{$this->base_url}/{$available_module->id}/versions/records")
            ]);

        $table->setFooterOut()->left([
            new TableControl\Total
        ]);



        $table->addColumns([
            (new Column\Button('install',  $this->_('Установка'), 110))->setShowLabel(false)->setSort(false),
            (new Column\Text('version',    $this->_('Версия'),    150)),
            (new Column\Text('vendor',     $this->_('Издатель'),  150)),
            (new Column\Button('file',     '', 1))->setSort(false),
            (new Column\Select()),
        ]);

        return $table->toArray();
    }


    /**
     * Форма добавление модуля
     * @throws \Exception
     */
    public function getFormInstallHand(): array {

        $form = new Form('admin', 'modules', 'module');
        $form->setHandler('saveHand');
        $form->setSuccessLoadUrl("#/{$this->base_url}");
        $form->setSuccessNotice();
        $form->setWidthLabel(190);

        $form->setRecord([
            'title'            => '',
            'icon'             => '',
            'name'             => '',
            'version'          => '1.0.0',
            'description'      => '',
            'is_active'        => 1,
            'is_visible'       => 1,
            'is_visible_index' => 1,
        ]);


        $form->addFields([
            (new Field\Text('title',              $this->_('Название')))->setWidth(350)->setRequired(true),
            (new Field\Text('icon',               $this->_('Иконка')))->setWidth(350)
                ->setHelp($this->_('Укажите значение класса из доступных видов иконок: bootstrap, fontawesome, material')),
            (new Field\Text('name',               $this->_('Идентификатор')))->setWidth(150)->setRequired(true)
                ->setValidPattern('[\\da-z]')->setInvalidText('Маленькие латинские буквы или цифры')
                ->setHelp($this->_('Уникальное имя модуля, должно быть таким же как и папка в которой он находится')),
            (new Field\Text('version',            $this->_('Версия')))->setWidth(150)->setRequired(true)
                ->setValidPattern('[\\d]+\\.[\\d]\\.[\\d]+(-[a-z0-9]+|)')->setInvalidText('Требуемый формат версии 0.0.0'),
            (new Field\Textarea('description',    $this->_('Описание')))->setWidth(350)->setHeight(50),
            (new Field\Toggle('is_active',        $this->_('Активен'))),
            (new Field\Toggle('is_visible',       $this->_('Видимый')))->setDescription($this->_('Будет скрыт из меню, если не активно')),
            (new Field\Toggle('is_visible_index', $this->_('Имеет главную страницу')))->setDescription($this->_('Будет ли кликабельным в меню название модуля. <br>Следует отключить если модуль имеет только отдельные разделы')),
        ]);


        $form->addControls([
            $form->getBtnSubmit(),
            (new Control\Link('Отмена'))->setUrl("#/{$this->base_url}")->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
    }


    /**
     * Форма добавление модуля
     * @throws \Exception
     */
    public function getFormInstallFile(): array {

        $form = new Form('admin', 'modules', 'module');
        $form->setHandler('saveFile');
        $form->setSuccessLoadUrl("#/{$this->base_url}");
        $form->setSuccessNotice();
        $form->setWidthLabel(190);

        $form->setRecord([
            'file'      => null,
            'is_active' => 1,
        ]);


        $form->addFields([
            (new Field\FileUpload('file',  $this->_('Файл модуля (.zip)')))->setAcceptZip()->setFilesLimit(1)->setUrl("/{$this->base_url}/installed/file/upload")->setRequired(),
            (new Field\Toggle('is_active', $this->_('Активен'))),
        ]);


        $form->addControls([
            $form->getBtnSubmit(),
            (new Control\Link('Отмена'))->setUrl("#/{$this->base_url}")->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
    }



    /**
     * Форма добавление модуля
     * @throws \Exception
     */
    public function getFormInstallLink(): array {

        $form = new Form('admin', 'modules', 'module');
        $form->setHandler('saveLink');
        $form->setSuccessLoadUrl("#/{$this->base_url}");
        $form->setSuccessNotice();
        $form->setWidthLabel(190);

        $form->setRecord([
            'link'       => '',
            'is_active'  => 1,
        ]);


        $form->addFields([
            (new Field\Text('link',        $this->_('Ссылка на файл')))->setWidth(350)->setRequired(),
            (new Field\Toggle('is_active', $this->_('Активен'))),
        ]);


        $form->addControls([
            $form->getBtnSubmit(),
            (new Control\Link('Отмена'))->setUrl("#/{$this->base_url}")->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
    }


    /**
     * Форма редактирования модуля
     * @param Row $module
     * @return array
     */
    public function getFormModule(Row $module): array {

        $form = new Form('admin', 'modules', 'module');
        $form->setTable($this->modAdmin->tableModules, $module->id);
        $form->setHandler("{$this->base_url}/{$module->id}", 'put');
        $form->setSuccessLoadUrl("#/{$this->base_url}/{$module->id}");
        $form->setSuccessNotice();
        $form->setWidthLabel(160);


        $form->setRecord([
            'title'       => $module->title,
            'icon'        => $module->icon,
            'description' => $module->description,
            'is_active'   => (string)$module->is_active,
        ]);

        $form->addFields([
            (new Field\Text('title',           $this->_('Название')))->setWidth(350)->setRequired(true),
            (new Field\Text('icon',            $this->_('Иконка')))->setWidth(350)
                ->setHelp($this->_('Укажите значение класса из доступных видов иконок: bootstrap, fontawesome, material')),
            (new Field\Textarea('description', $this->_('Описание')))->setWidth(350)->setHeight(50),
            (new Field\Toggle('is_active',     $this->_('Активен')))->setValueY(1)->setValueN(0),

        ]);

        $form->addControls([
            $form->getBtnSubmit(),
        ]);

        return $form->toArray();
    }


    /**
     * Форма редактирования модуля вручную
     */
    public function getFormModuleHand(Row $module): array {

        $form = new Form('admin', 'modules', 'module');
        $form->setTable($this->modAdmin->tableModules, $module->id);
        $form->setHandler("{$this->base_url}/{$module->id}/hand", 'put');
        $form->setSuccessLoadUrl("#/{$this->base_url}/{$module->id}");
        $form->setSuccessNotice();

        $form->setRecord([
            'title'            => $module->title,
            'icon'             => $module->icon,
            'name'             => $module->name,
            'vendor'           => $module->vendor,
            'version'          => $module->version,
            'description'      => $module->description,
            'is_active'        => $module->is_active,
            'is_visible'       => $module->is_visible,
            'is_visible_index' => $module->is_visible_index,
        ]);


        $form->addFields([
            (new Field\Text('title',              $this->_('Название')))->setWidth(350)->setRequired(true),
            (new Field\Text('icon',               $this->_('Иконка')))->setWidth(350)
                ->setHelp($this->_('Укажите значение класса из доступных видов иконок: bootstrap, fontawesome, material')),
            (new Field\Text('name',               $this->_('Идентификатор')))->setWidth(150)->setRequired(true)
                ->setValidPattern('[\\da-z]*')->setInvalidText('Маленькие латинские буквы или цифры')
                ->setHelp($this->_('Уникальное имя модуля, должно быть таким же как и папка в которой он находится')),
            (new Field\Text('vendor',             $this->_('Издатель')))->setWidth(150)
                ->setValidPattern('[\\da-z_]*')->setInvalidText('Маленькие латинские буквы, цифры или знак _')
                ->setHelp($this->_('Имя автора или название производителя')),
            (new Field\Text('version',            $this->_('Версия')))->setWidth(150)->setRequired(true)
                ->setValidPattern('[\\d]+\\.[\\d]\\.[\\d]+(-[a-z0-9]+|)')->setInvalidText('Требуемый формат версии 0.0.0'),
            (new Field\Textarea('description',    $this->_('Описание')))->setWidth(350)->setHeight(50),
            (new Field\Toggle('is_active',        $this->_('Активен'))),
            (new Field\Toggle('is_visible',       $this->_('Видимый')))->setDescription($this->_('Будет скрыт из меню, если не активно')),
            (new Field\Toggle('is_visible_index', $this->_('Имеет главную страницу')))->setDescription($this->_('Будет ли кликабельным в меню название модуля. <br>Следует отключить если модуль имеет только отдельные разделы')),
        ]);


        $form->addControls([
            $form->getBtnSubmit()
        ]);

        return $form->toArray();
    }


    /**
     * Форма редактирования модуля
     * @param string   $base_url
     * @param Row      $module
     * @param Row|null $section
     * @return array
     */
    public function getFormSection(string $base_url, Row $module, Row $section = null): array {

        $form = new Form('admin', 'modules', 'module_section');
        $form->setTable($this->modAdmin->tableModulesSections, $section?->id);
        $form->setHandler("{$this->base_url}/{$module->id}/sections/" . ((int)$section?->id), 'put');
        $form->setSuccessLoadUrl("#/{$this->base_url}/{$module->id}/sections");
        $form->setTitle($section ? $this->_('Изменение раздела') : $this->_('Добавление раздела'));
        $form->setSuccessNotice();
        $form->setWidthLabel(160);


        $form->setRecord([
            'title'     => $section?->title,
            'name'      => $section?->name,
            'is_active' => (string)$section?->is_active,
        ]);

        $form->addFields([
            (new Field\Text('title',       $this->_('Название')))->setWidth(350)->setRequired(true),
            (new Field\Text('name',        $this->_('Идентификатор')))->setNoSend(true)->setRequired(true),
            (new Field\Toggle('is_active', $this->_('Активен')))->setValueY(1)->setValueN(0),
        ]);

        $form->addControls([
            $form->getBtnSubmit(),
            (new Control\Link('Отмена', "#/{$base_url}/{$module->id}/sections"))->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
    }


    /**
     * @param bool $is_edit
     * @return array
     */
    public function getFormRepo(bool $is_edit = false): array {

        $form = new Form('admin', 'modules', 'repo');
        $form->setHandler("{$this->base_url}/repo", 'put');
        $form->setSuccessLoadUrl("#/{$this->base_url}/repo");
        $form->setSuccessNotice();
        $form->setWidthLabel(210);
        $form->setValidResponseType(['text']);

        $setting_repo              = $this->modAdmin->tableSettings->getRowByCodeModule('modules_repo', 'admin');
        $setting_date_update_check = $this->modAdmin->tableSettings->getRowByCodeModule('modules_date_update_check', 'admin');

        $form->setRecord([
            'date_update_check' => $setting_date_update_check?->value ?: '-',
            'repo'              => $setting_repo?->value,
        ]);

        $descr = $this->_('Каждый репозиторий с новой строки');

        $form->addFields([
            (new Field\Datetime('date_update_check', $this->_('Дата последнего обновления')))->setReadonly(true)->setNoSend(true),
            (new Field\Textarea('repo',              $this->_('Репозитории')))->setWidth(450)->setHeight(80)->setDescription($descr),
        ]);


        if (empty($setting_repo) || $is_edit) {
            $form->addControls([
                $form->getBtnSubmit(),
            ]);

            if ($setting_repo) {
                $form->addControls([
                    $form->getBtnCancel("#/{$this->base_url}/repo")
                ]);
            }

        } else {
            $form->setReadonly(true);
            $form->addControls([
                (new Control\Button('<i class="bi bi-cloud-arrow-down"></i> ' . $this->_('Получить обновления')))->setOnClick('adminModules.upgradeRepo()'),
                (new Control\Link($this->_('Редактировать')))->setUrl("#/{$this->base_url}/repo?edit=1"),
            ]);
        }


        return $form->toArray();
    }


    /**
     * Форма добавления версии модуля
     * @param Row $module
     * @return array
     */
    public function getFormVersionsFile(Row $module): array {

        $form = new Form('admin', 'modules', 'version');
        $form->setHandler('saveFile');
        $form->setSuccessLoadUrl("#/{$this->base_url}");
        $form->setSuccessNotice();
        $form->setWidthLabel(190);
        $form->setTitle($this->_('Добавление версии'));

        $form->setRecord([
            'file'      => null,
            'is_active' => 1,
        ]);

        $form->addFields([
            (new Field\FileUpload('file',  $this->_('Файл модуля (.zip)')))->setAcceptZip()->setFilesLimit(1)->setUrl("/{$this->base_url}/versions/upload")->setRequired(),
            (new Field\Toggle('is_active', $this->_('Активен'))),
        ]);

        $form->addControls([
            $form->getBtnSubmit(),
            (new Control\Link('Отмена'))->setUrl("#/{$this->base_url}/{$module->id}/versions")->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
    }


    /**
     * @param int $available_module_id
     * @return array
     */
    public function getPanelAvailModule(int $available_module_id): array {

        return [];
    }


    /**
     * @param string $description
     * @return string
     */
    public function getAvailableDescription(string $description): string {

        return $description;
    }
}