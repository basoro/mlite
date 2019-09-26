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
                                    $sql = "SELECT count(no_resep) AS jumlah
                                        FROM resep_obat a, reg_periksa b
                                        WHERE b.tgl_registrasi = CURRENT_DATE()
                                        AND a.no_rawat = b.no_rawat
                                        GROUP BY b.kd_poli";
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
