<?php

namespace Plugins\Lisensi;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Manage' => 'manage'
        ];
    }

    public function getManage()
    {
      $this->_addHeaderFiles();
      $data_rs = $this->core->mysql('mlite_data_rs')->toArray();
      return $this->draw('manage.html', ['list' => $data_rs]);
    }

    public function postInsert()
    {
      $kode_lisensi = $_POST["license_key"];
      $cek_email = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM mlie_data_rs WHERE email = '".$_POST['email']."'"));
      if($cek_email == "") {
  	  $query = "INSERT INTO data_rs (`id`, `nama_instansi`, `alamat_instansi`, `kabupaten`, `propinsi`, `kontak`, `email`, `kode_ppk`, `kode_ppkinhealth`, `kode_ppkkemenkes`, `kode_lisensi`) VALUES (NULL, '".$_POST["nama_instansi"]."', '".$_POST["alamat_instansi"]."', '".$_POST["kabupaten"]."', '".$_POST["propinsi"]."', '".$_POST["kontak"]."', '".$_POST["email"]."', '".$_POST["kode_ppk"]."', '".$_POST["kode_ppkinhealth"]."', '".$_POST["kode_ppkkemenkes"]."', '')";
          mysqli_query($conn,$query);
      } else {
          $query = "UPDATE mlie_data_rs SET nama_instansi = '".$_POST["nama_instansi"]."', alamat_instansi = '".$_POST["alamat_instansi"]."', kabupaten = '".$_POST["kabupaten"]."', propinsi = '".$_POST["propinsi"]."', kontak = '".$_POST["kontak"]."', email = '".$_POST["email"]."', kode_ppk = '".$_POST["kode_ppk"]."', kode_ppkinhealth = '".$_POST["kode_ppkinhealth"]."', kode_ppkkemenkes = '".$_POST["kode_ppkkemenkes"]."' WHERE email = '".$_POST["email"]."'";
          mysqli_query($conn,$query);
      }
    }

    public function getCek()
    {
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
    }

}
