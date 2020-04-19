<?php
if(!defined("INDEX")) header('location: ../index.php');

$show = isset($_GET['show']) ? $_GET['show'] : "";
$link = "?module=admisi";
switch($show){
	default:
	if(userroles('role')=="admin"){
		echo '<div class="block-header">';
		echo '	<h2>';
		echo '		DATA PENDAFTARAN';
		echo '		<small>Periode '.tgl_indonesia($date).'</small>';
		echo '	</h2>';
		echo '</div>';
	}
	?>
	<div class="card">
			<div class="header">
					<h2>
							Formulir Pendaftaran <button class="btn btn-primary btn-xs pull-right top-button waves-effect accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapsePendaftaran"><i class="material-icons">swap_vert</i></button>
					</h2>
			</div>
			<div class="panel-group" id="accordion">
				<div class="panel panel-default" style="border: none !important;">
					<div id="collapsePendaftaran" class="panel-collapse collapse in" style="margin-top:0px;">
						<div class="panel-body">
							<form class="form-horizontal_">
									<div class="row clearfix">
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="form-group">
															<label for="no_rm" class="col-md-4 form-control-label font-20">No. RM :</label>
															<div class="col-md-8">
																	<div class="input-group input-group-lg">
																			<div class="form-line">
																					<input type="text" class="form-control" id="no_rkm_medis" placeholder="Nomor Rekam Medis" readonly>
																			</div>
																			<span class="input-group-addon">
																					<i class="material-icons" data-toggle="modal" data-target="#pasienModal">attach_file</i>
																			</span>
																	</div>
															</div>
													</div>
											</div>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="form-group">
															<label for="nm_pasien" class="col-md-4 form-control-label font-20">Nama Pasien :</label>
															<div class="col-md-8">
																	<div class="input-group input-group-lg">
																			<div class="form-line">
																					<input type="text" class="form-control" id="nm_pasien" placeholder="Nama Lengkap Dengan Gelar" readonly>
																			</div>
																	</div>
															</div>
													</div>
											</div>
									</div>
									<div class="row clearfix">
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="form-group">
															<label for="no_rawat" class="col-md-4 form-control-label font-20">No. Rawat :</label>
															<div class="col-md-8">
																	<div class="input-group input-group-lg">
																			<div class="form-line">
																					<input type="text" class="form-control" id="no_rawat" value="<?php echo setNoRawat(); ?>" placeholder="Nomor Rawat" readonly>
																			</div>
																	</div>
															</div>
													</div>
											</div>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="form-group">
															<label for="nm_pasien" class="col-md-4 form-control-label font-20">Tgl Registrasi :</label>
															<div class="col-md-8">
																	<div class="input-group input-group-lg">
																			<div class="form-line">
																					<input type="text" class="form-control datepicker" id="tgl_registrasi" value="<?php echo $date_time; ?>" placeholder="Tanggal Pendaftaran" readonly>
																			</div>
																	</div>
															</div>
													</div>
											</div>
									</div>
									<div class="row clearfix">
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="form-group">
															<label for="nm_pasien" class="col-md-4 form-control-label font-20">Unit :</label>
															<div class="col-md-8">
																	<div class="input-group input-group-lg">
																			<div class="form-line">
																					<input type="hidden" class="form-control" id="kd_poli"><input type="text" class="form-control" id="nm_poli" placeholder="Unit atau Klinik" readonly>
																			</div>
																			<span class="input-group-addon">
																					<i class="material-icons" data-toggle="modal" data-target="#unitModal">attach_file</i>
																			</span>
																	</div>
															</div>
													</div>
											</div>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="form-group">
															<label for="dokter" class="col-md-4 form-control-label font-20">Dokter :</label>
															<div class="col-md-8">
																	<div class="input-group input-group-lg">
																			<div class="form-line">
																					<input type="hidden" class="form-control" id="kd_dokter"><input type="text" class="form-control" id="nm_dokter" placeholder="Dokter tujuan" readonly>
																			</div>
																			<span class="input-group-addon">
																					<i class="material-icons" data-toggle="modal" data-target="#dokterModal">attach_file</i>
																			</span>
																	</div>
															</div>
													</div>
											</div>
									</div>
									<div class="row clearfix">
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="form-group">
															<label for="keluarga" class="col-md-4 form-control-label font-20">Png. Jawab :</label>
															<div class="col-md-8">
																	<div class="input-group input-group-lg">
																			<div class="form-line">
																					<input type="text" class="form-control" id="namakeluarga" placeholder="Nama Penanggung Jawab" readonly>
																			</div>
																	</div>
															</div>
													</div>
											</div>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="form-group">
															<label for="nm_pasien" class="col-md-4 form-control-label font-20">Alamat :</label>
															<div class="col-md-8">
																	<div class="input-group input-group-lg">
																			<div class="form-line">
																					<input type="text" class="form-control" id="alamatpj" placeholder="Alamat Penanggung Jawab" readonly>
																			</div>
																	</div>
															</div>
													</div>
											</div>
									</div>
									<div class="row clearfix">
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="form-group">
															<label for="png_jawab" class="col-md-4 form-control-label font-20">Jenis Bayar :</label>
															<div class="col-md-8">
																	<div class="input-group input-group-lg">
																			<div class="form-line">
																					<input type="hidden" class="form-control" id="kd_pj"><input type="text" class="form-control" id="png_jawab" placeholder="Jenis Bayar" readonly>
																			</div>
																			<span class="input-group-addon">
																					<i class="material-icons" data-toggle="modal" data-target="#penjabModal">attach_file</i>
																			</span>
																	</div>
															</div>
													</div>
											</div>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
													<div class="form-group">
															<label for="nm_pasien" class="col-md-4 form-control-label font-20">Rujukan :</label>
															<div class="col-md-8">
																	<div class="input-group input-group-lg">
																			<div class="form-line">
																					<input type="text" class="form-control" id="nama_perujuk" placeholder="Asal Rujukan">
																			</div>
																			<span class="input-group-addon">
																					<i class="material-icons" data-toggle="modal" data-target="#perujukModal">attach_file</i>
																			</span>
																	</div>
															</div>
													</div>
											</div>
									</div>
									<div class="row clearfix" style="margin-bottom:40px;">
											<div class="col-lg-12 text-center">
													<button type="button" class="btn btn-lg btn-primary m-t-15 m-l-5 m-r-5 waves-effect" id="simpan">SIMPAN</button>
													<button type="button" class="btn btn-lg btn-info m-t-15 m-l-5 m-r-5 waves-effect" id="ganti">GANTI</button>
													<button type="button" class="btn btn-lg btn-success m-t-15 m-l-5 m-r-5 waves-effect" id="reset">BARU</button>
													<button type="button" class="btn btn-lg btn-danger m-t-15 m-l-5 wm-r-5 aves-effect" id="hapus">HAPUS</button>
											</div>
									</div>
							</form>
						</div>
					</div>
				</div>
			</div>
	</div>
	<?php
	buka_section_body('Tabel Pendaftaran');
	$no = 1;
	$sql = "SELECT a.nm_pasien, b.no_rkm_medis, a.alamat, c.png_jawab, d.nm_poli, b.no_rawat, b.no_reg, b.tgl_registrasi, b.jam_reg, b.p_jawab, b.almt_pj, b.stts, f.kd_dokter, f.nm_dokter, b.kd_poli, c.kd_pj, a.no_tlp FROM pasien a, reg_periksa b, penjab c, poliklinik d, dokter f WHERE a.no_rkm_medis = b.no_rkm_medis AND b.kd_pj = c.kd_pj AND b.kd_poli = d.kd_poli AND b.kd_dokter = f.kd_dokter";
	if(userroles('role') == 'medis' || userroles('role') == 'paramedis') {
		$sql .= " AND b.kd_poli = userroles('cap')";
	}
	if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) {
		$sql .= " AND b.tgl_registrasi BETWEEN '$_POST[tgl_awal]' AND '$_POST[tgl_akhir]'";
	} else {
			$sql .= " AND b.tgl_registrasi = '$date'";
	}
	$sql .= " ORDER BY b.no_reg";

	$query = $mysqli->query($sql);
	?>
	<table id="pendaftaran" class="table table-bordered table-striped table-hover nowrap" width="100%">
			<thead>
					<tr>
							<th>Nama Pasien</th>
							<th>No. RM</th>
							<th>No. Reg</th>
							<th>Poliklinik</th>
							<th>Dokter</th>
							<th>Jenis Bayar</th>
							<th>Alamat</th>
							<th>Tgl. Reg</th>
							<th>Jam Reg</th>
							<th>Aksi</th>
					</tr>
			</thead>
			<tbody>
			<?php
			while($data = $query->fetch_array()){
				$perujuk = $mysqli->query("SELECT perujuk FROM rujuk_masuk WHERE no_rawat = '".$data['5']."'")->fetch_array();
				if(!empty($perujuk['perujuk'])) {
					$rujuk_masuk = $perujuk['perujuk'];
				} else {
					$rujuk_masuk = "";
				}
			?>
			<tr class="editpasien"
				data-norm="<?php echo $data['1']; ?>"
				data-nmpasien="<?php echo $data['0']; ?>"
				data-tglregistrasi="<?php echo $data['7']; ?> <?php echo $data['8']; ?>"
				data-norawat="<?php echo $data['5']; ?>"
				data-namakeluarga="<?php echo $data['9']; ?>"
				data-alamatpj="<?php echo $data['10']; ?>"
				data-pngjawab="<?php echo $data['3']; ?>"
				data-perujuk="<?php echo $rujuk_masuk; ?>"
				data-nmdokter="<?php echo $data['13']; ?>"
				data-kddokter="<?php echo $data['12']; ?>"
				data-nmpoli="<?php echo $data['4']; ?>"
				data-kdpoli="<?php echo $data['14']; ?>"
				data-kdpj="<?php echo $data['15']; ?>"
			>
					<td><?php echo SUBSTR($data['0'], 0, 25).' ...'; ?></td>
					<td><?php echo $data['1']; ?></td>
					<td><?php echo $data['6']; ?></td>
					<td><?php echo SUBSTR($data['4'], 0, 25).' ...'; ?></td>
					<td><?php echo SUBSTR($data['13'], 0, 25).' ...'; ?></td>
					<td><?php echo $data['3']; ?></td>
					<td><?php echo SUBSTR($data['2'], 0, 25).' ...'; ?></td>
					<td><?php echo $data['7']; ?></td>
					<td><?php echo $data['8']; ?></td>
					<td>
						<a href="pdf/bukti_pendaftaran.php?id=<?php echo $data['5']; ?>" target="_blank" type="button" class="btn btn-xs btn-success waves-effect" id="cetak">CETAK</a>
						<a href="pdf/bukti_pendaftaran.php?id=<?php echo $data['5']; ?>" target="_blank" type="button" class="btn btn-xs btn-primary waves-effect" id="bpjs">BPJS</a>
					</td>
			</tr>
			<?php
				$no++;
			}
			?>
			</tbody>
	</table>
	<div class="row clearfix">
		<form method="post" action="">
		<div class="col-sm-5">
				<div class="form-group">
						<div class="form-line">
								<input type="text" name="tgl_awal" class="datepicker form-control" placeholder="Pilih tanggal awal...">
						</div>
				</div>
		</div>
		<div class="col-sm-5">
				<div class="form-group">
						<div class="form-line">
								<input type="text" name="tgl_akhir" class="datepicker form-control" placeholder="Pilih tanggal akhir...">
						</div>
				</div>
		</div>
		<div class="col-sm-2">
				<div class="form-group">
						<div class="form-line">
								<input type="submit" class="btn bg-blue btn-block btn-lg waves-effect">
						</div>
				</div>
		</div>
		</form>
	</div>
	<?php
	//tutup_tabel();
	tutup_section_body();
	?>
	<div class="modal fade" id="pasienModal" tabindex="-1" role="dialog" aria-labelledby="pasienModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
					<div class="modal-content">
							<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									<h4 class="modal-title" id="pasienModalLabel">Database Pasien</h4>
							</div>
							<div class="modal-body">
									<table id="pasien" class="table table-bordered table-striped table-hover nowrap" width="100%">
											<thead>
													<tr>
														<th>No. RM</th>
														<th>Nama Pasien</th>
														<th>No KTP/SIM</th>
														<th>J.K</th>
														<th>Tmp. Lahir</th>
														<th>Tgl. Lahir</th>
														<th>Nama Ibu</th>
														<th>Alamat</th>
														<th>Gol. Darah</th>
														<th>Pekerjaan</th>
														<th>Stts. Nikah</th>
														<th>Agama</th>
														<th>Tgl. Daftar</th>
														<th>No. Tlp</th>
														<th>Umur</th>
														<th>Pendidikan</th>
														<th>Keluarga</th>
														<th>Nama Keluarga</th>
														<th>Asuransi</th>
														<th>No. Asuransi</th>
														<th>Pekerjaan PJ</th>
														<th>Alamat PJ</th>
														<th>NIP/NRP</th>
														<th>E-Mail</th>
														<th>Cacat Fisik</th>
													</tr>
											</thead>
											<tbody>
											</tbody>
									</table>
							</div>
					</div>
			</div>
	</div>
	<div class="modal fade" id="dokterModal" tabindex="-1" role="dialog" aria-labelledby="dokterModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
					<div class="modal-content">
							<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									<h4 class="modal-title" id="dokterModalLabel">Database Dokter</h4>
							</div>
							<div class="modal-body">
									<table class="table table-bordered table-striped table-hover display" width="100%">
											<thead>
													<tr>
															<th>#</th>
															<th>Kode Dokter</th>
															<th>Nama Dokter</th>
													</tr>
											</thead>
											<tbody>
												<?php
												$no = 1;
												$query = $mysqli->query("SELECT * FROM dokter");
												if (!empty($query) && $query->num_rows > 0) {
													while($data = $query->fetch_array()){
														echo '<tr class="pilihdokter" data-kddokter="'.$data['kd_dokter'].'" data-nmdokter="'.$data['nm_dokter'].'" >';
														echo '<td>'.$no.'</td>';
														echo '<td>'.$data['kd_dokter'].'</td>';
														echo '<td>'.$data['nm_dokter'].'</td>';
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

	<div class="modal fade" id="unitModal" tabindex="-1" role="dialog" aria-labelledby="unitModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
					<div class="modal-content">
							<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									<h4 class="modal-title" id="unitModalLabel">Database Poliklinik</h4>
							</div>
							<div class="modal-body">
									<table class="table table-bordered table-striped table-hover display" width="100%">
											<thead>
													<tr>
															<th>#</th>
															<th>Kode Klinik</th>
															<th>Nama Klinik</th>
													</tr>
											</thead>
											<tbody>
											<?php
											$no = 1;
											$query = $mysqli->query("SELECT * FROM poliklinik");
											if (!empty($query) && $query->num_rows > 0) {
												while($data = $query->fetch_array()){
													echo '<tr class="pilihpoliklinik" data-kdpoli="'.$data['kd_poli'].'" data-nmpoli="'.$data['nm_poli'].'" >';
													echo '<td>'.$no.'</td>';
													echo '<td>'.$data['kd_poli'].'</td>';
													echo '<td>'.$data['nm_poli'].'</td>';
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

	<div class="modal fade" id="penjabModal" tabindex="-1" role="dialog" aria-labelledby="penjabModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
					<div class="modal-content">
							<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									<h4 class="modal-title" id="penjabModalLabel">Database Cara Bayar</h4>
							</div>
							<div class="modal-body">
									<table class="table table-bordered table-striped table-hover display" width="100%">
											<thead>
													<tr>
															<th>#</th>
															<th>Kode Cara Bayar</th>
															<th>Nama Cara Bayar</th>
													</tr>
											</thead>
											<tbody>
											<?php
											$no = 1;
											$query = $mysqli->query("SELECT * FROM penjab");
											if (!empty($query) && $query->num_rows > 0) {
												while($data = $query->fetch_array()){
													echo '<tr class="pilihpenjab" data-kdpj="'.$data['kd_pj'].'" data-pngjawab="'.$data['png_jawab'].'" >';
													echo '<td>'.$no.'</td>';
													echo '<td>'.$data['kd_pj'].'</td>';
													echo '<td>'.$data['png_jawab'].'</td>';
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

	<div class="modal fade" id="perujukModal" tabindex="-1" role="dialog" aria-labelledby="perujukModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
					<div class="modal-content">
							<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									<h4 class="modal-title" id="perujukModalLabel">Database Perujuk</h4>
							</div>
							<div class="modal-body">
									<table class="table table-bordered table-striped table-hover display" width="100%">
											<thead>
													<tr>
															<th>#</th>
															<th>Perujuk</th>
													</tr>
											</thead>
											<tbody>
											<?php
											$no = 1;
											$query = $mysqli->query("SELECT perujuk FROM rujuk_masuk GROUP BY perujuk");
											if (!empty($query) && $query->num_rows > 0) {
												while($data = $query->fetch_array()){
													echo '<tr class="pilihperujuk" data-perujuk="'.$data['perujuk'].'" >';
													echo '<td>'.$no.'</td>';
													echo '<td>'.$data['perujuk'].'</td>';
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

	<?php
	break;

}

function addCSS() {
	echo '<link href="assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css" rel="stylesheet" />';
	dataTablesCSS();
}
function addJS() {
	global $date_time;
	echo '<script src="assets/plugins/momentjs/moment.js"></script>';
	echo '<script src="assets/plugins/momentjs/locale/id.js"></script>';
  echo '<script src="assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js"></script>';
	dataTablesJS();
	dataTablesDisplay();
	datePicker();
	?>
	<script>
		$(document).ready(function() {
		    var t = $('#pasien').DataTable( {
						"bInfo" : true,
		      	"scrollX": true,
		        "processing": true,
		        "serverSide": true,
		        "responsive": true,
		        "oLanguage": {
		            "sProcessing":   "Sedang memproses...",
		            "sLengthMenu":   "Tampilkan _MENU_ entri",
		            "sZeroRecords":  "Tidak ditemukan data yang sesuai",
		            "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
		            "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
		            "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
		            "sInfoPostFix":  "",
		            "sSearch":       "Cari:",
		            "sUrl":          "",
		            "oPaginate": {
		                "sFirst":    "«",
		                "sPrevious": "‹",
		                "sNext":     "›",
		                "sLast":     "»"
		            }
		        },
		        "order": [[ 0, 'asc' ]],
						"ajax": "ajax/pendaftaran.php",
						"createdRow": function( row, data, index ) {
								$(row).addClass('pilihpasien');
								$(row).attr('data-norm', data[0]);
								$(row).attr('data-nmpasien', data[1]);
								$(row).attr('data-namakeluarga', data[6]);
								$(row).attr('data-alamatpj', data[21]);
						}
		    } );
				$('#pendaftaran').DataTable( {
						"bInfo" : true,
						"scrollX": true,
						"processing": true,
						"serverSide": false,
						"responsive": true,
						"oLanguage": {
								"sProcessing":   "Sedang memproses...",
								"sLengthMenu":   "Tampilkan _MENU_ entri",
								"sZeroRecords":  "Tidak ditemukan data yang sesuai",
								"sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
								"sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
								"sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
								"sInfoPostFix":  "",
								"sSearch":       "Cari:",
								"sUrl":          "",
								"oPaginate": {
										"sFirst":    "«",
										"sPrevious": "‹",
										"sNext":     "›",
										"sLast":     "»"
								}
						},
						"order": [[ 2, 'desc' ]]
				} );
				$(document).on('click', '.pilihpasien', function (e) {
		        var no_rkm_medis = $(this).attr('data-norm');
		        $.ajax({
		            url:'ajax/pendaftaran.php?p=check',
		            type:'POST',
		            dataType: "json",
		            data:{
		                no_rkm_medis:no_rkm_medis
		            },
		           success:function(data){
		              if(data.status == 'exist'){
		                alert('Ada tagihan belum bayar, silahkan hubungi kasir!')
		                $('#pasienModal').modal('hide');
		                $(':input').val('');
		                $("#no_rawat")[0].value = '<?php echo setNoRawat(); ?>';
		                $("#tgl_registrasi")[0].value = '<?php echo $date_time; ?>';
		              }
		              //console.log(data);
		           }
		        });
		        document.getElementById("nm_pasien").value = $(this).attr('data-nmpasien');
		        document.getElementById("no_rkm_medis").value = $(this).attr('data-norm');
		        document.getElementById("namakeluarga").value = $(this).attr('data-namakeluarga');
		        document.getElementById("alamatpj").value = $(this).attr('data-alamatpj');
		        $('#pasienModal').modal('hide');
		    });
		    $(document).on('click', '.pilihdokter', function (e) {
		        document.getElementById("kd_dokter").value = $(this).attr('data-kddokter');
		        document.getElementById("nm_dokter").value = $(this).attr('data-nmdokter');
		        $('#dokterModal').modal('hide');
		    });
		    $(document).on('click', '.pilihpoliklinik', function (e) {
		        document.getElementById("kd_poli").value = $(this).attr('data-kdpoli');
		        document.getElementById("nm_poli").value = $(this).attr('data-nmpoli');
		        $('#unitModal').modal('hide');
		    });
		    $(document).on('click', '.pilihpenjab', function (e) {
		        document.getElementById("kd_pj").value = $(this).attr('data-kdpj');
		        document.getElementById("png_jawab").value = $(this).attr('data-pngjawab');
		        $('#penjabModal').modal('hide');
		    });
		    $(document).on('click', '.pilihperujuk', function (e) {
		        document.getElementById("nama_perujuk").value = $(this).attr('data-perujuk');
		        $('#perujukModal').modal('hide');
		    });
		    $(document).on('click', '.editpasien', function (e) {
		        document.getElementById("no_rkm_medis").value = $(this).attr('data-norm');
		        document.getElementById("nm_pasien").value = $(this).attr('data-nmpasien');
		        document.getElementById("no_rawat").value = $(this).attr('data-norawat');
		        document.getElementById("tgl_registrasi").value = $(this).attr('data-tglregistrasi');
		        document.getElementById("nm_dokter").value = $(this).attr('data-nmdokter');
		        document.getElementById("kd_dokter").value = $(this).attr('data-kddokter');
		        document.getElementById("kd_poli").value = $(this).attr('data-kdpoli');
		        document.getElementById("nm_poli").value = $(this).attr('data-nmpoli');
		        document.getElementById("namakeluarga").value = $(this).attr('data-namakeluarga');
		        document.getElementById("kd_pj").value = $(this).attr('data-kdpj');
		        document.getElementById("alamatpj").value = $(this).attr('data-alamatpj');
		        document.getElementById("png_jawab").value = $(this).attr('data-pngjawab');
		        document.getElementById("nama_perujuk").value = $(this).attr('data-perujuk');
		    });
		    $("#simpan").click(function(){
		        var no_rkm_medis = document.getElementById("no_rkm_medis").value;
		        var kd_dokter = document.getElementById("kd_dokter").value;
		        var kd_poli = document.getElementById("kd_poli").value;
		        var kd_pj = document.getElementById("kd_pj").value;
		        var tgl_registrasi = document.getElementById("tgl_registrasi").value;
		        var namakeluarga = document.getElementById("namakeluarga").value;
		        var alamatpj = document.getElementById("alamatpj").value;
		        var nama_perujuk = document.getElementById("nama_perujuk").value;
		        $.ajax({
		            url:'ajax/pendaftaran.php?p=add',
		            method:'POST',
		            data:{
		                no_rkm_medis:no_rkm_medis,
		                kd_dokter:kd_dokter,
		                kd_poli:kd_poli,
		                kd_pj:kd_pj,
		                tgl_registrasi:tgl_registrasi,
		                namakeluarga:namakeluarga,
		                alamatpj:alamatpj,
		                nama_perujuk:nama_perujuk
		            },
		           success:function(data){
		               window.location.reload(true)
		           }
		        });
		    });
		    $("#ganti").click(function(){
		        var no_rkm_medis = document.getElementById("no_rkm_medis").value;
		        var kd_dokter = document.getElementById("kd_dokter").value;
		        var no_rawat = document.getElementById("no_rawat").value;
		        var kd_poli = document.getElementById("kd_poli").value;
		        var kd_pj = document.getElementById("kd_pj").value;
		        var tgl_registrasi = document.getElementById("tgl_registrasi").value;
		        var namakeluarga = document.getElementById("namakeluarga").value;
		        var alamatpj = document.getElementById("alamatpj").value;
		        var nama_perujuk = document.getElementById("nama_perujuk").value;
		        $.ajax({
		            url:'ajax/pendaftaran.php?p=update',
		            method:'POST',
		            data:{
		                no_rkm_medis:no_rkm_medis,
		                no_rawat:no_rawat,
		                kd_dokter:kd_dokter,
		                kd_poli:kd_poli,
		                kd_pj:kd_pj,
		                tgl_registrasi:tgl_registrasi,
		                namakeluarga:namakeluarga,
		                alamatpj:alamatpj,
		                nama_perujuk:nama_perujuk
		            },
		           success:function(data){
		               window.location.reload(true)
		           }
		        });
		    });
		    $("#reset").click(function(){
		      $(':input').val('');
		      $("#no_rawat")[0].value = '<?php echo setNoRawat(); ?>';
		      $("#tgl_registrasi")[0].value = '<?php echo $date_time; ?>';
		    });
		    $("#hapus").click(function(){
		        var no_rawat = document.getElementById("no_rawat").value;
		        $.ajax({
		            url:'ajax/pendaftaran.php?p=delete',
		            method:'POST',
		            data:{
		              no_rawat:no_rawat
		            },
		            success:function(data){
		               window.location.reload(true)
		            }
		        });
		    });
		} );
		</script>
	<?php
}

?>
