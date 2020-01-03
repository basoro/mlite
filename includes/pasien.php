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
      $tgl_daftar = date('Y-m-d');
      //menentukan umur sekarang
      list($cY, $cm, $cd) = explode('-', date('Y-m-d'));
      list($Y, $m, $d) = explode('-', date('Y-m-d', strtotime($_POST['tgl_lahir'])));
      $umur = $cY - $Y;
      $insert = query("INSERT
          INTO
              pasien
          SET
              no_rkm_medis = '{$_POST['no_rkm_medis']}',
              nm_pasien = '{$_POST['nm_pasien']}',
              no_ktp = '{$_POST['no_ktp']}',
              jk = '{$_POST['jk']}',
              tmp_lahir = '{$_POST['tmp_lahir']}',
              tgl_lahir = '{$_POST['tgl_lahir']}',
              nm_ibu = '{$_POST['nm_ibu']}',
              keluarga = '{$_POST['keluarga']}',
              alamat = '{$_POST['alamat']}',
              gol_darah = '{$_POST['gol_darah']}',
              pekerjaan = '{$_POST['pekerjaan']}',
              stts_nikah = '{$_POST['stts_nikah']}',
              agama = '{$_POST['agama']}',
              tgl_daftar = '{$tgl_daftar}',
              no_tlp = '{$_POST['no_tlp']}',
              umur = '{$umur}',
              pnd = '{$_POST['pnd']}',
              namakeluarga = '{$_POST['namakeluarga']}',
              kd_pj = '{$_POST['kd_pj']}',
              no_peserta = '{$_POST['no_peserta']}',
              kd_kel = '{$_POST['kd_kel']}',
              kd_kec = '{$_POST['kd_kec']}',
              kd_kab = '{$_POST['kd_kab']}',
              pekerjaanpj = '{$_POST['pekerjaanpj']}',
              alamatpj = '{$_POST['alamatpj']}',
              kelurahanpj = '{$_POST['kelurahanpj']}',
              kecamatanpj = '{$_POST['kecamatanpj']}',
              kabupatenpj = '{$_POST['kabupatenpj']}',
              perusahaan_pasien = '{$_POST['perusahaan_pasien']}',
              suku_bangsa = '{$_POST['suku_bangsa']}',
              bahasa_pasien = '{$_POST['bahasa_pasien']}',
              cacat_fisik = '{$_POST['cacat_fisik']}',
              email = '{$_POST['email']}',
              nip = '{$_POST['nip']}',
              kd_prop = '{$_POST['kd_prop']}',
              propinsipj = '{$_POST['propinsipj']}'
      ");
      if($insert) {
          query("UPDATE set_no_rkm_medis SET no_rkm_medis = '{$_POST['no_rkm_medis']}'");
      }
  }
} else if($page=='update'){
  if(!empty($_POST['no_rkm_medis'])){
      $data = array();
      $tgl_daftar = date('Y-m-d');
      //menentukan umur sekarang
      list($cY, $cm, $cd) = explode('-', date('Y-m-d'));
      list($Y, $m, $d) = explode('-', date('Y-m-d', strtotime($_POST['tgl_lahir'])));
      $umur = $cY - $Y;

      $insert = query("
          UPDATE
              pasien
          SET
              nm_pasien = '{$_POST['nm_pasien']}',
              no_ktp = '{$_POST['no_ktp']}',
              jk = '{$_POST['jk']}',
              tmp_lahir = '{$_POST['tmp_lahir']}',
              tgl_lahir = '{$_POST['tgl_lahir']}',
              nm_ibu = '{$_POST['nm_ibu']}',
              keluarga = '{$_POST['keluarga']}',
              alamat = '{$_POST['alamat']}',
              gol_darah = '{$_POST['gol_darah']}',
              pekerjaan = '{$_POST['pekerjaan']}',
              stts_nikah = '{$_POST['stts_nikah']}',
              agama = '{$_POST['agama']}',
              tgl_daftar = '{$tgl_daftar}',
              no_tlp = '{$_POST['no_tlp']}',
              umur = '{$umur}',
              pnd = '{$_POST['pnd']}',
              namakeluarga = '{$_POST['namakeluarga']}',
              kd_pj = '{$_POST['kd_pj']}',
              no_peserta = '{$_POST['no_peserta']}',
              kd_kel = '{$_POST['kd_kel']}',
              kd_kec = '{$_POST['kd_kec']}',
              kd_kab = '{$_POST['kd_kab']}',
              pekerjaanpj = '{$_POST['pekerjaanpj']}',
              alamatpj = '{$_POST['alamatpj']}',
              kelurahanpj = '{$_POST['kelurahanpj']}',
              kecamatanpj = '{$_POST['kecamatanpj']}',
              kabupatenpj = '{$_POST['kabupatenpj']}',
              perusahaan_pasien = '{$_POST['perusahaan_pasien']}',
              suku_bangsa = '{$_POST['suku_bangsa']}',
              bahasa_pasien = '{$_POST['bahasa_pasien']}',
              cacat_fisik = '{$_POST['cacat_fisik']}',
              email = '{$_POST['email']}',
              nip = '{$_POST['nip']}',
              kd_prop = '{$_POST['kd_prop']}',
              propinsipj = '{$_POST['propinsipj']}'
          WHERE
              no_rkm_medis = '{$_POST['no_rkm_medis']}'
      ");
  }
} else if($page=='delete'){
  query("DELETE FROM pasien WHERE no_rkm_medis='$_POST[no_rkm_medis]'");
} else {
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

    $primaryKey = 'no_rkm_medis';
    $columns = array(
        array( 'db' => 'nm_pasien','dt' => 0),
        array( 'db' => 'no_rkm_medis','dt' => 1),
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
