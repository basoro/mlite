<?php

namespace Plugins\Api;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Notifikasi APAM' => 'notifikasi',
            'Pengaturan APAM' => 'settingsapam',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Notifikasi APAM', 'url' => url([ADMIN, 'api', 'notifikasi']), 'icon' => 'database', 'desc' => 'Notifikasi APAM API'],
        ['name' => 'Pengaturan APAM', 'url' => url([ADMIN, 'api', 'settingsapam']), 'icon' => 'database', 'desc' => 'Pengaturan APAM API'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getNotifikasi()
    {

      $totalRecords = $this->db('mlite_notifications')
        ->select('id')
        ->toArray();
      $offset         = 10;
      $return['halaman']    = 1;
      $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
      $return['jumlah_data']    = count($totalRecords);

      $return['list'] = $this->db('mlite_notifications')
        ->join('pasien', 'pasien.no_rkm_medis=mlite_notifications.no_rkm_medis')
        ->desc('id')
        ->limit(10)
        ->toArray();

      $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
      $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
      $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

      $this->core->addJS(url([ADMIN, 'api', 'notifikasijs']), 'footer');
      return $this->draw('notifikasi.html', [
        'notifikasi' => $return
      ]);

    }

    public function anyNotifikasiForm()
    {
        if (isset($_POST['id'])){
          $return['form'] = $this->db('mlite_notifications')->where('id', $_POST['id'])->oneArray();
        } else {
          $return['form'] = [
            'id' => '',
            'judul' => '',
            'pesan' => '',
            'tanggal' => '',
            'no_rkm_medis' => '',
            'status' => ''
          ];
        }

        echo $this->draw('notifikasi.form.html', ['notifikasi' => $return]);
        exit();
    }

    public function anyNotifikasiDisplay()
    {

        $perpage = '10';
        $totalRecords = $this->db('mlite_notifications')
          ->select('id')
          ->toArray();
        $offset         = 10;
        $return['halaman']    = 1;
        $return['jml_halaman']    = ceil(count($totalRecords) / $offset);
        $return['jumlah_data']    = count($totalRecords);

        $return['list'] = $this->db('mlite_notifications')
          ->join('pasien', 'pasien.no_rkm_medis=mlite_notifications.no_rkm_medis')
          ->desc('id')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        if(isset($_POST['cari'])) {
          $return['list'] = $this->db('mlite_notifications')
            ->join('pasien', 'pasien.no_rkm_medis=mlite_notifications.no_rkm_medis')
            ->like('id', '%'.$_POST['cari'].'%')
            ->orLike('judul', '%'.$_POST['cari'].'%')
            ->desc('id')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($return['list']);
          $jml_halaman = ceil($jumlah_data / $offset);
        }
        if(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $return['list'] = $this->db('mlite_notifications')
            ->join('pasien', 'pasien.no_rkm_medis=mlite_notifications.no_rkm_medis')
            ->desc('id')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $return['halaman'] = $_POST['halaman'];
        }

        echo $this->draw('notifikasi.display.html', ['notifikasi' => $return]);
        exit();
    }

    public function postNotifikasiSave()
    {
      if (!$this->db('mlite_notifications')->where('id', $_POST['id'])->oneArray()) {
        $_POST['status'] = 'unread';
        $query = $this->db('mlite_notifications')->save($_POST);
      } else {
        $query = $this->db('mlite_notifications')->where('id', $_POST['id'])->save($_POST);
      }
      return $query;
    }

    public function postNotifikasiHapus()
    {
      return $this->db('mlite_notifications')->where('id', $_POST['id'])->delete();
    }

    public function getNotifikasiJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/api/js/admin/notifikasi.js');
        exit();
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
