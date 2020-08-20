<?php

namespace Plugins\Themes;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Index' => 'index',
        ];
    }

    public function anyIndex($theme = null, $file = null)
    {
        $this->_addHeaderFiles();
        $text = 'Hello World';
        if (empty($theme) && empty($file)) {
            $this->tpl->set('settings', $this->options('settings'));
            $this->tpl->set('themes', $this->_getThemes());
            return $this->draw('index.html', ['text' => $text]);
        } else {
            if ($file == 'activate') {
                $this->db('lite_options')->where('module', 'settings')->where('field', 'theme')->save(['value' => $theme]);
                $this->notify('success', 'Pengaturan teme sukses');
                redirect(url([ADMIN, 'themes', 'index']));
            }

            $this->tpl->set('settings', $this->options->get('settings'));
            $this->tpl->set('theme', array_merge($this->_getThemes($theme), $this->assign));
            return $this->draw('theme.html', ['text' => $text]);
        }
    }

    private function _getThemes($theme = null)
    {
        $themes = glob(THEMES.'/*', GLOB_ONLYDIR);
        $return = [];
        foreach ($themes as $e) {
            if ($e != THEMES.'/admin') {
                $theme_info = array_fill_keys(['name', 'version', 'author', 'email', 'thumb'], 'Unknown');
                $theme_info['name'] = basename($e);
                $theme_info['thumb'] = '../admin/img/unknown_theme.png';

                if (file_exists($e.'/info.json')) {
                    $theme_info = array_merge($theme_info, json_decode(file_get_contents($e.'/info.json'), true));
                }

                if ($theme == basename($e)) {
                    return array_merge($theme_info, ['dir' => basename($e)]);
                }

                $return[] = array_merge($theme_info, ['dir' => basename($e)]);
            }
        }

        return $return;
    }

    private function _updateSettings($field, $value)
    {
        return $this->settings('settings', $field, $value);
    }

    private function rcopy($source, $dest, $permissions = 0755, $expect = [])
    {
        foreach ($expect as $e) {
            if ($e == $source) {
                return;
            }
        }

        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        if (is_file($source)) {
            if (!is_dir(dirname($dest))) {
                mkdir(dirname($dest), 0777, true);
            }

            return copy($source, $dest);
        }

        if (!is_dir($dest)) {
            mkdir($dest, $permissions, true);
        }

        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            $this->rcopy("$source/$entry", "$dest/$entry", $permissions, $expect);
        }

        $dir->close();
        return true;
    }

    private function rglob($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->rglob($dir.'/'.basename($pattern), $flags));
        }
        return $files;
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/themes/js/admin/app.js');
        exit();
    }

    private function _addHeaderFiles()
    {

        // CSS
        $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'), 'footer');

        // MODULE SCRIPTS
        $this->core->addJS(url([ADMIN, 'themes', 'javascript']), 'footer');
    }
}
