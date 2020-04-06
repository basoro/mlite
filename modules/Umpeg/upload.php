<?php
include '../../config.php';
include 'excel_reader2.php';

$target = basename($_FILES['filepegawai']['name']);
move_uploaded_files($_FILES['filepegawai']['tmp_name'], $target);

chmod($_FILES['filepegawai']['name'], 0777);

$data = new Spreadsheet_Excel_Reader($_FILES['filepegawai']['name'], false);
$jumlah_baris = $data->rowcount($sheet_index=0);

$berhasil = 0;
for ($i=2; $i <= $jumlah_baris ; $i++) {
    $nik = $data->val($i, 1);
    $nama = $data->val($i, 2);
    $jk = $data->val($i, 3);
    $jbtn = $data->val($i, 4);
    $jnj = $data->val($i, 5);
    $dep = $data->val($i, 6);
    $bid = $data->val($i, 7);
    $stwp = $data->val($i, 8);
    $stkrj = $data->val($i, 9);
    $npwp = $data->val($i, 10);
    $pendik = $data->val($i, 11);
    $gapok = $data->val($i, 12);
    $tmplhr = $data->val($i, 13);
    $tgllhr = $data->val($i, 14);
    $alamat = $data->val($i, 15);
    $kota = $data->val($i, 16);
    $mulaikrj = $data->val($i, 17);
    $mskrj = $data->val($i, 18);
    $indexins = $data->val($i, 19);
    $bpd = $data->val($i, 20);
    $rek = $data->val($i, 21);
    $sttsakt = $data->val($i, 22);
    $wjbmsk = $data->val($i, 23);
    $kurang = $data->val($i, 24);
    $index = $data->val($i, 25);
    $mulaikntrk = $data->val($i, 26);
    $cuti = $data->val($i, 27);
    $dankes = $data->val($i, 28);
    $poto = $data->val($i, 29);
    $noktp = $data->val($i, 30);
    query("INSERT INTO pegawai VALUES ('', '$nik', '$nama', '$jk', '$jbtn', '$jnj', '$dep', '$bid', '$stwp', '$stkrj', '$npwp', '$pendik', '$gapok', '$tmplhr', '$tgllhr', '$alamat', '$kota', '$mulaikrj', '$mskrj', '$indexins', '$bpd', '$rek', '$sttsakt', '$wjbmsk', '$kurang', '$index', '$mulaikntrk', '$cuti', '$dankes', '$poto', '$noktp') ");
    $berhasil++;
}

unlink($_FILES['filepegawai']['name']);

header("location:".URL.";?module=Umpeg&page=index&berhasil=$berhasil");
