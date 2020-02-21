<?php
$yesterday = date("Y-m-j", strtotime( '-1 days' ) );
$jenis_poli = isset($_SESSION['jenis_poli'])?$_SESSION['jenis_poli']:null;
$role = isset($_SESSION['role'])?$_SESSION['role']:null;
?>
<div class="card">
  <div class="header">
      <h2>Pasien Lanjutan</h2>
  </div>
  <div class="body">
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
        $sql = "SELECT a.nm_pasien, b.no_rkm_medis, a.alamat, c.png_jawab, d.nm_poli, b.no_rawat, b.no_reg, b.tgl_registrasi, b.jam_reg FROM pasien a, reg_periksa b, penjab c, poliklinik d WHERE b.stts = 'Belum' AND a.no_rkm_medis = b.no_rkm_medis AND b.kd_pj = c.kd_pj AND b.kd_poli = d.kd_poli";
        if($role == 'Medis' || $role == 'Paramedis') {
          $sql .= " AND b.kd_poli = '$jenis_poli'";
        }
        if(isset($_POST['tanggal']) && $_POST['tanggal'] !="") {
            $sql .= " AND b.tgl_registrasi = '{$_POST['tanggal']}'";
        } else {
            $sql .= " AND b.tgl_registrasi = '{$yesterday}'";
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
        <div class="col-sm-10">
            <div class="form-group">
                <div class="form-line">
                    <input type="text" name="tanggal" class="datepicker form-control" placeholder="Pilih tanggal...">
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
</div>
