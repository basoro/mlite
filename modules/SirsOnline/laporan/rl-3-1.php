<div class="card">
    <div class="header">
        <h2>
          LAPORAN RL 3.1 (Rawat Inap)
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
                      echo '<li><a href="'.URL.'/index.php?module=SirsOnline&page=rl_3_1&tahun='.$year.'">'.$year.'</a></li>';
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
                    <th>Pasien <br>Awal <br>Tahun</th>
                  	<th>Pasien Masuk</th>
                  	<th>Pasien Keluar Hidup</th>
                  	<th>Pasien Meninggal <br>Kurang 48 Jam</th>
                  	<th>Pasien Meninggal <br>Lebih Dari atau <br>Sama Dengan 48 Jam</th>
                  	<th>Jumlah Lama Dirawat</th>
                  	<th>Pasien Akhir Tahun</th>
                  	<th>Jumlah Hari Perawatan</th>
                  	<th>VVIP</th>
                  	<th>VIP</th>
                  	<th>I</th>
                  	<th>II</th>
                  	<th>III</th>
                  	<th>Kelas Khusus</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sql =
            "SELECT bangsal.nm_bangsal, count(bangsal.kd_bangsal) AS jumlah_pasien, bangsal.kd_bangsal
            FROM bangsal , setting , kamar , kamar_inap
            WHERE kamar.kd_bangsal = bangsal.kd_bangsal AND kamar_inap.kd_kamar = kamar.kd_kamar
            AND kamar_inap.tgl_masuk LIKE '%{$tahun}%' AND bangsal.kd_bangsal != 'B0015'
            GROUP BY bangsal.kd_bangsal";
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
                    <td><?php if ($row['2'] == 'B0003') {echo 'Penyakit Dalam';}
              			elseif ($row['2'] == 'B0010') {echo 'Bedah';}
              			elseif ($row['2'] == 'B0012') {echo 'Obsygin';}
              			elseif ($row['2'] == 'B0007') {echo 'ICU';}
              			elseif ($row['2'] == 'B0008') {echo 'PICU';}
              			elseif ($row['2'] == 'B0005') {echo 'Kesehatan Anak';}
              			elseif ($row['2'] == 'B0006') {echo 'Perinatologi';}
              			elseif ($row['2'] == 'B0004') {echo 'Bedah Orthopedi';}
              			else {echo $row['2'];}; ?></td>
                  	<td><?php
              		$tahun_before = $tahun - 1;
              		$awal_tahun = fetch_array(query("SElECT count(kamar_inap.no_rawat) FROM kamar_inap , kamar , bangsal WHERE kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND bangsal.kd_bangsal = '{$row['2']}' AND kamar_inap.tgl_masuk LIKE '%{$tahun_before}-12-31%'")); echo $awal_tahun['0']; ?></td>
                 	<td><?php
              		$masuk = fetch_array(query("SElECT count(kamar_inap.no_rawat) FROM kamar_inap , kamar , bangsal WHERE kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND bangsal.kd_bangsal = '{$row['2']}' AND kamar_inap.tgl_masuk BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'")); echo $masuk['0']; ?></td>
                  	<td><?php
              		$keluar = fetch_array(query("SElECT count(kamar_inap.no_rawat) FROM kamar_inap , kamar , bangsal WHERE kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND kamar_inap.stts_pulang IN ('Membaik','APS','Pindah Kamar','+') AND bangsal.kd_bangsal = '{$row['2']}' AND kamar_inap.tgl_keluar BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'")); echo $keluar['0']; ?></td>
                  	<td><?php
              		$dieless = fetch_array(query("SElECT count(kamar_inap.no_rawat) FROM kamar_inap , kamar , bangsal WHERE kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND kamar_inap.stts_pulang = 'Meninggal' AND kamar_inap.lama <=2 AND bangsal.kd_bangsal = '{$row['2']}' AND kamar_inap.tgl_keluar BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'")); echo $dieless['0']; ?></td>
                  	<td><?php
              		$diemore = fetch_array(query("SElECT count(kamar_inap.no_rawat) FROM kamar_inap , kamar , bangsal WHERE kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND kamar_inap.stts_pulang = 'Meninggal' AND kamar_inap.lama >=2 AND bangsal.kd_bangsal = '{$row['2']}' AND kamar_inap.tgl_keluar BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'")); echo $diemore['0']; ?></td>
                  	<td><?php
              		$lama = fetch_array(query("SElECT SUM(kamar_inap.lama) FROM kamar_inap , kamar , bangsal WHERE kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND kamar_inap.stts_pulang != '-' AND bangsal.kd_bangsal = '{$row['2']}' AND kamar_inap.tgl_keluar BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'")); echo $lama['0']; ?></td>
                  	<td><?php
              		$akhir_tahun = fetch_array(query("SElECT count(kamar_inap.no_rawat) FROM kamar_inap , kamar , bangsal WHERE kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND bangsal.kd_bangsal = '{$row['2']}' AND kamar_inap.tgl_keluar LIKE '%{$tahun}-12-31%'")); echo $akhir_tahun['0']; ?></td>
                  	<td><?php $msk = fetch_array(query("SELECT SUM(kamar_inap.lama) FROM kamar_inap , kamar , bangsal WHERE kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND bangsal.kd_bangsal = '{$row['2']}' AND kamar_inap.tgl_masuk BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' GROUP BY kamar_inap.tgl_masuk"));
              			$klr = fetch_array(query("SELECT SUM(kamar_inap.lama) FROM kamar_inap , kamar , bangsal WHERE kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND bangsal.kd_bangsal = '{$row['2']}' AND kamar_inap.tgl_keluar BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31' GROUP BY kamar_inap.tgl_keluar"));$ttl = $akhir_tahun['0'] + $msk ['0'] - $klr ['0'];echo $ttl;?></td>
                  	<td>0</td>
                  	<td><?php
              		$vip = fetch_array(query("SElECT count(kamar_inap.no_rawat) FROM kamar_inap , kamar , bangsal WHERE kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND bangsal.kd_bangsal = '{$row['2']}' AND kamar.kelas = 'Kelas VIP' AND kamar_inap.tgl_keluar BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'")); echo $vip['0']; ?></td>
                  	<td><?php
                  	$k1 = fetch_array(query("SElECT count(kamar_inap.no_rawat) FROM kamar_inap , kamar , bangsal WHERE kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND bangsal.kd_bangsal = '{$row['2']}' AND kamar.kelas = 'Kelas 1' AND kamar_inap.tgl_keluar BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'")); echo $k1['0']; ?></td>
                  	<td><?php
              		$k2 = fetch_array(query("SElECT count(kamar_inap.no_rawat) FROM kamar_inap , kamar , bangsal WHERE kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND bangsal.kd_bangsal = '{$row['2']}' AND kamar.kelas = 'Kelas 2' AND kamar_inap.tgl_keluar BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'")); echo $k2['0']; ?></td>
                  	<td><?php
              		$k3 = fetch_array(query("SElECT count(kamar_inap.no_rawat) FROM kamar_inap , kamar , bangsal WHERE kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND bangsal.kd_bangsal = '{$row['2']}' AND kamar.kelas = 'Kelas 3' AND kamar_inap.tgl_keluar BETWEEN '{$tahun}-01-01' AND '{$tahun}-12-31'")); echo $k3['0']; ?></td>
                  	<td>0</td>
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
