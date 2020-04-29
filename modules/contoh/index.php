<?php
function info_modul(){
  $info['module_title']       = 'Contoh';
  $info['module_version']     = '1.0';
  $info['module_directory']   = 'contoh';
  return $info;
}
buat_menu_dashboard("contoh", "folder", "Modul Contoh", array("admin"));
?>
