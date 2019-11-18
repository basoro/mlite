<?php
if(!empty($_POST['cid'])) {
	include_once '../../../config.php';
	include_once '../../../init.php';
	$cid = $_POST['cid'];
	$query = query("DELETE FROM `comments` WHERE `cid` = $cid ");
}
?>
