<?php
namespace Core3\Exceptions;


/**
 *
 */
class HttpException extends Exception {

    protected string $error_code = '';


    /**
     * @param int    $http_code
     * @param string $error_code
     * @param string $message
     */
    public function __construct(int $http_code, string $error_code, string $message) {

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