<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Data Periksa Lab';
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
                                Data Periksa Lab
                            </h2>
                        </div>
                        <div class="body">
						  <?php 
							  $action = isset($_GET['action'])?$_GET['action']:null;
							  if(!$action){
							?>
							<form method="POST" action="" enctype="multipart/form-data">
							<div class="form-group form-float">							
								<select name="jenis_bayar" style="width:100%" class="form-control kd_tdk">
									<option value="semua" selected="selected" >SEMUA JENIS BAYAR</option>
									<?php
										$query = "SELECT * FROM penjab where kd_pj NOT IN('-','A16')";
										$hasil = query($query);
									    while ($data = mysqli_fetch_array($hasil)){
											echo "<option value='".$data['png_jawab']."'>".$data['png_jawab']."</option>";
										}
									?>
					
								</select>	
							</div>
							<div class="form-group form-float">
								<div class="form-line">
									<label class="form-label">Tanggal Awal</label>
									<input type="text" name="tanggal_awal" class="datepicker form-control" value="<?php echo date('Y-m-d');?>">
								</div>
							</div>
							<div class="form-group form-float">
								<div class="form-line">
									<label class="form-label">Tanggal Akhir</label>
									<input type="text" name="tanggal_akhir" class="datepicker form-control" value="<?php echo date('Y-m-d');?>">
								</div>
							</div>	
							<button type="submit" class='btn btn-block btn-lg btn-info waves-effect'> 
								CARI DATA
							</button>
							</form> <br><br>
							<div class="form-line"></div>
							<table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
							<thead>
							<tr>
								<th>No. Rawat</th>
								<th>No. RM</th>
								<th>Nama Pasien</th>
								<th>Jenis Bayar</th>
								<th>Status</th>
								<th>Total Biaya</th>  
							</tr>
							</thead>
							<tbody>
								<?php
								if (isset($_POST['tanggal_awal'])){
									$tgl_awal = $_POST['tanggal_awal'];
									}
								if (isset($_POST['tanggal_akhir'])){
									$tgl_akhir = $_POST['tanggal_akhir'];
									}
								if (isset($_POST['jenis_bayar'])){
									$jenis_bayar = $_POST['jenis_bayar'];
									if ($jenis_bayar == "semua"){
									$query = "SELECT
													a.no_rawat,
													b.no_rkm_medis,
													c.nm_pasien,
													d.png_jawab,
													a.status,
													SUM(a.biaya) as total_biaya
												FROM 
													periksa_lab as a,
													reg_periksa as b,
													pasien as c,
													penjab as d
												WHERE
													a.no_rawat=b.no_rawat
													AND
													b.no_rkm_medis=c.no_rkm_medis
													AND
													b.kd_pj=d.kd_pj
													AND
													tgl_periksa>='$tgl_awal' AND tgl_periksa<='$tgl_akhir'  
													GROUP BY
													a.no_rawat"; 	
													
									$execute=query($query); 
											while ($row = fetch_array($execute)){
											?>
											  <tr>
												  <td><?php echo $row['no_rawat'];?></td>
												  <td><?php echo $row['no_rkm_medis'];?></td>
												  <td><?php echo $row['nm_pasien'];?></td>
												  <td><?php echo $row['png_jawab'];?></td>
												  <td><?php echo $row['status'];?></td>
												  <td><?php echo $row['total_biaya'];?></td> 
											  </tr>
									<?php			
										}	
									}else{
									$query = "SELECT
													a.no_rawat,
													b.no_rkm_medis,
													c.nm_pasien,
													d.png_jawab,
													a.status,
													SUM(a.biaya) as total_biaya
												FROM 
													periksa_lab as a,
													reg_periksa as b,
													pasien as c,
													penjab as d
												WHERE
													a.no_rawat=b.no_rawat
													AND
													b.no_rkm_medis=c.no_rkm_medis
													AND
													b.kd_pj=d.kd_pj
													AND
													tgl_periksa>='$tgl_awal' AND tgl_periksa<='$tgl_akhir'  
													AND
													d.png_jawab='$jenis_bayar'
													GROUP BY
													a.no_rawat"; 
										$execute=query($query); 
											while ($row = fetch_array($execute)){
											?>
											  <tr>
												  <td><?php echo $row['no_rawat'];?></td>
												  <td><?php echo $row['no_rkm_medis'];?></td>
												  <td><?php echo $row['nm_pasien'];?></td>
												  <td><?php echo $row['png_jawab'];?></td>
												  <td><?php echo $row['status'];?></td>
												  <td><?php echo $row['total_biaya'];?></td> 
											  </tr>
										<?php
										}
									}
								}
							?>				
							</tbody>
						</table>					
							<?php  
							}  
						  ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
include_once('layout/footer.php');
?>
