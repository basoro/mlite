<?php

namespace Plugins\Anjungan;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Display' => 'index',
            'Pemanggil' => 'pemanggil',
            'Pengaturan' => 'settings',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Display', 'url' => url([ADMIN, 'anjungan', 'index']), 'icon' => 'desktop', 'desc' => 'Display-Display Informasi Anjungan'],
        ['name' => 'Pemanggil', 'url' => url([ADMIN, 'anjungan', 'pemanggil']), 'icon' => 'bullhorn', 'desc' => 'Pemanggil Antrian'],
        ['name' => 'Pengaturan', 'url' => url([ADMIN, 'anjungan', 'settings']), 'icon' => 'gear', 'desc' => 'Pengaturan Anjungan'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getIndex()
    {
        return $this->draw('index.html');
    }

    public function getPemanggil()
    {
      $title = 'Display Antrian Loket';
      $logo  = $this->settings->get('settings.logo');
      $display = '';

      $_username = '';
      $__username = 'Tamu';
      if(isset($_SESSION['mlite_user'])) {
        $_username = $this->core->getUserInfo('fullname', null, true);
        $__username = $this->core->getUserInfo('username');
      }
      $tanggal       = getDayIndonesia(date('Y-m-d')).', '.dateIndonesia(date('Y-m-d'));
      $username      = !empty($_username) ? $_username : $__username;

      $show = isset($_GET['show']) ? $_GET['show'] : "";
      switch($show){
        default:
        break;
        case "panggil_loket":
          $display = 'Panggil Loket';

          $_username = '';
          $__username = 'Tamu';
          if(isset($_SESSION['mlite_user'])) {
            $_username = $this->core->getUserInfo('fullname', null, true);
            $__username = $this->core->getUserInfo('username');
          }
          $tanggal       = getDayIndonesia(date('Y-m-d')).', '.dateIndonesia(date('Y-m-d'));
          $username      = !empty($_username) ? $_username : $__username;

          $setting_antrian_loket = str_replace(",","','", $this->settings->get('anjungan.antrian_loket'));
          $loket = explode(",", $this->settings->get('anjungan.antrian_loket'));
          $namaloket = 'a';
          $panggil_loket = 'panggil_loket';
          $get_antrian = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'Loket')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
          $noantrian = 0;
          if(!empty($get_antrian['noantrian'])) {
            $noantrian = $get_antrian['noantrian'];
          }

          $antriloket = $this->settings->get('anjungan.panggil_loket_nomor');
          $tcounter = $antriloket;
          $_tcounter = 1;
          if(!empty($tcounter)) {
            $_tcounter = $tcounter + 1;
          }
          if(isset($_GET['loket'])) {
            $this->db('mlite_antrian_loket')
              ->where('type', 'Loket')
              ->where('noantrian', $tcounter)
              ->where('postdate', date('Y-m-d'))
              ->save(['end_time' => date('H:i:s')]);
            $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_loket')->save(['value' => $_GET['loket']]);
            $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_loket_nomor')->save(['value' => $_tcounter]);
          }
          if(isset($_GET['antrian'])) {
            $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_loket')->save(['value' => $_GET['reset']]);
            $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_loket_nomor')->save(['value' => $_GET['antrian']]);
          }
          if(isset($_GET['no_rkm_medis'])) {
            $this->db('mlite_antrian_loket')->where('noantrian', $_GET['noantrian'])->where('postdate', date('Y-m-d'))->save(['no_rkm_medis' => $_GET['no_rkm_medis']]);
          }
          $hitung_antrian = $this->db('mlite_antrian_loket')
            ->where('type', 'Loket')
            ->like('postdate', date('Y-m-d'))
            ->toArray();
          $counter = strlen($tcounter);
          $xcounter = [];
          for($i=0;$i<$counter;$i++){
            $xcounter[] = '<audio id="suarabel'.$i.'" src="{?=url()?}/plugins/anjungan/suara/'.substr($tcounter,$i,1).'.wav" ></audio>';
          };

        break;
        case "panggil_cs":
          $display = 'Panggil CS';
          $loket = explode(",", $this->settings->get('anjungan.antrian_cs'));
          $namaloket = 'b';
          $panggil_loket = 'panggil_cs';
          $get_antrian = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'CS')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
          $noantrian = 0;
          if(!empty($get_antrian['noantrian'])) {
            $noantrian = $get_antrian['noantrian'];
          }

          $antriloket = $this->settings->get('anjungan.panggil_cs_nomor');
          $tcounter = $antriloket;
          $_tcounter = 1;
          if(!empty($tcounter)) {
            $_tcounter = $tcounter + 1;
          }
          if(isset($_GET['loket'])) {
            $this->db('mlite_antrian_loket')
              ->where('type', 'CS')
              ->where('noantrian', $tcounter)
              ->where('postdate', date('Y-m-d'))
              ->save(['end_time' => date('H:i:s')]);
            $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_cs')->save(['value' => $_GET['loket']]);
            $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_cs_nomor')->save(['value' => $_tcounter]);
          }
          if(isset($_GET['antrian'])) {
            $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_cs')->save(['value' => $_GET['reset']]);
            $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_cs_nomor')->save(['value' => $_GET['antrian']]);
          }
          $hitung_antrian = $this->db('mlite_antrian_loket')
            ->where('type', 'CS')
            ->like('postdate', date('Y-m-d'))
            ->toArray();
          $counter = strlen($tcounter);
          $xcounter = [];
          for($i=0;$i<$counter;$i++){
            $xcounter[] = '<audio id="suarabel'.$i.'" src="{?=url()?}/plugins/anjungan/suara/'.substr($tcounter,$i,1).'.wav" ></audio>';
          };

        break;
        case "panggil_apotek":
          $display = 'Panggil Apotek';
          $loket = explode(",", $this->settings->get('anjungan.antrian_apotek'));
          $namaloket = 'f';
          $panggil_loket = 'panggil_apotek';
          $get_antrian = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'Apotek')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
          $noantrian = 0;
          if(!empty($get_antrian['noantrian'])) {
            $noantrian = $get_antrian['noantrian'];
          }

          $antriloket = $this->settings->get('anjungan.panggil_apotek_nomor');
          $tcounter = $antriloket;
          $_tcounter = 1;
          if(!empty($tcounter)) {
            $_tcounter = $tcounter + 1;
          }
          if(isset($_GET['loket'])) {
            $this->db('mlite_antrian_loket')
              ->where('type', 'Apotek')
              ->where('noantrian', $tcounter)
              ->where('postdate', date('Y-m-d'))
              ->save(['end_time' => date('H:i:s')]);
            $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_apotek')->save(['value' => $_GET['loket']]);
            $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_apotek_nomor')->save(['value' => $_tcounter]);
          }
          if(isset($_GET['antrian'])) {
            $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_apotek')->save(['value' => $_GET['reset']]);
            $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_apotek_nomor')->save(['value' => $_GET['antrian']]);
          }
          $hitung_antrian = $this->db('mlite_antrian_loket')
            ->where('type', 'Apotek')
            ->like('postdate', date('Y-m-d'))
            ->toArray();
          $counter = strlen($tcounter);
          $xcounter = [];
          for($i=0;$i<$counter;$i++){
            $xcounter[] = '<audio id="suarabel'.$i.'" src="{?=url()?}/plugins/anjungan/suara/'.substr($tcounter,$i,1).'.wav" ></audio>';
          };

        break;
      }

      return $this->draw('pemanggil.html', [
        'title' => $title,
        'logo' => $logo,
        'powered' => 'Powered by <a href="https://mlite.id/">mLITE</a>',
        'username' => $username,
        'tanggal' => $tanggal,
        'show' => isset_or($show),
        'loket' => isset_or($loket),
        'namaloket' => isset_or($namaloket),
        'panggil_loket' => isset_or($panggil_loket),
        'antrian' => isset_or($tcounter),
        'hitung_antrian' => isset_or($hitung_antrian),
        'xcounter' => isset_or($xcounter),
        'noantrian' =>isset_or($noantrian),
        'display' => $display
      ]);
    }

    public function getSettings()
    {
        $this->assign['title'] = 'Pengaturan Modul Anjungan';
        $this->assign['poliklinik'] = $this->_getPoliklinik($this->settings->get('anjungan.display_poli'));
        $this->assign['penjab'] = $this->_getPenjab($this->settings->get('anjungan.carabayar'));

        $this->assign['anjungan'] = htmlspecialchars_array($this->settings('anjungan'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        $_POST['anjungan']['display_poli'] = implode(',', $_POST['anjungan']['display_poli']);
        $_POST['anjungan']['carabayar'] = implode(',', $_POST['anjungan']['carabayar']);
        foreach ($_POST['anjungan'] as $key => $val) {
            $this->settings('anjungan', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'anjungan', 'settings']));
    }

    private function _getPoliklinik($kd_poli = null)
    {
        $result = [];
        $rows = $this->db('poliklinik')->toArray();

        if (!$kd_poli) {
            $kd_poliArray = [];
        } else {
            $kd_poliArray = explode(',', $kd_poli);
        }

        foreach ($rows as $row) {
            if (empty($kd_poliArray)) {
                $attr = '';
            } else {
                if (in_array($row['kd_poli'], $kd_poliArray)) {
                    $attr = 'selected';
                } else {
                    $attr = '';
                }
            }
            $result[] = ['kd_poli' => $row['kd_poli'], 'nm_poli' => $row['nm_poli'], 'attr' => $attr];
        }
        return $result;
    }

    private function _getPenjab($kd_pj = null)
    {
        $result = [];
        $rows = $this->db('penjab')->where('status', '1')->toArray();

        if (!$kd_pj) {
            $kd_pjArray = [];
        } else {
            $kd_pjArray = explode(',', $kd_pj);
        }

        foreach ($rows as $row) {
            if (empty($kd_pjArray)) {
                $attr = '';
            } else {
                if (in_array($row['kd_pj'], $kd_pjArray)) {
                    $attr = 'selected';
                } else {
                    $attr = '';
                }
            }
            $result[] = ['kd_pj' => $row['kd_pj'], 'png_jawab' => $row['png_jawab'], 'attr' => $attr];
        }
        return $result;
    }

    public function getResetAnjunganLoket(){
      date_default_timezone_set($this->settings->get('settings.timezone'));
      $date = date('Y-m-d');
      $checkAnjungan = $this->db('mlite_antrian_loket')->where('postdate',$date)->oneArray();
      if($checkAnjungan){
        $this->db('mlite_antrian_loket')->where('postdate',$date)->delete();
        echo 'Berhasil Reset Antrian Anjungan Loket<br>';
        $checkAnjungan = $this->db('mlite_antrian_loket')->where('postdate',$date)->oneArray();
        if(!$checkAnjungan){
          $this->db('mlite_antrian_loket')->save([
            'type' => 'Loket',
            'noantrian' => '1',
            'postdate' => $date,
            'start_time' => date('H:i:s')
          ]);
        }
        echo 'Berhasil Memperbarui Antrian Anjungan Loket';
      } else {
        echo 'Antrian Anjungan Loket Tidak Ada Data';
      }
      exit();
    }

    public function getResetAnjunganCS(){
      date_default_timezone_set($this->settings->get('settings.timezone'));
      $date = date('Y-m-d');
      $checkAnjungan = $this->db('mlite_antrian_loket')->where('postdate',$date)->oneArray();
      if($checkAnjungan){
        $this->db('mlite_antrian_loket')->where('postdate',$date)->delete();
        echo 'Berhasil Reset Antrian Anjungan CS<br>';
        $checkAnjungan = $this->db('mlite_antrian_loket')->where('postdate',$date)->oneArray();
        if(!$checkAnjungan){
          $this->db('mlite_antrian_loket')->save([
            'type' => 'CS',
            'noantrian' => '1',
            'postdate' => $date,
            'start_time' => date('H:i:s')
          ]);
        }
        echo 'Berhasil Memperbarui Antrian Anjungan CS';
      } else {
        echo 'Antrian Anjungan CS Tidak Ada Data';
      }
      exit();
    }

    public function getResetAnjunganApotek(){
      date_default_timezone_set($this->settings->get('settings.timezone'));
      $date = date('Y-m-d');
      $checkAnjungan = $this->db('mlite_antrian_loket')->where('postdate',$date)->oneArray();
      if($checkAnjungan){
        $this->db('mlite_antrian_loket')->where('postdate',$date)->delete();
        echo 'Berhasil Reset Antrian Anjungan Apotek<br>';
        $checkAnjungan = $this->db('mlite_antrian_loket')->where('postdate',$date)->oneArray();
        if(!$checkAnjungan){
          $this->db('mlite_antrian_loket')->save([
            'type' => 'Apotek',
            'noantrian' => '1',
            'postdate' => $date,
            'start_time' => date('H:i:s')
          ]);
        }
        echo 'Berhasil Memperbarui Antrian Anjungan Apotek';
      } else {
        echo 'Antrian Anjungan Apotek Tidak Ada Data';
      }
      exit();
    }

}
