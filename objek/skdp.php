<?php 
$sql = "SELECT no_antrian FROM skdp_bpjs WHERE no_rkm_medis = '{$b['no_rkm_medis']}' and tanggal_datang = '{$date}'";
$skdp = query($sql);
$sksk = mysqli_fetch_assoc($skdp);
?>