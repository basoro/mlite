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
} else if($page=='update'){
} else if($page=='delete'){
} else {
    $table = <<<EOT
     (
       SELECT
         *
       FROM
         databarang
     ) temp
EOT;

    $primaryKey = 'kode_brng';
    $columns = array(
        array( 'db' => 'kode_brng','dt' => 0),
        array( 'db' => 'nama_brng','dt' => 1),
        array( 'db' => 'kode_sat','dt' => 2),
        array( 'db' => 'letak_barang','dt' => 3),
        array( 'db' => 'h_beli','dt' => 4),
        array( 'db' => 'ralan','dt' => 5),
        array( 'db' => 'kelas1','dt' => 6),
        array( 'db' => 'kelas2','dt' => 7),
        array( 'db' => 'kelas3','dt' => 8),
        array( 'db' => 'utama','dt' => 9),
        array( 'db' => 'vip','dt' => 10),
        array( 'db' => 'vvip','dt' => 11),
        array( 'db' => 'beliluar','dt' => 12),
        array( 'db' => 'jualbebas','dt' => 13),
        array( 'db' => 'karyawan','dt' => 14),
        array( 'db' => 'stokminimal','dt' => 15),
        array( 'db' => 'kdjns','dt' => 16),
        array( 'db' => 'kapasitas','dt' => 17),
        array( 'db' => 'expire','dt' => 18),
        array( 'db' => 'status','dt' => 19),
        array( 'db' => 'kode_industri','dt' => 20),
        array( 'db' => 'kode_kategori','dt' => 21),
        array( 'db' => 'kode_golongan','dt' => 22)
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
