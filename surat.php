<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : dentix.id@gmail.com
* Licence under GPL
***/

$title = 'Pengajuan Cuti Pegawai';
include_once('config.php');
include_once('layout/header.php');
include_once('layout/sidebar.php');

$tgl_reg = date('Ymd', strtotime($date));
$no_pengajuan = fetch_array(query("SELECT max(no_pengajuan) FROM pengajuan_cuti WHERE tanggal='$date'"));
$no_urut_pengajuan = substr($no_pengajuan[0], 10, 3);
$next_no_pengajuan = 'PC'.$tgl_reg.''.sprintf('%03s', ($no_urut_pengajuan + 1));

?>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                          <h2>
                              <?php echo $title; ?> <div class="right pendaftaran"><?php if(!$action){ ?><button class="btn btn-default waves-effect accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapsePendaftaran"></button><?php } ?></div>
                              <small>Periode <?php if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) { echo date("d-m-Y",strtotime($_POST['tgl_awal']))." s/d ".date("d-m-Y",strtotime($_POST['tgl_akhir'])); } else { echo date("d-m-Y",strtotime($date)) . ' s/d ' . date("d-m-Y",strtotime($date));} ?></small>
                          </h2>
                        </div>
                        <div class="panel-group" id="accordion">
                          <div class="panel panel-default" style="border: none !important;">
                            <div id="collapsePendaftaran" class="panel-collapse collapse" style="margin-top:40px;">
                              <div class="panel-body">
                                <form class="form-horizontal">
                                    <div class="row clearfix">
                                        <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                            <label for="email_address_2">No. Pengajuan :</label>
                                        </div>
                                        <div class="col-lg-4 col-md-10 col-sm-8">
                                          <div class="input-group input-group-lg">
                                              <div class="form-line">
                                                  <input type="text" class="form-control" id="no_pengajuan" value="<?php echo $next_no_pengajuan; ?>" placeholder="Nomor Pengajuan">
                                              </div>
                                          </div>
                                        </div>
                                      <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                            <label for="password_2">Tgl Pengajuan :</label>
                                        </div>
                                        <div class="col-lg-4 col-md-10 col-sm-8">
                                          <div class="input-group input-group-lg">
                                              <div class="form-line">
                                                  <input type="text" class="form-control datepicker" id="tanggal_pengajuan" value="<?php echo $date; ?>" placeholder="Tanggal Pengajuan Cuti">
                                              </div>
                                          </div>
                                        </div>
                                    </div>
                                    <div class="row clearfix">
                                        <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                            <label for="email_address_2">Alamat Tujuan :</label>
                                        </div>
                                        <div class="col-lg-10 col-md-10 col-sm-8">
                                          <div class="input-group input-group-lg">
                                              <div class="form-line">
                                                  <textarea class="form-control" id="alamat_tujuan" placeholder="Alamat Tujuan Cuti"></textarea>
                                              </div>
                                          </div>
                                        </div>
                                    </div>
                                    <div class="row clearfix">
                                        <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                            <label for="email_address_2">Tgl. Cuti</label>
                                        </div>
                                        <div class="col-lg-4 col-md-10 col-sm-8">
                                          <div class="input-group input-group-lg">
                                              <div class="form-line">
                                                  <input type="text" class="form-control datepicker" id="tgl_awal" placeholder="Tanggal Awal">
                                              </div>
                                              <span class="input-group-addon">
                                                  S/D
                                              </span>
                                              <div class="form-line m-l-10">
                                                  <input type="text" class="form-control datepicker" id="tgl_akhir" placeholder="Tanggal Akhir">
                                              </div>
                                          </div>
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                            <label for="password_2">Jenis Cuti</label>
                                        </div>
                                        <div class="col-lg-4 col-md-10 col-sm-8">
                                          <div class="input-group input-group-lg">
                                              <div class="form">
                                                  <?php echo enumDropdown('pengajuan_cuti', 'urgensi', '&nbsp;', ''); ?>
                                              </div>
                                          </div>
                                        </div>
                                    </div>
                                    <div class="row clearfix">
                                        <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                            <label for="email_address_2">Alasan Cuti :</label>
                                        </div>
                                        <div class="col-lg-10 col-md-10 col-sm-8">
                                          <div class="input-group input-group-lg">
                                              <div class="form-line">
                                                  <textarea class="form-control" id="alasan_cuti" placeholder="Alasan / Kepentingan Cuti"></textarea>
                                              </div>
                                          </div>
                                        </div>
                                    </div>
                                    <div class="row clearfix">
                                        <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                            <label for="email_address_2">PJ. Terkait :</label>
                                        </div>
                                        <div class="col-lg-10 col-md-10 col-sm-8">
                                          <div class="input-group input-group-lg">
                                              <div class="form-line">
                                                  <input type="hidden" class="form-control" id="nik_pj"><input type="text" class="form-control" id="nama_pj" placeholder="Atasan Langsung">
                                              </div>
                                              <span class="input-group-addon">
                                                  <i class="material-icons" data-toggle="modal" data-target="#atasanModal">attach_file</i>
                                              </span>
                                          </div>
                                        </div>
                                    </div>
                                    <div class="row clearfix" style="margin-bottom:40px;">
                                        <div class="col-lg-12 text-center">
                                            <button type="button" class="btn btn-lg btn-primary m-t-15 m-l-15 waves-effect" id="simpan">SIMPAN</button>
                                            <button type="button" class="btn btn-lg btn-info m-t-15 m-l-15 waves-effect" id="ganti">GANTI</button>
                                            <button type="button" class="btn btn-lg btn-danger m-t-15 m-l-15 waves-effect" id="hapus">HAPUS</button>
                                        </div>
                                    </div>
                                </form>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="body">
                            <table id="datatable" class="table table-bordered table-striped table-hover display wrap" width="100%">
                              <thead>
                                <tr>
                                  <th>Nomor</th>
                                  <th>Pengajuan</th>
                                  <th>Tgl. Awal</th>
                                  <th>Tgl. Akhir</th>
                                  <th>Jenis Cuti</th>
                                  <th>Alamat Tujuan</th>
                                  <th>Jml</th>
                                  <th>Alasan Cuti</th>
                                  <th>PJ. Terkait</th>
                                  <th>Status</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php
                                $sql = "SELECT a.*, b.nama FROM pengajuan_cuti a, pegawai b WHERE a.nik = b.nik AND a.nik_pj = b.nik";
                                if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) {
                                	$sql .= " AND a.tanggal BETWEEN '$_POST[tgl_awal]' AND '$_POST[tgl_akhir]'";
                                } else {
                                  	$sql .= " AND a.tanggal = '$date'";
                                }
                                $query = query($sql);
                                while($row = fetch_array($query)) {
                                ?>
                                <tr class="editcuti"
                                  data-no_pengajuan="<?php echo $row['0']; ?>"
                                  data-tanggal_pengajuan="<?php echo $row['1']; ?>"
                                  data-alamat_tujuan="<?php echo $row['6']; ?>"
                                  data-tgl_awal="<?php echo $row['2']; ?>"
                                  data-tgl_akhir="<?php echo $row['3']; ?>"
                                  data-alasan_cuti="<?php echo $row['8']; ?>"
                                  data-nik_pj="<?php echo $row['9']; ?>"
                                  data-nama_pj="<?php echo $row['11']; ?>"
                                >
                                    <td><?php echo $row['0']; ?></td>
                                    <td><?php echo $row['1']; ?></td>
                                    <td><?php echo $row['2']; ?></td>
                                    <td><?php echo $row['3']; ?></td>
                                    <td><?php echo $row['5']; ?></td>
                                    <td><?php echo $row['6']; ?></td>
                                    <td><?php echo $row['7']; ?></td>
                                    <td><?php echo $row['8']; ?></td>
                                    <td><?php echo $row['11']; ?></td>
                                    <td><?php echo $row['10']; ?></td>
                                </tr>
                              <?php } ?>
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
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="atasanModal" tabindex="-1" role="dialog" aria-labelledby="atasanModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="width:800px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="atasanModalLabel">Database Pegawai</h4>
                </div>
                <div class="modal-body">
                  <table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">
                      <thead>
                          <tr>
                              <th>Kode Kelurahan</th>
                              <th>Nama Kelurahan</th>
                          </tr>
                      </thead>
                      <tbody>
                        <?php
                        $sql = "SELECT nik, nama FROM pegawai";
                        $result = query($sql);
                        while($row = fetch_array($result)) {
                          echo '<tr class="pilihatasan" data-nik_pj="'.$row[0].'" data-nama_pj="'.$row[1].'">';
                          echo '<td>'.$row[0].'</td>';
                          echo '<td>'.$row[1].'</td>';
                          echo '</tr>';
                        }
                        ?>
                      </tbody>
                  </table>
                </div>
            </div>
        </div>
    </div>

