<?php

namespace Plugins\Afm;

use Systems\AdminModule;

class Admin extends AdminModule
{
  public function navigation()
  {
      return [
          'Kelola' => 'manage'
      ];
  }

  public function getManage()
  {
      $this->assign['title'] = 'Pengaturan Modul API AFM';
      $this->assign['afm'] = htmlspecialchars_array($this->settings('afm'));
      return $this->draw('settings.html', ['settings' => $this->assign]);
  }

  public function postSaveSettings()
  {
      foreach ($_POST['afm'] as $key => $val) {
          $this->settings('afm', $key, $val);
      }
      $this->notify('success', 'Pengaturan telah disimpan');
      redirect(url([ADMIN, 'afm', 'manage']));
  }
}
