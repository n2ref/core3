<?php
namespace Core3\Classes\Router;

/**
 *
 */
class Method {

    private array          $params = [];
    private array|\Closure $action = [];


    /**
     * @param array|\Closure $action
     */
    public function __construct(array|\Closure $action) {

        $this->action = $action;
    }


    /**
     * @param array $params
     * @return array
     */
    public function getParams(array $params): array {

        return $this->params;
    }


    /**
     * @param array $params
     * @return $this
     */
    public function setParams(array $params): self {

        $this->params = array_values($params);
        return $this;
    }


    /**
     * @param mixed $param
     * @return $this
     */
    public function appendParam(mixed $param): self {

        $this->params[] = $param;
        return $this;
    }


    /**
     * @param mixed $param
     * @return $this
     */
    public function prependParam(mixed $param): self {

        array_unshift($this->params, $param);
        return $this;
    }


    /**
     * @return mixed
     */
    public function run(): mixed {

        if (is_array($this->action)) {
            if (is_object($this->action[0])) {
                return call_user_func_array([$this->action[0], $this->action[1]], $this->params);
            } else {
                return call_user_func_array([new $this->action[0](), $this->action[1]], $this->params);
            }

        } else {
            return call_user_func_array($this->action, $this->params);
        }
    }
}