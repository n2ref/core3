<?php
namespace Core3\Mod\Admin\Classes\Settings;
use Core3\Classes\Tools;
use Core3\Exceptions\Exception;
use Core3\Classes\Common;
use Core3\Classes\Table;
use Core3\Classes\Form;
use Core3\Classes\Db\Row;
use CoreUI\Table\Filter;
use CoreUI\Table\Search;
use CoreUI\Table\Column;
use CoreUI\Table\Control as TableControl;
use CoreUI\Form\Field;
use CoreUI\Form\Control;


/**
 *
 */
class View extends Common {

    private string $base_url = "admin/settings";

    /**
     * Таблица с настройками
     * @return array
     * @throws Exception
     * @throws \Exception
     */
    public function getTable(): array {

        $table = new Table('admin', 'settings');
        $table->setShowScrollShadow(true);
        $table->setSaveState(true);
        $table->setOverflow(true);
        $table->setClickUrl("#/{$this->base_url}/[id]");
        $table->setGroupBy("module_title");

        $table->addControlSearch();
        $table->addControlColumns();
        $table->addControlBtnAdd("#/{$this->base_url}/0");
        $table->addControlBtnDelete($this->base_url);

        $table->setFooterOut($table::FIRST)->left([
            new TableControl\Total,
        ]);

        $modules = $this->modAdmin->tableModules->fetchPairs('name', 'title');

        $table->setHeaderOut($table::LAST)
            ->left([
                (new Filter\Text('title'))->setAttributes(['placeholder' => $this->_('Название')])->setWidth(200)->setAutoSearch(true),
                (new Filter\Select('module', $this->_('Модули')))->setWidth(200)->setOptions($modules),
                (new TableControl\FilterClear()),
            ]);

        $table->addSearch([
            (new Search\Text('title',            $this->_('Название'))),
            (new Search\Text('code',             $this->_('Код'))),
            (new Search\CheckboxBtn('is_active', $this->_('Активность')))->setOptions(['1' => $this->_('Да'), '0' => $this->_('Нет')]),
        ]);

        $table->addColumns([
            (new Column\Select()),
            $table->getColumnToggle('is_active',  $this->_('Активность'),             45),
            (new Column\Link('title',             $this->_('Название')))->setMinWidth(150),
            (new Column\Text('code',              $this->_('Код'),                    100))->setMinWidth(180)->setNoWrap(true)->setShow(false),
            (new Column\Text('value',             $this->_('Значение')))->setMinWidth(180)->setNoWrap(true),
            (new Column\Text('note',              $this->_('Описание')))->setMinWidth(180)->setNoWrap(true),
            (new Column\Datetime('date_modify',   $this->_('Дата изменения'),         155))->setMinWidth(155),
            (new Column\Text('author_modify',     $this->_('Автор изменения'),        155))->setMinWidth(155)->setShow(false),
        ]);


        $settings = $this->modAdmin->tableSettings->fetchAll()->toArray();
        $settings = Tools::arrayMultisort($settings, [
            'module' => 'asc',
            'title'  => 'asc',
        ]);

        $table->setRecords($settings);

        foreach ($table->getRecords() as $record) {

            $record->title = [
                'content' => $record->title,
                'url'     => "#/{$this->base_url}/{$record->id}",
                'attr'    => ['class' => 'fw-medium']
            ];

            if ($record->module) {
                $record->module_title = $modules[$record->module] ?? $record->module;
            } else {
                $record->module_title = '--';
            }
        }

        return $table->toArray();
    }


