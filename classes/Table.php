<?php
namespace Core3\Classes;
use Core3\Exceptions\Exception;
use Core3\Sys\Auth;
use CoreUI\Table\Abstract;
use CoreUI\Table\Column;
use CoreUI\Table\Column\Toggle;
use CoreUI\Table\Control;


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

        if (empty($table_id)) {
            $table_id = crc32(uniqid());
        }

        parent::__construct($table_id);

        $this->module  = $module;
        $this->section = $section;
        $this->auth    = Registry::has('auth') ? Registry::get('auth') : null;
        $this->system  = new System();

        $this->setClass('table-hover table-striped');
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
                    $this->setHeaderOut($this::LAST)->left([
                        new Control\Search()
                    ]);
                    break;

                case 'columns':
                    $this->setHeaderOut($this::LAST)->left([
                        new Control\Columns()
                    ]);
                    break;

                case 'add':
                    $url = is_string($options) ? $options : null;
                    $this->setHeaderOut($this::LAST)->right([
                        $this->getBtnAdd($url)
                    ]);
                    break;

                case 'delete':
                    $handler = is_string($options) ? $options : 'delete';
                    $this->setHeaderOut($this::LAST)->right([
                        $this->getBtnDelete($handler)
                    ]);
                    break;
            }
        }

        return $this;
    }


    /**
     * Установка обработчика
     * @param string     $handler
     * @param string     $http_method
     * @param array|null $params
     * @return self
     */
    public function setHandler(string $handler, string $http_method = 'GET', array $params = null): self {

        $this->setRecordsRequest("core3/mod/{$this->module}/{$this->section}/handler/{$handler}", $http_method, $params);
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
     * @param string $handler
     * @return Control\Button|null
     */
    public function getBtnDelete(string $handler = 'delete'):? Control\Button {

        if ( ! $this->auth->isAllowed("{$this->module}_{$this->section}", 'delete')) {
            return null;
        }

        $url      = "core3/mod/{$this->module}/{$this->section}/handler/{$handler}";
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
    public function getColumnToggle(string $field, string $label, int $width, string $handler): Column\Toggle {

        $switch_url = "/core3/mod/{$this->module}/{$this->section}/handler/{$handler}?id=[id]";
        $table_id   = $this->getId();

        $column = new Column\Toggle($field, $label, $width);
        $column->setOnChange("Core.ui.table.get('{$table_id}').switch('{$switch_url}', checked, id)")
            ->setValueY(1)
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