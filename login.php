<?php
session_start();
include "config.php";
include "functions/function_setting.php";
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title><?php echo setting('nama_instansi'); ?></title>
    <!-- Favicon-->
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link href="assets/css/style.min.css" rel="stylesheet">
</head>

<body class="login-page">
    <div class="login-box" style="margin: 10px;">
        <div class="logo">
            <div class="align-center p-b-15"><img src="assets/images/logo.png"></div>
            <a href="index.php"><?php echo setting('nama_instansi'); ?></a>
            <small><?php echo setting('alamat_instansi'); ?></small>
        </div>

        <?php
        if(isset($_POST['login'])){
          	$username = antiinjeksi($_POST['username']);
          	$password = antiinjeksi($_POST['password']);

            $cekadmin  = $mysqli->query("SELECT AES_DECRYPT(usere,'nur') as username, AES_DECRYPT(passworde,'windi') as password FROM admin WHERE usere = AES_ENCRYPT('$username','nur')");
            $cekuser  = $mysqli->query("SELECT AES_DECRYPT(id_user,'nur') as username, AES_DECRYPT(password,'windi') as password FROM user WHERE id_user = AES_ENCRYPT('$username','nur')");

            if($cekadmin->num_rows == 1) {

                $data = $cekadmin->fetch_array();
                $adminutama = $data['username'];

                if($data['password'] !== $password) {
                    $errors[] = 'Kata kunci admin utama tidak valid.';
                }

                if (file_exists($dbFile)) {
                  $db->exec("CREATE TABLE IF NOT EXISTS lite_modules (id_modul integer NOT NULL PRIMARY KEY AUTOINCREMENT, judul TEXT, folder TEXT, menu TEXT, konten TEXT, widget TEXT, aktif TEXT)");
                  $db->exec("CREATE TABLE IF NOT EXISTS lite_roles (username TEXT, role TEXT, cap TEXT, module TEXT)");
                  $cekroles = $db->query("SELECT * FROM lite_roles WHERE username = '$adminutama'");
                  $result = $cekroles->fetchArray(SQLITE3_ASSOC);
                  if($result == false && $data['password'] == $password) {
                    $db->exec("INSERT INTO lite_roles (username, role, cap, module) VALUES ('$adminutama','admin','','')");
                  }
                }

            } else if($cekuser->num_rows == 1) {

                $data = $cekuser->fetch_array();

                if($data['password'] !== $password) {
                    $errors[] = 'Kata kunci tidak valid.';
                }

                $cekroles = $db->query("SELECT * FROM lite_roles WHERE username = '$data[username]'");
                if(!empty($cekroles)) {
                  $result = $cekroles->fetchArray(SQLITE3_ASSOC);                  
                }

                if($result == false) {
                    $errors[] = 'Kode login tidak terdaftar atau tidak aktif.';
                }


            } else {

                $errors[] = 'Kode login tidak terdaftar atau tidak aktif.';

            }

            if(!empty($errors)) {

                foreach($errors as $error) {
                    echo validation_errors($error);
                }

            } else {

                $_SESSION['username']     = $data['username'];
                $_SESSION['password']     = $data['password'];
                $_SESSION['login']        = 1;

                if (isset($_POST['rememberme'])) {
                    $_SESSION['timeout']      = time()+60*60*24*365;
                } else {
                    $_SESSION['timeout']      = time()+1000;
                }
                redirect('index.php');
            }

        }
        ?>

				<div class="card">
						<div class="body">
								<form id="sign_in" method="POST">
                    <div class="msg">Silahkan login dulu untuk memulai</div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="material-icons">person</i>
                        </span>
                        <div class="form-line">
                            <input type="text" class="form-control" name="username" placeholder="Kode Masuk" required autofocus>
                        </div>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="material-icons">lock</i>
                        </span>
                        <div class="form-line">
                            <input type="password" class="form-control" name="password" placeholder="Kata Kunci" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-8 p-t-5">
                            <input type="checkbox" name="rememberme" id="rememberme" class="filled-in chk-col-pink">
                            <label for="rememberme">Ingat saya</label>
                        </div>
                        <div class="col-xs-4 pull-right">
                            <button name="login" class="btn btn-block bg-pink waves-effect" type="submit">Login</button>
                        </div>
                    </div>
								</form>
						</div>
				</div>
		</div>

    <?php
    //logout
    if(isset($_GET['action']) == "logout"){
      session_destroy();
      echo "<script>alert('Anda telah logout');window.location = 'login.php';</script>";
    }
    ?>
		<script src="assets/js/admin.js"></script>
		<script src="assets/js/login.js"></script>
</body>

</html>
