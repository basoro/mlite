<?php

namespace Systems\Lib;

class Settings
{

    protected $core;

    protected $cache = [];

    public function __construct(\Systems\Main $core)
    {
        $this->core = $core;
        $this->reload();
    }

    public function all()
    {
        return $this->cache;
    }

    public function reload()
    {
        $results = $this->core->db->select('mlite_settings', '*');
        foreach ($results as $result) {
            $this->cache[$result['module']][$result['field']] = $result['value'];
        }
    }

    public function get($module, $field = false)
    {
        if (substr_count($module, '.') == 1) {
            list($module, $field) = explode('.', $module);
        }

        if (empty($field)) {
            return $this->cache[$module];
        }

        return $this->cache[$module][$field];
    }

    public function set($module, $field, $value = false)
    {
        if (substr_count($module, '.') == 1) {
            $value = $field;
            list($module, $field) = explode('.', $module);
        }

        if ($value === false) {
            throw new \Exception('Value cannot be empty');
        }

        $isMliteSettings = $this->core->db->has('mlite_settings', ['module' => $module, 'field' => $field, 'value' => $value]);
        if ($isMliteSettings) {
            $this->cache[$module][$field] = $value;
            return true;
        }

        return false;
    }
}
