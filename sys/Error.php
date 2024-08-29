<?php
namespace Core3\Sys;
use Core3\Classes\Log;
use Core3\Classes\Registry;
use Core3\Classes\Tools;


/**
 * Class Error
 * @package Core
 */
class Error {

    /**
     * @param \Exception $e
     * @return void
     */
    public static function catchException(\Exception $e): void {

        $config   = Registry::has('config') ? Registry::get('config') : [];
        $is_debug = empty($config) || $config?->system?->debug?->on;

        if (PHP_SAPI === 'cli') {
            if ($is_debug) {
                echo $e->getMessage() . PHP_EOL;
                echo $e->getFile() . ': ' . $e->getLine() . PHP_EOL . PHP_EOL;
                echo $e->getTraceAsString();
            } else {
                echo $e->getMessage() . PHP_EOL;
            }

        } else {
            if (substr($e->getMessage(), 0, 8) == 'SQLSTATE') {
                $message = 'Ошибка базы данных';
            } else {
                $message = 'Ошибка системы';
            }

            $type = Tools::getBestMathType('text/html');

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

        $log = new Log();
        $log->error('Critical error', $e);
    }
}