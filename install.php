<?php
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('max_execution_time', 300);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Khanza Lite Setup</title>
    <!-- Favicon-->
    <link rel="icon" href="./assets/images/favicon.ico" type="image/x-icon">

    <!-- Google Fonts -->
    <link href="./assets/css/roboto.css" rel="stylesheet">

    <!-- Material Icon Css -->
    <link href="./assets/css/material-icon.css" rel="stylesheet">

    <!-- Bootstrap Core Css -->
    <link href="./assets/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Waves Effect Css -->
    <link href="./assets/plugins/node-waves/waves.css" rel="stylesheet" />

    <!-- Animation Css -->
    <link href="./assets/plugins/animate-css/animate.css" rel="stylesheet" />

    <!-- Custom Css -->
    <link href="./assets/css/style.css" rel="stylesheet">

    <!-- AdminBSB Themes. You can choose a theme from css/themes instead of get all themes -->
    <link href="./assets/css/themes/all-themes.min.css" rel="stylesheet" />
</head>

<body class="login-page">
    <div class="login-box" style="margin: 10px;">
        <div class="logo">
            <div class="align-center p-b-15"><img src="assets/images/yaski.png"></div>
            <a href="./index.php">Khanza Lite</a>
            <small>Installation</small>
        </div>
        <div class="card">

<?php

$action = isset($_GET['action'])?$_GET['action']:null;

if(!$action) {

require('config.php');

if(num_rows(query("SHOW TABLES LIKE 'setting'"))  == 1) {
    header("Location: index.php");
}

?>
    <div class="body">
        <form id="sign_in" method="POST" action="install.php?action=start">
            <div class="msg">Anda ada dihalaman instalasi awal SIMKES Khanza versi Lite. <br>Jika anda sudah pernah menginstall sebelumnya, silahkan hapus file install.php di sistem anda.</div>
            <div class="row">
                <div class="col-xs-3">
                </div>
                <div class="col-xs-6">
                    <button class="btn btn-block bg-pink waves-effect" type="submit" name="start">LANJUTKAN</button>
                </div>
                <div class="col-xs-3">
                </div>
            </div>
        </form>
    </div>
<?php
}

if($action == 'start') {
?>
  <div class="body">
      <form id="sign_in" method="POST" action="install.php?action=config">
          <div class="msg">Silahkan isikan konfirgurasi database anda</div>
          <div class="input-group">
              <span class="input-group-addon">
                  <i class="material-icons">home</i>
              </span>
              <div class="form-line">
                  <input type="text" class="form-control" name="DB_HOST" placeholder="Database Host" required autofocus>
              </div>
          </div>
          <div class="input-group">
              <span class="input-group-addon">
                  <i class="material-icons">person</i>
              </span>
              <div class="form-line">
                  <input type="text" class="form-control" name="DB_USER" placeholder="Database Username" required>
              </div>
          </div>
          <div class="input-group">
              <span class="input-group-addon">
                  <i class="material-icons">lock</i>
              </span>
              <div class="form-line">
                  <input type="password" class="form-control" name="DB_PASS" placeholder="Database Password">
              </div>
          </div>
          <div class="input-group">
              <span class="input-group-addon">
                  <i class="material-icons">reorder</i>
              </span>
              <div class="form-line">
                  <input type="text" class="form-control" name="DB_NAME" placeholder="Database Name" required>
              </div>
          </div>
          <div class="row">
              <div class="col-xs-3">
              </div>
              <div class="col-xs-6">
                  <button class="btn btn-block bg-pink waves-effect" type="submit" name="config">SAVE CONFIG</button>
              </div>
              <div class="col-xs-3">
              </div>
          </div>
      </form>
  </div>
  <?php
}

