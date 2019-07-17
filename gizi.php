<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title ='DATA DIET PASIEN';
include_once('config.php');
include_once('layout/header.php');
include_once('layout/sidebar.php');
?>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                DATA DIET PASIEN
                            </h2>
                    </div>
					<div class="body table-responsive">
                            <div id="buttons" class="align-center m-l-10 m-b-15 export-hidden"></div>
                            <table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                          <thead>
						  <tr>
							 <th class="text-center">No. Rawat</th>
						     <th class="text-center">No. RM</th>
						     <th class="text-center">Nama Pasien</th>
						     <th class="text-center">Kamar</th>
							 <th class="text-center">Jenis Bayar</th>
						     <th class="text-center">Tanggal</th>
						     <th class="text-center">Waktu Diet</th>
						     <th class="text-center">Jenis Diet</th>
						     <th class="text-center">Diagnosa</th>
						   </tr>
						   </thead>
						   <tbody>
						    <?php
		  	$tgl1 = isset($_POST['tgl_awal']);
		    $tgl2 = isset($_POST['tgl_akhir']);
            $kd_bangsal = isset($_POST['ruangan'])?$_POST['ruangan']:null;
			//$kd_bangsal = isset($_POST['ruangan']);
		    if($tgl1 == "" && $tgl2 == ""&& $kd_bangsal == "" ){
			echo 'Tidak ada data';
			} else {$sql="SELECT
									detail_beri_diet.no_rawat,
									detail_beri_diet.tanggal,
									detail_beri_diet.waktu,
									diet.nama_diet,
									reg_periksa.no_rkm_medis,
									pasien.nm_pasien,
									kamar_inap.kd_kamar,
									kamar.kd_bangsal,
									bangsal.nm_bangsal,
									kamar_inap.diagnosa_awal,
									kamar_inap.no_rawat,
									penjab.png_jawab
									FROM
									detail_beri_diet
									INNER JOIN diet ON detail_beri_diet.kd_diet = diet.kd_diet
									INNER JOIN reg_periksa ON detail_beri_diet.no_rawat = reg_periksa.no_rawat
									INNER JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
									INNER JOIN kamar_inap ON kamar_inap.no_rawat = reg_periksa.no_rawat
									INNER JOIN kamar ON detail_beri_diet.kd_kamar = kamar.kd_kamar AND kamar_inap.kd_kamar = kamar.kd_kamar
									INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
									INNER JOIN penjab ON reg_periksa.kd_pj = penjab.kd_pj AND pasien.kd_pj = penjab.kd_pj
									WHERE 
									 detail_beri_diet.tanggal>='".$_POST['tgl_awal']."'AND detail_beri_diet.tanggal<='".$_POST['tgl_akhir']."'";
                                    if($kd_bangsal) {
                                      $sql .= "AND kamar.kd_bangsal='".$_POST['ruangan']."'"; 
                                    }
									$query=query($sql);
									while($baranghaja=$query->fetch_assoc()){
										echo "
										<tr>
										 <td>".$baranghaja['no_rawat']."</td>
										 <td>".$baranghaja['no_rkm_medis']."</td>
										 <td>".$baranghaja['nm_pasien']."</td>
										 <td>".$baranghaja['nm_bangsal']."</td>
										 <td>".$baranghaja['png_jawab']."</td>
										 <td>".$baranghaja['tanggal']."</td>
										 <td>".$baranghaja['waktu']."</td>
										 <td>".$baranghaja['nama_diet']."</td>
										 <td>".$baranghaja['diagnosa_awal']."</td>
										</tr>";
			}}
							?>
							
						   </tbody>
						</table>
					 <div class="body">
					  <div class="row clearfix">
                                <form method="post" action="">
								<div class="col-sm-3">
								<div class="form-group">
                                      <div class="form-line"> 
								<select name="ruangan" style=""class="form-control kd_tdk">
								  <option value="" selected="selected" >Pilih Ruangan</option>  
								<?php
								$query = "SELECT * FROM bangsal where kd_bangsal !='-' and kd_bangsal !='B0009' and kd_bangsal !='B0013' and kd_bangsal !='B0015'";
								$hasil = query($query);
								  while ($data = mysqli_fetch_array($hasil)){
								  echo "<option value='".$data['kd_bangsal']."'>".$data['nm_bangsal']."</option>";
								}
								?>    
                    </select>
                                      </div>
                                    </div>
									</div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <input type="text" name="tgl_awal" class="datepicker form-control" placeholder="tanggal awal"value="<?php echo date('Y-m-d');?>">
                                        </div>
                                    </div>
                                </div>
								<div class="col-sm-4">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <input type="text" name="tgl_akhir" class="datepicker form-control" placeholder="tanggal akhir"value="<?php echo date('Y-m-d');?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-1">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <input value="Cari Data" type="submit" class="btn bg-blue btn-block btn-lg waves-effect">
                                        </div>
                                    </div>
								</div>
								</form>
					   </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
include_once('layout/footer.php');
?>
