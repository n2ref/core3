<?php
namespace Core3;

require_once 'Tools.php';
require_once 'Log.php';


/**
 * Class Error
 * @package Core
 */
class Error {

    /**
     * @param \Exception $e
     */
    public static function catchException(\Exception $e) {

        $config   = self::getConfig();
        $is_debug = empty($config) || $config->system->debug->on;

        if (PHP_SAPI === 'cli') {
            if ($is_debug) {
                echo $e->getMessage() . PHP_EOL;
                echo $e->getFile() . ': ' . $e->getLine() . PHP_EOL . PHP_EOL;
                echo $e->getTraceAsString();
            } else {
                echo $e->getMessage() . PHP_EOL;
            }

        } elseif ($is_debug && ! empty($config) && $config->debug && $config->debug->firephp) {
            Tools::fb($e->getMessage() . "\n" . $e->getFile() . ': ' . $e->getLine() . "\n\n" . $e->getTraceAsString());

        } else {
            if (substr($e->getMessage(), 0, 8) == 'SQLSTATE') {
                $message = 'Ошибка базы данных';
            } else {
                $message = 'Ошибка системы';
            }

            $type = self::getBestMathType('text/html');
            switch ($type) {
                default:
                case 'text/html':
                    header('Content-Type: text/html; charset=utf-8');
                    if ($is_debug) {
                        $msg  = '<pre>';
                        $msg .= $e->getMessage() . "\n";
                        $msg .= '<b>' . $e->getFile() . ': ' . $e->getLine() . "</b>\n\n";
                        $msg .= $e->getTraceAsString();
                        $msg .= '</pre>';
                        echo $msg;
                    } else {
                        echo $message;
                    }
                    break;

                case 'text/plain':
                    header('Content-Type: text/plain; charset=utf-8');
                    if ($is_debug) {
                        $msg  = $e->getMessage() . "\n";
                        $msg .= $e->getFile() . ': ' . $e->getLine() . "\n\n";
                        $msg .= $e->getTraceAsString();
                        echo $msg;
                    } else {
                        echo $message;
                    }
                    break;

                case 'application/json':
                    header('Content-type: application/json; charset="utf-8"');
                    if ($is_debug) {
                        echo json_encode([
                            'message'     => $e->getMessage(),
                            'error_code'  => $e->getCode(),
                            'error_file'  => $e->getFile(),
                            'error_line'  => $e->getLine(),
                            'error_trace' => $e->getTrace(),
                        ]);
                    } else {
                        echo json_encode(['message' => $message]);
                    }
                    break;
            }
        }

        if (class_exists('\Zend_Registry')) {
            $log = new Log();
            $log->error($e->getMessage());
        }
    }


    /**
     * Получение формата для ответа
     * @param string $default
     * @return string
     */
    private static function getBestMathType($default = 'text/html') {

        $types           = [];
        $available_types = [
            'text/html', 'text/plain', 'application/json'
        ];

        if (isset($_SERVER['HTTP_ACCEPT']) && ($accept = strtolower($_SERVER['HTTP_ACCEPT']))) {
            $explode_accept = explode(',', $accept);

            if ( ! empty($explode_accept)) {
                foreach ($explode_accept as $accept_type) {
                    if (strpos($accept_type, ';') !== false) {
                        $explode_accept_type = explode(';', $accept_type);
                        $quality             = '';

                        if (preg_match('/q=([0-9.]+)/', $explode_accept_type[1], $quality)) {
                            $types[$explode_accept_type[0]] = $quality[1];
                        } else {
                            $types[$explode_accept_type[0]] = 0;
                        }

                    } else {
                        $types[$accept_type] = 1;
                    }
                }

                arsort($types, SORT_NUMERIC);
            }
        }


        if ( ! empty($types)) {
            foreach ($types as $type => $v) {
                if (array_key_exists($type, $available_types)) {
                    return $type;
                }
            }
        }

        return $default;
    }


    /**
     * Получаем экземпляр конфига
     * @return mixed
     */
    private static function getConfig() {

        $config = [];

        if (class_exists('\Zend_Registry') && \Zend_Registry::isRegistered('config')) {
            $config = \Zend_Registry::get('config');
        }

        return $config;
    }
}