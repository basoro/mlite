<?php
	require("../../../config.php");
	session_start();
	require("functions.php");
	if(isset($_POST) && count($_POST)){
		$action = $_POST['act'];

		switch($action){
			case "getq":
				echo get_questions();
			break;
			case "suba":
				save_answer($_POST);
			break;
			case "getq":
			break;
		}
	}
?>
