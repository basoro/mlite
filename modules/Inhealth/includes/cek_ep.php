<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

ob_start();
session_start();

include('../../../config.php');
include('../../../init.php');

$q = $_POST['data'];

$sql = query("SELECT no_peserta FROM pasien WHERE no_rkm_medis = '{$q}'");
$data = fetch_array($sql);

$postdata = new StdClass();
$postdata->token = Token;
$postdata->kodeprovider = Provider;
$postdata->nokainhealth = $data['0'];
$postdata->tglpelayanan = '2019-12-11';
$postdata->jenispelayanan = '3';
$postdata->poli = 'SRF';

$pos = json_encode($postdata);

$method = "POST";
$ch = curl_init();
$headers = array(
 'Content-Type:application/json',
 'Content-length: '.strlen($pos),
);
curl_setopt($ch, CURLOPT_URL, InhealthApiUrl."api/EligibilitasPeserta");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
curl_setopt($ch, CURLOPT_POSTFIELDS, $pos);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$content = curl_exec($ch);
$err = curl_error($ch);

curl_close($ch);
$result = json_decode($content, true);
$data = $result["ERRORDESC"];
$ce = $result['ERRORCODE'];
$np = $result['NAMAPRODUK'];
// echo json_encode($data);
echo 'Status: '.$data.' Kode: '.$ce.' Produk: '.$np;
