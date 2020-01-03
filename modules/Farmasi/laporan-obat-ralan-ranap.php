<?php
?>

            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                LAPORAN OBAT RALAN - RANAP <?php if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) { echo "Periode ".date("d-m-Y",strtotime($_POST['tgl_awal']))." s/d ".date("d-m-Y",strtotime($_POST['tgl_akhir'])); } ?>
                            </h2>
                        </div>
                        <div class="body">
                            <table id="datatable" class="table table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Obat</th>
                                        <th>Ralan</th>
                                        <th>Ranap</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $tgl_awal = isset($_POST['tgl_awal'])?$_POST['tgl_awal']:null;
                                $tgl_akhir = isset($_POST['tgl_akhir'])?$_POST['tgl_akhir']:null;
                                if($tgl_awal && $tgl_akhir) {
                                  $sql = query("SELECT nama.nama_brng, nama.kode_brng, (SELECT COUNT(jml) FROM detail_pemberian_obat WHERE kode_brng = nama.kode_brng AND status='Ralan' AND tgl_perawatan BETWEEN '$tgl_awal' AND '$tgl_akhir') AS ralan, (SELECT COUNT(jml) FROM detail_pemberian_obat WHERE  kode_brng = nama.kode_brng AND status='Ranap' AND tgl_perawatan BETWEEN '$tgl_awal' AND '$tgl_akhir') AS ranap, (SELECT COUNT(jml) FROM detail_pemberian_obat WHERE kode_brng = nama.kode_brng AND tgl_perawatan BETWEEN '$tgl_awal' AND '$tgl_akhir') AS total FROM (SELECT DISTINCT nama_brng, kode_brng FROM databarang WHERE kode_brng IN(SELECT kode_brng FROM detail_pemberian_obat)) AS nama ORDER BY nama.nama_brng ASC");
                                  $no = 1;
                                  while($row = fetch_array($sql)) {
                                ?>
                                    <tr>
                                        <th scope="row"><?php echo $no; ?></th>
                                        <td><?php echo $row['0']; ?></td>
                                        <td><?php echo $row['2']; ?></td>
                                        <td><?php echo $row['3']; ?></td>
                                        <td><?php echo $row['4']; ?></td>
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
