<?php 
include_once '../config.php';
$sql = query("INSERT INTO rawat_jl_pr VALUES ('{$no_rawat}','{$_POST['kd_tdk']}','{$_POST['username']}','$date','$time','0','0','{$_POST['kdtdk']}','0','0','{$_POST['kdtdk']}','Belum')");

?>