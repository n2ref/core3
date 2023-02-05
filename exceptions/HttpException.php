<?php
namespace Core3\Exceptions;

require_once 'RuntimeException.php';

/**
 *
 */
class HttpException extends RuntimeException {

    protected string $error_code = '';


    /**
     * @param string $message
     * @param string $error_code
     * @param int    $http_code
     */
    public function __construct(string $message, string $error_code, int $http_code = 200) {

        parent::__construct($message, $http_code);
        $this->error_code = $error_code;
    }


    /**
     * @return string
     */
    public function getErrorCode(): string {
        return $this->error_code;
    }
}