<?php
include_once '../config.php';

$sup = new StdClass();
$sup->noSep = $_GET['no_sep'];
$sup->user = 'loket1';

$data = new StdClass();
$data->request = new StdClass();
$data->request->t_sep = $sup;

$sep = json_encode($data);

date_default_timezone_set('UTC');
$tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
$signature = hash_hmac('sha256', ConsID."&".$tStamp, SecretKey, true);
$encodedSignature = base64_encode($signature);
$ch = curl_init();
$headers = array(
  'X-cons-id: '.ConsID.'',
  'X-timestamp: '.$tStamp.'' ,
  'X-signature: '.$encodedSignature.'',
  'Content-Type:Application/x-www-form-urlencoded',
);
curl_setopt($ch, CURLOPT_URL, BpjsApiUrl."SEP/Delete");
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $sep);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$content = curl_exec($ch);
$err = curl_error($ch);

curl_close($ch);
$result = json_decode($content,true);
$meta = $result['metaData']['code'];
$mets = $result['metaData']['message'];

  echo "Kode : ".$meta."</br>";
  echo "Pesan : ".$mets."</br>";
  echo $sepranap;
if ($meta == "200") {
  
  $insert = query("DELETE FROM bridging_sep WHERE no_sep = '".$_GET['no_sep']."'");
	}else {
    echo "Kode : ".$meta."</br>";
    echo "Pesan : ".$mets."</br>";
  	
	
  };
  ?>
	<a href="pasien-batal-brid.php" class="btn btn-secondary form-control">Back</a>