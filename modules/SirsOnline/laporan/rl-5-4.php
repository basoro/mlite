<div class="card">
    <div class="header">
        <h2>
            LAPORAN RL 5.4 (Daftar 10 Besar Penyakit Rawat Jalan)
            <small><?php if(isset($_POST['kd_pj']) && $_POST['kd_pj'] == 'A01') { echo 'Cara Bayar UMUM, '; } ?><?php if(isset($_POST['kd_pj']) && $_POST['kd_pj'] == 'A02') { echo 'Cara Bayar BPJS, '; } ?><?php if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) { echo "Periode ".date("d-m-Y",strtotime($_POST['tgl_awal']))." s/d ".date("d-m-Y",strtotime($_POST['tgl_akhir'])); } ?></small>
        </h2>
    </div>
    <div class="body table-responsive">
        <div id="buttons" class="align-center m-l-10 m-b-15 export-hidden"></div>
        <table id="datatable" class="table table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
            <thead>
                <tr>
                    <th>KODE PROPINSI</th>
                    <th>KAB / KOTA</th>
                    <th>KODE RS</th>
                    <th>Nama RS</th>
                    <th>Bulan</th>
                    <th>Tahun</th>
                    <th>No. Urut</th>
                    <th>KODE ICD 10</th>
                    <th>Deskripsi</th>
                    <th>Kasus Baru LK</th>
                    <th>Kasus Baru PR</th>
                    <th>Jml. Kasus Baru</th>
                    <th>Jml. Kunjungan</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $tgl_awal = isset($_POST['tgl_awal'])?$_POST['tgl_awal']:null;
            $tgl_akhir = isset($_POST['tgl_akhir'])?$_POST['tgl_akhir']:null;
            $kd_pj = isset($_POST['kd_pj'])?$_POST['kd_pj']:null;
            $bln = date("m",strtotime($tgl_akhir));
            $sql = "SELECT c.nm_penyakit, a.kd_penyakit, count(a.kd_penyakit) AS jumlah
              FROM diagnosa_pasien a, reg_periksa b, penyakit c
              WHERE b.tgl_registrasi BETWEEN '$tgl_awal' AND '$tgl_akhir'
              AND a.no_rawat = b.no_rawat
              AND a.kd_penyakit = c.kd_penyakit
              AND a.status = 'Ralan'";
            if($kd_pj) {
            $sql .= " AND b.kd_pj = '$_POST[kd_pj]'";
            }
            $sql .= " GROUP BY a.kd_penyakit
              ORDER BY jumlah DESC
              LIMIT 10";
            $query = query($sql);
            $no = 1;
            while($row = fetch_array($query)) {
            ?>
                <tr>
                    <td><?php echo KODEPROP; ?></td>
                    <td><?php echo $dataSettings['kabupaten']; ?></td>
                    <td><?php echo KODERS; ?></td>
                    <td><?php echo $dataSettings['nama_instansi']; ?></td>
                    <td><?php echo $bulanList[$bln]; ?></td>
                    <td><?php echo date("Y",strtotime($_POST['tgl_akhir'])); ?></td>
                    <td><?php echo $no; ?></td>
                    <td><?php echo $row['1']; ?></td>
                    <td><?php echo $row['0']; ?></td>
                    <td>
                      <?php
                      $sql_baru_lk = "SELECT count(a.kd_penyakit)
                        FROM diagnosa_pasien a, reg_periksa b, pasien c
                        WHERE a.kd_penyakit = '$row[1]'
                        AND a.no_rawat = b.no_rawat
                        AND b.no_rkm_medis = c.no_rkm_medis
                        AND b.tgl_registrasi BETWEEN '$tgl_awal' AND '$tgl_akhir'
                        AND c.jk = 'L'
                        AND b.stts_daftar = 'Baru'
                        AND a.status = 'Ralan'";
                      if($kd_pj) {
                      $sql_baru_lk .= " AND b.kd_pj = '$_POST[kd_pj]'";
                      }
                      $sql_baru_lk .= " GROUP BY a.kd_penyakit";
                      $query_baru_lk = query($sql_baru_lk);
                      if(num_rows($query_baru_lk) >= 1) {
                      while ($hasil_baru_lk = fetch_array($query_baru_lk)) {
                        $baru_lk = $hasil_baru_lk[0];
                        echo $baru_lk;
                      }
                      } else {
                        $baru_lk = '0';
                        echo $baru_lk;
                      }
                      ?>
                    </td>
                    <td>
                      <?php
                      $sql_baru_pr = "SELECT count(a.kd_penyakit)
                        FROM diagnosa_pasien a, reg_periksa b, pasien c
                        WHERE a.kd_penyakit = '$row[1]'
                        AND a.no_rawat = b.no_rawat
                        AND b.no_rkm_medis = c.no_rkm_medis
                        AND b.tgl_registrasi BETWEEN '$tgl_awal' AND '$tgl_akhir'
                        AND c.jk = 'P'
                        AND b.stts_daftar = 'Baru'
                        AND a.status = 'Ralan'";
                      if($kd_pj) {
                      $sql_baru_pr .= " AND b.kd_pj = '$_POST[kd_pj]'";
                      }
                      $sql_baru_pr .= " GROUP BY a.kd_penyakit";
                      $query_baru_pr = query($sql_baru_pr);
                      if(num_rows($query_baru_pr) >= 1) {
                      while ($hasil_baru_pr = fetch_array($query_baru_pr)) {
                        $baru_pr = $hasil_baru_pr[0];
                        echo $baru_pr;
                      }
                      }else {
                        $baru_pr = '0';
                        echo $baru_pr;
                      }
                      ?>
                    </td>
                    <td><?php $baru_total = $baru_lk + $baru_pr; echo $baru_total; ?></td>
                    <td><?php echo $row['2']; ?></td>
                </tr>
            <?php
            $no++;
            }
            ?>
            </tbody>
        </table>
        <div class="row clearfix">
            <form method="post" action="">
              <div class="col-sm-4">
                  <div class="form-group">
                      <div class="form-line">
                          <input type="text" name="tgl_awal" class="datepicker form-control" placeholder="Pilih tanggal awal...">
                      </div>
                  </div>
              </div>
              <div class="col-sm-4">
                  <div class="form-group">
                      <div class="form-line">
                          <input type="text" name="tgl_akhir" class="datepicker form-control" placeholder="Pilih tanggal akhir...">
                      </div>
                  </div>
              </div>
              <div class="col-sm-2">
                  <div class="form-group">
                      <div class="form-line">
                        <select name="kd_pj" class="form-control show-tick">
                            <option value="">Semua</option>
                            <option value="A01">Umum</option>
                            <option value="A02">BPJS</option>
                        </select>
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
