<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Kasir Ranap';
include_once('config.php');
include_once('layout/header.php');
include_once('layout/sidebar.php');
?>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                PEMBAYARAN PASIEN RANAP
                                <small><?php if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) { echo "Periode ".date("d-m-Y",strtotime($_POST['tgl_awal']))." s/d ".date("d-m-Y",strtotime($_POST['tgl_akhir'])); } ?></small>
                            </h2>
                        </div>
                        <div class="body">
                            <div id="buttons" class="align-center m-l-10 m-b-15 export-hidden"></div>
                            <table id="datatable" class="table table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Nama Pasien</th>
                                        <th>No. RM</th>
                                        <th>Alamat</th>
                                        <th>Jenis Bayar</th>
                                        <th>Status Bayar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $sql = "SELECT a.nm_pasien, b.no_rkm_medis, a.alamat, c.png_jawab, b.status_bayar FROM pasien a, reg_periksa b, penjab c WHERE a.no_rkm_medis = b.no_rkm_medis AND b.kd_pj = c.kd_pj AND b.kd_poli != 'U0027' AND b.status_lanjut = 'Ranap'";
                                if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) {
                                	$sql .= " AND b.tgl_registrasi BETWEEN '$_POST[tgl_awal]' AND '$_POST[tgl_akhir]'";
                                } else {
                                  $sql .= " AND b.status_bayar = 'Belum Bayar'";
                                }
                                $query = query($sql);
                                $no = 1;
                                while($row = fetch_array($query)) {
                                ?>
                                    <tr>
                                        <td><?php echo SUBSTR($row['0'],0,15); ?></td>
                                        <td><?php echo $row['1']; ?></td>
                                        <td><?php echo $row['2']; ?></td>
                                        <td><?php echo $row['3']; ?></td>
                                        <td><?php echo $row['4']; ?></td>
                                    </tr>
                                <?php
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
                </div>
            </div>
        </div>
    </section>

<?php
include_once('layout/footer.php');
?>
