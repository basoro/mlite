<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : dentix.id@gmail.com
* Licence under GPL
***/

$title = 'Pendaftaran Pasien';
include_once('config.php');
include_once('layout/header.php');
include_once('layout/sidebar.php');

if(isset($_GET['no_rawat'])) {
    $_sql = "SELECT a.no_rkm_medis, a.no_rawat, b.nm_pasien, b.umur FROM reg_periksa a, pasien b WHERE a.no_rkm_medis = b.no_rkm_medis AND a.no_rawat = '$_GET[no_rawat]'";
    $found_pasien = query($_sql);
    if(num_rows($found_pasien) == 1) {
	     while($row = fetch_array($found_pasien)) {
	        $no_rkm_medis  = $row['0'];
          $no_rawat	     = $row['1'];
	        $nm_pasien     = $row['2'];
	        $umur          = $row['3'];
	     }
    } else {
	     redirect ('pendaftaran.php');
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
                                <?php echo $title; ?> <div class="right pendaftaran"><button class="btn btn-default waves-effect accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapsePendaftaran"></button></div>
                                <small>Periode <?php if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) { echo date("d-m-Y",strtotime($_POST['tgl_awal']))." s/d ".date("d-m-Y",strtotime($_POST['tgl_akhir'])); } else { echo date("d-m-Y",strtotime($date)) . ' s/d ' . date("d-m-Y",strtotime($date));} ?></small>
                            </h2>
                        </div>
                        <?php display_message(); ?>
                        <?php
                        $action = isset($_GET['action'])?$_GET['action']:null;
                        if(!$action){
                        // Hitung nomor rawat
                        $tgl_reg = date('Y/m/d', strtotime($date));
                        $no_rawat_akhir = fetch_array(query("SELECT max(no_rawat) FROM reg_periksa WHERE tgl_registrasi='$date'"));
                        $no_urut_rawat = substr($no_rawat_akhir[0], 11, 6);
                        $no_rawat = $tgl_reg.'/'.sprintf('%06s', ($no_urut_rawat + 1));

                        ?>
                        <div class="panel-group" id="accordion">
                          <div class="panel panel-default" style="border: none !important;">
                            <div id="collapsePendaftaran" class="panel-collapse collapse in" style="margin-top:40px;">
                              <div class="panel-body">
                                <form class="form-horizontal">
                                    <div class="row clearfix">
                                        <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                            <label for="email_address_2">No. RM :</label>
                                        </div>
                                        <div class="col-lg-4 col-md-10 col-sm-8">
                                          <div class="input-group input-group-lg">
                                              <div class="form-line">
                                                  <input type="text" class="form-control" id="no_rkm_medis" placeholder="Nomor Rekam Medis">
                                              </div>
                                              <span class="input-group-addon">
                                                  <i class="material-icons" data-toggle="modal" data-target="#pasienModal">attach_file</i>
                                              </span>
                                          </div>
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                            <label for="password_2">Nama Pasien :</label>
                                        </div>
                                        <div class="col-lg-4 col-md-10 col-sm-8">
                                          <div class="input-group input-group-lg">
                                              <div class="form-line">
                                                  <input type="text" class="form-control" id="nm_pasien" placeholder="Nama Lengkap Dengan Gelar">
                                              </div>
                                          </div>
                                        </div>
                                    </div>
                                    <div class="row clearfix">
                                        <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                            <label for="email_address_2">No. Rawat :</label>
                                        </div>
                                        <div class="col-lg-4 col-md-10 col-sm-8">
                                          <div class="input-group input-group-lg">
                                              <div class="form-line">
                                                  <input type="text" class="form-control" id="no_rawat" value="<?php echo $no_rawat; ?>" placeholder="Nomor Rawat">
                                              </div>
                                          </div>
                                        </div>
                                      <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                            <label for="password_2">Tgl Registrasi :</label>
                                        </div>
                                        <div class="col-lg-4 col-md-10 col-sm-8">
                                          <div class="input-group input-group-lg">
                                              <div class="form-line">
                                                  <input type="text" class="form-control" id="tgl_registrasi" value="<?php echo $date_time; ?>" placeholder="Tanggal Pendaftaran">
                                              </div>
                                          </div>
                                        </div>
                                    </div>
                                    <div class="row clearfix">
                                        <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                            <label for="email_address_2">Dokter :</label>
                                        </div>
                                        <div class="col-lg-4 col-md-10 col-sm-8">
                                          <div class="input-group input-group-lg">
                                              <div class="form-line">
                                                  <input type="hidden" class="form-control" id="kd_dokter"><input type="text" class="form-control" id="nm_dokter" placeholder="Dokter tujuan">
                                              </div>
                                              <span class="input-group-addon">
                                                  <i class="material-icons" data-toggle="modal" data-target="#dokterModal">attach_file</i>
                                              </span>
                                          </div>
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                            <label for="password_2">Unit :</label>
                                        </div>
                                        <div class="col-lg-4 col-md-10 col-sm-8">
                                          <div class="input-group input-group-lg">
                                              <div class="form-line">
                                                  <input type="hidden" class="form-control" id="kd_poli"><input type="text" class="form-control" id="nm_poli" placeholder="Unit atau Klinik">
                                              </div>
                                              <span class="input-group-addon">
                                                  <i class="material-icons" data-toggle="modal" data-target="#unitModal">attach_file</i>
                                              </span>
                                          </div>
                                        </div>
                                    </div>
                                    <div class="row clearfix">
                                        <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                            <label for="email_address_2">Png. Jawab :</label>
                                        </div>
                                        <div class="col-lg-4 col-md-10 col-sm-8">
                                          <div class="input-group input-group-lg">
                                              <div class="form-line">
                                                  <input type="text" class="form-control" id="namakeluarga" placeholder="Nama Penanggung Jawab">
                                              </div>
                                          </div>
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                            <label for="password_2">Alamat :</label>
                                        </div>
                                        <div class="col-lg-4 col-md-10 col-sm-8">
                                          <div class="input-group input-group-lg">
                                              <div class="form-line">
                                                  <input type="text" class="form-control" id="alamatpj" placeholder="Alamat Penanggung Jawab">
                                              </div>
                                          </div>
                                        </div>
                                    </div>
                                    <div class="row clearfix">
                                        <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                            <label for="email_address_2">Jenis Bayar :</label>
                                        </div>
                                        <div class="col-lg-4 col-md-10 col-sm-8">
                                          <div class="input-group input-group-lg">
                                              <div class="form-line">
                                                  <input type="hidden" class="form-control" id="kd_pj"><input type="text" class="form-control" id="png_jawab" placeholder="Jenis Bayar">
                                              </div>
                                              <span class="input-group-addon">
                                                  <i class="material-icons" data-toggle="modal" data-target="#penjabModal">attach_file</i>
                                              </span>
                                          </div>
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                            <label for="password_2">Rujukan :</label>
                                        </div>
                                        <div class="col-lg-4 col-md-10 col-sm-8">
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
                            <table id="pendaftaran" class="table responsive table-bordered table-striped table-hover display nowrap" width="100%">
                                <thead>
                                    <tr>
                                        <th>Nama Pasien</th>
                                        <th>No. RM</th>
                                        <th>No. Reg</th>
                                        <th>Tgl. Reg</th>
                                        <th>Jam Reg</th>
                                        <th>Alamat</th>
                                        <th>Jenis Bayar</th>
                                        <th>Poliklinik</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $sql = "SELECT a.nm_pasien, b.no_rkm_medis, a.alamat, c.png_jawab, d.nm_poli, b.no_rawat, b.no_reg, b.tgl_registrasi, b.jam_reg, b.p_jawab, b.almt_pj, e.perujuk, f.kd_dokter, f.nm_dokter, b.kd_poli FROM pasien a, reg_periksa b, penjab c, poliklinik d, rujuk_masuk e, dokter f WHERE a.no_rkm_medis = b.no_rkm_medis AND b.kd_pj = c.kd_pj AND b.kd_poli = d.kd_poli AND b.no_rawat = e.no_rawat AND b.kd_dokter = f.kd_dokter";
                                if($role == 'Medis' || $role == 'Paramedis') {
                                  $sql .= " AND b.kd_poli = '$jenis_poli'";
                                }
                                if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) {
                                	$sql .= " AND b.tgl_registrasi BETWEEN '$_POST[tgl_awal]' AND '$_POST[tgl_akhir]'";
                                } else {
                                  	$sql .= " AND b.tgl_registrasi = '$date'";
                                }
                                $query = query($sql);
                                while($row = fetch_array($query)) {
                                ?>

                                    <tr class="editpasien"
                                      data-norm="<?php echo $row['1']; ?>"
                                      data-nmpasien="<?php echo $row['0']; ?>"
                                      data-tglregistrasi="<?php echo $row['7']; ?> <?php echo $row['8']; ?>"
                                      data-norawat="<?php echo $row['5']; ?>"
                                      data-namakeluarga="<?php echo $row['9']; ?>"
                                      data-alamatpj="<?php echo $row['10']; ?>"
                                      data-pngjawab="<?php echo $row['3']; ?>"
                                      data-perujuk="<?php echo $row['11']; ?>"
                                      data-nmdokter="<?php echo $row['13']; ?>"
                                      data-kddokter="<?php echo $row['12']; ?>"
                                      data-nmpoli="<?php echo $row['4']; ?>"
                                      data-kdpoli="<?php echo $row['14']; ?>"
                                    >
                                        <td><?php echo SUBSTR($row['0'], 0, 15).' ...'; ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-secondary waves-effect dropdown-toggle" data-toggle="dropdown" data-disabled="true" aria-expanded="true"><?php echo $row['1']; ?> <span class="caret"></span></button>
                                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                    <li><a href="javascript:void(0);">Bridging BPJS</a></li>
                                                    <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=view&no_rawat=<?php echo $row['5']; ?>">Data SEP BPJS</a></li>
                                                    <li><a href="javascript:void(0);">Bridging Inhealt</a></li>
                                                    <li><a href="javascript:void(0);">Data SEP Inhealth</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                        <td><?php echo $row['6']; ?></td>
                                        <td><?php echo $row['7']; ?></td>
                                        <td><?php echo $row['8']; ?></td>
                                        <td><?php echo $row['2']; ?></td>
                                        <td><?php echo $row['3']; ?></td>
                                        <td><?php echo $row['4']; ?></td>
                                    </tr>
                                <?php
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
                    </div>
                    <?php } ?>
                    <?php if($action == "view"){ ?>
                    <?php echo $no_rawat; ?>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="pasienModal" tabindex="-1" role="dialog" aria-labelledby="pasienModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="width:800px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="pasienModalLabel">Database Pasien</h4>
                </div>
                <div class="modal-body">
                  <table id="pasien" class="table table-bordered table-striped table-hover display nowrap" width="100%">
                      <thead>
                          <tr>
                            <th>Nama Pasien</th>
                            <th>No. RM</th>
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
                            <th>Suku Bangsa</th>
                            <th>Bahasa</th>
                            <th>Instansi/Perusahaan</th>
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
        <div class="modal-dialog" style="width:800px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="dokterModalLabel">Database Dokter</h4>
                </div>
                <div class="modal-body">
                    <table id="dokter" class="table responsive table-bordered table-striped table-hover display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Kode Dokter</th>
                                <th>Nama Dokter</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="unitModal" tabindex="-1" role="dialog" aria-labelledby="unitModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="width:800px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="unitModalLabel">Database Poliklinik</h4>
                </div>
                <div class="modal-body">
                    <table id="poliklinik" class="table responsive table-bordered table-striped table-hover display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Kode Poli</th>
                                <th>Nama Poli</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="penjabModal" tabindex="-1" role="dialog" aria-labelledby="penjabModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="width:800px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="penjabModalLabel">Database Cara Bayar</h4>
                </div>
                <div class="modal-body">
                    <table id="penjab" class="table responsive table-bordered table-striped table-hover display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Cara Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="perujukModal" tabindex="-1" role="dialog" aria-labelledby="perujukModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="width:800px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="perujukModalLabel">Database Perujuk</h4>
                </div>
                <div class="modal-body">
                    <table id="perujuk" class="table responsive table-bordered table-striped table-hover display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Asal Rujukan</th>
                            </tr>
                        </thead>
                        <tbody>
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
  $('#pendaftaran').dataTable( {
        "processing": true,
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
        "order": [[ 0, "asc" ]]
  } );
  $('#pasien').dataTable( {
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
        "order": [[ 0, "asc" ]],
        "ajax": "includes/pasien.php",
        "createdRow": function( row, data, index ) {
          	$(row).addClass('pilihpasien');
            $(row).attr('data-nmpasien', data[0]);
            $(row).attr('data-norm', data[1]);
            $(row).attr('data-namakeluarga', data[6]);
            $(row).attr('data-alamatpj', data[21]);
        }
  } );
  $('#dokter').dataTable( {
        "processing": true,
        "serverSide": false,
        "responsive": false,
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
        "order": [[ 0, "asc" ]],
        "ajax": "includes/dokter.php",
        "createdRow": function( row, data, index ) {
          	$(row).addClass('pilihdokter');
            $(row).attr('data-kddokter', data[0]);
            $(row).attr('data-nmdokter', data[1]);
        }
  } );
  $('#poliklinik').dataTable( {
        "processing": true,
        "serverSide": false,
        "responsive": false,
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
        "order": [[ 0, "asc" ]],
        "ajax": "includes/poliklinik.php",
        "createdRow": function( row, data, index ) {
          	$(row).addClass('pilihpoliklinik');
            $(row).attr('data-kdpoli', data[0]);
            $(row).attr('data-nmpoli', data[1]);
        }
  } );
  $('#penjab').dataTable( {
        "processing": true,
        "serverSide": false,
        "responsive": false,
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
        "order": [[ 0, "asc" ]],
        "ajax": "includes/penjab.php",
        "createdRow": function( row, data, index ) {
          	$(row).addClass('pilihpenjab');
            $(row).attr('data-kdpj', data[0]);
            $(row).attr('data-pngjawab', data[1]);
        }
  } );
  $('#perujuk').dataTable( {
        "processing": true,
        "serverSide": false,
        "responsive": false,
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
        "order": [[ 0, "asc" ]],
        "ajax": "includes/perujuk.php",
        "createdRow": function( row, data, index ) {
          	$(row).addClass('pilihperujuk');
            $(row).attr('data-perujuk', data[0]);
        }
  } );
</script>
<script type="text/javascript">
    $(document).on('click', '.pilihpasien', function (e) {
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
            url:'includes/pendaftaran.php?p=add',
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
        var nama_perujuk = document.getElementById("nama_perujuk").value;
        $.ajax({
            url:'includes/pendaftaran.php?p=update',
            method:'POST',
            data:{
                no_rkm_medis:no_rkm_medis,
                no_rawat:no_rawat,
                kd_dokter:kd_dokter,
                kd_poli:kd_poli,
                kd_pj:kd_pj,
                tgl_registrasi:tgl_registrasi,
                nama_perujuk:nama_perujuk
            },
           success:function(data){
               window.location.reload(true)
           }
        });
    });
    $("#hapus").click(function(){
        var no_rawat = document.getElementById("no_rawat").value;
        $.ajax({
            url:'includes/pendaftaran.php?p=delete',
            method:'POST',
            data:{
              no_rawat:no_rawat
            },
           success:function(data){
               window.location.reload(true)
           }
      });
    });
</script>
