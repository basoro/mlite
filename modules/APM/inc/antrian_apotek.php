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
  ul.list-group.list-group-striped li:nth-of-type(odd){
      background: #ddd;
  }
  ul.list-group.list-group-striped li:nth-of-type(even){
      background: #999;
  }
  ul.list-group.list-group-hover li:hover{
      background: #888;
  }
  </style>
  <script src="<?php echo URL; ?>/modules/APM/inc/js/jquery.min.js" type="text/javascript"></script>
</head>
<body>
  <div class="px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center mb-4">
    <div class="text-white display-3">ANTRIAN APOTEK RAWAT JALAN</div>
  </div>

  <div class="container-fluid">
    <div class="row">
      <div class="col-8">
        <div class="card bg-dark text-white">
          <div class="embed-responsive embed-responsive-16by9">
            <video class="embed-responsive-item" src="video/demo.mp4" controls width="576" auto height="547" loop></video>
          </div>
        </div>
      </div>
      <div class="col-4">
        <div class="card border-success mb-4">
          <div class="card-body">
            <marquee direction="up" height="480">
              <ul class="list-unstyled list-group list-group-hover list-group-striped antrian_apotek">
              </ul>
            </marquee>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
    <div class="col-12 d-flex flex-column justify-content-end pl-2 pr-2 pb-0 mt-3">
      <div class="card-title bg-primary text-white p-2" style="font-size:36px;">
        <marquee>
          Ini isinya informasi atau pengumuman atau apasaja. Tambaheeee laageeee.....!!
        </marquee>
      </div>
    </div>
    </div>
  </div>
<script>

setInterval(function(){ getAntrianApotek(); }, 2000);

function getAntrianApotek() {
  $.ajax({
    url: 'includes/antriapotek.php',
    type: 'post',
    success: function(data) {
      $('.antrian_apotek').html(data);
    }
  });
};
</script>
</body>
</html>
