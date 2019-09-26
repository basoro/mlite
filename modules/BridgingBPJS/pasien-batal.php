<div class="body">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=BridgingBPJS">
                <i class="material-icons">home</i> <span class="hidden-xs">IGD</span>
            </a>
        </li>
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=BridgingBPJS&page=rawat_jalan">
                <i class="material-icons">accessible</i> <span class="hidden-xs">Rawat Jalan</span>
            </a>
        </li>
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=BridgingBPJS&page=rawat_inap">
                <i class="material-icons">hotel</i> <span class="hidden-xs">Rawat Inap</span>
            </a>
        </li>
        <li role="presentation" class="active">
            <a href="<?php echo URL; ?>/?module=BridgingBPJS&page=pasien_batal">
                <i class="material-icons">clear</i> <span class="hidden-xs">Pasien Batal</span>
            </a>
        </li>
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=BridgingBPJS&page=cek_kepesertaan">
                <i class="material-icons">assignment_ind</i> <span class="hidden-xs">Cek Kepesertaan</span>
            </a>
        </li>
    </ul>
    <div class="content m-t-30">
      <table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
        <thead>
          <tr>
            <th>No RM</th>
            <th>Nama</th>
            <th>Poli</th>
            <th>Tanggal</th>
            <th>No Peserta</th>
            <th>No SEP</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php $hapus = query("SELECT reg_periksa.no_rkm_medis , pasien.nm_pasien , poliklinik.nm_poli , reg_periksa.tgl_registrasi , reg_periksa.no_rawat , pasien.no_peserta
        FROM reg_periksa , poliklinik , pasien WHERE reg_periksa.kd_poli = poliklinik.kd_poli AND
        reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.stts = 'Batal' AND reg_periksa.kd_pj IN ('A02','BPJ') AND reg_periksa.tgl_registrasi = CURRENT_DATE()");
              while($row = fetch_array($hapus)) {?>
          <tr>
            <td><?php echo $row['no_rkm_medis'];?></td>
            <td><?php echo SUBSTR($row['nm_pasien'], 0, 15).' ...';?></td>
            <td><?php echo $row['nm_poli'];?></td>
            <td><?php echo $row['tgl_registrasi'];?></td>
            <td><?php echo $row['no_peserta'];?></td>
            <td><?php $sep = fetch_array(query("SELECT no_sep from bridging_sep where no_rawat = '".$row['no_rawat']."'"));echo $sep['no_sep'];?></td>
            <td><a href="hapus-brid.php?no_sep=<?php echo $sep['no_sep'];?>" class="btn btn-danger">X</a></td>
          </tr><?php } ?>
        </tbody>
      </table>
    </div>
</div>
