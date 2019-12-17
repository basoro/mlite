<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : dentix.id@gmail.com
* Licence under GPL
***/

$getmodule = isset($_GET['module'])?$_GET['module']:null;

if(!$getmodule) {
  $title = 'Dashboard';
} else {
  $title = $_GET['module'];
}

include_once('config.php');
include_once('layout/header.php');
include_once('layout/sidebar.php');

?>

<?php if(!$getmodule) { ?>
    <section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <p class="col-orange font-24 font-uppercase"><?php echo $title; ?> <?php $last = json_decode($json_updates, true); $last[] = $last; if($last['0']['versi'] > VERSION) { echo '<a href="./update.php" class="btn btn-lg bg-red text-white right">Update Ke V.'.$last['0']['versi'].'</a>'; } ?></p>
            </div>
            <div class="row clearfix">
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-pink hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">enhanced_encryption</i>
                        </div>
                        <div class="content">
                            <div class="text">TOTAL PASIEN</div>
                            <div class="number count-to" data-from="0" data-to="<?php echo num_rows(query("SELECT no_rkm_medis FROM pasien"));?>" data-speed="1000" data-fresh-interval="20"></div>
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
                            <div class="number count-to" data-from="0" data-to="<?php echo num_rows(query("SELECT no_rkm_medis FROM pasien WHERE tgl_daftar LIKE '%$month%'"));?>" data-speed="1000" data-fresh-interval="20"></div>
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
            <div class="row clearfix">
              <div class="col-lg-12">
                <div class="card">
                    <div class="header">
                        <h2>POLIKLINIK HARI INI</h2>
                    </div>
                    <div class="body">
                        <canvas id="line_chart" height="250"></canvas>
                    </div>
                </div>
              </div>
            </div>
            <div class="row clearfix">
              <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                  <div class="card">
                      <div class="header">
                          <h2>MODUL-MODUL</h2>
                      </div>
                      <div class="setting-con" style="padding:0; height:381px; margin-right: 0px; overflow: hidden;overflow-y:scroll">
                          <div class="container-fluid module">
                              <div class="row">
                              <?php
                              if($_SESSION['role'] == 'Admin') {
                                foreach (glob("modules/*/index.php") as $filename) {
                                  include $filename;
                                }
                              } else if(!empty($getUserModule['module'])) {
                                foreach ($userModules as $key=>$filename) {
                                    $filename = str_replace(" ", "", $filename);
                                    include ("modules/".$filename."/index.php");
                                }
                              } else {
                                echo '<div class="alert bg-pink alert-dismissible" style="margin:20px;">Module Tidak Tersedia!</div>';
                              }
                              ?>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
              <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                 <div class="card">
                    <div class="header">
                       <h2>KUNJUNGAN PASIEN DALAM TAHUN</h2>
                    </div>
                    <div class="body">
                       <canvas id="bar_chart" height="341px"></canvas>
                    </div>
                 </div>
              </div>
            </div>
        </div>
    </section>
<?php } else { ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

                    <?php
                    if(file_exists($module_base_dir.$module. '/' .$module_base_file)) {
                      if(isset($_GET['page'])) {
                        $page = $_GET['page'];
                      } else {
                        $page = 'index';
                      }
                      include($module_base_dir.$module. '/' .$module_base_file);
                      $moduleClass = new $module;
                      if($role == 'Admin') {
                        if(method_exists($moduleClass, $page)) {
                          $moduleClass->$page();
                        } else {
                          echo '<div class="alert bg-pink alert-dismissible" role="alert">Halaman tidak ditemukan!</div>';
                        }
                      } else {
                        if(in_array($module, $userModules)) {
                          if(method_exists($moduleClass, $page)) {
                            $moduleClass->$page();
                          } else {
                            echo '<div class="alert bg-pink alert-dismissible" role="alert">Halaman tidak ditemukan!</div>';
                          }
                        } else {
                          echo '<div class="alert bg-pink alert-dismissible" role="alert">Anda tidak punyak hak akses!</div>';
                        }
                      }
                    } else {
                      echo '<div class="alert bg-pink alert-dismissible" role="alert">Modul tidak ditemukan!</div>';
                    }
                    ?>

                </div>
            </div>
        </div>
    </section>

<?php } ?>


<?php include_once('layout/footer.php'); ?>
<?php if(!$getmodule) { ?>
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
                          //labels: ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "Sepetember", "Oktober", "November", "Desember"],
                          labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
                          datasets: [{
                              label: "Tahun <?php echo $last_year; ?>",
                              data: [
                                <?php
                                    $sql = "SELECT count(*) AS jumlah
                                        FROM reg_periksa
                                        WHERE MONTH(tgl_registrasi)
                                        AND YEAR(STR_TO_DATE(tgl_registrasi,'%Y-%m-%d')) = '$last_year'
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
                              label: "Tahun <?php echo $year; ?>",
                              data: [
                                <?php
                                    $sql = "SELECT count(*) AS jumlah
                                        FROM reg_periksa
                                        WHERE MONTH(tgl_registrasi)
                                        AND YEAR(STR_TO_DATE(tgl_registrasi,'%Y-%m-%d')) = '$year'
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
                                        $get_poli = '"'.$data['nm_poli'].'", ';
                                        echo $get_poli;
                                    }
                            ?>
                          ],
                          datasets: [{
                              label: "Tahun <?php echo $year; ?>",
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
<?php } ?>
