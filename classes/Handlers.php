<?php
namespace Core3;
use CoreUI\Form;


require_once 'Common.php';


/**
 * Class Handler
 * @package Core
 */
class Handlers extends Common {

    private $form_handler;
    private $coreui_handlers;


    /**
     * @param  string $name
     * @return mixed|null
     */
    protected function getSessData($name) {

        if ( ! $this->form_handler) {
            $this->form_handler = new Form\Handler();
        }
        return $this->form_handler->getSessData($name);
    }


    /**
     * @return mixed
     */
    protected function getRecordId() {

        if ( ! $this->form_handler) {
            $this->form_handler = new Form\Handler();
        }
        return $this->form_handler->getRecordId();
    }


    /**
     * @return string
     */
    protected function getResponse() {

        if ( ! $this->coreui_handlers) {
            $this->coreui_handlers = new \CoreUI\Handlers();
        }
        return $this->coreui_handlers->getResponse();
    }


    /**
     * @param array $data
     * @param array $fields
     * @return bool
     */
    protected function validateControls($data, $fields = array()) {

        if ( ! $this->form_handler) {
            $this->form_handler = new Form\Handler();
        }

        $is_valid = $this->form_handler->validateControls($data, $fields);

        return $is_valid;
    }


    /**
     * Установка ошибки
     * @param string $message
     * @param string $field
     */
    protected function addError($message, $field = '') {

        if ( ! $this->form_handler) {
            $this->form_handler = new Form\Handler();
        }
        $this->form_handler->addError(['field' => $field, 'message' => $message]);
    }


    /**
     * Проверка наличия ошибок
     * @return bool
     */
    protected function isError() {

        if ( ! $this->form_handler) {
            $this->form_handler = new Form\Handler();
        }
        return $this->form_handler->isError();
    }


    /**
     * @param array $data
     * @return int
     */
    protected function saveData($data) {

        if ( ! $this->form_handler) {
            $this->form_handler = new Form\Handler();
        }
        return $this->form_handler->saveData($data);
    }


    /**
     * @param array $data
     * @return array
     */
    protected function filterControls($data) {

        if ( ! $this->form_handler) {
            $this->form_handler = new Form\Handler();
        }
        return $this->form_handler->filterControls($data);
    }


    /**
     *
     */
    protected function uploadFile() {

        if ( ! $this->form_handler) {
            $this->form_handler = new Form\Handler();
        }
        $this->form_handler->uploadFile();
    }
}