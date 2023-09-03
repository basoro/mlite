<?php

namespace Systems\Lib;

class ModulesCollection
{

    protected $modules = [];

    public function __construct($core)
    {
        $modules = array_column($core->db('mlite_modules')->asc('sequence')->toArray(), 'dir');
        if ($core instanceof \Systems\Admin) {
            $clsName = 'Admin';
        } else {
            $clsName = 'Site';
        }

        foreach ($modules as $dir) {
            $file = MODULES.'/'.$dir.'/'.$clsName.'.php';
            if (file_exists($file)) {
                $namespace = 'Plugins\\'.$dir.'\\'.$clsName;
                $this->modules[$dir] = new $namespace($core);
            }
        }

        // Init loop
        $this->initLoop();

        // Routes loop for Site
        if ($clsName != 'Admin') {
            $this->routesLoop();
        }
    }

    protected function initLoop()
    {
        foreach ($this->modules as $module) {
            $module->init();
        }
    }

    protected function routesLoop()
    {
        foreach ($this->modules as $module) {
            $module->routes();
        }
    }

    public function finishLoop()
    {
        foreach ($this->modules as $module) {
            $module->finish();
        }
    }

    public function getArray()
    {
        return $this->modules;
    }

    public function has($name)
    {
        return array_key_exists($name, $this->modules);
    }

    public function __get($module)
    {
        if (isset($this->modules[$module])) {
            return $this->modules[$module];
        } else {
            return null;
        }
    }
}
