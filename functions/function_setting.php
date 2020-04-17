<?php
// Fungsi untuk mendapatkan data pada tabel setting
function setting($parameter){
	global $mysqli;
	$query = $mysqli->query("SELECT nama_instansi, alamat_instansi, kabupaten, propinsi, kontak, email, kode_ppk, kode_ppkinhealth, kode_ppkkemenkes, logo FROM setting");
	$setting = $query->fetch_array();
	return $setting[$parameter];
}
// redirect to another page
function redirect($location) {
    return header("Location: {$location}");
}
// add message to session
function set_message($message) {
    if(!empty($message)) {
        $_SESSION['message'] = $message;
    } else {
        $message = "";
    }
}
// display session message
function display_message() {
    if(isset($_SESSION['message'])) {
        echo '<div class="alert bg-pink alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.$_SESSION['message'].'</div>';
        unset($_SESSION['message']);
    }
}
// show errors
function validation_errors($error) {
    $errors = '<div class="alert bg-pink alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.$error.'</div>';
    return $errors;
}
function antiinjeksi($text){
	global $mysqli;
	$safetext = $mysqli->real_escape_string(stripslashes(strip_tags(htmlspecialchars($text,ENT_QUOTES))));
	return $safetext;
}
function tgl_indonesia($tgl){
	$nama_bulan = array(1=>"Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");

	$tanggal = substr($tgl,8,2);
	$bulan = $nama_bulan[(int)substr($tgl,5,2)];
	$tahun = substr($tgl,0,4);

	return $tanggal.' '.$bulan.' '.$tahun;
}
function unzip_file($filename, $source,  $target){
	$target_path = $target.$filename;
	move_uploaded_file($source, $target_path);
	$zip = new ZipArchive();
	$x = $zip->open($target_path);
	if ($x === true) {
		$zip->extractTo($target);
		$zip->close();

		unlink($target_path);
	}
}
function hapus_folder($path) {
 	$files = glob($path . '/*');
	foreach ($files as $file) {
		is_dir($file) ? hapus_folder($file) : unlink($file);
	}
	rmdir($path);
 	return;
}
function file_get_contents_curl($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
	curl_setopt($ch, CURLOPT_URL, $url);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}
function foldersize($path) {
  $total_size = 0;
  $files = scandir($path);
  $cleanPath = rtrim($path, '/'). '/';

  foreach($files as $t) {
      if ($t<>"." && $t<>"..") {
          $currentFile = $cleanPath . $t;
          if (is_dir($currentFile)) {
              $size = foldersize($currentFile);
              $total_size += $size;
          }
          else {
              $size = filesize($currentFile);
              $total_size += $size;
          }
      }
  }

  return $total_size;
}

function roundSize($bytes) {
    if ($bytes/1024 < 1) {
        return $bytes.' B';
    }
    if ($bytes/1024/1024 < 1) {
        return round($bytes/1024).' KB';
    }
    if ($bytes/1024/1024/1024 < 1) {
        return round($bytes/1024/1024, 2).' MB';
    } else {
        return round($bytes/1024/1024/1024, 2).' GB';
    }
}
function hitung_data_dashboard($name, $table, $icon, $link, $warna){
	global $mysqli;
	$query = $mysqli->query("select * from $table");
	$jml_data = $query->num_rows;
  echo '<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
        <div class="info-box bg-'.$warna.' hover-expand-effect">
            <div class="icon">
                <i class="material-icons">'.$icon.'</i>
            </div>
            <div class="content">
                <div class="text">TOTAL '.$name.'</div>
                <div class="number count-to" data-from="0" data-to="'.$jml_data.'" data-speed="1000" data-fresh-interval="20"></div>
            </div>
        </div>
    </div>';

}
?>
