<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Booking Pendaftaran';
include_once('config.php');
include_once('layout/header.php');
include_once('layout/sidebar.php');

if(isset($_POST['validasi'])) {
 // loop data field
 foreach ($_POST['no_rkm_medis'] as $key=>$val) {
  //$no_rkm_medis = (int) $_POST['no_rkm_medis'][$key];
  $no_rkm_medis = $_POST['no_rkm_medis'][$key];
  $get_booking = query("SELECT * FROM booking_registrasi WHERE no_rkm_medis = '{$no_rkm_medis}' AND tanggal_periksa = '{$date}'");
  $data = fetch_assoc($get_booking);

  // Cari kesesuaian data
  $get_pasien = fetch_array(query("SELECT * FROM pasien WHERE no_rkm_medis = '{$no_rkm_medis}'"));
  $tgl_reg = date('Y/m/d', strtotime($data['tanggal_periksa']));

  //mencari no rawat terakhir
  $no_rawat_akhir = fetch_array(query("SELECT max(no_rawat) FROM reg_periksa WHERE tgl_registrasi='{$date}'"));
  $no_urut_rawat = substr($no_rawat_akhir[0], 11, 6);
  $no_rawat = $tgl_reg.'/'.sprintf('%06s', ($no_urut_rawat + 1));

  //mencari no reg terakhir
  $no_reg_akhir = fetch_array(query("SELECT max(no_reg) FROM reg_periksa WHERE kd_dokter='{$data['kd_dokter']}' and tgl_registrasi='{$date}'"));
  $no_urut_reg = substr($no_reg_akhir[0], 0, 3);
  $no_reg = sprintf('%03s', ($no_urut_reg + 1));

  $biaya_reg=fetch_array(query("SELECT registrasilama FROM poliklinik WHERE kd_poli='{$data['kd_poli']}'"));

  //menentukan umur sekarang
  list($cY, $cm, $cd) = explode('-', date('Y-m-d'));
  list($Y, $m, $d) = explode('-', date('Y-m-d', strtotime($get_pasien['tgl_lahir'])));
  $umurdaftar = $cY - $Y;

  $cek_status_poli = fetch_array(query("SELECT no_rkm_medis FROM reg_periksa WHERE no_rkm_medis='{$no_rkm_medis}' AND kd_poli='{$data['kd_poli']}'"));
  if($cek_status_poli == ''){
    $status_poli = 'Baru';
  } else {
    $status_poli = 'Lama';
  }

  $insert = query("
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
    query("UPDATE booking_registrasi SET status = 'Terdaftar' WHERE no_rkm_medis = '{$no_rkm_medis}' AND tanggal_periksa = '{$date}'");
  }
 }
}
?>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                <?php echo $title; ?><?php echo date('Y-m-d'); ?>

                                <small><?php if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) { echo "Periode ".date("d-m-Y",strtotime($_POST['tgl_awal']))." s/d ".date("d-m-Y",strtotime($_POST['tgl_akhir'])); } ?></small>
                            </h2>
                        </div>
                        <div class="body table-responsive">
                            <form id="frm-booking_datatable" action="" method="POST">
                            <table id="booking_datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox"  id="basic_checkbox_1" /><label></label></th>
                                        <th>Nama Pasien</th>
                                        <th>No. RM</th>
                                        <th>No. Reg</th>
                                        <th>Tgl. Reg</th>
                                        <th>Jam Reg</th>
                                        <th>Alamat</th>
                                        <th>Jenis Bayar</th>
                                        <th>Poliklinik</th>
                                        <th>Validasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $sql = "SELECT a.nm_pasien, b.no_rkm_medis, a.alamat, c.png_jawab, d.nm_poli, b.no_reg, b.tanggal_booking, b.jam_booking, b.status FROM pasien a, booking_registrasi b, penjab c, poliklinik d WHERE a.no_rkm_medis = b.no_rkm_medis AND b.kd_pj = c.kd_pj AND b.kd_poli = d.kd_poli";
                                if($role == 'Medis' || $role == 'Paramedis') {
                                  $sql .= " AND b.kd_poli = '$jenis_poli'";
                                }
                                if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) {
                                	$sql .= " AND b.tanggal_periksa BETWEEN '$_POST[tgl_awal]' AND '$_POST[tgl_akhir]'";
                                } else {
                                  	$sql .= " AND b.tanggal_periksa = '$date'";
                                }
                                $query = query($sql);
                                while($row = fetch_array($query)) {
                                ?>
                                    <tr>
                                        <td><?php echo $row['1']; ?></td>
                                        <td><?php echo SUBSTR($row['0'], 0, 15).' ...'; ?></td>
                                        <td><?php echo $row['1']; ?></td>
                                        <td><?php echo $row['5']; ?></td>
                                        <td><?php echo $row['6']; ?></td>
                                        <td><?php echo $row['7']; ?></td>
                                        <td><?php echo $row['2']; ?></td>
                                        <td><?php echo $row['3']; ?></td>
                                        <td><?php echo $row['4']; ?></td>
                                        <td><?php echo $row['8']; ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                                </tbody>
                            </table>

                            <hr>
                            <p><button type="submit" name="validasi" class="btn btn-lg btn-danger">Validasi</button></p>
                            <p>Press <b>Validasi</b> to validate Pasien Booking to Registration.</p>
                            <hr>

                            <!--
                            <p><b>Selected rows data:</b></p>
                            <pre id="booking_datatable-console-rows"></pre>

                            <p><b>Form data as submitted to the server:</b></p>
                            <pre id="booking_datatable-console-form"></pre>
                            -->
                            </form>

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
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
include_once('layout/footer.php');
?>
<script type="text/javascript">


$(document).ready(function() {
 var table = $('#booking_datatable').DataTable({
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
   'order': [[1, 'asc']]
});

 // Handle form submission event
 $('#frm-booking_datatable').on('submit', function(e){
    var form = this;

    var rows_selected = table.column(0).checkboxes.selected();

    // Iterate over all selected checkboxes
    $.each(rows_selected, function(index, rowId){
       // Create a hidden element
       $(form).append(
           $('<input>')
              .attr('type', 'hidden')
              .attr('name', 'no_rkm_medis[]')
              .val(rowId)
       );
    });

    // FOR DEMONSTRATION ONLY
    // The code below is not needed in production

    // Output form data to a console
    //$('#booking_datatable-console-rows').text(rows_selected.join(","));

    // Output form data to a console
    //$('#booking_datatable-console-form').text($(form).serialize());

    // Remove added elements
    //$('input[name="no_rkm_medis\[\]"]', form).remove();

    // Prevent actual form submission
    //e.preventDefault();
 });
});

</script>
