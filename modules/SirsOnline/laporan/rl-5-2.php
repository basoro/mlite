<div class="card">
    <div class="header">
        <h2>
          LAPORAN RL 5.2 (Kunjungan Rawat Jalan)
          <small><?php $date = date('Y-m-d'); if (isset($_POST['tahun'])) {
    $tahun = $_POST['tahun'];
} else {
    $tahun = date("Y", strtotime($date));
};
                   if (isset($_POST['bulan'])) {
                       $bulan = $_POST['bulan'];
                   } else {
                       $bulan = date("M", strtotime($date));
                   };echo "Periode ".$tahun; ?></small>
        </h2>
        <ul class="header-dropdown m-r--5">
            <li class="dropdown">
                <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    <i class="material-icons">more_vert</i>
                </a>
                <ul class="dropdown-menu pull-right">
                  <?php
                  $current_year = date('Y');
                  $years = range($current_year-5, $current_year);
                  foreach ($years as $year) {
                      echo '<li><a href="'.URL.'/?module=SirsOnline&page=rl_5_2&tahun='.$year.'">'.$year.'</a></li>';
                  }
                  ?>
                </ul>
            </li>
        </ul>
    </div>
    <div class="body">
        <div id="buttons" class="align-center m-l-10 m-b-15 export-hidden"></div>
        <table id="datatable" class="table table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
            <thead>
                <tr>
                    <th>Kode RS</th>
                  	<th>Kode<br>Propinsi</th>
                  	<th>Kab/Kota</th>
                  	<th>Nama RS</th>
                  	<th>Tahun</th>
                  	<th>No</th>
                    <th>Jenis Kegiatan</th>
                    <th>Jumlah</th>
                 </tr>
            </thead>
            <tbody>
            <?php
            $sql =
            "SELECT reg_periksa.kd_poli , poliklinik.nm_poli , pasien.umur
            FROM reg_periksa , poliklinik , pasien
            WHERE reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.tgl_registrasi LIKE '%{$tahun}%' AND reg_periksa.kd_poli !='-' GROUP BY reg_periksa.kd_poli";
            $query = query($sql);
            $no = 1;
            while ($row = fetch_array($query)) {
                ?>
                <tr>
                    <td>6307012</td>
                  	<td>63prop</td>
                  	<td><?php
                      $nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting"));
                echo $nm_its['0']; ?></td>
                  	<td><?php
                      $bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting"));
                echo $bpt['0']; ?></td>
                    <td><?php echo $tahun; ?></td>
                    <td><?php echo $no; ?></td>
                    <td><?php echo $row[1]; ?></td>
                  	<td><?php
                      $awal_tahun = fetch_array(query("SElECT COUNT(reg_periksa.no_rawat) from reg_periksa where reg_periksa.tgl_registrasi BETWEEN '{$tahun}-{$bulan}-01' AND '{$tahun}-{$bulan}-31' AND reg_periksa.kd_poli = '$row[0]'"));
                echo $awal_tahun['0']; ?></td>
                 	</tr>
            <?php
            $no++;
            }
            ?>
            </tbody>
        </table>
        <div class="row clearfix">
        	<form method="post" action="">
            <div class="col-lg-5">
                <div class="form-group">
                    <div class="form-line">
                        <select name="bulan" class="form-control">
                          <option value="01">Januari</option>
                          <option value="02">Pebruari</option>
                          <option value="03">Maret</option>
                          <option value="04">April</option>
                          <option value="05">Mei</option>
                          <option value="06">Juni</option>
                          <option value="07">Juli</option>
                          <option value="08">Agustus</option>
                          <option value="09">September</option>
                          <option value="10">Oktober</option>
                          <option value="11">November</option>
                          <option value="12">Desember</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="form-group">
                    <div class="form-line">
                        <select name="tahun" class="form-control">
                          <?php
                          $current_year = date('Y');
                          $years = range($current_year-5, $current_year);
                          foreach ($years as $year) {
                              echo '<option value="'.$year.'">'.$year.'</option>';
                          }
                          ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <div class="form-line">
                        <input type="submit" class="btn bg-blue btn-block btn-lg waves-effect" value="Submit">
                    </div>
                </div>
            </div>
          </form>
        </div>
    </div>
</div>
