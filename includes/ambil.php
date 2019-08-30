<?php
include_once ('../config.php');
$hasil1 = mysqli_query($connection,"SELECT ifnull(MAX(CONVERT(no_antrian,signed)),0) from skdp_bpjs");
while ($sql = mysqli_fetch_array($hasil1)){
    $antri = $sql['0']+1;
    echo "<dd><input type='text' id='antri' class='form-control' name='noan' value='0".$antri."' placeholder='No Antrian' required></dd>";
};
?>
