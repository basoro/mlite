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
	     redirect ('./index.php?module=RawatJalan&page=index');
    }
}

?>
<div class="card">
    <div class="header">
      <h2>Pasien Rawat Jalan</h2>
    </div>
    <div class="body">
                        <?php display_message(); ?>
                        <?php
                        $action = isset($_GET['action'])?$_GET['action']:null;
                        $jenis_poli = isset($_SESSION['jenis_poli'])?$_SESSION['jenis_poli']:null;
                        $role = isset($_SESSION['role'])?$_SESSION['role']:null;
                        if(!$action){
                        ?>
                                <table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Nama Pasien</th>
                                            <th>No. RM</th>
                                            <th width="10%">No.<br>Reg</th>
                                            <th>Tgl. Reg</th>
                                            <th>Jam Reg</th>
                                            <th>Alamat</th>
                                            <th>Jenis<br>Bayar</th>
                                            <th>Poliklinik</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $sql = "SELECT a.nm_pasien, b.no_rkm_medis, a.alamat, c.png_jawab, d.nm_poli, b.no_rawat, b.no_reg, b.tgl_registrasi, b.jam_reg, a.no_tlp FROM pasien a, reg_periksa b, penjab c, poliklinik d WHERE a.no_rkm_medis = b.no_rkm_medis AND b.kd_pj = c.kd_pj AND b.kd_poli = d.kd_poli";
                                    if($role == 'Medis' || $role == 'Paramedis') {
                                      $sql .= " AND b.kd_poli = '$jenis_poli'";
                                    }
                                    if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) {
                                        $sql .= " AND b.tgl_registrasi BETWEEN '$_POST[tgl_awal]' AND '$_POST[tgl_akhir]'";
                                    } else {
                                        $sql .= " AND b.tgl_registrasi = '{$date}'";
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
                                                        <li><a href="./index.php?module=RawatJalan&page=index&action=tindakan&no_rawat=<?php echo $row['5']; ?>">Assesment & Tindakan</a></li>
                                                        <li><a href="./index.php?module=RawatJalan&page=index&action=berkas_digital&no_rawat=<?php echo $row['5']; ?>">Berkas Digital Perawatan</a></li>
                                                        <li><a href="./index.php?module=RawatJalan&page=index&action=radiologi&no_rawat=<?php echo $row['5']; ?>">Berkas Radiologi</a></li>
                                                        <li><a href="./index.php?module=RawatJalan&page=index&action=status_pulang&no_rawat=<?php echo $row['5']; ?>">Status</a></li>
                                                        <?php if(is_dir(ABSPATH.'/modules/SMSGateway/')) { ?>
                                                          <li><a href="./index.php?module=SMSGateway&page=listphone&op=instant_pasien&ph=<?php echo $row['16']; ?>">Kirim SMS</a></li>
                                                        <?php } ?>
                                                        <!--
                                                        <li><a class="trigger right-caret">Another Dropdown</a>
                                                            <ul class="dropdown-menu sub-menu">
                                                              <li><a href="#">Level 3</a></li>
                                                              <li><a href="#">Level 3</a></li>
                                                            </ul>
                                                        </li>
                                                        -->
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

                        <?php } ?>

                        <?php
                        if($action == "tindakan"){
                          if (isset($_POST['ok_tdk'])) {
                                        if (($_POST['kd_tdk'] <> "") and ($no_rawat <> "")) {
                                              $insert = query("INSERT INTO rawat_jl_pr VALUES ('{$no_rawat}','{$_POST['kd_tdk']}','{$_SESSION['username']}','$date','$time','0','0','{$_POST['kdtdk']}','0','0','{$_POST['kdtdk']}','Belum')");
                                              if ($insert) {
                                                  redirect("./index.php?module=RawatJalan&page=index&action=tindakan&no_rawat={$no_rawat}");
                                              };
                                        };
                                  };

                          if(isset($_POST['ok_per'])){
                                if(($no_rawat <> "")){
                                  $insert = query("INSERT INTO pemeriksaan_ralan VALUES ('{$no_rawat}',CURRENT_DATE(),CURRENT_TIME(),'{$_POST['suhu']}','{$_POST['tensi']}','{$_POST['nadi']}','{$_POST['respirasi']}','{$_POST['tinggi']}','{$_POST['berat']}'
                                              ,'{$_POST['gcs']}','{$_POST['keluhan']}','{$_POST['pemeriksaan']}','{$_POST['alergi']}','-','{$_POST['tndklnjt']}','-')");
                                  if($insert){
                                    redirect("./index.php?module=RawatJalan&page=index&action=tindakan&no_rawat={$no_rawat}");
                                  }
                                }
                              };
                          if(isset($_POST['edit_an'])){
                                if(($no_rawat <> "")){
                              	$insert = query("UPDATE pemeriksaan_ralan SET suhu_tubuh = '{$_POST['suhu']}', tensi = '{$_POST['tensi']}', nadi = '{$_POST['nadi']}', respirasi = '{$_POST['respirasi']}', tinggi = '{$_POST['tinggi']}', berat = '{$_POST['berat']}', gcs = '{$_POST['gcs']}', keluhan = '{$_POST['keluhan']}', pemeriksaan = '{$_POST['pemeriksaan']}', alergi = '{$_POST['alergi']}', rtl = '{$_POST['tndklnjt']}' WHERE no_rawat = '{$no_rawat}'");

                                  if($insert){
                                    redirect("./index.php?module=RawatJalan&page=index&action=tindakan&no_rawat={$no_rawat}");
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
                               </div>
                               <div class="row">
                                 <ul class="nav nav-tabs tab-nav-right" role="tablist">
                                   <li role="presentation" class="active"><a href="#riwayat" data-toggle="tab">RIWAYAT</a></li>
                                   <li role="presentation"><a href="#pemeriksaan" data-toggle="tab">PEMERIKSAAN</a></li>
                                   <?php if(is_dir(ABSPATH.'/modules/Odontogram/')) { ?>
                                     <li role="presentation"><a href="#odontogram" data-toggle="tab">ODONTOGRAM</a></li>
                                   <?php } ?>
                                   <li role="presentation"><a href="#tindakan" data-toggle="tab">TINDAKAN</a></li>
                                 </ul>
                               </div>
                               <!-- End Nav Tabs -->
                               <button class="btn bg-cyan waves-effect m-t-15 m-b-15" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">Berkas RM Lama</button>
                               <div class="collapse" id="collapseExample">
                                 <div class="well">
                                             <div id="animated-thumbnails" class="list-unstyled row clearfix">
                                             <?php
                                             $sql_rmlama = query("SELECT * FROM berkas_digital_perawatan WHERE kode = '003' AND no_rawat IN (SELECT no_rawat FROM reg_periksa WHERE no_rkm_medis = '$no_rkm_medis')");
                                             $no=1;
                                             while ($row_rmlama = fetch_array($sql_rmlama)) {
                                                 echo '<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">';
                                                 echo '<a href="'.URLSIMRS.'/berkasrawat/'.$row_rmlama[2].'" data-sub-html=""><img class="img-responsive thumbnail"  src="'.URLSIMRS.'/berkasrawat/'.$row_rmlama[2].'"></a>';
                                                 echo '</div>';
                                                 $no++;
                                             }
                                             ?>
                                             <?php
                                             $sql_rmlama = query("SELECT * FROM berkas_digital_perawatan WHERE kode = '006' AND no_rawat IN (SELECT no_rawat FROM reg_periksa WHERE no_rkm_medis = '$no_rkm_medis')");
                                             $no=1;
                                             while ($row_rmlama = fetch_array($sql_rmlama)) {
                                                 echo '<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">';
                                                 echo '<a href="'.URLSIMRS.'/berkasrawat/'.$row_rmlama[2].'" data-sub-html=""><img class="img-responsive thumbnail"  src="'.URLSIMRS.'/berkasrawat/'.$row_rmlama[2].'"></a>';
                                                 echo '</div>';
                                                 $no++;
                                             }
                                             ?>
                                             </div>
                                 </div>
                               </div>
                               <div class="clearfix"></div>

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
                                           <th>Laboratorium</th>
                                           <th>Radiologi</th>
                                           <th>Catatan Rawat</th>
                                         </tr>
                                       </thead>
                                       <tbody>
                                         <?php
                                         $q_kunj = query ("SELECT tgl_registrasi, no_rawat, status_lanjut FROM reg_periksa WHERE no_rkm_medis = '$no_rkm_medis' AND stts !='Batal'");
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
                                               $sql_riksaralan = fetch_assoc(query("SELECT keluhan, pemeriksaan, tinggi, berat, suhu_tubuh, tensi, nadi, respirasi FROM pemeriksaan_ralan WHERE no_rawat = '$no_rawat_kunj'"));
                                               echo "<td>".$sql_riksaralan['keluhan']."</td>";
                                               echo "<td>";
                                               echo "<ul style='list-style:none;margin:0;padding:0;'>";
                                               echo "<li>".$sql_riksaralan['pemeriksaan']."</li>";
                                               if(!empty($sql_riksaralan['tinggi'])) {
                                               echo "<li>Tinggi : ".$sql_riksaralan['tinggi']." cm</li>";
                                               }
                                               if(!empty($sql_riksaralan['berat'])) {
                                                 echo "<li>BB : ".$sql_riksaralan['berat']." Kg</li>";
                                               }
                                               if(!empty($sql_riksaralan['suhu_tubuh'])) {
                                               echo "<li>Suhu : ".$sql_riksaralan['suhu_tubuh']." C</li>";
                                               }
                                               if(!empty($sql_riksaralan['tensi'])) {
                                               echo "<li>Tensi : ".$sql_riksaralan['tensi']." mmHg</li>";
                                               }
                                               if(!empty($sql_riksaralan['nadi'])) {
                                               echo "<li>Nadi : ".$sql_riksaralan['nadi']." x/mnt</li>";
                                               }
                                               if(!empty($sql_riksaralan['respirasi'])) {
                                               echo "<li>RR : ".$sql_riksaralan['respirasi']." x/mnt</li>";
                                               }
                                               echo "</ul>";
                                               echo "</td>";
                                             } else {
                                               $sql_riksaranap = fetch_assoc(query("SELECT keluhan, pemeriksaan , berat , suhu_tubuh FROM pemeriksaan_ranap WHERE no_rawat = '$no_rawat_kunj'"));
                                               echo "<td>".$sql_riksaranap['keluhan']."</td>";
                                               echo "<td><ul style='list-style:none;margin:0;padding:0;'><li>Pemeriksaan=".$sql_riksaranap['pemeriksaan']."</li></br><li>BB=".$sql_riksaranap['berat']."</li></br><li>Suhu=".$sql_riksaranap['suhu_tubuh']."</li></ul></td>";
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

                         <ul style="list-style:none;margin-left:0;padding-left:0;">
                           <?php
                           $query = query("SELECT a.kode, b.deskripsi_pendek, a.prioritas FROM prosedur_pasien a, icd9 b, reg_periksa c WHERE a.kode = b.kode AND a.no_rawat = '{$no_rawat_kunj}' AND a.no_rawat = c.no_rawat ORDER BY a.prioritas ASC");
                             $no=1;
                           if(num_rows($query) !== 0){
                           	echo '<li><b>Prosedur ICD 9</b></li>';
                           }
                            while ($data = fetch_array($query)) {
                           ?>
                                     <li><?php echo $no; ?>. <?php echo $data['0']; ?> - <?php echo $data['1']; ?></li>
                           <?php
                                 $no++;
                           }
                           ?>
                         </ul>
                         <ul style="list-style:none;margin-left:0;padding-left:0;">
                           <?php
                           if($status_lanjut_kunj == 'Ralan') {
                             $query = query("SELECT a.kd_jenis_prw, b.nm_perawatan FROM rawat_jl_dr a, jns_perawatan b, reg_periksa c WHERE a.kd_jenis_prw = b.kd_jenis_prw AND a.no_rawat = '{$no_rawat_kunj}' AND a.no_rawat = c.no_rawat");
                               $no=1;
                             if(num_rows($query) !== 0){
                             	echo '<li><b>Jenis Perawatan Dokter</b></li>';
                             }
                             while ($data = fetch_array($query)) {
                             ?>
                                       <li><?php echo $no; ?>. <?php echo $data['0']; ?> - <?php echo $data['1']; ?></li>
                             <?php
                                   $no++;
                             }
                             $query2 = query("SELECT a.kd_jenis_prw, b.nm_perawatan FROM rawat_jl_pr a, jns_perawatan b, reg_periksa c WHERE a.kd_jenis_prw = b.kd_jenis_prw AND a.no_rawat = '{$no_rawat_kunj}' AND a.no_rawat = c.no_rawat");
                               $no=1;
                             if(num_rows($query2) !== 0){
                             	echo '<li><b>Jenis Perawatan Perawat</b></li>';
                             }
                             while ($data = fetch_array($query2)) {
                             ?>
                                       <li><?php echo $no; ?>. <?php echo $data['0']; ?> - <?php echo $data['1']; ?></li>
                             <?php
                                   $no++;
                             }
                             $query3 = query("SELECT a.kd_jenis_prw, b.nm_perawatan FROM rawat_jl_drpr a, jns_perawatan b, reg_periksa c WHERE a.kd_jenis_prw = b.kd_jenis_prw AND a.no_rawat = '{$no_rawat_kunj}' AND a.no_rawat = c.no_rawat");
                               $no=1;
                             if(num_rows($query3) !== 0){
                             	echo '<li><b>Jenis Perawatan Perawat & Dokter</b></li>';
                             }
                             while ($data = fetch_array($query3)) {
                             ?>
                                       <li><?php echo $no; ?>. <?php echo $data['0']; ?> - <?php echo $data['1']; ?></li>
                             <?php
                                   $no++;
                             }
                           } else {
                             $query = query("SELECT a.kd_jenis_prw, b.nm_perawatan FROM rawat_inap_dr a, jns_perawatan_inap b, reg_periksa c WHERE a.kd_jenis_prw = b.kd_jenis_prw AND a.no_rawat = '{$no_rawat_kunj}' AND a.no_rawat = c.no_rawat");
                               $no=1;
                             if(num_rows($query) !== 0){
                             	echo '<li><b>Jenis Perawatan Dokter</b></li>';
                             }
                             while ($data = fetch_array($query)) {
                             ?>
                                       <li><?php echo $no; ?>. <?php echo $data['0']; ?> - <?php echo $data['1']; ?></li>
                             <?php
                                   $no++;
                             }
                             $query2 = query("SELECT a.kd_jenis_prw, b.nm_perawatan FROM rawat_inap_pr a, jns_perawatan_inap b, reg_periksa c WHERE a.kd_jenis_prw = b.kd_jenis_prw AND a.no_rawat = '{$no_rawat_kunj}' AND a.no_rawat = c.no_rawat");
                               $no=1;
                             if(num_rows($query2) !== 0){
                             	echo '<li><b>Jenis Perawatan Perawat</b></li>';
                             }
                             while ($data = fetch_array($query2)) {
                             ?>
                                       <li><?php echo $no; ?>. <?php echo $data['0']; ?> - <?php echo $data['1']; ?></li>
                             <?php
                                   $no++;
                             }
                             $query3 = query("SELECT a.kd_jenis_prw, b.nm_perawatan FROM rawat_inap_drpr a, jns_perawatan_inap b, reg_periksa c WHERE a.kd_jenis_prw = b.kd_jenis_prw AND a.no_rawat = '{$no_rawat_kunj}' AND a.no_rawat = c.no_rawat");
                               $no=1;
                             if(num_rows($query3) !== 0){
                             	echo '<li><b>Jenis Perawatan Perawat & Dokter</b></li>';
                             }
                             while ($data = fetch_array($query3)) {
                             ?>
                                       <li><?php echo $no; ?>. <?php echo $data['0']; ?> - <?php echo $data['1']; ?></li>
                             <?php
                                   $no++;
                             }
                           }
                           ?>
                         </ul>

                                           </td>
                                           <td>
                                               <ul style="list-style:none;">
                                               <?php
                                               //$sql_obat = query("select detail_pemberian_obat.jml, databarang.nama_brng, resep_dokter.aturan_pakai from detail_pemberian_obat inner join databarang on detail_pemberian_obat.kode_brng=databarang.kode_brng inner join resep_obat on detail_pemberian_obat.no_rawat=resep_obat.no_rawat inner join resep_dokter on resep_dokter.no_resep=resep_obat.no_resep where detail_pemberian_obat.no_rawat= '$no_rawat_kunj'");
                                               $sql_obat = query("select detail_pemberian_obat.jml, databarang.nama_brng, detail_pemberian_obat.no_rawat, databarang.kode_brng from detail_pemberian_obat inner join databarang on detail_pemberian_obat.kode_brng=databarang.kode_brng where detail_pemberian_obat.no_rawat= '$no_rawat_kunj'");
                                               //$sql_obat = query("SELECT databarang.nama_brng, resep_dokter.jml, resep_dokter.aturan_pakai FROM resep_dokter, resep_obat, databarang WHERE resep_dokter.no_resep = resep_obat.no_resep AND resep_dokter.kode_brng = databarang.kode_brng AND resep_obat.no_rawat = '$no_rawat_kunj'");
                                               $no=1;
                                               while ($row_obat = fetch_array($sql_obat)) {
                                                   $get_aturan = fetch_assoc(query("SELECT resep_dokter.aturan_pakai AS aturan FROM resep_dokter, resep_obat WHERE resep_dokter.no_resep = resep_obat.no_resep AND resep_obat.no_rawat = '$row_obat[2]' AND resep_dokter.kode_brng = '{$row_obat['3']}'"));
                                                   echo '<li>'.$no.'. '.$row_obat[1].' - '.$get_aturan[aturan].' ('.$row_obat[0].')</li>';
                                                   //echo '<li>'.$no.'. '.$row_obat[1].' ('.$row_obat[0].')</li>';
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
                                               <div id="animated-thumbnails" class="list-unstyled row clearfix">
                                               <?php
                                               $sql_lab = query("select * from berkas_digital_perawatan where kode = '005' and no_rawat = '$no_rawat_kunj'");
                                               $no=1;
                                               while ($row_lab = fetch_array($sql_lab)) {
                                                   echo '<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">';
                                                   echo '<a href="'.$_SERVER['PHP_SELF'].'?action=laboratorium&no_rawat='.$no_rawat_kunj.'" class="title"><img class="img-responsive thumbnail"  src="'.URLSIMRS.'/berkasrawat/'.$row_lab[2].'"></a>';
                                                   echo '</div>';
                                                   $no++;
                                               }
                                               ?>
                                             </div>
                                           </td>
                                           <td>
                                               <div id="animated-thumbnails" class="list-unstyled row clearfix">
                                               <?php
                                               $sql_rad = query("select * from gambar_radiologi where no_rawat= '$no_rawat_kunj'");
                                               $no=1;
                                               while ($row_rad = fetch_array($sql_rad)) {
                                                   echo '<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">';
                                                   echo '<a href="'.$_SERVER['PHP_SELF'].'?action=radiologi&no_rawat='.$no_rawat_kunj.'" class="title"><img class="img-responsive thumbnail"  src="'.URLSIMRS.'/radiologi/'.$row_rad[3].'"></a>';
                                                   echo '</div>';
                                                   $no++;
                                               }
                                               ?>
                                             </div>
                                           </td>
                                           <td>
                   	                    <?php
                       	                $query = query("SELECT catatan FROM catatan_perawatan WHERE no_rawat = '{$no_rawat_kunj}'");
                           	            while ($data = fetch_array($query)) {
                               	        ?>
                                                 <?php echo nl2br($data['catatan']); ?>
                                   	    <?php
                                       	}
                                       	?>
                                           </td>
                                         </tr>
                                         <?php } ?>
                                       </tbody>
                                     </table>
                                   </div>
                                   <!-- riwayat -->
                                   <div role="tabpanel" class="tab-pane fade in" id="pemeriksaan">
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
                                               <td><a href="#" data-toggle="modal" data-target="#edit_anamneseModal">Edit</a> <a href="./index.php?module=RawatJalan&action=delete_pemeriksaan&no_rawat=<?php echo $no_rawat; ?>">Hapus</a></td>
                                             </tr>
                                         <?php
                                         }
                                         ?>
                                         </tbody>
                                     </table>
                                     </div>
                                   </div>
                                   <!-- odontogram -->
                                   <?php if(is_dir(ABSPATH.'/modules/Odontogram/')) { ?>
                                   <div class="tab-pane fade" role="tabpanel" id="odontogram">
                                     <?php include('modules/Odontogram/inc/history.php'); ?>
                                   </div>
                                   <?php } ?>
                                   <!-- end odontogram -->
                                   <div role="tabpanel" class="tab-pane fade in" id="tindakan">
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
                                                 <td><a href="./index.php?module=RawatJalan&page=index&action=delete_tindakan&kd_jenis_prw=<?php echo $data_tindakan['0']; ?>&no_rawat=<?php echo $no_rawat; ?>">Hapus</a></td>
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

                          <?php if($action == "berkas_digital") { ?>
                          <?php
                            if (isset($_POST['ok_berdig'])) {
                              $periksa_radiologi = fetch_assoc(query("SELECT tgl_periksa, jam FROM periksa_radiologi WHERE no_rawat = '{$no_rawat}'"));
                              $date = $periksa_radiologi['tgl_periksa'];
                              $time = $periksa_radiologi['jam'];
                              if($_FILES['file']['name']!=='') {
                                $tmp_name = $_FILES["file"]["tmp_name"];
                                $namefile = $_FILES["file"]["name"];
                                $explode = explode(".", $namefile);
                                $ext = end($explode);
                                if($_POST['masdig']=='001') {
                                    $image_name = "berkasdigital-".time().".".$ext;
                                }else{
                                    $image_name = "rujukanfktp-".time().".".$ext;
                                }
                                move_uploaded_file($tmp_name,WEBAPPS."/berkasrawat/pages/upload/".$image_name);
                                $lokasi_berkas = 'pages/upload/'.$image_name;
                                $insert_berkas = query("INSERT INTO berkas_digital_perawatan VALUES('$no_rawat','{$_POST['masdig']}', '$lokasi_berkas')");
                                if($insert_berkas) {
                                  set_message('Berkas digital perawatan telah ditersimpan.');
                                  redirect("./index.php?module=RawatJalan&page=index");
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
                              <div id="animated-thumbnails" class="list-unstyled row clearfix">
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
                                          <img id="image_upload_preview" width="200px" src="<?php echo URL; ?>/modules/RawatJalan/images/upload_berkas.png" onclick="upload_berkas()" style="cursor:pointer;" />
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
                              move_uploaded_file($tmp_name,WEBAPPS."/radiologi/pages/upload/".$image_name);
                              $lokasi_berkas = 'pages/upload/'.$image_name;
                              $insert_berkas = query("INSERT INTO gambar_radiologi VALUES('$no_rawat', '$date', '$time', '$lokasi_berkas')");
                                if($insert_berkas) {
                                set_message('Berkas digital radiologi telah ditersimpan.');
                                    redirect("./index.php?module=RawatJalan&page=index&action=radiologi&no_rawat=$no_rawat");
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
                                            <div id="animated-thumbnails" class="list-unstyled row clearfix">
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
                                          <img id="image_upload_preview" width="200px" src="./modules/RawatJalan/images/upload_berkas.png" onclick="upload_berkas()" style="cursor:pointer;" />
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
                            <?php if(isset($_POST['ok_status_pulang'])){
        								if($_POST['stts_pulang'] == "Dirawat"){
                                        $sql = query("INSERT INTO `kamar_inap` (`no_rawat`, `kd_kamar`, `trf_kamar`, `diagnosa_awal`, `diagnosa_akhir`, `tgl_masuk`,
                                        `jam_masuk`, `tgl_keluar`, `jam_keluar`, `lama`, `ttl_biaya`, `stts_pulang`) VALUES ('{$_POST['no_rawat']}','{$_POST['kamar']}',
                                        '{$_POST['hrgkmr']}','{$_POST['dx']}','-','{$_POST['tgl']}','$time','0000-00-00','00:00:00','0','0','-')");
                                         if($sql){$update = query("UPDATE kamar SET status = 'ISI' WHERE kd_kamar = '".$_POST['kamar']."'");
                                                  $regs = query("UPDATE reg_periksa SET stts = '".$_POST['stts_pulang']."' WHERE no_rawat = '".$_POST['no_rawat']."'");
                                      			}
                                      }else{
                                        $status = query("UPDATE reg_periksa SET stts = '".$_POST['stts_pulang']."' WHERE no_rawat = '".$_POST['no_rawat']."'");
                                         }}?>
                                <form method="POST">
                                    <div class="form-group">
                                      <div class="form-line">
                                        <label for="stts_pulang">Status</label>
                                        <select name="stts_pulang" id="stts_pulang"class="form-control show-tick">
                                        <?php
                                        $no_rawat = $_GET['no_rawat'];
                                        $result = query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'reg_periksa' AND COLUMN_NAME = 'stts'");
                                        $row = fetch_array($result);
                                        $enumList = explode(",", str_replace("'", "", substr($row['COLUMN_TYPE'], 5, (strlen($row['COLUMN_TYPE'])-6))));
                                        foreach($enumList as $value) {
                                            echo "<option value='$value'>$value</option>";
                                        }
                                        ?>
                                        </select>
                                      </div>
                                    </div>
                                    <div class="form-group">
                                      <div class="form-line">
                                          <label for="kamar">Kamar</label>
                                          <select name="kamar" class="form-control kamar" id="kamar" style="width:100%"></select>
                                                  <br/>
                                          <input type="hidden" class="form-control" id="hrgkmr" name="hrgkmr"/>
                                      </div>
                                    </div>
                                    <div class="form-group">
                                      <div class="form-line">
                                          <label for="dx">Diagnosa</label>
                                          <input type="text" class="form-control" name="dx" value="">
                                      </div>
                                    </div>
                                    <div class="form-group">
                                      <div class="form-line">
                                          <label for="tglplg">Tanggal</label>
                                          <input type="text" class="datepicker form-control" name="tgl" value="<?php echo date('Y-m-d'); ?>">
                                      </div>
                                    </div>
                                      <input type="hidden" name="no_rawat" value="<?php echo $no_rawat;?>">
                                      <button type="submit"  name="ok_status_pulang" value="ok_status_pulang"  class="btn btn-success waves-effect" onclick="this.value=\'ok_status_pulang\'">SIMPAN</button>
                                </form>
                          <?php } ?>

                        <?php
                        //delete
                        if($action == "delete_diagnosa"){
                              $hapus = "DELETE FROM diagnosa_pasien WHERE no_rawat='{$_REQUEST['no_rawat']}' AND kd_penyakit = '{$_REQUEST['kode']}' AND prioritas = '{$_REQUEST['prioritas']}'";
                              $hasil = query($hapus);
                              if (($hasil)) {
                                  redirect("./index.php?module=RawatJalan&page=index&action=view&no_rawat={$no_rawat}");
                              }
                        }

                        //delete
                        if($action == "delete_obat"){
                              $hapus = "DELETE FROM resep_dokter WHERE no_resep='{$_REQUEST['no_resep']}' AND kode_brng='{$_REQUEST['kode_obat']}'";
                              $hasil = query($hapus);
                              if (($hasil)) {
                              redirect("./index.php?module=RawatJalan&page=index&action=view&no_rawat={$no_rawat}");
                              }
                        }
                        if ($action == "delete_pemeriksaan") {
                          $hapus = "DELETE FROM pemeriksaan_ralan WHERE no_rawat='{$_REQUEST['no_rawat']}'";
                          $hasil = query($hapus);
                          if (($hasil)) {
                            redirect("./index.php?module=RawatJalan&action=tindakan&no_rawat={$no_rawat}");
                          }
                        }

                        if ($action == "delete_tindakan") {
                          $hapus = "DELETE FROM rawat_jl_pr WHERE kd_jenis_prw='{$_REQUEST['kd_jenis_prw']}' AND no_rawat='{$_REQUEST['no_rawat']}'";
                          $hasil = query($hapus);
                          if (($hasil)) {
                            redirect("./index.php?module=RawatJalan&page=index&action=tindakan&no_rawat={$no_rawat}");
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
