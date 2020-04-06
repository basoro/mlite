<?php
include('../../../config.php');

$sus = new StdClass();
$sus->PASIEN = new StdClass();
$sus->PASIEN->NORM = $_POST['norm'];
$sus->PASIEN->NIK = $_POST['norm'];
$sus->PASIEN->NO_KARTU_JKN = $_POST['nops'];
$sus->PASIEN->NAMA = $_POST['nmps'];
$sus->PASIEN->JENIS_KELAMIN = $_POST['jk'] == "L" ? "1" : "2" ;
$sus->PASIEN->TANGGAL_LAHIR = $_POST['tgllhr'];
$sus->PASIEN->TEMPAT_LAHIR = $_POST['tmplhr'];
$sus->PASIEN->ALAMAT = $_POST['norm'];
$sus->PASIEN->KONTAK = $_POST['notlp'];
$sus->RUJUKAN = new StdClass();
$sus->RUJUKAN->JENIS_RUJUKAN = $_POST['jns_rujuk'];
$sus->RUJUKAN->PELAYANAN = $_POST['kdpel'];
$sus->RUJUKAN->NOMOR_RUJUKAN_BPJS = "";
$sus->RUJUKAN->KRITERIA = new StdClass();
$sus->RUJUKAN->KRITERIA->KRITERIA_RUJUKAN = "";
$sus->RUJUKAN->KRITERIA->KRITERIA_KHUSUS = "";
$sus->RUJUKAN->KRITERIA->SDM = "";
$sus->RUJUKAN->KRITERIA->PELAYANAN = "";
$sus->RUJUKAN->KRITERIA->KELAS_PERAWATAN = "";
$sus->RUJUKAN->KRITERIA->JENIS_PERAWATAN = "";
$sus->RUJUKAN->KRITERIA->ALAT = "";
$sus->RUJUKAN->KRITERIA->SARANA = "";
$sus->RUJUKAN->TANGGAL = $_POST['tgl_rujuk'];
$sus->RUJUKAN->FASKES_TUJUAN = $_POST['faskes'];
$sus->RUJUKAN->ALASAN = $_POST['alasan'];
$sus->RUJUKAN->ALASAN_LAINNYA = $_POST['alasan_lain'];
$sus->RUJUKAN->DIAGNOSA = $_POST['diagnosa'];
$sus->RUJUKAN->DOKTER = new StdClass();
$sus->RUJUKAN->DOKTER->NIK = $_POST['dr'];
$sus->RUJUKAN->DOKTER->NAMA = $_POST['kddr'];
$sus->RUJUKAN->PETUGAS = new StdClass();
$sus->RUJUKAN->PETUGAS->NIK = $_POST['petugas_nik'];
$sus->RUJUKAN->PETUGAS->NAMA = $_POST['petugas_entry'];
$sus->KONDISI_UMUM = new StdClass();
$sus->KONDISI_UMUM->ANAMNESIS_DAN_PEMERIKSAAN_FISIK = $_POST['anamnesis'];
$sus->KONDISI_UMUM->KESADARAN = $_POST['kesadaran'];
$sus->KONDISI_UMUM->TEKANAN_DARAH = $_POST['tdarah'];
$sus->KONDISI_UMUM->FREKUENSI_NADI = $_POST['nadi'];
$sus->KONDISI_UMUM->SUHU = $_POST['suhu'];
$sus->KONDISI_UMUM->PERNAPASAN = $_POST['nafas'];
$sus->KONDISI_UMUM->KEADAAN_UMUM = $_POST['keadaan_umum'];
$sus->KONDISI_UMUM->NYERI = $_POST['nyeri'];
$sus->KONDISI_UMUM->ALERGI = $_POST['alergi'];
$sus->PENUNJANG = new StdClass();
$sus->PENUNJANG->LABORATORIUM = $_POST['lab'];
$sus->PENUNJANG->RADIOLOGI = $_POST['rad'];
$sus->PENUNJANG->TERAPI_ATAU_TINDAKAN = $_POST['terapi'];

$postdata = json_encode($sus);

$dt = new DateTime(null, new DateTimeZone("UTC"));
$timestamp = $dt->getTimestamp();
$pass = md5(KeySisrute);
$key = IDSisrute.'&'.$timestamp;
$method = "POST";
// $postdata = "";
$signature = hash_hmac('sha256', utf8_encode($key), utf8_encode($pass), true);
$encodedSignature = base64_encode($signature);
$ch = curl_init();
$headers = array(
 'X-cons-id: '.IDSisrute.'',
 'X-timestamp: '.$timestamp.'' ,
 'X-signature: '.$encodedSignature.'',
 'Content-Type:application/json',
 'Accept:application/json',
 'Content-length: '.strlen($postdata),
);
curl_setopt($ch, CURLOPT_URL, SisruteApiUrl."rujukan");
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$content = curl_exec($ch);
$err = curl_error($ch);

