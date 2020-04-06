<?php

if (isset($_GET['no_rawat'])) {
    $sql = "SELECT a.no_rkm_medis , a.no_rawat , b.nm_pasien , b.umur FROM reg_periksa a JOIN pasien b ON a.no_rkm_medis = b.no_rkm_medis WHERE a.no_rawat = '{$_GET['no_rawat']}'";
    $found_pasien = query($sql);
    if (num_rows($found_pasien) == 1) {
        while ($a = fetch_array($found_pasien)) {
            $no_rawat = $a['no_rawat'];
            $no_rkm_medis = $a['no_rkm_medis'];
            $nama = $a['nm_pasien'];
            $umur = $a['umur'];
        }
    } else {
        redirect('./?module=RawatInap&page=index');
    }
}
?>
<div class="card">
  <div class="header">
    <h2>Assesmen Awal Keperawatan Rawat Inap</h2>
  </div>
  <div class="body">
    <?php display_message(); ?>
    <br>
    <?php
      $action = isset($_GET['action'])?$_GET['action']:null;
      $jenis_poli = isset($_SESSION['jenis_poli'])?$_SESSION['jenis_poli']:null;
      $role = isset($_SESSION['role'])?$_SESSION['role']:null;
      if (!$action) { ?>
        <table id="datatable" class="table table_bordered responsive table_striped table_hover display nowrap js-exportable" width="100%">
          <thead>
            <tr>
              <th>Nama</th>
              <th>No MR</th>
              <th>Kamar # Bed</th>
              <th>Tanggal Masuk</th>
              <th>Cara Bayar</th>
              <th>DPJP</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $sql = "SELECT a.kd_kamar, a.tgl_masuk, b.nm_pasien , c.no_rkm_medis , c.no_rawat , d.png_jawab , e.nm_bangsal
                      FROM kamar_inap a JOIN pasien b JOIN reg_periksa c JOIN penjab d JOIN bangsal e JOIN kamar f
                      ON a.no_rawat = c.no_rawat AND b.no_rkm_medis = c.no_rkm_medis AND f.kd_bangsal = e.kd_bangsal AND c.kd_pj = d.kd_pj AND a.kd_kamar = f.kd_kamar
                      WHERE a.stts_pulang = '-'";
                      if ($role == 'Paramedis_Ranap') {
                          $sql .= "AND e.kd_bangsal = '$jenis_poli'";
                      }
                      $sql .= "ORDER BY a.kd_kamar ASC";
                      $result = query($sql);
                      while ($a = fetch_array($result)) { ?>
                      <tr>
                        <td><?php echo SUBSTR($a['nm_pasien'], 0, 22); ?></td>
                        <td><a href="<?php echo URL; ?>/?module=RawatInap&page=awkep&action=askep&no_rawat=<?php echo $a['no_rawat']; ?>"><?php echo $a['no_rkm_medis']; ?></a></td>
                        <td><?php echo $a['nm_bangsal']." # ";echo $a['kd_kamar']; ?></td>
                        <td><?php echo $a['tgl_masuk']; ?></td>
                        <td><?php echo $a['png_jawab']; ?></td>
                        <td>DPJP</td>
                      </tr>
                      <?php }
             ?>
          </tbody>
        </table>
    <?php
      } elseif ($action == "askep") { ?>
        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="body">
              <dl class="dl-horizontal">
                <dt>Nama Lengkap</dt>
                <dd><?php echo $nama ?></dd>
                <dt>No RM</dt>
                <dd><?php echo $no_rkm_medis ?></dd>
                <dt>No Rawat</dt>
                <dd><?php echo $no_rawat; ?></dd>
                <dt>Umur</dt>
                <dd><?php echo $umur; ?></dd>
              </dl>
            </div>
            <div class="row">
              <ul class="nav nav-tabs tab-nav-right" role="tablist">
                <li role="presentation" class="active"><a href="#pernapasan" data-toggle="tab">Pernafasan</a></li>
                <li role="presentation"><a href="#kardiovaskuler" data-toggle="tab">Kardiovaskuler</a></li>
                <li role="presentation"><a href="#saraf" data-toggle="tab">Saraf Pusat</a></li>
                <li role="presentation"><a href="#gastrointestinal" data-toggle="tab">Gastrointestinal</a></li>
                <li role="presentation"><a href="#perkemihan" data-toggle="tab">Perkemihan</a></li>
                <li role="presentation"><a href="#obstetri" data-toggle="tab">Obstetri</a></li>
                <li role="presentation"><a href="#muskul" data-toggle="tab">Muskulosceletal Dan Integument</a></li>
                <li role="presentation"><a href="#hematologi" data-toggle="tab">Hematologi</a></li>
                <li role="presentation"><a href="#psikososial" data-toggle="tab">Psikososial</a></li>
                <li role="presentation"><a href="#spiritual" data-toggle="tab">Spiritual</a></li>
                <li role="presentation"><a href="#invasif" data-toggle="tab">Alat Invasif Yang Digunakan</a></li>
                <li role="presentation"><a href="#terapi" data-toggle="tab">Terapi</a></li>
                <li role="presentation"><a href="#nutrisi" data-toggle="tab">Nutrisi</a></li>
                <li role="presentation"><a href="#konsep" data-toggle="tab">Konsep Diri Dan Kognitif</a></li>
                <li role="presentation"><a href="#pola" data-toggle="tab">Pola Fungsional</a></li>
                <li role="presentation"><a href="#persepsi" data-toggle="tab">Persepsi Sensori</a></li>
                <li role="presentation"><a href="#latihan" data-toggle="tab">Aktifitas Dan Latihan</a></li>
              </ul>
            </div>
          </div>
        </div>
      <?php }
    ?>
  </div>
</div>