if($action == 'config') {

  file_put_contents('config.php', str_replace("\ndefine('DB_HOST', 'localhost')", "\ndefine('DB_HOST', '".$_POST['DB_HOST']."')", file_get_contents('config.php')));
  file_put_contents('config.php', str_replace("\ndefine('DB_USER', 'root')", "\ndefine('DB_USER', '".$_POST['DB_USER']."')", file_get_contents('config.php')));
  file_put_contents('config.php', str_replace("\ndefine('DB_PASS', '')", "\ndefine('DB_PASS', '".$_POST['DB_PASS']."')", file_get_contents('config.php')));
  file_put_contents('config.php', str_replace("\ndefine('DB_NAME', 'khanzalite')", "\ndefine('DB_NAME', '".$_POST['DB_NAME']."')", file_get_contents('config.php')));
?>
  <div class="body">
      <form id="sign_in" class="finish" method="POST" action="install.php?action=finish">
          <div class="preinstall-information">
          <div class="msg">Koneksi ke database anda sukses. Silahkan klik tombol dibawah untuk memulai instalasi.</div>
          <div class="row">
              <div class="col-xs-3">
              </div>
              <div class="col-xs-6">
                  <button class="btn btn-block bg-pink waves-effect" type="submit" name="finish">INSTALL DATABASE</button>
              </div>
              <div class="col-xs-3">
              </div>
          </div>
          </div>
          <div class="install-information">
            <div class="text-center">
              <div class="preloader">
                  <div class="spinner-layer pl-red">
                      <div class="circle-clipper left">
                          <div class="circle"></div>
                      </div>
                      <div class="circle-clipper right">
                          <div class="circle"></div>
                      </div>
                  </div>
              </div>
            </div>
            <div class="msg">Silahkan tunggu sebentar selama proses pemasangan database. Perkiraan waktu pemasangan tergantung spesifikasi sistem anda. Tapi rata-rata kurang dari 5 menit.</div>
          </div>
      </form>
  </div>
<?php
}

if($action == 'finish') {

  require('config.php');

  if (!$connection) {
  die("MySQL Connection error");
  }

  if(isset($_POST['finish'])) {
    $templine = '';
    $lines = file('sik.sql');
    foreach ($lines as $line) {
      if (substr($line, 0, 2) == '--' || $line == '')
        continue;
        $templine .= $line;
      if (substr(trim($line), -1, 1) == ';') {
        mysqli_query($connection,$templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysqli_error() . '<br /><br />');
        $templine = '';
      }
    }
    ?>
    <div class="body">
      <div class="alert bg-pink alert-dismissible" role="alert">Instalasi Khanza Lite selesai!<br>Silahkan hapus file install.php untuk mencegah digunakan menggangu sistem anda.</div>
      <p>Untuk menggunakan SIMKES Khanza versi Desktop silahkan merujuk ke repo utama <a href="https://github.com/mas-elkhanza/SIMRS-Khanza">Github Mas Elkhanza</a> atau ikuti <a href="https://simkes.basoro.id">langkah cepat</a> <a href="https://github.com/basoro/SIMKES-Khanza/blob/master/README.md">disini</a>.</p>
      <p><a href="./login.php" class="btn btn-lg bg-green">LOGIN</a></p>
      <label>Username:</labe> <b>spv</b><br>
      <label>Password:</labe> <b>server</b>
    </div>

    <?php
  }

}
?>
</div>
</div>

<!-- Jquery Core Js -->
<script src="./assets/plugins/jquery/jquery.min.js"></script>

<!-- Bootstrap Core Js -->
<script src="./assets/plugins/bootstrap/js/bootstrap.js"></script>

<!-- Waves Effect Plugin Js -->
<script src="./assets/plugins/node-waves/waves.js"></script>

<!-- Validation Plugin Js -->
<script src="./assets/plugins/jquery-validation/jquery.validate.js"></script>

<!-- Custom Js -->
<script src="./assets/js/admin.js"></script>
<script>
  $(".install-information").hide();
  $(".btn").click(function () {
      $(".preinstall-information").hide()
      $(".install-information").show()
  });
</script>
</body>

</html>
