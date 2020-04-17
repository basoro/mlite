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

  $pdf = new FPDF('L', 'mm', array(59,98));
  $pdf->AddPage();
  $pdf->SetAutoPageBreak(true, 10);
  $pdf->SetTopMargin(5);
  $pdf->SetLeftMargin(5);
  $pdf->SetRightMargin(5);

  $pdf->Image('../assets/images/logo.png',5,5,20);
  $pdf->SetFont('Arial', '', 16);
  $pdf->Text(28, 12, setting('nama_instansi'));
  $pdf->SetFont('Arial', '', 8);
  $pdf->Text(28, 17, setting('alamat_instansi'));
  $pdf->Text(28, 20, setting('kontak').' - '.setting('email'));
  $pdf->Text(28, 23, setting('kabupaten').' - '.setting('propinsi'));

  $pdf->SetFont('Arial', '', 10);
  $pdf->Text(5, 40, 'No. Kartu');
  $pdf->Text(21, 40, ': '.pasieninfo($_GET['id'], 'no_rkm_medis'));
  $pdf->Text(5, 46, 'Nama');
  $pdf->Text(21, 46, ': '.pasieninfo($_GET['id'], 'nm_pasien'));
  $pdf->Text(5, 52, 'Alamat');
  $pdf->Text(21, 52, ': '.pasieninfo($_GET['id'], 'alamat'));

  $pdf->Output('kartu_pasien.pdf','I');

}
?>