<?php
include_once('layout/footer.php');
?>
<script>
$(document).on('click', '.editcuti', function (e) {
  document.getElementById("no_pengajuan").value = $(this).attr('data-no_pengajuan');
  document.getElementById("tanggal_pengajuan").value = $(this).attr('data-tanggal_pengajuan');
  document.getElementById("alamat_tujuan").value = $(this).attr('data-alamat_tujuan');
  document.getElementById("tgl_awal").value = $(this).attr('data-tgl_awal');
  document.getElementById("tgl_akhir").value = $(this).attr('data-tgl_akhir');
  document.getElementById("alasan_cuti").value = $(this).attr('data-alasan_cuti');
  document.getElementById("nik_pj").value = $(this).attr('data-nik_pj');
  document.getElementById("nama_pj").value = $(this).attr('data-nama_pj');
});
$(document).on('click', '.pilihatasan', function (e) {
  document.getElementById("nik_pj").value = $(this).attr('data-nik_pj');
  document.getElementById("nama_pj").value = $(this).attr('data-nama_pj');
  $('#atasanModal').modal('hide');
});
$("#simpan").click(function(){
    var no_pengajuan = document.getElementById("no_pengajuan").value;
    var tanggal_pengajuan = document.getElementById("tanggal_pengajuan").value;
    var alamat_tujuan = document.getElementById("alamat_tujuan").value;
    var tgl_awal = document.getElementById("tgl_awal").value;
    var tgl_akhir = document.getElementById("tgl_akhir").value;
    var urgensi = document.getElementById("urgensi").value;
    var alasan_cuti = document.getElementById("alasan_cuti").value;
    var nik_pj = document.getElementById("nik_pj").value;
    $.ajax({
        url:'includes/cuti.php?p=add',
        method:'POST',
        data:{
            no_pengajuan:no_pengajuan,
            tanggal_pengajuan:tanggal_pengajuan,
            alamat_tujuan:alamat_tujuan,
            tgl_awal:tgl_awal,
            tgl_akhir:tgl_akhir,
            urgensi:urgensi,
            alasan_cuti:alasan_cuti,
            nik_pj:nik_pj
        },
       success:function(data){
            window.location.reload(true)
            //$('#pasien').load("includes/pasien.php");
       }
    });
});
$("#ganti").click(function(){
  var no_pengajuan = document.getElementById("no_pengajuan").value;
  var tanggal_pengajuan = document.getElementById("tanggal_pengajuan").value;
  var alamat_tujuan = document.getElementById("alamat_tujuan").value;
  var tgl_awal = document.getElementById("tgl_awal").value;
  var tgl_akhir = document.getElementById("tgl_akhir").value;
  var urgensi = document.getElementById("urgensi").value;
  var alasan_cuti = document.getElementById("alasan_cuti").value;
  var nik_pj = document.getElementById("nik_pj").value;
    $.ajax({
        url:'includes/cuti.php?p=update',
        method:'POST',
        data:{
          no_pengajuan:no_pengajuan,
          tanggal_pengajuan:tanggal_pengajuan,
          alamat_tujuan:alamat_tujuan,
          tgl_awal:tgl_awal,
          tgl_akhir:tgl_akhir,
          urgensi:urgensi,
          alasan_cuti:alasan_cuti,
          nik_pj:nik_pj
        },
       success:function(data){
            window.location.reload(true)
       }
    });
});
$("#hapus").click(function(){
    var no_pengajuan = document.getElementById("no_pengajuan").value;
    $.ajax({
        url:'includes/cuti.php?p=delete',
        method:'POST',
        data:{
          no_pengajuan:no_pengajuan
        },
        success:function(data){
           window.location.reload(true)
        }
    });
});
</script>
