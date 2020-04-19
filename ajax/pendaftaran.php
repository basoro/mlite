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

$table = <<<EOT
 (
   SELECT
     SUBSTR(a.nm_pasien,1,20) AS nm_pasien,
     a.no_rkm_medis,
     a.no_ktp,
     a.jk,
     a.tmp_lahir,
     a.tgl_lahir,
     a.nm_ibu,
     a.alamat,
     a.gol_darah,
     a.pekerjaan,
     a.stts_nikah,
     a.agama,
     a.tgl_daftar,
     a.no_tlp,
     a.umur,
     a.pnd,
     a.keluarga,
     a.namakeluarga,
     a.kd_pj,
     a.no_peserta,
     a.kd_kel,
     a.kd_kec,
     a.kd_kab,
     a.pekerjaanpj,
     a.alamatpj,
     a.cacat_fisik,
     a.email,
     a.nip,
     a.kd_prop,
     a.kelurahanpj,
     a.kecamatanpj,
     a.kabupatenpj,
     a.propinsipj,
     a.suku_bangsa,
     a.bahasa_pasien,
     a.perusahaan_pasien
   FROM
     pasien AS a
 ) temp
EOT;

$page = isset($_GET['p'])? $_GET['p'] : '';

if($page=='add'){
  if(!empty($_POST['no_rkm_medis'])){
      $data = array();
      $_POST['tgl_registrasi'] = date('Y-m-d', strtotime($_POST['tgl_registrasi']));
      // get data pasien
      $get_pasien = $mysqli->query("SELECT * FROM pasien WHERE no_rkm_medis = '{$_POST['no_rkm_medis']}'")->fetch_array();
      // set format tanggal
      $tgl_reg = date('Y/m/d', strtotime($_POST['tgl_registrasi']));
      //mencari no rawat terakhir
      $no_rawat_akhir = $mysqli->query("SELECT max(no_rawat) FROM reg_periksa WHERE tgl_registrasi='$_POST[tgl_registrasi]'")->fetch_array();
      $no_urut_rawat = substr($no_rawat_akhir[0], 11, 6);
      $no_rawat = $tgl_reg.'/'.sprintf('%06s', ($no_urut_rawat + 1));
      //mencari no reg terakhir
      $no_reg_akhir = $mysqli->query("SELECT max(no_reg) FROM reg_periksa WHERE kd_dokter='$_POST[kd_dokter]' and tgl_registrasi='$_POST[tgl_registrasi]'")->fetch_array();
      if($no_reg_akhir[0] == NULL) {
          $no_reg = '001';
      } else {
        $no_urut_reg = substr($no_reg_akhir[0], 0, 3);
        $no_reg = sprintf('%03s', ($no_urut_reg + 1));
      }
      // get biaya
      $biaya_reg=$mysqli->query("SELECT registrasilama FROM poliklinik WHERE kd_poli='{$_POST['kd_poli']}'")->fetch_array();
      //menentukan umur sekarang
      list($cY, $cm, $cd) = explode('-', date('Y-m-d'));
      list($Y, $m, $d) = explode('-', date('Y-m-d', strtotime($get_pasien['tgl_lahir'])));
      $umurdaftar = $cY - $Y;

      $insert = $mysqli->query("INSERT
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
      $insert_perujuk = $mysqli->query("INSERT
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
    $cek = $mysqli->query("SELECT count(status_bayar) FROM reg_periksa WHERE no_rkm_medis='{$_POST['no_rkm_medis']}' AND status_bayar = 'Belum Bayar'")->fetch_array();
    if($cek['0'] >= 1) {
      $data['status'] = 'exist';
      echo json_encode($data);
    }
  }
} else {
  $primaryKey = 'no_rkm_medis';
  $columns = array(
      array( 'db' => 'no_rkm_medis','dt' => 0),
      array( 'db' => 'nm_pasien','dt' => 1),
      array( 'db' => 'no_ktp','dt' => 2 ),
      array( 'db' => 'jk','dt' => 3 ),
      array( 'db' => 'tmp_lahir','dt' => 4 ),
      array( 'db' => 'tgl_lahir','dt' => 5 ),
      array( 'db' => 'nm_ibu','dt' => 6 ),
      array( 'db' => 'alamat','dt' => 7 ),
      array( 'db' => 'gol_darah','dt' => 8 ),
      array( 'db' => 'pekerjaan','dt' => 9 ),
      array( 'db' => 'stts_nikah','dt' => 10 ),
      array( 'db' => 'agama','dt' => 11 ),
      array( 'db' => 'tgl_daftar','dt' => 12 ),
      array( 'db' => 'no_tlp','dt' => 13 ),
      array( 'db' => 'umur','dt' => 14 ),
      array( 'db' => 'pnd','dt' => 15 ),
      array( 'db' => 'keluarga','dt' => 16 ),
      array( 'db' => 'namakeluarga','dt' => 17 ),
      array( 'db' => 'kd_pj','dt' => 18 ),
      array( 'db' => 'no_peserta','dt' => 19 ),
      array( 'db' => 'pekerjaanpj','dt' => 20 ),
      array( 'db' => 'alamatpj','dt' => 21 ),
      array( 'db' => 'nip','dt' => 22 ),
      array( 'db' => 'email','dt' => 23 ),
      array( 'db' => 'cacat_fisik','dt' => 24 ),
      array( 'db' => 'kelurahanpj','dt' => 25 ),
      array( 'db' => 'kecamatanpj','dt' => 26 ),
      array( 'db' => 'kabupatenpj','dt' => 27 ),
      array( 'db' => 'propinsipj','dt' => 28 ),
      array( 'db' => 'suku_bangsa','dt' => 29 ),
      array( 'db' => 'bahasa_pasien','dt' => 30 ),
      array( 'db' => 'perusahaan_pasien','dt' => 31 )
  );

  $sql_details = array(
      'user' => DB_USER,
      'pass' => DB_PASS,
      'db'   => DB_NAME,
      'host' => DB_HOST
  );
  require('ssp.class.php');
  echo json_encode(
      SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
  );
}
?>
