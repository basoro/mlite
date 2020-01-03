<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : dentix.id@gmail.com
* Licence under GPL
***/

if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
   header("HTTP/1.0 403 Forbidden");
   exit;
}

ob_start();
session_start();

include ('../config.php');
include ('../init.php');

$page = isset($_GET['p'])? $_GET['p'] : '';

if($page=='add'){
  if(!empty($_POST['no_rkm_medis'])){
      $data = array();
      $_POST['tgl_registrasi'] = date('Y-m-d', strtotime($_POST['tgl_registrasi']));
      // get data pasien
      $get_pasien = fetch_array(query("SELECT * FROM pasien WHERE no_rkm_medis = '{$_POST['no_rkm_medis']}'"));
      // set format tanggal
      $tgl_reg = date('Y/m/d', strtotime($_POST['tgl_registrasi']));
      //mencari no rawat terakhir
      $no_rawat_akhir = fetch_array(query("SELECT max(no_rawat) FROM reg_periksa WHERE tgl_registrasi='$_POST[tgl_registrasi]'"));
      $no_urut_rawat = substr($no_rawat_akhir[0], 11, 6);
      $no_rawat = $tgl_reg.'/'.sprintf('%06s', ($no_urut_rawat + 1));
      //mencari no reg terakhir
      $no_reg_akhir = fetch_array(query("SELECT max(no_reg) FROM reg_periksa WHERE kd_dokter='$_POST[kd_dokter]' and tgl_registrasi='$_POST[tgl_registrasi]'"));
      if($no_reg_akhir[0] == NULL) {
          $no_reg = '001';
      } else {
        $no_urut_reg = substr($no_reg_akhir[0], 0, 3);
        $no_reg = sprintf('%03s', ($no_urut_reg + 1));
      }
      // get biaya
      $biaya_reg=fetch_array(query("SELECT registrasilama FROM poliklinik WHERE kd_poli='{$_POST['kd_poli']}'"));
      //menentukan umur sekarang
      list($cY, $cm, $cd) = explode('-', date('Y-m-d'));
      list($Y, $m, $d) = explode('-', date('Y-m-d', strtotime($get_pasien['tgl_lahir'])));
      $umurdaftar = $cY - $Y;

      $insert = query("INSERT
          INTO
              reg_periksa
          SET
              no_reg          = '$no_reg',
              no_rawat        = '$no_rawat',
              tgl_registrasi  = '$tgl_reg',
              jam_reg         = '$time',
              kd_dokter       = '{$_POST['kd_dokter']}',
              no_rkm_medis    = '{$_POST['no_rkm_medis']}',
              kd_poli         = '{$_POST['kd_poli']}',
              p_jawab         = '{$_POST['namakeluarga']}',
              almt_pj         = '{$_POST['alamatpj']}',
              hubunganpj      = '-',
              biaya_reg       = '{$biaya_reg['registrasilama']}',
              stts            = 'Belum',
              stts_daftar     = 'Lama',
              status_lanjut   = 'Ralan',
              kd_pj           = '{$_POST['kd_pj']}',
              umurdaftar      = '{$umurdaftar}',
              sttsumur        = 'Th',
              status_bayar    = 'Belum Bayar',
              status_poli     = 'Lama'
      ");
      $insert_perujuk = query("INSERT
          INTO
              rujuk_masuk
          SET
              no_rawat        = '$no_rawat',
              perujuk         = '{$_POST['nama_perujuk']}',
              alamat          = '-',
              no_rujuk        = '-',
              jm_perujuk      = '0',
              dokter_perujuk  = '{$_POST['nama_perujuk']}',
              kd_penyakit     = '-',
              kategori_rujuk  = '-',
              keterangan      = '-',
              no_balasan      = '-'
      ");
  }
} else if($page=='update'){
  if(!empty($_POST['no_rawat'])){
      $data = array();
      $kd_dokter = $_POST['kd_dokter'];
      // get data pasien
      $get_pasien = fetch_array(query("SELECT * FROM pasien WHERE no_rkm_medis = '{$_POST['no_rkm_medis']}'"));
      // get biaya
      $biaya_reg=fetch_array(query("SELECT registrasilama FROM poliklinik WHERE kd_poli='{$_POST['kd_poli']}'"));

      $insert = query("
          UPDATE
              reg_periksa
          SET
              kd_dokter       = '$kd_dokter',
              kd_poli         = '{$_POST['kd_poli']}',
              p_jawab         = '{$_POST['namakeluarga']}',
              almt_pj         = '{$_POST['alamatpj']}',
              hubunganpj      = '-',
              biaya_reg       = '{$biaya_reg['registrasilama']}',
              kd_pj           = '{$_POST['kd_pj']}',
              sttsumur        = 'Th',
              status_bayar    = 'Belum Bayar',
              status_poli     = 'Lama'
          WHERE
              no_rawat = '{$_POST['no_rawat']}'
      ");
      $insert_perujuk = query("
          UPDATE
              rujuk_masuk
          SET
              perujuk         = '{$_POST['nama_perujuk']}',
              dokter_perujuk  = '{$_POST['nama_perujuk']}'
          WHERE
              no_rawat = '{$_POST['no_rawat']}'
      ");
  }
} else if($page=='delete'){
    query("DELETE FROM reg_periksa WHERE no_rawat='$_POST[no_rawat]'");
} else if($page=='check'){
  if(!empty($_POST['no_rkm_medis'])){
    $data = array();
    $cek = fetch_array(query("SELECT count(status_bayar) FROM reg_periksa WHERE no_rkm_medis='{$_POST['no_rkm_medis']}' AND status_bayar = 'Belum Bayar'"));
    if($cek > "1") {
      $data['status'] = 'exist';
      echo json_encode($data);
    }
  }
} else {

}
?>
