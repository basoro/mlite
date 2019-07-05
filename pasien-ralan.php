<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
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
	        $get_no_rawat	     = $row['1'];
          $no_rawat	     = $row['1'];
	        $nm_pasien     = $row['2'];
	        $umur          = $row['3'];
	     }
    } else {
	     redirect ('pasien-ralan.php');
    }
}
if($_SERVER['REQUEST_METHOD'] == "POST") {
  $update = query("UPDATE reg_periksa SET stts = '".$_POST['stts']."' WHERE no_rawat = '".$_POST['no_rawat']."'");
  if($update){
  	redirect('pasien-ralan.php');
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
                                PASIEN RAWAT JALAN
                                <small><?php if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) { echo "Periode ".date("d-m-Y",strtotime($_POST['tgl_awal']))." s/d ".date("d-m-Y",strtotime($_POST['tgl_akhir'])); } ?></small>
                            </h2>
                        </div>
                        <?php display_message(); ?>
                        <?php
                        $action = isset($_GET['action'])?$_GET['action']:null;
                        if(!$action){
                        ?>
                      	
                      	
                            <div class="body table-responsive">
                              <!-- Nav tabs -->
                              <div class="row">
                                <ul class="nav nav-tabs tab-nav-right" role="tablist">
                                  <li role="presentation" class="active"><a href="#data" data-toggle="tab">DATA REGISTRASI</a></li>
                                  <li role="presentation"><a href="#reglama" data-toggle="tab">REGISTRASI PASIEN LAMA</a></li>
                                  <li role="presentation"><a href="#regbaru" data-toggle="tab">REGISTRASI PASIEN BARU</a></li>
                                </ul>
                              </div>
                              <div class="tab-content m-t-20">
                          		<div role="tabpanel" class="tab-pane fade in active" id="data">
                                <div id="buttons" class="align-center m-l-10 m-b-15 export-hidden"></div>
                                <table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
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
                                    $sql = "SELECT a.nm_pasien, b.no_rkm_medis, a.alamat, c.png_jawab, d.nm_poli, b.no_rawat, b.no_reg, b.tgl_registrasi, b.jam_reg FROM pasien a, reg_periksa b, penjab c, poliklinik d WHERE a.no_rkm_medis = b.no_rkm_medis AND b.kd_pj = c.kd_pj AND b.kd_poli = d.kd_poli";
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
                                        <tr>
                                            <td><?php echo SUBSTR($row['0'], 0, 15).' ...'; ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-secondary waves-effect dropdown-toggle" data-toggle="dropdown" data-disabled="true" aria-expanded="true"><?php echo $row['1']; ?> <span class="caret"></span></button>
                                                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                        <li><a href="javascript:void(0);">Assesment Awal</a></li>
                                                        <?php if($role !== 'Paramedis_Ranap')  { ?>
                                                        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=view&no_rawat=<?php echo $row['5']; ?>">e-Dokter</a></li>
                                                        <?php } ?>
                                                        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=tindakan&no_rawat=<?php echo $row['5']; ?>">Input Tindakan</a></li>
                                                        <li><a href="javascript:void(0);">Input Obat</a></li>
                                                        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=radiologi&no_rawat=<?php echo $row['5']; ?>">Berkas Radiologi</a></li>
                                                        <li><a href="includes/insertpasien.php?no_rawat=<?php echo $row['5']; ?>">Status</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                            <td><?php echo $row['6']; ?></td>
                                            <td><?php echo $row['7']; ?></td>
                                            <td><?php echo $row['8']; ?></td>
                                            <td><?php echo SUBSTR($row['2'],0,25); ?></td>
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
                                <div role="tabpanel" class="tab-pane fade in" id="regbaru">
                                  <div class="row clearfix">
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>No Rekam Medis</dt>
                                          <dd><input type="text" class="form-control" name="noreg"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Nama</dt>
                                          <dd><input type="text" class="form-control" name="norawat"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Jenis Kelamin</dt>
                                          <dd><input type="text" class="datepicker form-control" name="tgl"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Golongan Darah</dt>
                                          <dd><input type="text" class="form-control" name="jam" value="<?php echo $time;?>"></dd>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="row clearfix">
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Tanggal Lahir</dt>
                                          <dd><input type="text" class="form-control" name="dr"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Tempat Lahir</dt>
                                          <dd><input type="text" class="form-control" name="poli"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Umur</dt>
                                          <dd><select name="no_pasien" class="pasien" style="width:100%"></select></dd>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="row clearfix">
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Asuransi</dt>
                                          <dd><input type="text" class="form-control" name="status"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>No Peserta</dt>
                                          <dd><input type="text" class="form-control" name="pj"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>No Telp</dt>
                                          <dd><input type="text" class="form-control" name="rujuk"></dd>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="row clearfix">
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <dd><button type="submit" name="ok_an" value="ok_an" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_an\'">OK</button></dd><br/>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              <div role="tabpanel" class="tab-pane fade in" id="reglama">
                                 <div class="row clearfix">
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>No Reg</dt>
                                          <dd><input type="text" class="form-control" name="noreg"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>No Rawat</dt>
                                          <dd><input type="text" class="form-control" name="norawat"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Tanggal</dt>
                                          <dd><input type="text" class="datepicker form-control" name="tgl"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Jam</dt>
                                          <dd><input type="text" class="form-control" name="jam" value="<?php echo $time;?>"></dd>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="row clearfix">
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Dokter Yang Dituju</dt>
                                          <dd><input type="text" class="form-control" name="dr"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Poli Tujuan</dt>
                                          <dd><input type="text" class="form-control" name="poli"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>No Rekam Medik</dt>
                                          <dd><select name="no_pasien" class="pasien" style="width:100%"></select></dd>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="row clearfix">
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Status Pasien</dt>
                                          <dd><input type="text" class="form-control" name="status"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Jenis Pembayaran</dt>
                                          <dd><input type="text" class="form-control" name="pj"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Asal Rujukan</dt>
                                          <dd><input type="text" class="form-control" name="rujuk"></dd>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="row clearfix">
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <dd><button type="submit" name="ok_an" value="ok_an" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_an\'">OK</button></dd><br/>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                      </div>
                        </div>
                      </div>
                  </div>
                        <?php } ?>
                        <?php if($action == "view"){ ?>
                        <!-- View Pasien -->
                        <div class="body">
                            <dl class="dl-horizontal">
                                <dt>Nama Lengkap</dt>
                                <dd><?php echo $nm_pasien; ?></dd>
                                <dt>No. RM</dt>
                                <dd><?php echo $no_rkm_medis; ?></dd>
                                <dt>No. Rawat</dt>
                                <dd><?php echo $no_rawat; ?></dd>
                                <dt>Umur</dt>
                                <dd><?php echo $umur; ?></dd>
                            </dl>
                        </div>
                        <div class="header">
                            <h2>
                                Catatan Medis
                            </h2>
                        </div>
                        <div class="body">
                          <div id="buttons" class="align-center m-l-10 m-b-15 export-hidden"></div>
                             <table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nomor Rawat</th>
                                        <th>Klinik/Ruangan</th>
                                        <th>Keluhan</th>
                                        <th>Pemeriksaan</th>
                                        <th>Diagnosa</th>
                                        <th>Obat</th>
                                        <th>Laboratorium</th>
                                        <th>Radiologi</th>
                                    </tr>
                                </thead>
                            <tbody>
                            <?php
                            $q_kunj = query ("SELECT tgl_registrasi, no_rawat, status_lanjut FROM reg_periksa WHERE no_rkm_medis = '$no_rkm_medis' AND stts !='Batal' ORDER BY tgl_registrasi DESC");
                            while ($data_kunj = fetch_array($q_kunj)) {
                                $tanggal_kunj   = $data_kunj[0];
                                $no_rawat_kunj = $data_kunj[1];
                                $status_lanjut = $data_kunj[2];
                            ?>
                                <tr>
                                    <td><?php echo $tanggal_kunj; ?></td>
                                    <td><?php echo $no_rawat_kunj; ?></td>
                                    <td>
                                      <?php
                                      if($status_lanjut == 'Ralan') {
                                        $sql_poli = fetch_assoc(query("SELECT a.nm_poli FROM poliklinik a, reg_periksa b WHERE b.no_rawat = '$no_rawat_kunj' AND a.kd_poli = b.kd_poli"));
                                        echo $sql_poli['nm_poli'];
                                      } else {
                                        echo 'Rawat Inap';
                                      }
                                      ?>
                                    </td>
                                      <?php
                                      if($status_lanjut == 'Ralan') {
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
                                        $sql_obat = query("select detail_pemberian_obat.jml, databarang.nama_brng from detail_pemberian_obat inner join databarang on detail_pemberian_obat.kode_brng=databarang.kode_brng where detail_pemberian_obat.no_rawat= '$no_rawat_kunj'");
                                        $no=1;
                                        while ($row_obat = fetch_array($sql_obat)) {
                                            echo '<li>'.$no.'. '.$row_obat[1].' ('.$row_obat[0].')</li>';
                                            $no++;
                                        }
                                        ?>
                                        </ul>
                                    </td>
                                    <td>
                                        <ul style="list-style:none;">
                                        <?php
                                        $sql_lab = query("select template_laboratorium.Pemeriksaan, detail_periksa_lab.nilai, template_laboratorium.satuan, detail_periksa_lab.nilai_rujukan, detail_periksa_lab.keterangan from detail_periksa_lab inner join  template_laboratorium on detail_periksa_lab.id_template=template_laboratorium.id_template  where detail_periksa_lab.no_rawat= '$no_rawat_kunj'");
                                        $no=1;
                                        while ($row_lab = fetch_array($sql_lab)) {
                                            echo '<li>'.$no.'. '.$row_lab[0].' ('.$row_lab[3].') = '.$row_lab[1].' '.$row_lab[2].'</li>';
                                            $no++;
                                        }
                                        ?>
                                        </ul>
                                    </td>
                                    <td>
                                        <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                                        <?php
                                        $sql_rad = query("select * from berkas_digital_perawatan where no_rawat= '$no_rawat_kunj' and kode='005'");
                                        $no=1;
                                        while ($row_rad = fetch_array($sql_rad)) {
                                            echo '<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">';
                                            echo '<a href="http://simrs.rshdbarabai.com/berkasrawat/'.$row_rad[2].'" data-sub-html=""><img class="img-responsive thumbnail"  src="http://simrs.rshdbarabai.com/berkasrawat/'.$row_rad[2].'" width="300"></a>';
                                            echo '</div>';
                                            $no++;
                                        }
                                        ?>

                                      </div>

                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                            </tbody>
                            </table>
                        </div>

                          <form method="post" action="">
                            <div class="header">
                                <h2>
                                    Detail e-Diagnosa
                                </h2>
                            </div>
                            <?php
                            if (isset($_POST['ok_diagnosa'])) {
                                    if (($_POST['kode_diagnosa'] <> "") and ($no_rawat <> "")) {
                                          $insert = query("INSERT INTO diagnosa_pasien VALUES ('{$no_rawat}', '{$_POST['kode_diagnosa']}', 'Ralan', '{$_POST['prioritas']}')");
                                          if ($insert) {
                                              redirect("pendaftaran.php?action=view&no_rawat={$no_rawat}");
                                          }
                                    }
                              }
                              ?>
                            <div class="body">
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
                                        </select>
                                    </dd><br/>
                                    <dt></dt>
                                    <dd><button type="submit" name="ok_diagnosa" value="ok_diagnosa" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_diagnosa\'">OK</button></dd><br/>
                                    <dt></dt>
                                    <dd>
                                            <ul style="list-style:none;margin-left:0;padding-left:0;">
                                              <?php
                                          $query = query("SELECT a.kd_penyakit, b.nm_penyakit, a.prioritas FROM diagnosa_pasien a, penyakit b, reg_periksa c WHERE a.kd_penyakit = b.kd_penyakit AND a.no_rawat = '{$no_rawat}' AND a.no_rawat = c.no_rawat AND c.kd_dokter = '{$_SESSION['username']}' ORDER BY a.prioritas ASC");
                                            $no=1;
                                            while ($data = fetch_array($query)) {
                                            ?>
                                              <li><?php echo $no; ?>. <?php echo $data['1']; ?> <a href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_diagnosa&kode=<?php echo $data['0']; ?>&prioritas=<?php echo $data['2']; ?>&no_rawat=<?php echo $no_rawat; ?>">[Hapus]</a></li>
                                              <?php
                                           $no++;
                                            }
                                            ?>
                                            </ul>
                                    </dd>
                                </dl>
                            </div>
                            <div class="header">
                                <h2>
                                    Detail e-Resep
                                </h2>
                            </div>
                                <?php
                                if (isset($_POST['ok_obat'])) {
                                if (($_POST['kode_obat'] <> "") and ($no_rawat <> "")) {
                                        $onhand = query("SELECT no_resep FROM resep_obat WHERE no_rawat = '{$no_rawat}'");
                                        $dtonhand = fetch_array($onhand);
                                        $get_number = fetch_array(query("SELECT max(no_resep) FROM resep_obat"));
                                        $lastNumber = substr($get_number[0], 0, 10);
                                        $next_no_resep = sprintf('%010s', ($lastNumber + 1));

                                    if ($dtonhand['0'] > 1) {
                                      if ($_POST['aturan_pakai_lainnya'] == "") {
                                            $insert = query("INSERT INTO resep_dokter VALUES ('{$dtonhand['0']}', '{$_POST['kode_obat']}', '{$_POST['jumlah']}', '{$_POST['aturan_pakai']}')");
                                      } else {
                                            $insert = query("INSERT INTO resep_dokter VALUES ('{$dtonhand['0']}', '{$_POST['kode_obat']}', '{$_POST['jumlah']}', '{$_POST['aturan_pakai_lainnya']}')");
                                      }
                                        redirect("pendaftaran.php?action=view&no_rawat={$no_rawat}");
                                                    } else {
                                            $insert = query("INSERT INTO resep_obat VALUES ('{$next_no_resep}', '{$date}', '{$time}', '{$no_rawat}', '{$_SESSION['username']}', '{$date}', '{$time}')");
                                        if ($_POST['aturan_pakai_lainnya'] == "") {
                                                $insert2 = query("INSERT INTO resep_dokter VALUES ('{$next_no_resep}', '{$_POST['kode_obat']}', '{$_POST['jumlah']}', '{$_POST['aturan_pakai']}')");
                                        } else {
                                                $insert2 = query("INSERT INTO resep_dokter VALUES ('{$next_no_resep}', '{$_POST['kode_obat']}', '{$_POST['jumlah']}', '{$_POST['aturan_pakai_lainnya']}')");
                                        }
                                            redirect("pendaftaran.php?action=view&no_rawat={$no_rawat}");
                                        }
                                  }
                                }
                                ?>
                            <div class="body">
                                <dl class="dl-horizontal">
                                    <dt>Nama Obat</dt>
                                    <dd><select name="kode_obat" class="kd_obat" style="width:100%"></select></dd><br>
                                    <dt>Jumlah Obat</dt>
                                    <dd><input name="jumlah" value="10" style="width:100%"></dd><br>
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
                                    <dd><input name="aturan_pakai_lainnya" style="width:100%"></dd><br>
                                    </div>
                                    <dt></dt>
                                    <dd><button type="submit" name="ok_obat" value="ok_obat" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_obat\'">OK</button></dd><br>
                                    <dt></dt>
                                </dl>
                            </div>
                            <div class="body">
                                <div class="body table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nama Obat</th>
                                            <th>Jumlah</th>
                                            <th>Aturan Pakai</th>
                                            <th>Tools</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $query_resep = query("SELECT a.kode_brng, a.jml, a.aturan_pakai, b.nama_brng, a.no_resep FROM resep_dokter a, databarang b, resep_obat c WHERE a.kode_brng = b.kode_brng AND a.no_resep = c.no_resep AND c.no_rawat = '{$no_rawat}' AND c.kd_dokter = '{$_SESSION['username']}' ");
                                    while ($data_resep = fetch_array($query_resep)) {
                                    ?>
                                        <tr>
                                            <td><?php echo $data_resep['3']; ?></td>
                                            <td><?php echo $data_resep['1']; ?></td>
                                            <td><?php echo $data_resep['2']; ?></td>
                                            <td><a href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_obat&kode_obat=<?php echo $data_resep['0']; ?>&no_resep=<?php echo $data_resep['4']; ?>&no_rawat=<?php echo $no_rawat; ?>">Hapus</a></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        </form>
                    <?php } ?>
                    <?php
                    if($action == "radiologi"){
                                if (isset($_POST['ok_radiologi'])) {
                              $periksa_radiologi = fetch_assoc(query("SELECT tgl_periksa, jam FROM periksa_radiologi WHERE no_rawat = '{$no_rawat}'"));
                              $date = $periksa_radiologi['tgl_periksa'];
                              $time = $periksa_radiologi['jam'];
                //$photo_berkas=fetch_array(query("SELECT lokasi_file FROM berkas_digital_perawatan WHERE kode = '{$kode_berkas}' AND no_rawat='{$no_rawat}'"));
                //$kode_berkas = $_POST['kode'];
                if($_FILES['file']['name']!='') {
                //$file='../webapps/berkasrawat/'.$photo_berkas;
                //@unlink($file);
                $tmp_name = $_FILES["file"]["tmp_name"];
                $namefile = $_FILES["file"]["name"];
                $explode = explode(".", $namefile);
                $ext = end($explode);
                $image_name = "berkasradiologi-".time().".".$ext;
                move_uploaded_file($tmp_name,"../radiologi/pages/upload/".$image_name);
                $lokasi_berkas = 'pages/upload/'.$image_name;
                $insert_berkas = query("INSERT INTO gambar_radiologi VALUES('$no_rawat', '$date', '$time', '$lokasi_berkas')");
                if($insert_berkas) {
                set_message('Berkas digital radiologi telah ditersimpan.');
                    redirect("pasien-ralan.php?action=radiologi&no_rawat=$no_rawat");
                }


                }


                                }
                    ?>
                        <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <div class="body">
                          <dl class="dl-horizontal">
                              <dt>Nama Lengkap</dt>
                              <dd><?php echo $nm_pasien; ?></dd>
                              <dt>No. RM</dt>
                              <dd><?php echo $no_rkm_medis; ?></dd>
                              <dt>No. Rawat</dt>
                              <dd><?php echo $no_rawat; ?></dd>
                              <dt>Umur</dt>
                              <dd><?php echo $umur; ?></dd>
                          </dl>
              <hr>
                                      <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                                      <?php
                                      $sql_rad = query("select * from gambar_radiologi where no_rawat= '{$_GET['no_rawat']}'");
                                      $no=1;
                                      while ($row_rad = fetch_array($sql_rad)) {
                                          echo '<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">';
                                          echo '<a href="'.URLSIMRS.'/radiologi/'.$row_rad[3].'" data-sub-html=""><img class="img-responsive thumbnail"  src="'.URLSIMRS.'/radiologi/'.$row_rad[3].'"></a>';
                                          echo '</div>';
                                          $no++;
                                      }
                                      ?>

                                    </div>
                      <hr>
                        </div>
                          <div class="body">
                            <form id="form_validation" name="pilihan" action="" method="POST"  enctype="multipart/form-data">
                                <label for="email_address">Unggah Berkas Radiologi</label>
                                <div class="form-group">
                                    <img id="image_upload_preview" width="200px" src="images/upload_berkas.png" onclick="upload_berkas()" style="cursor:pointer;" />
                                    <br/>
                                    <input name="file" id="inputFile" type="file" style="display:none;"/>
                                </div>
                                <button type="submit" name="ok_radiologi" value="ok_radiologi" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_radiologi\'">UPLOAD BERKAS</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    <?php } ?>
                    <?php 
                    if($action == "tindakan"){
                      if (isset($_POST['ok_tdk'])) {
                                    if (($_POST['kd_tdk'] <> "") and ($no_rawat <> "")) {
                                          $insert = query("INSERT INTO rawat_jl_pr VALUES ('{$no_rawat}','{$_POST['kd_tdk']}','{$_SESSION['username']}','$date','$time','0','0','{$_POST['kdtdk']}','0','0','{$_POST['kdtdk']}','Belum')");
                                          if ($insert) {
                                              redirect("pasien-ralan.php?action=tindakan&no_rawat={$no_rawat}");
                                          };
                                    };
                              };
                      if(isset($_POST['ok_per'])){
                            if(($no_rawat <> "")){
                              $insert = query("INSERT INTO pemeriksaan_ralan VALUE ('{$no_rawat}','{$date}','{$time}','{$_POST['suhu']}','{$_POST['tensi']}','{$_POST['nadi']}','{$_POST['respirasi']}','{$_POST['tinggi']}','{$_POST['berat']}'
                                          ,'{$_POST['gcs']}','{$_POST['keluhan']}','{$_POST['pemeriksaan']}','{$_POST['alergi']}','-','{$_POST['tndklnjt']}')");
                              if($insert){
                                redirect("{$_SERVER['PHP_SELF']}?action=tindakan&no_rawat={$no_rawat}");
                              }
                            }
                          }
                      ?>
                       <div class="row">
                         <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                           <div class="body">
                               <dl class="dl-horizontal">
                                   <dt>Nama Lengkap</dt>
                                   <dd><?php echo $nm_pasien; ?></dd>
                                   <dt>No. RM</dt>
                                   <dd><?php echo $no_rkm_medis; ?></dd>
                                   <dt>No. Rawat</dt>
                                   <dd><?php echo $no_rawat; ?></dd>
                                   <dt>Umur</dt>
                                   <dd><?php echo $umur; ?></dd>
                               </dl>
                               <ul class="nav nav-tabs tab-nav-right" role="tablist">
                                 <li role="presentation" class="active"><a href="#datapem" data-toggle="tab">PEMERIKSAAN</a></li>
                                 <li role="presentation"><a href="#data" data-toggle="tab">TINDAKAN</a></li>
                               </ul>
                           </div>
                             <div class="tab-content m-t-20">
                               <div role="tabpanel" class="tab-pane fade in" id="data">
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
                                     $query_tindakan = query("SELECT a.kd_jenis_prw, a.tgl_perawatan, a.tarif_tindakanpr, b.nm_perawatan  FROM rawat_jl_pr a, jns_perawatan b WHERE a.kd_jenis_prw = b.kd_jenis_prw AND a.no_rawat = '{$no_rawat}'");
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

                               <div role="tabpanel" class="tab-pane fade in active" id="datapem">
                                 <div class="body">
                                 <form method="POST">
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
                                   <button type="submit" name="ok_per" value="ok_per" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_per\'">SIMPAN</button>
                                 </form>
                                 </div>
                                 <div class="body">
                                 <table id="datatab" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                                     <thead>
                                         <tr>
                                             <th>Keluhan</th>
                                             <th>Pemeriksaan</th>
                                             <th>Tools</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                                     <?php
                                     $query_tindakan = query("SELECT * FROM pemeriksaan_ralan WHERE no_rawat = '{$no_rawat}'");
                                     while ($data_tindakan = fetch_array($query_tindakan)) {
                                     ?>
                                         <tr>
                                             <td><?php echo $data_tindakan['keluhan']; ?></td>
                                             <td><?php echo $data_tindakan['pemeriksaan']; ?></td>
                                             <td><a href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_tindakan&kd_jenis_prw=<?php echo $data_tindakan['0']; ?>&no_rawat=<?php echo $no_rawat; ?>">Hapus</a></td>
                                         </tr>
                                     <?php
                                     }
                                     ?>
                                     </tbody>
                                 </table>
                                 </div>
                               </div>
                             </div>
                           
                      <?php } ?>
                    <?php
                    //delete
                    if($action == "delete_diagnosa"){
                          $hapus = "DELETE FROM diagnosa_pasien WHERE no_rawat='{$_REQUEST['no_rawat']}' AND kd_penyakit = '{$_REQUEST['kode']}' AND prioritas = '{$_REQUEST['prioritas']}'";
                          $hasil = query($hapus);
                          if (($hasil)) {
                              redirect("pendaftaran.php?action=view&no_rawat={$no_rawat}");
                          }
                    }

                    //delete
                    if($action == "delete_obat"){
                          $hapus = "DELETE FROM resep_dokter WHERE no_resep='{$_REQUEST['no_resep']}' AND kode_brng='{$_REQUEST['kode_obat']}'";
                          $hasil = query($hapus);
                          if (($hasil)) {
                          redirect("pendaftaran.php?action=view&no_rawat={$no_rawat}");
                          }
                    }

                    if ($action == "delete_tindakan") {
                      $hapus = "DELETE FROM rawat_jl_pr WHERE kd_jenis_prw='{$_REQUEST['kd_jenis_prw']}' AND no_rawat='{$_REQUEST['no_rawat']}'";
                      $hasil = query($hapus);
                      if (($hasil)) {
                        redirect("pasien-ralan.php?action=tindakan&no_rawat={$no_rawat}");
                      }
                    }
                    ?>
                    </div>
                </div>
            </div>
        </section>
                  <div class="modal fade" id="statuspulang" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-sm" role="document">
                        <div class="modal-content">
                            <div class="stts_pulang">
                            </div>
                        </div>
                    </div>
                  </div>

<?php
include_once('layout/footer.php');
?>
    <script type="text/javascript">
     
        function formatData (data) {
            var $data = $(
                '<b>'+ data.id +'</b> - <i>'+ data.text +'</i>'
            );
            return $data;
        };
      
        function formatInputData (data) {
              var $data = $(
                  '<b>('+ data.id +')</b> Rp '+ data.tarif +' - <i>'+ data.text +'</i>'
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

        $('.pasien').select2({
          placeholder: 'Pilih nama/no.RM pasien',
          ajax: {
            url: 'includes/select-pasien.php',
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
		
      	

      	$('.kd_tdk').select2({
          placeholder: 'Pilih tindakan',
          ajax: {
            url: 'includes/select-tindakan.php',
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
         	url: 'includes/biaya.php',
         	data: "kode="+kode,
         }).success(function (data){
           var json = data,
               obj = JSON.parse(json);
           		$('#kdtdk').val(obj.tarif);
           });
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
