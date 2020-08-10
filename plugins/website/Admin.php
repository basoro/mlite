<?php

namespace Plugins\Website;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Kelola' => 'index',
            'Pengaturan' => 'settings'
        ];
    }

    public function getIndex()
    {
        $text = 'Website Module';
        return $this->draw('index.html', ['text' => $text]);
    }

    public function getSettings()
    {
        $this->assign['title'] = 'Pengaturan Modul Website';
        $this->assign['website'] = htmlspecialchars_array($this->options('website'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['website'] as $key => $val) {
            $this->options('website', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'website', 'settings']));
    }

}
