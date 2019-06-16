<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

ob_start();
session_start();

include ('../config.php');
include ('../init.php');

$searchTerm = $_GET['term'];
$query = query("(SELECT kd_dokter AS id, nm_dokter AS title FROM dokter WHERE kd_dokter LIKE '%".$searchTerm."%' OR nm_dokter LIKE '%".$searchTerm."%') UNION (SELECT nik AS id, nama AS title FROM pegawai WHERE nik LIKE '%".$searchTerm."%' OR nama LIKE '%".$searchTerm."%') UNION (SELECT nip AS id, nama AS title FROM petugas WHERE nip LIKE '%".$searchTerm."%' OR nama LIKE '%".$searchTerm."%')");
while ($row = fetch_assoc($query)) {
    $data[] =  array(
     'label' => $row['title'],
     'value' => $row['id'],
    );
    //$data[] = $row['username'];
}
echo json_encode($data);

?>
