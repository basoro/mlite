<?php

namespace Systems\Lib;

class Widget
{

    protected static $widgets = [];

    public static function add($name, callable $callback)
    {
        static::$widgets[$name][] = $callback;
    }

    public static function call($name, $params = [])
    {
        $result = [];
        foreach (isset_or(static::$widgets[$name], []) as $widget) {
            $content = call_user_func_array($widget, $params);
            if (is_string($content)) {
                $result[] = $content;
            }
        }

        return implode("\n", $result);
    }
}
