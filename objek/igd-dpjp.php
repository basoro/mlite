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

$q = $_GET['q'];

date_default_timezone_set('UTC');
$tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
$signature = hash_hmac('sha256', ConsID."&".$tStamp, SecretKey, true);
$encodedSignature = base64_encode($signature);
$ch = curl_init();
$headers = array(
'X-cons-id: '.ConsID.'',
'X-timestamp: '.$tStamp.'' ,
'X-signature: '.$encodedSignature.'',
'Content-Type:application/json',
);
curl_setopt($ch, CURLOPT_URL, BpjsApiUrl."referensi/dokter/pelayanan/1/tglPelayanan/".$date."/Spesialis/IGD");
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_HTTPGET, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$content = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);
$drigd = json_decode($content, true);
$kode = $result['response']['list']['kode'];
$nama = $result['response']['list']['nama'];

//$sql = query("SELECT kode_brng AS id, nama_brng AS text FROM databarang WHERE (kode_brng LIKE '%".$q."%' OR nama_brng LIKE '%".$q."%')");
$json = [];

//while($row = fetch_assoc($sql)){
	foreach($drigd['response']['list'] as $key => $value):
     $json[] = ['id'=>$value['kode'], 'text'=>$value['nama']];
	endforeach;

echo json_encode($json);


?>
