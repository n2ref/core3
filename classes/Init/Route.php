<?php
namespace Core3\Classes\Init;

/**
 *
 */
class Route {

    private string         $method;
    private string         $path;
    private ?array         $params;
    private array|\Closure $callback;


    /**
     * @param string         $method
     * @param string         $path
     * @param array|\Closure $callback
     * @param array|null     $params
     * @throws \Exception
     */
    public function __construct(string $method, string $path, array|\Closure $callback, array $params = null) {

        if (is_array($callback)) {
            $correct = ! empty($callback[0]) &&
                       ! empty($callback[1]) &&
                       is_string($callback[1]);

            if ($correct) {
                if (is_string($callback[0])) {
                    if ( ! class_exists($callback[0])) {
                        $correct = false;
                    }

                } elseif ( ! is_object($callback[0])) {
                    $correct = false;
                }
            }

            if ( ! $correct) {
                throw new \Exception('Error callback param');
            }
        }

        $this->method   = $method;
        $this->path     = $path;
        $this->params   = $params;
        $this->callback = $callback;
    }


    /**
     * @return string
     */
    public function getMethod(): string {
        return $this->method;
    }


    /**
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }


    /**
     * @return array|null
     */
    public function getParams():? array {
        return $this->params;
    }


    /**
     * @return array|\Closure
     */
    public function getCallback(): array|\Closure {
        return $this->callback;
    }


    /**
     * @param array|null $params
     * @return mixed
     */
    public function run(array $params = null): mixed {

        $params = $params ?: $this->params;
        $params = $params ?: [];

        if (is_array($this->callback)) {
            if (is_object($this->callback[0])) {
                return call_user_func_array([$this->callback[0], $this->callback[1]], $params);
            } else {
                return call_user_func_array([new $this->callback[0](), $this->callback[1]], $params);
            }

        } else {
            return call_user_func_array($this->callback, $params);
        }
    }
}