<?php
	if(!empty($_POST['pid'])) {
		include_once '../../../config.php';
		include_once '../../../init.php';
		$username = $_SESSION['username'];
		$pid = $_POST['pid'];
		$query = query("DELETE FROM `posts` WHERE `username` = '{$username}' AND `pid` = $pid ");
	}
?>
