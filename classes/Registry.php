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
    private static $_service;


    /**
     * @param string $name
     * @return bool
     */
    public static function has(string $name): bool {

        if (self::$_service === null) {
            return false;
        }

        return self::$_service->has($name);
    }


    /**
     * @param string $name
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function get(string $name): mixed {

        $instance = self::getRealInstance();
        return $instance->get($name);
    }


    /**
     * @param string $name
     * @param mixed  $service
     * @return void
     */
    public static function set(string $name, mixed $service): void {

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