<?php
namespace Core3\Classes;
use Core3\Exceptions\Exception;
use Core3\Mod\Admin;
use CoreUI\Form\Control;
use CoreUI\Form\Field;
use Laminas\Db\Sql\Select;


/**
 *
 */
class Form extends \CoreUI\Form {

    private string $module;
    private string $section;
    private ?Auth  $auth;
    private System $system;

    private array $send_props = [
        'record_id'      => null,
        'record_version' => null,
        'handler'        => null,
        'http_method'    => 'post',
    ];


    /**
     * @param string      $module
     * @param string      $section
     * @param string|null $form_id
     */
    public function __construct(string $module, string $section, string $form_id = null) {

        parent::__construct($form_id);

        $this->module  = $module;
        $this->section = $section;
        $this->auth    = Registry::has('auth') ? Registry::get('auth') : null;
        $this->system  = new System();


        $this->setValidate(true);
        $this->setWidthLabel(225);
        $this->setValidResponseHeaders([
            'Content-Type' => [ 'application/json', 'application/json; charset=utf-8' ]
        ]);
        $this->setValidResponseType([ 'json' ]);


        if ( ! $this->auth->isAllowed("{$this->module}_{$this->section}", $this->auth::PRIVILEGE_EDIT)) {
            $this->setReadonly(true);
        }
    }


    /**
     * Установка
     * @param Db\Table $table
     * @param int      $record_id
     * @return $this
     */
    public function setTable(Db\Table $table, int $record_id): self {

        $admin = new Admin\Controller();
        $control = $admin->tableControls->createRow($table->getTable(), $record_id);

        $this->send_props['record_id']      = $record_id;
        $this->send_props['record_version'] = $control->version;

        $this->fillSend();
        return $this;
    }


    /**
     * Установка обработчика
     * @param string $handler
     * @param string $http_method
     * @return self
     */
    public function setHandler(string $handler, string $http_method = 'post'): self {

        $this->send_props['handler']     = $handler;
        $this->send_props['http_method'] = $http_method;

        $this->fillSend();
        return $this;
    }


    /**
     * Получение файлов для поля
     * @param Db\Table $table
     * @param string   $field_name
     * @param int      $id
     * @return array
     * @throws Exception
     */
    public function getFiles(Db\Table $table, string $field_name, int $id): array {

        $table_name = $table->getTable();

        if ( ! str_ends_with($table_name, '_files')) {
            throw new Exception('Incorrect table');
        }


        $files_row = $table->select(function (Select $select) use ($id, $field_name) {
            $select->where([
                'ref_id'     => $id,
                'field_name' => $field_name,
            ]);
        });

        $result = [];

        foreach ($files_row as $file_row) {
            $file = [
                'id'          => $file_row->id,
                'name'        => $file_row->file_name,
                'size'        => $file_row->file_size,
                'urlDownload' => "/core3/mod/{$this->module}/{$this->section}/handler/getFileDownload?t={$table_name}&id={$file_row->id}",
            ];

            if ($file_row->file_type && preg_match('~^image/.*~', $file_row->file_type)) {
                $file['urlPreview'] = "/core3/mod/{$this->module}/{$this->section}/handler/getFilePreview?t={$table_name}&id={$file_row->id}";
            }

            $result[] = $file;
        }

        return $result;
    }


    /**
     * Получение кнопки для сохранения
     * @param string|null $content
     * @return Control\Submit|null
     */
    public function getBtnSubmit(string $content = null):? Control\Submit {

        $button = null;

        if ($this->auth->isAllowed("{$this->module}_{$this->section}", $this->auth::PRIVILEGE_EDIT)) {
            $content = is_string($content)
                ? $content
                : $this->system->_('Сохранить');

            $button = new Control\Submit($content);
        }

        return $button;
    }


    /**
     * Добавление полей
     * @param array       $fields
     * @param string|null $position
     * @return self
     */
    public function addFields(array $fields, string $position = null): self {

        foreach ($fields as $field) {
            if ($field instanceof Field\FileUpload) {
                if (empty($field->getUrl())) {
                    $field->setUrl("/core3/mod/{$this->module}/{$this->section}/handler/uploadFile");
                }
            }
        }

        parent::addFields($fields, $position);

        return $this;
    }


    /**
     * Заполнение адреса для отправки формы
     * @return void
     */
    private function fillSend(): void {

        $send = $this->send_props;

        if ($send['handler']) {
            $base_url = "/core3/mod/{$this->module}/{$this->section}/handler/{$send['handler']}";

            if ($send['record_id'] && $send['record_version']) {
                $this->setSend("{$base_url}?id={$send['record_id']}&v={$send['record_version']}", $send['http_method']);
            } else {
                $this->setSend($base_url, $send['http_method']);
            }
        }
    }
}