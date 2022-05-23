<?php
namespace Core3\Classes;

/**
 *
 */
class Config {

    /**
     * @var \Zend_Config|null
     */
    private $_data = null;


    /**
     * @param string $param_name
     * @return void
     */
    public function __get(string $param_name) {

        if ($this->_data === null || ! isset($this->_data->{$param_name})) {
            return null;
        }


        $config_array  = $this->_data->{$param_name}->toArray();
        $config_object = json_decode(json_encode($config_array));

        if (isset($config_object->{$param_name})) {
            return $config_object->{$param_name};
        }

        return null;
    }


    /**
     * @param array $settings
     * @return void
     */
    public function addArray(array $settings): void {

        if ($this->_data === null) {
            $this->_data = new \Zend_Config($settings, true);
        } else {
            $config = new \Zend_Config($settings, true);
            $this->_data->merge($config);
        }
    }


    /**
     * @param string      $file_path
     * @param string|null $section
     * @return void
     * @throws \Exception
     */
    public function addFileIni(string $file_path, string $section = null): void {

        if ( ! file_exists($file_path)) {
            throw new \Exception("File not found: {$file_path}");
        }

        if ($this->_data === null) {
            $this->_data = new \Zend_Config_Ini($file_path, $section);
        } else {
            $config_ini = new \Zend_Config_Ini($file_path, $section);
            $this->_data->merge($config_ini);
        }
    }


    /**
     * @return array
     */
    public function toArray(): array {

        // TODO вернуть массив
    }


    /**
     * @return void
     */
    public function setReadOnly(): void {

        if ($this->_data !== null) {
            $this->_data->setReadOnly();
        }
    }
}