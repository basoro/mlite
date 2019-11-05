<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

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
	     redirect ('./?module=RawatInap&page=index');
    }
}

?>
<div class="card">
    <div class="header">
      <h2>Pasien Rawat Inap</h2>
    </div>
    <div class="body">
                        <?php display_message(); ?>
                        <?php
                        $action = isset($_GET['action'])?$_GET['action']:null;
                        $jenis_poli = isset($_SESSION['jenis_poli'])?$_SESSION['jenis_poli']:null;
                        $role = isset($_SESSION['role'])?$_SESSION['role']:null;
                        if(!$action){
                        ?>
                        <table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th width = "1%">No<br>MR</th>
                                    <th>Kamar</th>
                                    <th>Bed</th>
                                    <th width = "10px">Tanggal<br>Masuk</th>
                                    <th width = "10px">Cara<br>Bayar</th>
                                    <th>DPJP</th>
                                 </tr>
                            </thead>
                            <tbody>
                            <!-- This query based on Adly's (Adly Hidayat S.KOM) query. Thanks bro -->
                            <?php
                            $sql = "
                              SELECT
                                pasien.nm_pasien,
                                  reg_periksa.no_rkm_medis,
                                  bangsal.nm_bangsal,
                                  kamar_inap.kd_kamar,
                                  kamar_inap.tgl_masuk,
                                  penjab.png_jawab,
                                  reg_periksa.no_rawat
                                FROM
                                  kamar_inap,
                                    reg_periksa,
                                    pasien,
                                    bangsal,
                                    kamar,
                                    penjab
                                WHERE
                                  kamar_inap.no_rawat = reg_periksa.no_rawat
                                AND
                                  reg_periksa.no_rkm_medis = pasien.no_rkm_medis
                                AND
                                  kamar_inap.kd_kamar = kamar.kd_kamar
                                AND
                                  kamar.kd_bangsal = bangsal.kd_bangsal
                                AND
                                  kamar_inap.stts_pulang = '-'
                                AND
                                  reg_periksa.kd_pj = penjab.kd_pj

                            ";
                            if($role == 'Paramedis_Ranap') {
                              $sql .= " AND bangsal.kd_bangsal = '$jenis_poli'";
                            }
                            $sql .= " ORDER BY kamar_inap.kd_kamar ASC";
                            $result = query($sql);
                            while($row = fetch_array($result)) {
                              $get_no_rawat = $row['6'];
                            ?>
                                <tr>
                                    <td><?php echo SUBSTR($row['0'], 0, 15).' ...'; ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-info waves-effect dropdown-toggle" data-toggle="dropdown" data-disabled="true" aria-expanded="true"><?php echo $row['1']; ?> <span class="caret"></span></button>
                                            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                <li><a href="<?php echo URL; ?>/?module=RawatInap&page=index&action=tindakan&no_rawat=<?php echo $row['6']; ?>">CPPT & Tindakan</a></li>
                                                <li><a href="<?php echo URL; ?>/?module=RawatInap&page=index&action=berkas_digital&no_rawat=<?php echo $row['6']; ?>">Berkas Digital Perawatan</a></li>
                                                <li><a href="<?php echo URL; ?>/?module=RawatInap&page=index&action=radiologi&no_rawat=<?php echo $row['6']; ?>">Berkas Radiologi</a></li>
                                                <li><a href="<?php echo URL; ?>/?module=RawatInap&page=index&action=status_pulang&no_rawat=<?php echo $row['6']; ?>&bed=<?php echo $row['3']?>">Status Pulang</a></li>
                                              <li><a href="<?php echo URL; ?>/?module=RawatInap&page=index&action=pindah&no_rawat=<?php echo $row['6'];?>&nm_pasien=<?php echo $row['nm_pasien'];?>&no_rkm_medis=<?php echo $row['no_rkm_medis'];?>&kd_kmr_sblmny=<?php echo $row['3'];?>">Pindah Kamar</a></li>
                                          </ul>
                                        </div>
                                    </td>
                                    <td><?php echo $row['2']; ?></td>
                                    <td><?php echo $row['3']; ?></td>
                                    <td><?php echo $row['4']; ?></td>
                                    <td><?php echo $row['5']; ?></td>
                                    <td><?php $dpjp = query("SELECT dokter.nm_dokter FROM dpjp_ranap , dokter WHERE dpjp_ranap.kd_dokter = dokter.kd_dokter AND dpjp_ranap.no_rawat = '".$row['6']."'");$dpjpp = fetch_array($dpjp);echo $dpjpp['0'];?></td>
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

                        <?php } ?>



                        <?php
                        if($action == "tindakan"){
                          if (isset($_POST['ok_tdk'])) {
                                        if (($_POST['kd_tdk'] <> "") and ($no_rawat <> "")) {
                                              $insert = query("INSERT INTO rawat_jl_pr VALUES ('{$no_rawat}','{$_POST['kd_tdk']}','{$_SESSION['username']}','$date','$time','0','0','{$_POST['kdtdk']}','0','0','{$_POST['kdtdk']}','Belum')");
                                              if ($insert) {
                                                  redirect("./?module=RawatInap&page=index&action=tindakan&no_rawat={$no_rawat}");
                                              };
                                        };
                                  };

                          if(isset($_POST['ok_per'])){
                                if(($no_rawat <> "")){
                                  $insert = query("INSERT INTO pemeriksaan_ralan VALUES ('{$no_rawat}',CURRENT_DATE(),CURRENT_TIME(),'{$_POST['suhu']}','{$_POST['tensi']}','{$_POST['nadi']}','{$_POST['respirasi']}','{$_POST['tinggi']}','{$_POST['berat']}'
                                              ,'{$_POST['gcs']}','{$_POST['keluhan']}','{$_POST['pemeriksaan']}','{$_POST['alergi']}','-','{$_POST['tndklnjt']}','-')");
                                  if($insert){
                                    redirect("./?module=RawatInap&page=index&action=tindakan&no_rawat={$no_rawat}");
                                  }
                                }
                              };
                          if(isset($_POST['edit_an'])){
                                if(($no_rawat <> "")){
                              	$insert = query("UPDATE pemeriksaan_ralan SET suhu_tubuh = '{$_POST['suhu']}', tensi = '{$_POST['tensi']}', nadi = '{$_POST['nadi']}', respirasi = '{$_POST['respirasi']}', tinggi = '{$_POST['tinggi']}', berat = '{$_POST['berat']}', gcs = '{$_POST['gcs']}', keluhan = '{$_POST['keluhan']}', pemeriksaan = '{$_POST['pemeriksaan']}', alergi = '{$_POST['alergi']}', rtl = '{$_POST['tndklnjt']}' WHERE no_rawat = '{$no_rawat}'");

                                  if($insert){
                                    redirect("./?module=RawatInap&page=index&action=tindakan&no_rawat={$no_rawat}");
                                  }
                                }
                              };

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
                                     <li role="presentation" class="active"><a href="#datapem" data-toggle="tab">CPPT</a></li>
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
                                     <table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">
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
                                                 <td><a href="./?module=RawatInap&page=index&action=delete_tindakan&kd_jenis_prw=<?php echo $data_tindakan['0']; ?>&no_rawat=<?php echo $no_rawat; ?>">Hapus</a></td>
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
                                     <table id="datatab" class="table table-bordered table-striped table-hover display nowrap" width="100%">
                                         <thead>
                                             <tr>
                                               <th>Keluhan</th>
                                               <th>Pemeriksaan</th>
                                               <th>Suhu</th>
                                               <th>BB</th>
                                               <th>Tinggi</th>
                                               <th>Tensi</th>
                                               <th>Nadi</th>
                                               <th>RR</th>
                                               <th>Action</th>
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
                                               <td><?php echo $data_tindakan['suhu_tubuh']; ?></td>
                                               <td><?php echo $data_tindakan['berat']; ?></td>
                                               <td><?php echo $data_tindakan['tinggi']; ?></td>
                                               <td><?php echo $data_tindakan['tensi']; ?></td>
                                               <td><?php echo $data_tindakan['nadi']; ?></td>
                                               <td><?php echo $data_tindakan['respirasi']; ?></td>
                                               <td><a href="#" data-toggle="modal" data-target="#edit_anamneseModal">Edit</a> <a href="./?module=RawatInap&action=delete_pemeriksaan&no_rawat=<?php echo $no_rawat; ?>">Hapus</a></td>
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

                          <?php if($opt == 'edit_anamnese' ) { ?>
                          <?php
                              $row = fetch_assoc(query("SELECT * FROM pemeriksaan_ralan WHERE no_rawat = '{$no_rawat}'"));
                          ?>
                          <form method="post">
                          <div class="row clearfix">
                          <div class="col-md-3">
                            <div class="form-group">
                              <div class="form-line">
                                <dt>Edit Keluhan</dt>
                                <dd><textarea rows="4" name="keluhan" class="form-control"><?php echo $row['keluhan']; ?></textarea></dd>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <div class="form-line">
                                <dt>Pemeriksaan</dt>
                                <dd><textarea rows="4" name="pemeriksaan" class="form-control"><?php echo $row['pemeriksaan']; ?></textarea></dd>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <div class="form-line">
                                <dt>Alergi</dt>
                                <dd><input type="text" class="form-control" value="<?php echo $row['alergi']; ?>" name="alergi"></dd>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <div class="form-line">
                                <dt>Tindak Lanjut</dt>
                                <dd><input type="text" class="form-control" value="<?php echo $row['rtl']; ?>" name="tndklnjt"></dd>
                              </div>
                            </div>
                          </div>
                          </div>
                          <div class="row clearfix">
                          <div class="col-md-3">
                            <div class="form-group">
                              <div class="form-line">
                                <dt>Suhu Badan (C)</dt>
                                <dd><input type="text" class="form-control" value="<?php echo $row['suhu_tubuh']; ?>" name="suhu"></dd>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <div class="form-line">
                                <dt>Tinggi Badan (Cm)</dt>
                                <dd><input type="text" class="form-control" value="<?php echo $row['tinggi']; ?>" name="tinggi"></dd>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <div class="form-line">
                                <dt>Tensi</dt>
                                <dd><input type="text" class="form-control" value="<?php echo $row['tensi']; ?>" name="tensi"></dd>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <div class="form-line">
                                <dt>Respirasi (per Menit)</dt>
                                <dd><input type="text" class="form-control" value="<?php echo $row['respirasi']; ?>" name="respirasi"></dd>
                              </div>
                            </div>
                          </div>
                          </div>
                          <div class="row clearfix">
                          <div class="col-md-3">
                            <div class="form-group">
                              <div class="form-line">
                                <dt>Berat (Kg)</dt>
                                <dd><input type="text" class="form-control" value="<?php echo $row['berat']; ?>" name="berat"></dd>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <div class="form-line">
                                <dt>Nadi (per Menit)</dt>
                                <dd><input type="text" class="form-control" value="<?php echo $row['nadi']; ?>" name="nadi"></dd>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <div class="form-line">
                                <dt>Imun Ke</dt>
                                <dd><input type="text" class="form-control" value="<?php echo $row['imun_ke']; ?>"  name="imun"></dd>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <div class="form-line">
                                <dt>GCS(E , V , M)</dt>
                                <dd><input type="text" class="form-control" value="<?php echo $row['gcs']; ?>" name="gcs"></dd>
                              </div>
                            </div>
                          </div>
                          </div>
                          <div class="row clearfix">
                          <div class="col-md-3">
                            <div class="form-group">
                              <dd><button type="submit" name="edit_an" value="edit_an" class="btn bg-indigo waves-effect" onclick="this.value=\'edit_an\'">SIMPAN</button></dd><br/>
                            </div>
                          </div>
                          </div>
                          </form>
                          <?php } ?>
                          <?php if($action == "berkas_digital") { ?>
                          <?php
                            if (isset($_POST['ok_berdig'])) {
                              $periksa_radiologi = fetch_assoc(query("SELECT tgl_periksa, jam FROM periksa_radiologi WHERE no_rawat = '{$no_rawat}'"));
                              $date = $periksa_radiologi['tgl_periksa'];
                              $time = $periksa_radiologi['jam'];
                              if($_FILES['file']['name']!='') {
                                $tmp_name = $_FILES["file"]["tmp_name"];
                                $namefile = $_FILES["file"]["name"];
                                $explode = explode(".", $namefile);
                                $ext = end($explode);
                                if($_POST['masdig']=='001') {
                                    $image_name = "berkasdigital-".time().".".$ext;
                                }else{
                                    $image_name = "rujukanfktp-".time().".".$ext;
                                }
                                move_uploaded_file($tmp_name,"../berkasrawat/pages/upload/".$image_name);
                                $lokasi_berkas = 'pages/upload/'.$image_name;
                                $insert_berkas = query("INSERT INTO berkas_digital_perawatan VALUES('$no_rawat','{$_POST['masdig']}', '$lokasi_berkas')");
                                if($insert_berkas) {
                                  set_message('Berkas digital perawatan telah ditersimpan.');
                                  redirect("./?module=RawatInap&page=index");
                                }
                              }
                            }
                          ?>
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
                                $sql_rad = query("select * from berkas_digital_perawatan where no_rawat= '{$_GET['no_rawat']}'");
                                $no=1;
                                while ($row_rad = fetch_array($sql_rad)) {
                                  echo '<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">';
                                  echo '<a href="'.URLSIMRS.'/berkasrawat/'.$row_rad[2].'" data-sub-html=""><img class="img-responsive thumbnail"  src="'.URLSIMRS.'/berkasrawat/'.$row_rad[2].'"></a>';
                                  echo '</div>';
                                  $no++;
                                }
                              ?>
                              </div>
                            <hr>
                              </div>
                                <div class="body">
                                  <form id="form_validation" name="berdigi" action="" method="POST"  enctype="multipart/form-data">
                                      <label for="email_address">Unggah Berkas Digital Perawatan</label>
                                      <div class="form-group">
                                          <select class="form-control" name="masdig">
                                          <?php $berkas = query("SELECT * FROM master_berkas_digital");
                                          foreach($berkas as $berkas1):?>
                                            <option value="<?php echo $berkas1['kode'];?>"><?php echo $berkas1['nama'];?></option>
                                          <?php endforeach; ?>
                                          </select>
                                          <img id="image_upload_preview" width="200px" src="<?php echo URL; ?>/modules/RawatInap/images/upload_berkas.png" onclick="upload_berkas()" style="cursor:pointer;" />
                                          <br/>
                                          <input name="file" id="inputFile" type="file" style="display:none;"/>
                                      </div>
                                      <button type="submit" name="ok_berdig" value="ok_berdig" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_berdig\'">UPLOAD BERKAS</button>
                                  </form>
                                </div>
                              </div>
                          <?php } ?>
                          <?php
                          if($action == "radiologi"){
                            if (isset($_POST['ok_radiologi'])) {
                              $periksa_radiologi = fetch_assoc(query("SELECT tgl_periksa, jam FROM periksa_radiologi WHERE no_rawat = '{$no_rawat}'"));
                              $date = $periksa_radiologi['tgl_periksa'];
                              $time = $periksa_radiologi['jam'];
                              if($_FILES['file']['name']!='') {
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
                                    redirect("./?module=RawatInap&page=index&action=radiologi&no_rawat=$no_rawat");
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
                                          <img id="image_upload_preview" width="200px" src="./modules/RawatInap/images/upload_berkas.png" onclick="upload_berkas()" style="cursor:pointer;" />
                                          <br/>
                                          <input name="file" id="inputFile" type="file" style="display:none;"/>
                                      </div>
                                      <button type="submit" name="ok_radiologi" value="ok_radiologi" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_radiologi\'">UPLOAD BERKAS</button>
                                  </form>
                                </div>
                              </div>
                            </div>
                          <?php } ?>
                          <?php if($action == "status_pulang") { ?>
                            <?php if(isset($_POST['simpan_stts_pulang'])){

                                  if($_POST['stts_pulang'] == "Membaik"){
                                    $update = query("UPDATE kamar_inap SET tgl_keluar = '".$_POST['tglplg']."' , jam_keluar = '".$time."' , diagnosa_akhir = '".$_POST['dx']."' , stts_pulang = '".$_POST['stts_pulang']."' WHERE no_rawat = '".$_POST['no_rawat']."'");
                                    if($update){
                                      $update1 = query("UPDATE kamar SET status = 'KOSONG' WHERE kd_kamar = '".$_POST['bed']."'");
                                      redirect('./?module=RawatInap&page=index');
                                    }
                                  }else{echo "<script>alert('Pilih Membaik untuk Memulangkan')</script>";}

                                }?>
                                <form action="" method="POST">
                                  <div class="modal-body">
                                    <div class="form-group">
                                      <div class="form-line">
                                          <label for="dx">Diagnosa</label>
                                          <input type="text" class="form-control" name="dx" value="">
                                          <input type="hidden" class="form-control" name="bed" value="<?php echo $_GET['bed'];?>">
                                      </div>
                                    </div>
                                    <div class="form-group">
                                      <div class="form-line">
                                          <label for="tglplg">Tanggal Pulang</label>
                                          <input type="text" class="datepicker form-control" name="tglplg" value="<?php echo $date; ?>">
                                      </div>
                                    </div>
                                    <div class="form-group">
                                      <div class="form-line">
                                        <label for="stts_pulang">Status Pulang</label>
                                        <select name="stts_pulang" class="form-control show-tick">
                                        <?php
                                        $result = query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'kamar_inap' AND COLUMN_NAME = 'stts_pulang'");
                                        $row = fetch_array($result);
                                        $enumList = explode(",", str_replace("'", "", substr($row['COLUMN_TYPE'], 5, (strlen($row['COLUMN_TYPE'])-6))));
                                        foreach($enumList as $value) {
                                            echo "<option value='$value'>$value</option>";
                                        }
                                        ?>
                                        </select>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="modal-footer">
                                      <input type="hidden" name="no_rawat" value="<?php echo $_GET['no_rawat'];?>">
                                      <input type="submit" class="btn btn-success waves-effect" name="simpan_stts_pulang" value="SIMPAN">
                                  </div>
                             	  </form>
                          <?php } ?>
                          <?php if($action == "pindah") {

                            if ($_POST['stts_pindah'] == "2"){
                          			$no_rawat	 = $_POST['no_rawat'];
                          			$kd_kamar 	 = $_POST['kamar'];
                          			$kd_kmr_sbl	 = $_POST['kd_kamar_sebelumnya'];

                          			$sql_kamar   = "SELECT trf_kamar FROM kamar WHERE kd_kamar ='{$_POST['kamar']}'";
                          			$query		 =query($sql_kamar);
                          			$data		 =fetch_assoc($query);

                          			$tarif_kamar = $data['trf_kamar'];
                          			$tgl_masuk 	 = $_POST['tgl_masuk'];
                          			$jam		 = $_POST['jam_masuk'];

                          			$update = query("UPDATE kamar_inap SET kd_kamar = '".$_POST['kamar']."', trf_kamar ='".$data['trf_kamar']."', tgl_masuk ='".$_POST['tgl_masuk']."', lama = '1', ttl_biaya = '".$data['trf_kamar']."' WHERE no_rawat = '".$_POST['no_rawat']."' AND stts_pulang ='-' ");
                          			$update_status_kamarbr = query("UPDATE kamar SET status = 'ISI' WHERE kd_kamar='$kd_kamar'");
                          			$update_status_kamarsbl = query("UPDATE kamar SET status = 'KOSONG' WHERE kd_kamar='$kd_kmr_sbl'");

                          	} else if ($_POST['stts_pindah'] == "3") {
                          			$no_rawat	 = $_POST['no_rawat'];
                          			$kd_kamar 	 = $_POST['kamar'];
                          			$kd_kmr_sbl	 = $_POST['kd_kamar_sebelumnya'];

                          			$sql_kamar   = "SELECT trf_kamar FROM kamar WHERE kd_kamar ='{$_POST['kamar']}'";
                          			$query		 =query($sql_kamar);
                          			$data		 =fetch_assoc($query);

                          			$tarif_kamar = $data['trf_kamar'];

                          			$tgl_masuk 	 = $_POST['tgl_masuk'];
                          			$jam		 = $_POST['jam_masuk'];

                          			$tambah = query("INSERT INTO kamar_inap VALUES ('$no_rawat','$kd_kamar','$tarif_kamar','{$_POST['diagnosa_awal']}','-','$tgl_masuk','$jam','0000-00-00','00:00:00','1','$tarif_kamar','-')");
                          			$update_status_kamarbr = query("UPDATE kamar SET status = 'ISI' WHERE kd_kamar='$kd_kamar'");

                          			$tgl_masuk2       = new DateTime($tgl_masuk);

                          			$sql_kamar_2     = "SELECT tgl_masuk,trf_kamar FROM kamar_inap WHERE kd_kamar ='{$_POST['kd_kamar_sebelumnya']}' AND no_rawat ='{$_POST['no_rawat']}'";
                          			$query2		     =query($sql_kamar_2);
                          			$data		     =fetch_assoc($query2);
                          			$data_tgl 		 = $data['tgl_masuk'];
                          			$tgl_keluar			= new DateTime($data_tgl);
                          			$tgl_keluar_post 	= $tgl_masuk2->diff($tgl_keluar);
                          			$ttl_biaya 			= $tgl_keluar_post->days * $tarif_kamar;
                          			$tgl_keluar_bujur 	= $tgl_keluar->format('Y-m-d');

                          			$update_kamar_sebelumnya = query("UPDATE kamar_inap SET stts_pulang = 'Pindah Kamar', tgl_keluar = '$tgl_masuk',jam_keluar='$jam',lama = '".$tgl_keluar_post->d."', ttl_biaya = '$ttl_biaya' WHERE no_rawat = '".$_POST['no_rawat']."' AND kd_kamar='$kd_kmr_sbl' ");
                          			$update_status_kamarsbl = query("UPDATE kamar SET status = 'KOSONG' WHERE kd_kamar='$kd_kmr_sbl'");

                          		} else {

                              }

                            ?>

                            <div class="row">
             							   <div class="body">
                                      		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        		<div class="card">
             								  <div class="card-body" style="padding-top:15px;padding-left:15px;">
             								   <h5><?php echo $_GET['nm_pasien'];?></h5>
             								   <?php
             								     $sql ="SELECT
             									a.no_rawat,
             									b.no_rkm_medis,
             									c.nm_pasien,
             									a.tgl_masuk,
             									a.tgl_keluar,
             									a.kd_kamar,
             									d.kd_bangsal,
             									e.nm_bangsal,
             									a.stts_pulang
             								  FROM
             									kamar_inap as a,
             									reg_periksa as b,
             									pasien as c,
             									kamar as d,
             									bangsal as e
             								  WHERE
             									a.no_rawat = b.no_rawat
             									AND
             									b.no_rkm_medis = c.no_rkm_medis
             									AND
             									a.kd_kamar=d.kd_kamar
             									AND
             									d.kd_bangsal=e.kd_bangsal
             									AND
             									a.no_rawat ='$nomor_rawat'
             									";

             									$query=query($sql);
             								    $no=1;
             									while($data=$query->fetch_assoc()){  ?>
             									  <?php echo $no++;?>.
             									  <?php echo $data['nm_bangsal'];?>
             										&nbsp;&nbsp;/&nbsp;&nbsp;<?php echo $data['kd_kamar'];?>
             										&nbsp;&nbsp;/&nbsp;&nbsp;<?php echo $data['tgl_masuk'];?>
             										&nbsp;&nbsp;/&nbsp;&nbsp;<?php if ($data['stts_pulang'] == "-"){echo "Belum Pulang";} else {echo $data['stts_pulang'];};?>
             								        <hr>
             								      <?php }
             								      $no_rkm_medis=$data['no_rkm_medis'];
             								      ?>
             								  </div>
             								  </div>
             								</div>
             								<form method="post" action="" style ="margin-left:5px;margin-right:5px;">
             									<div class="col-sm-3">
             										<div class="form-group">
             											<div class="form-line">
             												<input type="text" class="form-control" name="no_rawat" value="<?php echo $_GET['no_rawat'];?>">
             												<label class="form-label">Nomor Rawat</label>
             											</div>
             										</div>
             									</div>
             									<div class="col-sm-2">
             										<div class="form-group">
             											<div class="form-line">
             												<input type="text" class="form-control" name="no_rkm_medis" value="<?php echo $_GET['no_rkm_medis'];?>">
             												<label class="form-label">Nomor Rekam Medik</label>
             											</div>
             										</div>
             									</div>
             									<div class="col-sm-2">
             										<div class="form-group">
             											<div class="form-line">
             												<input type="text" class="form-control" name="kd_kamar_sebelumnya" value="<?php echo $_GET['kd_kmr_sblmny'];?>">
             												<label class="form-label">Kd Kamar Sebelumnya</label>
             											</div>
             										</div>
             									</div>
             									<div class="col-sm-5">
             										<div class="form-group">
             											<div class="form-line">
             												<input type="text" class="form-control" name="nm_pasien" value="<?php echo $_GET['nm_pasien'];?>">
             												<label class="form-label">Nama Pasien</label>
             											</div>
             										</div>
             									</div>
             									<div class="col-sm-3">
             										<div class="form-group">
             											<div class="form-line">
             												<label class="form-label">Tanggal Masuk</label>
             												<input type="text" name="tgl_masuk" class="datepicker form-control" value="<?php echo date('Y-m-d');?>">
             											</div>
             										</div>
             									</div>
             									<div class="col-sm-2">
             										<div class="form-group">
             											<div class="form-line">
             												<label class="form-label">Jam Masuk</label>
             												<input type="text" name="jam_masuk" class="form-control" value="<?php echo date('H:m:s');?>">
             											</div>
             										</div>
             									</div>
             									<div class="col-sm-2">
             										<div class="form-group">
             											<div class="form-line">
             												<label class="form-label">Diagnosa Awal</label>
             												<input type="text" name="diagnosa_awal" class="form-control" value="<?php
             														$sql_kamar   = "SELECT diagnosa_awal FROM kamar_inap WHERE no_rawat ='{$_GET['no_rawat']}'";
             														$query		 =query($sql_kamar);
             														$data		 =fetch_assoc($query);
             														echo $data['diagnosa_awal'];?> ">
             											</div>
             										</div>
             									</div>
             									<div class="col-sm-5">
             										<div class="form-group">
             											<div class="form-line">
             												<select name="stts_pindah" style="width:100%" class="form-control" id="stts_pindah">
             													<option value="2" selected="selected" >2. Kamar sebelumnya diganti dengan kamar yang baru</option>
             													<option value="3" >3. Kamar sebelumnya distatus pindah</option>
             												</select>
             											</div>
             										</div>
             									</div>
             									<div class="form-group" style="width:97%; margin-left:15px;">
                                                   <select name="kamar" class="form-control kamar" id="kamar" style="width:100%"></select>
                                                    <!--   <br/>
             										<input type="hidden" class="form-control" id="kamar" name="kamar"/> -->
                                                 </div>
                                              	<button type="submit" class="form-control btn bg-indigo waves-effect tombol-simpan" name="simpan">Simpan</button>
                                             </form>
             						        </div>
             						  </div>
                          <?php } ?>

                        <?php
                        if ($action == "delete_pemeriksaan") {
                          $hapus = "DELETE FROM pemeriksaan_ralan WHERE no_rawat='{$_REQUEST['no_rawat']}'";
                          $hasil = query($hapus);
                          if (($hasil)) {
                            redirect("./?module=RawatInap&action=tindakan&no_rawat={$no_rawat}");
                          }
                        }

                        if ($action == "delete_tindakan") {
                          $hapus = "DELETE FROM rawat_jl_pr WHERE kd_jenis_prw='{$_REQUEST['kd_jenis_prw']}' AND no_rawat='{$_REQUEST['no_rawat']}'";
                          $hasil = query($hapus);
                          if (($hasil)) {
                            redirect("./?module=RawatInap&page=index&action=tindakan&no_rawat={$no_rawat}");
                          }
                        }
                        ?>
</div>
<div>

  <div class="modal fade" id="edit_anamneseModal" tabindex="-1" role="dialog" aria-labelledby="edit_anamneseModalLabel" aria-hidden="true">
      <div class="modal-dialog" style="width:800px">
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="edit_anamneseModalLabel">Database Dokter</h4>
              </div>
              <div class="modal-body">

                <?php
                    $row = fetch_assoc(query("SELECT * FROM pemeriksaan_ralan WHERE no_rawat = '{$no_rawat}'"));
                ?>
                <form method="post">
                <div class="row clearfix">
                <div class="col-md-3">
                  <div class="form-group">
                    <div class="form-line">
                      <dt>Edit Keluhan</dt>
                      <dd><textarea rows="4" name="keluhan" class="form-control"><?php echo $row['keluhan']; ?></textarea></dd>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <div class="form-line">
                      <dt>Pemeriksaan</dt>
                      <dd><textarea rows="4" name="pemeriksaan" class="form-control"><?php echo $row['pemeriksaan']; ?></textarea></dd>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <div class="form-line">
                      <dt>Alergi</dt>
                      <dd><input type="text" class="form-control" value="<?php echo $row['alergi']; ?>" name="alergi"></dd>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <div class="form-line">
                      <dt>Tindak Lanjut</dt>
                      <dd><input type="text" class="form-control" value="<?php echo $row['rtl']; ?>" name="tndklnjt"></dd>
                    </div>
                  </div>
                </div>
                </div>
                <div class="row clearfix">
                <div class="col-md-3">
                  <div class="form-group">
                    <div class="form-line">
                      <dt>Suhu Badan (C)</dt>
                      <dd><input type="text" class="form-control" value="<?php echo $row['suhu_tubuh']; ?>" name="suhu"></dd>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <div class="form-line">
                      <dt>Tinggi Badan (Cm)</dt>
                      <dd><input type="text" class="form-control" value="<?php echo $row['tinggi']; ?>" name="tinggi"></dd>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <div class="form-line">
                      <dt>Tensi</dt>
                      <dd><input type="text" class="form-control" value="<?php echo $row['tensi']; ?>" name="tensi"></dd>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <div class="form-line">
                      <dt>Respirasi (per Menit)</dt>
                      <dd><input type="text" class="form-control" value="<?php echo $row['respirasi']; ?>" name="respirasi"></dd>
                    </div>
                  </div>
                </div>
                </div>
                <div class="row clearfix">
                <div class="col-md-3">
                  <div class="form-group">
                    <div class="form-line">
                      <dt>Berat (Kg)</dt>
                      <dd><input type="text" class="form-control" value="<?php echo $row['berat']; ?>" name="berat"></dd>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <div class="form-line">
                      <dt>Nadi (per Menit)</dt>
                      <dd><input type="text" class="form-control" value="<?php echo $row['nadi']; ?>" name="nadi"></dd>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <div class="form-line">
                      <dt>Imun Ke</dt>
                      <dd><input type="text" class="form-control" value="<?php echo $row['imun_ke']; ?>"  name="imun"></dd>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <div class="form-line">
                      <dt>GCS(E , V , M)</dt>
                      <dd><input type="text" class="form-control" value="<?php echo $row['gcs']; ?>" name="gcs"></dd>
                    </div>
                  </div>
                </div>
                </div>
                <div class="row clearfix">
                <div class="col-md-3">
                  <div class="form-group">
                    <dd><button type="submit" name="edit_an" value="edit_an" class="btn bg-indigo waves-effect" onclick="this.value=\'edit_an\'">SIMPAN</button></dd><br/>
                  </div>
                </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
