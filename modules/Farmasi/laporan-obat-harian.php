<?php

?>

            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                LAPORAN OBAT HARIAN <?php if(isset($_POST['tgl_perawatan'])) { echo "Tanggal ".$_POST['tgl_perawatan']; } ?>
                            </h2>
                        </div>
                        <div class="body">
                            <div id="buttons" class="align-center m-l-10 m-b-15 export-hidden"></div>
                          	<div class="table-responsive">
                            <table id="datatable" class="table table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Obat</th>
                                        <th>Kode Obat</th>
                                        <th>Nama Pasien</th>
                                        <th>No. RM</th>
                                        <th>Cara Bayar</th>
                                        <th>Dokter</th>
                                        <th>Jumlah</th>
                                        <th>Biaya</th>
                                        <th>Total Biaya</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $tgl_perawatan = isset($_POST['tgl_perawatan'])?$_POST['tgl_perawatan']:null;
                                if ($tgl_perawatan) {
                                  $sql = query("SELECT d.nama_brng, a.kode_brng, c.nm_pasien, b.no_rkm_medis, a.no_rawat, a.jml, e.nm_dokter, a.biaya_obat, f.png_jawab FROM detail_pemberian_obat a, reg_periksa b, pasien c, databarang d, dokter e, penjab f WHERE a.no_rawat = b.no_rawat AND b.no_rkm_medis = c.no_rkm_medis AND a.kode_brng = d.kode_brng AND b.kd_dokter = e.kd_dokter AND b.kd_pj = f.kd_pj AND a.tgl_perawatan = '$tgl_perawatan' ORDER BY a.no_rawat ASC");
                                  $no = 1;
                                  while($row = fetch_array($sql)) {
                                ?>
                                    <tr>
                                        <th scope="row"><?php echo $no; ?></th>
                                        <td><?php echo $row['0']; ?></td>
                                        <td><?php echo $row['1']; ?></td>
                                        <td><?php echo $row['2']; ?></td>
                                        <td><?php echo $row['3']; ?></td>
                                        <td><?php echo $row['8']; ?></td>
                                        <td><?php echo $row['6']; ?></td>
                                        <td><?php echo $row['5']; ?></td>
                                        <td><?php echo $row['7']; ?></td>
                                        <td><?php echo $row['5']*$row['7']; ?></td>
                                    </tr>
                                <?php
                                  $no++;
                                  }
                                }
                                ?>
                                </tbody>
                            </table>
                          	</div>
                          <div id="numbers_numbers"></div>
                            <div class="row clearfix">
                                <form method="post" action="">
                                <div class="col-sm-10">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <input type="text" name="tgl_perawatan" class="datepicker form-control" placeholder="Pilih tanggal....">
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
