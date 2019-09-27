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

          $('#allobat').dataTable( {
                "bInfo" : true,
              	"scrollX": true,
                "processing": true,
                "serverSide": true,
                "responsive": false,
                "oLanguage": {
                    "sProcessing":   "Sedang memproses...",
                    "sLengthMenu":   "Tampilkan _MENU_ entri",
                    "sZeroRecords":  "Tidak ditemukan data yang sesuai",
                    "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
                    "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                    "sInfoPostFix":  "",
                    "sSearch":       "Cari:",
                    "sUrl":          "",
                    "oPaginate": {
                        "sFirst":    "«",
                        "sPrevious": "‹",
                        "sNext":     "›",
                        "sLast":     "»"
                    }
                },
                "order": [[ 0, "asc" ]],
                "ajax": "<?php echo URL; ?>/includes/obat.php"
          } );
          
    </script>
