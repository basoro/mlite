<?php
?>
<div class="card">
  <div class="header">
      <h2>Diet Pasien</h2>
  </div>
  <div class="body">

                                <table id="datatable_ralan" class="table table-bordered table-striped table-hover display nowrap">
                                    <thead>
                                        <tr>
                                          <th class="text-center">No. Rawat</th>
                           						     <th class="text-center">No. RM</th>
                           						     <th class="text-center">Nama Pasien</th>
                           						     <th class="text-center">Kamar</th>
                             							 <th class="text-center">Jenis Bayar</th>
                           						     <th class="text-center">Tanggal</th>
                           						     <th class="text-center">Waktu Diet</th>
                           						     <th class="text-center">Jenis Diet</th>
                           						     <th class="text-center">Diagnosa</th>
                                        </tr>
                                    </thead>
    	    						<tbody>
    	    						<?php

                      $_sql = "SELECT
                        detail_beri_diet.no_rawat,
                        detail_beri_diet.tanggal,
                        detail_beri_diet.waktu,
                        diet.nama_diet,
                        reg_periksa.no_rkm_medis,
                        pasien.nm_pasien,
                        kamar_inap.kd_kamar,
                        kamar.kd_bangsal,
                        bangsal.nm_bangsal,
                        kamar_inap.diagnosa_awal,
                        kamar_inap.no_rawat,
                        penjab.png_jawab
                        FROM
                        detail_beri_diet
                        INNER JOIN diet ON detail_beri_diet.kd_diet = diet.kd_diet
                        INNER JOIN reg_periksa ON detail_beri_diet.no_rawat = reg_periksa.no_rawat
                        INNER JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
                        INNER JOIN kamar_inap ON kamar_inap.no_rawat = reg_periksa.no_rawat
                        INNER JOIN kamar ON detail_beri_diet.kd_kamar = kamar.kd_kamar AND kamar_inap.kd_kamar = kamar.kd_kamar
                        INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
                        INNER JOIN penjab ON reg_periksa.kd_pj = penjab.kd_pj AND pasien.kd_pj = penjab.kd_pj
                        WHERE kamar.kd_bangsal = '{$_SESSION['jenis_poli']}'";
                        if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) {
                            $sql .= " AND detail_beri_diet.tanggal BETWEEN '$_POST[tgl_awal]' AND '$_POST[tgl_akhir]'";
            						} else {
                						$_sql .= " AND detail_beri_diet.tanggal = '$date'";
            						}
            						$_sql .= "  ORDER BY detail_beri_diet.waktu ASC";
            						$sql = query($_sql);
            						$no = 1;
    								while($row = fetch_array($sql)){
    		    						echo "<tr>";
                        echo "<td>".$row['no_rawat']."</td>";
     										echo "<td>".$row['no_rkm_medis']."</td>";
     										echo "<td>".$row['nm_pasien']."</td>";
     										echo "<td>".$row['nm_bangsal']."</td>";
     										echo "<td>".$row['png_jawab']."</td>";
     										echo "<td>".$row['tanggal']."</td>";
     										echo "<td>".$row['waktu']."</td>";
     										echo "<td>".$row['nama_diet']."</td>";
     										echo "<td>".$row['diagnosa_awal']."</td>";
    		    						echo "</tr>";
            							$no++;
    	    						}
    	    						?>
    	    						</tbody>
                                </table>

                                <div class="row clearfix">
                                    <form method="post" action="">
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <div class="form-line">
                                                <input type="text" name="tgl_awal" class="datepicker form-control" placeholder="Pilih tanggal awal...">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <div class="form-line">
                                                <input type="text" name="tgl_akhir" class="datepicker form-control" placeholder="Pilih tanggal akhir...">
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

  </div>
</div>
