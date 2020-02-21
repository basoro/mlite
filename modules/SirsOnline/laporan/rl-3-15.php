<div class="card">
    <div class="header">
        <h2>
          LAPORAN RL 3.15 (Cara Bayar)
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
                    echo '<li><a href="'.URL.'/index.php?module=SirsOnline&page=rl_3_15&tahun='.$year.'">'.$year.'</a></li>';
                  }
                  ?>
                </ul>
            </li>
        </ul>
    </div>
    <div class="body">
      <p><font size ='3' face = 'Arial'><strong>Kode RS	: <?php echo KODERS;?></strong></font></p>
      <p><font size ='3' face = 'Arial'><strong>Nama RS		: <?php $bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting"));echo $bpt['0'];?></strong></font></p>
      <p><font size ='3' face = 'Arial'><strong>Tahun	: <?php echo $tahun;?></strong></font></p>
        <div id="buttons" class="align-center m-l-10 m-b-15 export-hidden"></div>
        <table id="datatable" class="table table-bordered table-striped table-hover table-responsive display nowrap js-exportable" width="100%">
            <thead>
                <tr>
                    <!--<th>Kode RS</th>
                  	<th>Kode<br>Propinsi</th>
                  	<th>Kab/Kota</th>
                  	<th>Nama RS</th>
                  	<th>Tahun</th>-->
                  	<th>No</th>
                    <th>Cara Pembayaran</th>
                  	<th>Pasien<br> Rawat Inap<br>Jumlah <br>Pasien Keluar</th>
                  	<th>Pasien<br> Rawat Inap<br>Jumlah <br>Lama Dirawat</th>
                  	<th>Jumlah<br> Pasien<br> Rawat<br> Jalan</th>
                  	<th>Jumlah<br> Pasien<br> Rawat<br> Jalan Lab</th>
                  	<th>Jumlah<br> Pasien<br> Rawat<br> Jalan Rad</th>
                  	<th>Jumlah<br> Pasien<br> Rawat<br> Jalan <br>Lain lain</th>
                </tr>
           	</thead>
            <tbody>
            	<tr>
                    <!--<td><?php echo KODERS;?></td>
                  	<td><?php echo KODEPROP;?></td>
                  	<td><?php
              		$nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting"));echo $nm_its['0']; ?></td>
                  	<td><?php
              		$bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting"));echo $bpt['0']; ?></td>
                    <td><?php echo $tahun; ?></td>-->
                    <td>1</td>
                    <td>Membayar Sendiri</td>
                  	<td><?php $jpk = fetch_array(query("SELECT COUNT(kamar_inap.no_rawat) FROM kamar_inap , reg_periksa WHERE reg_periksa.no_rawat = kamar_inap.no_rawat AND kamar_inap.tgl_keluar BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj = 'A01'"));echo $jpk[0];?></td>
                 	<td><?php $jld = fetch_array(query("SELECT SUM(kamar_inap.lama) FROM kamar_inap , reg_periksa WHERE reg_periksa.no_rawat = kamar_inap.no_rawat AND kamar_inap.tgl_keluar BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj = 'A01'"));echo $jld[0];?></td>
                  	<td><?php $rln = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , poliklinik WHERE reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.status_lanjut = 'Ralan' AND poliklinik.nm_poli LIKE '%poli%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj = 'A01'"));echo $rln[0];?></td>
                  	<td><?php $lab = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa WHERE reg_periksa.status_lanjut = 'Ralan' AND reg_periksa.kd_poli = 'U0024' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj = 'A01'"));echo $lab[0];?></td>
                  	<td><?php $rad = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa WHERE reg_periksa.status_lanjut = 'Ralan' AND reg_periksa.kd_poli = 'U0026' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj = 'A01'"));echo $rad[0];?></td>
                  	<td><?php $ll = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa WHERE reg_periksa.status_lanjut = 'Ralan' AND reg_periksa.kd_poli = 'U0033' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj = 'A01'"));echo $ll[0];?></td>
          		</tr>
              	<tr>
                    <!--<td><?php echo KODERS;?></td>
                  	<td><?php echo KODEPROP;?></td>
                  	<td><?php
              		$nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting"));echo $nm_its['0']; ?></td>
                  	<td><?php
              		$bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting"));echo $bpt['0']; ?></td>
                    <td><?php echo $tahun; ?></td>-->
                    <td>2</td>
                    <td>Asuransi</td>
                  	<td><?php $jpk = fetch_array(query("SELECT COUNT(kamar_inap.no_rawat) FROM kamar_inap , reg_periksa WHERE reg_periksa.no_rawat = kamar_inap.no_rawat AND kamar_inap.tgl_keluar BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A02','BPJ','A05','A06','A08','A09','A11','A12','A13','A14','A18','A22')"));echo $jpk[0];?></td>
                 	<td><?php $jld = fetch_array(query("SELECT SUM(kamar_inap.lama) FROM kamar_inap , reg_periksa WHERE reg_periksa.no_rawat = kamar_inap.no_rawat AND kamar_inap.tgl_keluar BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A02','BPJ','A05','A06','A08','A09','A11','A12','A13','A14','A18','A22')"));echo $jld[0];?></td>
                  	<td><?php $rln = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , poliklinik WHERE reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.status_lanjut = 'Ralan' AND poliklinik.nm_poli LIKE '%poli%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A02','BPJ','A05','A06','A08','A09','A11','A12','A13','A14','A18','A22')"));echo $rln[0];?></td>
                  	<td><?php $lab = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa WHERE reg_periksa.status_lanjut = 'Ralan' AND reg_periksa.kd_poli = 'U0024' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A02','BPJ','A05','A06','A08','A09','A11','A12','A13','A14','A18','A22')"));echo $lab[0];?></td>
                  	<td><?php $rad = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa WHERE reg_periksa.status_lanjut = 'Ralan' AND reg_periksa.kd_poli = 'U0026' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A02','BPJ','A05','A06','A08','A09','A11','A12','A13','A14','A18','A22')"));echo $rad[0];?></td>
                  	<td><?php $ll = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa WHERE reg_periksa.status_lanjut = 'Ralan' AND reg_periksa.kd_poli = 'U0033' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A02','BPJ','A05','A06','A08','A09','A11','A12','A13','A14','A18','A22')"));echo $ll[0];?></td>
          		</tr>
              	<tr>
                    <!--<td><?php echo KODERS;?></td>
                  	<td><?php echo KODEPROP;?></td>
                  	<td><?php
              		$nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting"));echo $nm_its['0']; ?></td>
                  	<td><?php
              		$bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting"));echo $bpt['0']; ?></td>
                    <td><?php echo $tahun; ?></td>-->
                    <td>3</td>
                    <td>Keringanan (Cost Sharing)</td>
                  	<td><?php $jpk = fetch_array(query("SELECT COUNT(kamar_inap.no_rawat) FROM kamar_inap , reg_periksa , deposit WHERE kamar_inap.no_rawat = deposit.no_rawat AND reg_periksa.no_rawat = kamar_inap.no_rawat AND kamar_inap.tgl_keluar BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A02','BPJ')"));echo $jpk[0];?></td>
                 	<td><?php $jld = fetch_array(query("SELECT SUM(kamar_inap.lama) FROM kamar_inap , reg_periksa , deposit WHERE kamar_inap.no_rawat = deposit.no_rawat AND reg_periksa.no_rawat = kamar_inap.no_rawat AND kamar_inap.tgl_keluar BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A02','BPJ')"));echo $jld[0];?></td>
                  	<td><?php $rln = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , poliklinik , deposit WHERE reg_periksa.no_rawat = deposit.no_rawat AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.status_lanjut = 'Ralan' AND poliklinik.nm_poli LIKE '%poli%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A02','BPJ')"));echo $rln[0];?></td>
                  	<td><?php $lab = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , deposit WHERE reg_periksa.no_rawat = deposit.no_rawat AND reg_periksa.status_lanjut = 'Ralan' AND reg_periksa.kd_poli = 'U0024' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A02','BPJ')"));echo $lab[0];?></td>
                  	<td><?php $rad = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , deposit WHERE reg_periksa.no_rawat = deposit.no_rawat AND reg_periksa.status_lanjut = 'Ralan' AND reg_periksa.kd_poli = 'U0026' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A02','BPJ')"));echo $rad[0];?></td>
                  	<td><?php $ll = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , deposit WHERE reg_periksa.no_rawat = deposit.no_rawat AND reg_periksa.status_lanjut = 'Ralan' AND reg_periksa.kd_poli = 'U0033' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A02','BPJ')"));echo $ll[0];?></td>
          		</tr>
              	<tr>
                    <!--<td><?php echo KODERS;?></td>
                  	<td><?php echo KODEPROP;?></td>
                  	<td><?php
              		$nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting"));echo $nm_its['0']; ?></td>
                  	<td><?php
              		$bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting"));echo $bpt['0']; ?></td>
                    <td><?php echo $tahun; ?></td>-->
                    <td>4</td>
                    <td>Gratis</td>
                  	<td><?php $jpk = fetch_array(query("SELECT COUNT(kamar_inap.no_rawat) FROM kamar_inap , reg_periksa WHERE reg_periksa.no_rawat = kamar_inap.no_rawat AND kamar_inap.tgl_keluar BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A19','A20','BNN','A17')"));echo $jpk[0];?></td>
                 	<td><?php $jld = fetch_array(query("SELECT SUM(kamar_inap.lama) FROM kamar_inap , reg_periksa WHERE reg_periksa.no_rawat = kamar_inap.no_rawat AND kamar_inap.tgl_keluar BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A19','A20','BNN','A17')"));echo $jld[0];?></td>
                  	<td><?php $rln = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa , poliklinik WHERE reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.status_lanjut = 'Ralan' AND poliklinik.nm_poli LIKE '%poli%' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A19','A20','BNN','A17')"));echo $rln[0];?></td>
                  	<td><?php $lab = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa WHERE reg_periksa.status_lanjut = 'Ralan' AND reg_periksa.kd_poli = 'U0024' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A19','A20','BNN','A17')"));echo $lab[0];?></td>
                  	<td><?php $rad = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa WHERE reg_periksa.status_lanjut = 'Ralan' AND reg_periksa.kd_poli = 'U0026' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A19','A20','BNN','A17')"));echo $rad[0];?></td>
                  	<td><?php $ll = fetch_array(query("SELECT COUNT(reg_periksa.no_rawat) FROM reg_periksa WHERE reg_periksa.status_lanjut = 'Ralan' AND reg_periksa.kd_poli = 'U0033' AND reg_periksa.tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' AND reg_periksa.kd_pj IN ('A19','A20','BNN','A17')"));echo $ll[0];?></td>
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
