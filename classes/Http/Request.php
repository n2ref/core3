<?php
namespace Core3\Classes\Http;
use Core3\Exceptions\Exception;


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


    const FORMAT_RAW  = 'raw';
    const FORMAT_JSON = 'json';
    const FORMAT_FORM = 'form';


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
     * @throws Exception
     */
    public function getBody(string $format = null): string|array {

        $request_raw = file_get_contents('php://input', 'r');

        switch ($format) {
            case self::FORMAT_RAW:
            default:
                $return = &$request_raw;
                break;

            case self::FORMAT_FORM:
                $return = $this->getFormData($request_raw)['fields'];
                break;

            case self::FORMAT_JSON:
                $json_data = @json_decode($request_raw, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Incorrect json data');
                }
                $return = $json_data;
                break;
        }

        return $return;
    }


    /**
     * @return array
     * @throws Exception
     */
    public function getFormContent(): array {

        return $_SERVER['REQUEST_METHOD'] == 'POST'
            ? $this->getPost()
            : $this->getBody($this::FORMAT_FORM);
    }


    /**
     * @return array
     * @throws Exception
     */
    public function getJsonContent(): array {

        return $this->getBody($this::FORMAT_JSON);
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


    /**
     * @param string $content
     * @return array
     */
    private function getFormData(string $content): array {

        // Fetch content and determine boundary
        $boundary = substr($content, 0, strpos($content, "\r\n"));
        $files    = [];
        $data     = [];

        if (empty($boundary)) {
            parse_str($content, $data);

        } else {
            // Fetch each part
            $parts = array_slice(explode($boundary, $content), 1);


            foreach ($parts as $part) {
                // If this is the last part, break
                if ($part == "--\r\n") {
                    break;
                }

                // Separate content from headers
                $part = ltrim($part, "\r\n");
                [$raw_headers, $body] = explode("\r\n\r\n", $part, 2);

                // Parse the headers list
                $raw_headers = explode("\r\n", $raw_headers);
                $headers     = [];

                foreach ($raw_headers as $header) {
                    [$name, $value] = explode(':', $header);
                    $headers[strtolower($name)] = ltrim($value, ' ');
                }

                // Parse the Content-Disposition to get the field name, etc.
                if (isset($headers['content-disposition'])) {
                    preg_match(
                        '/^(?<type>.+); *name="(?<name>[^"]*)"(; *filename="(?<filename>[^"]*)")?/',
                        $headers['content-disposition'],
                        $matches
                    );

                    $is_file = isset($matches['filename']);

                    //Parse File
                    if ($is_file) {
                        //get tmp name
                        $filename_parts = pathinfo($matches['filename']);
                        $tmp_name       = tempnam(ini_get('upload_tmp_dir'), $filename_parts['filename']);

                        $value = [
                            'error'    => 0,
                            'name'     => $matches['filename'],
                            'tmp_name' => $tmp_name,
                            'size'     => strlen($body),
                            'type'     => $matches['type'],
                        ];

                        //place in temporary directory
                        file_put_contents($tmp_name, $body);
                    } else {
                        $value = substr($body, 0, strlen($body) - 2);
                    }

                    parse_str($matches['name'], $name_structure);
                    $path      = preg_split('~(\[|\])~', $matches['name']);
                    $name_part = &$name_structure;

                    foreach ($path as $key) {
                        if ( ! empty($key)) {
                            if ( ! is_array($name_part)) {
                                $name_part = array();
                            }
                            $name_part = &$name_part[$key];
                        }
                    }
                    $name_part = $value;

                    if ($is_file) {
                        $files = array_merge_recursive($files, $name_structure);
                    } else {
                        $data = array_merge_recursive($data, $name_structure);
                    }
                }
            }
        }

        return [
            'fields' => $data,
            'files'  => $files ?: null,
        ];
    }
}