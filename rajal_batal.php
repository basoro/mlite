<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Grafik Kunjungan Pasien';
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

            <!-- #END# Widgets -->
            <!-- Bar chartjs -->
            <div class="card">
                <div class="header">
                    <h2>GRAFIK KUNJUNGAN PASIEN</h2>
                </div>
                <div class="body">
                    <canvas id="bar_chart" height="350"></canvas>
                </div>
            </div>
            <!-- #End# Bar chartjs -->
            <!-- Bar chartjs -->

            <!-- #End# Bar chartjs -->
            <!-- Summary -->

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
                      type: 'line',
                      data: {
                          labels: ["15", "16", "18", "20", "22", "23", "24", "25", "26", "27", "29", "30", "2", "3", "4", "6", "7", "8", "9", "10", "11", "13", "14", "15"],
                          datasets: [{
                              label: "Batal",
                              data: [
                                <?php
                                    $sql = "SELECT ifnull(count(*),0) AS batal
                                        FROM reg_periksa
                                        WHERE stts = 'Batal'
                                        AND tgl_registrasi BETWEEN '2019-04-15' AND '2019-05-15'
                                        GROUP BY tgl_registrasi";
                                    $hasil=query($sql);
                                    while ($data = fetch_array ($hasil)){
                                        $jumlah = $data['batal'].', ';
                                        echo $jumlah;
                                    }
                                ?>
                              ],
                              backgroundColor: 'rgba(255, 81, 254, 0.8)'
                              },{
                              label: "Booking",
                              data: [
                                <?php
                                    $sql = "SELECT count(*) AS jumlah
                                        FROM booking_registrasi
                                        WHERE tanggal_periksa BETWEEN '2019-04-15' AND '2019-05-15' AND tanggal_periksa NOT IN ('2019-04-17', '2019-04-19', '2019-04-21', '2019-04-28', '2019-05-01', '2019-05-12')
                                        AND kd_poli NOT IN ('IGDK' , 'U0007' , 'U0019' , 'U0024' , 'U0025' , 'U0026' , 'U0027' , 'U0033' , 'U0031')
                                        GROUP BY tanggal_periksa";
                                    $hasil=query($sql);
                                    while ($data = fetch_array ($hasil)){
                                        $jumlah = $data['jumlah'].', ';
                                        echo $jumlah;
                                    }
                                ?>
                              ],
                              backgroundColor: 'rgba(45, 48, 255, 0.8)'
                          }, {
                              label: "Total Daftar",
                              data: [
                                <?php
                                    $sql = "SELECT IFNULL(count(reg_periksa.no_rawat),0) AS jml
                                        FROM reg_periksa
                                        WHERE reg_periksa.kd_poli NOT IN ('IGDK' , 'U0007' , 'U0019' , 'U0024' , 'U0025' , 'U0026' , 'U0027' , 'U0033' , 'U0031')
                                        AND reg_periksa.tgl_registrasi BETWEEN '2019-04-15' AND '2019-05-15' AND reg_periksa.tgl_registrasi NOT IN ('2019-04-28')
                                        GROUP BY reg_periksa.tgl_registrasi";
                                    $hasil=query($sql);
                                    while ($data1 = fetch_array ($hasil)){
                                        $jumlah = $data1['jml'].', ';
                                        echo $jumlah;
                                    }
                                ?>
                              ],
                              backgroundColor: 'rgba(112, 252, 96, 0.8)'
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
