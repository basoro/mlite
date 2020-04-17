<?php
$module = isset($_GET['module']) ? $_GET['module'] : 'dashboard';
$kosong = true;
$page = array('dashboard', 'pasien', 'pendaftaran', 'booking', 'kasir', 'modul', 'user', 'setting');
foreach($page as $pg){
  if($module == $pg and $kosong){
    include 'pages/'.$pg.'.php';
    $kosong = false;
  }
}
$query = $mysqli->query("SELECT * FROM lite_modul");
while($data = $query->fetch_array()){
  if(file_exists("modules/$data[folder]/admin.php")){
    if($module == $data['folder'] and $kosong){
      include "modules/$data[folder]/admin.php";
      $kosong = false;
    }
  }
}
if($kosong){
  echo '<div class="alert alert-warning"><b>Sorry</b>, Halaman tidak ditemukan!</div>';
}
?>
