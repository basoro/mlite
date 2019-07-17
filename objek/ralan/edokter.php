							<div class="tab-content m-t-20">
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
                                  <?php
                                  }
                                  ?>
                                  </tbody>
                                  </table>

                                </div>
                                <div class="tab-pane fade" role="tabpanel" id="anamnese">
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
                                        <dd><button type="submit" name="ok_an" value="ok_an" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_an\'">OK</button></dd><br/>
                                      </div>
                                    </div>
                                  </div>
                                    <div class="row clearfix">
                                      <table class="table striped">
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
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="diagnosa">
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
                                      <dd><button type="submit" name="ok_diagnosa" value="ok_diagnosa" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_diagnosa\'">OK</button></dd><br/>
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
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="resep">
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
                                      <dd><button type="submit" name="ok_obat" value="ok_obat" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_obat\'">OK</button></dd><br>
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
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="permintaanlab">
                                  <dl class="dl-horizontal">
                                      <dt>Jenis Pemeriksaan</dt>
                                      <dd><select name="kd_jenis_prw_lab[]" class="kd_jenis_prw_lab" multiple="multiple" style="width:100%"></select></dd><br/>
                                      <dt></dt>
                                      <dd><button type="submit" name="ok_lab" value="ok_lab" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_lab\'">OK</button></dd><br/>
                                      <dt></dt>
                                      <dd>
      	                        		<ul style="list-style:none;margin-left:0;padding-left:0;">
      	                    		    <?php
      	                    		    $query = query("SELECT c.kd_jenis_prw, d.nm_perawatan, c.noorder FROM  reg_periksa a, permintaan_lab b, permintaan_pemeriksaan_lab c, jns_perawatan_lab d  WHERE a.no_rawat = '{$no_rawat}' AND a.no_rawat = b.no_rawat AND b.noorder = c.noorder AND c.kd_jenis_prw = d.kd_jenis_prw");
                                  		$no=1;
      	                    		    while ($data = fetch_array($query)) {
      	                    		    ?>
              	                              <li><?php echo $no; ?>. <?php echo $data['1']; ?> <a class="btn btn-danger btn-xs" href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_lab&kd_jenis_prw=<?php echo $data['0']; ?>&noorder=<?php echo $data['2']; ?>&no_rawat=<?php echo $no_rawat; ?>">[X]</a></li>
      	                    		    <?php
                                      		$no++;
      	                        		}
      	                        		?>
      	                        		</ul>
                                      </dd>
                                  </dl>
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="permintaanrad">
                                  <dl class="dl-horizontal">
                                      <dt>Jenis Pemeriksaan</dt>
                                      <dd><select name="kd_jenis_prw_rad[]" class="kd_jenis_prw_rad" multiple="multiple" style="width:100%"></select></dd><br/>
                                      <dt></dt>
                                      <dd><button type="submit" name="ok_rad" value="ok_rad" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_rad\'">OK</button></dd><br/>
                                      <dt></dt>
                                      <dd>
      	                        		<ul style="list-style:none;margin-left:0;padding-left:0;">
      	                    		    <?php
                                    $query = query("SELECT c.kd_jenis_prw, d.nm_perawatan, c.noorder FROM  reg_periksa a, permintaan_radiologi b, permintaan_pemeriksaan_radiologi c, jns_perawatan_radiologi d  WHERE a.no_rawat = '{$no_rawat}' AND a.no_rawat = b.no_rawat AND b.noorder = c.noorder AND c.kd_jenis_prw = d.kd_jenis_prw");
                                  		$no=1;
      	                    		    while ($data = fetch_array($query)) {
      	                    		    ?>
              	                              <li><?php echo $no; ?>. <?php echo $data['1']; ?> <a class="btn btn-danger btn-xs" href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_rad&kd_jenis_prw=<?php echo $data['0']; ?>&noorder=<?php echo $data['2']; ?>&no_rawat=<?php echo $no_rawat; ?>">[X]</a></li>
      	                    		    <?php
                                      		$no++;
      	                        		}
      	                        		?>
      	                        		</ul>
                                      </dd>
                                  </dl>
                                </div>
                            </div>
<?php

    //delete
    if($action == "delete_diagnosa"){

	$hapus = "DELETE FROM diagnosa_pasien WHERE no_rawat='{$_REQUEST['no_rawat']}' AND kd_penyakit = '{$_REQUEST['kode']}' AND prioritas = '{$_REQUEST['prioritas']}'";
	$hasil = query($hapus);
	if (($hasil)) {
	    redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}");
	}

    }

    //delete
    if($action == "delete_obat"){

	$hapus = "DELETE FROM resep_dokter WHERE no_resep='{$_REQUEST['no_resep']}' AND kode_brng='{$_REQUEST['kode_obat']}'";
	$hasil = query($hapus);
	if (($hasil)) {
	    redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}");
	}

    }

    //delete
    if($action == "delete_lab"){

	$hapus = "DELETE FROM permintaan_pemeriksaan_lab WHERE noorder='{$_REQUEST['noorder']}' AND kd_jenis_prw='{$_REQUEST['kd_jenis_prw']}'";
	$hasil = query($hapus);
	if (($hasil)) {
	    redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}");
	}

    }

    //delete
    if($action == "delete_rad"){

	$hapus = "DELETE FROM permintaan_pemeriksaan_radiologi WHERE noorder='{$_REQUEST['noorder']}' AND kd_jenis_prw='{$_REQUEST['kd_jenis_prw']}'";
	$hasil = query($hapus);
	if (($hasil)) {
	    redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}");
	}

    }

    if($action == "delete_an"){

    $hapus = "DELETE FROM pemeriksaan_ralan WHERE no_rawat='{$_REQUEST['no_rawat']}' AND keluhan='{$_REQUEST['keluhan']}'";
    $hasil = query($hapus);
    if (($hasil)) {
      redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}");
    }

    }

    ?>