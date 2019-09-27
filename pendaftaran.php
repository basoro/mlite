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
    $_sql = "SELECT a.no_rkm_medis, a.no_rawat, b.nm_pasien, b.umur, a.status_lanjut , a.kd_pj, c.png_jawab FROM reg_periksa a, pasien b, penjab c WHERE a.no_rkm_medis = b.no_rkm_medis AND a.no_rawat = '$_GET[no_rawat]'";
    $found_pasien = query($_sql);
    if(num_rows($found_pasien) == 1) {
	     while($row = fetch_array($found_pasien)) {
	        $no_rkm_medis  = $row['0'];
	        $get_no_rawat	 = $row['1'];
          $no_rawat	     = $row['1'];
	        $nm_pasien     = $row['2'];
	        $umur          = $row['3'];
          $status_lanjut = $row['4'];
          $kd_pj         = $row['5'];
          $png_jawab     = $row['6'];
	     }
    } else {
	     redirect ("{$_SERVER['PHP_SELF']}");
    }
}

$action = isset($_GET['action'])?$_GET['action']:null;

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
                        <?php
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
                                $sql = "SELECT a.nm_pasien, b.no_rkm_medis, a.alamat, c.png_jawab, d.nm_poli, b.no_rawat, b.no_reg, b.tgl_registrasi, b.jam_reg, b.p_jawab, b.almt_pj, e.perujuk, f.kd_dokter, f.nm_dokter, b.kd_poli, c.kd_pj FROM pasien a, reg_periksa b, penjab c, poliklinik d, rujuk_masuk e, dokter f WHERE a.no_rkm_medis = b.no_rkm_medis AND b.kd_pj = c.kd_pj AND b.kd_poli = d.kd_poli AND b.no_rawat = e.no_rawat AND b.kd_dokter = f.kd_dokter";
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
                                      data-kdpj="<?php echo $row['15']; ?>"
                                    >
                                        <td><?php echo SUBSTR($row['0'], 0, 15).' ...'; ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-secondary waves-effect dropdown-toggle" data-toggle="dropdown" data-disabled="true" aria-expanded="true"><?php echo $row['1']; ?> <span class="caret"></span></button>
                                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                    <?php if(FKTL !== 'Yes') { ?><li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=view&no_rawat=<?php echo $row['5']; ?>">Pelayanan</a></li><?php } ?>
                                                    <?php if(FKTL == 'Yes') { ?>
                                                      <?php if(is_dir(ABSPATH.'/modules/BridgingBPJS/')) { ?>
                                                        <li><a href="./?module=BridgingBPJS&page=index&action=bridging&no_rawat=<?php echo $row['5']; ?>">Bridging BPJS</a></li>
                                                        <li><a href="./?module=BridgingBPJS&page=data_sep&no_rkm_medis=<?php echo $row['1']; ?>">Data SEP BPJS</a></li>
                                                      <?php } ?>
                                                    <?php } ?>
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
                    <?php if(FKTL !== 'Yes') { ?>
                    <?php if($action == "view"){ ?>
                            <div class="body">
                              <dl class="dl-horizontal">
                                <dt>Nama Lengkap</dt>
                                <dd><?php echo $nm_pasien; ?></dd>
                                <dt>No. RM</dt>
                                <dd><?php echo $no_rkm_medis; ?></dd>
                                <dt>No. Rawat</dt>
                                <dd><?php echo $no_rawat; ?></dd>
                                <dt>Cara Bayar</dt>
                                <dd><?php echo $png_jawab; ?></dd>
                                <dt>Umur</dt>
                                <dd><?php echo $umur; ?> Th</dd>
                              </dl>
                            </div>
                            <div class="body">
                              <!-- Nav Tabs -->
                              <div class="row">
                                <ul class="nav nav-tabs tab-nav-right" role="tablist">
                                  <li role="presentation" class="active"><a href="#riwayat" data-toggle="tab">RIWAYAT</a></li>
                                  <li role="presentation"><a href="#anamnese" data-toggle="tab">PEMERIKSAAN</a></li>
                                  <li role="presentation"><a href="#diagnosa" data-toggle="tab">DIAGNOSA</a></li>
                                  <li role="presentation"><a href="#tindakan" data-toggle="tab">TINDAKAN</a></li>
                                  <li role="presentation"><a href="#resep" data-toggle="tab">RESEP</a></li>
                                </ul>
                              </div>
                              <!-- End Nav Tabs -->
                              <!-- Tab Panes -->
                              <div class="tab-content m-t-20">
                                <!-- riwayat -->
                                <div role="tabpanel" class="tab-pane fade in active" id="riwayat">
                                  <table id="riwayatmedis" class="table">
                                    <thead>
                                      <tr>
                                        <th>Tanggal</th>
                                        <th>Nomor Rawat</th>
                                        <th>Klinik/Ruangan/Dokter</th>
                                        <th>Keluhan</th>
                                        <th>Pemeriksaan</th>
                                        <th>Diagnosa</th>
                                        <th>Tindakan</th>
                                        <th>Obat</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <?php
                                      $q_kunj = query ("SELECT tgl_registrasi, no_rawat, status_lanjut FROM reg_periksa WHERE no_rkm_medis = '$no_rkm_medis' AND stts !='Batal' ORDER BY tgl_registrasi DESC");
                                      while ($data_kunj = fetch_array($q_kunj)) {
                                          $tanggal_kunj   = $data_kunj[0];
                                          $no_rawat_kunj = $data_kunj[1];
                                          $status_lanjut_kunj = $data_kunj[2];
                                      ?>
                                      <tr>
                                        <td><?php echo $tanggal_kunj; ?></td>
                                        <td><?php echo $no_rawat_kunj; ?></td>
                                        <td>
                                          <?php
                                          if($status_lanjut_kunj == 'Ralan') {
                                            $sql_poli = fetch_assoc(query("SELECT a.nm_poli, c.nm_dokter FROM poliklinik a, reg_periksa b, dokter c WHERE b.no_rawat = '$no_rawat_kunj' AND a.kd_poli = b.kd_poli AND b.kd_dokter = c.kd_dokter"));
                                            echo $sql_poli['nm_poli'];
                                            echo '<br>';
                                            echo "(".$sql_poli['nm_dokter'].")";
                                          } else {
                                            echo 'Rawat Inap';
                                          }
                                          ?>
                                        </td>
                                          <?php
                                          if($status_lanjut_kunj == 'Ralan') {
                                            $sql_riksaralan = fetch_assoc(query("SELECT keluhan, pemeriksaan FROM pemeriksaan_ralan WHERE no_rawat = '$no_rawat_kunj'"));
                                            echo "<td>".$sql_riksaralan['keluhan']."</td>";
                                            echo "<td>".$sql_riksaralan['pemeriksaan']."</td>";
                                          } else {
                                            $sql_riksaranap = fetch_assoc(query("SELECT keluhan, pemeriksaan FROM pemeriksaan_ranap WHERE no_rawat = '$no_rawat_kunj'"));
                                            echo "<td>".$sql_riksaranap['keluhan']."</td>";
                                            echo "<td>".$sql_riksaranap['pemeriksaan']."</td>";
                                          }
                                          ?>
                                        <td>
                                            <ul style="list-style:none;">
                                            <?php
                                            $sql_dx = query("SELECT a.kd_penyakit, a.nm_penyakit FROM penyakit a, diagnosa_pasien b WHERE a.kd_penyakit = b.kd_penyakit AND b.no_rawat = '$no_rawat_kunj'");
                                            $no=1;
                                            while ($row_dx = fetch_array($sql_dx)) {
                                                echo '<li>'.$no.'. '.$row_dx[1].' ('.$row_dx[0].')</li>';
                                                $no++;
                                            }
                                            ?>
                                            </ul>
                                        </td>
                                        <td>
                                            <ul style="list-style:none;">
                                            <?php
                                            $sql_tx = query("SELECT a.kd_jenis_prw, b.nm_perawatan  FROM rawat_jl_dr a, jns_perawatan b WHERE a.kd_jenis_prw = b.kd_jenis_prw AND a.no_rawat = '{$no_rawat_kunj}'");
                                            $no=1;
                                            while ($row_tx = fetch_array($sql_tx)) {
                                                echo '<li>'.$no.'. '.$row_tx[1].' ('.$row_tx[0].')</li>';
                                                $no++;
                                            }
                                            ?>
                                            </ul>
                                        </td>
                                        <td>
                                            <ul style="list-style:none;">
                                            <?php
                                            $sql_obat = query("select detail_pemberian_obat.jml, databarang.nama_brng from detail_pemberian_obat inner join databarang on detail_pemberian_obat.kode_brng=databarang.kode_brng where detail_pemberian_obat.no_rawat= '$no_rawat_kunj'");
                                            $no=1;
                                            while ($row_obat = fetch_array($sql_obat)) {
                                                echo '<li>'.$no.'. '.$row_obat[1].' ('.$row_obat[0].')</li>';
                                                $no++;
                                            }
                                            ?>
                                            </ul>
                                        </td>
                                      </tr>
                                      <?php } ?>
                                    </tbody>
                                  </table>
                                </div>
                                <!-- riwayat -->
                                <!-- anamnese -->
                                  <div class="tab-pane fade" role="tabpanel" id="anamnese">
                                    <form method="post">
                                      <?php
                                      if(isset($_POST['ok_an'])){
                                        if(($no_rawat <> "")){
                                          $insert = query("INSERT INTO pemeriksaan_ralan VALUE ('{$no_rawat}','{$date}','{$time}','{$_POST['suhu']}','{$_POST['tensi']}','{$_POST['nadi']}','{$_POST['respirasi']}','{$_POST['tinggi']}','{$_POST['berat']}'
                                                      ,'{$_POST['gcs']}','{$_POST['keluhan']}','{$_POST['pemeriksaan']}','{$_POST['alergi']}','-','{$_POST['tndklnjt']}','-')");
                                          if($insert){
                                            redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}");
                                          }
                                        }
                                      }
                                      ?>
                                    <div class="row clearfix">
                                      <div class="col-md-3">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Keluhan</dt>
                                            <dd><textarea rows="4" name="keluhan" class="form-control"></textarea></dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-3">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Pemeriksaan</dt>
                                            <dd><textarea rows="4" name="pemeriksaan" class="form-control"></textarea></dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-3">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Alergi</dt>
                                            <dd><input type="text" class="form-control" name="alergi"></dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-3">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Tindak Lanjut</dt>
                                            <dd><input type="text" class="form-control" name="tndklnjt"></dd>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="row clearfix">
                                      <div class="col-md-3">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Suhu Badan (C)</dt>
                                            <dd><input type="text" class="form-control" name="suhu"></dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-3">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Tinggi Badan (Cm)</dt>
                                            <dd><input type="text" class="form-control" name="tinggi"></dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-3">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Tensi</dt>
                                            <dd><input type="text" class="form-control" name="tensi"></dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-3">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Respirasi (per Menit)</dt>
                                            <dd><input type="text" class="form-control" name="respirasi"></dd>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="row clearfix">
                                      <div class="col-md-3">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Berat (Kg)</dt>
                                            <dd><input type="text" class="form-control" name="berat"></dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-3">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Nadi (per Menit)</dt>
                                            <dd><input type="text" class="form-control" name="nadi"></dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-3">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>Imun Ke</dt>
                                            <dd><input type="text" class="form-control" name="imun"></dd>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-3">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <dt>GCS(E , V , M)</dt>
                                            <dd><input type="text" class="form-control" name="gcs"></dd>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="row clearfix">
                                      <div class="col-md-3">
                                        <div class="form-group">
                                          <dd><button type="submit" name="ok_an" value="ok_an" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_an\'">SIMPAN</button></dd><br/>
                                        </div>
                                      </div>
                                    </div>
                                      <div class="row clearfix">
                                        <table id="keluhan" class="table striped">
                                          <tr>
                                            <th>No</th>
                                            <th>Keluhan</th>
                                            <th>Pemeriksaan</th>
                                            <th>Hapus</th>
                                          </tr>
                                          <?php
                                          $query = query("SELECT keluhan , pemeriksaan FROM pemeriksaan_ralan WHERE no_rawat = '{$no_rawat}'");
                                          $no=1;
                                           while ($data = fetch_array($query)) {
                                          ?>
                                          <tr>
                                            <td><?php echo $no; ?></td>
                                            <td><?php echo $data['0']; ?></td>
                                            <td><?php echo $data['1']; ?></td>
                                            <td><a class="btn btn-danger btn-xs" href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_an&keluhan=<?php echo $data['0']; ?>&no_rawat=<?php echo $no_rawat; ?>">[X]</a></td>
                                          </tr>
                                          <?php
                                            $no++;}
                                          ?>
                                        </table>
                                      </div>
                                    </form>
                                  </div>
                                <!-- anamnese -->
                                <!-- diagnosa -->
                                  <div role="tabpanel" class="tab-pane fade" id="diagnosa">
                                    <form method="post">
                                      <?php
                                      if (isset($_POST['ok_diagnosa'])) {
                                        if (($_POST['kode_diagnosa'] <> "") and ($no_rawat <> "")) {

                                          $cek_dx = fetch_assoc(query("SELECT a.kd_penyakit FROM diagnosa_pasien a, reg_periksa b WHERE a.kd_penyakit = '".$_POST['kode_diagnosa']."' AND b.no_rkm_medis = '$no_rkm_medis' AND a.no_rawat = b.no_rawat"));
                                          if(empty($cek_dx)) {
                                            $status_penyakit = 'Baru';
                                          } else {
                                            $status_penyakit = 'Lama';
                                          }

                                          $cek_prioritas_penyakit = fetch_assoc(query("SELECT prioritas FROM diagnosa_pasien WHERE kd_penyakit = '".$_POST['kode_diagnosa']."' AND no_rawat = '$no_rawat'"));
                                          $cek_prioritas_primer = fetch_assoc(query("SELECT prioritas FROM diagnosa_pasien WHERE prioritas = '1' AND no_rawat = '$no_rawat'"));
                                          $cek_prioritas = fetch_assoc(query("SELECT prioritas FROM diagnosa_pasien WHERE prioritas = '".$_POST['prioritas']."' AND no_rawat = '$no_rawat'"));

                                          if (!empty($cek_prioritas_penyakit)) {
                                              $errors[] = 'Sudah ada diagnosa yang sama.';
                                          }

                                          if(!empty($errors)) {

                                              foreach($errors as $error) {
                                                  echo validation_errors($error);
                                              }

                                          } else {

                                               $insert = query("INSERT INTO diagnosa_pasien VALUES ('{$no_rawat}', '{$_POST['kode_diagnosa']}', 'Ralan', '{$_POST['prioritas']}', '{$status_penyakit}')");
                                               if ($insert) {
                                                    redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}");
                                               }
                                          }
                                        }
                                      }
                                      ?>
                                    <dl class="dl-horizontal">
                                      <dt>Diagnosa</dt>
                                        <dd><select name="kode_diagnosa" class="kd_diagnosa" style="width:100%"></select></dd><br/>
                                      <dt>Prioritas</dt>
                                        <dd>
                                          <select name="prioritas" class="prioritas" style="width:100%">
                                            <option value="1">Diagnosa Ke-1</option>
                                            <option value="2">Diagnosa Ke-2</option>
                                            <option value="3">Diagnosa Ke-3</option>
                                            <option value="4">Diagnosa Ke-4</option>
                                            <option value="5">Diagnosa Ke-5</option>
                                            <option value="6">Diagnosa Ke-6</option>
                                            <option value="7">Diagnosa Ke-7</option>
                                            <option value="8">Diagnosa Ke-8</option>
                                            <option value="9">Diagnosa Ke-9</option>
                                            <option value="10">Diagnosa Ke-10</option>
                                          </select>
                                        </dd><br/>
                                      <dt></dt>
                                        <dd><button type="submit" name="ok_diagnosa" value="ok_diagnosa" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_diagnosa\'">SIMPAN</button></dd><br/>
                                      <dt></dt>
                                        <dd>
                                          <ul style="list-style:none;margin-left:0;padding-left:0;">
                                            <?php
                                            $query = query("SELECT a.kd_penyakit, b.nm_penyakit, a.prioritas FROM diagnosa_pasien a, penyakit b, reg_periksa c WHERE a.kd_penyakit = b.kd_penyakit AND a.no_rawat = '{$no_rawat}' AND a.no_rawat = c.no_rawat ORDER BY a.prioritas ASC");
                                              $no=1;
                                            while ($data = fetch_array($query)) {
                                            ?>
                                                      <li><?php echo $no; ?>. <?php echo $data['1']; ?> <a class="btn btn-danger btn-xs" href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_diagnosa&kode=<?php echo $data['0']; ?>&prioritas=<?php echo $data['2']; ?>&no_rawat=<?php echo $no_rawat; ?>">[X]</a></li>
                                            <?php
                                                  $no++;
                                            }
                                            ?>
                                          </ul>
                                        </dd>
                                    </dl>
                                    </form>
                                  </div>
                                <!-- end diagnosa -->
                                <!-- diagnosa -->
                                  <div role="tabpanel" class="tab-pane fade" id="tindakan">
                                    <?php
                                    if($action == "view"){
                                      if (isset($_POST['ok_tdk'])) {
                                                    if (($_POST['kd_tdk'] <> "") and ($no_rawat <> "")) {
                                                          $insert = query("INSERT INTO rawat_jl_dr VALUES ('{$no_rawat}','{$_POST['kd_tdk']}','{$_SESSION['username']}','$date','$time','0','0','{$_POST['kdtdk']}','0','0','{$_POST['kdtdk']}','Belum')");
                                                          if ($insert) {
                                                              redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}");
                                                          };
                                                    };
                                              };
                                    }
                                    ?>

                                    <div class="body">
                                    <form method="POST">
                                      <label for="email_address">Nama Tindakan</label>
                                      <div class="form-group">
                                         <select name="kd_tdk" class="form-control kd_tdk" id="kd_tdk" style="width:100%"></select>
                                         <br/>
                                         <input type="hidden" class="form-control" id="kdtdk" name="kdtdk"/>
                                      </div>
                                      <button type="submit" name="ok_tdk" value="ok_tdk" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_tdk\'">SIMPAN</button>
                                    </form>
                                    </div>
                                    <div class="body">
                                    <table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                                        <thead>
                                            <tr>
                                                <th>Nama Tindakan</th>
                                                <th>Tanggal Tindakan</th>
                                                <th>Biaya</th>
                                                <th>Tools</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $query_tindakan = query("SELECT a.kd_jenis_prw, a.tgl_perawatan, a.tarif_tindakandr, b.nm_perawatan  FROM rawat_jl_dr a, jns_perawatan b WHERE a.kd_jenis_prw = b.kd_jenis_prw AND a.no_rawat = '{$no_rawat}'");
                                        while ($data_tindakan = fetch_array($query_tindakan)) {
                                        ?>
                                            <tr>
                                                <td><?php echo SUBSTR($data_tindakan['3'], 0, 20).' ...'; ?></td>
                                                <td><?php echo $data_tindakan['1']; ?></td>
                                                <td><?php echo $data_tindakan['2']; ?></td>
                                                <td><a href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_tindakan&kd_jenis_prw=<?php echo $data_tindakan['0']; ?>&no_rawat=<?php echo $no_rawat; ?>">Hapus</a></td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                    </div>
                                  </div>
                                <!-- end diagnosa -->
                                <!-- eresep -->
                                  <div role="tabpanel" class="tab-pane fade" id="resep">
                                    <form method="post">
                                      <?php
                                      if (isset($_POST['ok_obat'])) {
                                          if (($_POST['kode_obat'] <> "") and ($no_rawat <> "")) {
                                              $onhand = query("SELECT no_resep FROM resep_obat WHERE no_rawat = '{$no_rawat}' AND tgl_peresepan = CURRENT_DATE()");
                                              $dtonhand = fetch_array($onhand);
                                              $get_number = fetch_array(query("select ifnull(MAX(CONVERT(RIGHT(no_resep,10),signed)),0) from resep_obat where tgl_perawatan like '%{$date}%'"));
                                              $lastNumber = substr($get_number[0], 0, 10);
                                              $next_no_resep = sprintf('%010s', ($lastNumber + 1));
                                              //$next_no_resep = $get_number + 1;

                                              if ($dtonhand['0'] > 1) {
                                                if ($_POST['aturan_pakai_lainnya'] == "") {
                                                  $insert = query("INSERT INTO resep_dokter VALUES ('{$dtonhand['0']}', '{$_POST['kode_obat']}', '{$_POST['jumlah']}', '{$_POST['aturan_pakai']}')");
                                                } else {
                                                  $insert = query("INSERT INTO resep_dokter VALUES ('{$dtonhand['0']}', '{$_POST['kode_obat']}', '{$_POST['jumlah']}', '{$_POST['aturan_pakai_lainnya']}')");
                                                }
                                                redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}");
                                              } else {
                                                  $insert = query("INSERT INTO resep_obat VALUES ('{$next_no_resep}', '{$date}', '{$time}', '{$no_rawat}', '{$_SESSION['username']}', '{$date}', '{$time}', '{$status_lanjut}')");
                                                  if ($_POST['aturan_pakai_lainnya'] == "") {
                                                    $insert2 = query("INSERT INTO resep_dokter VALUES ('{$next_no_resep}', '{$_POST['kode_obat']}', '{$_POST['jumlah']}', '{$_POST['aturan_pakai']}')");
                                                  } else {
                                                    $insert2 = query("INSERT INTO resep_dokter VALUES ('{$next_no_resep}', '{$_POST['kode_obat']}', '{$_POST['jumlah']}', '{$_POST['aturan_pakai_lainnya']}')");
                                                  }
                                                  redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}");
                                              }
                                          }
                                      }
                                      ?>
                                    <dl class="dl-horizontal">
                                        <dt>Nama Obat</dt>
                                        <dd><select name="kode_obat" class="kd_obat" style="width:100%"></select></dd><br>
                                        <dt>Jumlah Obat</dt>
                                        <dd><input class="form-control" name="jumlah" value="10" style="width:100%"></dd><br>
                                        <dt>Aturan Pakai</dt>
                                        <dd>
                                            <select name="aturan_pakai" class="aturan_pakai" id="lainnya" style="width:100%">
                                            <?php
                                            $sql = query("SELECT aturan FROM master_aturan_pakai");
                                            while($row = fetch_array($sql)){
                                                echo '<option value="'.$row[0].'">'.$row[0].'</option>';
                                            }
                                            ?>
                                            <option value="lainnya">Lainnya</option>
                                            </select>
                                        </dd><br>
                                        <div id="row_dim">
                                        <dt></dt>
                                        <dd><input class="form-control" name="aturan_pakai_lainnya" style="width:100%"></dd><br>
                                        </div>
                                        <dt></dt>
                                        <dd><button type="submit" name="ok_obat" value="ok_obat" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_obat\'">SIMPAN</button></dd><br>
                                        <dt></dt>
                                    </dl>
                                    <div class="table-responsive">
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
                                        $query_resep = query("SELECT a.kode_brng, a.jml, a.aturan_pakai, b.nama_brng, a.no_resep FROM resep_dokter a, databarang b, resep_obat c WHERE a.kode_brng = b.kode_brng AND a.no_resep = c.no_resep AND c.no_rawat = '{$no_rawat}' AND c.kd_dokter = '{$_SESSION['username']}' ");
                                        while ($data_resep = fetch_array($query_resep)) {
                                        ?>
                                            <tr>
                                                <td><?php echo $data_resep['3']; ?> <a class="btn btn-danger btn-xs" href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_obat&kode_obat=<?php echo $data_resep['0']; ?>&no_resep=<?php echo $data_resep['4']; ?>&no_rawat=<?php echo $no_rawat; ?>">[X]</a></td>
                                                <td><?php echo $data_resep['1']; ?></td>
                                                <td><?php echo $data_resep['2']; ?></td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                    </div>
                                    </form>
                                  </div>
                                <!-- end eresep -->
                              </div>
                              <!-- Tab Panes -->
                            </div>
                    <!-- Menu View -->
                    <?php } ?>
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

    <!-- delete -->
    <?php
    if($action == "delete_diagnosa"){
    	$hapus = "DELETE FROM diagnosa_pasien WHERE no_rawat='{$_REQUEST['no_rawat']}' AND kd_penyakit = '{$_REQUEST['kode']}' AND prioritas = '{$_REQUEST['prioritas']}'";
    	$hasil = query($hapus);
    	if (($hasil)) {
    	    redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}#diagnosa");
    	}
    }

    if($action == "delete_obat"){
    	$hapus = "DELETE FROM resep_dokter WHERE no_resep='{$_REQUEST['no_resep']}' AND kode_brng='{$_REQUEST['kode_obat']}'";
    	$hasil = query($hapus);
    	if (($hasil)) {
    	    redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}#resep");
    	}
    }

    if($action == "delete_an"){
      $hapus = "DELETE FROM pemeriksaan_ralan WHERE no_rawat='{$_REQUEST['no_rawat']}' AND keluhan='{$_REQUEST['keluhan']}'";
      $hasil = query($hapus);
      if (($hasil)) {
        redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}#anamnese");
      }
    }
    if ($action == "delete_tindakan") {
      $hapus = "DELETE FROM rawat_jl_dr WHERE kd_jenis_prw='{$_REQUEST['kd_jenis_prw']}' AND no_rawat='{$_REQUEST['no_rawat']}'";
      $hasil = query($hapus);
      if (($hasil)) {
        redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}#tindakan");
      }
    }

    ?>
    <!-- end delete -->

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
  $('#riwayatmedis').dataTable( {
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
        var namakeluarga = document.getElementById("namakeluarga").value;
        var alamatpj = document.getElementById("alamatpj").value;
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
                namakeluarga:namakeluarga,
                alamatpj:alamatpj,
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

    function formatData (data) {
        var $data = $(
            '<b>'+ data.id +'</b> - <i>'+ data.text +'</i>'
        );
        return $data;
    };

    function formatDataTEXT (data) {
        var $data = $(
            '<b>'+ data.text +'</b>'
        );
        return $data;
    };

    $('.kd_diagnosa').select2({
        placeholder: 'Pilih diagnosa',
        ajax: {
            url: 'includes/select-diagnosa.php',
            dataType: 'json',
            delay: 250,
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
            cache: true
        },
        templateResult: formatData,
        minimumInputLength: 3
    });

    $('.prioritas').select2({
        placeholder: 'Pilih prioritas diagnosa'
    });

    $('.kd_obat').select2({
      placeholder: 'Pilih obat',
      ajax: {
        url: 'includes/select-obat.php',
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
          return {
            results: data
          };
        },
        cache: true
      },
      templateResult: formatData,
    minimumInputLength: 3
    });

    $('.aturan_pakai').select2({
        placeholder: 'Pilih aturan pakai'
    });

    $(function () {
         $('#row_dim').hide();
         $('#lainnya').change(function () {
             $('#row_dim').hide();
             if (this.options[this.selectedIndex].value == 'lainnya') {
                 $('#row_dim').show();
             }
         });
     });

</script>

<script type="text/javascript">

    function formatInputData (data) {
          var $data = $(
              '<b>('+ data.id +')</b> Rp '+ data.tarif +' - <i>'+ data.text +'</i>'
          );
          return $data;
      };

    $('.kd_tdk').select2({
      placeholder: 'Pilih tindakan',
      ajax: {
        url: './includes/select-tindakan.php',
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
          return {
            results: data
          };
        },
        cache: true
      },
      templateResult: formatInputData,
    minimumInputLength: 3
    });

    $('.kd_tdk').on('change', function () {
     var kode = $("#kd_tdk").val();
     $.ajax({
      url: './includes/select-biaya.php',
      data: "kode="+kode,
     }).success(function (data){
       var json = data,
           obj = JSON.parse(json);
          $('#kdtdk').val(obj.tarif);
       });
    });


</script>
