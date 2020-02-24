<?php
ob_start();
session_start();

include_once('init.php');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title><?php echo $dataSettings['nama_instansi']; ?> &raquo; <?php echo $title; ?></title>
    <!-- Favicon-->
    <link rel="icon" href="<?php echo URL; ?>/assets/images/favicon.ico" type="image/x-icon">

    <!-- Google Fonts -->
    <link href="<?php echo URL; ?>/assets/css/roboto.css" rel="stylesheet">

    <!-- Material Icon Css -->
    <link href="<?php echo URL; ?>/assets/css/material-icon.css" rel="stylesheet">

    <!-- Webfont Medical Icon Css -->
    <link href="<?php echo URL; ?>/assets/css/wfmi-style.css" rel="stylesheet">

    <!-- Bootstrap Core Css -->
    <link href="<?php echo URL; ?>/assets/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Waves Effect Css -->
    <link href="<?php echo URL; ?>/assets/plugins/node-waves/waves.css" rel="stylesheet" />

    <!-- Animation Css -->
    <link href="<?php echo URL; ?>/assets/plugins/animate-css/animate.css" rel="stylesheet" />

    <!-- JQuery DataTable Css -->
    <link href="<?php echo URL; ?>/assets/plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css" rel="stylesheet">
    <link href="<?php echo URL; ?>/assets/plugins/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css" rel="stylesheet">

    <!-- Bootstrap Material Datetime Picker Css -->
    <link href="<?php echo URL; ?>/assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css" rel="stylesheet" />

    <!-- Bootstrap Select Css -->
    <link href="<?php echo URL; ?>/assets/plugins/bootstrap-select/css/bootstrap-select.css" rel="stylesheet" />

    <!-- Jquery-UI Css -->
    <link href="<?php echo URL; ?>/assets/css/jquery-ui.min.css" rel="stylesheet">

    <!-- Select2 Css -->
    <link href="<?php echo URL; ?>/assets/css/select2.min.css" rel="stylesheet">
    <link href="<?php echo URL; ?>/assets/plugins/light-gallery/css/lightgallery.css" rel="stylesheet">

    <?php
    if(isset($_GET['module'])) {
      if(file_exists('modules/'.$_GET['module'].'/css.php')) {
        include('modules/'.$_GET['module'].'/css.php');
      }
    }
    ?>

    <!-- Custom Css -->
    <link href="<?php echo URL; ?>/assets/css/style.css" rel="stylesheet">

    <link href="<?php echo URL;?>/modules/Odontogram/css/odontogram.css" rel="stylesheet" type="text/css" />

    <!-- AdminBSB Themes. You can choose a theme from css/themes instead of get all themes -->
    <link href="<?php echo URL; ?>/assets/css/themes/all-themes.min.css" rel="stylesheet" />

    <?php if(PWA == true) { ?>
    <meta name="theme-color" content="#6700DF">
    <link rel="manifest" href="<?php echo URL; ?>/manifest.json">
    <!-- iOS Support -->
    <link rel="apple-touch-icon" href="assets/icons/icon-96x96.png">
    <meta name="apple-mobile-web-app-status-bar" content="#FFFFFF">
    <?php } ?>


</head>

<body class="theme-<?php echo THEME; ?>">
    <!-- Page Loader -->
    <!-- #END# Page Loader -->
    <!-- Overlay For Sidebars -->
    <div class="overlay"></div>
    <!-- #END# Overlay For Sidebars -->
    <!-- Top Bar -->
    <nav class="navbar">
        <div class="container-fluid">
            <div class="navbar-header">
                <a href="javascript:void(0);" class="bars"></a>
                <a class="navbar-brand" href="<?php echo URL; ?>/index.php"><?php echo $dataSettings['nama_instansi']; ?> <?php if(FKTL == true) { echo "<span class='btn btn-xs bg-red'>v.FKTL</span>"; } else { echo "<span class='btn btn-xs bg-red'>v.FKTP</span>"; } ?></a>
            </div>
        </div>
    </nav>
    <!-- #Top Bar -->
