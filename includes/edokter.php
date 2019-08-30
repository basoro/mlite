<!-- Menu View -->
      <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="header">
              <h2>Detail Pasien</h2>
            </div>
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
            <div class="body">
              <!-- Nav Tabs -->
              <div class="row">
                <ul class="nav nav-tabs tab-nav-right" role="tablist">
                  <li role="presentation" class="active"><a href="#riwayat" data-toggle="tab">RIWAYAT</a></li>
                  <!--<li role="presentation"><a href="#anamnese" data-toggle="tab">PEMERIKSAAN</a></li>-->
                  <li role="presentation"><a href="#diagnosa" data-toggle="tab">DIAGNOSA</a></li>
                  <li role="presentation"><a href="#resep" data-toggle="tab">RESEP</a></li>
                  <li role="presentation"><a href="#permintaanlab" data-toggle="tab">PERMINTAAN LAB</a></li>
                  <li role="presentation"><a href="#permintaanrad" data-toggle="tab">PERMINTAAN RAD</a></li>
                  <li role="presentation"><a href="#skdp" data-toggle="tab">SURAT KONTROL</a></li>
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
                        <th>Laboratorium</th>
                        <th>Obat</th>
                        <th>Radiologi</th>
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
                            <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                            <?php
                            $sql_rad = query("select * from gambar_radiologi where no_rawat= '$no_rawat_kunj'");
                            $no=1;
                            while ($row_rad = fetch_array($sql_rad)) {
                                echo '<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">';
                                echo '<a href="'.$_SERVER['PHP_SELF'].'?action=radiologi&no_rawat='.$no_rawat_kunj.'" class="title"><img class="img-responsive thumbnail"  src="'.SIMRSURL.'/radiologi/'.$row_rad[3].'"></a>';
                                echo '</div>';
                                $no++;
                            }
                            ?>
                          </div>
                        </td>
                      </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>
                <!-- riwayat -->
                <!-- anamnese -->
                  <div class="tab-pane fade" role="tabpanel" id="anamnese">
                    <?php include_once ('module/anamnese.php');?>
                  </div>
                <!-- anamnese -->
                <!-- diagnosa -->
                  <div role="tabpanel" class="tab-pane fade" id="diagnosa">
                    <?php include_once ('module/diagnosa.php');?>
                  </div>

                <!-- end diagnosa -->
                <!-- eresep -->
                  <div role="tabpanel" class="tab-pane fade" id="resep">
                    <?php include_once ('module/eresep.php');?>
                  </div>
                <!-- end eresep -->
                <!-- permintaan lab -->
                  <div role="tabpanel" class="tab-pane fade" id="permintaanlab">
                    <?php include_once ('module/mintalab.php');?>
                  </div>
                <!-- end permintaan lab -->
                <!-- permintaan rad -->
                  <div role="tabpanel" class="tab-pane fade" id="permintaanrad">
                    <?php include_once ('module/mintarad.php');?>
                  </div>
                <!-- end permintaan rad -->
                <!-- skdp -->
                  <div role="tabpanel" class="tab-pane fade" id="skdp">
                    <?php include_once ('module/skdp.php');?>
                  </div>
                <!-- end skdp -->
              </div>
              <!-- Tab Panes -->
            </div>
          </div>
      </div>
    <!-- Menu View -->