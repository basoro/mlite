                                <form action="" method="post" class="data">
                                    <h5 class="card-inside-title">1. Intra Vena Kateter</h5>
                                    <div class="demo-checkbox">
                                        <input type="checkbox" value="Vena Sentral" name="jenis_tdk" id="basic_checkbox_1">
                                        <label for="basic_checkbox_1">Vena Sentral</label>
                                        <input type="checkbox" value="Vena Perifer" name="jenis_tdk" id="basic_checkbox_2">
                                        <label for="basic_checkbox_2">Vena Perifer</label>
                                        <input type="checkbox" value="Arteri" name="jenis_tdk" id="basic_checkbox_3">
                                        <label for="basic_checkbox_3">Arteri</label>
                                        <input type="checkbox" value="Umbilikal" name="jenis_tdk" id="basic_checkbox_4">
                                        <label for="basic_checkbox_4">Umbilikal</label>
                                    </div>
                                    <h5 class="card-inside-title">2. Vena Kateter</h5>
                                    <div class="demo-checkbox">
                                        <input type="checkbox" value="Urine Kateter" name="jenis_tdk" id="basic_checkbox_5">
                                        <label for="basic_checkbox_5">Urine Kateter</label>
                                        <input type="checkbox" value="Suprapubik Kateter" name="jenis_tdk" id="basic_checkbox_6">
                                        <label for="basic_checkbox_6">Suprapubik Kateter</label>
                                    </div>
                                    <h5 class="card-inside-title">3. Ventilasi Mekanik</h5>
                                    <div class="demo-checkbox">
                                        <input type="checkbox" value="Tuba Endotrakeal" name="jenis_tdk" id="basic_checkbox_7">
                                        <label for="basic_checkbox_7">Tuba Endotrakeal</label>
                                        <input type="checkbox" value="Trakeostomi" name="jenis_tdk" id="basic_checkbox_8">
                                        <label for="basic_checkbox_8">Trakeostomi</label>
                                    </div>
                                    <div class="row clearfix">
                                      <div class="col-sm-2">
                                        <h5 class="card-inside-title">4. Lain - lain </h5>
                                      </div>
                                      <div class="col-sm-3">
                                        <div class="form-group">
                                          <div class="form-line">
                                            <input type="text" placeholder="............................." name="lain_lain" class="form-control" id="catatan">
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                    <div>
                                        <div class="demo-checkbox">
                                            <input type="checkbox" value="Drain" name="jenis_tdk" id="basic1">
                                            <label for="basic1">Drain</label>
                                            <input type="checkbox" value="IABP" name="jenis_tdk" id="basic2">
                                            <label for="basic2">IABP</label>
                                            <input type="checkbox" value="CVVH" name="jenis_tdk" id="basic3">
                                            <label for="basic3">CVVH</label>
                                        </div>
                                    </div>
                                    <div class="row clearfix">
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <div class="form-line">
                                                    <input type="hidden" value="<?php echo $no_rawat;?>" name="no_rawat">
                                                    <input type="text" placeholder="Lokasi" name="lokasi" class="form-control" id="lokasi">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <div class="form-line">
                                                    <input type="text" placeholder="Tanggal Awal" name="mulai" class="form-control" id="mulai">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <div class="form-line">
                                                    <input type="text" placeholder="Tanggal Akhir" name="akhir" class="form-control" id="akhir">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <div class="form-line">
                                                    <input type="text" placeholder="Total Hari" name="total" value="" class="form-control" id="total" disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row clearfix">
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <div class="form-line">
                                                    <input type="text" placeholder="Tanggal Infeksi" name="tglin" class="form-control datepicker" id="tglin">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <div class="form-line">
                                                    <input type="text" placeholder="Catatan" name="catatan" class="form-control" id="catatan">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <h5 class="card-inside-title">Faktor Penyakit</h5>
                                    <ul>
                                        <li>
                                        HBS Ag :
                                            <div class="demo-radio-button">
                                            <input name="group1" value="Positif" type="radio" class="with-gap" id="radio_3" />
                                            <label for="radio_3">Positif</label>
                                            <input name="group1" value="Negatif" type="radio" id="radio_4" class="with-gap" />
                                            <label for="radio_4">Negatif</label>
                                            <input name="group1" value="Tidak Diperiksa" type="radio" id="radio_1" class="with-gap" />
                                            <label for="radio_1">Tidak Diperiksa</label>
                                            </div>
                                        </li>
                                        <li>
                                        Anti HCV :
                                            <div class="demo-radio-button">
                                            <input name="group2" value="Positif" type="radio" class="with-gap" id="radio_2" />
                                            <label for="radio_2">Positif</label>
                                            <input name="group2" value="Negatif" type="radio" id="radio_5" class="with-gap" />
                                            <label for="radio_5">Negatif</label>
                                            <input name="group2" value="Tidak Diperiksa" type="radio" id="radio_6" class="with-gap" />
                                            <label for="radio_6">Tidak Diperiksa</label>
                                            </div>
                                        </li>
                                        <li>
                                        Anti HIV :
                                            <div class="demo-radio-button">
                                            <input name="group3" value="Positif" type="radio" class="with-gap" id="radio_7" />
                                            <label for="radio_7">Positif</label>
                                            <input name="group3" value="Negatif" type="radio" id="radio_8" class="with-gap" />
                                            <label for="radio_8">Negatif</label>
                                            <input name="group3" value="Tidak Diperiksa" type="radio" id="radio_9" class="with-gap" />
                                            <label for="radio_9">Tidak Diperiksa</label>
                                            </div>
                                        </li>
                                    </ul>
                                    <h5 class="card-inside-title">Hasil Laboratorium</h5>
                                    <div class="row clearfix">
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <div class="form-line">
                                                    <label for="lokasi">Leukocyt</label>
                                                    <input type="text" placeholder="" name="leuk" class="form-control" id="lokasi">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <div class="form-line">
                                                    <label for="mulai">LED</label>
                                                    <input type="text" placeholder="" name="led" class="form-control" id="mulai">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <div class="form-line">
                                                    <label for="akhir">GDS</label>
                                                    <input type="text" placeholder="" name="gds" class="form-control" id="akhir">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <h5 class="card-inside-title">Hasil Radiologi</h5>
                                    <div class="row clearfix">
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <div class="form-line">
                                                    <input type="text" placeholder="" name="rad" class="form-control" id="lokasi">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                      <div class="form-group col col">
                                        <button type="submit" name="ok_sis" value="ok_sis" class="btn simpan bg-indigo waves-effect">SIMPAN</button>
                                      </div>
                                    </div>
                                </form>
