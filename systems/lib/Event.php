<?php

namespace Systems\Lib;


class Event
{

    protected static $events = [];


    public static function add($name, callable $callback)
    {
        static::$events[$name][] = $callback;
    }


    /**
     * Call event callbacks with parameters
     * Compatible with PHP 8+ variadic parameters
     * 
     * @param string $name Event name
     * @param array $params Parameters array
     * @return bool
     */
    public static function call(string $name, array $params = []): bool
    {
        $return = true;
        foreach (isset_or(static::$events[$name], []) as $value) {
            $return = $return && ($value(...$params) !== false);
        }
        return $return;
    }
}
