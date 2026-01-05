<?php

namespace Systems;

abstract class SiteModule extends BaseModule
{

    public function routes()
    {
    }

    protected function route($pattern, $callback)
    {
        if (is_callable($callback)) {
            $this->core->router->set($pattern, $callback);
        } else {
            $this->core->router->set($pattern, function (...$args) use ($callback) {
                return $this->$callback(...$args);
            });
        }
    }

    protected function api($pattern, $callback)
    {
        $this->core->router->set($pattern, function (...$args) use ($callback) {
            // JWT Check
            $token = null;
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                if (preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
                    $token = $matches[1];
                }
            } elseif (function_exists('getallheaders')) {
                $headers = getallheaders();
                if (isset($headers['Authorization'])) {
                    if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                        $token = $matches[1];
                    }
                }
            }

            if (!$token || !($payload = \Systems\Lib\Jwt::verify($token, JWT_SECRET))) {
                 header('Content-Type: application/json');
                 http_response_code(401);
                 echo json_encode(['error' => 'Unauthorized']);
                 exit;
            }

            // Call original callback
            if (is_callable($callback)) {
                 $result = $callback(...$args);
            } else {
                 $result = $this->$callback(...$args);
            }
            
            header('Content-Type: application/json');
            if (is_array($result) || is_object($result)) {
                echo json_encode($result);
            } else {
                echo $result;
            }
            exit;
        });
    }

    protected function setTemplate($file)
    {
        $this->core->template = $file;
    }
}
