<?php
namespace Core3\Classes;
use Core3\Classes\Db\Table;
use Core3\Classes\Db\TableAbstract;
use Core3\Classes\Db\TableFiles;
use Core3\Exceptions\Exception;
use Core3\Mod\Admin;
use Core3\Sys\Auth;
use CoreUI\Form\Control;
use CoreUI\Form\Control\Submit;
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
     * @param Table    $table
     * @param int|null $record_id
     * @return $this
     */
    public function setTable(Db\Table $table, int $record_id = null): self {

        if ($record_id) {
            $admin   = new Admin\Controller();
            $control = $admin->tableControls->createRow($table->getTable(), $record_id);

            $this->send_props['record_version'] = $control->version;
        }

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
     * @param TableFiles    $table
     * @param int           $id
     * @param string|null   $object_type
     * @param \Closure|null $callback
     * @return array
     */
    public function getFiles(Db\TableFiles $table, int $id, string $object_type = null, \Closure $callback = null): array {

        $fields = $table->getFields();

        $files_row = $table->select(function (Select $select) use ($id, $object_type, $fields) {
            $select->where([ $fields['ref_id'] => $id, ]);

            if ($object_type) {
                $select->where([$fields['object_type'] => $object_type,]);
            }
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
     * Получение кнопки для отмены
     * @param string      $url
     * @param string|null $content
     * @return Control\Link|null
     */
    public function getBtnCancel(string $url, string $content = null):? Control\Link {

        return (new Control\Link($content ?: $this->system->_('Отмена'), $url))
            ->setAttr('class', 'btn btn-secondary');
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
            if ($send['record_version']) {
                $amp = strpos($send['handler'], '?') === false ? '?' : '&';
                $this->setSend("{$send['handler']}{$amp}v={$send['record_version']}", $send['http_method'], $this::DATA_JSON);

            } else {
                $this->setSend($send['handler'], $send['http_method'], $this::DATA_JSON);
            }
        }
    }
}