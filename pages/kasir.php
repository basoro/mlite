<?php
if(!defined("INDEX")) header('location: ../index.php');

if($_SERVER['REQUEST_METHOD'] == "POST") {
  if(isset($_POST['nama_biaya']) && $_POST['nama_biaya'] <> '') {
    $mysqli->query("INSERT INTO tambahan_biaya SET no_rawat = '{$_GET['no_rawat']}', nama_biaya = '{$_POST['nama_biaya']}', besar_biaya = '{$_POST['besar_biaya']}'");
  }
  if(isset($_POST['kd_jenis_prw']) && $_POST['kd_jenis_prw'] <> '') {
    $mysqli->query("INSERT INTO tambahan_biaya SET no_rawat = '{$_GET['no_rawat']}', nama_biaya = '{$_POST['nama_biaya']}', besar_biaya = '{$_POST['besar_biaya']}'");
  }
}

$show = isset($_GET['show']) ? $_GET['show'] : "";
$link = "?module=kasir";
?>
<div class="block-header">
		<h2>
				KASIR
				<small>Periode <?php if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) { echo tgl_indonesia($_POST['tgl_awal'])." s/d ".tgl_indonesia($_POST['tgl_akhir']); } else { echo tgl_indonesia($date) . ' s/d ' . tgl_indonesia($date);} ?></small>
		</h2>
</div>

