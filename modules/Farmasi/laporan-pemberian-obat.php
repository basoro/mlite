<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="header">
                <h2>
                    LAPORAN PEMBERIAN OBAT <?php if (isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) {
    echo "Periode ".date("d-m-Y", strtotime($_POST['tgl_awal']))." s/d ".date("d-m-Y", strtotime($_POST['tgl_akhir']));
} ?>
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
                        if ($_POST['status'] == 'B0001') {
                            $status = "Ranap";
                        } else {
                            $status = "Ralan";
                        };
                        $penjab = $_POST['penjab'];
                        if ($tgl_awal && $tgl_akhir) {
                            $sql = query("SELECT SUM(detail_pemberian_obat.jml) AS jml , kategori_barang.nama , bangsal.nm_bangsal FROM detail_pemberian_obat JOIN databarang JOIN kategori_barang JOIN reg_periksa JOIN bangsal ON detail_pemberian_obat.kode_brng = databarang.kode_brng AND databarang.kode_kategori = kategori_barang.kode AND detail_pemberian_obat.no_rawat = reg_periksa.no_rawat AND detail_pemberian_obat.kd_bangsal = bangsal.kd_bangsal WHERE detail_pemberian_obat.tgl_perawatan BETWEEN '$tgl_awal' AND '$tgl_akhir' AND detail_pemberian_obat.status = '$status' AND detail_pemberian_obat.kd_bangsal = '{$_POST['status']}' AND reg_periksa.kd_pj IN ($penjab) GROUP BY kategori_barang.kode");
                            while ($row = fetch_array($sql)) {
                                ?>
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
                    <div class="col-sm-2">
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
                    <div class="col-sm-2">
                        <div class="form-group">
                            <div class="form-line">
                            <select name="penjab" style="width:100%" class="form-control">
                              <option value="'A01'">UMUM</option>
                              <option value="'A02','BPJ'">BPJS</option>
								            </select>
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
    </div>
</div>
