<?php
include_once ('../../../config.php');
$hasil1 = query("SELECT ifnull(MAX(CONVERT(no_antrian,signed)),0) from skdp_bpjs");
while ($sql = fetch_array($hasil1)){
    $antri = $sql['0']+1;
    $tmp = array('skdp' => $antri,);
    echo json_encode($tmp);
}