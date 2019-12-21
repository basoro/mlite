<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
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

$kode = $_GET['kode'];
$sql = query("SELECT trf_kamar FROM kamar WHERE kd_kamar='$kode'");
$data = fetch_assoc($sql);
$tmp = array('tarif'=>$data['trf_kamar'],);
echo json_encode($tmp);

?>
