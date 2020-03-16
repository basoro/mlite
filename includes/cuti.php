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
  if(!empty($_POST['no_pengajuan'])){
      $tgl1 = new DateTime($_POST['tgl_akhir']);
    	$tgl2 = new DateTime($_POST['tgl_awal']);
    	$jml = $tgl2->diff($tgl1)->days;
      $data = array();
      $insert = query("INSERT
          INTO
              pengajuan_cuti
          SET
              no_pengajuan       = '{$_POST['no_pengajuan']}',
              tanggal    = '{$_POST['tanggal_pengajuan']}',
              tanggal_awal         = '{$_POST['tgl_awal']}',
              tanggal_akhir         = '{$_POST['tgl_akhir']}',
              nik         = '{$_SESSION['username']}',
              urgensi         = '{$_POST['urgensi']}',
              alamat         = '{$_POST['alamat_tujuan']}',
              jumlah            = $jml,
              kepentingan         = '{$_POST['alasan_cuti']}',
              nik_pj         = '{$_POST['nik_pj']}',
              status     = 'Proses Pengajuan'
      ");
  }
} else if($page=='update'){
  if(!empty($_POST['no_pengajuan'])){
      $tgl1 = new DateTime($_POST['tgl_akhir']);
      $tgl2 = new DateTime($_POST['tgl_awal']);
      $jml = $tgl2->diff($tgl1)->days;
      $data = array();
      $insert = query("
          UPDATE
              pengajuan_cuti
          SET
              tanggal_awal         = '{$_POST['tgl_awal']}',
              tanggal_akhir         = '{$_POST['tgl_akhir']}',
              urgensi         = '{$_POST['urgensi']}',
              alamat         = '{$_POST['alamat_tujuan']}',
              jumlah            = $jml,
              kepentingan         = '{$_POST['alasan_cuti']}',
              nik_pj         = '{$_POST['nik_pj']}'
          WHERE
              no_pengajuan = '{$_POST['no_pengajuan']}'
      ");
  }
} else if($page=='delete'){
    query("DELETE FROM pengajuan_cuti WHERE no_pengajuan='$_POST[no_pengajuan]'");
} else {

}
?>
