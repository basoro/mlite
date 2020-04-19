<?php
$module = isset($_GET['module']) ? $_GET['module'] : 'dashboard';
$kosong = true;
$page = array('dashboard', 'pasien', 'pendaftaran', 'modul', 'user', 'setting');
foreach($page as $pg){
  if($module == $pg and $kosong){
    include 'pages/'.$pg.'.php';
    $kosong = false;
  }
}

$query = $db->query("SELECT * FROM lite_modules");
while($data = $query->fetchArray()){
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
