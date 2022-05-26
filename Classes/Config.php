<?php
namespace Core3\Classes;

/**
 *
 */
class Config {

    /**
     * @var array
     */
    private array $_data               = [];
    private bool  $_allowModifications = true;


    /**
     * @param string $param_name
     * @return void
     */
    public function __get(string $param_name) {

        $result = null;

        if (array_key_exists($param_name, $this->_data)) {
            $result = $this->_data[$param_name];
        }

        return $result;
    }


    /**
     * Only allow setting of a property if $allowModifications
     * was set to true on construction. Otherwise, throw an exception.
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws \Exception
     * @return void
     */
    public function __set(string $name, mixed $value) {

        if ($this->_allowModifications) {
            if (is_array($value)) {
                $config = new self();
                $config->addArray($value);

                $this->_data[$name] = $config;

            } elseif ($value instanceof Config) {
                $this->_data[$name] = $value;

            } elseif (is_scalar($value)) {
                $this->_data[$name] = $value;
            }

        } else {
            throw new \Exception('Config is read only');
        }
    }


    /**
     * Support isset() overloading on PHP 5.1
     * @param string $name
     * @return boolean
     */
    public function __isset(string $name): bool {
        return isset($this->_data[$name]);
    }


    /**
     * @param array $settings
     * @return Config
     */
    public function addArray(array $settings): self {

        if ($this->_data === null) {
            foreach ($settings as $key => $value) {
                if (is_array($value)) {
                    $config = new self();
                    $config->addArray($value);

                    $this->_data[$key] = $config;

                } else {
                    $this->_data[$key] = $value;
                }
            }

        } else {
            $this->merge($settings);
        }

        return $this;
    }


    /**
     * @param string      $file_path
     * @param string|null $section
     * @return Config
     * @throws \Exception
     */
    public function addFileIni(string $file_path, string $section = null): self {

        if ( ! file_exists($file_path)) {
            throw new \Exception("File not found: {$file_path}");
        }

        $config_ini = new \Zend_Config_Ini($file_path, $section);

        if ($this->_data === null) {
            $this->addArray($config_ini->toArray());

        } else {
            $this->merge($config_ini->toArray());
        }

        return $this;
    }


    /**
     * @return array
     */
    public function toArray(): array {

        $array = [];

        foreach ($this->_data as $key => $value) {
            if ($value instanceof Config) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }


    /**
     * @return void
     */
    public function setReadOnly(): void {

        $this->_allowModifications = false;

        if ($this->_data !== null) {
            foreach ($this->_data as $value) {
                if ($value instanceof Config) {
                    $value->setReadOnly();
                }
            }
        }
    }


    /**
     * Merge another Zend_Config with this one. The items
     * in $merge will override the same named items in
     * the current config.
     * @param array $config
     * @return void
     */
    private function merge(array $config): void {

        foreach ($config as $key => $item) {
            if (array_key_exists($key, $this->_data)) {
                if (is_array($item)) {

                    if ($this->$key instanceof Config) {
                        $this->$key->merge($item);

                    } else {
                        $config_new = new self();
                        $config_new->addArray($item);

                        if ( ! $this->_allowModifications) {
                            $config_new->setReadOnly();
                        }

                        $this->$key = $config_new;
                    }


                } elseif (is_scalar($item)) {
                    $this->$key = $item;
                }

            } else {
                if (is_array($item)) {
                    $config_new = new self();
                    $config_new->addArray($item);

                    if ( ! $this->_allowModifications) {
                        $config_new->setReadOnly();
                    }

                    $this->$key = $config_new;

                } elseif (is_scalar($item)) {
                    $this->$key = $item;
                }
            }
        }
    }
}