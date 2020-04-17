<?php

/***
* SIMRS RSHD Barabai version 0.5
* Last modified: 02 October 2017
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
*
* File : includes/select-wilayah.php
* Description : Select data wilayah
* Licence under GPL
***/

if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
   header("HTTP/1.0 403 Forbidden");
   exit;
}

include ('../config.php');

error_reporting(0);

$page = isset($_GET['page'])?$_GET['page']:null;
if($page == 'add-kelurahan'){
  if(!empty($_POST['nama_kelurahan'])){
    $insert = $mysqli->query("INSERT INTO kelurahan (kd_kel, nm_kel) VALUES (NULL, '{$_POST['nama_kelurahan']}')");
  }
}

if ($page == 'kelurahan')
{
$table = <<<EOT
	 (
	    SELECT kd_kel, nm_kel FROM kelurahan
	 ) temp
EOT;

	$primaryKey = 'kd_kel';
	$columns = array(
	    array( 'db' => 'kd_kel','dt' => 0),
	    array( 'db' => 'nm_kel','dt' => 1),
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

$show = isset($_GET['show']) ? $_GET['show'] : "";
switch($show){
	default:
  break;
	case "propinsi":
    $res = $mysqli->query("SELECT * FROM propinsi");
    while($row = $res->fetch_array()){
      echo '<tr class="pilihpropinsi" data-kdprop="'.$row['kd_prop'].'" data-namaprop="'.$row['nama'].'">';
			echo '<td>'.$row['kd_prop'].'</td>';
			echo '<td>'.$row['nm_prop'].'</td>';
			echo '</tr>';
    }
  break;
	case "kabupaten":
    $kode = $_POST['kd_prop'];
    $kode = substr($kode, 0, 2);
  	$res = $mysqli->query("SELECT * FROM kabupaten WHERE kd_kab LIKE '$kode%'");
    while($row = $res->fetch_array()){
      echo '<tr class="pilihpropinsi" data-kdprop="'.$row['kd_prop'].'" data-namaprop="'.$row['nama'].'">';
      echo '<td>'.$row['kd_prop'].'</td>';
      echo '<td>'.$row['nm_prop'].'</td>';
      echo '</tr>';
    }
  break;
	case "kecamatan":
    $kode = $_POST['kd_kab'];
    $kode = substr($kode, 0, 4);
  	$res = $mysqli->query("SELECT * FROM kecamatan WHERE kd_kec LIKE '$kode%'");
    while($row = $res->fetch_array()){
      echo '<tr class="pilihpropinsi" data-kdprop="'.$row['kd_prop'].'" data-namaprop="'.$row['nama'].'">';
      echo '<td>'.$row['kd_prop'].'</td>';
      echo '<td>'.$row['nm_prop'].'</td>';
      echo '</tr>';
    }
  break;
	case "kelurahan":
    $kode = $_POST['kd_kec'];
    $kode = substr($kode, 0, 7);
  	$kota = $mysqli->query("SELECT * FROM kelurahan WHERE kd_kel LIKE '$kode%'");
    while($row = $res->fetch_array()){
      echo '<tr class="pilihpropinsi" data-kdprop="'.$row['kd_prop'].'" data-namaprop="'.$row['nama'].'">';
      echo '<td>'.$row['kd_prop'].'</td>';
      echo '<td>'.$row['nm_prop'].'</td>';
      echo '</tr>';
    }
  break;
}
?>
