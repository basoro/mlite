<?php
ob_start();
session_start();
include_once ('../config.php');
$kode = $_GET['kode'];
$hasil1 = mysqli_query($connection,"SELECT ifnull(MAX(CONVERT(no_reg,signed)),0) from booking_registrasi WHERE tanggal_periksa = '$kode' AND kd_poli = '".$_SESSION['jenis_poli']."'");
while ($sql = mysqli_fetch_array($hasil1)){
    $antri = sprintf('%03s', ($sql['0']+1));
    // $no_reg = sprintf('%03s', ($no_urut_reg + 1));
    $tmp = array('noreg'=>$antri);
    echo json_encode($tmp);
    //echo "<dd><input type='text' id='noreg' class='form-control' name='noreg' value='00".$antri."' placeholder='No Antrian' required></dd>";
};
?>
