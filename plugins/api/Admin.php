<?php

namespace Plugins\Api;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Pengaturan APAM' => 'settingsapam',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Pengaturan APAM', 'url' => url([ADMIN, 'api', 'settingsapam']), 'icon' => 'database', 'desc' => 'Pengaturan APAM API'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    /* Settings Section */
    public function getSettingsApam()
    {
        $this->assign['title'] = 'Pengaturan Modul API';
        $this->assign['api'] = htmlspecialchars_array($this->settings('api'));
        return $this->draw('settings.apam.html', ['settings' => $this->assign]);
    }

    public function postSaveSettingsApam()
    {
        foreach ($_POST['api'] as $key => $val) {
            $this->settings('api', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'api', 'settingsapam']));
    }
    /* End Settings Farmasi Section */

}
