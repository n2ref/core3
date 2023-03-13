<?php
namespace Core3\Classes\Http;


/**
 *
 */
class Response {


    /**
     * @param string $error_message
     * @param string $error_code
     * @param int    $http_core
     * @param array  $headers
     * @return string
     */
    public static function errorJson(string $error_message, string $error_code, int $http_core = 200, array $headers = []): string {

        http_response_code($http_core);
        header('Content-Type: application/json');

        if ( ! empty($headers)) {
            foreach ($headers as $header) {
                header($header);
            }
        }

        $body = [
            'error_code'    => $error_code,
            'error_message' => $error_message,
        ];

        return json_encode($body, JSON_UNESCAPED_UNICODE);
    }


    /**
     * @param mixed          $data
     * @param int            $http_core
     * @param array|string[] $headers
     * @return string
     */
    public static function dataJson(mixed $data, int $http_core = 200, array $headers = []): string {

        http_response_code($http_core);
        header('Content-Type: application/json');

        if ( ! empty($headers)) {
            foreach ($headers as $header) {
                header($header);
            }
        }

        return (string)json_encode($data);
    }
}