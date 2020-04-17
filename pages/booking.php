<?php
if(!defined("INDEX")) header('location: ../index.php');

$show = isset($_GET['show']) ? $_GET['show'] : "";
$link = "?module=booking";
if(isset($_POST['validasi'])) {
 // loop data field
 foreach ($_POST['no_rkm_medis'] as $key=>$val) {
  //$no_rkm_medis = (int) $_POST['no_rkm_medis'][$key];
  $no_rkm_medis = $_POST['no_rkm_medis'][$key];
  $get_booking = $mysqli->query("SELECT * FROM booking_registrasi WHERE no_rkm_medis = '{$no_rkm_medis}' AND tanggal_periksa = '{$date}'");
  $data = $get_booking->fetch_array();

  // Cari kesesuaian data
  $get_pasien = $mysqli->query("SELECT * FROM pasien WHERE no_rkm_medis = '{$no_rkm_medis}'")->fetch_array();
  $tgl_reg = date('Y/m/d', strtotime($data['tanggal_periksa']));

  //mencari no rawat terakhir
  $no_rawat_akhir = $mysqli->query("SELECT max(no_rawat) FROM reg_periksa WHERE tgl_registrasi='{$date}'")->fetch_array();
  $no_urut_rawat = substr($no_rawat_akhir[0], 11, 6);
  $no_rawat = $tgl_reg.'/'.sprintf('%06s', ($no_urut_rawat + 1));

  //mencari no reg terakhir
  $no_reg_akhir = $mysqli->query("SELECT max(no_reg) FROM reg_periksa WHERE kd_dokter='{$data['kd_dokter']}' and tgl_registrasi='{$date}'")->fetch_array();
  $no_urut_reg = substr($no_reg_akhir[0], 0, 3);
  $no_reg = sprintf('%03s', ($no_urut_reg + 1));

  $biaya_reg=$mysqli->query("SELECT registrasilama FROM poliklinik WHERE kd_poli='{$data['kd_poli']}'")->fetch_array();

  //menentukan umur sekarang
  list($cY, $cm, $cd) = explode('-', date('Y-m-d'));
  list($Y, $m, $d) = explode('-', date('Y-m-d', strtotime($get_pasien['tgl_lahir'])));
  $umurdaftar = $cY - $Y;

  $cek_status_poli = $mysqli->query("SELECT no_rkm_medis FROM reg_periksa WHERE no_rkm_medis='{$no_rkm_medis}' AND kd_poli='{$data['kd_poli']}'")->fetch_array();
  if($cek_status_poli == ''){
    $status_poli = 'Baru';
  } else {
    $status_poli = 'Lama';
  }

  $insert = $mysqli->query("
        INSERT INTO reg_periksa
        SET no_reg          = '{$data['no_reg']}',
            no_rawat        = '$no_rawat',
            tgl_registrasi  = '$tgl_reg',
            jam_reg         = '$time',
            kd_dokter       = '{$data['kd_dokter']}',
            no_rkm_medis    = '{$no_rkm_medis}',
            kd_poli         = '{$data['kd_poli']}',
            p_jawab         = '{$get_pasien['namakeluarga']}',
            almt_pj         = '{$get_pasien['alamat']}',
            hubunganpj      = '{$get_pasien['keluarga']}',
            biaya_reg       = '{$biaya_reg['0']}',
            stts            = 'Belum',
            stts_daftar     = 'Lama',
            status_lanjut   = 'Ralan',
            kd_pj           = '{$data['kd_pj']}',
            umurdaftar      = '$umurdaftar',
            sttsumur        = 'Th',
            status_bayar    = 'Belum Bayar',
            status_poli     = '$status_poli'
    ");
  if($insert) {
    $mysqli->query("UPDATE booking_registrasi SET status = 'Terdaftar' WHERE no_rkm_medis = '{$no_rkm_medis}' AND tanggal_periksa = '{$date}'");
  }
 }
}
switch($show){
	default:
	if(userroles('role')=="admin"){
		echo '<div class="block-header">';
		echo '	<h2>';
		echo '		BOOKING PENDAFTARAN';
		echo '		<small>Periode ';
		if(isset($_POST['tanggal_periksa'])) {
			echo tgl_indonesia($_POST['tanggal_periksa']);
		} else {
			echo tgl_indonesia($date);
		}
		echo '</small>';
		echo '	</h2>';
		echo '</div>';
	}
	buka_section_body('Tabel Booking');
	$no = 1;
	$sql = "SELECT a.nm_pasien, b.no_rkm_medis, a.alamat, c.png_jawab, d.nm_poli, b.no_reg, b.tanggal_booking, b.jam_booking, b.status, e.nm_dokter FROM pasien a, booking_registrasi b, penjab c, poliklinik d, dokter e WHERE a.no_rkm_medis = b.no_rkm_medis AND b.kd_pj = c.kd_pj AND b.kd_poli = d.kd_poli AND b.kd_dokter = e.kd_dokter";
	if(userroles('role') == 'medis' || userroles('role') == 'paramedis') {
		$sql .= " AND b.kd_poli = userroles('cap')";
	}
	if(isset($_POST['tanggal_periksa'])) {
		$sql .= " AND b.tanggal_periksa = '$_POST[tanggal_periksa]'";
	} else {
			$sql .= " AND b.tanggal_periksa = '$date'";
	}
	$query = $mysqli->query($sql);
	?>
	<form id="frm-booking_datatable" action="" method="POST">
	<table id="booking" class="table table-bordered nowrap" width="100%">
			<thead>
					<tr>
							<th width="30"><input type="checkbox" id="basic_checkbox_1" /><label></label></th>
							<th>Nama Pasien</th>
							<th>No. RM</th>
							<th>No. Reg</th>
							<th>Dokter</th>
							<th>Poliklinik</th>
							<th>Jenis Bayar</th>
							<th>Tgl. Reg</th>
							<th>Jam Reg</th>
							<th>Alamat</th>
							<th>Validasi</th>
					</tr>
			</thead>
			<tbody>
			<?php
			if (!empty($query) && $query->num_rows > 0) {
					while($data = $query->fetch_array()){
							if($data['8'] == 'Terdaftar') {
									$warna = 'bg-info';
							} else if($data['8'] == 'Batal') {
									$warna = 'bg-danger';
							} else {
									$warna = '';
							}
			?>
			<tr class="<?php echo $warna; ?>">
					<td><?php echo $data['1']; ?></td>
					<td><?php echo SUBSTR($data['0'], 0, 15).' ...'; ?></td>
					<td><?php echo $data['1']; ?></td>
					<td><?php echo $data['5']; ?></td>
					<td><?php echo SUBSTR($data['9'], 0, 25).' ...'; ?></td>
					<td><?php echo SUBSTR($data['4'], 0, 25).' ...'; ?></td>
					<td><?php echo $data['3']; ?></td>
					<td><?php echo $data['6']; ?></td>
					<td><?php echo $data['7']; ?></td>
					<td><?php echo SUBSTR($data['2'], 0, 25).' ...'; ?></td>
					<td><?php echo $data['8']; ?></td>
			</tr>
			<?php
							$no++;
					}
			}
			?>
			</tbody>
	</table>
	<hr>
	<p><button type="submit" name="validasi" class="btn btn-lg btn-danger">Validasi</button></p>
	<p>Tekan tombol <b>Validasi</b> untuk persetujuan ke pendaftaran pasien.</p>
	<hr>
	</form>
	<div class="row clearfix">
		<form method="post" action="">
		<div class="col-sm-10">
				<div class="form-group">
						<div class="form-line">
								<input type="text" name="tanggal_periksa" class="datepicker form-control" placeholder="Pilih tanggal kunjungan...">
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
	tutup_section_body();
	break;

}

function addCSS() {
	echo '<link href="assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css" rel="stylesheet" />';
	echo '<link href="assets/plugins/bootstrap-select/css/bootstrap-select.css" rel="stylesheet" />';
	dataTablesCSS();
}
function addJS() {
	global $date_time;
	echo '<script src="assets/plugins/momentjs/moment.js"></script>';
	echo '<script src="assets/plugins/momentjs/locale/id.js"></script>';
  echo '<script src="assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js"></script>';
	dataTablesJS();
	datePicker();
	echo '<script src="assets/plugins/jquery-datatable/extensions/jquery-datatables-checkboxes/dataTables.checkboxes.min.js"></script>';

	?>
	<script>
		$(document).ready(function() {
				var table = $('#booking').DataTable({
			    'bSort':true,
					"bInfo" : true,
					"scrollX": true,
					"processing": true,
					"serverSide": false,
					"responsive": true,
			    'columnDefs': [
			       {
			          'targets': 0,
			          'render': function(data, type, row, meta){
			             if(type === 'display'){
			                data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes filled-in"><label></label></div>';
			             }
			             return data;
			          },
			          'checkboxes': {
			             'selectRow': true,
			             'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes filled-in"><label></label></div>'
			          }
			       }
			    ],
			    'select': 'multi',
			    'order': [[2, 'desc']]
			 });

			  $('#frm-booking_datatable').on('submit', function(e){
			     var form = this;
			     var rows_selected = table.column(0).checkboxes.selected();
			     $.each(rows_selected, function(index, rowId){
			        $(form).append(
 		             $('<input>')
	               .attr('type', 'hidden')
	               .attr('name', 'no_rkm_medis[]')
	               .val(rowId)
			        );
			     });
			  });
		} );
		</script>
	<?php
}

?>
