<?php
include('../../../config.php');
include('../../../init.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="180">
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
</head>
<body class="bg-light text-white">
  <h1 class="display-3 text-center text-white m-3">Antrian Poliklinik <?php echo $dataSettings['nama_instansi']; ?></h1>

  <table class="table table-bordered table-striped lead">
    <thead>
      <tr>
        <th scope="col" class="bg-primary" width="35%">Dokter/Klinik</th>
        <th scope="col" class="bg-success" width="30%">Dalam Pemeriksaan</th>
        <th scope="col" class="bg-warning" width="35%">Nomor Urut Berikutnya</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $master = query("SELECT a.kd_dokter, a.kd_poli, b.nm_poli, c.nm_dokter, a.jam_mulai, a.jam_selesai FROM jadwal a, poliklinik b, dokter c WHERE a.kd_poli = b.kd_poli AND a.kd_dokter = c.kd_dokter AND a.hari_kerja = '$namahari'  AND a.kd_poli IN ($poli_hari_ini)");
      while ($row = fetch_array($master)) {
        $dalam_pemeriksaan = fetch_assoc(query("SELECT a.no_reg, b.nm_pasien FROM reg_periksa a, pasien b WHERE a.tgl_registrasi = '$date' AND a.no_rkm_medis = b.no_rkm_medis AND a.stts = 'Berkas Diterima' AND a.kd_poli = '$row[kd_poli]' AND a.kd_dokter = '$row[kd_dokter]' LIMIT 1"));
        echo '<tr>';
        echo '  <th>'.$row['nm_dokter'].'<br>'.$row['nm_poli'].'</th>';
        if($dalam_pemeriksaan == '') {
          echo '  <td class="align-middle">Kosong</td>';
        } else {
          echo '  <td class="align-middle">('.$dalam_pemeriksaan['no_reg'].') '.$dalam_pemeriksaan['nm_pasien'].'</td>';
        }
        $max_reg = fetch_array(query("SELECT MAX(no_reg) FROM reg_periksa WHERE tgl_registrasi = '$date' AND kd_poli = '$row[kd_poli]' AND kd_dokter = '$row[kd_dokter]' ORDER BY no_reg ASC"));
        $no_max_reg = substr($max_reg['0'], 0, 3);
        $selanjutnya = query("SELECT a.no_reg, b.nm_pasien FROM reg_periksa a, pasien b WHERE a.tgl_registrasi = '$date' AND a.no_rkm_medis = b.no_rkm_medis AND a.stts = 'Belum' AND a.kd_poli = '$row[kd_poli]' AND a.kd_dokter = '$row[kd_dokter]' ORDER BY a.no_reg ASC");
        echo '  <td class="align-middle"><marquee scrollamount="3">';
        while($row2 = fetch_array($selanjutnya)) {
          $no_urut_reg = substr($row2['no_reg'], 0, 3);
          $diff = ($row['jam_selesai'] - $row['jam_mulai']) * 60;
          $get_interval = round($diff/$no_max_reg);
          if($get_interval > 10){
            $interval = 10;
          } else {
            $interval = $get_interval;
          }
          $minutes = $no_urut_reg * $interval;
          $row['jam_mulai'] = date('H:i:s',strtotime('+10 minutes',strtotime($row['jam_mulai'])));
          echo '- '.$row2['nm_pasien'].' (<strong>'.$row2['no_reg'].' - '.$row['jam_mulai'].'</strong>) ';
        }
        echo '  </marquee></td>';
        echo '</tr>';
      }
      ?>
    </tbody>
  </table>
</body>
</html>
