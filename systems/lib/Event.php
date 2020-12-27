<?php

namespace Systems\Lib;


class Event
{

    protected static $events = [];


    public static function add($name, callable $callback)
    {
        static::$events[$name][] = $callback;
    }


    public static function call($name, array $params = [])
    {
        $return = true;
        foreach (isset_or(static::$events[$name], []) as $value) {
            $return = $return && (call_user_func_array($value, $params) !== false);
        }
        return $return;
    }
}
