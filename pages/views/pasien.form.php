<div class="col-md-8">
		<div class="panel panel-default">
				<div class="panel-heading">
						<h3 class="panel-title">
								Informasi Pribadi
						</h3>
				</div>
				<div class="panel-body">
						<div class="row clearfix">
								<div class="col-md-6">
										<div class="form-group">
												<label for="no_rkm_medis">Nomor RM</label>
												<div class="form-line">
														<input type="text" name="no_rkm_medis" value="<?php echo $data['no_rkm_medis']; ?>" id="no_rkm_medis" class="form-control" readonly>
												</div>
										</div>
								</div>
								<div class="col-md-6">
										<div class="form-group">
												<label for="no_ktp">Nomor KTP</label>
												<div class="input-group input-group">
														<div class="form-line">
																<input type="text" name="no_ktp" value="" id="no_ktp" class="form-control" placeholder="Nomor KTP">
														</div>
														<span class="input-group-addon">
																<div class="dropdown">
																		<a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
																				<i class="material-icons">attach_file</i>
																		</a>
																		<ul class="dropdown-menu pull-right">
																				<li><a href="javascript:void(0);">Bridging NIK BPJS</a></li>
																				<li><a href="javascript:void(0);">Bridging NIK Dukcapil</a></li>
																		</ul>
																</div>
														</span>
												</div>
										</div>
										<?php //buat_textbox_alt("Nomor KTP", "no_ktp", $data['no_ktp']); ?>
								</div>
						</div>
						<div class="row clearfix">
								<div class="col-md-6">
										<?php buat_textbox_alt("Nama Lengkap", "nm_pasien", $data['nm_pasien']); ?>
								</div>
								<div class="col-md-6">
										<?php buat_textbox_alt("Nama Ibu", "nm_ibu", $data['nm_ibu']); ?>
								</div>
						</div>
						<div class="row clearfix">
								<div class="col-md-3">
										<?php buat_textbox_alt("Tempat Lahir", "tmp_lahir", $data['tmp_lahir']); ?>
								</div>
								<div class="col-md-3">
										<?php buat_datepicker_alt("Tanggl Lahir", "tgl_lahir", $data['tgl_lahir']); ?>
								</div>
								<div class="col-md-3">
										<div class="form-group">
												<label>Jenis Kelamin</label>
												<select name="jk" class="form-control show-tick show-tick">
														<option value="">Pilih Data</option>
														<?php
														foreach(getEnum('pasien', 'jk') as $value) {
															$select = $value==$data['jk'] ? 'selected' : '';
															echo '<option value="'.$value.'" '.$select.'>'.$value.'</option>';
														}
														?>
												</select>
										</div>
								</div>
								<div class="col-md-3">
										<div class="form-group">
												<label>Golongan Darah</label>
												<select name="gol_darah" class="form-control show-tick">
														<option value="">Pilih Data</option>
														<?php
														foreach(getEnum('pasien', 'gol_darah') as $value) {
															$select = $value==$data['gol_darah'] ? 'selected' : '';
															echo '<option value="'.$value.'" '.$select.'>'.$value.'</option>';
														}
														?>
												</select>
										</div>
								</div>
						</div>
						<div class="row clearfix">
								<div class="col-md-3">
										<div class="form-group">
												<label>Agama</label>
												<select name="agama" id="agama" class="form-control show-tick" data-width="100%">
													 <?php
													 $agama = array("Islam","Kristen","Protestan","Hindu","Budah","Konghucu","Kepercyaan");
													 foreach($agama as $value) {
														 $select = $value==$data['agama'] ? 'selected' : '';
														 echo '<option value="'.$value.'" '.$select.'>'.$value.'</option>';
													 }
													 ?>
												</select>
										</div>
								</div>
								<div class="col-md-3">
										<div class="form-group">
												<label>Pendidikan</label>
												<select name="pnd" class="form-control show-tick">
														<option value="">Pilih Data</option>
														<?php
														foreach(getEnum('pasien', 'pnd') as $value) {
															$select = $value==$data['pnd'] ? 'selected' : '';
															echo '<option value="'.$value.'" '.$select.'>'.$value.'</option>';
														}
														?>
												</select>
										</div>
								</div>
								<div class="col-md-3">
										<?php buat_textbox_alt("Pekerjaan", "pekerjaan", $data['pekerjaan']); ?>
								</div>
								<div class="col-md-3">
										<div class="form-group">
												<label>Status Nikah</label>
												<select name="stts_nikah" class="form-control show-tick">
														<option value="">Pilih Data</option>
														<?php
														foreach(getEnum('pasien', 'stts_nikah') as $value) {
															$select = $value==$data['stts_nikah'] ? 'selected' : '';
															echo '<option value="'.$value.'" '.$select.'>'.$value.'</option>';
														}
														?>
												</select>
										</div>
								</div>
						</div>
						<div class="row clearfix">
								<div class="col-md-12">
										<?php buat_textarea_alt("Alamat", "alamat", $data['alamat'], "form-line"); ?>
								</div>
						</div>
						<div class="row clearfix">
								<div class="col-md-6">
										<div class="form-group">
												<label for="propinsi">Propinsi</label>
												<div class="input-group input-group">
														<div class="form-line">
																<input type="hidden" name="kd_prop" value="<?php echo $data['kd_prop']; ?>" id="kd_prop">
																<input type="text" name="nm_prop" value="<?php echo propinsi($data['kd_prop']); ?>" id="nm_prop" class="form-control" placeholder="Propinsi">
														</div>
														<span class="input-group-addon">
																<i class="material-icons" data-toggle="modal" data-target="#propinsiModal">attach_file</i>
														</span>
												</div>
										</div>
								</div>
								<div class="col-md-6">
										<div class="form-group">
												<label for="kabupaten">Kabupaten</label>
												<div class="input-group input-group">
														<div class="form-line">
																<input type="hidden" name="kd_kab" value="<?php echo $data['kd_kab']; ?>" id="kd_kab">
																<input type="text" name="nm_kab" value="<?php echo kabupaten($data['kd_kab']); ?>" id="nm_kab" class="form-control" placeholder="Kabupaten">
														</div>
														<span class="input-group-addon">
																<i class="material-icons" data-toggle="modal" data-target="#kabupatenModal">attach_file</i>
														</span>
												</div>
										</div>
								</div>
						</div>
						<div class="row clearfix">
								<div class="col-md-6">
										<div class="form-group">
												<label for="kecamatan">Kecamatan</label>
												<div class="input-group input-group">
														<div class="form-line">
																<input type="hidden" name="kd_kec" value="<?php echo $data['kd_kec']; ?>" id="kd_kec">
																<input type="text" name="nm_kec" value="<?php echo kecamatan($data['kd_kec']); ?>" id="nm_kec" class="form-control" placeholder="Kecamatan">
														</div>
														<span class="input-group-addon">
																<i class="material-icons" data-toggle="modal" data-target="#kecamatanModal">attach_file</i>
														</span>
												</div>
										</div>
								</div>
								<div class="col-md-6">
										<div class="form-group">
												<label for="kelurahan">Kelurahan</label>
												<div class="input-group input-group">
														<div class="form-line">
																<input type="hidden" name="kd_kel" value="<?php echo $data['kd_kel']; ?>" id="kd_kel">
																<input type="text" name="nm_kel" value="<?php echo kelurahan($data['kd_kel']); ?>" id="nm_kel" class="form-control" placeholder="Kelurahan">
														</div>
														<span class="input-group-addon">
																<i class="material-icons" data-toggle="modal" data-target="#kelurahanModal">attach_file</i>
														</span>
												</div>
										</div>
								</div>
						</div>
				</div>
		</div>
		<div class="panel panel-default">
				<div class="panel-heading">
						<h3 class="panel-title">
								Informasi Keluarga
						</h3>
				</div>
				<div class="panel-body">
						<div class="row clearfix">
								<div class="col-md-3">
										<div class="form-group">
												<label>Keluarga</label>
												<select name="keluarga" class="form-control show-tick">
													<option value="">Pilih Data</option>
													<?php
													foreach(getEnum('pasien', 'keluarga') as $value) {
														$select = $value==$data['keluarga'] ? 'selected' : '';
														echo '<option value="'.$value.'" '.$select.'>'.$value.'</option>';
													}
													?>
												</select>
										</div>
								</div>
								<div class="col-md-6">
										<?php buat_textbox_alt("Nama Keluarga", "namakeluarga", $data['namakeluarga']); ?>
								</div>
								<div class="col-md-3">
										<?php buat_textbox_alt("Pekerjaan Keluarga", "pekerjaanpj", $data['pekerjaanpj']); ?>
								</div>
						</div>
						<div class="row clearfix">
								<div class="col-md-12">
										<?php buat_textarea_alt("Alamat Keluarga <button type='button' id='copy_alamat' class='btn btn-xs btn-success'><i class='material-icons'>sync</i></button>", "alamatpj", $data['alamatpj'], "form-line"); ?>
								</div>
						</div>
            <div class="row clearfix">
                <div class="col-md-6">
                    <?php buat_textbox_alt("Propinsi", "propinsipj", $data['propinsipj']); ?>
                </div>
                <div class="col-md-6">
                    <?php buat_textbox_alt("Kabupaten", "kabupatenpj", $data['kabupatenpj']); ?>
                </div>
            </div>
            <div class="row clearfix">
                <div class="col-md-6">
                    <?php buat_textbox_alt("Kecamatan", "kecamatanpj", $data['kecamatanpj']); ?>
                </div>
                <div class="col-md-6">
                    <?php buat_textbox_alt("Kelurahan", "kelurahanpj", $data['kelurahanpj']); ?>
                </div>
            </div>
				</div>
		</div>
