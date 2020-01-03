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

$sql = query("SELECT kamar.kd_kamar AS id, bangsal.nm_bangsal AS text , kamar.kelas AS kelas FROM kamar , bangsal WHERE kamar.kd_bangsal = bangsal.kd_bangsal AND kamar.status = 'KOSONG' AND kamar.statusdata = '1' AND bangsal.nm_bangsal LIKE '%".$q."%'");
$num = num_rows($sql);
if($num > 0){
	while($data = fetch_assoc($sql)){
		$tmp[] = $data;
	}
} else $tmp = array();

echo json_encode($tmp);

?>