<div class="row clearfix">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="card">
						<div class="header">
								<h2>
										Tagihan Pembayaran
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
														<th>Alamat</th>
														<th>Jenis Bayar</th>
														<th>Status Bayar</th>
												</tr>
										</thead>
										<tbody>
										<?php
										$sql = "SELECT a.nm_pasien, b.no_rkm_medis, a.alamat, c.png_jawab, b.stts, b.status_bayar, b.no_rawat FROM pasien a, reg_periksa b, penjab c WHERE a.no_rkm_medis = b.no_rkm_medis AND b.kd_pj = c.kd_pj";
										if(isset($_POST['status_lanjut']) && $_POST['status_lanjut'] == 'Ralan') {
											$sql .= " AND b.status_lanjut = 'Ralan'";
										}
										if(isset($_POST['status_lanjut']) && $_POST['status_lanjut'] == 'Ranap') {
											$sql .= " AND b.status_lanjut = 'Ranap'";
										}
										if(isset($_POST['tgl_awal']) && $_POST['tgl_awal'] !=="" && isset($_POST['tgl_akhir']) && $_POST['tgl_akhir'] !=="") {
											$sql .= " AND b.tgl_registrasi BETWEEN '$_POST[tgl_awal]' AND '$_POST[tgl_akhir]'";
										} else {
												$sql .= " AND b.tgl_registrasi = '$date'";
										}
										$query = $mysqli->query($sql);
                    $no = 1;
										while($row = $query->fetch_array()) {
										?>
												<tr>
                            <td><?php echo $no; ?></td>
														<td><?php echo SUBSTR($row['0'],0,20); ?></td>
														<td><a href="<?php echo $link; ?>&show=view&no_rawat=<?php echo $row['6']; ?>"><?php echo $row['1']; ?></a></td>
														<td><?php echo $row['2']; ?></td>
														<td><?php echo $row['3']; ?></td>
														<td><?php echo $row['5']; ?></td>
												</tr>
										<?php
                      $no++;
										}
										?>
										</tbody>
								</table>
								<div class="row clearfix">
										<form method="post" action="">
										<div class="col-sm-4">
												<div class="form-group">
														<div class="form-line">
																<input type="text" name="tgl_awal" class="datepicker form-control" placeholder="Pilih tanggal awal...">
														</div>
												</div>
										</div>
										<div class="col-sm-4">
												<div class="form-group">
														<div class="form-line">
																<input type="text" name="tgl_akhir" class="datepicker form-control" placeholder="Pilih tanggal akhir...">
														</div>
												</div>
										</div>
										<div class="col-sm-2">
												<div class="form-group">
														<div class="form-line">
															<select name="status_lanjut" class="form-control show-tick">
																	<option>Semua</option>
																	<option value="Ralan">Rawat Jalan</option>
																	<option value="Ranap">Rawat Inap</option>
															</select>
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
				</div>
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
<table id="kasir" class="table table-bordered table-hover nowrap" width="100%">
		<thead>
				<tr>
						<th>Nama Item</th>
						<th>Jumlah</th>
						<th>Biaya</th>
						<th>Total Biaya</th>
				</tr>
		</thead>
		<tbody>
			<tr>
					<th>Tindakan</th><th></th><th></th><th><button class="btn bg-orange waves-effect pull-right" data-toggle="modal" data-target="#tindakanModal">+</button></th>
			</tr>
		<?php
		$query_tindakan = $mysqli->query("SELECT a.kd_jenis_prw, a.tgl_perawatan, a.tarif_tindakandr, b.nm_perawatan  FROM rawat_jl_dr a, jns_perawatan b WHERE a.kd_jenis_prw = b.kd_jenis_prw AND a.no_rawat = '{$_GET['no_rawat']}'");
		$total_tindakan = 0;
		while ($data_tindakan = $query_tindakan->fetch_array()) {
				$total_tindakan += $data_tindakan['2'];
		?>
				<tr>
						<td><?php echo $data_tindakan['3']; ?></td>
						<td></td>
						<td>Rp. <span class="pull-right"><?php echo number_format($data_tindakan['2'],2,',','.'); ?></span></td>
						<td>Rp. <span class="pull-right"><?php echo number_format($data_tindakan['2'],2,',','.'); ?></span></td>
				</tr>
		<?php
		}
		?>
		<tr>
				<td>Sub total tindakan</td><td></td><td></td><td>Rp. <span class="pull-right"><?php echo number_format($total_tindakan,2,',','.'); ?></span></td>
		</tr>
		<tr>
				<th>Obat</th><th></th><th></th><th><button class="btn bg-orange waves-effect pull-right" data-toggle="modal" data-target="#obatModal">+</button></th>
		</tr>
		 <?php
		 $query_resep = $mysqli->query("SELECT a.kode_brng, a.jml, a.aturan_pakai, b.nama_brng, a.no_resep, b.jualbebas FROM resep_dokter a, databarang b, resep_obat c WHERE a.kode_brng = b.kode_brng AND a.no_resep = c.no_resep AND c.no_rawat = '{$_GET['no_rawat']}'");
		 $total_obat = 0;
		 while ($data_resep = $query_resep->fetch_array()) {
			 $total_obat += $data_resep['1']*$data_resep['5'];
		 ?>
				 <tr>
						 <td><?php echo $data_resep['3']; ?></td>
						 <td><?php echo $data_resep['1']; ?></td>
						 <td>Rp. <span class="pull-right"><?php echo number_format($data_resep['5'],2,',','.'); ?></span></td>
						 <td>Rp. <span class="pull-right"><?php echo number_format($data_resep['1']*$data_resep['5'],2,',','.'); ?></span></td>
				 </tr>
		 <?php
		 }
		 ?>
		 <tr>
				 <td>Sub total obat</td><td></td><td></td><td>Rp. <span class="pull-right"><?php echo number_format($total_obat,2,',','.'); ?></span></td>
		 </tr>
		 <tr>
				 <th>Tambahan Biaya</th><th></th><th></th><th><button class="btn bg-orange waves-effect pull-right" data-toggle="modal" data-target="#tambahanModal">+</button></th>
		 </tr>
			<?php
			$query_tambahan_biaya = $mysqli->query("SELECT * FROM tambahan_biaya WHERE no_rawat = '{$_GET['no_rawat']}'");
			$total_tambahan = 0;
			while ($data_tambahan_biaya = $query_tambahan_biaya->fetch_array()) {
				$total_tambahan += $data_tambahan_biaya['2'];
			?>
					<tr>
							<td><?php echo $data_tambahan_biaya['1']; ?> <a class="btn btn-danger waves-effect" href="<?php echo $link; ?>&show=delete&action=delete_biaya&no_rawat=<?php echo $_GET['no_rawat']; ?>&nama_biaya=<?php echo $data_tambahan_biaya['1']; ?>">x</a></td>
							<td>-</td>
							<td>Rp. <span class="pull-right"><?php echo number_format($data_tambahan_biaya['2'],2,',','.'); ?></span></td>
							<td>Rp. <span class="pull-right"><?php echo number_format($data_tambahan_biaya['2'],2,',','.'); ?></span></td>
					</tr>
			<?php
			}
			?>
			<tr>
					<th>Total</th><th></th><th></th><th>Rp. <span class="pull-right"><?php echo number_format($total_tindakan+$total_obat+$total_tambahan,2,',','.'); ?></span></th>
			</tr>
		</tbody>
 </table>
