<?php
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
curl_setopt($ch, CURLOPT_URL, BpjsApiUrl."Peserta/nokartu/".$b['no_peserta']."/tglSEP/".$date);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_HTTPGET, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$content = curl_exec($ch);
$err = curl_error($ch);

curl_close($ch);
$result = json_decode($content, true);
$status = $result['response']['peserta']['statusPeserta']['keterangan'];
$kelas = $result['response']['peserta']['hakKelas']['keterangan'];
$klask = $result['response']['peserta']['hakKelas']['kode'];
$jnspe = $result['response']['peserta']['jenisPeserta']['keterangan'];
  ?>
