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

include'../config.php';

error_reporting(0);
//$page = $_GET['page'];
$page = isset($_GET['page'])?$_GET['page']:null;

if ($page == 'cari-kota')
{
	$kode = $_POST['kode'];
  $kode = substr($kode, 0, 2);
	$kota = query("SELECT * FROM kabupaten WHERE kd_kab LIKE '$kode%'");
	while ($rowkota = fetch_assoc($kota)) {
	    echo'<option value="'.$rowkota['kd_kab'].'">'.$rowkota['nm_kab'].'</option>';
	}
}

if ($page == 'cari-kecamatan')
{
	$kode = $_GET['kode'];
  $kode = substr($kode, 0, 4);
	$kota = query("SELECT * FROM kecamatan WHERE kd_kec LIKE '$kode%'");
	while ($rowkota = fetch_assoc($kota)) {
	    echo'<option value="'.$rowkota['kd_kec'].'">'.$rowkota['nm_kec'].'</option>';
	}
}

if ($page == 'cari-kelurahan')
{
	$kode = $_POST['kode'];
  $kode = substr($kode, 0, 7);
	$kota = query("SELECT * FROM kelurahan WHERE kd_kel = '$kode'");
	while ($rowkota = fetch_assoc($kota)) {
	    echo'<option value="'.$rowkota['kd_kel'].'">'.$rowkota['nm_kel'].'</option>';
	}
}

?>
