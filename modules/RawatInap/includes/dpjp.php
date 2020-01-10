<div class="body">
      <form method="POST">
        <?php 
        	if (isset($_POST['ok_dpjp'])) {
            	if (($_POST['dpjp'] <> "") and ($no_rawat <> "")) {
                	$insert = query("INSERT INTO dpjp_ranap VALUES ('{$no_rawat}','{$_POST['dpjp']}')");
                    if ($insert) {
                      redirect("./?module=RawatInap&page=index&action=tindakan&no_rawat={$no_rawat}");
                    };
                };
            };
        ?>
        <label for="dpjp">Nama DPJP</label>
          <div class="form-group">
            <select name="dpjp" class="form-control dpjp" id="dpjp" style="width:100%"></select>
          </div>
          <button type="submit" name="ok_dpjp" value="ok_dpjp" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_dpjp\'">SIMPAN</button>
        </form>
      </div>
      <div class="body">
        <table class="table datatable responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
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
                <td><a class="btn bg-red waves-effect" href="./?module=RawatInap&page=index&action=delete_dpjp&kd_dokter=<?php echo $dpjp['kd_dokter']; ?>&no_rawat=<?php echo $no_rawat; ?>">Hapus</a></td>
            </tr>
          <?php
            }
          ?>
        </tbody>
      </table>
    </div>