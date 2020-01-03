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

$q = $_GET['q'];

$sql = query("SELECT kd_jenis_prw AS id, nm_perawatan AS text, tarif_tindakanpr AS tarif FROM jns_perawatan WHERE status = '1' AND nm_perawatan LIKE '%".$q."%'");
$num = num_rows($sql);
if($num > 0){
	while($data = fetch_assoc($sql)){
		$tmp[] = $data;
	}
} else $tmp = array();

echo json_encode($tmp);

?>
