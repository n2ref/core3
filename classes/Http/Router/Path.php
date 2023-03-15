<?php
namespace Core3\Classes\Http\Router;

use Core3\Exceptions\HttpException;

/**
 * 
 */
class Path {

    private string $path   = '';
    private array  $params = [];
    private array  $methods = [];


    /**
     * @param string $path
     * @param array  $params
     */
    public function __construct(string $path, array $params = []) {

        $this->path   = $path;
        $this->params = array_values($params);
    }


    /**
     * @param string $action
     * @return $this
     */
    public function get(string $action): self {

        return $this->method('get', $action);
    }


    /**
     * @param string $action
     * @return $this
     */
    public function post(string $action): self {

        return $this->method('post', $action);
    }


    /**
     * @param string $action
     * @return $this
     */
    public function put(string $action): self {

        return $this->method('put', $action);
    }


    /**
     * @param string $action
     * @return $this
     */
    public function patch(string $action): self {

        return $this->method('patch', $action);
    }


    /**
     * @param string $action
     * @return $this
     */
    public function delete(string $action): self {

        return $this->method('delete', $action);
    }


    /**
     * @param string $action
     * @return $this
     */
    public function any(string $action): self {

        return $this->method('*', $action);
    }


    /**
     * @param string $method
     * @param string $action
     * @return $this
     */
    public function method(string $method, string $action): self {

        $this->methods[$method] = $action;

        return $this;
    }


    /**
     * @param string $method
     * @param string $query
     * @return array
     */
    public function getRoute(string $method, string $query): array {

        $method      = strtolower($method);
        $route       = [];
        $preparePath = $this->preparePath();

        if (preg_match("~^{$preparePath}$~u", $query, $matches)) {
            $method = ! empty($this->methods[$method]) ? $method : '*';
            if ( ! empty($this->methods[$method])) {
                foreach ($matches as $key => $match) {
                    if (is_numeric($key)) {
                        unset($matches[$key]);
                    }
                }

                $route['action'] = $this->methods[$method];
                $route['params'] = $matches;
            }
        }

        return $route;
    }


    /**
     * @return string
     */
    private function preparePath(): string {

        $path = $this->path;

        if (preg_match_all('~\{[a-z0-9_]+\}~u', $this->path, $matches)) {

            if ( ! empty($matches[0])) {
                foreach ($matches[0] as $key => $match) {
                    if (isset($this->params[$key])) {
                        $count = 1;
                        $name  = substr($match, 1, -1);
                        $path  = str_replace($match, "(?<{$name}>{$this->params[$key]})", $path, $count);
                    }
                }
            }
        }

        return $path;
    }
}