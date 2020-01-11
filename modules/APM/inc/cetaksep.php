<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Cetak SEP';
include('../../../config.php');
include('../../../init.php');
?>
<title>Cetak SEP</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<style>
  .col-md-2 { font-size: 20px;}
  .col-md-3 { font-size: 20px;}
  .col-md-4 { font-size: 20px;}
  .col-md-8 { font-size: 20px;}
</style>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="body">
                          <div class="container" style="margin-top : 20px;" action="cetak">
                            <div class="row clearfix">
                              <div class="col-md-2">
                                <div class="form-group">
                                  <img src="img/bpjslogo.png" height="60" width="310">
                                </div>
                              </div>
                              <div class="col-md-10">
                                <div class="form-group">
                                  <h2><center>SURAT ELEGIBILITAS PESERTA</center></h2>
                                  <h5><center>RSUD H. DAMANHURI</center></h5>
                                </div>
                              </div>
                            </div>
                            <?php $sql = "SELECT * , DATE_ADD(tglrujukan , INTERVAL 90 DAY) as tgl_tempo FROM bridging_sep WHERE no_rawat = '{$_GET['no_rawat']}'";
                            	$assoc = query($sql);
                            	$cetak = fetch_assoc($assoc);
                            ?>
                            <div class="row clearfix">
                              <div class="col-md-2">

                              </div>
                              <div class="col-md-4">

                              </div>
                              <div class="col-md-6">
                                <img src="barcode.php?text=<?php echo $cetak['no_sep'];?>&size=40&print=true"/>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2">
                                No. SEP
                              </div>
                              <div class="col-md-4">
                                : <?php echo $cetak['no_sep'];?>
                              </div>
                              <div class="col-md-2">

                              </div>
                              <div class="col-md-4">

                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2">
                                Tgl. SEP
                              </div>
                              <div class="col-md-4">
                                : <?php echo date("d/m/Y", strtotime($cetak['tglsep']));?>
                              </div>
                              <div class="col-md-2">
                                Masa Berlaku
                              </div>
                              <div class="col-md-4">
                                : <?php echo date("d/m/Y", strtotime($cetak['tglrujukan'])).' s/d '.date("d/m/Y", strtotime($cetak['tgl_tempo']));?>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2">
                                No. Kartu
                              </div>
                              <div class="col-md-4">
                                : <?php echo $cetak['no_kartu'].' ('.$cetak['nomr'].')';?>
                              </div>
                              <div class="col-md-2">
                                Jenis Kelamin
                              </div>
                              <div class="col-md-4">
                                : <?php if($cetak['jkel'] == "L"){echo "Laki-Laki";}else{echo "Perempuan";};?>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2">
                                Nama Peserta
                              </div>
                              <div class="col-md-4">
                                : <?php echo $cetak['nama_pasien'];?>
                              </div>
                              <div class="col-md-2">
                                No. Rawat
                              </div>
                              <div class="col-md-4">
                                : <?php echo $cetak['no_rawat'];?>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2">
                                Tgl. Lahir
                              </div>
                              <div class="col-md-4">
                                : <?php echo date("d/m/Y", strtotime($cetak['tanggal_lahir']));?>
                              </div>
                              <div class="col-md-2">
                                No. Reg
                              </div>
                              <div class="col-md-4">
                                : <?php $noreg = fetch_array(query("select no_reg from reg_periksa where no_rawat = '{$_GET['no_rawat']}'"));echo $noreg['0'];?>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2">
                                No.Telepon
                              </div>
                              <div class="col-md-4">
                                : <?php echo $cetak['notelep'];?>
                              </div>
                              <div class="col-md-2">
                                Peserta
                              </div>
                              <div class="col-md-4">
                                : <?php echo $cetak['peserta'];?>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2">
                                Sub/Spesialis
                              </div>
                              <div class="col-md-4">
                                : <?php echo $cetak['nmpolitujuan'];?>
                              </div>
                              <div class="col-md-2">
                                COB
                              </div>
                              <div class="col-md-4">
                                : <?php echo $cetak['cob'];?>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2">
                                Faskes Perujuk
                              </div>
                              <div class="col-md-4">
                                : <?php echo $cetak['nmppkrujukan'];?>
                              </div>
                              <div class="col-md-2">
                                Jns. Rawat
                              </div>
                              <div class="col-md-4">
                                : <?php if($cetak['jnspelayanan'] == 1){echo "Rawat Inap";}{echo "Rawat Jalan";};?>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2">
                                Diagnosa Awal
                              </div>
                              <div class="col-md-4">
                                : <?php echo $cetak['nmdiagnosaawal'];?>
                              </div>
                              <div class="col-md-2">
                                Kls. Rawat
                              </div>
                              <div class="col-md-4">
                                : <?php echo "Kelas ".$cetak['klsrawat'];?>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-6">

                              </div>
                              <div class="col-md-2">
                                Penjamin
                              </div>
                              <div class="col-md-4">
                                : <?php echo $cetak['penjamin'];?>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2">
                                Catatan
                              </div>
                              <div class="col-md-4">
                                : <?php echo $cetak['catatan'];?>
                              </div>
                              <div class="col-md-6" style="left: 90px;">
                                Pasien/Keluarga Pasien
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-6">
                                <small><em><font size="1px">*Saya Menyetujui BPJS Kesehatan menggunakan informasi Medis Pasien jika diperlukan.</font></em></small>
                              </div>
                              <div class="col-md-6">
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-6">
                              </div>
                              <div class="col-md-6">
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-6">
                                <small><em><font size="1px">**SEP bukan sebagai bukti penjaminan peserta</font></em></small>
                              </div>
                              <div class="col-md-6">
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2">
                                <small><em><font size="1px">Cetakan ke 1</font></em></small>
                              </div>
                              <div class="col-md-10">
                                <small><em><font size="1px"><?php echo date("d/m/Y h:i:s A");?></font></em></small>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-7">
                              </div>
                              <div class="col-md-5">
                                -----------------------------
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-12">
                                <div class="form-group">
                                  <br>
                                  <h3><center>RESUME MEDIS RAWAT JALAN JKN</center></h3>
                                  <h5><center>RSUD H. DAMANHURI</center></h5>
                                </div>
                              </div>
                            </div>

                            <div class="row clearfix"><br>
                              <div class="col-md-2">

                              </div>
                              <div class="col-md-8">

                              </div>
                              <div class="col-md-2">
                                Kode ICD X
                              </div>
                            </div>
                            <div class="row clearfix"><br>
                              <div class="col-md-2" style="font-size:16px;">
                                Diagnosa
                              </div>
                              <div class="col-md-8">
                                : 1._________________________________________________________________
                              </div>
                              <div class="col-md-2">
                                (______________)
                              </div>
                            </div>
                            <div class="row clearfix"><br>
                              <div class="col-md-2">

                              </div>
                              <div class="col-md-8">
                                : 2._________________________________________________________________
                              </div>
                              <div class="col-md-2">
                                (______________)<br><br>
                              </div>
                            </div>

                            <div class="row clearfix"><br>
                              <div class="col-md-2">

                              </div>
                              <div class="col-md-8">

                              </div>
                              <div class="col-md-2">
                                Kode ICD IX
                              </div>
                            </div>
                            <div class="row clearfix"><br>
                              <div class="col-md-2" style="font-size:16px;">
                                Prosedur/Tindakan
                              </div>
                              <div class="col-md-8">
                                : 1._________________________________________________________________
                              </div>
                              <div class="col-md-2">
                                (______________)
                              </div>
                            </div>
                            <div class="row clearfix"><br>
                              <div class="col-md-2">

                              </div>
                              <div class="col-md-8">
                                : 2._________________________________________________________________
                              </div>
                              <div class="col-md-2">
                                (______________)
                                <br><br><br>
                              </div>
                            </div>

                            <div class="row clearfix">
                              <div class="col-md-2">

                              </div>
                              <div class="col-md-8">

                              </div>
                              <div class="col-md-2">

                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2" style="font-size:16px;">
                                Laporan Tindakan
                              </div>
                              <div class="col-md-8">
                                : ___________________________________________________________________
                              </div>
                              <div class="col-md-2">

                              </div>
                            </div>
                            <div class="row clearfix"><br>
                              <div class="col-md-2">

                              </div>
                              <div class="col-md-8">
                                : ___________________________________________________________________
                              </div>
                              <div class="col-md-2">

                              </div>
                            </div>
                            <div class="row clearfix"><br>
                              <div class="col-md-2">

                              </div>
                              <div class="col-md-8">
                                : ___________________________________________________________________
                              </div>
                              <div class="col-md-2">
                              <br>
                              </div>
                            </div>

                            <div class="row clearfix">
                              <div class="col-md-3">
                                <br>
                                Kunjungan Awal
                              </div>
                              <div class="col-md-7">

                              </div>
                              <div class="col-md-2">

                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-3">
                                Kunjungan Lanjutan
                              </div>
                              <div class="col-md-6">

                              </div>
                              <div class="col-md-3">
                                <center>Dokter,</center>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-3">
                                Observasi
                              </div>
                              <div class="col-md-7">

                              </div>
                              <div class="col-md-2">

                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-3">
                                Post Operasi
                              </div>
                              <div class="col-md-6">

                              </div>
                              <div class="col-md-3">

                                <br><br>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-3">

                              </div>
                              <div class="col-md-6">

                              </div>
                              <div class="col-md-3">
                                <center><?php echo $cetak['nmdpdjp'];?></center>
                                <br><br>
                              </div>
                            </div>
                          </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
	<script>
    	window.print();
    	window.close();
      	setTimeout(function() { window.location.href = "<?php echo URL; ?>/modules/APM/inc/ceksep.php"; }, 500);
	</script>
