<?php
include('../../../config.php');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">

	<title>APM | Cek SEP</title>

	<!-- demo -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/fontawesome.min.css">
    <link href="css/gijgo.min.css" rel="stylesheet" type="text/css" />

<style>
:root {
--input-padding-x: 1.5rem;
--input-padding-y: .75rem;
}
body {
font-size: 20px;
}
body.login {
background: #007bff;
background: linear-gradient(to right, #0062E6, #33AEFF);
}
.card-signin {
top: 5%;
border: 0;
border-radius: 1rem
box-shadow: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.1);
background: transparent;
}

.card-signin .card-title {
margin-bottom: 2rem;
font-weight: 300;
font-size: 2.5rem;
}

.card-signin .card-body {
padding: 2rem;
}

.form-signin {
width: 100%;
}

.form-signin .btn, .form-signin .form-control {
font-size: 160%;
border-radius: 5rem;
letter-spacing: .1rem;
font-weight: bold;
padding: 1rem;
transition: all 0.2s;
}
.form-label-group {
position: relative;
margin-bottom: 1rem;
}

.form-label-group input {
height: auto;
border-radius: 2rem;
text-align: center;
}

</style>
</head>

<body class="login">
    <div class="px-3 py-3 pt-md-4 pb-md-4 mx-auto text-center text-white">
      <h1 class="display-2">APM</h1>
      <h3 class="display-6">Anjungan Pasien Mandiri Pelayanan Rawat Jalan</h3>
      <h2 class="display-5"><?php echo $dataSettings['nama_instansi']; ?></h2>
    </div>

    <div class="container">
      <div class="row">
        <div class="col-sm-9 col-md-8 col-lg-8 mx-auto">
          <div class="card card-signin my-5">
            <div class="card-body">
              <div class="form-signin">
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
                      curl_setopt($ch, CURLOPT_URL, BpjsApiUrl."Rujukan/List/Peserta/".$_GET['no_peserta']);
                      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                      curl_setopt($ch, CURLOPT_HTTPGET, 1);
                      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                      $content = curl_exec($ch);
                      $err = curl_error($ch);
                      curl_close($ch);
                      $result = json_decode($content, true);
                	?>
				<div>
					  <?php
                      foreach($result['response']['rujukan'] as $kode => $val): ?>
                      <a href="bikinsep.php?rujukan=<?php echo $val['noKunjungan'];?>&no_mr=<?php echo $_GET['no_rkm_medis'];?>" class="btn btn-lg btn-dark btn-block shadow text-uppercase"><?php echo $val['poliRujukan']['nama']; ?></a><br>
                      <?php endforeach;?>
				</div><br>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <br>
    <div class="pricing-header mt-5 px-3 py-3 pt-md-3 pb-md-2 mx-auto text-center text-danger bg-white">
      <h3 class="display-6"><marquee>Silahkan hubungi petugas jika anda mengalami kesulitan.</marquee></h3>
    </div>

</body>
</html>
