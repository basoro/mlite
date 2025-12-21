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

    protected function setTemplate($file)
    {
        $this->core->template = $file;
    }
}
