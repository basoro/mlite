<div class="card">
    <div class="header">
        <h2>
          LAPORAN RL 3.14 (Rujukan)
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
                    echo '<li><a href="'.URL.'/index.php?module=SirsOnline&page=rl_3_14&tahun='.$year.'">'.$year.'</a></li>';
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
                    <th>Jenis Pelayanan</th>
                    <th>Rujukan <br>Diterima <br>dari Puskesmas</th>
                  	<th>Rujukan <br>Diterima <br>dari Fasilitas Kes Lain</th>
                  	<th>Rujukan <br>Diterima <br>dari RS Lain</th>
                  	<th>Rujukan <br>Dikembalikan <br>ke Puskesmas</th>
                  	<th>Rujukan <br>Dikembalikan <br>ke Fasilitas Kes Lain</th>
                  	<th>Rujukan <br>Dikembalikan <br>ke RS Lain</th>
                  	<th>Dirujukan Pasien Rujukan</th>
                  	<th>Dirujuk Pasien Datang Sendiri</th>
                  	<th>Dirujuk Diterima Kembali</th>

                </tr>
            </thead>
            <tbody>
            	<tr>
                    <td><?php echo KODERS; ?></td>
                  	<td><?php echo KODEPROP; ?></td>
                  	<td><?php $kab = fetch_array(query("select kabupaten from setting"));$kb = $kab[0];echo $kb;?></td>
                  	<td><?php $nama = fetch_array(query("select nama_instansi from setting"));$nm = $nama[0];echo $nm;?></td>
                    <td><?php echo $tahun;?></td>
                    <td>1</td>
                    <td>Penyakit Dalam</td>
                  	<td><?php $pus = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0002' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus[0];?></td>
                 	<td><?php $fas = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0002' AND rujuk_masuk.perujuk REGEXP 'dr|dok' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas[0];?></td>
                  	<td><?php $rs = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0002' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs[0];?></td>
                  	<td><?php $pus1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0002' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus1[0];?></td>
                  	<td><?php $fas1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0002' AND rujuk_masuk.perujuk LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas1[0];?></td>
                  	<td><?php $rs1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0002' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs1[0];?></td>
                  	<td><?php $rj = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0002' AND rujuk_masuk.perujuk NOT LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj[0];?></td>
                  	<td><?php $rj1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0002' AND rujuk_masuk.perujuk LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj1[0];?></td>
                  	<td><?php $rj2 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0002' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj2[0];?></td>
            	</tr>
              	<tr>
                    <td><?php echo KODERS; ?></td>
                  	<td><?php echo KODEPROP; ?></td>
                  	<td><?php $kab = fetch_array(query("select kabupaten from setting"));$kb = $kab[0];echo $kb;?></td>
                  	<td><?php $nama = fetch_array(query("select nama_instansi from setting"));$nm = $nama[0];echo $nm;?></td>
                    <td><?php echo $tahun;?></td>
                    <td>2</td>
                    <td>Bedah</td>
                  	<td><?php $pus = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0005' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus[0];?></td>
                 	<td><?php $fas = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0005' AND rujuk_masuk.perujuk LIKE '%dr%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas[0];?></td>
                  	<td><?php $rs = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0005' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs[0];?></td>
                  	<td><?php $pus1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0005' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus1[0];?></td>
                  	<td><?php $fas1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0005' AND rujuk_masuk.perujuk LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas1[0];?></td>
                  	<td><?php $rs1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0005' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs1[0];?></td>
                  	<td><?php $rj = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0005' AND rujuk_masuk.perujuk NOT LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj[0];?></td>
                  	<td><?php $rj1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0005' AND rujuk_masuk.perujuk LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj1[0];?></td>
                  	<td><?php $rj2 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0005' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj2[0];?></td>
            	</tr>
              	<tr>
                    <td><?php echo KODERS; ?></td>
                  	<td><?php echo KODEPROP; ?></td>
                  	<td><?php $kab = fetch_array(query("select kabupaten from setting"));$kb = $kab[0];echo $kb;?></td>
                  	<td><?php $nama = fetch_array(query("select nama_instansi from setting"));$nm = $nama[0];echo $nm;?></td>
                    <td><?php echo $tahun;?></td>
                    <td>3</td>
                    <td>Kesehatan Anak</td>
                  	<td><?php $pus = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0004' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus[0];?></td>
                 	<td><?php $fas = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0004' AND rujuk_masuk.perujuk REGEXP 'dr|dok' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas[0];?></td>
                  	<td><?php $rs = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0004' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs[0];?></td>
                  	<td><?php $pus1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0004' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus1[0];?></td>
                  	<td><?php $fas1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0004' AND rujuk_masuk.perujuk LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas1[0];?></td>
                  	<td><?php $rs1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0004' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs1[0];?></td>
                  	<td><?php $rj = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0004' AND rujuk_masuk.perujuk NOT LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj[0];?></td>
                  	<td><?php $rj1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0004' AND rujuk_masuk.perujuk LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj1[0];?></td>
                  	<td><?php $rj2 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0004' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj2[0];?></td>
            	</tr>
              	<tr>
                    <td><?php echo KODERS; ?></td>
                  	<td><?php echo KODEPROP; ?></td>
                  	<td><?php $kab = fetch_array(query("select kabupaten from setting"));$kb = $kab[0];echo $kb;?></td>
                  	<td><?php $nama = fetch_array(query("select nama_instansi from setting"));$nm = $nama[0];echo $nm;?></td>
                    <td><?php echo $tahun;?></td>
                    <td>4</td>
                    <td>Obsterik dan Ginekologi</td>
                  	<td><?php $pus = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0001' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus[0];?></td>
                 	<td><?php $fas = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0001' AND rujuk_masuk.perujuk REGEXP 'dr|dok' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas[0];?></td>
                  	<td><?php $rs = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0001' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs[0];?></td>
                  	<td><?php $pus1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0001' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus1[0];?></td>
                  	<td><?php $fas1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0001' AND rujuk_masuk.perujuk LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas1[0];?></td>
                  	<td><?php $rs1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0001' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs1[0];?></td>
                  	<td><?php $rj = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0001' AND rujuk_masuk.perujuk NOT LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj[0];?></td>
                  	<td><?php $rj1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0001' AND rujuk_masuk.perujuk LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj1[0];?></td>
                  	<td><?php $rj2 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0001' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj2[0];?></td>
            	</tr>
              	<tr>
                    <td><?php echo KODERS; ?></td>
                  	<td><?php echo KODEPROP; ?></td>
                  	<td><?php $kab = fetch_array(query("select kabupaten from setting"));$kb = $kab[0];echo $kb;?></td>
                  	<td><?php $nama = fetch_array(query("select nama_instansi from setting"));$nm = $nama[0];echo $nm;?></td>
                    <td><?php echo $tahun;?></td>
                    <td>5</td>
                    <td>Saraf</td>
                  	<td><?php $pus = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0020' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus[0];?></td>
                 	<td><?php $fas = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0020' AND rujuk_masuk.perujuk REGEXP 'dr|dok' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas[0];?></td>
                  	<td><?php $rs = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0020' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs[0];?></td>
                  	<td><?php $pus1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0020' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus1[0];?></td>
                  	<td><?php $fas1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0020' AND rujuk_masuk.perujuk LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas1[0];?></td>
                  	<td><?php $rs1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0020' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs1[0];?></td>
                  	<td><?php $rj = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0020' AND rujuk_masuk.perujuk NOT LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj[0];?></td>
                  	<td><?php $rj1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0020' AND rujuk_masuk.perujuk LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj1[0];?></td>
                  	<td><?php $rj2 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0020' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj2[0];?></td>
            	</tr>
              	<tr>
                    <td><?php echo KODERS; ?></td>
                  	<td><?php echo KODEPROP; ?></td>
                  	<td><?php $kab = fetch_array(query("select kabupaten from setting"));$kb = $kab[0];echo $kb;?></td>
                  	<td><?php $nama = fetch_array(query("select nama_instansi from setting"));$nm = $nama[0];echo $nm;?></td>
                    <td><?php echo $tahun;?></td>
                    <td>6</td>
                    <td>Jiwa</td>
                  	<td><?php $pus = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0036' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus[0];?></td>
                 	<td><?php $fas = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0036' AND rujuk_masuk.perujuk REGEXP 'dr|dok' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas[0];?></td>
                  	<td><?php $rs = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0036' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs[0];?></td>
                  	<td><?php $pus1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0036' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus1[0];?></td>
                  	<td><?php $fas1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0036' AND rujuk_masuk.perujuk LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas1[0];?></td>
                  	<td><?php $rs1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0036' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs1[0];?></td>
                  	<td><?php $rj = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0036' AND rujuk_masuk.perujuk NOT LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj[0];?></td>
                  	<td><?php $rj1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0036' AND rujuk_masuk.perujuk LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj1[0];?></td>
                  	<td><?php $rj2 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0036' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj2[0];?></td>
            	</tr>
              	<tr>
                    <td><?php echo KODERS; ?></td>
                  	<td><?php echo KODEPROP; ?></td>
                  	<td><?php $kab = fetch_array(query("select kabupaten from setting"));$kb = $kab[0];echo $kb;?></td>
                  	<td><?php $nama = fetch_array(query("select nama_instansi from setting"));$nm = $nama[0];echo $nm;?></td>
                    <td><?php echo $tahun;?></td>
                    <td>7</td>
                    <td>THT</td>
                  	<td><?php $pus = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0009' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus[0];?></td>
                 	<td><?php $fas = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0009' AND rujuk_masuk.perujuk REGEXP 'dr|dok' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas[0];?></td>
                  	<td><?php $rs = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0009' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs[0];?></td>
                  	<td><?php $pus1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0009' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus1[0];?></td>
                  	<td><?php $fas1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0009' AND rujuk_masuk.perujuk LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas1[0];?></td>
                  	<td><?php $rs1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0009' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs1[0];?></td>
                  	<td><?php $rj = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0009' AND rujuk_masuk.perujuk NOT LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj[0];?></td>
                  	<td><?php $rj1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0009' AND rujuk_masuk.perujuk LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj1[0];?></td>
                  	<td><?php $rj2 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0009' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj2[0];?></td>
            	</tr>
              	<tr>
                    <td><?php echo KODERS; ?></td>
                  	<td><?php echo KODEPROP; ?></td>
                  	<td><?php $kab = fetch_array(query("select kabupaten from setting"));$kb = $kab[0];echo $kb;?></td>
                  	<td><?php $nama = fetch_array(query("select nama_instansi from setting"));$nm = $nama[0];echo $nm;?></td>
                    <td><?php echo $tahun;?></td>
                    <td>8</td>
                    <td>Mata</td>
                  	<td><?php $pus = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0003' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus[0];?></td>
                 	<td><?php $fas = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0003' AND rujuk_masuk.perujuk REGEXP 'dr|dok' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas[0];?></td>
                  	<td><?php $rs = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0003' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs[0];?></td>
                  	<td><?php $pus1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0003' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus1[0];?></td>
                  	<td><?php $fas1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0003' AND rujuk_masuk.perujuk LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas1[0];?></td>
                  	<td><?php $rs1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0003' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs1[0];?></td>
                  	<td><?php $rj = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0003' AND rujuk_masuk.perujuk NOT LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj[0];?></td>
                  	<td><?php $rj1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0003' AND rujuk_masuk.perujuk LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj1[0];?></td>
                  	<td><?php $rj2 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0003' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj2[0];?></td>
            	</tr>
              	<tr>
                    <td><?php echo KODERS; ?></td>
                  	<td><?php echo KODEPROP; ?></td>
                  	<td><?php $kab = fetch_array(query("select kabupaten from setting"));$kb = $kab[0];echo $kb;?></td>
                  	<td><?php $nama = fetch_array(query("select nama_instansi from setting"));$nm = $nama[0];echo $nm;?></td>
                    <td><?php echo $tahun;?></td>
                    <td>9</td>
                    <td>Kulit dan Kelamin</td>
                  	<td><?php $pus = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0016' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus[0];?></td>
                 	<td><?php $fas = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0016' AND rujuk_masuk.perujuk REGEXP 'dr|dok' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas[0];?></td>
                  	<td><?php $rs = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0016' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs[0];?></td>
                  	<td><?php $pus1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0016' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus1[0];?></td>
                  	<td><?php $fas1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0016' AND rujuk_masuk.perujuk LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas1[0];?></td>
                  	<td><?php $rs1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0016' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs1[0];?></td>
                  	<td><?php $rj = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0016' AND rujuk_masuk.perujuk NOT LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj[0];?></td>
                  	<td><?php $rj1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0016' AND rujuk_masuk.perujuk LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj1[0];?></td>
                  	<td><?php $rj2 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0016' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj2[0];?></td>
            	</tr>
              	<tr>
                    <td><?php echo KODERS; ?></td>
                  	<td><?php echo KODEPROP; ?></td>
                  	<td><?php $kab = fetch_array(query("select kabupaten from setting"));$kb = $kab[0];echo $kb;?></td>
                  	<td><?php $nama = fetch_array(query("select nama_instansi from setting"));$nm = $nama[0];echo $nm;?></td>
                    <td><?php echo $tahun;?></td>
                    <td>10</td>
                    <td>Gigi dan Mulut</td>
                  	<td><?php $pus = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0017' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus[0];?></td>
                 	<td><?php $fas = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0017' AND rujuk_masuk.perujuk REGEXP 'dr|dok' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas[0];?></td>
                  	<td><?php $rs = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0017' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs[0];?></td>
                  	<td><?php $pus1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0017' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus1[0];?></td>
                  	<td><?php $fas1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0017' AND rujuk_masuk.perujuk LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas1[0];?></td>
                  	<td><?php $rs1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0017' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs1[0];?></td>
                  	<td><?php $rj = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0017' AND rujuk_masuk.perujuk NOT LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj[0];?></td>
                  	<td><?php $rj1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0017' AND rujuk_masuk.perujuk LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj1[0];?></td>
                  	<td><?php $rj2 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0017' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj2[0];?></td>
            	</tr>
              	<tr>
                    <td><?php echo KODERS; ?></td>
                  	<td><?php echo KODEPROP; ?></td>
                  	<td><?php $kab = fetch_array(query("select kabupaten from setting"));$kb = $kab[0];echo $kb;?></td>
                  	<td><?php $nama = fetch_array(query("select nama_instansi from setting"));$nm = $nama[0];echo $nm;?></td>
                    <td><?php echo $tahun;?></td>
                    <td>11</td>
                    <td>Instalasi Radiologi</td>
                  	<td><?php $pus = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0026' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus[0];?></td>
                 	<td><?php $fas = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0026' AND rujuk_masuk.perujuk REGEXP 'dr|dok' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas[0];?></td>
                  	<td><?php $rs = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0026' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs[0];?></td>
                  	<td><?php $pus1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0026' AND rujuk_masuk.perujuk REGEXP 'pus|pkm|p.|pos' AND rujuk_masuk.perujuk NOT LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $pus1[0];?></td>
                  	<td><?php $fas1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0026' AND rujuk_masuk.perujuk LIKE '%dr%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $fas1[0];?></td>
                  	<td><?php $rs1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.kd_poli = 'U0026' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.stts = 'Batal' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $rs1[0];?></td>
                  	<td><?php $rj = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0026' AND rujuk_masuk.perujuk NOT LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj[0];?></td>
                  	<td><?php $rj1 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0026' AND rujuk_masuk.perujuk LIKE '%datang sendiri%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj1[0];?></td>
                  	<td><?php $rj2 = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , rujuk_masuk , rujuk WHERE reg_periksa.no_rawat = rujuk_masuk.no_rawat AND reg_periksa.no_rawat = rujuk.no_rawat AND reg_periksa.kd_poli = 'U0026' AND rujuk_masuk.perujuk LIKE '%rs%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND rujuk.rujuk_ke NOT LIKE '%datang sendiri%'"));echo $rj2[0];?></td>
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
