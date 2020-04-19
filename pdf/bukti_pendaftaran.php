<?php
session_start();
ob_start();
define("INDEX",true);
include("../config.php");
include("../functions/function_info.php");
include("../functions/function_setting.php");
include("fpdf/FPDF.php");
$timeout = $_SESSION['timeout'];
if(time()<$timeout){
	$_SESSION['timeout'] = time()+5000;
}else{
	$_SESSION['login'] = 0;
}

if(empty($_SESSION['username']) or empty($_SESSION['password']) or $_SESSION['login']==0){
	header('location: login.php');
}else{

  $pdf = new FPDF('P', 'mm', array(59,98));
  $pdf->AddPage();
  $pdf->SetAutoPageBreak(true, 10);
  $pdf->SetTopMargin(5);
  $pdf->SetLeftMargin(5);
  $pdf->SetRightMargin(5);

  $pdf->Image('../assets/images/logo.png',2,2,10);
  $pdf->SetFont('Arial', '', 10);
  $pdf->Text(14, 5, setting('nama_instansi'));
  $pdf->SetFont('Arial', '', 6);
  $pdf->Text(14, 8, setting('alamat_instansi'));
  $pdf->Text(14, 10, setting('kontak').' - '.setting('email'));
  $pdf->Text(14, 12, setting('kabupaten').' - '.setting('propinsi'));
	$pdf->SetFont('Arial', '', 11);
  $pdf->Text(9, 20, 'BUKTI PENDAFTARAN');
	$pdf->Text(5, 21, '_______________________');
	$pdf->SetFont('Arial', '', 10);
	$pdf->Text(17, 26, 'RAWAT JALAN');
	$pdf->SetFont('Arial', '', 9);
	$pdf->Text(3, 35, 'Tanggal');
	$pdf->Text(16, 35, ': '.reginfo($_GET['id'], 'tgl_registrasi'));
	$pdf->Text(3, 40, 'No. Reg');
	$pdf->Text(16, 40, ': '.reginfo($_GET['id'], 'no_reg'));
	$pdf->Text(3, 45, 'Nama');
	$pdf->Text(16, 45, ': '.reginfo($_GET['id'], 'nm_pasien'));
	$pdf->Text(3, 50, 'No. RM');
	$pdf->Text(16, 50, ': '.reginfo($_GET['id'], 'no_rkm_medis'));
	$pdf->Text(3, 55, 'Alamat');
	$pdf->Text(16, 55, ': '.substr(reginfo($_GET['id'], 'alamat'),0,20));
	$pdf->Text(17, 60, substr(reginfo($_GET['id'], 'alamat'),21,42));
	$pdf->Text(3, 65, 'Ruang');
	$pdf->Text(16, 65, ': '.substr(reginfo($_GET['id'], 'nm_poli'),0,20));
	$pdf->Text(3, 70, 'Dokter');
	$pdf->Text(16, 70, ': '.substr(reginfo($_GET['id'], 'nm_dokter'),0,20));
	$pdf->Text(3, 75, 'Bayar');
	$pdf->Text(16, 75, ': '.reginfo($_GET['id'], 'png_jawab'));
	$pdf->SetFont('Arial', '', 7);
	$pdf->Text(9, 83, 'Terima Kasih Atas kepercayaan Anda');
	$pdf->Text(12, 86, 'Bawalah kartu Berobat anda dan');
	$pdf->Text(14, 89, 'datang 30 menit sebelumnya');
	$pdf->Text(6, 92, 'Bawalah surat rujukan atau surat kontrol asli');
	$pdf->Text(3, 95, 'dan tunjukkan pada petugas di Lobby resepsionis');

  $pdf->Output('kartu_pasien.pdf','I');

}
?>
