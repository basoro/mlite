<?php

include "../../../config.php";
include "../../../init.php";
$tgl = date("dmY");

if ($_GET['op'] == "report")
{
$namaFile = "report-sentitems-".$tgl.".xls";


// Function penanda awal file (Begin Of File) Excel

function xlsBOF() {
echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
return;
}

// Function penanda akhir file (End Of File) Excel

function xlsEOF() {
echo pack("ss", 0x0A, 0x00);
return;
}

// Function untuk menulis data (angka) ke cell excel

function xlsWriteNumber($Row, $Col, $Value) {
echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
echo pack("d", $Value);
return;
}

// Function untuk menulis data (text) ke cell excel

function xlsWriteLabel($Row, $Col, $Value ) {
$L = strlen($Value);
echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
echo $Value;
return;
}

// header file excel

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,
        pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");

// header untuk nama file
header("Content-Disposition: attachment; filename=".$namaFile."");

header("Content-Transfer-Encoding: binary ");

// memanggil function penanda awal file excel
xlsBOF();

// ------ membuat kolom pada excel --- //

// mengisi pada cell A1 (baris ke-0, kolom ke-0)
xlsWriteLabel(0,0,"NO");

// mengisi pada cell A2 (baris ke-0, kolom ke-1)
xlsWriteLabel(0,1,"DESTINATION NUMBER");

// mengisi pada cell A3 (baris ke-0, kolom ke-2)
xlsWriteLabel(0,2,"TEXT DECODED");

// mengisi pada cell A4 (baris ke-0, kolom ke-3)
xlsWriteLabel(0,3,"DATE TIME");

// mengisi pada cell A5 (baris ke-0, kolom ke-4)
xlsWriteLabel(0,4,"STATUS");

$query = "SELECT * FROM sentitems ORDER BY id";
$hasil = query($query);

// nilai awal untuk baris cell
$noBarisCell = 1;

// nilai awal untuk nomor urut data
$noData = 1;

while ($data = fetch_array($hasil))
{
   // menampilkan no. urut data
   xlsWriteNumber($noBarisCell,0,$noData);

   // menampilkan data no tujuan
   xlsWriteLabel($noBarisCell,1,$data['DestinationNumber']);

   // menampilkan data isi sms
   xlsWriteLabel($noBarisCell,2,$data['TextDecoded']);

   // menampilkan data date time sending
   xlsWriteLabel($noBarisCell,3,$data['SendingDateTime']);


   // menampilkan status
   xlsWriteLabel($noBarisCell,4,$data['Status']);

   // increment untuk no. baris cell dan no. urut data
   $noBarisCell++;
   $noData++;
}

// memanggil function penanda akhir file excel
xlsEOF();
exit();

}
else if ($_GET['op'] == "phonebook")
{

$namaFile = "phonebook-".$tgl.".xls";

// Function penanda awal file (Begin Of File) Excel

function xlsBOF() {
echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
return;
}

// Function penanda akhir file (End Of File) Excel

function xlsEOF() {
echo pack("ss", 0x0A, 0x00);
return;
}

// Function untuk menulis data (angka) ke cell excel

function xlsWriteNumber($Row, $Col, $Value) {
echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
echo pack("d", $Value);
return;
}

// Function untuk menulis data (text) ke cell excel

function xlsWriteLabel($Row, $Col, $Value ) {
$L = strlen($Value);
echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
echo $Value;
return;
}

// header file excel

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,
        pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");

// header untuk nama file
header("Content-Disposition: attachment; filename=".$namaFile."");

header("Content-Transfer-Encoding: binary ");

// memanggil function penanda awal file excel
xlsBOF();

// ------ membuat kolom pada excel --- //

// mengisi pada cell A1 (baris ke-0, kolom ke-0)
xlsWriteLabel(0,0,"NAMA");

// mengisi pada cell A2 (baris ke-0, kolom ke-1)
xlsWriteLabel(0,1,"NO HP");

// mengisi pada cell A3 (baris ke-0, kolom ke-2)
xlsWriteLabel(0,2,"ALAMAT");

// mengisi pada cell A4 (baris ke-0, kolom ke-3)
xlsWriteLabel(0,3,"GROUP ID");

// mengisi pada cell A4 (baris ke-0, kolom ke-3)
xlsWriteLabel(0,4,"DATE JOINED");

$query = "SELECT * FROM sms_phonebook ORDER BY nama";
$hasil = query($query);

// nilai awal untuk baris cell
$noBarisCell = 1;

// nilai awal untuk nomor urut data
$noData = 1;

while ($data = fetch_array($hasil))
{

   // menampilkan data no pengirim
   xlsWriteLabel($noBarisCell,0,$data['nama']);

   // menampilkan data isi sms
   xlsWriteLabel($noBarisCell,1,$data['noTelp']);

   // menampilkan data date time sending
   xlsWriteLabel($noBarisCell,2,$data['alamat']);

    // menampilkan data date time sending
   xlsWriteLabel($noBarisCell,3,$data['idgroup']);

   // menampilkan data date time sending
   xlsWriteLabel($noBarisCell,4,$data['dateJoin']);

   // increment untuk no. baris cell dan no. urut data
   $noBarisCell++;
   $noData++;
}

// memanggil function penanda akhir file excel
xlsEOF();
exit();



}
else if ($_GET['op'] == "inbox")
{

$namaFile = "report-inbox-".$tgl.".xls";

// Function penanda awal file (Begin Of File) Excel

function xlsBOF() {
echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
return;
}

// Function penanda akhir file (End Of File) Excel

function xlsEOF() {
echo pack("ss", 0x0A, 0x00);
return;
}

// Function untuk menulis data (angka) ke cell excel

function xlsWriteNumber($Row, $Col, $Value) {
echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
echo pack("d", $Value);
return;
}

// Function untuk menulis data (text) ke cell excel

function xlsWriteLabel($Row, $Col, $Value ) {
$L = strlen($Value);
echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
echo $Value;
return;
}

// header file excel

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");

// header untuk nama file
header("Content-Disposition: attachment; filename=".$namaFile."");

header("Content-Transfer-Encoding: binary ");

// memanggil function penanda awal file excel
xlsBOF();

// ------ membuat kolom pada excel --- //

// mengisi pada cell A1 (baris ke-0, kolom ke-0)
xlsWriteLabel(0,0,"NO");

// mengisi pada cell A2 (baris ke-0, kolom ke-1)
xlsWriteLabel(0,1,"SENDER NUMBER");

// mengisi pada cell A3 (baris ke-0, kolom ke-2)
xlsWriteLabel(0,2,"TEXT DECODED");

// mengisi pada cell A4 (baris ke-0, kolom ke-3)
xlsWriteLabel(0,3,"DATE TIME");

$query = "SELECT * FROM sms_inbox ORDER BY id";
$hasil = query($query);

// nilai awal untuk baris cell
$noBarisCell = 1;

// nilai awal untuk nomor urut data
$noData = 1;

while ($data = fetch_array($hasil))
{
   // menampilkan no. urut data
   xlsWriteNumber($noBarisCell,0,$noData);

   // menampilkan data no pengirim
   xlsWriteLabel($noBarisCell,1,$data['sender']);

   // menampilkan data isi sms
   xlsWriteLabel($noBarisCell,2,$data['msg']);

   // menampilkan data date time sending
   xlsWriteLabel($noBarisCell,3,$data['time']);

   // increment untuk no. baris cell dan no. urut data
   $noBarisCell++;
   $noData++;
}

// memanggil function penanda akhir file excel
xlsEOF();
exit();

}

?>