</div>
<div class="col-md-4">
		<div class="panel panel-default">
				<div class="panel-heading">
						<h3 class="panel-title">
								Informasi Tambahan
						</h3>
				</div>
				<div class="panel-body">
						<?php buat_textbox_alt("Nomor HP", "no_tlp", $data['no_tlp']); ?>
						<div class="form-group">
								<label>Cara Bayar</label>
								<select name="kd_pj" class="form-control show-tick">
										<option value="">Pilih Data</option>
										<?php
										$query = $mysqli->query("SELECT * FROM penjab");
										while($penjab = $query->fetch_array()){
												$select = $penjab['kd_pj']==$data['kd_pj'] ? 'selected' : '';
												echo'<option value='.$penjab['kd_pj'].' '.$select.'>'.$penjab['png_jawab'].'</option>';
										}
										?>
								</select>
						</div>
						<div class="form-group">
								<label for="no_peserta">Momor Peserta</label>
								<div class="input-group input-group">
										<div class="form-line">
												<input type="text" name="no_peserta" value="" id="no_peserta" class="form-control" placeholder="Nomor Peserta Asuransi">
										</div>
										<span class="input-group-addon">
												<div class="dropdown">
														<a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
																<i class="material-icons">attach_file</i>
														</a>
														<ul class="dropdown-menu pull-right">
																<li><a href="javascript:void(0);">Bridging Kepesertaan BPJS</a></li>
																<li><a href="javascript:void(0);">Bridging Kepesertaan Inhealth</a></li>
														</ul>
												</div>
										</span>
								</div>
						</div>
						<?php //buat_textbox_alt("Nomor Peserta", "no_peserta", $data['no_peserta']); ?>
						<div class="form-group">
								<label>Suku Bangsa</label>
								<select name="suku_bangsa" class="form-control  show-tick">
										<option value="">Pilih Data</option>
										<?php
										$query = $mysqli->query("SELECT * FROM suku_bangsa");
										while($suku_bangsa = $query->fetch_array()){
												$select = $suku_bangsa['id']==$data['suku_bangsa'] ? 'selected' : '';
												echo'<option value='.$suku_bangsa['id'].' '.$select.'>'.$suku_bangsa['nama_suku_bangsa'].'</option>';
										}
										?>
								</select>
						</div>
						<div class="form-group">
								<label>Bahasa</label>
								<select name="bahasa_pasien" class="form-control show-tick">
										<option value="">Pilih Data</option>
										<?php
										$query = $mysqli->query("SELECT * FROM bahasa_pasien");
										while($bahasa_pasien = $query->fetch_array()){
												$select = $bahasa_pasien['id']==$data['bahasa_pasien'] ? 'selected' : '';
												echo'<option value='.$bahasa_pasien['id'].' '.$select.'>'.$bahasa_pasien['nama_bahasa'].'</option>';
										}
										?>
								</select>
						</div>
						<div class="form-group">
								<label>Cacat Fisik</label>
								<select name="cacat_fisik" class="form-control show-tick">
										<option value="">Pilih Data</option>
										<?php
										$query = $mysqli->query("SELECT * FROM cacat_fisik");
										while($cacat_fisik = $query->fetch_array()){
												$select = $cacat_fisik['id']==$data['cacat_fisik'] ? 'selected' : '';
												echo'<option value='.$cacat_fisik['id'].' '.$select.'>'.$cacat_fisik['nama_cacat'].'</option>';
										}
										?>
								</select>
						</div>
				</div>
		</div>
		<div class="panel panel-default">
				<div class="panel-heading">
						<h3 class="panel-title">
								Pengaturan Lainnya
						</h3>
				</div>
				<div class="panel-body">
						<div class="form-group">
								<label>Perusahaan Pasien</label>
								<select name="perusahaan_pasien" class="form-control show-tick">
										<option value="">Pilih Data</option>
										<?php
										$query = $mysqli->query("SELECT * FROM perusahaan_pasien");
										while($perusahaan_pasien = $query->fetch_array()){
												$select = $perusahaan_pasien['kode_perusahaan']==$data['perusahaan_pasien'] ? 'selected' : '';
												echo'<option value='.$perusahaan_pasien['kode_perusahaan'].' '.$select.'>'.$perusahaan_pasien['nama_perusahaan'].'</option>';
										}
										?>
								</select>
						</div>
						<?php buat_textbox_alt("Email", "email", $data['email']); ?>
						<?php buat_datepicker_alt("Tanggal Daftar", "tgl_daftar", date('Y-m-d'), $data['tgl_daftar']); ?>
						<input type="submit" value="Simpan" class="btn btn-lg btn-primary" />
						<a href="pdf/kartu_berobat.php?id=<?php echo $data['no_rkm_medis']; ?>" target="_blank" class="btn btn-lg btn-info">Kartu</a>
						<a href="<?php echo $link; ?>" class="btn btn-lg btn-warning">Batal</a>
						<a href="<?php echo $link; ?>&show=delete&id=<?php echo $data['no_rkm_medis']; ?>" class="btn btn-lg btn-danger delete">Hapus</a>
				</div>
		</div>
