<?php
include('../../../config.php');
include('../../../init.php');

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
$sep = $result['response']['sep']['noSep'];

  /*echo "Kode : ".$meta."</br>";
  echo "Pesan : ".$mets."</br>";
  echo $sep;*/
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/fontawesome.min.css">
    <link href="css/gijgo.min.css" rel="stylesheet" type="text/css" />

    <style>
    .modal-full {
      min-width: 100%;
      margin: 0;
    }
    .modal-full .modal-content {
      min-height: 100vh;
    }
    .modal-fix {
      min-width: 1024px;
      margin: 0;
    }
    .modal-fix .modal-content {
      min-height: 100vh;
    }
    .modal .tab-content {
      min-height: 50vh;
    }
    .nav-pills.nav-wizard > li {
      position: relative;
      overflow: visible;
      border-right: 8px solid transparent;
      border-left: 8px solid transparent;
    }

    .nav-pills.nav-wizard > li + li {
      margin-left: 0;
    }

    .nav-pills.nav-wizard > li:first-child {
      border-left: 0;
    }

    .nav-pills.nav-wizard > li:first-child a {
      border-radius: 5px 0 0 5px;
    }

    .nav-pills.nav-wizard > li:last-child {
      border-right: 0;
    }

    .nav-pills.nav-wizard > li:last-child a {
      border-radius: 0 5px 5px 0;
    }

    .nav-pills.nav-wizard > li a {
      border-radius: 0;
      background-color: #eee;
    }

    .nav-pills.nav-wizard > li:not(:last-child) a:after {
      position: absolute;
      content: "";
      top: 0px;
      right: -20px;
      width: 0px;
      height: 0px;
      border-style: solid;
      border-width: 20px 0 20px 20px;
      border-color: transparent transparent transparent #eee;
      z-index: 150;
    }

    .nav-pills.nav-wizard > li:not(:first-child) a:before {
      position: absolute;
      content: "";
      top: 0px;
      left: -20px;
      width: 0px;
      height: 0px;
      border-style: solid;
      border-width: 20px 0 20px 20px;
      border-color: #eee #eee #eee transparent;
      z-index: 150;
    }

    .nav-pills.nav-wizard > li:hover:not(:last-child) a:after {
      border-color: transparent transparent transparent #aaa;
    }

    .nav-pills.nav-wizard > li:hover:not(:first-child) a:before {
      border-color: #aaa #aaa #aaa transparent;
    }

    .nav-pills.nav-wizard > li:hover a {
      background-color: #aaa;
      color: #fff;
    }

    .nav-pills.nav-wizard > li:not(:last-child) a.active:after {
      border-color: transparent transparent transparent #428bca;
    }

    .nav-pills.nav-wizard > li:not(:first-child) a.active:before {
      border-color: #428bca #428bca #428bca transparent;
    }

    .nav-pills.nav-wizard > li a.active {
      background-color: #428bca;
    }
    </style>
    <title>SKDP</title>
  </head>
  <body>
    <div class="px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
      <h1 class="display-2">APM</h1>
      <h3 class="display-6">Anjungan Pasien Mandiri Pelayanan Rawat Jalan</h3>
      <h2 class="display-5"><?php echo $dataSettings['nama_instansi']; ?></h2>
    </div>
    <br><br>
    <div class="container">
      <?php
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
      $insert = query("INSERT INTO bridging_sep VALUES (
      '{$sep}','{$_POST['no_rawat']}','{$_POST['tglsep']}','{$_POST['tglrjk']}','{$_POST['no_rujuk']}','{$_POST['ppruj']}','{$_POST['nmruj']}',
      '{$_POST['ppk']}','{$_POST['nmrs']}','{$_POST['kdpl']}','{$_POST['cttn']}','{$_POST['kddx']}','{$_POST['nmdx']}','{$_POST['kdpoli']}',
      '{$_POST['nmpoli']}','{$_POST['kkls']}','{$_POST['lkln']}','loket1','{$_POST['norm']}','{$_POST['nmps']}','{$_POST['tgllhr']}',
      '{$_POST['psrt']}','{$_POST['jk']}','{$_POST['nops']}','{$_POST['tglplg']}','{$fskes}','{$eks}','{$cob}','','{$_POST['notlp']}',
      '{$ktrk}','{$_POST['tglkkl']}','{$_POST['ktrg']}','{$supl}','{$_POST['sepsup']}','','','','','','','{$_POST['skdp']}','{$_POST['dpjp']}','{$_POST['nmdpjp']}')");
        echo "<h3>Kode : ".$meta."</h3></br>";
        echo "<h3>Pesan : ".$mets."</h3></br>";
        echo $_POST['dpjp'];
        echo '<script>setTimeout(function() { window.location.href = "cetaksep.php?no_rawat='.$_POST['no_rawat'].'"; }, 1000);</script>';
    }else {
        echo "<h3>Kode : ".$meta."</h3></br>";
        echo "<h3>Pesan : ".$mets."</h3></br>";
        echo $_POST['dpjp'];
        echo '<script>setTimeout(function() { window.location.href = "'.URL.'/modules/APM/inc/ceksep.php"; }, 10000);</script>';
  };

  ?>
    </div>
    <div class="pricing-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center text-danger">
      <h3 class="display-6">Silahkan hubungi petugas jika anda mengalami kesulitan.</h3>
    </div>

  </body>
</html>
