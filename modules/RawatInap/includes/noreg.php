<?php
ob_start();
session_start();
include_once ('../../../config.php');
$kode = $_GET['kode'];
$poli = $_GET['poli'];
$hasil1 = query("SELECT ifnull(MAX(CONVERT(no_reg,signed)),0) from booking_registrasi WHERE tanggal_periksa = '$kode' AND kd_poli = '$poli'");
while ($sql = fetch_array($hasil1)){
    $antri = sprintf('%03s', ($sql['0']+1));
    // $no_reg = sprintf('%03s', ($no_urut_reg + 1));
    $tmp = array('noreg'=>$antri);
    echo json_encode($tmp);
    //echo "<dd><input type='text' id='noreg' class='form-control' name='noreg' value='00".$antri."' placeholder='No Antrian' required></dd>";
};
?>
