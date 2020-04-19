<?php
if(!defined("INDEX")) header('location: ../index.php');

$show = isset($_GET['show']) ? $_GET['show'] : "";
$link = "?module=pasien";
switch($show){
	default:
	if(userroles('role')=="admin"){
		echo '<div class="block-header">';
		echo '	<h2>';
		echo '		<a href="'.$link.'&show=form" class="btn btn-primary btn-sm pull-right top-button">';
		echo '			<i class="glyphicon glyphicon-plus-sign"></i> Tambah';
		echo '		</a>';
		echo '		DATA PASIEN';
		echo '		<small>Periode '.tgl_indonesia($date).'</small>';
		echo '	</h2>';
		echo '</div>';
	}
	buka_section_body('Tabel Pasien');
	?>
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
	<?php
	tutup_section_body();
	break;
	case "form":
		display_message();
		if(isset($_GET['id'])){
			$query 	= $mysqli->query("SELECT * FROM pasien WHERE no_rkm_medis='$_GET[id]'");
			$data	= $query->fetch_array();
			$aksi 	= "Edit";
		}else{
			$data = array(
				"no_rkm_medis" 				=>setNoRM(),
				"nm_pasien" 					=>"",
				"no_ktp" 							=>"",
				"jk" 									=>"",
				"tmp_lahir" 					=>"",
				"tgl_lahir" 					=>"",
				"nm_ibu" 							=>"",
				"alamat" 							=>"",
				"gol_darah" 					=>"",
				"pekerjaan" 					=>"",
				"stts_nikah" 					=>"",
				"agama" 							=>"",
				"tgl_daftar" 					=>"",
				"no_tlp" 							=>"",
				"umur" 								=>"",
				"pnd" 								=>"",
				"keluarga" 						=>"",
				"namakeluarga" 				=>"",
				"kd_pj" 							=>"",
				"no_peserta" 					=>"",
				"kd_kel" 							=>"",
				"kd_kec" 							=>"",
				"kd_kab" 							=>"",
				"pekerjaanpj" 				=>"",
				"alamatpj" 						=>"",
				"cacat_fisik" 				=>"",
				"email" 							=>"",
				"nip" 								=>"",
				"kd_prop" 						=>"",
				"kelurahanpj" 				=>"",
				"kecamatanpj" 				=>"",
				"kabupatenpj" 				=>"",
				"propinsipj" 					=>"",
				"suku_bangsa" 				=>"",
				"bahasa_pasien" 			=>"",
				"perusahaan_pasien"		=>""
			);
			$aksi 	= "Tambah";
		}
		?>
		<div class="row">
				<div class="col-lg-12">
						<div class="block-header">
						    <p class="col-orange font-24 font-uppercase">TAMBAH PASIEN</p>
						</div>
							<div class="row">
		<?php

		buka_form_alt($link, $data['no_rkm_medis'], strtolower($aksi));
		include ("pages/views/pasien.form.php");
		tutup_form_alt($link);
		?>
						</div>
				</div>
		</div>
		<?php
	break;
	//Menyisipkan atau mengedit data di database
	case "action":
		$next_no_rm = setNoRM();
		$umur = setUmur($_POST['tgl_lahir']);
		if($_POST['aksi'] == "tambah"){
			$mysqli->query("INSERT INTO pasien SET
				no_rkm_medis 				= '$next_no_rm',
				nm_pasien 					= '$_POST[nm_pasien]',
				no_ktp 							= '$_POST[no_ktp]',
				jk 									= '$_POST[jk]',
				tmp_lahir 					= '$_POST[tmp_lahir]',
				tgl_lahir 					= '$_POST[tgl_lahir]',
				nm_ibu 							= '$_POST[nm_ibu]',
				alamat 							= '$_POST[alamat]',
				gol_darah 					= '$_POST[gol_darah]',
				pekerjaan 					= '$_POST[pekerjaan]',
				stts_nikah 					= '$_POST[stts_nikah]',
				agama 							= '$_POST[agama]',
				tgl_daftar 					= '$_POST[tgl_daftar]',
				no_tlp 							= '$_POST[no_tlp]',
				umur 								= '$umur',
				pnd 								= '$_POST[pnd]',
				keluarga 						= '$_POST[keluarga]',
				namakeluarga 				= '$_POST[namakeluarga]',
				kd_pj 							= '$_POST[kd_pj]',
				no_peserta 					= '$_POST[no_peserta]',
				kd_kel 							= '$_POST[kd_kel]',
				kd_kec 							= '$_POST[kd_kec]',
				kd_kab 							= '$_POST[kd_kab]',
				pekerjaanpj 				= '$_POST[pekerjaanpj]',
				alamatpj 						= '$_POST[alamatpj]',
				cacat_fisik 				= '$_POST[cacat_fisik]',
				email 							= '$_POST[email]',
				nip 								= '$_POST[nip]',
				kd_prop 						= '$_POST[kd_prop]',
				kelurahanpj 				= '$_POST[kelurahanpj]',
				kecamatanpj 				= '$_POST[kecamatanpj]',
				kabupatenpj 				= '$_POST[kabupatenpj]',
				propinsipj 					= '$_POST[propinsipj]',
				suku_bangsa 				= '$_POST[suku_bangsa]',
				bahasa_pasien 			= '$_POST[bahasa_pasien]',
				perusahaan_pasien		= '$_POST[perusahaan_pasien]'
			");
			set_message('Data pasien berhasil ditambah.');
		}elseif($_POST['aksi'] == "edit"){
			$mysqli->query("UPDATE pasien SET
				nm_pasien 					= '$_POST[nm_pasien]',
				no_ktp 							= '$_POST[no_ktp]',
				jk 									= '$_POST[jk]',
				tmp_lahir 					= '$_POST[tmp_lahir]',
				tgl_lahir 					= '$_POST[tgl_lahir]',
				nm_ibu 							= '$_POST[nm_ibu]',
				alamat 							= '$_POST[alamat]',
				gol_darah 					= '$_POST[gol_darah]',
				pekerjaan 					= '$_POST[pekerjaan]',
				stts_nikah 					= '$_POST[stts_nikah]',
				agama 							= '$_POST[agama]',
				tgl_daftar 					= '$_POST[tgl_daftar]',
				no_tlp 							= '$_POST[no_tlp]',
				umur 								= $umur,
				pnd 								= '$_POST[pnd]',
				keluarga 						= '$_POST[keluarga]',
				namakeluarga 				= '$_POST[namakeluarga]',
				kd_pj 							= '$_POST[kd_pj]',
				no_peserta 					= '$_POST[no_peserta]',
				kd_kel 							= '$_POST[kd_kel]',
				kd_kec 							= '$_POST[kd_kec]',
				kd_kab 							= '$_POST[kd_kab]',
				pekerjaanpj 				= '$_POST[pekerjaanpj]',
				alamatpj 						= '$_POST[alamatpj]',
				cacat_fisik 				= '$_POST[cacat_fisik]',
				email 							= '$_POST[email]',
				nip 								= '$_POST[nip]',
				kd_prop 						= '$_POST[kd_prop]',
				kelurahanpj 				= '$_POST[kelurahanpj]',
				kecamatanpj 				= '$_POST[kecamatanpj]',
				kabupatenpj 				= '$_POST[kabupatenpj]',
				propinsipj 					= '$_POST[propinsipj]',
				suku_bangsa 				= '$_POST[suku_bangsa]',
				bahasa_pasien 			= '$_POST[bahasa_pasien]',
				perusahaan_pasien		= '$_POST[perusahaan_pasien]'
			WHERE no_rkm_medis = '$_POST[id]'");
			set_message('Data pasien berhasil diubah.');
		}
		header('location:'.$link.'&show=form&id='.$_POST[id]);
	break;

	//Menghapus data di database
	case "delete":
	  $mysqli->query("DELETE FROM pasien WHERE no_rkm_medis='$_GET[id]'");
		header('location:'.$link);
	break;
}
function addCSS() {
	echo '<link href="assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css" rel="stylesheet" />';
	echo '<link href="assets/plugins/bootstrap-select/css/bootstrap-select.css" rel="stylesheet" />';
	dataTablesCSS();
}
function addJS() {
	echo '<script src="assets/plugins/momentjs/moment.js"></script>';
	echo '<script src="assets/plugins/momentjs/locale/id.js"></script>';
  echo '<script src="assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js"></script>';
	echo '<script src="assets/plugins/bootstrap-select/js/bootstrap-select.js"></script>';
	dataTablesJS();
	dataTablesDisplay();
	datePicker();
	?>
	<script>
    $('#pasien').DataTable( {
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
				"ajax": "ajax/pasien.php"
    } );
		var data_kelurahan = $('#kelurahan').DataTable( {
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
				"ajax": "ajax/wilayah.php?page=kelurahan",
				"createdRow": function( row, data, index ) {
						$(row).addClass('pilihkelurahan');
						$(row).attr('data-kdkel', data[0]);
						$(row).attr('data-nmkel', data[1]);
				}
    } );
		$(document).on('click', '.pilihpropinsi', function (e) {
				$("#kd_prop")[0].value = $(this).attr('data-kdprop');
				$("#nm_prop")[0].value = $(this).attr('data-nmprop');
				$('#propinsiModal').modal('hide');
		});
		$(document).on('click', '.pilihkabupaten', function (e) {
				$("#kd_kab")[0].value = $(this).attr('data-kdkab');
				$("#nm_kab")[0].value = $(this).attr('data-nmkab');
				$('#kabupatenModal').modal('hide');
		});
		$(document).on('click', '.pilihkecamatan', function (e) {
				$("#kd_kec")[0].value = $(this).attr('data-kdkec');
				$("#nm_kec")[0].value = $(this).attr('data-nmkec');
				$('#kecamatanModal').modal('hide');
		});
		$(document).on('click', '.pilihkelurahan', function (e) {
				$("#kd_kel")[0].value = $(this).attr('data-kdkel');
				$("#nm_kel")[0].value = $(this).attr('data-nmkel');
				$('#kelurahanModal').modal('hide');
		});
		$("#simpan-kelurahan").click(function(){
        var data = $('.form-data').serialize();
        var nama_kelurahan = $("#nama_kelurahan")[0].value;
          if (nama_kelurahan=="") {
          	$("#err_nama_kelurahan")[0].innerHTML = "Nama Kelurahan Harus Diisi";
          } else {
          	$("#err_nama_kelurahan")[0].innerHTML = "";
          }
          if (nama_kelurahan!="") {
          	 $.ajax({
  	            type: 'POST',
  	            url: "ajax/wilayah.php?page=add-kelurahan",
  	            data: {
                  nama_kelurahan:nama_kelurahan
                },
  	            success: function(data) {
  	                $(':input').val('');
  	            }
	           });
          }
    });
		$("#copy_alamat").click(function(){
				$("#alamatpj")[0].value = $("#alamat").val();
        $("#propinsipj")[0].value = $("#nm_prop").val();
				$("#kabupatenpj")[0].value = $("#nm_kab").val();
				$("#kecamatanpj")[0].value = $("#nm_kec").val();
				$("#kelurahanpj")[0].value = $("#nm_kel").val();
    });
		</script>
	<?php
}
?>
