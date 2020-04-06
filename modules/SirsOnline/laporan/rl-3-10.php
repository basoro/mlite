<div class="card">
    <div class="header">
        <h2>
          LAPORAN RL 3.10 (Pelayanan Khusus)
          <small><?php if (isset($_GET['tahun'])) {
    $tahun = $_GET['tahun'];
} else {
    $tahun = date("Y", strtotime($date));
}; echo "Periode ".$tahun; ?></small>
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
                      echo '<li><a href="'.URL.'/?module=SirsOnline&page=rl_3_10&tahun='.$year.'">'.$year.'</a></li>';
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
                <tr>
                  <td>6307012</td>
                  <td>63prop</td>
                  <td><?php $nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting")); echo $nm_its['0']; ?></td>
                  <td><?php $bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting")); echo $bpt['0']; ?></td>
                  <td><?php echo $tahun; ?></td>
                  <td>1</td>
                  <td>Elektro Kardiographi (EKG)</td>
                  <td><?php $query = fetch_array(query("SELECT COUNT(no_rawat) as jml FROM rawat_jl_pr WHERE kd_jenis_prw ='J000301' AND tgl_perawatan BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $query['0'];?></td>
                </tr>
                <tr>
                  <td>6307012</td>
                  <td>63prop</td>
                  <td><?php $nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting")); echo $nm_its['0']; ?></td>
                  <td><?php $bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting")); echo $bpt['0']; ?></td>
                  <td><?php echo $tahun; ?></td>
                  <td>2</td>
                  <td>Elektro Myographi (EMG)</td>
                  <td>0</td>
                </tr>
                <tr>
                  <td>6307012</td>
                  <td>63prop</td>
                  <td><?php $nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting")); echo $nm_its['0']; ?></td>
                  <td><?php $bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting")); echo $bpt['0']; ?></td>
                  <td><?php echo $tahun; ?></td>
                  <td>3</td>
                  <td>Echo Cardiographi (ECG)</td>
                  <td>0</td>
                </tr>
                <tr>
                  <td>6307012</td>
                  <td>63prop</td>
                  <td><?php $nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting")); echo $nm_its['0']; ?></td>
                  <td><?php $bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting")); echo $bpt['0']; ?></td>
                  <td><?php echo $tahun; ?></td>
                  <td>4</td>
                  <td>Endoskopi (semua bentuk)</td>
                  <td>0</td>
                </tr>
                <tr>
                  <td>6307012</td>
                  <td>63prop</td>
                  <td><?php $nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting")); echo $nm_its['0']; ?></td>
                  <td><?php $bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting")); echo $bpt['0']; ?></td>
                  <td><?php echo $tahun; ?></td>
                  <td>5</td>
                  <td>Hemodialisa</td>
                  <td><?php $query = fetch_array(query("SELECT COUNT(no_rawat) FROM `reg_periksa` WHERE kd_poli = 'U0019' and tgl_registrasi BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'"));echo $query['0']; ?></td>
                </tr>
                <tr>
                  <td>6307012</td>
                  <td>63prop</td>
                  <td><?php $nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting")); echo $nm_its['0']; ?></td>
                  <td><?php $bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting")); echo $bpt['0']; ?></td>
                  <td><?php echo $tahun; ?></td>
                  <td>6</td>
                  <td>Densometri Tulang</td>
                  <td>0</td>
                </tr>
                <tr>
                  <td>6307012</td>
                  <td>63prop</td>
                  <td><?php $nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting")); echo $nm_its['0']; ?></td>
                  <td><?php $bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting")); echo $bpt['0']; ?></td>
                  <td><?php echo $tahun; ?></td>
                  <td>7</td>
                  <td>Pungsi</td>
                  <td>0</td>
                </tr>
                <tr>
                  <td>6307012</td>
                  <td>63prop</td>
                  <td><?php $nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting")); echo $nm_its['0']; ?></td>
                  <td><?php $bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting")); echo $bpt['0']; ?></td>
                  <td><?php echo $tahun; ?></td>
                  <td>8</td>
                  <td>Spirometri</td>
                  <td>0</td>
                </tr>
                <tr>
                  <td>6307012</td>
                  <td>63prop</td>
                  <td><?php $nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting")); echo $nm_its['0']; ?></td>
                  <td><?php $bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting")); echo $bpt['0']; ?></td>
                  <td><?php echo $tahun; ?></td>
                  <td>9</td>
                  <td>Tes Kulit/Alergi/Histamin</td>
                  <td>0</td>
                </tr>
                <tr>
                  <td>6307012</td>
                  <td>63prop</td>
                  <td><?php $nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting")); echo $nm_its['0']; ?></td>
                  <td><?php $bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting")); echo $bpt['0']; ?></td>
                  <td><?php echo $tahun; ?></td>
                  <td>10</td>
                  <td>Topometri</td>
                  <td>0</td>
                </tr>
                <tr>
                  <td>6307012</td>
                  <td>63prop</td>
                  <td><?php $nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting")); echo $nm_its['0']; ?></td>
                  <td><?php $bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting")); echo $bpt['0']; ?></td>
                  <td><?php echo $tahun; ?></td>
                  <td>11</td>
                  <td>Akupuntur</td>
                  <td>0</td>
                </tr>
                <tr>
                  <td>6307012</td>
                  <td>63prop</td>
                  <td><?php $nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting")); echo $nm_its['0']; ?></td>
                  <td><?php $bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting")); echo $bpt['0']; ?></td>
                  <td><?php echo $tahun; ?></td>
                  <td>12</td>
                  <td>Hiperbarik</td>
                  <td>0</td>
                </tr>
                <tr>
                  <td>6307012</td>
                  <td>63prop</td>
                  <td><?php $nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting")); echo $nm_its['0']; ?></td>
                  <td><?php $bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting")); echo $bpt['0']; ?></td>
                  <td><?php echo $tahun; ?></td>
                  <td>13</td>
                  <td>Herbal/Jamu</td>
                  <td>0</td>
                </tr>
                <tr>
                  <td>6307012</td>
                  <td>63prop</td>
                  <td><?php $nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting")); echo $nm_its['0']; ?></td>
                  <td><?php $bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting")); echo $bpt['0']; ?></td>
                  <td><?php echo $tahun; ?></td>
                  <td>88</td>
                  <td>Lain-lain</td>
                  <td>0</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
