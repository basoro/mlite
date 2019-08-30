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
            <th>Tanggal<br/>Tindakan</th>
            <th>Biaya</th>
            <th>Tools</th>
          </tr>
        </thead>
        <tbody>
        <?php
          $query_tindakan = query("SELECT rawat_inap_pr.kd_jenis_prw, rawat_inap_pr.tgl_perawatan, rawat_inap_pr.biaya_rawat, jns_perawatan_inap.nm_perawatan  FROM rawat_inap_pr, jns_perawatan_inap WHERE rawat_inap_pr.kd_jenis_prw = jns_perawatan_inap.kd_jenis_prw AND rawat_inap_pr.no_rawat = '{$no_rawat}'");
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

  <div role="tabpanel" class="tab-pane fade in" id="hais">
    <div class="body">
      <form method="POST">
        <?php 
        	if (isset($_POST['ok_hais'])) {
            	if (($_POST['tgl'] <> "") and ($no_rawat <> "")) {
                	$insert = query("INSERT INTO data_HAIs VALUES ('{$_POST['tgl']}','{$no_rawat}','{$_POST['dpjp']}','{$_POST['ett']}','{$_POST['cvl']}','{$_POST['ivl']}','{$_POST['uc']}'
                    ,'{$_POST['vap']}','{$_POST['iad']}','{$_POST['pleb']}','{$_POST['isk']}','{$_POST['ilo']}','{$_POST['hap']}','{$_POST['tinea']}','{$_POST['scab']}','{$_POST['deku']}'
                    ,'{$_POST['sputum']}','{$_POST['darah']}','{$_POST['urine']}','{$_POST['anti']}')");
                    if ($insert) {
                    	redirect("pasien-ranap.php?action=tindakan&no_rawat={$no_rawat}#dpjp");
                    };
                };
            };
        ?>
        <div class="row clearfix">
          <div class="col-md-3">
            <div class="form-group">
              <div class="form-line">
                <dt>Sputum</dt>
                <dd><input type="text" class="form-control" name="sputum"></dd>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <div class="form-line">
                <dt>Darah</dt>
                <dd><input type="text" class="form-control" name="darah"></dd>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <div class="form-line">
                <dt>Urine</dt>
                <dd><input type="text" class="form-control" name="alergi"></dd>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <div class="form-line">
                <dt>Antibiotik</dt>
                <dd><input type="text" class="form-control" name="anti"></dd>
              </div>
            </div>
          </div>
        </div>
        
        <div class="row clearfix">
          <div class="col-md-1">
            <div class="form-group">
              <div class="form-line">
                <dt>ETT</dt>
                <dd><input type="text" class="form-control" name="ett"></dd>
              </div>
            </div>
          </div>
          <div class="col-md-1">
            <div class="form-group">
              <div class="form-line">
                <dt>CVL</dt>
                <dd><input type="text" class="form-control" name="cvl"></dd>
              </div>
            </div>
          </div>
          <div class="col-md-1">
            <div class="form-group">
              <div class="form-line">
                <dt>IVL</dt>
                <dd><input type="text" class="form-control" name="ivl"></dd>
              </div>
            </div>
          </div>
          <div class="col-md-1">
            <div class="form-group">
              <div class="form-line">
                <dt>UC</dt>
                <dd><input type="text" class="form-control" name="uc"></dd>
              </div>
            </div>
          </div>
          <div class="col-md-1">
            <div class="form-group">
              <div class="form-line">
                <dt>VAP</dt>
                <dd><input type="text" class="form-control" name="vap"></dd>
              </div>
            </div>
          </div>
          <div class="col-md-1">
            <div class="form-group">
              <div class="form-line">
                <dt>IAD</dt>
                <dd><input type="text" class="form-control" name="iad"></dd>
              </div>
            </div>
          </div>
          <div class="col-md-1">
            <div class="form-group">
              <div class="form-line">
                <dt>PLEB</dt>
                <dd><input type="text" class="form-control" name="pleb"></dd>
              </div>
            </div>
          </div>
          <div class="col-md-1">
            <div class="form-group">
              <div class="form-line">
                <dt>ISK</dt>
                <dd><input type="text" class="form-control" name="isk"></dd>
              </div>
            </div>
          </div>
          <div class="col-md-1">
            <div class="form-group">
              <div class="form-line">
                <dt>ILO</dt>
                <dd><input type="text" class="form-control" name="ilo"></dd>
              </div>
            </div>
          </div>
          <div class="col-md-1">
            <div class="form-group">
              <div class="form-line">
                <dt>HAP</dt>
                <dd><input type="text" class="form-control" name="hap"></dd>
              </div>
            </div>
          </div>
          <div class="col-md-1">
            <div class="form-group">
              <div class="form-line">
                <dt>Tinea</dt>
                <dd><input type="text" class="form-control" name="tinea"></dd>
              </div>
            </div>
          </div>
          <div class="col-md-1">
            <div class="form-group">
              <div class="form-line">
                <dt>Scabies</dt>
                <dd><input type="text" class="form-control" name="scab"></dd>
              </div>
            </div>
          </div>
        </div>
        
        <div class="row clearfix">
          <div class="col-md-1">
            <div class="form-group">
              <div class="form-line">
                <dt>Bed</dt>
                <dd><input type="text" class="form-control" name="bed" value=""></dd>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <dt>Tanggal</dt>
                <dd><input type="text" class="datepicker form-control" name="tgl"></dd>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <dt>Deku</dt>
                <dd><select type="text" class="form-control" name="deku"><option value="IYA">IYA</option><option value="TIDAK">TIDAK</option></select></dd>
              </div>
            </div>
          </div>
        </div>
        
        <button type="submit" name="ok_hais" value="ok_hais" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_hais\'">SIMPAN</button>
      </form>
    </div>
    
    <div class="body">
      <table id="hais" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Kamar</th>
            <th>Tools</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $query_tindakan = query("SELECT * FROM data_HAIs WHERE no_rawat = '{$no_rawat}'");
        while ($data_tindakan = fetch_array($query_tindakan)) {
        ?>
          <tr>
            <td><?php echo $data_tindakan['tanggal']; ?></td>
            <td><?php echo $data_tindakan['kd_kamar']; ?></td>
            <td><a href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_pemeriksaan&keluhan=<?php echo $data_tindakan['keluhan']; ?>&no_rawat=<?php echo $no_rawat; ?>">Hapus</a></td>
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
        $query_tindakan = query("SELECT * FROM pemeriksaan_ranap WHERE no_rawat = '{$no_rawat}'");
        while ($data_tindakan = fetch_array($query_tindakan)) {
        ?>
          <tr>
            <td><?php echo $data_tindakan['keluhan']; ?></td>
            <td><?php echo $data_tindakan['pemeriksaan']; ?></td>
            <td><a href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_pemeriksaan&keluhan=<?php echo $data_tindakan['keluhan']; ?>&no_rawat=<?php echo $no_rawat; ?>">Hapus</a></td>
          </tr>
        <?php
          }
        ?>
        </tbody>
      </table>
    </div>
  </div>
  
  <div role="tabpanel" class="tab-pane fade in" id="dpjp">
    <div class="body">
      <form method="POST">
        <?php 
        	if (isset($_POST['ok_dpjp'])) {
            	if (($_POST['dpjp'] <> "") and ($no_rawat <> "")) {
                	$insert = query("INSERT INTO dpjp_ranap VALUES ('{$no_rawat}','{$_POST['dpjp']}')");
                    if ($insert) {
                    	redirect("pasien-ranap.php?action=tindakan&no_rawat={$no_rawat}#dpjp");
                    };
                };
            };
        ?>
        <label for="email_address">Nama DPJP</label>
          <div class="form-group">
            <select name="dpjp" class="form-control dpjp" id="dpjp" style="width:100%"></select>
          </div>
          <button type="submit" name="ok_dpjp" value="ok_dpjp" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_dpjp\'">SIMPAN</button>
        </form>
      </div>
      <div class="body">
        <table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
          <thead>
            <tr>
              <th>Nama DPJP</th>
              <th>Tools</th>
            </tr>
            </thead>
          <tbody>
          <?php
            $query_tindakan = query("SELECT dokter.nm_dokter , dpjp_ranap.kd_dokter FROM dokter , dpjp_ranap WHERE dokter.kd_dokter = dpjp_ranap.kd_dokter AND dpjp_ranap.no_rawat = '{$no_rawat}'");
            while ($dpjp = fetch_array($query_tindakan)) {
          ?>
            <tr>
              <td><?php echo $dpjp['nm_dokter']; ?></td>
              <td><a href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_dpjp&kd_dokter=<?php echo $dpjp['kd_dokter']; ?>&no_rawat=<?php echo $no_rawat; ?>">Hapus</a></td>
            </tr>
          <?php
            }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>