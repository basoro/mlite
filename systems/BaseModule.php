<?php

namespace Systems;

class BaseModule
{
    protected $core;
    protected $tpl;
    protected $router;
    protected $settings;
    protected $name;

    public function __construct(Main $core)
    {
        $this->core = $core;
        $this->tpl = $core->tpl;
        $this->router = $core->router;
        $this->settings = $core->settings;
        $this->name = strtolower(str_replace(['Plugins\\', '\\Admin', '\\Site'], '', static::class));
    }

    public function init()
    {
    }

    public function finish()
    {
    }

    protected function draw($file, array $variables = [])
    {
        if (!empty($variables)) {
            foreach ($variables as $key => $value) {
                $this->tpl->set($key, $value);
            }
        }

        if (strpos($file, BASE_DIR) !== 0) {
            if ($this instanceof AdminModule) {
                $file = MODULES.'/'.$this->name.'/view/admin/'.$file;
            } else {
                $file = MODULES.'/'.$this->name.'/view/'.$file;
            }
        }

        return $this->tpl->draw($file);
    }

    protected function settings($module, $field = false, $value = false)
    {
        if (substr_count($module, '.') == 1) {
            $value = $field;
            list($module, $field) = explode('.', $module);
        }

        if ($value === false) {
            return $this->settings->get($module, $field);
        } else {
            return $this->settings->set($module, $field, $value);
        }
    }

    protected function db($table = null)
    {
        return $this->core->db($table);
    }

    protected function notify()
    {
        call_user_func_array([$this->core, 'setNotify'], func_get_args());
    }
}
