<?php
namespace Core3\Classes;

/**
 *
 */
class Validator extends System {

    private array $fields = [];


    /**
     * @param array|null $fields
     */
    public function __construct(array $fields = null) {

        if ($fields) {
            foreach ($fields as $field => $rule) {
                if (is_array($rule)) {
                    $rule_string  = null;
                    $title_string = null;

                    if (isset($rule[0]) && is_string($rule[0])) {
                        $rule_string = $rule[0];

                    } elseif (isset($rule['rule']) && is_string($rule['rule'])) {
                        $rule_string = $rule['rule'];
                    }

                    if ( ! $rule_string) {
                        continue;
                    }


                    if (isset($rule[1]) && is_string($rule[1])) {
                        $title_string = $rule[1];

                    } elseif (isset($rule['title']) && is_string($rule['title'])) {
                        $title_string = $rule['title'];
                    }

                    $this->setField($field, $rule_string, $title_string);

                } elseif (is_string($rule)) {
                    $this->setField($field, $rule);
                }
            }
        }
    }


    /**
     * @param string      $field
     * @param string      $rule
     * @param string|null $title
     * @return void
     */
    public function setField(string $field, string $rule, string $title = null): void {

        $rule = trim($rule);

        if (empty($rule)) {
            return;
        }

        $this->fields[$field] = [
            'rule'  => $rule,
            'title' => $title ?: null,
        ];
    }


    /**
     * @param string $field
     * @return void
     */
    public function deleteField(string $field): void {

        if (isset($this->fields[$field])) {
            unset($this->fields[$field]);
        }
    }


    /**
     * @param array $fields
     * @return void
     */
    public function deleteFields(array $fields): void {

        foreach ($fields as $field) {
            if (is_string($field)) {
                $this->deleteField($field);
            }
        }
    }


    /**
     * @return void
     */
    public function clearFields(): void {

        $this->fields = [];
    }


    /**
     * @param array $data
     * @param bool  $strict
     * @return array
     */
    public function validate(array $data, bool $strict = true): array {

        return $this->validateFields($this->fields, $data, $strict);
    }


    /**
     * @param string $method_name
     * @return bool
     */
    public static function isRequestMethod(string $method_name): bool {

        return $_SERVER['REQUEST_METHOD'] !== strtoupper($method_name);
    }


    /**
     * @param string $type_name
     * @return bool
     */
    public static function isContentType(string $type_name): bool {

        return empty($_SERVER['CONTENT_TYPE']) || strpos($_SERVER['CONTENT_TYPE'], $type_name) !== 0;
    }


    /**
     * @param mixed $value
     * @return bool
     */
    public static function isRequirement(mixed $value): bool {

        return isset($value);
    }


    /**
     * @param mixed $value
     * @param array $options
     * @return bool
     */
    public static function isString(mixed $value, array $options = []): bool {

        return is_string($value);
    }


    /**
     * @param string $value
     * @param int    $length_min
     * @return bool
     */
    public static function isStringMin(string $value, int $length_min): bool {

        return mb_strlen($value) < $length_min;
    }


    /**
     * @param string $value
     * @param int    $length_max
     * @return bool
     */
    public static function isStringMax(string $value, int $length_max): bool {

        return mb_strlen($value) > $length_max;
    }


    /**
     * @param string $value
     * @param array  $enum_value
     * @return bool
     */
    public static function isStringEnum(string $value, array $enum_value): bool {

        return in_array($value, $enum_value);
    }


    /**
     * @param mixed $value
     * @return bool
     */
    public static function isArray(mixed $value): bool {

        return is_array($value);
    }


    /**
     * @param mixed $value
     * @return bool
     */
    public static function isBool(mixed $value): bool {

        return is_bool($value);
    }


    /**
     * @param mixed $value
     * @return bool
     */
    public static function isInt(mixed $value): bool {

        return is_numeric($value) && filter_var($value, FILTER_VALIDATE_INT) !== false;
    }


    /**
     * @param int $value
     * @param int $min
     * @return bool
     */
    public static function isIntMin(int $value, int $min): bool {

        return $value < $min;
    }


    /**
     * @param int $value
     * @param int $max
     * @return bool
     */
    public static function isIntMax(int $value, int $max): bool {

        return $value > $max;
    }


    /**
     * @param mixed $value
     * @return bool
     */
    public static function isFloat(mixed $value): bool {

        return is_numeric($value) && filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }


    /**
     * @param float $value
     * @param int   $min
     * @return bool
     */
    public static function isFloatMin(float $value, int $min): bool {

        return $value < $min;
    }


    /**
     * @param float $value
     * @param int   $max
     * @return bool
     */
    public static function isFloatMax(float $value, int $max): bool {

        return $value > $max;
    }


