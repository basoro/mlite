<?php
$action = isset($_GET['action'])?$_GET['action']:null;
$jenis_poli = isset($_SESSION['jenis_poli'])?$_SESSION['jenis_poli']:null;
$role = isset($_SESSION['role'])?$_SESSION['role']:null;
?>
<div class="card">
  <div class="header">
      <h2>Rujukan Internal</h2>
  </div>
  <div class="body">

  <?php if(!$action){ ?>
      <table id="datatable_ralan" class="table table-bordered table-striped table-hover display nowrap">
          <thead>
              <tr>
                  <th>Nama Pasien</th>
                	<th>No RM</th>
                  <th>Dokter Tujuan</th>
                  <th>No. Antrian</th>
                  <th>Status</th>
              </tr>
          </thead>
					<tbody>
					<?php
					$_sql = "SELECT b.nm_pasien, c.nm_dokter, a.no_reg, a.no_rkm_medis, a.no_rawat, a.stts FROM reg_periksa a, pasien b, dokter c, rujukan_internal_poli d WHERE d.kd_poli = '{$_SESSION['jenis_poli']}' AND a.no_rkm_medis = b.no_rkm_medis AND d.kd_dokter = c.kd_dokter AND a.no_rawat=d.no_rawat";
						if(isset($_POST['tanggal']) && $_POST['tanggal'] !="") {
    						$_sql .= " AND a.tgl_registrasi = '{$_POST['tanggal']}'";
						} else {
    						$_sql .= " AND a.tgl_registrasi = '$date'";
						}
						$_sql .= "  ORDER BY a.no_reg ASC";

						$sql = query($_sql);
						$no = 1;
				while($row = fetch_array($sql)){
						echo '<tr>';
						echo '<td>';
						echo '<a href="./index.php?module=RawatJalan&page=rujuk_internal&action=jawaban&no_rawat='.$row['4'].'" class="title">'.ucwords(strtolower(SUBSTR($row['0'], 0, 20))).' ...</a>';
						echo '</td>';
                          	echo '<td>'.$row['3'].'</td>';
						echo '<td>'.$row['1'].'</td>';
						echo '<td>'.$row['2'].'</td>';
						echo '<td>'.$row['5'].'</td>';
						echo '</tr>';
							$no++;
					}
					?>
					</tbody>
        </table>

        <div class="row clearfix">
            <form method="post" action="">
            <div class="col-sm-10">
                <div class="form-group">
                    <div class="form-line">
                        <input type="text" name="tanggal" class="datepicker form-control" placeholder="Pilih tanggal...">
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
<?php } ?>
<?php if($action == 'jawaban'){ ?>
<?php } ?>

  </div>
</div>
