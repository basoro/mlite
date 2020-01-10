<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="header">
                <h2>
                    LAPORAN PEMBERIAN OBAT <?php if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) { echo "Periode ".date("d-m-Y",strtotime($_POST['tgl_awal']))." s/d ".date("d-m-Y",strtotime($_POST['tgl_akhir'])); } ?>
                </h2>
            </div>
            <div class="body">
                <table id="datatable" class="table table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                    <thead>
                        <tr>
                            <th>Depo</th>
                            <th>Tipe Obat</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody> 
                    <?php
                        $tgl_awal = isset($_POST['tgl_awal'])?$_POST['tgl_awal']:null;
                        $tgl_akhir = isset($_POST['tgl_akhir'])?$_POST['tgl_akhir']:null;
                        if($tgl_awal && $tgl_akhir) {
                            $sql = query("SELECT SUM(riwayat_barang_medis.keluar) as stok , kategori_barang.nama , bangsal.nm_bangsal 
                                        FROM riwayat_barang_medis JOIN kategori_barang JOIN bangsal JOIN databarang 
                                        ON riwayat_barang_medis.kode_brng = databarang.kode_brng AND databarang.kode_kategori = kategori_barang.kode AND riwayat_barang_medis.kd_bangsal = bangsal.kd_bangsal 
                                        WHERE riwayat_barang_medis.posisi = 'Pemberian Obat' AND riwayat_barang_medis.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' AND kategori_barang.kode = 'K01' AND bangsal.kd_bangsal = '{$_POST['status']}'");
                            while($row = fetch_array($sql)) {
                    ?><!--GENERIK AP RANAP-->
                        <tr>
                            <td><?php echo $row['2']; ?></td>
                            <td><?php echo $row['1']; ?></td>
                            <td><?php echo $row['0']; ?></td>
                        </tr>
                    <?php
                            }
                            $sql = query("SELECT SUM(riwayat_barang_medis.keluar) as stok , kategori_barang.nama , bangsal.nm_bangsal 
                                        FROM riwayat_barang_medis JOIN kategori_barang JOIN bangsal JOIN databarang 
                                        ON riwayat_barang_medis.kode_brng = databarang.kode_brng AND databarang.kode_kategori = kategori_barang.kode AND riwayat_barang_medis.kd_bangsal = bangsal.kd_bangsal 
                                        WHERE riwayat_barang_medis.posisi = 'Pemberian Obat' AND riwayat_barang_medis.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' AND kategori_barang.kode = 'K03' AND bangsal.kd_bangsal = '{$_POST['status']}'");
                            while($row = fetch_array($sql)) {
                    ?><!--NON GENERIK FORMULA-->
                        <tr>
                            <td><?php echo $row['2']; ?></td>
                            <td><?php echo $row['1']; ?></td>
                            <td><?php echo $row['0']; ?></td>
                        </tr>
                    <?php
                            }
                            $sql = query("SELECT SUM(riwayat_barang_medis.keluar) as stok , kategori_barang.nama , bangsal.nm_bangsal 
                                        FROM riwayat_barang_medis JOIN kategori_barang JOIN bangsal JOIN databarang 
                                        ON riwayat_barang_medis.kode_brng = databarang.kode_brng AND databarang.kode_kategori = kategori_barang.kode AND riwayat_barang_medis.kd_bangsal = bangsal.kd_bangsal 
                                        WHERE riwayat_barang_medis.posisi = 'Pemberian Obat' AND riwayat_barang_medis.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' AND kategori_barang.kode = 'K04' AND bangsal.kd_bangsal = '{$_POST['status']}'");
                            while($row = fetch_array($sql)) {
                    ?><!--NON GENERIK NON FORMULA-->
                        <tr>
                            <td><?php echo $row['2']; ?></td>
                            <td><?php echo $row['1']; ?></td>
                            <td><?php echo $row['0']; ?></td>
                        </tr>
                    <?php
                            }
                            $sql = query("SELECT SUM(riwayat_barang_medis.keluar) as stok , kategori_barang.nama , bangsal.nm_bangsal 
                                        FROM riwayat_barang_medis JOIN kategori_barang JOIN bangsal JOIN databarang 
                                        ON riwayat_barang_medis.kode_brng = databarang.kode_brng AND databarang.kode_kategori = kategori_barang.kode AND riwayat_barang_medis.kd_bangsal = bangsal.kd_bangsal 
                                        WHERE riwayat_barang_medis.posisi = 'Pemberian Obat' AND riwayat_barang_medis.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' AND kategori_barang.kode = 'K05' AND bangsal.kd_bangsal = '{$_POST['status']}'");
                            while($row = fetch_array($sql)) {
                    ?><!--BMHP-->
                        <tr>
                            <td><?php echo $row['2']; ?></td>
                            <td><?php echo $row['1']; ?></td>
                            <td><?php echo $row['0']; ?></td>
                        </tr>
                    <?php
                            }
                        }
                    ?>
                    </tbody>
                </table>
                <div class="row clearfix">
                    <form method="post" action="">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" name="tgl_awal" class="datepicker form-control" placeholder="Pilih tanggal awal...">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" name="tgl_akhir" class="datepicker form-control" placeholder="Pilih tanggal akhir...">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <div class="form-line">
                            <select name="status" style="width:100%" class="form-control">
									<option value="B0014" selected="selected">Rawat Jalan</option>
									<option value="B0001">Rawat Inap</option>
									<option value="B0018">IGD</option>
								</select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
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
    </div>
</div>