curl_close($ch);
$result = json_decode($content, true);
$status = $result['status'];
$data = $result['detail'];
$nomor = $result['data']['RUJUKAN']['NOMOR'];

if ($_POST['jns_rujuk'] == "1") {
    $jns_rujuk = "1. Rawat Jalan";
} elseif ($_POST['jns_rujuk'] == "2") {
    $jns_rujuk = "2. Rawat Darurat/Inap";
} else {
    $jns_rujuk = "3. Parsial";
};

$sadar = $_POST['kesadaran'] == "1" ? "1. Sadar" : "2. Tidak Sadar";

if ($_POST['nyeri'] == "0") {
    $nyeri = "0. Tidak Nyeri";
} elseif ($_POST['nyeri'] == "1") {
    $nyeri = "1. Ringan";
} elseif ($_POST['nyeri'] == "2") {
    $nyeri = "2. Sedang";
} else {
    $nyeri = "3. Berat";
}

if ($status == "200") {
    $query = ("INSERT INTO sisrute_rujukan_keluar VALUES
    ('{$_POST['no_rawat']}', '{$nomor}', '{$_POST['norm']}', '{$_POST['nmps']}', '{$_POST['nik']}', '{$_POST['nops']}',
     '{$_POST['jk']}', '{$_POST['tgllhr']}', '{$_POST['tmplhr']}', '{$_POST['alamat']}', '{$_POST['notlp']}', '{$jns_rujuk}',
     '{$_POST['tgl_rujuk']}', '{$_POST['faskes']}', '{$_POST['kdfaskes']}', '{$_POST['alasan']}', '{$_POST['kdalasan']}',
     '{$_POST['alasan_lain']}', '{$_POST['diagnosa']}', '{$_POST['kddx']}', '{$_POST['dr']}', '{$_POST['kddr']}',
     '{$_POST['petugas_nik']}', '{$_POST['petugas_entry']}', '{$_POST['anamnesis']}', '{$sadar}', '{$_POST['tdarah']}',
     '{$_POST['nadi']}', '{$_POST['suhu']}', '{$_POST['nafas']}', '{$_POST['keadaan_umum']}', '{$nyeri}', '{$_POST['alergi']}',
     '{$_POST['lab']}', '{$_POST['rad']}', '{$_POST['terapi']}')");
    if ($query) {
        echo '<strong>'.$data.' Dan Data Berhasil Disimpan</strong>';
    } else {
        echo '<strong>Gagal Menyimpan Data</strong/>';
    }
} else {
    echo '<strong>'.$data.' Dan Gagal Membuat Rujukan</strong>';
}



// echo json_encode($data);
// echo $nomor;
// echo $_POST['no_rawat'];'<br>';'<br>';
// echo $_POST['norm'];'<br>';
// echo $_POST['nmps'];'<br>';
// echo $_POST['jk'];'<br>';
// echo $_POST['tgllhr'];'<br>';
// echo $_POST['tmplhr'];'<br>';
// echo $_POST['notlp'];'<br>';
// echo $_POST['nops'];'<br>';
// echo $_POST['nik'];'<br>';
// echo $_POST['alamat'];'<br>';
// echo $_POST['jns_rujuk'];'<br>';
// echo $_POST['tgl_rujuk'];'<br>';
// echo $_POST['faskes'];'<br>';
// echo $_POST['kdfaskes'];'<br>';
// echo $_POST['alasan'];'<br>';
// echo $_POST['kdalasan'];'<br>';
// echo $_POST['alasan_lain'];'<br>';
// echo $_POST['diagnosa'];'<br>';
// echo $_POST['kddx'];'<br>';
// echo $_POST['dr'];'<br>';
// echo $_POST['kddr'];'<br>';
// echo $_POST['petugas_entry'];'<br>';
// echo $_POST['petugas_nik'];'<br>';
// echo $_POST['anamnesis'];'<br>';
// echo $_POST['kesadaran'];'<br>';
// echo $_POST['tdarah'];'<br>';
// echo $_POST['nadi'];'<br>';
// echo $_POST['suhu'];'<br>';
// echo $_POST['nafas'];'<br>';
// echo $_POST['keadaan_umum'];'<br>';
// echo $_POST['nyeri'];'<br>';
// echo $_POST['alergi'];'<br>';
// echo $_POST['lab'];'<br>';
// echo $_POST['rad'];'<br>';
// echo $_POST['terapi'];'<br>';
