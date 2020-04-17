<?php
echo '<li class="header">MENU UTAMA</li>';
buat_menu("dashboard", "home", "Dashboard", array("admin","manajemen"));
buka_dropdown("assignment", "Admisi", array("pendaftaran", "booking", "pasien"), array("admin"));
  buat_submenu("pendaftaran", "Pendaftaran", array("admin"));
  buat_submenu("booking", "Booking", array("admin"));
  buat_submenu("pasien", "Data Pasien", array("admin"));
tutup_dropdown(array("admin","author"));
buat_menu("kasir", "monetization_on", "Kasir", array("admin","none"));
$query = $mysqli->query("SELECT * FROM lite_modul WHERE menu='Y' AND aktif='Y'");
while($data = $query->fetch_array()){
  if(file_exists("modules/$data[folder]/menu.php")){
    include "modules/$data[folder]/menu.php";
  }
}
echo '<li class="header">ADMINISTRASI</li>';
buat_menu("user", "person_add", "Pengguna", array("admin", "manajemen"));
buka_dropdown("settings", "Pengaturan", array("setting", "modul"), array("admin","author"));
  buat_submenu("setting", "Pengaturan Aplikasi", array("admin"));
  buat_submenu("modul", "Pengaturan Modul", array("admin","author"));
tutup_dropdown(array("admin","author"));
?>
