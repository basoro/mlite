<?php
?>
            <div class="row clearfix">
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-pink hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">enhanced_encryption</i>
                        </div>
                        <div class="content">
                            <div class="text">OBAT & BHP</div>
                            <div class="number count-to" data-from="0" data-to="<?php echo num_rows(query("SELECT kode_brng FROM databarang"));?>" data-speed="1000" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-cyan hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">group_add</i>
                        </div>
                        <div class="content">
                            <div class="text">OBAT EXPIRED</div>
                            <div class="number count-to" data-from="0" data-to="<?php echo num_rows(query("SELECT kode_brng FROM databarang WHERE expire < CURRENT_DATE()"));?>" data-speed="1000" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-light-green hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">people</i>
                        </div>
                        <div class="content">
                            <div class="text">RESEP RALAN</div>
                            <div class="number count-to" data-from="0" data-to="<?php echo num_rows(query("SELECT no_rawat FROM resep_obat WHERE tgl_peresepan = CURRENT_DATE() AND status = 'ralan'"));?>" data-speed="1000" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-orange hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">person</i>
                        </div>
                        <div class="content">
                            <div class="text">RESEP RANAP</div>
                            <div class="number count-to" data-from="0" data-to="<?php echo num_rows(query("SELECT no_rawat FROM resep_obat WHERE tgl_peresepan = CURRENT_DATE() AND status = 'ranap'"));?>" data-speed="1000" data-fresh-interval="20"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row clearfix">
              <div class="col-lg-12">
                <div class="card">
                    <div class="header">
                        <h2>RESEP POLIKLINIK HARI INI</h2>
                    </div>
                    <div class="body">
                        <canvas id="line_chart" height="250"></canvas>
                    </div>
                </div>
              </div>
            </div>
            <div class="card">
                <div class="header">
                  <h2>Data Obat / BHP</h2>
                </div>
                <div class="body">
                <table id="allobat" class="table table-bordered table-striped table-hover display nowrap" width="100%">
                  <thead>
                    <tr>
                      <th>Kode Barang</th>
                      <th>Nama Barang</th>
                      <th>Kode Satuan</th>
                      <th>Letak Barang</th>
                      <th>Harga Beli</th>
                      <th>Rawat Jalan</th>
                      <th>Kelas 1</th>
                      <th>Kelas 2</th>
                      <th>Kelas 3</th>
                      <th>Utama</th>
                      <th>VIP</th>
                      <th>VVIP</th>
                      <th>Beli Luar</th>
                      <th>Jual Bebas</th>
                      <th>Karyawan</th>
                      <th>Stok Minimal</th>
                      <th>Kode Jenis</th>
                      <th>Kapasitas</th>
                      <th>Expire</th>
                      <th>Status</th>
                      <th>Kode Industri</th>
                      <th>Kategori</th>
                      <th>Golongan</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
