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
     * @return self
     */
    public function setContentTypeJson(): self {

        $this->setHeader('Content-Type', 'application/json; charset=utf-8');
        return $this;
    }


    /**
     * @return self
     */
    public function setContentTypeHtml(): self {

        $this->setHeader('Content-Type', 'text/html');
        return $this;
    }


    /**
     * @return string|null
     */
    public function getContentType():? string {

        return $this->getHeader('Content-Type');
    }


    /**
     * @param array $headers
     * @return self
     */
    public function setHeaders(array $headers): self {

        foreach ($headers as $name => $value) {
            if (is_scalar($value)) {
                $this->setHeader($name, $value);
            }
        }

        return $this;
    }


    /**
     * @param string $name
     * @param string $value
     * @return self
     */
    public function setHeader(string $name, string $value): self {

        $name = trim($name);
        $name = ucwords(strtolower($name));

        $this->headers[$name] = trim($value);

        return $this;
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
     * @return self
     * @throws Exception
     */
    public function setContent(mixed $content): self {

        if ( ! is_scalar($content)) {
            throw new Exception('Incorrect content type');
        }

        $this->content = $content;

        return $this;
    }


    /**
     * @param mixed $content
     * @return self
     */
    public function setContentJson(mixed $content): self {

        $this->content = json_encode($content, JSON_UNESCAPED_UNICODE);

        return $this;
    }


    /**
     * @param mixed $content
     * @return self
     * @throws Exception
     */
    public function appendContent(mixed $content): self {

        if ( ! is_scalar($content)) {
            throw new Exception('Incorrect content type');
        }

        $this->content = $content . $this->content;

        return $this;
    }


    /**
     * @param mixed $content
     * @return self
     * @throws Exception
     */
    public function prependContent(mixed $content): self {

        if ( ! is_scalar($content)) {
            throw new Exception('Incorrect content type');
        }

        $this->content .= $content;

        return $this;
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