<?php
echo '<li class="header">MENU UTAMA</li>';
buat_menu("dashboard", "home", "Dashboard", array("admin","manajemen"));
buat_menu("pendaftaran", "assignment", "Pendaftaran", array("admin","manajemen"));
buat_menu("pasien", "people", "Data Pasien", array("admin"));

if (file_exists($dbFile)) {
  $query = $db->query("SELECT * FROM lite_modules WHERE aktif = 'Y' ORDER BY id_modul");
  while($data = $query->fetchArray()){
    if(file_exists("modules/$data[folder]/menu.php")){
      include "modules/$data[folder]/menu.php";
    }
  }
}

echo '<li class="header">ADMINISTRASI</li>';
buat_menu("user", "person_add", "Pengguna", array("admin", "manajemen"));
buat_menu("modul", "view_list", "Pengaturan Modul", array("admin","author"));
buat_menu("setting", "settings", "Pengaturan Aplikasi", array("admin"));
?>
