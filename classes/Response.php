<?php
namespace Core3\Classes;
use Core3\Exceptions\Exception;


/**
 *
 */
class Response {

    private $http_code = 200;
    private $headers   = [];
    private $content;


    /**
     * @param int $http_code
     * @return void
     */
    public function setHttpCode(int $http_code): void {

        $this->http_code = $http_code;
    }


    /**
     * @return int
     */
    public function getHttpCode(): int {

        return $this->http_code;
    }


    /**
     * @return void
     */
    public function setContentTypeJson(): void {

        $this->setHeader('Content-Type', 'application/json; charset=utf-8');
    }


    /**
     * @return void
     */
    public function setContentTypeHtml(): void {

        $this->setHeader('Content-Type', 'text/html');
    }


    /**
     * @return string|null
     */
    public function getContentType():? string {

        return $this->getHeader('Content-Type');
    }


    /**
     * @param array $headers
     * @return void
     */
    public function setHeaders(array $headers): void {

        foreach ($headers as $name => $value) {
            if (is_scalar($value)) {
                $this->setHeader($name, $value);
            }
        }
    }


    /**
     * @param string $name
     * @param string $value
     * @return void
     */
    public function setHeader(string $name, string $value): void {

        $name = trim($name);
        $name = ucwords(strtolower($name));

        $this->headers[$name] = trim($value);
    }


    /**
     * @param string $name
     * @return string|null
     */
    public function getHeader(string $name):? string {

        return $this->headers[$name] ?? null;
    }


    /**
     * @return array
     */
    public function getHeaders(): array {

        return $this->headers;
    }


    /**
     * @param mixed $content
     * @return void
     * @throws Exception
     */
    public function setContent(mixed $content): void {

        if ( ! is_scalar($content)) {
            throw new Exception('Incorrect content type');
        }

        $this->content = $content;
    }


    /**
     * @param mixed $content
     * @return void
     */
    public function setContentJson(mixed $content): void {

        $this->content = json_encode($content, JSON_UNESCAPED_UNICODE);
    }


    /**
     * @param mixed $content
     * @return void
     * @throws Exception
     */
    public function appendContent(mixed $content): void {

        if ( ! is_scalar($content)) {
            throw new Exception('Incorrect content type');
        }

        $this->content = $content . $this->content;
    }


    /**
     * @param mixed $content
     * @return void
     * @throws Exception
     */
    public function prependContent(mixed $content): void {

        if ( ! is_scalar($content)) {
            throw new Exception('Incorrect content type');
        }

        $this->content .= $content;
    }


    /**
     * @return mixed
     */
    public function getContent(): mixed {

        return $this->content;
    }


    /**
     * @return void
     */
    public function printHeaders(): void {

        $headers = $this->getHeaders();

        http_response_code($this->http_code);

        foreach ($headers as $name => $value) {
            header("{$name}: {$value}");
        }
    }


    /**
     * @param int        $http_code
     * @param string     $error_code
     * @param string     $error_message
     * @param array|null $error_trace
     * @return Response
     * @throws Exception
     */
    public static function errorJson(int $http_code, string $error_code, string $error_message = '', array $error_trace = null): Response {

        $data = [
            'error_code'    => $error_code,
            'error_message' => $error_message,
        ];

        if ($error_trace) {
            $data['error_trace'] = $error_trace;
        }

        $response = new Response();
        $response->setHttpCode($http_code);
        $response->setContentTypeJson();
        $response->setContent(json_encode($data, JSON_UNESCAPED_UNICODE));

        return $response;
    }


    /**
     * @param int $http_code
     * @return Response
     */
    public static function httpCode(int $http_code): Response {

        $response = new Response();
        $response->setHttpCode($http_code);

        return $response;
    }
}