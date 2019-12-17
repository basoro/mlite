<?php
include('../../../../config.php');
$antriloket = fetch_assoc(query("SELECT loket FROM antrics"));
$antriloket = $antriloket['loket'];
echo $antriloket;
?>
