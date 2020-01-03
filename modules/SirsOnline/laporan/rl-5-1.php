<div class="card">
    <div class="header">
        <h2>
          LAPORAN RL 5.1 (Kunjungan Rawat Inap)
            <small><?php $date = date('Y-m-d'); if(isset($_POST['tahun'])) { $tahun = $_POST['tahun']; } else { $tahun = date("Y",strtotime($date)); };
              			 if(isset($_POST['bulan'])) { $bulan = $_POST['bulan']; } else { $bulan = date("M",strtotime($date)); };echo "Periode ".$tahun; ?></small>
        </h2>
    </div>
    <div class="body">
        <div id="buttons" class="align-center m-l-10 m-b-15 export-hidden"></div>
        <table id="datatable" class="table table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
            <thead>
                <tr>
                  <td>Bulan</td>
                  <td>No</td>
                  <td>Jenis Kegiatan</td>
                  <td>Jumlah</td>
                </tr>
            </thead>
            <tbody>
            	<tr>
                    <td><?php echo $bulan;?></td>
                  	<td>1</td>
                  	<td>Pengunjung Baru</td>
                  	<td><?php $sql = fetch_array(query("SELECT COUNT(no_rawat) FROM reg_periksa WHERE status_poli = 'Baru' AND status_lanjut = 'Ralan' AND tgl_registrasi BETWEEN '{$tahun}-{$bulan}-01' AND '{$tahun}-{$bulan}-31'"));echo $sql['0'];?></td>
                </tr>
              	<tr>
                    <td><?php echo $bulan;?></td>
                  	<td>2</td>
                  	<td>Pengunjung Lama</td>
                  	<td><?php $sql = fetch_array(query("SELECT COUNT(no_rawat) FROM reg_periksa WHERE status_poli = 'Lama' AND status_lanjut = 'Ralan' AND tgl_registrasi BETWEEN '{$tahun}-{$bulan}-01' AND '{$tahun}-{$bulan}-31'"));echo $sql['0'];?></td>
                </tr>
        	</tbody>
        </table>
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
