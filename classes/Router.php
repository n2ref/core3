<?php
namespace Core3\Classes;
use Core3\Classes\Router\Route;
use Core3\Classes\Router\Method;


/**
 *
 */
class Router {

    /**
     * @var Route[]
     */
    private array $routes = [];


    /**
     * @param string $path
     * @return Route
     */
    public function route(string $path): Route {

        $route = new Route($path);

        $this->routes[$path] = $route;

        return $route;
    }


    /**
     * @param string $method
     * @param string $path
     * @return Route|null
     * @throws \Exception
     */
    public function getRouteMethod(string $method, string $path):? Method {

        $method = strtolower($method);
        $path   = preg_replace('~\?.*~', '', $path);

        foreach ($this->routes as $route) {

            if (($params = $this->getRouteParams($route, $method, $path)) !== null) {
                $route_method = $route->getMethod($method);
                $route_method->setParams($params);

                return $route_method;
            }
        }

        return null;
    }


    /**
     * @param Route  $route
     * @param string $method
     * @param string $path
     * @return array|null
     */
    private function getRouteParams(Route $route, string $method, string $path):? array {

        $methods = array_keys($route->getMethods());

        if (in_array($method, $methods) || in_array('*', $methods)) {
            $path_regexp = $route->getPathRegexp();

            if (preg_match($path_regexp, $path, $matches)) {

                foreach ($matches as $key => $match) {
                    if (is_numeric($key)) {
                        unset($matches[$key]);
                    }
                }

                return $matches;
            }
        }

        return null;
    }
}