</div>

<div class="modal fade" id="propinsiModal" tabindex="-1" role="dialog" aria-labelledby="propinsiModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
				<div class="modal-content">
						<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title" id="propinsiModalLabel">Database Propinsi</h4>
						</div>
						<div class="modal-body">
								<table class="table table-bordered table-striped table-hover display" width="100%">
										<thead>
												<tr>
														<th>#</th>
														<th>Kode Propinsi</th>
														<th>Nama Propinsi</th>
												</tr>
										</thead>
										<tbody>
										<?php
										$no = 1;
										$query = $mysqli->query("SELECT * FROM propinsi");
										if (!empty($query) && $query->num_rows > 0) {
											while($data = $query->fetch_array()){
												echo '<tr class="pilihpropinsi" data-kdprop="'.$data['kd_prop'].'" data-nmprop="'.$data['nm_prop'].'" >';
												echo '<td>'.$no.'</td>';
												echo '<td>'.$data['kd_prop'].'</td>';
												echo '<td>'.$data['nm_prop'].'</td>';
												echo '</tr>';
												$no++;
											}
										}
										?>
										</tbody>
								</table>
						</div>
				</div>
		</div>
</div>

<div class="modal fade" id="kabupatenModal" tabindex="-1" role="dialog" aria-labelledby="kabupatenModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
				<div class="modal-content">
						<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title" id="kabupatenModalLabel">Database Kabupaten</h4>
						</div>
						<div class="modal-body">
								<table class="table table-bordered table-striped table-hover display" width="100%">
										<thead>
												<tr>
														<th>#</th>
														<th>Kode Kabupaten</th>
														<th>Nama Kabupaten</th>
												</tr>
										</thead>
										<tbody>
										<?php
										$no = 1;
										$query = $mysqli->query("SELECT * FROM kabupaten");
										if (!empty($query) && $query->num_rows > 0) {
											while($data = $query->fetch_array()){
												echo '<tr class="pilihkabupaten" data-kdkab="'.$data['kd_kab'].'" data-nmkab="'.$data['nm_kab'].'" >';
												echo '<td>'.$no.'</td>';
												echo '<td>'.$data['kd_kab'].'</td>';
												echo '<td>'.$data['nm_kab'].'</td>';
												echo '</tr>';
												$no++;
											}
										}
										?>
										</tbody>
								</table>
						</div>
				</div>
		</div>
