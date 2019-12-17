<?php
include('../../../../config.php');
$antriloket = fetch_assoc(query("SELECT loket FROM antriprioritas"));
$antriloket = $antriloket['loket'];
echo $antriloket;
?>
