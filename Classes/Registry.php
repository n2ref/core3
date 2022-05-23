<?php
namespace Core3\Classes;
use Laminas\ServiceManager\ServiceManager;

/**
 *
 */
class Registry {

    /**
     * @var ServiceManager
     */
    private static ServiceManager $_service;


    /**
     * @param $index
     * @return bool
     */
    public static function has($index): bool {

        if (self::$_service === null) {
            return false;
        }

        return self::$_service->has($index);
    }


    /**
     * @param $name
     * @return array|mixed|object
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function get($name) {

        $instance = self::getRealInstance();
        return $instance->get($name);
    }


    /**
     * @param $name
     * @param $service
     * @return void
     */
    public static function set($name, $service): void {

        $instance = self::getRealInstance();
        $instance->setService($name, $service);
    }


    /**
     * @return ServiceManager
     */
    private static function getRealInstance(): ServiceManager {

        if (self::$_service === null) {
            self::$_service = new ServiceManager();
            self::$_service->setAllowOverride(true); // можем создавать новые сервисы в любое время
        }

        return self::$_service;
    }
}