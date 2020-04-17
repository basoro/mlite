<?php

function aktifkan_modul(){
	global $mysqli;

	$sql = "CREATE TABLE IF NOT EXISTS `lite_contoh` (
			`id_pesan` int(10) NOT NULL AUTO_INCREMENT,
			`nama` varchar(100) NOT NULL,
			PRIMARY KEY(`id_pesan`)
			) ENGINE=MyISAM";
	$mysqli->query($sql);
}

function hapus_modul(){
	global $mysqli;
	$sql = "DROP TABLE `lite_contoh`";
	$mysqli->query($sql);
}

?>
