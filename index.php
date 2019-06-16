<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Dashboard';
include_once('config.php');
include_once('layout/header.php');
include_once('layout/sidebar.php');
?>

    <section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <p class="col-orange font-24 font-uppercase"><?php echo $title; ?></p>
            </div>
            <!-- Widgets -->
            <div class="row clearfix">
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-pink hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">enhanced_encryption</i>
                        </div>
                        <div class="content">
                            <div class="text">TOTAL PASIEN</div>
                            <div class="number count-to" data-from="0" data-to="<?php echo num_rows(query("SELECT no_rkm_medis FROM pasien"));?>" data-speed="5000" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-cyan hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">group_add</i>
                        </div>
                        <div class="content">
                            <div class="text">PASIEN TAHUN INI</div>
                            <div class="number count-to" data-from="0" data-to="<?php echo num_rows(query("SELECT no_rkm_medis FROM pasien WHERE tgl_daftar LIKE '%$month%'"));?>" data-speed="2000" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-light-green hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">people</i>
                        </div>
                        <div class="content">
                            <div class="text">PASIEN BULAN INI</div>
                            <div class="number count-to" data-from="0" data-to="<?php echo num_rows(query("SELECT no_rawat FROM reg_periksa WHERE tgl_registrasi LIKE '%$month%'"));?>" data-speed="1000" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-orange hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">person</i>
                        </div>
                        <div class="content">
                            <div class="text">PASIEN HARI INI</div>
                            <div class="number count-to" data-from="0" data-to="<?php echo num_rows(query("SELECT no_rawat FROM reg_periksa WHERE tgl_registrasi LIKE '%$date%'"));?>" data-speed="1000" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- #END# Widgets -->
            <!-- Bar chartjs -->
            <div class="card">
                <div class="header">
                    <h2>GRAFIK KUNJUNGAN PASIEN DALAM TAHUN</h2>
                </div>
                <div class="body">
                    <canvas id="bar_chart" height="150"></canvas>
                </div>
            </div>
            <!-- #End# Bar chartjs -->
            <!-- Bar chartjs -->
            <div class="card">
                <div class="header">
                    <h2>POLIKLINIK HARI INI</h2>
                </div>
                <div class="body">
                    <canvas id="line_chart" height="150"></canvas>
                </div>
            </div>
            <!-- #End# Bar chartjs -->
            <!-- Summary -->
            <div class="row clearfix">
                <!-- Visitors -->
                <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                    <div class="card">
                        <div class="body bg-pink">
                            <div class="sparkline" data-type="line" data-spot-Radius="4" data-highlight-Spot-Color="rgb(233, 30, 99)" data-highlight-Line-Color="#fff"
                                 data-min-Spot-Color="rgb(255,255,255)" data-max-Spot-Color="rgb(255,255,255)" data-spot-Color="rgb(255,255,255)"
                                 data-offset="90" data-width="100%" data-height="92px" data-line-Width="2" data-line-Color="rgba(255,255,255,0.7)"
                                 data-fill-Color="rgba(0, 188, 212, 0)">
                                 <?php
                                     $sql_grafik_ranap = query("SELECT count(*) AS jumlah
                                         FROM reg_periksa
                                         WHERE MONTH(tgl_registrasi)
                                         AND status_lanjut = 'Ranap'
                                         AND kd_poli != 'U0027'
                                         AND YEAR(STR_TO_DATE(tgl_registrasi,'%Y-%m-%d')) = '2018'
                                         GROUP BY EXTRACT(MONTH FROM tgl_registrasi)");
                                     while ($data = fetch_array ($sql_grafik_ranap)){
                                         echo $data['jumlah'].', ';
                                     }
                                 ?>
                            </div>
                            <ul class="dashboard-stat-list">
                                <li>
                                    PASIEN RANAP TAHUN INI
                                    <?php
                                    $ranap_th_ini = fetch_assoc(query("SELECT count(*) AS jumlah
                                    FROM reg_periksa
                                    WHERE status_lanjut = 'Ranap'
                                    AND kd_poli != 'U0027'
                                    AND YEAR(STR_TO_DATE(tgl_registrasi,'%Y-%m-%d')) = $year"));
                                    ?>
                                    <span class="pull-right"><b><?php echo $ranap_th_ini['jumlah']; ?></b> <small>ORANG</small></span>
                                </li>
                                <li>
                                    PASIEN RANAP BULAN INI
                                    <?php
                                    $ranap_bln_ini = fetch_assoc(query("SELECT count(*) AS jumlah
                                    FROM reg_periksa
                                    WHERE status_lanjut = 'Ranap'
                                    AND kd_poli != 'U0027'
                                    AND MONTH(STR_TO_DATE(tgl_registrasi,'%Y-%m-%d')) = $curr_month
                                    AND YEAR(STR_TO_DATE(tgl_registrasi,'%Y-%m-%d')) = $year"));
                                    ?>
                                    <span class="pull-right"><b><?php echo $ranap_bln_ini['jumlah']; ?></b> <small>ORANG</small></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- #END# Visitors -->
                <!-- Latest Social Trends -->
                <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                    <div class="card">
                        <div class="body bg-cyan">
                            <div class="m-b--35 font-bold">KELAS KAMAR PASIEN RANAP</div>
                            <ul class="dashboard-stat-list">
                                <li>
                                    KAMAR KELAS 1
                                    <?php
                                    $kmr_kls_1 = fetch_assoc(query("SELECT COUNT(*) AS jumlah
                                    FROM kamar
                                    WHERE kelas = 'Kelas 1'
                                    AND statusdata='1'
                                    AND status='ISI'"));
                                    ?>
                                    <span class="pull-right"><b><?php echo $kmr_kls_1['jumlah']; ?></b> <small>ORANG</small></span>
                                </li>
                                <li>
                                    KAMAR KELAS 2
                                    <?php
                                    $kmr_kls_2 = fetch_assoc(query("SELECT COUNT(*) AS jumlah
                                    FROM kamar
                                    WHERE kelas = 'Kelas 2'
                                    AND statusdata='1'
                                    AND status='ISI'"));
                                    ?>
                                    <span class="pull-right"><b><?php echo $kmr_kls_2['jumlah']; ?></b> <small>ORANG</small></span>
                                </li>
                                <li>
                                    KAMAR KELAS 3
                                    <?php
                                    $kmr_kls_3 = fetch_assoc(query("SELECT COUNT(*) AS jumlah
                                    FROM kamar
                                    WHERE kelas = 'Kelas 3'
                                    AND statusdata='1'
                                    AND status='ISI'"));
                                    ?>
                                    <span class="pull-right"><b><?php echo $kmr_kls_3['jumlah']; ?></b> <small>ORANG</small></span>
                                </li>
                                <li>
                                    KAMAR VIP
                                    <?php
                                    $kmr_kls_vip = fetch_assoc(query("SELECT COUNT(*) AS jumlah
                                    FROM kamar
                                    WHERE kelas = 'Kelas VIP'
                                    AND statusdata='1'
                                    AND status='ISI'"));
                                    ?>
                                    <span class="pull-right"><b><?php echo $kmr_kls_vip['jumlah']; ?></b> <small>ORANG</small></span>
                                </li>
                                <li>
                                    KAMAR VVIP
                                    <?php
                                    $kmr_kls_vvip = fetch_assoc(query("SELECT COUNT(*) AS jumlah
                                    FROM kamar
                                    WHERE kelas = 'Kelas VVIP'
                                    AND statusdata='1'
                                    AND status='ISI'"));
                                    ?>
                                    <span class="pull-right"><b><?php echo $kmr_kls_vvip['jumlah']; ?></b> <small>ORANG</small></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- #END# Latest Social Trends -->
                <!-- Answered Tickets -->
                <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                    <div class="card">
                        <div class="body bg-teal">
                            <div class="font-bold m-b--35">SUMMARY ADMISSION</div>
                            <ul class="dashboard-stat-list">
                                <li>
                                    PASIEN IGD
                                    <?php
                                    $pasien_igd = fetch_assoc(query("SELECT count(*) AS jumlah
                                    FROM reg_periksa
                                    WHERE kd_poli = 'IGDK'
                                    AND tgl_registrasi = '{$date}'"));
                                    ?>
                                    <span class="pull-right"><b><?php echo $pasien_igd['jumlah']; ?></b> <small>Orang</small></span>
                                </li>
                                <li>
                                    PASIEN RANAP
                                    <?php
                                    $pasien_ranap = fetch_assoc(query("SELECT count(*) AS jumlah
                                    FROM reg_periksa
                                    WHERE status_lanjut = 'Ranap'
                                    AND kd_poli != 'U0027'
                                    AND status_bayar = 'Belum Bayar'"));
                                    ?>
                                    <span class="pull-right"><b><?php echo $pasien_ranap['jumlah']; ?></b> <small>ORANG</small></span>
                                </li>
                                <li>
                                    BED TERISI
                                    <?php
                                    $bed_terisi = fetch_assoc(query("SELECT COUNT(*) AS jumlah
                                    FROM kamar
                                    WHERE statusdata='1'
                                    AND status='ISI'"));
                                    ?>
                                    <span class="pull-right"><b><?php echo $bed_terisi['jumlah']; ?></b> <small>TEMPAT TIDUR</small></span>
                                </li>
                                <li>
                                    BED KOSONG
                                    <?php
                                    $bed_kosong = fetch_assoc(query("SELECT COUNT(*) AS jumlah
                                    FROM kamar
                                    WHERE statusdata='1'
                                    AND status='KOSONG'"));
                                    ?>
                                    <span class="pull-right"><b><?php echo $bed_kosong['jumlah']; ?></b> <small>TEMPAT TIDUR</small></span>
                                </li>
                                <li>
                                    TOTAL KAPASITAS BED
                                    <span class="pull-right"><b><?php echo $bed_terisi['jumlah']+$bed_kosong['jumlah']; ?></b> <small>TEMPAT TIDUR</small></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- #END# Answered Tickets -->
            </div>
            <!-- #End# Summary -->
            <?php
            if (isset($_SESSION['jenis_poli']) && $_SESSION['jenis_poli'] !=="" && $_SESSION['role'] !=="Paramedis_Ranap") {
            ?>
            <?php
            // Get jenis poli
            $jenispoli=isset($_SESSION['jenis_poli'])?$_SESSION['jenis_poli']:NULL;
            $query_poli = query("SELECT * from poliklinik WHERE kd_poli = '".$jenispoli."'");
            $data_poli = fetch_array($query_poli);
            if ($jenispoli == $data_poli['0']) {
                $nmpoli = $data_poli['1'];
            }
            ?>
            <div class="row clearfix">
                <!-- Pasien Paling Aktif -->
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <div class="card">
                        <div class="header">
                            <h2>Pasien <?php echo ucwords(strtolower($nmpoli)); ?> Paling Aktif</h2>
                        </div>
                        <div class="body">
                            <table id="antrian_pasien" class="table table-bordered table-striped table-hover display nowrap dashboard-task-infos" width="100%">
	                             <thead>
		                               <tr>
		                                   <th>No</th>
		                                   <th>Nama Lengkap</th>
		                                   <th>Kunj</th>
		                               </tr>
	                             </thead>
	                             <tbody>
	                             <?php
		                           $sql = query("SELECT no_rkm_medis, count(no_rkm_medis) jumlah FROM reg_periksa WHERE kd_dokter = '{$_SESSION['username']}' GROUP BY no_rkm_medis ORDER BY jumlah DESC LIMIT 10");
		                           $no=1;
		                           while($row = fetch_array($sql)){
		                               $getNama = fetch_assoc(query("SELECT nm_pasien FROM pasien WHERE no_rkm_medis = '{$row['no_rkm_medis']}'"));
		                               echo '<tr>';
		                               echo '<td>'.$no.'</td>';
		                               echo '<td>'.ucwords(strtolower($getNama['nm_pasien'])).'</td>';
		                               echo '<td>'.$row['jumlah'].'</td>';
		                               echo '</tr>';
		                               $no++;
	                             }
	                             ?>
	                             </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- #END# Pasien Paling Aktif -->
                <!-- Antrian Pasien -->
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <div class="card">
                        <div class="header">
                            <h2>Antrian 10 Pasien Terakhir <?php echo ucwords(strtolower($nmpoli)); ?></h2>
                        </div>
                        <div class="body">
                            <table id="antrian_pasien" class="table table-bordered table-striped table-hover display nowrap dashboard-task-infos" width="100%">
                               <thead>
                                  <tr>
        		                          <th>No</th>
                                      <th>Nama Lengkap</th>
                                      <th>Status</th>
                                  </tr>
                               </thead>
                               <tbody>
                               <?php
                               $sql = query("SELECT a.no_rawat, b.no_rkm_medis, b.nm_pasien, a.stts FROM reg_periksa a, pasien b WHERE a.kd_poli = '{$_SESSION['jenis_poli']}' AND a.no_rkm_medis = b.no_rkm_medis AND a.tgl_registrasi = '$date' ORDER BY a.jam_reg ASC LIMIT 10");
		                           $no=1;
                               while($row = fetch_array($sql)){
                                  echo '<tr>';
        		                      echo '<td>'.$no.'</td>';
                                  echo '<td>';
                                  echo '<a href="pasien-ralan.php?action=view&no_rawat='.$row['0'].'" class="title">'.ucwords(strtolower($row['2'])).'</a>';
                                  echo '</td>';
                                  echo '<td>'.$row['3'].'</td>';
                                  echo '</tr>';
		                              $no++;
                               }
                               ?>
                               </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- #END# Antrian Pasien -->
            </div>
          <?php } ?>
        </div>
    </section>

<?php
include_once('layout/footer.php');
?>

    <script>

          $(function () {
              new Chart(document.getElementById("bar_chart").getContext("2d"), getChartJs('bar'));
              new Chart(document.getElementById("line_chart").getContext("2d"), getChartJs('line'));
              initSparkline();
          });

          function getChartJs(type) {
              var config = null;

              if (type === 'bar') {
                  config = {
                      type: 'bar',
                      data: {
                          labels: ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "Sepetember", "Oktober", "November", "Desember"],
                          datasets: [{
                              label: "Tahun 2018",
                              data: [
                                <?php
                                    $sql = "SELECT count(*) AS jumlah
                                        FROM reg_periksa
                                        WHERE MONTH(tgl_registrasi)
                                        AND YEAR(STR_TO_DATE(tgl_registrasi,'%Y-%m-%d')) = '2018'
                                        GROUP BY EXTRACT(MONTH FROM tgl_registrasi)";
                                    $hasil=query($sql);
                                    while ($data = fetch_array ($hasil)){
                                        $jumlah = $data['jumlah'].', ';
                                        echo $jumlah;
                                    }
                                ?>
                              ],
                              backgroundColor: 'rgba(0, 188, 212, 0.8)'
                          }, {
                              label: "Tahun 2019",
                              data: [
                                <?php
                                    $sql = "SELECT count(*) AS jumlah
                                        FROM reg_periksa
                                        WHERE MONTH(tgl_registrasi)
                                        AND YEAR(STR_TO_DATE(tgl_registrasi,'%Y-%m-%d')) = '2019'
                                        GROUP BY EXTRACT(MONTH FROM tgl_registrasi)";
                                    $hasil=query($sql);
                                    while ($data = fetch_array ($hasil)){
                                        $jumlah = $data['jumlah'].', ';
                                        echo $jumlah;
                                    }
                                ?>
                              ],
                              backgroundColor: 'rgba(233, 30, 99, 0.8)'
                              }]
                      },
                      options: {
                          responsive: true,
                          maintainAspectRatio: false,
                          legend: false
                      }
                  }
              }
              if (type === 'line') {
                  config = {
                      type: 'bar',
                      data: {
                          labels: [
                            <?php
                            	$sql_poli = "SELECT a.nm_poli AS nm_poli FROM poliklinik a, reg_periksa b WHERE a.kd_poli = b.kd_poli AND b.tgl_registrasi = '{$date}' GROUP BY b.kd_poli";
                            	$hasil_poli = query($sql_poli);
                                    while ($data = fetch_array ($hasil_poli)){
                                        $get_poli = '"'.$data[nm_poli].'", ';
                                        echo $get_poli;
                                    }
                            ?>
                          ],
                          datasets: [{
                              label: "Tahun 2019",
                              data: [
                                <?php
                                    $sql = "SELECT count(*) AS jumlah
                                        FROM reg_periksa
                                        WHERE tgl_registrasi = '{$date}'
                                        GROUP BY kd_poli";
                                    $hasil=query($sql);
                                    while ($data = fetch_array ($hasil)){
                                        $jumlah = $data['jumlah'].', ';
                                        echo $jumlah;
                                    }
                                ?>
                              ],
                              backgroundColor: 'rgba(9, 31, 146, 1)'
                              }]
                      },
                      options: {
                          responsive: true,
                          maintainAspectRatio: false,
                          legend: false
                      }
                  }
              }
              return config;
          }

          function initSparkline() {
              $(".sparkline").each(function () {
                  var $this = $(this);
                  $this.sparkline('html', $this.data());
              });
          }

    </script>
