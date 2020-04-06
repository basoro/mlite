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

$q = $_GET['q'];

$dt = new DateTime(null, new DateTimeZone("UTC"));
$timestamp = $dt->getTimestamp();
$pass = md5(KeySisrute);
$key = IDSisrute.'&'.$timestamp;
$method = "GET";
$postdata = "";
$signature = hash_hmac('sha256', utf8_encode($key), utf8_encode($pass), true);
$encodedSignature = base64_encode($signature);
$ch = curl_init();
$headers = array(
 'X-cons-id: '.IDSisrute.'',
 'X-timestamp: '.$timestamp.'' ,
 'X-signature: '.$encodedSignature.'',
 'Content-Type:application/json',
 'Content-length: '.strlen($postdata),
);
curl_setopt($ch, CURLOPT_URL, SisruteApiUrl."rsonline/referensi/pelayanan?start=0&limit=15");
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$content = curl_exec($ch);
$err = curl_error($ch);

curl_close($ch);
$result = json_decode($content, true);
$data = $result['data'];
echo json_encode($data);
