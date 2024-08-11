<?php
namespace Core3\Classes\Init;
use Core3\Classes\Init\Router\Path;


/**
 *
 */
class Router {

    /**
     * @var array
     */
    private array $paths = [];


    /**
     * @param string $query
     * @param array  $params
     * @return Path
     */
    public function addPath(string $query, array $params = []): Path {

        $path = new Path($query, $params);
        $this->paths[] = $path;

        return $path;
    }


    /**
     * @param string $method
     * @param string $query
     * @return array|null
     */
    public function getRoute(string $method, string $query):? array {

        foreach ($this->paths as $path) {
            if ($path instanceof Path) {
                $route = $path->getRoute($method, $query);

                if ($route) {
                    return $route;
                }
            }
        }

        return null;
    }
}