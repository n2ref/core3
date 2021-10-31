<?php

namespace Core3;


/**
 * Class Cache
 * backward compatibility for Zend\Cache
 * @package Core2
 */
class Cache {

    private $adapter;
    const NS = 'Core2';


    /**
     * Cache constructor.
     * @link https://docs.zendframework.com/zend-cache/storage/adapter/
     * @param $adapter
     */
    public function __construct($adapter) {

        $this->adapter = $adapter;
    }


    /**
     * call native methods
     * @link https://docs.zendframework.com/zend-cache/storage/adapter/#the-storageinterface
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments) {

        return call_user_func_array([$this->adapter, $name], $arguments);
    }


    /**
     * @param $key
     * @return mixed
     */
    public function test($key) {

        return $this->adapter->hasItem($key);
    }


    /**
     * @param $key
     * @return bool
     */
    public function load($key) {

        $data = $this->adapter->getItem($key);
        if ( ! $data) $data = false; //совместимость с проверкой от zf1

        return $data;
    }


    /**
     * @param       $data
     * @param       $key
     * @param array $tags
     */
    public function save($data, $key, $tags = []) {

        $this->adapter->setItem($key, $data);
        if ($tags) $this->adapter->setTags($key, $tags);
    }


    /**
     * @param       $mode
     * @param array $tags
     */
    public function clean($mode = '', $tags = []) {

        if ($tags) $this->adapter->clearByTags($tags); else {
            $this->adapter->clearByNamespace(self::NS);
        }
    }


    /**
     * @param $key
     */
    public function remove($key) {

        if (is_array($key)) $this->adapter->removeItems($key); else $this->adapter->removeItem($key);
    }

}