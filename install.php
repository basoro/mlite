<?php
define('BASE_DIR', __DIR__);
require('config.php');
$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
ini_set('max_execution_time', 300);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Khanza Lite Setup</title>
    <!-- Favicon-->
    <link rel="icon" href="favicon.png" type="image/x-icon">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="themes/admin/css/style.css" rel="stylesheet">
    <link href="assets/css/font-awesome.css" rel="stylesheet">
    <script src="assets/jscripts/jquery.min.js"></script>
    <script src="themes/admin/js/kalypto.min.js"></script>
</head>

<body style="background: #222 url('themes/admin/img/wallpaper.png'); background-repeat: no-repeat;  background-size: cover;">
    <section id="login" style="background: #222;padding:20px;">
        <div class="panel panel-default">
            <div class="panel-heading">
                <img src="themes/admin/img/logo.png"> <h2><span>Khanza</span> LITE</h2>
                <div>Installation</div>
            </div>
            <?php
            $action = isset($_GET['action'])?$_GET['action']:null;
            if(!$action) {
            if($mysqli->query("SHOW TABLES LIKE 'setting'")->num_rows  == 1) {
                header("Location: index.php");
            }
            ?>
            <form id="sign_in" method="POST" action="install.php?action=start">
              <div id="notify" class="alert-danger animated shake" style="margin-bottom:20px;">
                Anda ada dihalaman instalasi awal<br>
                SIMKES Khanza versi Lite.<br>
                Jika anda sudah pernah menginstall sebelumnya, silahkan hapus file install.php di sistem anda.
              </div>
              <div class="row mt-5">
                  <div class="col-md-12">
                      <button class="btn btn-success btn-lg btn-block" type="submit" name="start">LANJUTKAN</button>
                  </div>
              </div>
            </form>
            <?php
            }
            if($action == 'start') {
            ?>
            <form id="sign_in" method="POST" action="install.php?action=config">
                <div id="notify" class="alert-danger animated shake" style="margin-bottom:20px;">Isikan konfigurasi database anda</div>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-globe"></i></div>
                        <input type="text" class="form-control" name="DBHOST" placeholder="Database Host" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-user"></i></div>
                        <input type="text" class="form-control" name="DBUSER" placeholder="Database Username" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-lock"></i></div>
                        <input type="password" class="form-control" name="DBPASS" placeholder="Database Password">
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon"><i class="fa fa-database"></i></div>
                        <input type="text" class="form-control" name="DBNAME" placeholder="Database Name" required>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success btn-lg btn-block">TEST CONFIG</button>
                </div>
            </form>
            <?php
            }
            if($action == 'config') {
              file_put_contents('config.php', str_replace("\ndefine('DBHOST', '')", "\ndefine('DBHOST', '".$_POST['DBHOST']."')", file_get_contents('config.php')));
              file_put_contents('config.php', str_replace("\ndefine('DBUSER', '')", "\ndefine('DBUSER', '".$_POST['DBUSER']."')", file_get_contents('config.php')));
              file_put_contents('config.php', str_replace("\ndefine('DBPASS', '')", "\ndefine('DBPASS', '".$_POST['DBPASS']."')", file_get_contents('config.php')));
              file_put_contents('config.php', str_replace("\ndefine('DBNAME', '')", "\ndefine('DBNAME', '".$_POST['DBNAME']."')", file_get_contents('config.php')));
            ?>
            <div class="row">
                <div class="col-xs-12">
                  <form id="sign_in" class="finish" method="POST" action="install.php?action=finish">
                      <div class="preinstall-information">
                          <div id="notify" class="alert-success animated shake" style="margin-bottom:20px;">Koneksi ke database anda sukses. Klik tombol dibawah untuk memulai instalasi.</div>
                          <button class="btn btn-danger btn-lg btn-block" type="submit" name="finish">INSTALL DATABASE</button>
                      </div>
                      <div class="install-information">
                          <div class="text-center">
                            <br><i class="fa fa-spinner fa-spin fa-5x"></i><br><br>
                          </div>
                          <div id="notify" class="alert-danger animated shake" style="margin-bottom:20px;">Tunggu sebentar selama proses pemasangan database. Perkiraan waktu pemasangan tergantung spesifikasi sistem anda. Tapi rata-rata kurang dari 5 menit.</div>
                      </div>
                  </form>
                </div>
            </div>
            <?php
            }
            if($action == 'finish') {
              if ($mysqli->connect_errno) {
                  printf("Connect failed: %s\n", $mysqli->connect_error);
                  exit();
              }
              if(isset($_POST['finish'])) {
                $templine = '';
                $lines = file('sik.sql');
                foreach ($lines as $line) {
                  if (substr($line, 0, 2) == '--' || $line == '')
                    continue;
                    $templine .= $line;
                  if (substr(trim($line), -1, 1) == ';') {
                    $mysqli->query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . $mysqli->error . '<br /><br />');
                    $templine = '';
                  }
                }
                ?>
                  <div class="row">
                      <div class="col-xs-12">
                          <div id="notify" class="alert-success animated shake" style="margin-bottom:20px;">Instalasi Khanza Lite selesai!<br>Silahkan hapus file install.php untuk mencegah digunakan menggangu sistem anda.</div>
                          <p class="color-white ">Untuk menggunakan SIMKES Khanza versi Desktop silahkan merujuk ke repo utama <a href="https://github.com/mas-elkhanza/SIMRS-Khanza">Github Mas Elkhanza</a> atau ikuti <a href="https://simkes.basoro.id">langkah cepat</a> <a href="https://github.com/basoro/SIMKES-Khanza/blob/master/README.md">disini</a>.</p>
                          <br>
                              <a href="./admin/" class="btn btn-success btn-lg btn-block">LOGIN</a>
                          <br>
                          <label>Username:</labe> <b>spv</b><br>
                          <label>Password:</labe> <b>server</b>
                      </div>
                  </div>
                <?php
              }
            }
            ?>
        </div>
    </section>

    <script>
      $(".install-information").hide();
      $(".btn").click(function () {
          $(".preinstall-information").hide()
          $(".install-information").show()
      });
    </script>

</body>
</html>
