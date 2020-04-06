<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Laporan Keluhan Penyakit';
include_once('../../config.php');
?>
<div class="card">
  <div class="header">
    <h2>
      <?php echo $title;?>
      <small><?php $date = date('Y-m-d');?></small>
    </h2>
  </div>
<div class="body">

    <canvas id="line_chart" height="250"></canvas>
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

<?php
include_once('layout/footer.php');
?>
<script>

      $(function () {
          new Chart(document.getElementById("line_chart").getContext("2d"), getChartJs('line'));
          initSparkline();
      });

      function getChartJs(type) {
          var config = null;
          if (type === 'line') {
              config = {
                  type: 'bar',
                  data: {
                      labels: ["Panas","Batuk","Pilek","Sesak Nafas"],
                      datasets: [{
                          label: "Jumlah",
                          data: [
                            <?php
                                $sql = "SELECT count(no_rawat) AS jumlah
                                    FROM pemeriksaan_ralan
                                    WHERE keluhan LIKE '%panas%' AND tgl_perawatan BETWEEN '{$_POST['tahun']}-{$_POST['bulan']}-01' AND '{$_POST['tahun']}-{$_POST['bulan']}-31'
                                    ";
                                $hasil = query($sql);
                                while ($data = fetch_array($hasil)) {
                                    echo $data['0'];
                                }
                                echo ",";
                                $sql = "SELECT count(no_rawat) AS jumlah
                                    FROM pemeriksaan_ralan
                                    WHERE keluhan LIKE '%batuk%' AND tgl_perawatan BETWEEN '{$_POST['tahun']}-{$_POST['bulan']}-01' AND '{$_POST['tahun']}-{$_POST['bulan']}-31'
                                    ";
                                $hasil = query($sql);
                                while ($data = fetch_array($hasil)) {
                                    echo $data['0'];
                                }
                                echo ",";
                                $sql = "SELECT count(no_rawat) AS jumlah
                                    FROM pemeriksaan_ralan
                                    WHERE keluhan LIKE '%pilek%' AND tgl_perawatan BETWEEN '{$_POST['tahun']}-{$_POST['bulan']}-01' AND '{$_POST['tahun']}-{$_POST['bulan']}-31'
                                    ";
                                $hasil = query($sql);
                                while ($data = fetch_array($hasil)) {
                                    echo $data['0'];
                                }
                                echo ",";
                                $sql = "SELECT count(no_rawat) AS jumlah
                                    FROM pemeriksaan_ralan
                                    WHERE keluhan LIKE '%sesak nafas%' OR keluhan LIKE '%sesak napas%' AND tgl_perawatan BETWEEN '{$_POST['tahun']}-{$_POST['bulan']}-01' AND '{$_POST['tahun']}-{$_POST['bulan']}-31'
                                    ";
                                $hasil = query($sql);
                                while ($data = fetch_array($hasil)) {
                                    echo $data['0'];
                                }
                                echo ",";
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
