<?php
if(!defined("INDEX")) header('location: ../index.php');
if(userroles('role')!="admin") header('location: index.php');

$show = isset($_GET['show']) ? $_GET['show'] : "";
$link = "?module=modul";
switch($show){

	//Menampilkan data
	default:
		echo '<div class="block-header">';
		echo '	<h2>';
		echo '		<a href="'.$link.'&show=form" class="btn btn-primary btn-sm pull-right top-button">';
		echo '		<i class="glyphicon glyphicon-plus-sign"></i> Tambah';
		echo '	</a>';
		echo '	DATA MODUL';
		echo '	<small>Periode '.tgl_indonesia($date).'</small>';
		echo '</h2>';
		echo '</div>';

		buka_section_body('Tabel Modul');
		buka_tabel(array("Judul", "Aktif"));
		$no = 1;
		$query = $mysqli->query("SELECT * FROM lite_modul ORDER BY aktif");
		while($data = $query->fetch_array()){
			if($data['aktif']=='Y') $aktif = '<a href="'.$link.'&show=deactivate&id='.$data['id_modul'].'" style="color: green"><i class="material-icons">done</i></a>';
			else $aktif = '<a href="'.$link.'&show=activate&id='.$data['id_modul'].'" style="color: red"><i class="material-icons">not_interested</i></a>';

			isi_tabel($no, array($data['judul'], $aktif), $link, $data['id_modul']);
			$no++;
		}
		tutup_tabel();
		tutup_section_body();

	break;

	//Menampilkan form input dan edit data
	case "form":
		echo '<div class="block-header">';
		echo '	<h2>';
		echo '	DATA MODUL';
		echo '	<small>Periode '.tgl_indonesia($date).'</small>';
		echo '</h2>';
		echo '</div>';

		if(isset($_GET['id'])){
			$query 	= $mysqli->query("SELECT * FROM lite_modul WHERE id_modul='$_GET[id]'");
			$data	= $query->fetch_array();
			$aksi 	= "Edit";
		}else{
			$data = array("id_modul"=>"", "judul"=>"");
			$aksi 	= "Tambah";
		}

		buka_section_body($aksi.' Modul');
		buka_form($link, $data['id_modul'], strtolower($aksi));
			buat_textbox("Judul", "judul", $data['judul']);
			if($aksi=="Tambah") buat_textbox("File", "file", "", 4, "file");
		tutup_form($link);
		tutup_section_body();
	break;

	//Menyisipkan atau mengedit data di database
	case "action":
		if($_POST['aksi'] == "tambah"){
			$filename = $_FILES["file"]["name"];
			$source = $_FILES["file"]["tmp_name"];
			$type = $_FILES["file"]["type"];

			$nama = explode('.', $filename);
			if($nama[1] != "zip"){
				echo'<script>
						window.alert("File yang diupload tidak bertipe .zip. Silakan di ulang");
						window.location.href=history.back();
					</script>';
			}else{
				unzip_file($filename, $source, "modules/");
				$mysqli->query("INSERT INTO lite_modul SET
					judul 		= '$_POST[judul]',
					folder 		= '$nama[0]',
					aktif		= 'N'
				");
				header('location:'.$link);
			}
		}elseif($_POST['aksi'] == "edit"){
			$mysqli->query("UPDATE lite_modul SET
				judul 		= '$_POST[judul]'
			WHERE id_modul='$_POST[id]'");
			header('location:'.$link);
		}
	break;

	//Menghapus data di database
	case "delete":
		$query 	= $mysqli->query("SELECT * FROM lite_modul WHERE id_modul='$_GET[id]'");
		$data	= $query->fetch_array();

		if(file_exists("modules/$data[folder]/function.php")){
			include "modules/$data[folder]/function.php";
			hapus_modul();
		}

		hapus_folder("modules/$data[folder]");

		$mysqli->query("DELETE FROM lite_modul WHERE id_modul='$_GET[id]'");
		header('location:'.$link);
	break;

	//Mengaktifkan data
	case "activate":
		$query 	= $mysqli->query("SELECT * FROM lite_modul WHERE id_modul='$_GET[id]'");
		$data	= $query->fetch_array();

		$menu = (file_exists("modules/$data[folder]/menu.php")) ? 'Y' : 'N';
		$konten = (file_exists("modules/$data[folder]/content.php")) ? 'Y' : 'N';
		$widget = (file_exists("modules/$data[folder]/widget.php")) ? 'Y' : 'N';

		if(file_exists("modules/$data[folder]/function.php")){
			include "modules/$data[folder]/function.php";
			aktifkan_modul();
		}

		$mysqli->query("UPDATE lite_modul SET
			aktif	= 'Y',
			menu	= '$menu',
			konten	= '$konten',
			widget  = '$widget'
		WHERE id_modul='$_GET[id]'");
		header('location:'.$link);
	break;

	//Menonaktifkan data
	case "deactivate":

		$query 	= $mysqli->query("SELECT * FROM lite_modul WHERE id_modul='$_GET[id]'");
		$data	= $query->fetch_array();

		if(file_exists("modules/$data[folder]/function.php")){
			include "modules/$data[folder]/function.php";
			hapus_modul();
		}

		$mysqli->query("UPDATE lite_modul SET aktif='N' WHERE id_modul='$_GET[id]'");
		header('location:'.$link);
	break;
}

function addCSS() {
	echo '<link href="assets/plugins/bootstrap-select/css/bootstrap-select.css" rel="stylesheet" />';
	dataTablesCSS();
}
function addJS() {
	echo '<script src="assets/plugins/bootstrap-select/js/bootstrap-select.js"></script>';
	dataTablesJS();
	dataTablesDisplay();
}

?>
