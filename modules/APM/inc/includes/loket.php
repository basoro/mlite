<?php
include('../../../../config.php');
$antriloket = fetch_assoc(query("SELECT loket FROM antriloket"));
$antriloket = $antriloket['loket'];
echo $antriloket;
?>
