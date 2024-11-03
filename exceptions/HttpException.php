<?php
namespace Core3\Exceptions;


/**
 *
 */
class HttpException extends Exception {

    protected string $error_code = '';


    /**
     * @param int         $http_code
     * @param string      $message
     * @param string|null $error_code
     */
    public function __construct(int $http_code, string $message, string $error_code = null) {

        parent::__construct($message, $http_code);
        $this->error_code = (string)$error_code;
    }


    /**
     * @return string
     */
    public function getErrorCode(): string {
        return $this->error_code;
    }
}