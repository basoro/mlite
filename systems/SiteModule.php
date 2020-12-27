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
            $this->core->router->set($pattern, function () use ($callback) {
                return call_user_func_array([$this, $callback], func_get_args());
            });
        }
    }

    protected function setTemplate($file)
    {
        $this->core->template = $file;
    }
}
