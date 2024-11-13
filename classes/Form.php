<?php
namespace Core3\Classes;
use Core3\Classes\Db\TableAbstract;
use Core3\Exceptions\Exception;
use Core3\Mod\Admin;
use Core3\Sys\Auth;
use CoreUI\Form\Control;
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

        $form_id = $form_id
            ? "{$module}_{$section}_{$form_id}"
            : "{$module}_{$section}";

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
     * @param TableAbstract $table
     * @param string        $object_type
     * @param int           $id
     * @param \Closure|null $callback
     * @return array
     * @throws Exception
     */
    public function getFiles(Db\TableAbstract $table, string $object_type, int $id, \Closure $callback = null): array {

        $table_name = $table->getTable();

        if ( ! str_ends_with($table_name, '_files')) {
            throw new Exception('Incorrect table');
        }


        $files_row = $table->select(function (Select $select) use ($id, $object_type) {
            $select->where([
                'ref_id'      => $id,
                'object_type' => $object_type,
            ]);
        });

        $files = [];

        foreach ($files_row as $file_row) {
            $file = [
                'id'          => $file_row->id,
                'name'        => $file_row->name,
                'size'        => $file_row->size,
                'urlPreview'  => "",
                'urlDownload' => "",
            ];

            if ($callback) {
                $file = $callback($file);
            }

            $files[] = $file;
        }

        return $files;
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
     * Установка сообщения по умолчанию
     * @param string|null $notice
     * @return void
     */
    public function setSuccessNotice(string $notice = null): void {

        $notice = $notice ?: $this->system->_('Сохранено');

        $this->setOnSubmitSuccess("CoreUI.notice.default('{$notice}')");
    }


    /**
     * Заполнение адреса для отправки формы
     * @return void
     */
    private function fillSend(): void {

        $send = $this->send_props;

        if ($send['handler']) {
            if ($send['record_id'] && $send['record_version']) {
                $amp = strpos($send['handler'], '?') === false ? '?' : '&';
                $this->setSend("{$send['handler']}{$amp}v={$send['record_version']}", $send['http_method']);

            } else {
                $this->setSend($send['handler'], $send['http_method']);
            }
        }
    }
}