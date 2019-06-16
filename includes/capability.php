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
$query = query("(SELECT kd_poli AS id, nm_poli AS title FROM poliklinik WHERE kd_poli LIKE '%".$searchTerm."%' OR nm_poli LIKE '%".$searchTerm."%') UNION (SELECT kd_bangsal AS id, nm_bangsal AS title FROM bangsal WHERE kd_bangsal LIKE '%".$searchTerm."%' OR nm_bangsal LIKE '%".$searchTerm."%')");
while ($row = fetch_assoc($query)) {
    $data[] =  array(
      'label' => $row['title'],
      'value' => $row['id'],
    );
}
echo json_encode($data);

?>
