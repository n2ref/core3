<?php
namespace Core3\Classes;
use Core3\Classes\Init\Router\Path;


/**
 *
 */
class Router {

    /**
     * @var array
     */
    private array $routes = [];


    /**
     * @param string         $path
     * @param array|\Closure $action
     * @return void
     */
    public function get(string $path, array|\Closure $action): void {

        $this->method('get', $path, $action);
    }


    /**
     * @param string         $path
     * @param array|\Closure $action
     * @return void
     */
    public function post(string $path, array|\Closure $action): void {

        $this->method('post', $path, $action);
    }


    /**
     * @param string         $path
     * @param array|\Closure $action
     * @return void
     */
    public function put(string $path, array|\Closure $action): void {

        $this->method('put', $path, $action);
    }


    /**
     * @param string         $path
     * @param array|\Closure $action
     * @return void
     */
    public function delete(string $path, array|\Closure $action): void {

        $this->method('delete', $path, $action);
    }


    /**
     * @param string         $path
     * @param array|\Closure $action
     * @return void
     */
    public function patch(string $path, array|\Closure $action): void {

        $this->method('patch', $path, $action);
    }


    /**
     * @param string         $path
     * @param array|\Closure $action
     * @return void
     */
    public function options(string $path, array|\Closure $action): void {

        $this->method('options', $path, $action);
    }


    /**
     * @param string         $path
     * @param array|\Closure $action
     * @return void
     */
    public function any(string $path, array|\Closure $action): void {

        $this->method('*', $path, $action);
    }


    /**
     * @param string         $method
     * @param string         $path
     * @param array|\Closure $action
     * @return void
     */
    public function method(string $method, string $path, array|\Closure $action): void {

        $this->routes[] = [
            'method' => strtolower($method),
            'path'   => $path,
            'action' => $action,
        ];
    }


    /**
     * @param string $method
     * @param string $path
     * @return Route|null
     * @throws \Exception
     */
    public function getRoute(string $method, string $path):? Route {

        $method = strtolower($method);


        foreach ($this->routes as $route) {

            if ($method == $route['method'] || $route['method'] == '*') {
                $prepare_path = $this->preparePath($route['path']);

                if (preg_match("~{$prepare_path}~u", $path, $matches)) {

                    foreach ($matches as $key => $match) {
                        if (is_numeric($key)) {
                            unset($matches[$key]);
                        }
                    }

                    return new Route($route['method'], $route['path'], $route['action'], $matches);
                }
            }
        }

        return null;
    }


    /**
     * @param string $method
     * @param string $path
     * @return Route[]
     * @throws \Exception
     */
    public function getRoutes(string $method, string $path): array {

        $result = [];
        $method = strtolower($method);

        foreach ($this->routes as $route) {

            if ($method == $route['method'] || $route['method'] == '*') {
                $prepare_path = $this->preparePath($route['path']);

                if (preg_match("~{$prepare_path}~u", $path, $matches)) {

                    foreach ($matches as $key => $match) {
                        if (is_numeric($key)) {
                            unset($matches[$key]);
                        }
                    }

                    $result[] = new Route($route['method'], $route['path'], $route['action'], $matches);
                }
            }
        }

        return $result;
    }


    /**
     * @param string $path
     * @return string
     */
    private function preparePath(string $path): string {

        if (preg_match_all('~\{(?<name>[a-zA-Z0-9_]+)(?:|:(?<rule>[^}]+))\}~u', $path, $matches)) {

            if ( ! empty($matches[0])) {
                foreach ($matches[0] as $key => $match) {
                    $count = 1;
                    $name  = $matches['name'][$key];
                    $rule  = $matches['rule'][$key] ?: '[\d\w_\-]+';
                    $path  = str_replace($match, "(?<{$name}>{$rule})", $path, $count);
                }
            }
        }

        return $path;
    }
}