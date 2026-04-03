<?php
if (php_sapi_name() !== 'cli') {
    die("HANYA BISA DIJALANKAN VIA CLI\n");
}

if ($argc < 3) {
    die("Usage: php receiver.php <path_directory> <filename>\n");
}

$dir = $argv[1];
$file = $argv[2];
$filepath = rtrim($dir, '/') . '/' . $file;

if (!file_exists($filepath)) {
    die("File tidak ditemukan: $filepath\n");
}

define('BASE_DIR', realpath(__DIR__ . '/../../'));
require_once BASE_DIR . '/config.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if ($mysqli->connect_error) {
    die("Gagal Konek Database: " . $mysqli->connect_error . "\n");
}

function escape($str)
{
    global $mysqli;
    return $mysqli->real_escape_string(trim($str));
}

$binPath = 'export PATH=$PATH:/usr/local/bin:/usr/bin:/opt/homebrew/bin && ';

// EKSTRAKSI METADATA VIA DCMDUMP
$patientId = trim(shell_exec($binPath . "dcmdump +P 0010,0020 -q " . escapeshellarg($filepath) . " | grep -o '\\[.*\\]' | tr -d '[]' | head -n 1"));
$accessionNumber = trim(shell_exec($binPath . "dcmdump +P 0008,0050 -q " . escapeshellarg($filepath) . " | grep -o '\\[.*\\]' | tr -d '[]' | head -n 1"));
$studyUid = trim(shell_exec($binPath . "dcmdump +P 0020,000D -q " . escapeshellarg($filepath) . " | grep -o '\\[.*\\]' | tr -d '[]' | head -n 1"));
$seriesUid = trim(shell_exec($binPath . "dcmdump +P 0020,000E -q " . escapeshellarg($filepath) . " | grep -o '\\[.*\\]' | tr -d '[]' | head -n 1"));
$sopUid = trim(shell_exec($binPath . "dcmdump +P 0008,0018 -q " . escapeshellarg($filepath) . " | grep -o '\\[.*\\]' | tr -d '[]' | head -n 1"));
$modality = trim(shell_exec($binPath . "dcmdump +P 0008,0060 -q " . escapeshellarg($filepath) . " | grep -o '\\[.*\\]' | tr -d '[]' | head -n 1"));
$studyDate = trim(shell_exec($binPath . "dcmdump +P 0008,0020 -q " . escapeshellarg($filepath) . " | grep -o '\\[.*\\]' | tr -d '[]' | head -n 1"));

if (empty($studyUid) || empty($seriesUid) || empty($sopUid)) {
    die("Gagal ekstrak raw UID dari DICOM\n");
}

$no_rawat = '';

// Jika Accession Number berisi noorder permintaan rontgen, sambungkan ke no_rawat
if (!empty($accessionNumber)) {
    $res = $mysqli->query("SELECT no_rawat FROM permintaan_radiologi WHERE noorder = '" . escape($accessionNumber) . "' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $no_rawat = $res->fetch_assoc()['no_rawat'];
    }
}

// Cadangan: Jika lewat noorder gagal atau blong, tebak no_rawat berdasar kunjungan terakhir Patient ID
if (empty($no_rawat) && !empty($patientId)) {
    $res = $mysqli->query("SELECT no_rawat FROM reg_periksa WHERE no_rkm_medis = '" . escape($patientId) . "' ORDER BY tgl_registrasi DESC, jam_reg DESC LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $no_rawat = $res->fetch_assoc()['no_rawat'];
    }
}

// 1. CARI ATAU BUAT STUDY
$res = $mysqli->query("SELECT id FROM mlite_mini_pacs_study WHERE study_instance_uid = '" . escape($studyUid) . "'");
if ($res && $res->num_rows > 0) {
    $studyId = $res->fetch_assoc()['id'];

    // Auto-patching jika no_rawat baru ditemukan tapi study sebelumnya masih kosong no_rawat-nya
    if (!empty($no_rawat)) {
        $mysqli->query("UPDATE mlite_mini_pacs_study SET no_rawat = '" . escape($no_rawat) . "' WHERE id = " . $studyId . " AND (no_rawat IS NULL OR no_rawat = '')");
    }
} else {
    $stmt = $mysqli->prepare("INSERT INTO mlite_mini_pacs_study (no_rawat, study_instance_uid, study_date, modality, description) VALUES (?, ?, ?, ?, ?)");
    $desc = "Received via DICOM Receiver SCP";

    // Parse studyDate YYYYMMDD to YYYY-MM-DD
    $dateFmt = date('Y-m-d H:i:s');
    if (strlen($studyDate) == 8) {
        $dateFmt = substr($studyDate, 0, 4) . '-' . substr($studyDate, 4, 2) . '-' . substr($studyDate, 6, 2) . ' 00:00:00';
    }

    $stmt->bind_param("sssss", $no_rawat, $studyUid, $dateFmt, $modality, $desc);
    $stmt->execute();
    $studyId = $stmt->insert_id;
}

// 2. CARI ATAU BUAT SERIES
$res = $mysqli->query("SELECT id FROM mlite_mini_pacs_series WHERE series_instance_uid = '" . escape($seriesUid) . "'");
if ($res && $res->num_rows > 0) {
    $seriesId = $res->fetch_assoc()['id'];
} else {
    $stmt = $mysqli->prepare("INSERT INTO mlite_mini_pacs_series (study_id, series_instance_uid, series_description) VALUES (?, ?, ?)");
    $sDesc = "Series dari Mesin Modalitas";
    $stmt->bind_param("iss", $studyId, $seriesUid, $sDesc);
    $stmt->execute();
    $seriesId = $stmt->insert_id;
}

// 3. RENAME FILE MENJADI FORMAT SOP UID
$newFilepath = $dir . '/' . $sopUid . '.dcm';
if ($filepath !== $newFilepath) {
    if (file_exists($newFilepath)) {
        unlink($newFilepath); // Timpa file lama jika uid sama
    }
    rename($filepath, $newFilepath);
    $filepath = $newFilepath;
}

// 4. INSERT INSTANCE FILE
$res = $mysqli->query("SELECT id FROM mlite_mini_pacs_instance WHERE sop_instance_uid = '" . escape($sopUid) . "'");
if ($res && $res->num_rows == 0) {
    $stmt = $mysqli->prepare("INSERT INTO mlite_mini_pacs_instance (series_id, sop_instance_uid, file_path) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $seriesId, $sopUid, $filepath);
    $stmt->execute();
} else {
    $mysqli->query("UPDATE mlite_mini_pacs_instance SET file_path = '" . escape($filepath) . "' WHERE sop_instance_uid = '" . escape($sopUid) . "'");
}

// 5. GENERATE THUMBNAIL
$thumbnailFile = $dir . '/' . $sopUid . '_thumb.jpg';
$cmdThumb = sprintf(
    $binPath . 'dcmj2pnm +oj +Wn %s %s 2>&1',
    escapeshellarg($filepath),
    escapeshellarg($thumbnailFile)
);
shell_exec($cmdThumb);

echo "DICOM Berhasil diproses: $filepath\n";
