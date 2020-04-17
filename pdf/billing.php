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

    $pdf = new FPDF('P','mm','A4');
    $pdf->AddPage();

    //set font to arial, bold, 14pt
    $pdf->SetFont('Arial','B',14);

    //Cell(width , height , text , border , end line , [align] )

    $pdf->Cell(130 ,5,setting('nama_instansi'),0,0);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(59 ,5,'INVOICE',0,1);//end of line

    //set font to arial, regular, 12pt
    $pdf->SetFont('Arial','',12);

    $pdf->Cell(130 ,5,setting('alamat_instansi'),0,0);
    $pdf->Cell(59 ,5,'',0,1);//end of line

    $pdf->Cell(130 ,5,setting('kabupaten').' - '.setting('propinsi'),0,0);
    $pdf->Cell(25 ,5,'Tanggal',0,0);
    $pdf->Cell(34 ,5,': '.tgl_indonesia($date),0,1);//end of line

    $pdf->Cell(130 ,5,setting('kontak'),0,0);
    $pdf->Cell(25 ,5,'No. Rawat #',0,0);
    $pdf->Cell(34 ,5, ': '.$_GET['no_rawat'],0,1);//end of line

    $pdf->Cell(130 ,5,setting('email'),0,0);
    $pdf->Cell(25 ,5,'Nomor RM',0,0);
    $pdf->Cell(34 ,5,': '.rawatinfo($_GET['no_rawat'], 'no_rkm_medis'),0,1);//end of line

    //make a dummy empty cell as a vertical spacer
    $pdf->Cell(189 ,10,'',0,1);//end of line

    //billing address
    $pdf->Cell(100 ,5,'Kepada :',0,1);//end of line

    //add dummy cell at beginning of each line for indentation
    $pdf->Cell(10 ,5,'',0,0);
    $pdf->Cell(90 ,5,pasieninfo(rawatinfo($_GET['no_rawat'], 'no_rkm_medis'), 'nm_pasien'),0,1);

    $pdf->Cell(10 ,5,'',0,0);
    $pdf->Cell(90 ,5,pasieninfo(rawatinfo($_GET['no_rawat'], 'no_rkm_medis'), 'alamat'),0,1);

    $pdf->Cell(10 ,5,'',0,0);
    $pdf->Cell(90 ,5,pasieninfo(rawatinfo($_GET['no_rawat'], 'no_rkm_medis'), 'no_tlp'),0,1);

    //make a dummy empty cell as a vertical spacer
    $pdf->Cell(189 ,10,'',0,1);//end of line

    //invoice contents
    $pdf->SetFont('Arial','B',12);

    $pdf->Cell(100 ,5,'Nama Item',1,0);
    $pdf->Cell(25 ,5,'Jumlah',1,0);
    $pdf->Cell(30 ,5,'Biaya Satuan',1,0);//end of line
    $pdf->Cell(34 ,5,'Total Biaya',1,1);//end of line

    $pdf->SetFont('Arial','I',12);
    $pdf->Cell(189 ,5,'Tindakan',1,1);//end of line

    $pdf->SetFont('Arial','',12);

    //Numbers are right-aligned so we give 'R' after new line parameter

    $pdf->Cell(100 ,5,'UltraCool Fridge',1,0);
    $pdf->Cell(25 ,5,'-',1,0);
    $pdf->Cell(30 ,5,'-',1,0);
    $pdf->Cell(34 ,5,'3,250',1,1,'R');//end of line

    $pdf->Cell(100 ,5,'Supaclean Diswasher',1,0);
    $pdf->Cell(25 ,5,'-',1,0);
    $pdf->Cell(30 ,5,'-',1,0);
    $pdf->Cell(34 ,5,'1,200',1,1,'R');//end of line

    $pdf->Cell(100 ,5,'Something Else',1,0);
    $pdf->Cell(25 ,5,'-',1,0);
    $pdf->Cell(30 ,5,'-',1,0);
    $pdf->Cell(34 ,5,'1,000',1,1,'R');//end of line

    //summary
    $pdf->Cell(130 ,5,'',0,0);
    $pdf->Cell(25 ,5,'Subtotal',0,0);
    $pdf->Cell(34 ,5,'4,450',1,1,'R');//end of line

    $pdf->Cell(130 ,5,'',0,0);
    $pdf->Cell(25 ,5,'Taxable',0,0);
    $pdf->Cell(34 ,5,'0',1,1,'R');//end of line

    $pdf->Cell(130 ,5,'',0,0);
    $pdf->Cell(25 ,5,'Tax Rate',0,0);
    $pdf->Cell(34 ,5,'10%',1,1,'R');//end of line

    $pdf->Cell(130 ,5,'',0,0);
    $pdf->Cell(25 ,5,'Total Due',0,0);
    $pdf->Cell(34 ,5,'4,450',1,1,'R');//end of line

    $pdf->Output('billing.pdf','I');

}
?>
