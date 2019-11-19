<div class="card">
    <div class="header">
        <h2>
          LAPORAN RL 3.3 (Gigi & Mulut)
            <small><?php if(isset($_GET['tahun'])) { $tahun = $_GET['tahun']; } else { $tahun = date("Y",strtotime($date)); }; echo "Periode ".$tahun; ?></small>
        </h2>
        <ul class="header-dropdown m-r--5">
            <li class="dropdown">
                <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    <i class="material-icons">more_vert</i>
                </a>
                <ul class="dropdown-menu pull-right">
                    <li><a href="rl-3-3.php?tahun=2016">2016</a></li>
                    <li><a href="rl-3-3.php?tahun=2017">2017</a></li>
                    <li><a href="rl-3-3.php?tahun=2018">2018</a></li>
                    <li><a href="rl-3-3.php?tahun=2019">2019</a></li>
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
                  	<th>Kode ICD</th>
                    <th>Jenis Kegiatan</th>
                    <th>Jumlah</th>
                 </tr>
            </thead>
            <tbody>
            <?php
            $sql =
            "SELECT icd9.deskripsi_panjang ,icd9.kode
            FROM reg_periksa , prosedur_pasien , icd9 , setting
            WHERE reg_periksa.no_rawat = prosedur_pasien.no_rawat AND prosedur_pasien.kode = icd9.kode
            AND reg_periksa.kd_poli IN ('U0041','U0017') AND prosedur_pasien.status = 'Ralan'
            AND reg_periksa.tgl_registrasi LIKE '%{$tahun}%'
            GROUP BY prosedur_pasien.kode";
            $query = query($sql);
            $no = 1;
            while($row = fetch_array($query)) {

            ?>
                <tr>
                    <td>6307012</td>
                  	<td>63prop</td>
                  	<td><?php
              		$nm_its = fetch_array(query("SELECT setting.kabupaten FROM setting"));echo $nm_its['0']; ?></td>
                  	<td><?php
              		$bpt = fetch_array(query("SELECT setting.nama_instansi FROM setting"));echo $bpt['0']; ?></td>
                    <td><?php echo $tahun; ?></td>
                    <td><?php echo $no; ?></td>
                  	<td><?php echo $row['1']; ?></td>
                    <td><?php echo $row['0']; ?></td>
                  	<td><?php
              		$awal_tahun = fetch_array(query("SElECT count(reg_periksa.no_rawat) FROM reg_periksa , prosedur_pasien , icd9 WHERE reg_periksa.no_rawat = prosedur_pasien.no_rawat AND prosedur_pasien.kode = icd9.kode AND icd9.kode = '{$row['1']}'")); echo $awal_tahun['0']; ?></td>
                 	</tr>
            <?php
            $no++;
            }
            ?>
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
