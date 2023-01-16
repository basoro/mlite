<?php

namespace Systems\Lib;

class Router
{

    private $routes = array();

    private $patterns = array(
        ':any' => '.*',
        ':int' => '[0-9]+',
        ':str' => '[a-zA-Z0-9_-]+',
    );

    public function set($pattern, $callback)
    {
        $pattern = str_replace('/', '\/', $pattern);

        $this->routes[$pattern] = $callback;
    }

    public function execute($returnPath = false)
    {
        if (empty($path) && empty($_SERVER['PATH_INFO'])) {
            $_SERVER['PATH_INFO'] = explode("?", $_SERVER['REQUEST_URI'])[0];
        }

        $url = rtrim(dirname($_SERVER["SCRIPT_NAME"]), '/');
        $url = trim(preg_replace('#'.$url.'#', '', $_SERVER['PATH_INFO'], 1), '/');

        if ($returnPath) {
            return $url;
        }

        $patterns = '/('.implode('|', array_keys($this->patterns)).')/';
        uksort($this->routes, function ($a, $b) use ($patterns) {
            $pointsA = preg_match_all('/(\/)/', $a);
            $pointsB = preg_match_all('/(\/)/', $b);

            if ($pointsA == $pointsB) {
                $pointsA = preg_match_all($patterns, $a);
                $pointsB = preg_match_all($patterns, $b);
            }

            return $pointsA > $pointsB;
        });

        foreach ($this->routes as $pattern => $callback) {
            if (strpos($pattern, ':') !== false) {
                $pattern = str_replace(array_keys($this->patterns), array_values($this->patterns), $pattern);
            }
            if (preg_match('#^'.$pattern.'$#', $url, $params) === 1) {
                array_shift($params);
                array_walk($params, function (&$val) {
                    $val = $val ?: null;
                });

                return call_user_func_array($callback, array_values($params));
            }
        }

        Event::call('router.notfound');
    }

    public function changeRoute($path)
    {
        $_SERVER['PATH_INFO'] = $path;
    }
}
