<?php
include('../../../config.php');

$query = query("INSERT INTO survei_infeksi
VALUES ('{$_POST['no_rawat']}', '{$_POST['jenis_tdk']}', '{$_POST['lokasi']}', '{$_POST['mulai']}', '{$_POST['akhir']}', '{$_POST['total']}', '{$_POST['tglin']}', '{$_POST['catatan']}',
  '{$_POST['group1']}', '{$_POST['group2']}', '{$_POST['group3']}')");

if ($query) {
    echo "Simpan Data Survei Berhasil";
} else {
    echo "Gagal Menyimpan";
}
