<?php
$yesterday = date("Y-m-j", strtotime( '-1 days' ) );
?>
<div class="card">
  <div class="header">
      <h2>CPPT Pasien</h2>
  </div>
  <div class="body">
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
                            <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=tindakan&no_rawat=<?php echo $row['6']; ?>">Assesment & Tindakan</a></li>
                            <li><a href="javascript:void(0);">Input Obat</a></li>
                            <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=radiologi&no_rawat=<?php echo $row['6']; ?>">Berkas Radiologi</a></li>
                            <li><a href="includes/editsttspulang.php?no_rawat=<?php echo $row['6']; ?>&bed=<?php echo $row['3']?>">Status Pulang</a></li>
                          <li><a href="pindah-kamar-pasien2.php?action=pindah&no_rawat=<?php echo $row['6'];?>&nm_pasien=<?php echo $row['nm_pasien'];?>&no_rkm_medis=<?php echo $row['no_rkm_medis'];?>&kd_kmr_sblmny=<?php echo $row['3'];?>">Pindah Kamar</a></li>
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

  </div>
</div>
