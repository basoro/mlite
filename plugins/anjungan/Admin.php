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
            'Pengaturan' => 'settings',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Display', 'url' => url([ADMIN, 'anjungan', 'index']), 'icon' => 'desktop', 'desc' => 'Display-Display Informasi Anjungan'],
        ['name' => 'Pengaturan', 'url' => url([ADMIN, 'anjungan', 'settings']), 'icon' => 'desktop', 'desc' => 'Pengaturan Anjungan'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getIndex()
    {
        return $this->draw('index.html');
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
