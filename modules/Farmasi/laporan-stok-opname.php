<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="header">
                <h2>
                    LAPORAN STOK OPNAME <?php if (isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) {
    echo "Periode ".date("d-m-Y", strtotime($_POST['tgl_awal']))." s/d ".date("d-m-Y", strtotime($_POST['tgl_akhir']));
} ?>
                </h2>
            </div>
            <div class="body">
                <div id="buttons" class="align-center m-l-10 m-b-15 export-hidden"></div>
                <table id="datatable" class="table table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Satuan</th>
                            <th>Stok Awal</th>
                            <th>Penerimaan</th>
                            <th>Persediaan</th>
                            <th>Pengeluaran</th>
                            <th>Total Stok</th>
                            <th>Harga Satuan</th>
                            <th>Total Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $tgl_awal = isset($_POST['tgl_awal'])?$_POST['tgl_awal']:null;
                        $tgl_akhir = isset($_POST['tgl_akhir'])?$_POST['tgl_akhir']:null;
                        $yesterday = date('Y/m/d', strtotime($tgl_awal. '-1 days'));
                        $two_days_before = date('Y/m/d', strtotime($tgl_awal. '-4 days'));
                        $kode = $_POST['status'];
                        if ($tgl_awal && $tgl_akhir) {
                            $sql = query("SELECT databarang.nama_brng , kodesatuan.satuan , databarang.kode_brng , databarang.ralan
                            FROM databarang JOIN kodesatuan ON databarang.kode_sat = kodesatuan.kode_sat
                            WHERE databarang.kode_kategori IN ($kode) GROUP BY databarang.kode_brng");
                            $no = 1;
                            while ($row = fetch_array($sql)) {
                                ?>
                        <tr>
                            <td><?php echo $no; ?></td>
                            <td><?php echo $row['0']; ?></td>
                            <td><?php echo $row['1']; ?></td>
                            <td><?php
                                $stok = fetch_array(query("SELECT SUM(riwayat_barang_medis.stok_awal) , SUM(riwayat_barang_medis.masuk)
                                FROM riwayat_barang_medis
                                WHERE riwayat_barang_medis.kode_brng = '{$row['2']}' AND riwayat_barang_medis.posisi IN ('Opname') AND riwayat_barang_medis.tanggal BETWEEN '$two_days_before' AND '$yesterday' GROUP BY riwayat_barang_medis.kode_brng"));
                                $stok = $stok['0'] + $stok['1'];
                                echo $stok; ?></td>
                            <td><?php $masuk = fetch_array(query("SELECT SUM(riwayat_barang_medis.masuk)
                            FROM riwayat_barang_medis
                            WHERE riwayat_barang_medis.kode_brng = '{$row['2']}' AND riwayat_barang_medis.posisi IN ('Pengadaan','Penerimaan','Pengambilan Medis') AND riwayat_barang_medis.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' GROUP BY riwayat_barang_medis.kode_brng"));
                                echo $masuk['0']; ?></td>
                            <td><?php $sedia = $stok + $masuk['0'];
                                echo $sedia; ?></td>
                            <td><?php $keluar = fetch_array(query("SELECT SUM(riwayat_barang_medis.keluar)
                            FROM riwayat_barang_medis
                            WHERE riwayat_barang_medis.kode_brng = '{$row['2']}' AND riwayat_barang_medis.posisi IN ('Penjualan','Stok Keluar','Pemberian Obat','Resep Pulang') AND riwayat_barang_medis.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' GROUP BY riwayat_barang_medis.kode_brng"));
                                echo $keluar['0']; ?></td>
                            <td><?php $total = $sedia - $keluar['0'];
                                echo $total; ?></td>
                            <td><?php echo "Rp ".number_format($row['3'], 0, ".", "."); ?></td>
                            <td><?php $harga_total = $total * $row['3'];
                                echo "Rp ".number_format($harga_total, 0, ".", "."); ?></td>
                        </tr>
                    <?php
                            $no++;
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
									<option value="'K01'" selected="selected">Generik</option>
									<option value="'K03','K04'">Reguler</option>
									<option value="'K05'">BMHP</option>
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
