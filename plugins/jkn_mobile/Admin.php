<?php

namespace Plugins\JKN_Mobile;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola' => 'index',
            'Pengaturan' => 'settings',
        ];
    }

    public function getIndex()
    {
        $title = 'Khanza JKN Mobile';
        return $this->draw('index.html', ['title' => $title]);
    }

    public function getSettings()
    {
        $this->assign['title'] = 'Pengaturan Modul JKN Mobile';
        $this->assign['jkn_mobile'] = htmlspecialchars_array($this->options('jkn_mobile'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['jkn_mobile'] as $key => $val) {
            $this->options('jkn_mobile', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'jkn_mobile', 'settings']));
    }

}
