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
  if(!empty($_POST['nama_kelurahan'])){
    $insert = query("INSERT INTO kelurahan (kd_kel, nm_kel) VALUES (NULL, '{$_POST['nama_kelurahan']}')");
  }
} else {
  $sql_kelurahan = "SELECT * FROM kelurahan";
  $result_kelurahan = query($sql_kelurahan);
  while($row = fetch_array($result_kelurahan)) {
    echo '<tr class="pilihkelurahan" data-kdkel="'.$row[0].'" data-nmkel="'.$row[1].'">';
    echo '<td>'.$row[0].'</td>';
    echo '<td>'.$row[1].'</td>';
    echo '</tr>';
  }
}
?>
