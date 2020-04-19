<?php
// Fungsi untuk mendapatkan data pada tabel setting
function userinfo($parameter){
	global $mysqli;
	$username = $_SESSION['username'];
	$query = $mysqli->query("SELECT * FROM pegawai WHERE nik = '$username'");
	$setting = $query->fetch_array();
	return $setting[$parameter];
}

function userroles($parameter){
	global $db;
	$username = $_SESSION['username'];
	$query = $db->query("SELECT * FROM lite_roles WHERE username = '$username'");
	$setting = $query->fetchArray();
	return $setting[$parameter];
}

function usercap($parameter){
	global $db;
	$username = $_SESSION['username'];
	$query = $db->query("SELECT * FROM lite_roles WHERE username = '$username'");
	$setting = $query->fetchArray();
	return $setting[$parameter];
}

function usermodule($parameter){
	global $db;
	$username = $_SESSION['username'];
	$query = $db->query("SELECT * FROM lite_roles WHERE username = '$username'");
	$setting = $query->fetchArray();
	return $setting[$parameter];
}

function pasieninfo($no_rkm_medis, $parameter){
	global $mysqli;
	$query = $mysqli->query("SELECT * FROM pasien WHERE no_rkm_medis = '$no_rkm_medis'");
	$setting = $query->fetch_array();
	return $setting[$parameter];
}

function reginfo($no_rawat, $parameter){
	global $mysqli;
	$query = $mysqli->query("SELECT a.nm_pasien, b.no_rkm_medis, a.alamat, c.png_jawab, d.nm_poli, b.no_rawat, b.no_reg, b.tgl_registrasi, b.jam_reg, b.p_jawab, b.almt_pj, b.stts, f.kd_dokter, f.nm_dokter, b.kd_poli, c.kd_pj, a.no_tlp FROM pasien a, reg_periksa b, penjab c, poliklinik d, dokter f WHERE a.no_rkm_medis = b.no_rkm_medis AND b.kd_pj = c.kd_pj AND b.kd_poli = d.kd_poli AND b.kd_dokter = f.kd_dokter AND b.no_rawat = '$no_rawat'");
	$setting = $query->fetch_array();
	return $setting[$parameter];
}

function rawatinfo($no_rawat, $parameter){
	global $mysqli;
	$query = $mysqli->query("SELECT * FROM reg_periksa WHERE no_rawat = '$no_rawat'");
	$setting = $query->fetch_array();
	return $setting[$parameter];
}

function propinsi($kd_prop){
	global $mysqli;
	$query = $mysqli->query("SELECT * FROM propinsi WHERE kd_prop = '$kd_prop'");
	$result = $query->fetch_array();
	return $result['nm_prop'];
}

function kabupaten($kd_kab){
	global $mysqli;
	$query = $mysqli->query("SELECT * FROM kabupaten WHERE kd_kab = '$kd_kab'");
	$result = $query->fetch_array();
	return $result['nm_kab'];
}

function kecamatan($kd_kec){
	global $mysqli;
	$query = $mysqli->query("SELECT * FROM kecamatan WHERE kd_kec = '$kd_kec'");
	$result = $query->fetch_array();
	return $result['nm_kec'];
}

function kelurahan($kd_kel){
	global $mysqli;
	$query = $mysqli->query("SELECT * FROM kelurahan WHERE kd_kel = '$kd_kel'");
	$result = $query->fetch_array();
	return $result['nm_kel'];
}

?>
