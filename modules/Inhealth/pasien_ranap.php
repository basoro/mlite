<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

if (isset($_GET['no_rawat'])) {
    $_sql = "SELECT a.no_rkm_medis, a.no_rawat, b.nm_pasien, b.umur FROM reg_periksa a, pasien b WHERE a.no_rkm_medis = b.no_rkm_medis AND a.no_rawat = '$_GET[no_rawat]'";
    $found_pasien = query($_sql);
    if (num_rows($found_pasien) == 1) {
        while ($row = fetch_array($found_pasien)) {
            $no_rkm_medis  = $row['0'];
            $get_no_rawat	     = $row['1'];
            $no_rawat	     = $row['1'];
            $nm_pasien     = $row['2'];
            $umur          = $row['3'];
        }
    } else {
        redirect('./?module=Sisrute&page=index');
    }
}

?>
<div class="card">
    <div class="header">
      <h2>Inhealth Pasien Rawat Inap</h2>
    </div>
    <div class="body">
      <?php display_message(); ?>
      <br>
      <?php
      $action = isset($_GET['action'])?$_GET['action']:null;
      $jenis_poli = isset($_SESSION['jenis_poli'])?$_SESSION['jenis_poli']:null;
      $role = isset($_SESSION['role'])?$_SESSION['role']:null;
      if (!$action) {
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
          reg_periksa.kd_pj = 'A08'
          AND
          kamar_inap.kd_kamar = kamar.kd_kamar
          AND
          kamar.kd_bangsal = bangsal.kd_bangsal
          AND
          kamar_inap.stts_pulang = '-'
          AND
          reg_periksa.kd_pj = penjab.kd_pj";
          if ($role == 'Paramedis_Ranap') {
              $sql .= " AND bangsal.kd_bangsal = '$jenis_poli'";
          }
          $sql .= " ORDER BY kamar_inap.kd_kamar ASC";
          $result = query($sql);
          while ($row = fetch_array($result)) {
              $get_no_rawat = $row['6']; ?>
              <tr>
                <td><?php echo SUBSTR($row['0'], 0, 15).' ...'; ?></td>
                <td><a class="btn btn-primary" href="<?php echo URL; ?>/?module=Sisrute&page=index&action=sep&no_rawat=<?php echo $row['6']; ?>"><?php echo $row['1']; ?></a></td>
                <td><?php echo $row['2']; ?></td>
                <td><?php echo $row['3']; ?></td>
                <td><?php echo $row['4']; ?></td>
                <td><?php echo $row['5']; ?></td>
                <td><?php $dpjp = query("SELECT dokter.nm_dokter FROM dpjp_ranap , dokter WHERE dpjp_ranap.kd_dokter = dokter.kd_dokter AND dpjp_ranap.no_rawat = '".$row['6']."'");
              $dpjpp = fetch_array($dpjp);
              echo $dpjpp['0']; ?></td>
              </tr>
          <?php
          } ?>
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
      <?php
      }
      ?>
      <?php if ($action == "sep") { ?>
      <?php $sql = "SELECT pasien.nm_pasien , reg_periksa.no_rawat , pasien.tmp_lahir , pasien.no_ktp , reg_periksa.no_rkm_medis , pasien.tgl_lahir , pasien.no_tlp , pasien.no_peserta , pasien.jk , pasien.alamat
      FROM reg_periksa , pasien , poliklinik WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.no_rawat = '{$_GET['no_rawat']}'";
          $data = query($sql);
          $b = fetch_array($data); ?>
      <div class="body">
        <form method="post" action="" class="form">
          <div class="row clearfix">
            <div class="col-md-2">
              <div class="form-group">
                <div class="form-line">
                <label for="norm">No Rekam Medis</label>
                  <input type="hidden" class="form-control" name="no_rawat" value="<?php echo $_GET['no_rawat']; ?>" readonly>
                  <input type="text" class="form-control" name="norm" value="<?php echo $b['no_rkm_medis']; ?>" readonly>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <div class="form-line">
                  <label for="norm">Nama</label>
                  <input type="text" class="form-control" name="nmps" value="<?php echo $b['nm_pasien']; ?>" readonly>
                </div>
              </div>
            </div>
            <div class="col-md-1">
              <div class="form-group">
                <div class="form-line">
                  <label for="norm">JK</label>
                  <input type="text" class="form-control" name="jk" value="<?php echo $b['jk']; ?>" readonly>
                </div>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <div class="form-line">
                  <label for="norm">Tanggal Lahir</label>
                  <input type="text" class="form-control" name="tgllhr" value="<?php echo $b['tgl_lahir']; ?>" readonly>
                </div>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <div class="form-line">
                  <label for="norm">Tempat Lahir</label>
                  <input type="text" class="form-control" name="tmplhr" value="<?php echo $b['tmp_lahir']; ?>" readonly>
                </div>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <div class="form-line">
                <label for="norm">No Telp</label>
                  <input type="number" class="form-control" name="notlp" required minlength=8 maxlength=13 value="<?php echo $b['no_tlp']; ?>" readonly>
                </div>
              </div>
            </div>
          </div>
          <div class="row clearfix">
            <div class="col-md-3">
              <div class="form-group">
                <div class="form-line">
                  <label for="norm">No Peserta</label>
                  <input type="text" class="form-control" name="nops" value="<?php echo $b['no_peserta']; ?>" readonly>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <div class="form-line">
                  <label for="norm">No KTP</label>
                  <input type="text" class="form-control" name="nik" value="<?php echo $b['no_ktp']; ?>" readonly>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <div class="form-line">
                  <label for="norm">Alamat</label>
                  <input type="text" class="form-control" name="alamat" value="<?php echo $b['alamat']; ?>" readonly>
                </div>
              </div>
            </div>
        </div>
        <div class="row clearfix">
          <div class="col-md-3">
            <div class="form-group">
                <label for="norm">Jenis Rujukan</label>
                <select class="form-control" name="jns_rujuk">
                  <option value="1">Rawat Jalan</option>
                  <option value="2" selected>Rawat Darurat/Inap</option>
                  <option value="3">Parsial</option>
                </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Tanggal</label>
                <input type="text" name="tgl_rujuk" id="datetimepicker1" class="form-control">
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <div class="form-line">
                <label for="faskes">Nama Faskes</label>
                <select name="faskes" class="form-control faskes" id="faskes" style="width:100%"></select>
                <br/>
                <input type="hidden" class="form-control" id="kdfaskes" name="kdfaskes"/>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Alasan Rujukan</label>
                <select name="alasan" class="form-control alasan" id="alasan" style="width:100%"></select>
                <input type="hidden" class="form-control" id="kdalasan" name="kdalasan"/>
              </div>
            </div>
          </div>
        </div>
        <div class="row clearfix">
          <div class="col-md-3">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Alasan Lainnya</label>
                <input type="text" class="form-control" name="alasan_lain" value="">
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Diagnosa</label>
                <select name="diagnosa" class="form-control diagnosa" id="diagnosa" style="width:100%"></select>
                <input type="hidden" class="form-control" id="kddx" name="kddx"/>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Dokter Perujuk</label>
                <select name="dr" class="form-control dr" id="dr" style="width:100%"></select>
                <input type="hidden" class="form-control" id="kddr" name="kddr"/>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Pelayanan</label>
                <select name="kdpel" class="form-control kdpel" id="kdpel" style="width:100%"></select>
                <input type="hidden" class="form-control" id="pel" name="pel"/>
              </div>
            </div>
          </div>
          <input type="hidden" class="form-control" name="petugas_entry" value="<?php echo $dataGet['0']; ?>">
          <input type="hidden" class="form-control" name="petugas_nik" value="<?php $nik = fetch_array(query("SELECT no_ktp FROM pegawai WHERE nik = '$_SESSION[username]'"));
          echo $nik['0']; ?>">
        </div>
        <div class="row clearfix">
          <div class="col-md-4">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Anamnesis</label>
                <input type="text" class="form-control" name="anamnesis" value="">
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
                <label for="norm">Kesadaran</label>
                <select class="form-control" name="kesadaran">
                  <option value="1">Sadar</option>
                  <option value="2">Tidak Sadar</option>
                </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Tekanan Darah</label>
                <input type="text" class="form-control" name="tdarah" value="">
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Frekuensi Nadi</label>
                <input type="text" class="form-control" name="nadi" value="">
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Suhu</label>
                <input type="text" class="form-control" name="suhu" value="">
              </div>
            </div>
          </div>
        </div>
        <div class="row clearfix">
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Respirasi</label>
                <input type="text" class="form-control" name="nafas" value="">
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Keadaan Umum</label>
                <input type="text" class="form-control" name="keadaan_umum" value="">
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
                <label for="norm">Tingkat Nyeri</label>
                <select class="form-control" name="nyeri">
                  <option value="0">Tidak Nyeri</option>
                  <option value="1">Ringan</option>
                  <option value="2">Sedang</option>
                  <option value="3">Berat</option>
                </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Alergi</label>
                <input type="text" class="form-control" name="alergi" value="">
              </div>
            </div>
          </div>
        </div>
        <div class="row clearfix">
          <div class="col-md-4">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Laboratorium</label>
                <input type="text" class="form-control" name="lab" value="<?php
                $lab = query("SELECT detail_periksa_lab.kd_jenis_prw , detail_periksa_lab.nilai , jns_perawatan_lab.nm_perawatan
                  FROM detail_periksa_lab JOIN jns_perawatan_lab on detail_periksa_lab.kd_jenis_prw = jns_perawatan_lab.kd_jenis_prw
                  WHERE detail_periksa_lab.no_rawat = '{$_GET['no_rawat']}'");
                  while ($a = fetch_array($lab)) {
                      echo $a['2'].':'.$a['1'].';';
                  }
                ?>">
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Radiologi</label>
                <input type="text" class="form-control" name="rad" value="<?php
                $rad = query("SELECT periksa_radiologi.kd_jenis_prw , jns_perawatan_radiologi.nm_perawatan , hasil_radiologi.hasil
                  FROM periksa_radiologi JOIN jns_perawatan_radiologi JOIN hasil_radiologi ON periksa_radiologi.no_rawat = hasil_radiologi.no_rawat AND periksa_radiologi.kd_jenis_prw = jns_perawatan_radiologi.kd_jenis_prw
                  WHERE periksa_radiologi.no_rawat = '{$_GET['no_rawat']}' AND jns_perawatan_radiologi.kd_jenis_prw IN ('J000056','J000057','J000059','J000060','J000061','J000062','J000063','J000064','J000065','J000066','J000067','J000068')");
                  while ($a = fetch_array($rad)) {
                      echo $a['1'].':'.$a['2'].';';
                  }
                ?>">
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Terapi / Tindakan</label>
                <input type="text" class="form-control" name="terapi" value="<?php
                $trp = query("SELECT detail_pemberian_obat.kode_brng , databarang.nama_brng
                  FROM detail_pemberian_obat JOIN databarang ON detail_pemberian_obat.kode_brng = databarang.kode_brng
                  WHERE detail_pemberian_obat.no_rawat = '{$_GET['no_rawat']}' GROUP BY (detail_pemberian_obat.kode_brng)");
                  echo "TRP:";
                  while ($b = fetch_array($trp)) {
                      echo $b['1'].";";
                  }
                $tdk = query("SELECT rawat_inap_pr.kd_jenis_prw , jns_perawatan_inap.nm_perawatan
                  FROM rawat_inap_pr join jns_perawatan_inap on jns_perawatan_inap.kd_jenis_prw = rawat_inap_pr.kd_jenis_prw
                  WHERE rawat_inap_pr.no_rawat = '{$_GET['no_rawat']}' GROUP BY (rawat_inap_pr.kd_jenis_prw)");
                  echo '#TDK:';
                  while ($a = fetch_array($tdk)) {
                      echo $a['1'].';';
                  }
                  ?>">
              </div>
            </div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col col">
            <button type="submit" name="ok_sis" value="ok_sis" class="btn kirim bg-indigo waves-effect" onclick="this.value=\'ok_sis\'">KIRIM</button>
          </div>
        </div>
      </form>
    </div>
    <?php
      } ?>
            </div>
        </div>
    </div>
</div>
