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

include ('../../../config.php');
include ('../../../init.php');

$table = <<<EOT
 (
   SELECT
     no_sep, no_rawat, tglsep, tglrujukan, no_rujukan, nomr, nama_pasien, tanggal_lahir, peserta
   FROM
     bridging_sep
 ) temp
EOT;

$primaryKey = 'no_sep';
$columns = array(
    array( 'db' => 'no_sep','dt' => 0),
    array( 'db' => 'no_rawat','dt' => 1),
    array( 'db' => 'tglsep','dt' => 2),
    array( 'db' => 'tglrujukan','dt' => 3),
    array( 'db' => 'no_rujukan','dt' => 4),
    array( 'db' => 'nomr','dt' => 5),
    array( 'db' => 'nama_pasien','dt' => 6),
    array( 'db' => 'tanggal_lahir','dt' => 7),
    array( 'db' => 'peserta','dt' => 8)
);

$sql_details = array(
    'user' => DB_USER,
    'pass' => DB_PASS,
    'db'   => DB_NAME,
    'host' => DB_HOST
);
require('../../../includes/ssp.class.php');
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);

?>
