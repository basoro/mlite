<?php
		?>

            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                Data Permintaan Resep
                            </h2>
                        </div>
                        <div class="body">
							<form method="POST" action="" enctype="multipart/form-data">
							<div class="form-group form-float">
								<select name="status" style="width:100%" class="form-control kd_tdk">
									<option value="Rajal" selected="selected" >Rawat Jalan</option>
									<option value="Ranap">Rawat Inap</option>
									<option value="IGDK" >IGD</option>
									<option value="U0019" >Hemodialisa</option>
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
								Cari Data Resep
							</button>
							</form> <br><br>
							<div class="form-line"></div>
							<table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
							<thead>
							<tr>
								<th>No. Resep</th>
								<th>Tgl. Resep</th>
								<th>No. Rawat</th>
								<th>No. Rkm Medis</th>
								<th>Nama Pasien</th>
								<th>Nama Dokter</th>
								<th>Ruang</th>
								<th>Status</th>
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
								if (isset($_POST['status'])){
									$status = $_POST['status'];
									//echo $status;
									if ($status == "Rajal"){
										$query = "SELECT a.no_resep,a.tgl_perawatan,a.no_rawat,b.no_rkm_medis,c.nm_pasien,d.nm_dokter,a.tgl_peresepan, e.nm_poli,a.status as stts,
												if (a.jam_peresepan=a.jam,'Belum Terlayani','Sudah Terlayani') as status FROM resep_obat as a,reg_periksa as b,
												pasien as c,dokter as d,poliklinik as e where a.tgl_peresepan >='$tgl_awal' and a.tgl_peresepan <= '$tgl_akhir'
												AND a.no_rawat=b.no_rawat AND b.no_rkm_medis=c.no_rkm_medis and b.kd_poli=e.kd_poli and b.kd_poli NOT IN ('IGDK','U0027','U0019')
												AND a.kd_dokter = d.kd_dokter";
										$execute=query($query);
										while ($row = fetch_array($execute)){
										?>
										  <tr>
											  <td><?php echo $row['no_resep'];?></td>
											  <td><?php echo $row['tgl_peresepan'];?></td>
											  <td><?php echo $row['no_rawat'];?></td>
											  <td><?php echo $row['no_rkm_medis'];?></td>
											  <td><?php echo $row['nm_pasien'];?></td>
											  <td><?php echo $row['nm_dokter'];?></td>
											  <td><?php echo $row['nm_poli'];?></td>
											  <td><?php echo $row['status'];?></td>
										  </tr>
										  <?php
										}
									}
									if ($status == "Ranap"){
										$query = "SELECT a.no_resep,a.tgl_peresepan, a.no_rawat,b.no_rkm_medis, c.nm_pasien, if (a.jam_peresepan=a.jam,'Belum Terlayani','Sudah Terlayani') as status,
												a.status, f.nm_bangsal, g.nm_dokter FROM resep_obat as a, reg_periksa as b, pasien as c,kamar_inap as d,
												kamar as e,bangsal as f, dokter as g where a.tgl_peresepan >='$tgl_awal'  and a.tgl_peresepan <= '$tgl_akhir'
												AND a.status = 'ranap' AND a.no_rawat = b.no_rawat AND b.no_rawat = d.no_rawat AND a.kd_dokter = g.kd_dokter AND
												d.kd_kamar = e.kd_kamar AND e.kd_bangsal = f.kd_bangsal AND b.no_rkm_medis = c.no_rkm_medis";

													$execute=query($query);
													while ($row = fetch_array($execute)){
													?>
													  <tr>
														  <td><?php echo $row['no_resep'];?></td>
														  <td><?php echo $row['tgl_peresepan'];?></td>
														  <td><?php echo $row['no_rawat'];?></td>
														  <td><?php echo $row['no_rkm_medis'];?></td>
														  <td><?php echo $row['nm_pasien'];?></td>
														  <td><?php echo $row['nm_dokter'];?></td>
														  <td><?php echo $row['nm_bangsal'];?></td>
														  <td><?php echo $row['status'];?></td>
													  </tr>
										  <?php
										}
									}
									if ($status == "IGDK"){
										$query = "SELECT a.no_resep,a.tgl_perawatan,a.no_rawat,b.no_rkm_medis,c.nm_pasien,d.nm_dokter,a.tgl_peresepan, e.nm_poli,a.status as stts,
													if (a.jam_peresepan=a.jam,'Belum Terlayani','Sudah Terlayani') as status FROM resep_obat as a,reg_periksa as b,
													pasien as c,dokter as d,poliklinik as e where a.tgl_peresepan >='$tgl_awal' and a.tgl_peresepan <= '$tgl_akhir'
													AND a.no_rawat=b.no_rawat AND b.no_rkm_medis=c.no_rkm_medis and b.kd_poli=e.kd_poli
													AND e.kd_poli = 'IGDK' AND a.kd_dokter = d.kd_dokter GROUP BY a.no_rawat";
													$execute=query($query);
													while ($row = fetch_array($execute)){
													?>
													  <tr>
														  <td><?php echo $row['no_resep'];?></td>
														  <td><?php echo $row['tgl_peresepan'];?></td>
														  <td><?php echo $row['no_rawat'];?></td>
														  <td><?php echo $row['no_rkm_medis'];?></td>
														  <td><?php echo $row['nm_pasien'];?></td>
														  <td><?php echo $row['nm_dokter'];?></td>
														  <td><?php echo $row['nm_poli'];?></td>
														  <td><?php echo $row['status'];?></td>
													  </tr>
										  <?php
										}
									}
									if ($status == "U0019"){
											$query = "SELECT a.no_resep,a.tgl_perawatan,a.no_rawat,b.no_rkm_medis,c.nm_pasien,d.nm_dokter,a.tgl_peresepan, e.nm_poli,a.status as stts,
													if (a.jam_peresepan=a.jam,'Belum Terlayani','Sudah Terlayani') as status FROM resep_obat as a,reg_periksa as b,
													pasien as c,dokter as d,poliklinik as e where a.tgl_peresepan >='$tgl_awal' and a.tgl_peresepan <= '$tgl_akhir'
													AND a.no_rawat=b.no_rawat AND b.no_rkm_medis=c.no_rkm_medis and b.kd_poli=e.kd_poli
													AND e.kd_poli = 'U0019' AND a.kd_dokter = d.kd_dokter";
													$execute=query($query);
													while ($row = fetch_array($execute)){
													?>
													  <tr>
														  <td><?php echo $row['no_resep'];?></td>
														  <td><?php echo $row['tgl_peresepan'];?></td>
														  <td><?php echo $row['no_rawat'];?></td>
														  <td><?php echo $row['no_rkm_medis'];?></td>
														  <td><?php echo $row['nm_pasien'];?></td>
														  <td><?php echo $row['nm_dokter'];?></td>
														  <td><?php echo $row['nm_poli'];?></td>
														  <td><?php echo $row['status'];?></td>
													  </tr>
										  <?php
										}
									  }
									}
								?>
							</tbody>
						</table>
                        </div>
                    </div>
                </div>
            </div>
