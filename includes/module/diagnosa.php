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

      //if (!empty($cek_prioritas_primer)) {
      //    $errors[] = 'Sudah ada prioritas primer.';
      //} else if (!empty($cek_prioritas)) {
      //    $errors[] = 'Sudah ada prioritas yang sama sebelumnya.';
      //}

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
</form>
