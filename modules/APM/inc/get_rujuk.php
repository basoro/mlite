<?php
include('../../../config.php');

if(!empty($_POST['no_peserta'])){
    $data = array();

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
    curl_setopt($ch, CURLOPT_URL, BpjsApiUrl."Rujukan/List/Peserta/".$_POST['no_peserta']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_HTTPGET, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $content = curl_exec($ch);
    $err = curl_error($ch);
	curl_close($ch);
	$result = json_decode($content, true);
  	$hasil = [];
  	foreach($result['response']['rujukan'] as $key => $value):
	array_push($hasil,['no_rujukan' => $value['no_rujukan']]);
  	endforeach
    //returns data as JSON format
    echo json_encode($hasil);
}
?>
