<div class="card">
    <div class="header">
        <h2>
          LAPORAN RL 3.2 (Rawat Darurat)
            <small><?php if(isset($_GET['tahun'])) { $tahun = $_GET['tahun']; } else { $date = date('Y-m-d'); $tahun = date("Y",strtotime($date)); }; echo "Periode ".$tahun; ?></small>
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
                    echo '<li><a href="'.URL.'/index.php?module=SirsOnline&page=rl_3_2&tahun='.$year.'">'.$year.'</a></li>';
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
                    <th>Pelayanan</th>
                  	<th>Total<br> Pasien<br> Rujukan</th>
                  	<th>Total<br> Pasien<br> Non Rujukan</th>
                  	<th>Tindak<br> Lanjut<br> Pelayanan<br> Dirawat</th>
                   	<th>Tindak<br> Lanjut<br> Pelayanan<br> Dirujuk</th>
                  	<th>Tindak<br> Lanjut<br> Pelayanan<br> Pulang</th>
                  	<th>Mati di UGD</th>
                  	<th>DOA</th>
                </tr>
            </thead>
            <tbody>
              <tr>
                <td><?php echo KODERS;?></td>
                <td><?php echo KODEPROP;?></td>
                <td><?php $kab = fetch_array(query("Select kabupaten from setting"));echo $kab[0];?></td>
                <td><?php $nm = fetch_array(query("Select nama_instansi from setting"));echo $nm[0];?></td>
                <td><?php echo $tahun;?></td>
                <td>1</td>
                <td>Bedah</td>
                <td><?php $rujuk = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien , rujuk_masuk where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.no_rawat = rujuk_masuk.no_rawat and rujuk_masuk.perujuk NOT LIKE '%datang sendiri%' and pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk[0]?></td>
                <td><?php $rujuk1 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien , rujuk_masuk where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.no_rawat = rujuk_masuk.no_rawat and rujuk_masuk.perujuk LIKE '%datang sendiri%' and pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk1[0]?></td>
                <td><?php $rujuk2 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.stts = 'Dirawat' and reg_periksa.status_lanjut = 'Ranap' AND pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk2[0]?></td>
                <td><?php $rujuk3 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.stts = 'Dirujuk' and pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk3[0]?></td>
                <td><?php $rujuk4 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.stts = 'Sudah' and pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk4[0]?></td>
                <td><?php $rujuk5 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.stts = 'Meninggal' and pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk5[0]?></td>
                <td>0</td>
              </tr>
              <tr>
                <td><?php echo KODERS;?></td>
                <td><?php echo KODEPROP;?></td>
                <td><?php $kab = fetch_array(query("Select kabupaten from setting"));echo $kab[0];?></td>
                <td><?php $nm = fetch_array(query("Select nama_instansi from setting"));echo $nm[0];?></td>
                <td><?php echo $tahun;?></td>
                <td>2</td>
                <td>Non Bedah</td>
                <td><?php $rujuk = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien , rujuk_masuk where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.no_rawat = rujuk_masuk.no_rawat and rujuk_masuk.perujuk NOT LIKE '%datang sendiri%' and pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk[0]?></td>
                <td><?php $rujuk1 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien , rujuk_masuk where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.no_rawat = rujuk_masuk.no_rawat and rujuk_masuk.perujuk LIKE '%datang sendiri%' and pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk1[0]?></td>
                <td><?php $rujuk2 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.stts = 'Dirawat' and reg_periksa.status_lanjut = 'Ranap' AND pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk2[0]?></td>
                <td><?php $rujuk3 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.stts = 'Dirujuk' and pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk3[0]?></td>
                <td><?php $rujuk4 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.stts = 'Sudah' and pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk4[0]?></td>
                <td><?php $rujuk5 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.stts = 'Meninggal' and pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk5[0]?></td>
                <td>0</td>
              </tr>
              <tr>
                <td><?php echo KODERS;?></td>
                <td><?php echo KODEPROP;?></td>
                <td><?php $kab = fetch_array(query("Select kabupaten from setting"));echo $kab[0];?></td>
                <td><?php $nm = fetch_array(query("Select nama_instansi from setting"));echo $nm[0];?></td>
                <td><?php echo $tahun;?></td>
                <td>3</td>
                <td>Kebidanan</td>
                <td><?php $rujuk = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien , rujuk_masuk , kamar_inap where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.no_rawat = kamar_inap.no_rawat and reg_periksa.no_rawat = rujuk_masuk.no_rawat and kamar_inap.diagnosa_awal like '%hamil%' and rujuk_masuk.perujuk NOT LIKE '%datang sendiri%' and pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk[0]?></td>
                <td><?php $rujuk1 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien , rujuk_masuk , kamar_inap where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.no_rawat = kamar_inap.no_rawat and reg_periksa.no_rawat = rujuk_masuk.no_rawat and kamar_inap.diagnosa_awal like '%hamil%' and rujuk_masuk.perujuk LIKE '%datang sendiri%' and pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk1[0]?></td>
                <td><?php $rujuk2 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien , kamar_inap where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.no_rawat = kamar_inap.no_rawat and reg_periksa.stts = 'Dirawat' and kamar_inap.diagnosa_awal like '%hamil%' and reg_periksa.status_lanjut = 'Ranap' AND pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk2[0]?></td>
                <td><?php $rujuk3 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien , kamar_inap where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.no_rawat = kamar_inap.no_rawat and reg_periksa.stts = 'Dirujuk' and kamar_inap.diagnosa_awal like '%hamil%' and pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk3[0]?></td>
                <td><?php $rujuk4 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien , kamar_inap where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.no_rawat = kamar_inap.no_rawat and reg_periksa.stts = 'Sudah' and kamar_inap.diagnosa_awal like '%hamil%' and pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk4[0]?></td>
                <td><?php $rujuk5 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien , kamar_inap where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.no_rawat = kamar_inap.no_rawat and reg_periksa.stts = 'Meninggal' and kamar_inap.diagnosa_awal like '%hamil%' and pasien.umur >= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk5[0]?></td>
                <td>0</td>
              </tr>
              <tr>
                <td><?php echo KODERS;?></td>
                <td><?php echo KODEPROP;?></td>
                <td><?php $kab = fetch_array(query("Select kabupaten from setting"));echo $kab[0];?></td>
                <td><?php $nm = fetch_array(query("Select nama_instansi from setting"));echo $nm[0];?></td>
                <td><?php echo $tahun;?></td>
                <td>4</td>
                <td>Psikiatrik</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
              </tr>
              <tr>
                <td><?php echo KODERS;?></td>
                <td><?php echo KODEPROP;?></td>
                <td><?php $kab = fetch_array(query("Select kabupaten from setting"));echo $kab[0];?></td>
                <td><?php $nm = fetch_array(query("Select nama_instansi from setting"));echo $nm[0];?></td>
                <td><?php echo $tahun;?></td>
                <td>5</td>
                <td>Anak</td>
                <td><?php $rujuk = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien , rujuk_masuk where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.no_rawat = rujuk_masuk.no_rawat and rujuk_masuk.perujuk NOT LIKE '%datang sendiri%' and pasien.umur <= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk[0]?></td>
                <td><?php $rujuk1 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien , rujuk_masuk where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.no_rawat = rujuk_masuk.no_rawat and rujuk_masuk.perujuk LIKE '%datang sendiri%' and pasien.umur <= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk1[0]?></td>
                <td><?php $rujuk2 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.stts = 'Dirawat' and reg_periksa.status_lanjut = 'Ranap' AND pasien.umur <= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk2[0]?></td>
                <td><?php $rujuk3 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.stts = 'Dirujuk' and pasien.umur <= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk3[0]?></td>
                <td><?php $rujuk4 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.stts = 'Sudah' and pasien.umur <= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk4[0]?></td>
                <td><?php $rujuk5 = fetch_array(query("select count(reg_periksa.no_rawat) from reg_periksa , pasien where reg_periksa.no_rkm_medis = pasien.no_rkm_medis and reg_periksa.stts = 'Meninggal' and pasien.umur <= 18 and reg_periksa.kd_poli = 'IGDK' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rujuk5[0]?></td>
                <td>0</td>
              </tr>
            </tbody>
        </table>
        <!--
        <div class="row clearfix">
            <form method="post" action="">
            <div class="col-sm-10">
                <div class="form-group">
                    <div class="form-line">
                        <input type="text" name="tahun" class="tahun form-control" placeholder="Pilih tahun...">
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
-->
    </div>
</div>
