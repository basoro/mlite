<?php
require_once('lite/config.php'); // Sesuakan dengan absolute path config.php
$theme = 'default';
$mode = isset($_GET['mode'])?$_GET['mode']:'index';
$module_dir = DIR.'modules/Website/';
$module_file = $mode.'.php';
if(file_exists($module_dir.'/themes/'.$theme. '/' .$module_file)) {
  include($module_dir.'/themes/'.$theme. '/' .$module_file);
} else {
  echo '<div class="alert bg-pink alert-dismissible" role="alert">Halaman tidak ditemukan!</div>';
}
?>
