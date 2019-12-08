<?php
include('../../../config.php');
include('../../../init.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Aplikasi Antrian Pasien Rawat Jalan</title>
 	<link href="<?php echo URL; ?>/modules/APM/inc/css/bootstrap.min.css" rel="stylesheet" />
  <link href="<?php echo URL; ?>/modules/APM/inc/css/fontawesome.min.css" rel="stylesheet" />
  <script src="js/jquery.min.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <style>
  body{
  	font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
  	background: #0264d6; /* Old browsers */
  	background: -moz-radial-gradient(center, ellipse cover,  #0264d6 1%, #1c2b5a 100%); /* FF3.6+ */
  	background: -webkit-gradient(radial, center center, 0px, center center, 100%, color-stop(1%,#0264d6), color-stop(100%,#1c2b5a)); /* Chrome,Safari4+ */
  	background: -webkit-radial-gradient(center, ellipse cover,  #0264d6 1%,#1c2b5a 100%); /* Chrome10+,Safari5.1+ */
  	background: -o-radial-gradient(center, ellipse cover,  #0264d6 1%,#1c2b5a 100%); /* Opera 12+ */
  	background: -ms-radial-gradient(center, ellipse cover,  #0264d6 1%,#1c2b5a 100%); /* IE10+ */
  	background: radial-gradient(ellipse at center,  #0264d6 1%,#1c2b5a 100%); /* W3C */
  	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#0264d6', endColorstr='#1c2b5a',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */
  	height:calc(100vh);
  	width:100%;
  }
  .btn-group-justified {
    display: table;
    width: 100%;
    table-layout: fixed;
    border-collapse: separate;
  }
  .btn-group-justified .btn,
  .btn-group-justified .btn-group {
    float: none;
    display: table-cell;
    width: 1%;
  }
  .btn-group-justified .btn .btn,
  .btn-group-justified .btn-group .btn {
    width: 100%; }
  .btn-group-justified .btn .dropdown-menu,
  .btn-group-justified .btn-group .dropdown-menu {
    left: auto;
  }
  </style>
  <script src="<?php echo URL; ?>/modules/APM/inc/js/jquery.min.js" type="text/javascript"></script>
</head>
<body>
  <?php $action = isset($_GET['action'])?$_GET['action']:null; ?>
  <?php if(!$action){ ?>
  <div class="px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center mb-4">
    <div class="text-white display-3">ANTRIAN PASIEN RAWAT JALAN</div>
  </div>

  <div class="container-fluid">
    <div class="row">
      <div class="col-9">
        <div class="card bg-dark text-white">
          <div class="embed-responsive embed-responsive-16by9">
            <video class="embed-responsive-item" src="video/demo.mp4" controls width="576" auto height="447" loop></video>
          </div>
        </div>
      </div>
      <div class="col-3 ">
        <a href="antrian.php?action=panggil_loket" style="text-decoration:none; color:#000;">
          <div class="card border-success mb-4">
            <div class="card-body text-success">
              <div style="font-size:100px;font-weight:lighter;padding:0;margin-top:-45px;margin-bottom:-45px;">A<span class="antrian_loket"><span></div>
            </div>
            <div class="card-footer bg-transparent border-success" style="font-size:52px;padding-top:0;padding-bottom:0;">Loket <span class="get_loket"><span></div>
          </div>
        </a>
        <a href="antrian.php?action=panggil_cs" style="text-decoration:none; color:#000;">
          <div class="card border-success mb-4">
            <div class="card-body text-success">
              <div style="font-size:100px;font-weight:lighter;padding:0;margin-top:-45px;margin-bottom:-45px;">B<span class="antrian_cs"><span></div>
            </div>
            <div class="card-footer bg-transparent border-success" style="font-size:52px;padding-top:0;padding-bottom:0;">Loket <span class="get_cs"><span></div>
          </div>
        </a>
        <a href="antrian.php?action=panggil_prioritas" style="text-decoration:none; color:#000;">
          <div class="card border-success mb-4">
            <div class="card-body text-success">
              <div style="font-size:100px;font-weight:lighter;padding:0;margin-top:-45px;margin-bottom:-45px;">C<span class="antrian_prioritas"><span></div>
            </div>
            <div class="card-footer bg-transparent border-success" style="font-size:52px;padding-top:0;padding-bottom:0;">Loket <span class="get_prioritas"><span></div>
          </div>
        </a>
      </div>
    </div>
    <div class="row">
    <div class="col-12 d-flex flex-column justify-content-end pl-2 pr-2 pb-0 mt-3">
      <div class="card-title bg-primary text-white p-2" style="font-size:36px;"><marquee>
        <?php
        $jadwal = query("SELECT
            dokter.nm_dokter,
            poliklinik.nm_poli,
            DATE_FORMAT(jadwal.jam_mulai, '%H:%i') AS jam_mulai,
            DATE_FORMAT(jadwal.jam_selesai, '%H:%i') AS jam_selesai
        FROM jadwal
        INNER JOIN dokter
        INNER JOIN poliklinik on dokter.kd_dokter=jadwal.kd_dokter
        AND jadwal.kd_poli=poliklinik.kd_poli
        WHERE jadwal.hari_kerja='$namahari'");
        while ($row = fetch_array($jadwal)) {
          echo '<i class="fas fa-user-md"></i> '.$row['0'].' - '.$row['1'].' - '.$row['2'].' s/d '.$row['3'].' WITA &nbsp;&nbsp;&nbsp; ';
        }
        ?>
      </marquee></div>
    </div>
    </div>
  </div>
<?php } ?>
<?php if($action == 'panggil_cs') { ?>
<?php include('includes/panggil_cs.php'); ?>
<?php } ?>
<?php if($action == 'panggil_loket') { ?>
<?php include('includes/panggil_loket.php'); ?>
<?php } ?>
<?php if($action == 'panggil_prioritas') { ?>
<?php include('includes/panggil_prioritas.php'); ?>
<?php } ?>
<script>

setInterval(function(){ getAntrianLoket(); }, 2000);

function getAntrianLoket() {
  $.ajax({
    url: 'includes/antriloket.php',
    type: 'post',
    success: function(data) {
      $('.antrian_loket').html(data);
    }
  });
};

setInterval(function(){ getAntrianCS(); }, 2000);

function getAntrianCS() {
  $.ajax({
    url: 'includes/antrics.php',
    type: 'post',
    success: function(data) {
      $('.antrian_cs').html(data);
    }
  });
};

setInterval(function(){ getAntrianPrioritas(); }, 2000);

function getAntrianPrioritas() {
  $.ajax({
    url: 'includes/antriprioritas.php',
    type: 'post',
    success: function(data) {
      $('.antrian_prioritas').html(data);
    }
  });
};

setInterval(function(){ getLoket(); }, 2000);

function getLoket() {
  $.ajax({
    url: 'includes/loket.php',
    type: 'post',
    success: function(data) {
      $('.get_loket').html(data);
    }
  });
};

setInterval(function(){ getCS(); }, 2000);

function getCS() {
  $.ajax({
    url: 'includes/cs.php',
    type: 'post',
    success: function(data) {
      $('.get_cs').html(data);
    }
  });
};

setInterval(function(){ getPrioritas(); }, 2000);

function getPrioritas() {
  $.ajax({
    url: 'includes/prioritas.php',
    type: 'post',
    success: function(data) {
      $('.get_prioritas').html(data);
    }
  });
};

</script>
</body>
</html>
