<?php
session_start();
ob_start();
define("INDEX",true);
include("includes/init.php");
if($mysqli->query("SHOW TABLES LIKE 'setting'")->num_rows  !== 1) {
  header("Location: install.php");
}
$timeout = $_SESSION['timeout'];
if(time()<$timeout){
	$_SESSION['timeout'] = time()+5000;
}else{
	$_SESSION['login'] = 0;
}

if(empty($_SESSION['username']) or empty($_SESSION['password']) or $_SESSION['login']==0){
	header('location: login.php');
}else{
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
		<title><?php echo setting('nama_instansi'); ?></title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link href="assets/css/style.min.css" rel="stylesheet">
    <link href="assets/css/theme-indigo.min.css" rel="stylesheet" />
</head>

<body class="theme-indigo">
    <div class="page-loader-wrapper">
        <div class="loader">
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
            <p>Please wait...</p>
        </div>
    </div>
    <div class="overlay"></div>
    <div class="search-bar">
        <div class="search-icon">
            <i class="material-icons">search</i>
        </div>
        <input type="text" placeholder="START TYPING...">
        <div class="close-search">
            <i class="material-icons">close</i>
        </div>
    </div>
    <nav class="navbar">
        <div class="container-fluid">
            <div class="navbar-header">
                <a href="javascript:void(0);" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false"></a>
                <a href="javascript:void(0);" class="bars"></a>
                <a class="navbar-brand" href="index.php"><?php echo setting('nama_instansi'); ?></a>
            </div>
            <div class="collapse navbar-collapse" id="navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="javascript:void(0);" class="js-search" data-close="true"><i class="material-icons">search</i></a></li>
                    <li class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button">
                            <i class="material-icons">notifications</i>
                            <span class="label-count">1</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header">NOTIFICATIONS</li>
                            <li class="body">
                                <ul class="menu">
                                    <li>
                                        <a href="javascript:void(0);">
                                            <div class="icon-circle bg-light-green">
                                                <i class="material-icons">person_add</i>
                                            </div>
                                            <div class="menu-info">
                                                <h4>14 Pasien hari ini</h4>
                                                <p>
                                                    <i class="material-icons">access_time</i> 14 mins ago
                                                </p>
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="footer">
                                <a href="javascript:void(0);">View All Notifications</a>
                            </li>
                        </ul>
                    </li>
										<li><a href="login.php?action=logout"><i class="material-icons">exit_to_app</i> </a></li>
                </ul>
            </div>
        </div>
    </nav>
		<section>
        <aside id="leftsidebar" class="sidebar">
            <div class="user-info">
                <div class="image">
                    <img src="assets/images/user.png" width="48" height="48" alt="User" />
                </div>
                <div class="info-container">
                    <div class="name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo userinfo('nama'); ?></div>
                    <div class="email"><?php echo $_SESSION['username']; ?></div>
                </div>
            </div>
            <div class="menu">
                <ul class="list">
									<?php include ("includes/menu.php"); ?>
                </ul>
            </div>
            <div class="legal">
                <div class="copyright">
                    &copy; 2017 - <?php echo $year; ?> Made with <i class="material-icons" style="font-size:13px;color:red;">favorite</i> by <a href="javascript:void(0);">Basoro</a> (<b>V<?php echo VERSION; ?></b>)
                </div>
            </div>
        </aside>
    </section>
		<section class="content">
        <div class="container-fluid">
						<?php include("includes/content.php"); ?>
        </div>
    </section>
	<script src="assets/js/admin.js"></script>
	<?php
	 if(function_exists("addCSS")){
		 addCSS();
	 }
	?>
	<?php
	 if(function_exists("addJS")){
		 addJS();
	 }
	?>
	<script src="assets/js/script.js"></script>
</body>
</html>
<?php
}
?>
