<?php
namespace Core3\Classes;
use Core3\Exceptions\HttpException;

/**
 *
 */
class HttpValidator {


    /**
     * @param string $method_name
     * @return void
     * @throws HttpException
     */
    public static function testRequestMethod(string $method_name): void {

        $method_name = strtoupper($method_name);

        if ($_SERVER['REQUEST_METHOD'] !== $method_name) {
            throw new HttpException("incorrect request method. Need {$method_name}", 'incorrect_http_method', 405);
        }
    }


    /**
     * @param string $type_name
     * @return void
     * @throws HttpException
     */
    public static function testContentType(string $type_name): void {

        if (empty($_SERVER['CONTENT_TYPE']) || strpos($_SERVER['CONTENT_TYPE'], $type_name) !== 0) {
            throw new HttpException('Incorrect content type. Need ' . $type_name, 'incorrect_content_type', 400);
        }
    }


    /**
     * @param array $parameters
     * @param array $data
     * @param false $strict
     * @throws HttpException
     */
    public static function testParameters(array $parameters, array $data, bool $strict = false): void {

        if ( ! empty($parameters)) {
            foreach ($parameters as $param => $rules) {

                $rules_explode = explode(',', $rules);

                foreach ($rules_explode as $rule) {

                    $match = [];
                    preg_match('~([^\(]+)((\(([^\)]+)\))|)~', $rule, $match);

                    $rule_name    = isset($match[1]) ? $match[1] : false;
                    $rule_options = isset($match[4]) ? $match[4] : false;

                    if (empty($rule_name)) {
                        continue;
                    }

                    switch ($rule_name) {
                        case 'req':
                            if ( ! isset($data[$param])) {
                                throw new HttpException(
                                    "Empty required parameter {$param}",
                                    "empty_param_{$param}",
                                    400
                                );
                            }
                            break;

                        case 'string':
                            if (isset($data[$param])) {
                                if ( ! is_string($data[$param])) {
                                    throw new HttpException(
                                        "Incorrect type parameter {$param}. Need string",
                                        "incorrect_type_string_param_{$param}",
                                        400
                                    );
                                }

                                if (isset($rule_options) && $rule_options !== '' && strpos($rule_options, '-') !== false) {
                                    $range = explode('-', $rule_options);

                                    if ( ! empty($range) && isset($range[0]) && isset($range[1])) {
                                        if (mb_strlen($data[$param]) < $range[0] || mb_strlen($data[$param]) > $range[1]) {
                                            throw new HttpException(
                                                "Incorrect length parameter {$param}. Need range {$range[0]} - {$range[1]}",
                                                "incorrect_string_length_param_{$param}",
                                                400
                                            );
                                        }
                                    }

                                } elseif (isset($rule_options) && $rule_options !== '' && strpos($rule_options, '|') !== false) {
                                    $items = explode('|', $rule_options);

                                    if ( ! empty($items)) {
                                        if ( ! in_array($data[$param], $items)) {
                                            throw new HttpException(
                                                "Incorrect value parameter {$param}. Please enter one of the values: " . implode(', ', $items),
                                                "incorrect_string_value_param_{$param}",
                                                400
                                            );
                                        }
                                    }
                                }
                            }
                            break;

                        case 'array':
                            if (isset($data[$param])) {
                                if ( ! is_array($data[$param])) {
                                    throw new HttpException(
                                        "Incorrect type parameter {$param}. Need array",
                                        "incorrect_type_array_param_{$param}",
                                        400
                                    );
                                }
                            }
                            break;

                        case 'bool':
                            if (isset($data[$param])) {
                                if ( ! is_bool($data[$param])) {
                                    throw new HttpException(
                                        "Incorrect type parameter {$param}. Need bool",
                                        "incorrect_type_bool_param_{$param}",
                                        400
                                    );
                                }
                            }
                            break;

                        case 'int':
                            if (isset($data[$param])) {
                                if ( ! is_numeric($data[$param]) || filter_var($data[$param], FILTER_VALIDATE_INT) === false) {
                                    throw new HttpException(
                                        "Incorrect type parameter {$param}. Need int",
                                        "incorrect_type_int_param_{$param}",
                                        400
                                    );
                                }

                                if (isset($rule_options) && $rule_options !== '') {
                                    $range = explode('-', $rule_options);

                                    if ( ! empty($range)) {
                                        if (isset($range[0]) && isset($range[1]) &&
                                            $range[0] != '' && $range[1] != '' &&
                                            ($data[$param] < $range[0] || $data[$param] > $range[1])
                                        ) {
                                            throw new HttpException(
                                                "Incorrect range parameter {$param}. Need range {$range[0]} - {$range[1]}",
                                                "incorrect_int_range_param_{$param}",
                                                400
                                            );

                                        } elseif (isset($range[0]) && $range[0] != '' &&
                                                 ( ! isset($range[1]) || $range[1] == '') &&
                                                 $data[$param] < $range[0]
                                        ) {
                                            throw new HttpException(
                                                "Incorrect range parameter {$param}. Need range from {$range[0]}",
                                                "incorrect_int_range_param_{$param}",
                                                400
                                            );

                                        } elseif (isset($range[1]) && $range[1] != '' &&
                                                 ( ! isset($range[0]) || $range[0] == '') &&
                                                 $data[$param] > $range[1]
                                        ) {
                                            throw new HttpException(
                                                "Incorrect range parameter {$param}. Need range up to {$range[1]}",
                                                "incorrect_int_range_param_{$param}",
                                                400
                                            );
                                        }
                                    }
                                }
                            }
                            break;

                        case 'float':
                            if (isset($data[$param])) {
                                if ( ! is_numeric($data[$param]) || filter_var($data[$param], FILTER_VALIDATE_FLOAT) === false) {
                                    throw new HttpException(
                                        "Incorrect type parameter {$param}. Need float",
                                        "incorrect_type_float_param_{$param}",
                                        400
                                    );
                                }

                                if (isset($rule_options) && $rule_options !== '') {
                                    $range = explode('-', $rule_options);

                                    if ( ! empty($range)) {
                                        if (isset($range[0]) && isset($range[1]) &&
                                            $range[0] != '' && $range[1] != '' &&
                                            ($data[$param] < $range[0] || $data[$param] > $range[1])
                                        ) {
                                            if ($data[$param] < $range[0] || $data[$param] > $range[1]) {
                                                throw new HttpException(
                                                    "Incorrect range parameter {$param}. Need range {$range[0]} - {$range[1]}",
                                                    "incorrect_float_range_param_{$param}",
                                                    400
                                                );
                                            }

                                        } elseif (isset($range[0]) && $range[0] != '' &&
                                            ( ! isset($range[1]) || $range[1] == '') &&
                                            $data[$param] < $range[0]
                                        ) {
                                            throw new HttpException(
                                                "Incorrect range parameter {$param}. Need range from {$range[0]}",
                                                "incorrect_float_range_param_{$param}",
                                                400
                                            );

                                        } elseif (isset($range[1]) && $range[1] != '' &&
                                            ( ! isset($range[0]) || $range[0] == '') &&
                                            $data[$param] > $range[1]
                                        ) {
                                            throw new HttpException(
                                                "Incorrect range parameter {$param}. Need range up to {$range[1]}",
                                                "incorrect_float_range_param_{$param}",
                                                400
                                            );
                                        }
                                    }
                                }
                            }
                            break;

                        case 'email':
                            if ( ! empty($data[$param])) {
                                if ( ! is_string($data[$param]) || ! filter_var($data[$param], FILTER_VALIDATE_EMAIL)) {
                                    throw new HttpException(
                                        "Incorrect type parameter {$param}. Need email",
                                        "incorrect_type_email_param_{$param}",
                                        400
                                    );
                                }
                            }
                            break;

                        case 'datetime':
                            if (isset($data[$param])) {
                                if ( ! preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})$/', $data[$param])) {
                                    throw new HttpException(
                                        "Incorrect type parameter {$param}. Need datetime format YYYY-MM-DD HH:II:SS",
                                        "incorrect_type_datetime_param_{$param}",
                                        400
                                    );

                                } else {
                                    list($year, $month, $day, $hours, $min, $sec) = sscanf($data[$param], '%d-%d-%d %d:%d:%d');

                                    if ( ! checkdate($month, $day, $year)) {
                                        throw new HttpException(
                                            "Incorrect date in parameter {$param}",
                                            "incorrect_date_param_{$param}",
                                            400
                                        );
                                    }

                                    if ($hours > 23 || $min > 59 || $sec > 59) {
                                        throw new HttpException(
                                            "Incorrect time in parameter {$param}",
                                            "incorrect_time_param_{$param}",
                                            400
                                        );
                                    }
                                }
                            }
                            break;

                        case 'date':
                            if (isset($data[$param])) {
                                if ( ! preg_match('/^(\d{4}-\d{2}-\d{2})$/', $data[$param])) {
                                    throw new HttpException(
                                        "Incorrect type parameter {$param}. Need date format YYYY-MM-DD",
                                        "incorrect_type_date_param_{$param}",
                                        400
                                    );
                                } else {
                                    list($year, $month, $day) = sscanf($data[$param], '%d-%d-%d');
                                    if ( ! checkdate($month, $day, $year)) {
                                        throw new HttpException(
                                            "Incorrect date in parameter {$param}",
                                            "incorrect_date_param_{$param}",
                                            400
                                        );
                                    }
                                }
                            }
                            break;
                    }
                }
            }


            if ($strict === true) {
                foreach ($data as $param => $value) {
                    if ( ! isset($parameters[$param])) {
                        throw new HttpException(
                            "The query uses an extra parameter: {$param}",
                            "extra_parameter",
                            400
                        );
                    }
                }
            }
        }
    }
}