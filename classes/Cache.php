<?php
namespace Core3\Classes;
use Laminas\Cache\Storage;

/**
 *
 */
class Cache {
    private $adapter;
    private $namespace;


    /**
     * @param Storage\Adapter\AbstractAdapter $adapter
     */
    public function __construct(Storage\Adapter\AbstractAdapter $adapter) {

        $this->adapter = $adapter;

        $this->namespace = $this->adapter->getOptions()->getNamespace();
    }


    /**
     * call native methods
     * @link https://docs.zendframework.com/zend-cache/storage/adapter/#the-storageinterface
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments = []) {

        return call_user_func_array(array($this->adapter, $name), $arguments);
    }


    /**
     * @param string $key
     * @return bool
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    public function test(string $key): bool {

        return $this->adapter->hasItem($key);
    }


    /**
     * add tags to the key
     * @param string $key
     * @param array  $tags
     * @return bool
     */
    public function setTags(string $key, array $tags): bool {

        if (method_exists($this->adapter, 'setTags')) {
            return $this->adapter->setTags($key, $tags);
        }
        //TODO сделать тэгирование

        return false;
    }


    /**
     * @param string $key
     * @return mixed
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    public function load(string $key): mixed {

        return $this->adapter->getItem($key);
    }


    /**
     * @param string $key
     * @param mixed  $data
     * @param array  $tags
     * @return void
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    public function save(string $key, mixed $data, array $tags = []): void {

        $this->adapter->setItem($key, $data);

        if ($tags) {
            $this->setTags($key, $tags);
        }
    }


    /**
     * @param string $key
     * @return void
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    public function clear(string $key = ''): void {

        if ($key) {
            $this->adapter->removeItem($key);
        } else {
            $this->adapter->clearByNamespace($this->namespace);
        }
    }


    /**
     * @param array $tags
     * @return void
     */
    public function clearByTags(array $tags): void {

        if (method_exists($this->adapter, 'clearByTags')) {
            $this->adapter->clearByTags($tags);

        } else {
            //TODO сделать очистку по тэгам
            $this->adapter->clearByNamespace($this->namespace);
        }
    }


    /**
     * @param string $key
     * @return void
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    public function remove(string $key): void {

        if (is_array($key)) {
            $this->adapter->removeItems($key);
        } else {
            $this->adapter->removeItem($key);
        }
    }
}