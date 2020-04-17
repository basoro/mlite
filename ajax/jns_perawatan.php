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
         jns_perawatan
     ) temp
EOT;
    $primaryKey = 'kd_jenis_prw';
    $columns = array(
        array( 'db' => 'kd_jenis_prw','dt' => 0),
        array( 'db' => 'nm_perawatan','dt' => 1),
        array( 'db' => 'kd_kategori','dt' => 2),
        array( 'db' => 'material','dt' => 3),
        array( 'db' => 'bhp','dt' => 4),
        array( 'db' => 'tarif_tindakandr','dt' => 5),
        array( 'db' => 'tarif_tindakanpr','dt' => 6),
        array( 'db' => 'kso','dt' => 7),
        array( 'db' => 'menejemen','dt' => 8),
        array( 'db' => 'total_byrdr','dt' => 9),
        array( 'db' => 'total_byrpr','dt' => 10),
        array( 'db' => 'total_byrdrpr','dt' => 11),
        array( 'db' => 'kd_pj','dt' => 12),
        array( 'db' => 'kd_poli','dt' => 13),
        array( 'db' => 'status','dt' => 14)
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