</div>
<div class="body text-center">
  <a href="pdf/billing.php?no_rawat=<?php echo $_GET['no_rawat']; ?>" class="btn bg-green btn-lg m-r-5 m-l-5 waves-effect" target="_blank">BAYAR</a>
  <a href="pdf/billing.php?no_rawat=<?php echo $_GET['no_rawat']; ?>" class="btn bg-blue btn-lg m-r-5 m-l-5 waves-effect" target="_blank">SIMPAN</a>
  <a href="pdf/billing.php?no_rawat=<?php echo $_GET['no_rawat']; ?>" class="btn bg-red btn-lg m-r-5 m-l-5 waves-effect" target="_blank">HAPUS</a>
  <a href="pdf/billing.php?no_rawat=<?php echo $_GET['no_rawat']; ?>" class="btn bg-indigo btn-lg m-r-5 m-l-5 waves-effect hidden-xs" target="_blank">CETAK</a>
</div>

<div class="modal fade" id="tindakanModal" tabindex="-1" role="dialog" aria-labelledby="tindakanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        		<div class="modal-header">
        				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        				<h4 class="modal-title" id="tindakanModalLabel">Biaya Tindakan</h4>
        		</div>
        		<div class="modal-body">
                <form class="form-horizontal" method="POST" action="">
                    <div class="row clearfix">
                        <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                            <label for="email_address_2">Tindakan:</label>
                        </div>
                        <div class="col-lg-4 col-md-10 col-sm-8">
                          <div class="input-group input-group-lg">
                              <div class="form-line">
                                  <input type="hidden" name="kd_jenis_prw" id="kd_jenis_prw">
                                  <input type="text" class="form-control" name="nm_perawatan" id="nm_perawatan" placeholder="Nama Tindakan">
                              </div>
                          </div>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                            <label for="password_2">Biaya :</label>
                        </div>
                        <div class="col-lg-4 col-md-10 col-sm-8">
                          <div class="input-group input-group-lg">
                              <div class="form-line">
                                  <input type="text" class="form-control" name="biaya_obat" id="biaya_obat" placeholder="Besar biaya">
                              </div>
                          </div>
                        </div>
                    </div>
                    <div class="row clearfix" style="margin-bottom:40px;">
                        <div class="col-lg-12 text-center">
                            <button type="submit" class="btn btn-lg btn-primary m-t-15 m-l-15 waves-effect" id="simpan_obat">SIMPAN</button>
                        </div>
                    </div>
                </form>
        				<table id="jns_perawatan" class="table table-bordered table-striped table-hover nowrap" width="100%">
        						<thead>
        								<tr>
        										<th>Nama Tindakan</th>
        										<th>Besar Biaya</th>
                            <th>Nama Tindakan</th>
                            <th>Nama Tindakan</th>
                            <th>Nama Tindakan</th>
                            <th>Nama Tindakan</th>
                            <th>Nama Tindakan</th>
                            <th>Nama Tindakan</th>
                            <th>Nama Tindakan</th>
                            <th>Nama Tindakan</th>
                            <th>Nama Tindakan</th>
                            <th>Nama Tindakan</th>
                            <th>Nama Tindakan</th>
                            <th>Nama Tindakan</th>
                            <th>Nama Tindakan</th>
        								</tr>
        						</thead>
        						<tbody>
        						</tbody>
        				</table>
        		</div>
        </div>
    </div>
</div>

<div class="modal fade" id="obatModal" tabindex="-1" role="dialog" aria-labelledby="obatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        		<div class="modal-header">
        				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        				<h4 class="modal-title" id="obatModalLabel">Biaya Obat</h4>
        		</div>
        		<div class="modal-body">
        				<form class="form-horizontal" method="POST" action="">
        						<div class="row clearfix">
        								<div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
        										<label for="email_address_2">Obat:</label>
        								</div>
        								<div class="col-lg-4 col-md-10 col-sm-8">
        									<div class="input-group input-group-lg">
        											<div class="form-line">
        													<input type="text" class="form-control" name="nama_obat" id="nama_obat" placeholder="Item biaya">
        											</div>
        									</div>
        								</div>
        								<div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
        										<label for="password_2">Biaya :</label>
        								</div>
        								<div class="col-lg-4 col-md-10 col-sm-8">
        									<div class="input-group input-group-lg">
        											<div class="form-line">
        													<input type="text" class="form-control" name="biaya_obat" id="biaya_obat" placeholder="Besar biaya">
        											</div>
        									</div>
        								</div>
        						</div>
        						<div class="row clearfix" style="margin-bottom:40px;">
        								<div class="col-lg-12 text-center">
        										<button type="submit" class="btn btn-lg btn-primary m-t-15 m-l-15 waves-effect" id="simpan_obat">SIMPAN</button>
        								</div>
        						</div>
        				</form>
        				<table class="table table-bordered table-striped table-hover display nowrap" width="100%">
        						<thead>
        								<tr>
        										<th>Nama Obat</th>
        										<th>Besar Biaya</th>
        								</tr>
        						</thead>
        						<tbody>
        						</tbody>
        				</table>
        		</div>
        </div>
    </div>
