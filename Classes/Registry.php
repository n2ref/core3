<?php
namespace Rowan\Classes;

/**
 *
 */
class Registry {

    /**
     * @var array
     */
    private static $_service = [];


    /**
     * @param string $name
     * @return bool
     */
    public static function has(string $name): bool {

        if (self::$_service === null) {
            return false;
        }

        return isset(self::$_service[$name]);
    }


    /**
     * @param string $name
     * @return mixed
     */
    public static function get(string $name): mixed {

        return self::$_service[$name] ?? null;
    }


    /**
     * @param string $name
     * @param mixed  $service
     * @return void
     */
    public static function set(string $name, mixed $service): void {

        self::$_service[$name] = $service;
    }
}