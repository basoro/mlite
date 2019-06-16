<?php
include_once '../config.php';

$sup = new StdClass();
$sup->noKartu = $_POST['nops'];
$sup->tglSep = $_POST['tglsep'];
$sup->ppkPelayanan = $_POST['ppk'];
$sup->jnsPelayanan = $_POST['kdpl'];
$sup->klsRawat = $_POST['kkls'];
$sup->noMR = $_POST['norm'];
$sup->rujukan = new StdClass();
$sup->rujukan->asalRujukan = $_POST['fsks'];
$sup->rujukan->tglRujukan = $_POST['tglrjk'];
$sup->rujukan->noRujukan = $_POST['no_rujuk'];
$sup->rujukan->ppkRujukan = $_POST['ppruj'];
$sup->catatan = $_POST['cttn'];
$sup->diagAwal = $_POST['kddx'];
$sup->poli = new StdClass();
$sup->poli->tujuan = $_POST['kdpoli'];
$sup->poli->eksekutif = $_POST['eks'];
$sup->cob = new StdClass();
$sup->cob->cob = $_POST['cob'];
$sup->katarak = new StdClass();
$sup->katarak->katarak = $_POST['ktrk'];
$sup->jaminan = new StdClass();
$sup->jaminan->lakaLantas = $_POST['lkln'];
$sup->jaminan->penjamin = new StdClass();
$sup->jaminan->penjamin->penjamin = $_POST['pjlk'];
$sup->jaminan->penjamin->tglKejadian = $_POST['tglkkl'];
$sup->jaminan->penjamin->keterangan = $_POST['ktrg'];
$sup->jaminan->penjamin->suplesi = new StdClass();
$sup->jaminan->penjamin->suplesi->suplesi = $_POST['suplesi'];
$sup->jaminan->penjamin->suplesi->noSepSuplesi = $_POST['sepsup'];
$sup->jaminan->penjamin->suplesi->lokasiLaka = new StdClass();
$sup->jaminan->penjamin->suplesi->lokasiLaka->kdPropinsi = $_POST['prop'];
$sup->jaminan->penjamin->suplesi->lokasiLaka->kdKabupaten = $_POST['kbpt'];
$sup->jaminan->penjamin->suplesi->lokasiLaka->kdKecamatan = $_POST['kec'];
$sup->skdp = new StdClass();
$sup->skdp->noSurat = $_POST['skdp'];
$sup->skdp->kodeDPJP = $_POST['dpjp'];
$sup->noTelp = $_POST['notlp'];
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
curl_setopt($ch, CURLOPT_URL, BpjsApiUrl."SEP/1.1/insert");
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $sep);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$content = curl_exec($ch);
$err = curl_error($ch);

curl_close($ch);
$result = json_decode($content,true);
$meta = $result['metaData']['code'];
$mets = $result['metaData']['message'];
$sepranap = $result['response']['sep']['noSep'];

  echo "Kode : ".$meta."</br>";
  echo "Pesan : ".$mets."</br>";
  echo $sepranap;
if ($meta == "200") {
  if ($_POST['suplesi'] == '0') {
    $supl = '0. Tidak';
  }else {
    $supl = '1. Ya';
  };
  if ($_POST['eks'] == '0') {
    $eks = '0. Tidak';
  }else {
    $eks = '1. Ya';
  };
  if ($_POST['cob'] == '0') {
    $cob = '0. Tidak';
  }else {
    $cob = '1. Ya';
  };
  if ($_POST['ktrk'] == '0') {
    $ktrk = '0. Tidak';
  }else {
    $ktrk = '1. Ya';
  };
  if ($_POST['fsks'] == '1') {
    $fskes = '1. Faskes 1';
  }else {
    $fskes = '2. Faskes 2(RS)';
  };
  if ($_POST['tglkkl'] == "") {
  $_POST['tglkkl'] = '0000-00-00';
} else { 
  $_POST['tglkkl'] = $_POST['tglkkl'];
}
  $insert = query("INSERT INTO bridging_sep VALUES (
  '{$sepranap}','{$_POST['no_rawat']}','{$_POST['tglsep']}','{$_POST['tglrjk']}','{$_POST['no_rujuk']}','{$_POST['ppruj']}','{$_POST['nmruj']}',
  '{$_POST['ppk']}','{$_POST['nmrs']}','{$_POST['kdpl']}','{$_POST['cttn']}','{$_POST['kddx']}','{$_POST['nmdx']}','{$_POST['kdpoli']}',
  '{$_POST['nmpoli']}','{$_POST['kkls']}','{$_POST['lkln']}','loket1','{$_POST['norm']}','{$_POST['nmps']}','{$_POST['tgllhr']}',
  '{$_POST['psrt']}','{$_POST['jk']}','{$_POST['nops']}','{$_POST['tglplg']} 09:00:00','{$fskes}','{$eks}','{$cob}','','{$_POST['notlp']}',
  '{$ktrk}','{$_POST['tglkkl']}','{$_POST['ktrg']}','{$supl}','{$_POST['sepsup']}','','','','','','','{$_POST['skdp']}','{$_POST['dpjp']}','{$_POST['nmdpjp']}')");
	}else {
    echo "Kode : ".$meta."</br>";
    echo "Pesan : ".$mets."</br>";
  	
	
  };
  ?>
	<a href="http://simrs.rshdbarabai.com/dashboard/br-ranap.php" class="btn btn-secondary form-control">Back</a>