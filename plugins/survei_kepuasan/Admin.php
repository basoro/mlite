<?php
namespace Plugins\Survei_Kepuasan;

use Systems\AdminModule;

class Admin extends AdminModule
{
  public function navigation()
  {
    return[
      'Manage' => 'manage'
    ];
  }

  public function getManage()
  {

    $this->core->addCSS(url(BASE_DIR.'/assets/css/dataTables.bootstrap.min.css'));
    $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));
    $this->core->addJS(url(BASE_DIR.'/assets/jscripts/jquery.dataTables.min.js'));
    $this->core->addJS(url(BASE_DIR.'/assets/jscripts/dataTables.bootstrap.min.js'));

    return $this->draw('manage.html', [
      'puas' => $this->db('lite_survei_kepuasan')->select(['count' => 'COUNT(DISTINCT(id))'])->where('opsi', '1')->oneArray(),
      'tidak_puas' => $this->db('lite_survei_kepuasan')->select(['count' => 'COUNT(DISTINCT(id))'])->where('opsi', '2')->oneArray(),
      'list' => $this->db('lite_survei_kepuasan')->toArray()
    ]);
  }
}

 ?>
