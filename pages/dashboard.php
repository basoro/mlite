<?php
if(!defined("INDEX")) header('location: index.php');
?>
<div class="block-header">
    <p class="col-orange font-24 font-uppercase">DASHBOARD</p>
</div>
<div class="row clearfix">
<?php
	//Memanggil fungsi buat_tombol untuk membuat 4 tombol
	hitung_data_dashboard("PASIEN", "pasien", "enhanced_encryption", "?content=artikel", "pink");
	hitung_data_dashboard("DOKTER", "dokter", "people", "?content=menu", "cyan");
	hitung_data_dashboard("KUNJUNGAN", "reg_periksa", "group_add", "?content=halaman", "green");
	hitung_data_dashboard("PEGAWAI", "pegawai", "person", "?content=modul", "orange");
?>
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
          <div class="setting-con" style="padding:0; height:255px; margin-right: 0px; overflow: hidden;overflow-y:scroll">
              <div class="container-fluid module">
                  <div class="row">

                    <?php
										$query = $mysqli->query("SELECT * FROM lite_modul WHERE menu='Y' AND aktif='Y'");
										while($data = $query->fetch_array()){
											if(file_exists("modules/$data[folder]/index.php")){
												include "modules/$data[folder]/index.php";
											}
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
           <h2>KUNJUNGAN DALAM TAHUN</h2>
        </div>
        <div class="body">
            <canvas id="bar_chart" height="215"></canvas>
        </div>
     </div>
  </div>
</div>
<?php
function addCSS() {
?>
<?php
}
function addJS() {
  global $mysqli, $date, $thn_sekarang, $last_year, $year;
?>
  <script src="assets/plugins/chartjs/Chart.bundle.js"></script>
  <script src="assets/plugins/jquery-countto/jquery.countTo.js"></script>
  <script>
  $('.count-to').countTo();
	$(function () {
			new Chart(document.getElementById("line_chart").getContext("2d"), getChartJs('line'));
      new Chart(document.getElementById("bar_chart").getContext("2d"), getChartJs('bar'));
	});

	function getChartJs(type) {
			var config = null;

			if (type === 'line') {
					config = {
							type: 'bar',
							data: {
									labels: [
										<?php
											$query = $mysqli->query("SELECT a.nm_poli AS nm_poli FROM poliklinik a, reg_periksa b WHERE a.kd_poli = b.kd_poli AND b.tgl_registrasi = '$date' GROUP BY b.kd_poli");
											while($data = $query->fetch_array()){
													$get_poli = '"'.$data['nm_poli'].'", ';
													echo $get_poli;
											}
										?>
									],
									datasets: [{
											label: "Tahun <?php echo $thn_sekarang; ?>",
											data: [
												<?php
												$query = $mysqli->query("SELECT count(*) AS jumlah FROM reg_periksa WHERE tgl_registrasi = '$date' GROUP BY kd_poli");
												while ($data = $query->fetch_array()){
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
      if (type === 'bar') {
          config = {
              type: 'bar',
              data: {
                  labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
                  datasets: [{
                      label: "Tahun <?php echo $last_year; ?>",
                      data: [
                        <?php
                            $query = $mysqli->query("SELECT count(*) AS jumlah FROM reg_periksa WHERE MONTH(tgl_registrasi) AND YEAR(STR_TO_DATE(tgl_registrasi,'%Y-%m-%d')) = '$last_year' GROUP BY EXTRACT(MONTH FROM tgl_registrasi)");
                            while ($data = $query->fetch_array()){
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
                            $query = $mysqli->query("SELECT count(*) AS jumlah FROM reg_periksa WHERE MONTH(tgl_registrasi) AND YEAR(STR_TO_DATE(tgl_registrasi,'%Y-%m-%d')) = '$year' GROUP BY EXTRACT(MONTH FROM tgl_registrasi)");
                            while ($data = $query->fetch_array()){
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
			return config;
	}

</script>
<?php
}
?>