</div>

<div class="modal fade" id="kecamatanModal" tabindex="-1" role="dialog" aria-labelledby="kecamatanModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
				<div class="modal-content">
						<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title" id="kecamatanModalLabel">Database Kecamatan</h4>
						</div>
						<div class="modal-body">
								<table class="table table-bordered table-striped table-hover display" width="100%">
										<thead>
												<tr>
														<th>#</th>
														<th>Kode Kecamatan</th>
														<th>Nama Kecamatan</th>
												</tr>
										</thead>
										<tbody>
										<?php
										$no = 1;
										$query = $mysqli->query("SELECT * FROM kecamatan");
										if (!empty($query) && $query->num_rows > 0) {
											while($data = $query->fetch_array()){
												echo '<tr class="pilihkecamatan" data-kdkec="'.$data['kd_kec'].'" data-nmkec="'.$data['nm_kec'].'" >';
												echo '<td>'.$no.'</td>';
												echo '<td>'.$data['kd_kec'].'</td>';
												echo '<td>'.$data['nm_kec'].'</td>';
												echo '</tr>';
												$no++;
											}
										}
										?>
										</tbody>
								</table>
						</div>
				</div>
		</div>
</div>

<div class="modal fade" id="kelurahanModal" tabindex="-1" role="dialog" aria-labelledby="kelurahanModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
				<div class="modal-content">
						<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title" id="kelurahanModalLabel">Database Kelurahan</h4>
						</div>
						<div class="modal-body">
							<div class="form-horizontal form-data" id="form-data">
									<div class="row clearfix">
											<div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
													<label for="email_address_2">Nama Kelurahan :</label>
											</div>
											<div class="col-lg-8 col-md-8 col-sm-8">
												<div class="input-group input-group-lg">
														<div class="form-line">
																<input type="text" class="form-control" name="nama_kelurahan" id="nama_kelurahan" placeholder="Nama Kelurahan">
														</div>
														<p class="text-danger" id="err_nama_kelurahan"></p>
												</div>
											</div>
									</div>
									<div class="row clearfix" style="margin-bottom:40px;">
											<div class="col-lg-12 text-center">
													<button type="button" class="btn btn-lg btn-primary m-t-15 m-l-15 waves-effect" id="simpan-kelurahan">SIMPAN</button>
											</div>
									</div>
							</div>

								<table id="kelurahan" class="table table-bordered table-striped table-hover nowrap" width="100%">
										<thead>
												<tr>
														<th>Kode Kelurahan</th>
														<th>Nama Kelurahan</th>
												</tr>
										</thead>
										<tbody>
										</tbody>
								</table>
						</div>
				</div>
		</div>
</div>