</div>

<div class="modal fade" id="tambahanModal" tabindex="-1" role="dialog" aria-labelledby="tambahanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        		<div class="modal-header">
        				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        				<h4 class="modal-title" id="tambahanModalLabel">Tambahan Biaya</h4>
        		</div>
        		<div class="modal-body">
        				<form class="form-horizontal" method="POST" action="">
        						<div class="row clearfix">
        								<div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
        										<label for="email_address_2">Nama :</label>
        								</div>
        								<div class="col-lg-4 col-md-10 col-sm-8">
        									<div class="input-group input-group-lg">
        											<div class="form-line">
        													<input type="text" class="form-control" name="nama_biaya" id="nama_biaya" placeholder="Item biaya">
        											</div>
        									</div>
        								</div>
        								<div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
        										<label for="password_2">Biaya :</label>
        								</div>
        								<div class="col-lg-4 col-md-10 col-sm-8">
        									<div class="input-group input-group-lg">
        											<div class="form-line">
        													<input type="text" class="form-control" name="besar_biaya" id="besar_biaya" placeholder="Besar biaya">
        											</div>
        									</div>
        								</div>
        						</div>
        						<div class="row clearfix" style="margin-bottom:40px;">
        								<div class="col-lg-12 text-center">
        										<button type="submit" class="btn btn-lg btn-primary m-t-15 m-l-15 waves-effect" id="simpan_biaya">SIMPAN</button>
        								</div>
        						</div>
        				</form>
        				<table class="table table-bordered table-striped table-hover display nowrap" width="100%">
        						<thead>
        								<tr>
        										<th>Nama Biaya</th>
        										<th>Besar Biaya</th>
        								</tr>
        						</thead>
        						<tbody>
        							<?php
        							$all_tambahan_biaya = $mysqli->query("SELECT * FROM tambahan_biaya");
        							while ($data_tambahan_biaya = $all_tambahan_biaya->fetch_array()) {
        							?>
        									<tr class="tambahan_biaya" data-namabiaya="<?php echo $data_tambahan_biaya['1']; ?>" data-besarbiaya="<?php echo $data_tambahan_biaya['2']; ?>">
        											<td><?php echo $data_tambahan_biaya['1']; ?></td>
        											<td>Rp. <?php echo number_format($data_tambahan_biaya['2'],2,',','.'); ?></td>
        									</tr>
        							<?php
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
  case "delete":
    $action = isset($_GET['action'])?$_GET['action']:null;
    if($action == "delete_biaya"){
      $hapus = "DELETE FROM tambahan_biaya WHERE no_rawat='{$_REQUEST['no_rawat']}' AND nama_biaya = '{$_REQUEST['nama_biaya']}'";
      $hasil = $mysqli->query($hapus);
      if (($hasil)) {
          redirect("{$link}&show=view&no_rawat={$_REQUEST['no_rawat']}");
      }
    }
  break;

}
?>
<?php
function addCSS() {
  echo '<link href="assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css" rel="stylesheet" />';
	dataTablesCSS();
}
function addJS() {
  echo '<script src="assets/plugins/momentjs/moment.js"></script>';
	echo '<script src="assets/plugins/momentjs/locale/id.js"></script>';
  echo '<script src="assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js"></script>';
	dataTablesJS();
  dataTablesDisplay();
	datePicker();
?>
<script>
$(document).on('click', '.tambahan_biaya', function (e) {
    document.getElementById("nama_biaya").value = $(this).attr('data-namabiaya');
    document.getElementById("besar_biaya").value = $(this).attr('data-besarbiaya');
});
//Menerapkan plugin dataTable
$("#kasir").dataTable({
  "bStateSave": true,
  "processing": true,
  "responsive": true,
  "autoWidth": true,
	"ordering": false,
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
  //dom: 'Bfrtip',
  dom: "<'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row clearfix'<'col-sm-12'B>><'row'<'col-sm-5'i><'col-sm-7'p>>",
  buttons: [
      'copy', 'csv', 'excel', 'pdf', 'print'
  ]
});
$('#jns_perawatan').DataTable( {
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
    "order": [[ 1, 'asc' ]],
    "ajax": "ajax/jns_perawatan.php",
    dom: "<'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row clearfix'<'col-sm-12'B>><'row'<'col-sm-5'i><'col-sm-7'p>>",
    buttons: [
        'copy', 'csv', 'excel', 'pdf', 'print'
    ]
} );
</script>
<?php
}
?>
