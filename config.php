<?php
//ini_set('display_errors', 0);
//error_reporting(E_ERROR | E_WARNING | E_PARSE);

define('VERSION', '3.0');
define('ABSPATH', dirname(__FILE__) . '/');
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'coba');

ini_set('memory_limit', '-1');

//Menggunakan objek mysqli untuk membuat koneksi dan menyimpanya dalam variabel $mysqli	//
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

//Menggunakan SQLite3 untuk menyimpan pengaturan Khanza Lite
$dbFile = ABSPATH.'includes/khanzalite.db';
$db= new SQLite3($dbFile);

//Menentukan timezone //
date_default_timezone_set('Asia/Jakarta');

//Membuat variabel yang menyimpan nilai waktu //
$year       = date('Y');
$last_year  = $year-1;
$next_year  = $year+1;
$curr_month = date('m');
$month      = date('Y-m');
$date       = date('Y-m-d');
$time       = date('H:i:s');
$date_time  = date('Y-m-d H:i:s');

?>
