<?php
function buka_section_body($kepala_judul) {
?>
	<div class="row clearfix">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="card">
							<div class="header">
									<h2>
											<?php echo $kepala_judul; ?>
									</h2>
							</div>
							<div class="body">
<?php
}
function buka_section_body_alt($kepala_judul) {
?>
		<div class="card">
				<div class="header">
						<h2>
								<?php echo $kepala_judul; ?>
						</h2>
				</div>
				<div class="body">
<?php
}
function buka_tabel($judul){
	echo'
		<table class="table table-bordered table-striped table-hover display nowrap" width="100%">
		<thead>
			<tr>
					<th style="width: 20px">No</th>';
	foreach($judul as $jdl){
		echo '<th>'.$jdl.'</th>';
	}

	echo'		<th style="width: 60px">Aksi</th>
			</tr>
		</thead>
		<tbody>';
}

function isi_tabel($no, $data, $link, $id, $edit=true, $hapus=true){
	echo'<tr>
		<td valign="top">'.$no.'</td>';
	foreach($data as $dt){
		echo'<td valign="top">'.$dt.'</td>';
	}
	echo'<td valign="top">';
	if($edit){
		echo'<a href="'.$link.'&show=form&id='.$id.'" class="btn btn-primary btn-xs">
				<i class="material-icons">edit</i>
			</a> ';
	}
	if($hapus){
		echo'<a href="'.$link.'&show=delete&id='.$id.'" class="btn btn-danger btn-xs delete">
				<i class="material-icons">delete</i>
			</a>';
	}
	echo'</td>
		</tr>';
}

function tutup_tabel(){
	echo'		</tbody>
			</table>';
}
function tutup_section_body() {
	echo '		</div>';
	echo '	</div>';
	echo '</div>';
}
function tutup_section_body_alt() {
	echo '</div>';
}
function dataTablesCSS() {
	echo '<link href="assets/plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.min.css" rel="stylesheet">';
	echo '<link href="assets/plugins/jquery-datatable/extensions/responsive/css/responsive.dataTables.min.css" rel="stylesheet">';
}
function dataTablesJS() {
	echo '<script src="assets/plugins/jquery-datatable/jquery.dataTables.js"></script>';
	echo '<script src="assets/plugins/jquery-datatable/skin/bootstrap/js/dataTables.bootstrap.min.js"></script>';
	echo '<script src="assets/plugins/jquery-datatable/extensions/responsive/js/dataTables.responsive.min.js"></script>';
	echo '<script src="assets/plugins/jquery-datatable/extensions/export/dataTables.buttons.min.js"></script>';
	echo '<script src="assets/plugins/jquery-datatable/extensions/export/buttons.flash.min.js"></script>';
	echo '<script src="assets/plugins/jquery-datatable/extensions/export/jszip.min.js"></script>';
	echo '<script src="assets/plugins/jquery-datatable/extensions/export/pdfmake.min.js"></script>';
	echo '<script src="assets/plugins/jquery-datatable/extensions/export/vfs_fonts.js"></script>';
	echo '<script src="assets/plugins/jquery-datatable/extensions/export/buttons.html5.min.js"></script>';
	echo '<script src="assets/plugins/jquery-datatable/extensions/export/buttons.print.min.js"></script>';
}
function dataTablesDisplay() {
?>
		<script>
		$(document).ready(function() {
		  // DataTable initialisation
		  $('.display').DataTable({
		      "bStateSave": true,
		      "processing": true,
		      "responsive": true,
		      "autoWidth": true,
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
		      "paging": true,
		      //"dom": 'Bfrtip',
					"dom": "<'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row clearfix'<'col-sm-12'B>><'row'<'col-sm-5'i><'col-sm-7'p>>",
		      "buttons": [
		        'copy',
		        'csv',
		        'excel',
						'pdf',
						'print'
					]
		    });
		});
		$("a.delete").click(function(e){
		    if(!confirm('Anda yakin ingin menghapus?')){
		        e.preventDefault();
		        return false;
		    }
		    return true;
		});
		</script>
<?php
}
function datePicker() {
?>
	<script>
	$('.datepicker').bootstrapMaterialDatePicker({
	    format: 'YYYY-MM-DD',
	    lang: 'id',
	    clearText: "Bersih",
	    cancelText: "Batal",
	    okText: "Oke",
	    clearButton: true,
	    weekStart: 1,
	    time: false
	});
	</script>
<?php
}
?>
