<?php

?>

            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                DATA OBAT EXPIRED <?php if(isset($_POST['tanggal'])) { echo "Tanggal ".$_POST['tanggal']; } ?>
                            </h2>
                        </div>
                        <div class="body">
                            <div id="buttons" class="align-center m-l-10 m-b-15 export-hidden"></div>
                            <table id="datatable" class="table table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Nama Obat</th>
                                        <th>Kode Obat</th>
                                        <th>Stok Akhir</th>
                                        <th>Expired</th>
                                        <th>Update</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $tanggal = isset($_POST['tanggal'])?$_POST['tanggal']:null;
                                $bangsal = isset($_POST['bangsal'])?$_POST['bangsal']:null;
                                $sql = "SELECT a.nama_brng, b.kode_brng, b.stok_akhir, MAX(b.tanggal) AS tgl_update, a.expire FROM databarang a, riwayat_barang_medis b WHERE a.kode_brng = b.kode_brng AND a.status = '1'";
                                if($tanggal) {
									$sql .= " AND b.tanggal = '$tanggal' AND b.jam = (SELECT MAX(jam) FROM riwayat_barang_medis GROUP BY kode_brng LIMIT 1)";
                                }
                                if($bangsal) {
									$sql .= " AND b.kd_bangsal = '$bangsal'";
                                }
								$sql .= " GROUP BY b.kode_brng";
                                $result = query($sql);
                                $no = 1;
                                while($row = fetch_array($result)) {
                                ?>
                                    <tr>
                                        <td><?php echo $row['0']; ?></td>
                                        <td><?php echo $row['1']; ?></td>
                                        <td><?php echo $row['2']; ?></td>
                                        <td><?php echo $row['4']; ?></td>
                                        <td><?php echo $row['3']; ?></td>
                                    </tr>
                                <?php
                                $no++;
                                }
                                ?>
                                </tbody>
                            </table>
                          <div class="row clearfix">
                                <form method="post" action="">
                                  <div class="col-sm-8">
                                      <div class="form-group">
                                          <div class="form-line">
                                              <input type="text" name="tanggal" class="datepicker form-control" placeholder="Pilih tanggal akhir...">
                                          </div>
                                      </div>
                                  </div>
                                  <div class="col-sm-2">
                                      <div class="form-group">
                                          <div class="form-line">
                                            <select name="bangsal" class="form-control show-tick">
                                                <option value="">Semua</option>
                                                <option value="B0018">Apotek IGD</option>
                                                <option value="B0001">Apotek Ranap</option>
                                                <option value="B0014">Apotek Ralan</option>
                                                <option value="B0002">Apotek Gudang</option>
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