    /**
     * Редактирование пользователя
     * @param string $base_url
     * @param Row    $setting
     * @return array
     */
    public function getForm(string $base_url, Row $setting): array {

        $form = new Form('admin', 'settings');
        $form->setTable($this->modAdmin->tableSettings, $setting->id);
        $form->setHandler("admin/settings/{$setting->id}", 'put');
        $form->setSuccessLoadUrl('#/admin/settings');
        $form->setSuccessNotice();
        $form->setWidthLabel(150);

        $form->setRecord([
            'title'     => $setting->title,
            'value'     => $setting->value,
            'note'      => $setting->note,
            'is_active' => $setting->is_active,
        ]);


        switch ($setting->field_type) {
            case 'datetime': $value_type = (new Field\Datetime('value', $this->_('Значение'))); break;
            case 'date':     $value_type = (new Field\Date('value', $this->_('Значение'))); break;
            case 'number':   $value_type = (new Field\Number('value', $this->_('Значение')))->setWidth(300); break;
            case 'email':    $value_type = (new Field\Email('value', $this->_('Значение')))->setWidth(300)->setInvalidText($this->_('Обязательное поле. Только email')); break;
            case 'textarea': $value_type = (new Field\Textarea('value', $this->_('Значение')))->setWidth(300)->setHeight(50); break;
            case 'text':
            default:         $value_type = (new Field\Text('value', $this->_('Значение')))->setWidth(300); break;
        }

        $form->addFields([
            (new Field\Text('title',             $this->_('Название')))->setWidth(300)->setRequired(true),
            $value_type,
            (new Field\Textarea('note',          $this->_('Описание')))->setWidth(300)->setHeight(50),
            (new Field\Toggle('is_active',       $this->_('Активно'))),
        ]);

        $form->addControls([
            $form->getBtnSubmit(),
            (new Control\Link('Отмена'))->setUrl($base_url)->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
    }


    /**
     * Редактирование пользователя
     * @param string $base_url
     * @return array
     */
    public function getFormNew(string $base_url): array {

        $form = new Form('admin', 'settings');
        $form->setTable($this->modAdmin->tableSettings, 0);
        $form->setHandler("admin/settings/0", 'post');
        $form->setSuccessLoadUrl('#/admin/settings');
        $form->setSuccessNotice();
        $form->setWidthLabel(150);

        $form->setRecord([
            'title'      => '',
            'value'      => '',
            'code'       => '',
            'field_type' => '',
            'module'     => '',
            'note'       => '',
            'is_active'  => 1,
        ]);


        $modules = $this->modAdmin->tableModules->fetchPairs('name', 'title');
        $types   = [
            'text'     => $this->_('Текстовое'),
            'textarea' => $this->_('Текстовое расширенное'),
            'number'   => $this->_('Цифровое'),
            'date'     => $this->_('Дата'),
            'datetime' => $this->_('Дата и время'),
            'email'    => $this->_('Email'),
        ];

        $code_invalid_text = $this->_('Обязательное поле. Только латинские символы, цифры и "_"');
        $descr_no_change   = $this->_('Не изменяется в дальнейшем');
        $module_halp       = $this->_('Указываться в качестве пространства имен. Дает возможность создавать одинаковые коды настроек для разных модулей');
        $type_help         = $this->_('После сохранения, поле Значение будет адаптировано под указанный тип');

        $form->addFields([
            (new Field\Text('title',        $this->_('Название')))->setWidth(300)->setRequired(true),
            (new Field\Text('field_type',   $this->_('Значение')))->setWidth(300),
            (new Field\Text('code',         $this->_('Код')))->setWidth(300)->setDescription($descr_no_change)->setValidPattern('\\w+')->setInvalidText($code_invalid_text)->setRequired(true),
            (new Field\Select('field_type', $this->_('Тип значения')))->setWidth(300)->setDescription($descr_no_change)->setOptions($types)->setRequired(true)->setHelp($type_help),
            (new Field\Select('module',     $this->_('Модуль')))->setWidth(300)->setDescription($descr_no_change)->setHelp($module_halp)->setOptions(['' => '--'] + $modules),
            (new Field\Textarea('note',     $this->_('Описание')))->setWidth(300)->setHeight(50),
            (new Field\Toggle('is_active',  $this->_('Активно'))),
        ]);

        $form->addControls([
            $form->getBtnSubmit(),
            (new Control\Link('Отмена'))->setUrl($base_url)->setAttr('class', 'btn btn-secondary'),
        ]);

        return $form->toArray();
    }
}