<?php
namespace Core3\Classes;
use Core3\Exceptions\Exception;
use Core3\Sys\Auth;
use CoreUI\Table\Abstract;
use CoreUI\Table\Column;
use CoreUI\Table\Column\Toggle;
use CoreUI\Table\Control;
use CoreUI\Table\Control\Button;


/**
 *
 */
class Table extends \CoreUI\Table {

    private string $module;
    private string $section;
    private ?Auth  $auth;
    private System $system;

    /**
     * @param string      $module
     * @param string      $section
     * @param string|null $table_id
     */
    public function __construct(string $module, string $section, string $table_id = null) {

        $table_id = $table_id
            ? "{$module}_{$section}_{$table_id}"
            : "{$module}_{$section}";

        parent::__construct($table_id);

        $this->module  = $module;
        $this->section = $section;
        $this->auth    = Registry::has('auth') ? Registry::get('auth') : null;
        $this->system  = new System();

        $this->setClass('table-hover table-striped');
        $this->setTheme('compact');
    }


    /**
     * Установка наборов настроек
     * @param array $kit
     * @return self
     * @throws Exception
     */
    public function setKit(array $kit): self {

        foreach ($kit as $kit_index => $kit_val) {

            $name    = is_numeric($kit_index) ? $kit_val : $kit_index;
            $options = is_numeric($kit_index) ? null : $kit_val;

            switch ($name) {
                case 'default':
                    $this->setSearchLabelWidth(180);
                    $this->setMaxHeight(800);
                    $this->setShowScrollShadow(true);
                    $this->setSaveState(true);

                    $this->setFooterOut($this::FIRST)->left([
                            new Control\Total,
                            new Control\Pages(),
                        ])
                        ->right([
                            new Control\PageSize([ 25, 50, 100, 1000 ])
                        ]);

                    break;

                case 'search':
                    $this->setHeaderIn($this::LAST)->left([
                        (new Control\Search())
                            ->setButton('<i class="bi bi-search"></i> ' . $this->system->_('Поиск'), ['class' => 'btn'])
                            ->setButtonClear('<i class="bi bi-x bi-x-lg text-danger"></i>', ['class' => 'btn'])
                    ]);
                    break;

                case 'columns':
                    $this->setHeaderIn($this::LAST)->left([
                        (new Control\Columns())->setButton('<i class="bi bi-layout-three-columns"></i> ' . $this->system->_('Колонки'), ['class' => 'btn'])
                    ]);
                    break;

                case 'add':
                    $url = is_string($options) ? $options : null;
                    $this->setHeaderIn($this::LAST)->right([
                        $this->getBtnAdd($url)
                    ]);
                    break;

                case 'delete':
                    $handler = is_string($options) ? $options : '';
                    $this->setHeaderIn($this::LAST)->right([
                        $this->getBtnDelete($handler)
                    ]);
                    break;
            }
        }

        return $this;
    }


    /**
     * @return $this
     */
    public function addControlsDefault(): self {

        $this->setSearchLabelWidth(180);
        $this->setMaxHeight(800);
        $this->setSaveState(true);

        $this->setFooterOut($this::FIRST)
            ->left([
                new Control\Total,
                new Control\Pages(),
            ])
            ->right([
                new Control\PageSize([ 25, 50, 100, 1000 ])
            ]);

        return $this;
    }


    /**
     * @return Control\Search
     */
    public function getControlSearch(): Control\Search {

        return (new Control\Search())
            ->setButton('<i class="bi bi-search"></i> ' . $this->system->_('Поиск'), ['class' => 'btn'])
            ->setButtonClear('<i class="bi bi-x bi-x-lg text-danger"></i>', ['class' => 'btn']);
    }


    /**
     * @return Control\Columns
     */
    public function getControlColumns(): Control\Columns {

        return (new Control\Columns())
            ->setButton('<i class="bi bi-layout-three-columns"></i> ' . $this->system->_('Колонки'), ['class' => 'btn']);
    }


    /**
     * @param string $url
     * @param string $position
     * @return $this
     */
    public function addControlBtnAdd(string $url = '', string $position = 'out'): self {

        $position = $position == 'in'
            ? $this->setHeaderIn($this::LAST)
            : $this->setHeaderOut($this::LAST);

        $position->right([
            $this->getBtnAdd($url)
        ]);

        return $this;
    }


    /**
     * @param string $handler
     * @param string $position
     * @return $this
     */
    public function addControlBtnDelete(string $handler = '', string $position = 'out'): self {

        $position = $position == 'in'
            ? $this->setHeaderIn($this::LAST)
            : $this->setHeaderOut($this::LAST);

        $position->right([
            $this->getBtnDelete($handler)
        ]);

        return $this;
    }


    /**
     * Получение кнопки добавления
     * @param string $url
     * @return Control\Link|null
     */
    public function getBtnAdd(string $url):? Control\Link {

        if ( ! $this->auth->isAllowed("{$this->module}_{$this->section}", 'edit')) {
            return null;
        }

        return (new Control\Link('<i class="bi bi-plus"></i> ' . $this->system->_('Добавить'), $url))
            ->setAttr('class', 'btn btn-success');
    }


    /**
     * Получение кнопки удаления
     * @param string $url
     * @return Button|null
     */
    public function getBtnDelete(string $url):? Control\Button {

        if ( ! $this->auth->isAllowed("{$this->module}_{$this->section}", 'delete')) {
            return null;
        }

        $table_id = $this->getId();

        return (new Control\Button('<i class="bi bi-trash"></i> ' . $this->system->_('Удалить')))
            ->setOnClick("Core.ui.table.get('{$table_id}').deleteSelected('{$url}', Core.menu.reload)")
            ->setAttr('class', 'btn btn-warning');
    }


    /**
     * @param string $field
     * @param string $label
     * @param int    $width
     * @param string $handler
     * @return Toggle
     */
    public function getColumnToggle(string $field, string $label, int $width, string $handler = '/[id]'): Column\Toggle {

        $switch_url = "{$this->module}/{$this->section}{$handler}";
        $table_id   = $this->getId();

        $column = new Column\Toggle($field, $label, $width);
        $column->setOnChange("Core.ui.table.get('{$table_id}').switch('{$switch_url}', input, record)")
            ->setValueY(1)
            ->setValueN(0)
            ->setShowLabel(false);

        return $column;
    }


    /**
     * Добавление колонок
     * @param array $columns
     * @return Table
     */
    public function addColumns(array $columns): self {

        foreach ($columns as $column) {
            if ($column instanceof Abstract\Column) {
                if (is_null($column->getSort())) {
                    $column->setSort(true);
                }
            }
        }

        parent::addColumns($columns);

        return $this;
    }
}