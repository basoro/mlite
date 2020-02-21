<table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">
    <thead>
        <tr>
            <th>Nama Pasien</th>
            <th>No. RM</th>
            <th>Alamat</th>
            <th>Jenis Bayar</th>
            <th>Poliklinik</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $sql = "SELECT a.nm_pasien, b.no_rkm_medis, a.alamat, c.png_jawab, d.nm_poli, b.no_rawat, b.no_reg, b.tgl_registrasi, b.jam_reg, b.p_jawab, b.almt_pj, b.stts, f.kd_dokter, f.nm_dokter, b.kd_poli, c.kd_pj FROM pasien a, reg_periksa b, penjab c, poliklinik d, dokter f WHERE a.no_rkm_medis = b.no_rkm_medis AND b.kd_pj = c.kd_pj AND b.kd_poli = d.kd_poli AND b.kd_dokter = f.kd_dokter";
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
      $perujuk = fetch_assoc(query("SELECT perujuk FROM rujuk_masuk WHERE no_rawat = '".$row['5']."'"));
      if(!empty($perujuk['perujuk'])) {
        $rujuk_masuk = $perujuk['perujuk'];
      } else {
        $rujuk_masuk = "";
      }
    ?>

        <tr>
            <td><?php echo SUBSTR($row['0'], 0, 15).' ...'; ?></td>
            <td><a href="./index.php?module=Odontogram&page=history&no_rkm_medis=<?php echo $row['1']; ?>"><?php echo $row['1']; ?></a></td>
            <td><?php echo $row['2']; ?></td>
            <td><?php echo $row['3']; ?></td>
            <td><?php echo $row['4']; ?></td>
        </tr>
    <?php
    }
    ?>
    </tbody>
</table>
