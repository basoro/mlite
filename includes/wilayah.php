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
include ('../init.php');

error_reporting(0);
$page = isset($_GET['page'])?$_GET['page']:null;

if ($page == 'cari-kota')
{
	$kode = $_POST['kode'];
  $kode = substr($kode, 0, 2);
	$kota = query("SELECT * FROM kabupaten WHERE kd_kab LIKE '$kode%'");
	echo '<option>Pilih Kabupaten</option>';
	while ($rowkota = fetch_assoc($kota)) {
	    echo'<option value="'.$rowkota['kd_kab'].'">'.$rowkota['nm_kab'].'</option>';
	}
}

if ($page == 'cari-kecamatan')
{
	$kode = $_POST['kode'];
  $kode = substr($kode, 0, 4);
	$kota = query("SELECT * FROM kecamatan WHERE kd_kec LIKE '$kode%'");
	echo '<option>Pilih Kecamatan</option>';
	while ($rowkota = fetch_assoc($kota)) {
	    echo'<option value="'.$rowkota['kd_kec'].'">'.$rowkota['nm_kec'].'</option>';
	}
}

if ($page == 'cari-kelurahan')
{
	$kode = $_POST['kode'];
  $kode = substr($kode, 0, 7);
	$kota = query("SELECT * FROM kelurahan WHERE kd_kel LIKE '$kode%'");
	echo '<option>Pilih Kelurahan</option>';
	while ($rowkota = fetch_assoc($kota)) {
	    echo'<option value="'.$rowkota['kd_kel'].'">'.$rowkota['nm_kel'].'</option>';
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

?>
