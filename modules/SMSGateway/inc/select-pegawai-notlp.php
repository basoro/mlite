<?php

/***
* e-Dokter from version 0.1 Beta
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
*
* File : includes/select-obat.php
* Description : Get databarang data from json encode by select2
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

$sql = query("SELECT petugas.no_telp AS id, pegawai.nama AS text, pegawai.nik AS nik FROM pegawai, petugas WHERE pegawai.nik = petugas.nip AND (pegawai.nik LIKE '%".$q."%' OR pegawai.nama LIKE '%".$q."%' OR petugas.no_telp LIKE '%".$q."%')");
$json = [];

while($row = fetch_assoc($sql)){
     $json[] = ['id'=>$row['id'], 'text'=>$row['text'], 'nik'=>$row['nik']];
}
echo json_encode($json);


?>
