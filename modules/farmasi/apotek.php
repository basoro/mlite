<?php
if(!defined("INDEX")) header('location: ../index.php');

$show = isset($_GET['show']) ? $_GET['show'] : "";
$link = "?module=apotek";
?>
<div class="block-header">
		<h2>
				APOTEK
				<small>Periode <?php if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) { echo tgl_indonesia($_POST['tgl_awal'])." s/d ".tgl_indonesia($_POST['tgl_akhir']); } else { echo tgl_indonesia($date) . ' s/d ' . tgl_indonesia($date);} ?></small>
		</h2>
</div>

<div class="row clearfix">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="card">
						<div class="header">
								<h2>
										Data Resep Obat
								</h2>
						</div>

<?php
switch($show){
	default:
?>
			<div class="body">
					<table class="table table-bordered table-striped table-hover display nowrap" width="100%">
							<thead>
									<tr>
											<th>No</th>
											<th>Nama Pasien</th>
											<th>No. RM</th>
											<th>Jenis Bayar</th>
											<th>Status Bayar</th>
											<th>Resep</th>
									</tr>
							</thead>
							<tbody>
							<?php
							$sql = "SELECT a.nm_pasien, b.no_rkm_medis, a.alamat, c.png_jawab, b.stts, b.status_bayar, d.no_resep, GROUP_CONCAT(f.nama_brng, ' (', e.jml, ') - ', e.aturan_pakai SEPARATOR '<br>') AS resep_dokter, b.no_rawat FROM pasien a, reg_periksa b, penjab c, resep_obat d, resep_dokter e, databarang f WHERE a.no_rkm_medis = b.no_rkm_medis AND b.kd_pj = c.kd_pj AND b.status_lanjut = 'Ralan' AND b.no_rawat = d.no_rawat AND d.no_resep = e.no_resep AND e.kode_brng = f.kode_brng";
							if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) {
								$sql .= " AND b.tgl_registrasi BETWEEN '$_POST[tgl_awal]' AND '$_POST[tgl_akhir]'";
							} else {
									$sql .= " AND b.tgl_registrasi = '$date'";
							}
							$sql .= " GROUP BY b.no_rawat";
							$query = $mysqli->query($sql);
							$no = 1;
							while($row = $query->fetch_array()) {
							?>
									<tr>
											<td><?php echo $no; ?></td>
											<td><?php echo SUBSTR($row['0'],0,20); ?><br>Alamat: <?php echo $row['2']; ?></td>
											<td><a href="<?php echo $link; ?>&show=view&no_rawat=<?php echo $row['8']; ?>"><?php echo $row['1']; ?></a></td>
											<td><?php echo $row['3']; ?></td>
											<td><?php echo $row['5']; ?></td>
											<td><?php echo $row['7']; ?></td>
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
			</div>
<?php
	break;
	case "view":
	$no_rkm_medis = rawatinfo($_GET['no_rawat'], 'no_rkm_medis');
?>
			<div class="body">
				<dl class="dl-horizontal">
					<dt class="col-1">Nama Lengkap</dt>
					<dd class="col-1"><?php echo pasieninfo($no_rkm_medis, 'nm_pasien'); ?></dd>
					<dt class="col-2">No. RM</dt>
					<dd class="col-2"><?php echo $no_rkm_medis; ?></dd>
					<dt>No. Rawat</dt>
					<dd><?php echo $_GET['no_rawat']; ?></dd>
					<dt>Cara Bayar</dt>
					<dd><?php echo pasieninfo($no_rkm_medis, 'kd_pj'); ?></dd>
					<dt>Umur</dt>
					<dd><?php echo pasieninfo($no_rkm_medis, 'umur'); ?> Th</dd>
				</dl>
			</div>
			<div class="body">
				<table class="table table-striped">
					 <thead>
							 <tr>
									 <th>Nama Obat</th>
									 <th>Jumlah</th>
									 <th>Aturan Pakai</th>
							 </tr>
					 </thead>
					 <tbody>
					 <?php
					 $query_resep = $mysqli->query("SELECT a.kode_brng, a.jml, a.aturan_pakai, b.nama_brng, a.no_resep FROM resep_dokter a, databarang b, resep_obat c WHERE a.kode_brng = b.kode_brng AND a.no_resep = c.no_resep AND c.no_rawat = '{$_GET['no_rawat']}'");
					 if (!empty($query_resep) && $query_resep->num_rows > 0) {
					 		while ($data_resep = $query_resep->fetch_array()) {
					 ?>
							 <tr>
									 <td><?php echo $data_resep['3']; ?></td>
									 <td><?php echo $data_resep['1']; ?></td>
									 <td><?php echo $data_resep['2']; ?></td>
							 </tr>
					 <?php
					 		}
				 	 }
					 ?>
					 </tbody>
			 </table>
			</div>
<?php
	break;
}
?>
				</div>
		</div>
</div>
<?php
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
}
?>
