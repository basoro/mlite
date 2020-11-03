<?php
namespace Plugins\Survei_Kepuasan;
use Systems\SiteModule;
class Site extends SiteModule
{
  public function routes()
  {
    $this->route('survei_kepuasan', 'getIndex');
    $this->route('survei_kepuasan/save', 'getSave');
  }

  public function getIndex()
  {
    echo $this->draw('index.html', ['notify' => $this->core->getNotify()]);
    exit();
  }

  public function getSave()
  {
    $this->db('lite_survei_kepuasan')->save([
      'opsi' => $_GET['opsi'],
      'tanggal' => date('Y-m-d H:i:s')
    ]);
    $this->notify('success', '<center><h4>Terima kasih telah mengisi survei!</h4></center>');
    redirect(url('survei_kepuasan'));
  }
}

 ?>
