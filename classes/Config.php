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
     * @return Config|string|null
     */
    public function __get(string $param_name): Config|string|null {

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

        $config = $this->getConfig($file_path, $section);

        if ($this->_data === null) {
            $this->addArray($config);

        } else {
            $this->merge($config);
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


    /**
     * Parses INI file adding extends functionality via ":base" postfix on namespace.
     * @param string      $filename
     * @param string|null $section
     * @return array
     * @throws \Exception
     */
    private function getConfig(string $filename, string $section = null): array {

        $config  = parse_ini_file($filename, true);
        $config  = $this->resolveNestedSections($config);

        foreach ($config as $namespace => $properties) {
            if (is_array($properties)) {
                // overwrite / set current namespace values
                foreach ($properties as $key => $val) {
                    $config[$namespace] = $this->processKey($config[$namespace], $key, $val);
                    unset($config[$namespace][$key]);
                }

            } else {
                if ( ! isset($config['global'])) {
                    $config['global'] = [];
                }
                $parsed_key       = $this->processKey([], $namespace, $properties);
                $config['global'] = $this->mergeDistinct($config['global'], $parsed_key);
            }
        }

        if ($section) {
            if (isset($config[$section])) {
                return $config[$section] ?: [];
            } else {
                throw new \Exception("Config section '{$section}' not found");
            }
        } else {
            if (count($config) === 1 && isset($config['global'])) {
                return $config['global'] ?: [];
            }

            return $config;
        }
    }


    /**
     * @param array       $config
     * @param string|null $section
     * @return array
     */
    private function resolveNestedSections(array $config, string $section = null): array {

        foreach ($config as $namespace => $section_content) {
            if (strpos($namespace, ':') !== false) {
                @list($name, $extends) = explode(':', $namespace);
                $name    = trim($name);
                $extends = trim((string)$extends);


                if ($extends) {
                    if ($section) {
                        if ($name == $section) {
                            $config[$namespace] = $section_content;

                            if (isset($config[$extends])) {
                                $config[$name] = array_merge($section_content, $config[$extends]);

                            } else {
                                $nested_section = $this->resolveNestedSections($config, $extends);
                                $config[$name]  = $nested_section[$extends] ?? [];
                            }
                        }

                    } else {
                        if (isset($config[$extends])) {
                            $config[$name] = array_merge($section_content, $config[$extends]);

                        } else {
                            $nested_section = $this->resolveNestedSections($config, $extends);
                            $config[$name]  = $nested_section[$extends] ?? [];
                        }
                    }

                    unset($config[$namespace]);

                } else {
                    if ($section) {
                        if ($namespace == $section) {
                            $config[$namespace] = $section_content;
                        }

                    } else {
                        $config[$name] = $section_content;
                    }
                }

            } else {
                if ($section) {
                    if ($namespace == $section) {
                        $config[$namespace] = $section_content;
                    }

                } else {
                    $config[$namespace] = $section_content;
                }
            }
        }

        return $config;
    }


    /**
     * mergeDistinct does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * mergeDistinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * mergeDistinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
    private function mergeDistinct(array &$array1, array &$array2): array {

        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->mergeDistinct($merged [$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }


    /**
     * Процесс разделения на субсекции ключей конфига
     * @param array $config
     * @param string $key
     * @param string $val
     * @return array
     */
    private function processKey(array $config, string $key, string $val): array {

        $nest_separator = '.';

        if (strpos($key, $nest_separator) !== false) {
            $pieces = explode($nest_separator, $key, 2);
            if (strlen($pieces[0]) && strlen($pieces[1])) {
                if ( ! isset($config[$pieces[0]])) {
                    if ($pieces[0] === '0' && ! empty($config)) {
                        // convert the current values in $config into an array
                        $config = array($pieces[0] => $config);
                    } else {
                        $config[$pieces[0]] = array();
                    }
                }
                $config[$pieces[0]] = $this->processKey($config[$pieces[0]], $pieces[1], $val);
            }
        } else {
            $config[$key] = $val;
        }
        return $config;
    }
}