    /**
     * @param mixed $value
     * @return bool
     */
    public static function isEmail(mixed $value): bool {

        return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }


    /**
     * @param mixed $value
     * @return bool
     */
    public static function isDatetime(mixed $value): bool {

        return is_string($value) && preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})$/', $value);
    }


    /**
     * @param string $value
     * @return bool
     */
    public static function isDatetimeCheck(string $value): bool {

        [$year, $month, $day, $hours, $min, $sec] = sscanf($value, '%d-%d-%d %d:%d:%d');

        return checkdate($month, $day, $year);
    }


    /**
     * @param string $value
     * @param string $min_datetime
     * @return bool
     */
    public static function isDatetimeMin(string $value, string $min_datetime): bool {

        return $value < $min_datetime;
    }


    /**
     * @param string $value
     * @param string $max_datetime
     * @return bool
     */
    public static function isDatetimeMax(string $value, string $max_datetime): bool {

        return $value > $max_datetime;
    }


    /**
     * @param mixed $value
     * @return bool
     */
    public static function isDate(mixed $value): bool {

        return is_string($value) && preg_match('/^(\d{4}-\d{2}-\d{2})$/', $value);
    }


    /**
     * @param string $value
     * @return bool
     */
    public static function isDateCheck(string $value): bool {

        [$year, $month, $day, $hours, $min, $sec] = sscanf($value, '%d-%d-%d %d:%d:%d');

        return checkdate($month, $day, $year);
    }


    /**
     * @param string $value
     * @param string $min_date
     * @return bool
     */
    public static function isDateMin(string $value, string $min_date): bool {

        return $value < $min_date;
    }


    /**
     * @param string $value
     * @param string $max_date
     * @return bool
     */
    public static function isDateMax(string $value, string $max_date): bool {

        return $value > $max_date;
    }


    /**
     * @param array $fields
     * @param array $data
     * @param bool  $strict
     * @return array
     */
    public static function validateFields(array $fields, array $data, bool $strict = false): array {

        $errors = [];

        foreach ($fields as $field => $rule) {

            if ( ! is_array($rule)) {
                continue;
            }

            if (empty($rule['rule'])) {
                break;
            }

            $rules_explode = explode(',', $rule['rule']);
            $rule_title    = $rule['title'] ?? $field;

            foreach ($rules_explode as $rule_item) {

                $match = [];
                preg_match('~(?<name>[^\(]+)((\((?<options>[^\)]+)\))|)~', $rule_item, $match);

                $rule_name    = $match['name'] ?? null;
                $rule_options = $match['options'] ?? null;

                if (empty($rule_name)) {
                    continue;
                }

                switch ($rule_name) {
                    case 'req':
                        if ( ! array_key_exists($field, $data) || ! self::isRequirement($data[$field])) {
                            $errors[] = self::_('Пустое обязательное поле "%s"', [$rule_title]);
                        }
                        break;

                    case 'string':
                        if (isset($data[$field])) {
                            if ( ! self::isString($data[$field])) {
                                $errors[] = self::_('Некорректный тип поля "%s". Ожидается строка', [$rule_title]);

                            } elseif (isset($rule_options) && $rule_options !== '' && strpos($rule_options, '-') !== false) {
                                $range = explode('-', $rule_options);

                                if (isset($range[0]) && $range[0] !== '' && self::isStringMin($data[$field], $range[0])) {
                                    $errors[] = self::_('Значение поля "%s" слишком короткое. Минимальная длинна %s', [$rule_title, $range[0]]);

                                } elseif (isset($range[1]) && $range[1] !== '' && self::isStringMax($data[$field], $range[1])) {
                                    $errors[] = self::_('Значение поля "%s" слишком длинное. Максимальная длинна %s', [$rule_title, $range[1]]);
                                }

                            } elseif (isset($rule_options) && $rule_options !== '' && strpos($rule_options, '|') !== false) {
                                $items = explode('|', $rule_options);

                                if ( ! empty($items) && ! self::isStringEnum($data[$field], $items)) {
                                    $errors[] = self::_('Некорректное значение поля "%s". Доступные варианты значений: %s', [$rule_title, implode(', ', $items)]);
                                }
                            }
                        }
                        break;

                    case 'regex':
                        //TODO доделать
                        break;

                    case 'chars':
                        if (isset($data[$field])) {
                            if (isset($rule_options) && $rule_options !== '' && strpos($rule_options, '|') !== false) {
                                $items = explode('|', $rule_options);

                                if ( ! empty($items)) {
                                    $items_title = [];
                                    $items_test  = [];

                                    foreach ($items as $item) {
                                        if ($item == 'alphanumeric') {
                                            $items_title[] = 'a-Z, 0-9';
                                            $items_test[]  = "[a-zA-Z0-9]";
                                        } else {
                                            $items_title[] = $item;
                                            $items_test[]  = $item;
                                        }
                                    }

                                    if (preg_match('~[^' . implode('', $items_test) . ']~', $data[$field])) {
                                        $errors[] = self::_('Некорректное значение поля "%s". Доступные символы: %s', [$rule_title, implode(', ', $items_title)]);
                                    }
                                }
                            }
                        }
                        break;

                    case 'array':
                        if (isset($data[$field])) {
                            if ( ! self::isArray($data[$field])) {
                                $errors[] = self::_('Некорректный тип поля "%s". Ожидается массив', [$rule_title]);
                            }
                        }
                        break;

                    case 'bool':
                        if (isset($data[$field]) && ! self::isBool($data[$field])) {
                            $errors[] = self::_('Некорректный тип поля "%s". Ожидается логическое значение', [$rule_title]);
                        }
                        break;

                    case 'switch':
                        if (isset($data[$field]) && ! in_array($data[$field], ['0', '1'])) {
                            $errors[] = self::_('Некорректный тип поля "%s". Ожидается одно из значений: 0, 1', [$rule_title]);
                        }
                        break;

                    case 'int':
                        if (isset($data[$field])) {
                            if ( ! self::isInt($data[$field])) {
                                $errors[] = self::_('Некорректный тип поля "%s". Ожидается целое число', [$rule_title]);

                            } elseif (isset($rule_options) && $rule_options !== '') {
                                $range = explode('-', $rule_options);

                                if ( ! empty($range)) {
                                    if (isset($range[0]) && $range[0] !== '' && self::isIntMin($data[$field], $range[0])) {
                                        $errors[] = self::_('Значение поля "%s" слишком маленькое. Минимальное значение %s', [$rule_title, $range[0]]);

                                    } elseif (isset($range[1]) && $range[1] !== '' && self::isIntMax($data[$field], $range[1])) {
                                        $errors[] = self::_('Значение поля "%s" слишком большое. Максимальное значение %s', [$rule_title, $range[1]]);
                                    }
                                }
                            }
                        }
                        break;

                    case 'float':
                        if (isset($data[$field])) {
                            if ( ! self::isFloat($data[$field])) {
                                $errors[] = self::_('Некорректный тип поля "%s". Ожидается число', [$rule_title]);

                            } elseif (isset($rule_options) && $rule_options !== '') {
                                $range = explode('-', $rule_options);

                                if ( ! empty($range)) {
                                    if (isset($range[0]) && $range[0] !== '' && self::isFloatMin($data[$field], $range[0])) {
                                        $errors[] = self::_('Значение поля "%s" слишком маленькое. Минимальное значение %s', [$rule_title, $range[0]]);

                                    } elseif (isset($range[1]) && $range[1] !== ''  && self::isFloatMax($data[$field], $range[1])) {
                                        $errors[] = self::_('Значение поля "%s" слишком большое. Максимальное значение %s', [$rule_title, $range[1]]);
                                    }
                                }
                            }
                        }
                        break;

                    case 'email':
                        if ( ! empty($data[$field])) {
                            if ( ! self::isEmail($data[$field])) {
                                $errors[] = self::_('Значение поля "%s" не является email', [$rule_title]);
                            }
                        }
                        break;

                    case 'datetime':
                        if (isset($data[$field])) {
                            if ( ! self::isDatetime($data[$field])) {
                                $errors[] = self::_('Значение поля "%s" не является датой. Требуемый формат YYYY-MM-DD HH:II:SS', [$rule_title]);

                            } elseif ( ! self::isDatetimeCheck($data[$field])) {
                                $errors[] = self::_('В поле "%s" указана некорректная дата', [$rule_title]);
                            }
                        }
                        break;

                    case 'date':
                        if (isset($data[$field])) {
                            if ( ! self::isDate($data[$field])) {
                                $errors[] = self::_('Значение поля "%s" не является датой. Требуемый формат YYYY-MM-DD', [$rule_title]);

                            } elseif ( ! self::isDateCheck($data[$field])) {
                                $errors[] = self::_('В поле "%s" указана некорректная дата', [$rule_title]);
                            }
                        }
                        break;
                }
            }
        }


        if ($strict === true) {
            foreach ($data as $param => $value) {
                if ( ! isset($fields[$param])) {
                    $errors[] = self::_('Некорректный запрос. Среди переданных значений присутствует лишнее поле "%s"', [$param]);
                }
            }
        }

        return $errors;
    }
}