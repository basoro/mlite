<?php

namespace Plugins\Dashboard;

use Systems\AdminModule;

class Admin extends AdminModule
{
  public function navigation()
  {
    return [
      'Main' => 'main',
    ];
  }

  public function getMain()
  {
    $this->core->addJS(url(MODULES . '/dashboard/js/admin/webcam.js?v={$mlite.version}'));
    $settings = $this->settings('settings');

    $day = array(
      'Sun' => 'AKHAD',
      'Mon' => 'SENIN',
      'Tue' => 'SELASA',
      'Wed' => 'RABU',
      'Thu' => 'KAMIS',
      'Fri' => 'JUMAT',
      'Sat' => 'SABTU'
    );
    $hari = $day[date('D', strtotime(date('Y-m-d')))];

    return $this->draw('main.html', [
      'settings' => $settings
    ]);
  }

  public function getMenu()
  {
    $this->core->addCSS(url(MODULES . '/dashboard/css/admin/dashboard.css?v={$mlite.version}'));
    $this->core->addJS(url(MODULES . '/dashboard/js/admin/dashboard.js?v={$mlite.version}'), 'footer');
    return $this->draw('dashboard.html', ['modules' => $this->_modulesList()]);
  }

  private function _modulesList()
  {
    $modules = array_column($this->db('mlite__modules')->asc('sequence')->toArray(), 'dir');
    $result = [];

    if ($this->core->getUserInfo('access') != 'all') {
      $modules = array_intersect($modules, explode(',', $this->core->getUserInfo('access')));
    }

    foreach ($modules as $name) {
      $files = [
        'info'  => MODULES . '/' . $name . '/Info.php',
        'admin' => MODULES . '/' . $name . '/Admin.php',
      ];

      if (file_exists($files['info']) && file_exists($files['admin'])) {
        $details        = $this->core->getModuleInfo($name);
        $features       = $this->core->getModuleNav($name);

        if (empty($features)) {
          continue;
        }

        $details['url'] = url([ADMIN, $name, array_shift($features)]);
        $details['dir'] = $name;

        $result[] = $details;
      }
    }
    return $result;
  }

  public function postChangeOrderOfNavItem()
  {
    foreach ($_POST as $module => $order) {
      $this->db('mlite__modules')->where('dir', $module)->save(['sequence' => $order]);
    }
    exit();
  }

  public function getHelp($dir)
  {
    $files = [
      'info'      => MODULES . '/' . $dir . '/Info.php',
      'help'    => MODULES . '/' . $dir . '/Help.md'
    ];

    $module = $this->core->getModuleInfo($dir);
    $module['description'] = $this->tpl->noParse($module['description']);

    // ReadMe.md
    if (file_exists($files['help'])) {
      $parsedown = new \Systems\Lib\Parsedown();
      $module['help'] = $parsedown->text($this->tpl->noParse(file_get_contents($files['help'])));
    }

    $this->tpl->set('module', $module);
    echo $this->tpl->draw(MODULES . '/modules/view/admin/help.html', true);
    exit();
  }

}
