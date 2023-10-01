<?php
namespace Core3\Classes\Http;
use Core3\Exceptions\RuntimeException;


/**
 *
 */
class Request {

    /**
     * @var string
     */
    private string $method = '';

    /**
     * @var string
     */
    private string $query = '';

    /**
     * @var array
     */
    private array $path_params = [];

    /**
     * @var array
     */
    private array $props = [];


    const FORMAT_TEXT = 'text';
    const FORMAT_JSON = 'json';


    /**
     * @param array|null $path_params
     */
    public function __construct(array $path_params = null) {

        $this->setPathParams($path_params);

        $this->query  = $_SERVER['QUERY_STRING'];
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);

        $this->props['GET']    = $_GET;
        $this->props['POST']   = $_POST;
        $this->props['FILES']  = $_FILES;
        $this->props['COOKIE'] = $_COOKIE;
    }


    /**
     * @return string
     */
    public function getMethod(): string {

        return $this->method ?? '';
    }


    /**
     * @return array
     */
    public function getHeaders(): array {

        $headers = [];

        if (function_exists('getallheaders')) {
            $headers = getallheaders();

        } else {
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $headers[$name] = $value;

                } else if ($name == "CONTENT_TYPE") {
                    $headers["Content-Type"] = $value;
                } else if ($name == "CONTENT_LENGTH") {
                    $headers["Content-Length"] = $value;
                }
            }
        }

        return $headers;
    }


    /**
     * @param string $name
     * @return string|null
     */
    public function getPathParam(string $name):? string {

        return $this->path_params[$name] ?? null;
    }


    /**
     * @return array
     */
    public function getPathParams(): array {

        return $this->path_params;
    }


    /**
     * @return string
     */
    public function getQueryString(): string {

        return $this->query;
    }


    /**
     * @return array
     */
    public function getQueryParams(): array {

        return $this->props['GET'] ?? [];
    }


    /**
     * @param string $name
     * @return mixed
     */
    public function getQuery(string $name): mixed {

        $queries = $this->props['GET'];

        return $queries[$name] ?? null;
    }


    /**
     * @return array
     */
    public function getPost(): array {

        return $this->props['POST'] ?? [];
    }


    /**
     * @return array
     */
    public function getFiles(): array {

        return $this->props['FILES'] ?? [];
    }


    /**
     * @return array
     */
    public function getCookie(): array {

        return $this->props['COOKIE'] ?? [];
    }


    /**
     * @param string|null $format
     * @return string|array
     * @throws RuntimeException
     */
    public function getBody(string $format = null): string|array {

        $request_raw = file_get_contents('php://input', 'r');

        switch ($format) {
            case self::FORMAT_TEXT:
            default:
                return $request_raw;

            case self::FORMAT_JSON:
                $request = @json_decode($request_raw, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new RuntimeException('Incorrect json data');
                }
                return $request;
        }
    }


    /**
     * @param array $query_params
     * @return void
     */
    private function setPathParams(array $query_params): void {

        foreach ($query_params as $name => $value) {
            if (is_scalar($value)) {
                $name                     = trim($name);
                $this->path_params[$name] = $value;
            }
        }
    }
}