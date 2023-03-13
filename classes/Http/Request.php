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
     * @var array|null
     */
    private ?array $props = [];


    /**
     * @param array|null $props
     */
    public function __construct(array $props = null) {

        $this->query           = $_SERVER['QUERY_STRING'];
        $this->method          = strtolower($_SERVER['REQUEST_METHOD']);
        $this->props           = $props ?? [];
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
     * @return string
     */
    public function getQuery(): string {

        return $this->query;
    }


    /**
     * @return string
     */
    public function getQueryMod(): string {

        return $this->props['mod_query'] ?? '';
    }


    /**
     * @return array
     */
    public function getQueryParams(): array {

        return $this->props['GET'] ?? [];
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
     * @return string
     * @throws RuntimeException
     */
    public function getBody(string $format = null): string {

        $request_raw = file_get_contents('php://input', 'r');

        switch ($format) {
            case 'text':
            default:
                return $request_raw;

            case 'json':
                $request = @json_decode($request_raw, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new RuntimeException('Incorrect json data');
                }
                return $request;
        }
    }
}