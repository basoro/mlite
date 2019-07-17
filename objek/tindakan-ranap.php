							<div class="tab-content m-t-20">
                               <div role="tabpanel" class="tab-pane fade in" id="data">
                                 <div class="body">
                                 <form method="POST">
                                   <label for="email_address">Nama Tindakan</label>
                                   <div class="form-group">
                                      <select name="kd_tdk" class="form-control kd_tdk" id="kd_tdk" style="width:100%"></select>
                                      <br/>
                                      <input type="hidden" class="form-control" id="kdtdk" name="kdtdk"/>
                                   </div>
                                   <button type="submit" name="ok_tdk" value="ok_tdk" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_tdk\'">SIMPAN</button>
                                 </form>
                                 </div>
                                 <div class="body">
                                 <table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                                     <thead>
                                         <tr>
                                             <th>Nama Tindakan</th>
                                             <th>Tanggal Tindakan</th>
                                             <th>Biaya</th>
                                             <th>Tools</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                                     <?php
                                     $query_tindakan = query("SELECT rawat_inap_pr.kd_jenis_prw, rawat_inap_pr.tgl_perawatan, rawat_inap_pr.biaya_rawat, jns_perawatan_inap.nm_perawatan  FROM rawat_inap_pr, jns_perawatan_inap WHERE rawat_inap_pr.kd_jenis_prw = jns_perawatan_inap.kd_jenis_prw AND rawat_inap_pr.no_rawat = '{$no_rawat}'");
                                     while ($data_tindakan = fetch_array($query_tindakan)) {
                                     ?>
                                         <tr>
                                             <td><?php echo SUBSTR($data_tindakan['3'], 0, 20).' ...'; ?></td>
                                             <td><?php echo $data_tindakan['1']; ?></td>
                                             <td><?php echo $data_tindakan['2']; ?></td>
                                             <td><a href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_tindakan&kd_jenis_prw=<?php echo $data_tindakan['0']; ?>&no_rawat=<?php echo $no_rawat; ?>">Hapus</a></td>
                                         </tr>
                                     <?php
                                     }
                                     ?>
                                     </tbody>
                                 </table>
                                 </div>
                               </div>

                               <div role="tabpanel" class="tab-pane fade in active" id="datapem">
                                 <div class="body">
                                 <form method="POST">
                                   <div class="row clearfix">
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Keluhan</dt>
                                          <dd><textarea rows="4" name="keluhan" class="form-control"></textarea></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Pemeriksaan</dt>
                                          <dd><textarea rows="4" name="pemeriksaan" class="form-control"></textarea></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Alergi</dt>
                                          <dd><input type="text" class="form-control" name="alergi"></dd>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="row clearfix">
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Suhu Badan (C)</dt>
                                          <dd><input type="text" class="form-control" name="suhu"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Tinggi Badan (Cm)</dt>
                                          <dd><input type="text" class="form-control" name="tinggi"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Tensi</dt>
                                          <dd><input type="text" class="form-control" name="tensi"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Respirasi (per Menit)</dt>
                                          <dd><input type="text" class="form-control" name="respirasi"></dd>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="row clearfix">
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Berat (Kg)</dt>
                                          <dd><input type="text" class="form-control" name="berat"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Nadi (per Menit)</dt>
                                          <dd><input type="text" class="form-control" name="nadi"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Imun Ke</dt>
                                          <dd><input type="text" class="form-control" name="imun"></dd>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>GCS(E , V , M)</dt>
                                          <dd><input type="text" class="form-control" name="gcs"></dd>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                   <button type="submit" name="ok_per" value="ok_per" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_per\'">SIMPAN</button>
                                 </form>
                                 </div>
                                 <div class="body">
                                 <table id="datatab" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                                     <thead>
                                         <tr>
                                             <th>Keluhan</th>
                                             <th>Pemeriksaan</th>
                                             <th>Tools</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                                     <?php
                                     $query_tindakan = query("SELECT * FROM pemeriksaan_ranap WHERE no_rawat = '{$no_rawat}'");
                                     while ($data_tindakan = fetch_array($query_tindakan)) {
                                     ?>
                                         <tr>
                                             <td><?php echo $data_tindakan['keluhan']; ?></td>
                                             <td><?php echo $data_tindakan['pemeriksaan']; ?></td>
                                             <td><a href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_pemeriksaan&keluhan=<?php echo $data_tindakan['keluhan']; ?>&no_rawat=<?php echo $no_rawat; ?>">Hapus</a></td>
                                         </tr>
                                     <?php
                                     }
                                     ?>
                                     </tbody>
                                 </table>
                                 </div>
                               </div>
                             </div>