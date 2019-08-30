<form method="post">
  <?php
  if (isset($_POST['ok_rad'])) {
      if (($_POST['kd_jenis_prw_rad'] <> "") and ($no_rawat <> "")) {

          $get_number = fetch_array(query("SELECT ifnull(MAX(CONVERT(RIGHT(noorder,4),signed)),0) FROM permintaan_radiologi WHERE tgl_permintaan = '{$date}'"));
          $lastNumber = substr($get_number[0], 0, 4);
          $get_next_number = sprintf('%04s', ($lastNumber + 1));
          $get_date = str_replace('-', '',$date);
          $next_no_order = 'PR'.$get_date.''.$get_next_number;
          echo $next_no_order;
          $insert = query("INSERT INTO permintaan_radiologi VALUES ('{$next_no_order}', '{$no_rawat}', '{$date}', '{$time}', '0000-00-00', '00:00:00', '0000-00-00', '00:00:00', '{$_SESSION['username']}', 'ralan')");
          if($insert) {
            $get_kd_jenis_prw = $_POST['kd_jenis_prw_rad'];
            for ($i = 0; $i < count($get_kd_jenis_prw); $i++) {
                $kd_jenis_prw = $get_kd_jenis_prw[$i];
                $insert2 = query("INSERT INTO permintaan_pemeriksaan_radiologi VALUES ('{$next_no_order}', '{$kd_jenis_prw}', 'Belum')");
                redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}");
            }
          }

      }
  }
  ?>
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
</form>
