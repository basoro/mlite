<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : dentix.id@gmail.com
* Licence under GPL
***/

$title = 'Database Pasien';
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
                                <?php echo $title; ?>
                                <small>Periode <?php echo date("d-m-Y",strtotime($date)); ?></small>
                            </h2>
                        </div>
                        <div class="body">
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active"><a href="#pasienbaru" aria-controls="settings" role="tab" data-toggle="tab">PASIEN BARU</a></li>
                                <li role="presentation"><a href="#datapasien" aria-controls="settings" role="tab" data-toggle="tab">DATA PASIEN</a></li>
                            </ul>
                        </div>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane fade in active" id="pasienbaru">
                              <div class="panel-group" id="accordion">
                                <div class="panel panel-default" style="border: none !important;">
                                    <div class="panel-body">
                                      <form class="form-horizontal">
                                          <div class="row clearfix" style="margin-top:20px;">
                                              <div class="col-lg-6 col-md-6">
                                                 <div class="row clearfix">
                                                    <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                        <label for="email_address_2">No. RM :</label>
                                                    </div>
                                                    <div class="col-lg-8 col-md-8 col-sm-8">
                                                      <div class="input-group input-group-lg">
                                                          <div class="form-line">
                                                              <?php
                                                              $get_rm = fetch_array(query("SELECT no_rkm_medis FROM set_no_rkm_medis"));
                                                              $lastRM = substr($get_rm[0], 0, 6);
                                                              $no_rm_next = sprintf('%06s', ($lastRM + 1));
                                                              ?>
                                                              <input type="text" class="form-control" id="no_rawat"  value="<?php echo $no_rm_next; ?>" placeholder="Nomor Rekam Medik">
                                                          </div>
                                                      </div>
                                                    </div>
                                                 </div>
                                                 <div class="row clearfix">
                                                    <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                        <label for="email_address_2">Nama Pasien :</label>
                                                    </div>
                                                    <div class="col-lg-8 col-md-8 col-sm-8">
                                                      <div class="input-group input-group-lg">
                                                          <div class="form-line">
                                                              <input type="text" class="form-control" id="no_rawat" placeholder="Nama Lengkap Pasien">
                                                          </div>
                                                      </div>
                                                    </div>
                                                 </div>
                                                 <div class="row clearfix">
                                                    <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                        <label for="email_address_2">Nomor KTP :</label>
                                                    </div>
                                                    <div class="col-lg-8 col-md-8 col-sm-8">
                                                      <div class="input-group input-group-lg">
                                                          <div class="form-line">
                                                              <input type="text" class="form-control" id="no_rawat" placeholder="Nomor Induk Kependudukan">
                                                          </div>
                                                      </div>
                                                    </div>
                                                 </div>
                                                 <div class="row clearfix">
                                                    <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                        <label for="email_address_2">JK :</label>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-2">
                                                      <div class="input-group input-group-lg">
                                                          <div class="form">
                                                              <?php echo enumDropdown('pasien', 'jk', '&nbsp;', ''); ?>
                                                          </div>
                                                      </div>
                                                    </div>
                                                    <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                        <label for="email_address_2">Gol. Darah :</label>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-2">
                                                      <div class="input-group input-group-lg">
                                                          <div class="form">
                                                              <?php echo enumDropdown('pasien', 'gol_darah', '&nbsp;', ''); ?>
                                                          </div>
                                                      </div>
                                                    </div>
                                                 </div>
                                                 <div class="row clearfix">
                                                    <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                        <label for="email_address_2">TT Lahir :</label>
                                                    </div>
                                                    <div class="col-lg-5 col-md-5 col-sm-5">
                                                      <div class="input-group input-group-lg">
                                                          <div class="form-line">
                                                              <input type="text" class="form-control" id="no_rawat" placeholder="Tempat Lahir">
                                                          </div>
                                                      </div>
                                                    </div>
                                                    <div class="col-lg-3 col-md-3 col-sm-3">
                                                      <div class="input-group input-group-lg">
                                                          <div class="form-line">
                                                              <input type="text" class="form-control datepicker" id="no_rawat" placeholder="Tgl Lahir">
                                                          </div>
                                                      </div>
                                                    </div>
                                                 </div>
                                                 <div class="row clearfix">
                                                    <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                        <label for="email_address_2">Umur :</label>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-2">
                                                      <div class="input-group input-group-lg">
                                                          <div class="form-line">
                                                              <input type="text" class="form-control" id="no_rawat" placeholder="Umur">
                                                          </div>
                                                      </div>
                                                    </div>
                                                    <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                        <label for="email_address_2">Pendidikan :</label>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-2">
                                                      <div class="input-group input-group-lg">
                                                          <div class="form">
                                                              <?php echo enumDropdown('pasien', 'pnd', '&nbsp;', ''); ?>
                                                          </div>
                                                      </div>
                                                    </div>
                                                 </div>
                                                 <div class="row clearfix">
                                                    <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                        <label for="email_address_2">Agama :</label>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-2">
                                                      <div class="input-group input-group-lg">
                                                          <div class="form">
                                                              <select name="" data-width="100%">
                                                                 <option value="">&nbsp;</option>
                                                                 <option value="Islam">Islam</option>
                                                                 <option value="Kristen">Kristen</option>
                                                                 <option value="Hindu">Hindu</option>
                                                              </select>
                                                          </div>
                                                      </div>
                                                    </div>
                                                    <div class="col-lg-3 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                        <label for="email_address_2">Status :</label>
                                                    </div>
                                                    <div class="col-lg-3 col-md-2 col-sm-2 push-right-20">
                                                      <div class="input-group input-group-lg">
                                                          <div class="form">
                                                              <?php echo enumDropdown('pasien', 'stts_nikah', '&nbsp;', ''); ?>
                                                          </div>
                                                      </div>
                                                    </div>
                                                 </div>
                                                 <div class="row clearfix">
                                                    <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                        <label for="email_address_2">Pekerjaan :</label>
                                                    </div>
                                                    <div class="col-lg-8 col-md-8 col-sm-8">
                                                      <div class="input-group input-group-lg">
                                                          <div class="form-line">
                                                              <input type="text" class="form-control" id="no_rawat" placeholder="Pekerjaan">
                                                          </div>
                                                      </div>
                                                    </div>
                                                 </div>
                                                 <div class="row clearfix">
                                                    <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                        <label for="email_address_2">Nama Ibu :</label>
                                                    </div>
                                                    <div class="col-lg-8 col-md-8 col-sm-8">
                                                      <div class="input-group input-group-lg">
                                                          <div class="form-line">
                                                              <input type="text" class="form-control" id="no_rawat" placeholder="Nama Ibu">
                                                          </div>
                                                      </div>
                                                    </div>
                                                 </div>
                                                 <div class="row clearfix">
                                                    <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                        <label for="email_address_2">Png. Jawab :</label>
                                                    </div>
                                                    <div class="col-lg-8 col-md-8 col-sm-8">
                                                      <div class="input-group input-group-lg">
                                                          <div class="png-jawab-radio-button m-t-10">
                                                            <input name="group5" type="radio" id="radio_30" class="with-gap radio-col-red" />
                                                            <label for="radio_30">Ayah</label>
                                                            <input name="group5" type="radio" id="radio_31" class="with-gap radio-col-pink" />
                                                            <label for="radio_31">Ibu</label>
                                                            <input name="group5" type="radio" id="radio_32" class="with-gap radio-col-purple" />
                                                            <label for="radio_32">Suami</label>
                                                            <input name="group5" type="radio" id="radio_33" class="with-gap radio-col-deep-purple" />
                                                            <label for="radio_33">Istri</label>
                                                            <input name="group5" type="radio" id="radio_34" class="with-gap radio-col-indigo" />
                                                            <label for="radio_34">Saudara</label>
                                                            <input name="group5" type="radio" id="radio_35" class="with-gap radio-col-blue" />
                                                            <label for="radio_35">Anak</label>
                                                          </div>
                                                      </div>
                                                    </div>
                                                 </div>
                                              </div>
                                              <div class="col-lg-6 col-md-6">
                                                <div class="row clearfix">
                                                   <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                       <label for="email_address_2">Asuransi :</label>
                                                   </div>
                                                   <div class="col-lg-8 col-md-8 col-sm-8">
                                                     <div class="input-group input-group-lg">
                                                         <div class="form-line">
                                                             <select name="penjab" class="penjab" id="penjab" data-width="100%">
                                                             <?php
                                                             $query_penjab = query("SELECT * FROM penjab");
                                                             echo '<option>Pilih Asuransi</option>';
                                                             while($data = fetch_assoc($query_penjab)) {
                                                                 echo '<option value="'.$data['kd_pj'].'">'.$data['png_jawab'].'</option>';
                                                             }
                                                             ?>
                                                             </select>
                                                         </div>
                                                     </div>
                                                   </div>
                                                </div>
                                                <div class="row clearfix">
                                                   <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                       <label for="email_address_2">No. Peserta :</label>
                                                   </div>
                                                   <div class="col-lg-8 col-md-8 col-sm-8">
                                                     <div class="input-group input-group-lg">
                                                         <div class="form-line">
                                                             <input type="text" class="form-control" id="no_rawat" placeholder="Nomor Peserta">
                                                         </div>
                                                     </div>
                                                   </div>
                                                </div>
                                                <div class="row clearfix">
                                                   <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                       <label for="email_address_2">No. Telepon :</label>
                                                   </div>
                                                   <div class="col-lg-8 col-md-8 col-sm-8">
                                                     <div class="input-group input-group-lg">
                                                         <div class="form-line">
                                                             <input type="text" class="form-control" id="no_rawat" placeholder="Nomor Telepon">
                                                         </div>
                                                     </div>
                                                   </div>
                                                </div>
                                                <div class="row clearfix">
                                                   <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                       <label for="email_address_2">E-Mail :</label>
                                                   </div>
                                                   <div class="col-lg-8 col-md-8 col-sm-8">
                                                     <div class="input-group input-group-lg">
                                                         <div class="form-line">
                                                             <input type="text" class="form-control" id="no_rawat" placeholder="Alamat e-Mail">
                                                         </div>
                                                     </div>
                                                   </div>
                                                </div>
                                                <div class="row clearfix">
                                                   <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                       <label for="email_address_2">Alamat :</label>
                                                   </div>
                                                   <div class="col-lg-8 col-md-8 col-sm-8">
                                                     <div class="input-group input-group-lg">
                                                         <div class="form-line">
                                                             <textarea rows="4" class="form-control no-resize" placeholder="Alamat Lengkap ....."></textarea>
                                                         </div>
                                                     </div>
                                                   </div>
                                                </div>
                                                <div class="row clearfix">
                                                   <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                       <label for="email_address_2">Propinsi :</label>
                                                   </div>
                                                   <div class="col-lg-8 col-md-8 col-sm-8">
                                                     <div class="input-group input-group-lg">
                                                         <div class="">
                                                            <select name="provinsi" class="provinsi" id="provinsi" data-width="100%">
                                                     		    <?php
                                                     		    $query_provinsi = query("SELECT * FROM propinsi");
                                                     		    echo '<option>Pilih Provinsi</option>';
                                                     		    while($dataprov = fetch_assoc($query_provinsi)) {
                                                     			      echo '<option value="'.$dataprov['kd_prop'].'">'.$dataprov['nm_prop'].'</option>';
                                                     		    }
                                                     		    ?>
                                                     		    </select>
                                                         </div>
                                                     </div>
                                                   </div>
                                                </div>
                                                <div class="row clearfix">
                                                   <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                       <label for="email_address_2">Kabupaten :</label>
                                                   </div>
                                                   <div class="col-lg-8 col-md-8 col-sm-8">
                                                     <div class="input-group input-group-lg">
                                                         <div class="">
                                                             <select name="kota" class="kota" id="kota" data-width="100%">
                                                     		     </select>
                                                         </div>
                                                     </div>
                                                   </div>
                                                </div>
                                                <div class="row clearfix">
                                                   <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                       <label for="email_address_2">Kecamatan :</label>
                                                   </div>
                                                   <div class="col-lg-8 col-md-8 col-sm-8">
                                                     <div class="input-group input-group-lg">
                                                         <div class="">
                                                             <select name="kecamatan" class="kecamatan" id="kecamatan" data-width="100%">
                                                             </select>
                                                         </div>
                                                     </div>
                                                   </div>
                                                </div>
                                                <div class="row clearfix">
                                                   <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                       <label for="email_address_2">Kelurahan :</label>
                                                   </div>
                                                   <div class="col-lg-8 col-md-8 col-sm-8">
                                                     <div class="input-group input-group-lg">
                                                         <div class="form-line">
                                                            <input type="hidden" class="form-control" id="kd_kel"><input type="text" class="form-control" id="nm_kel" placeholder="Nama Kelurahan / Desa">
                                                         </div>
                                                         <span class="input-group-addon">
                                                             <i class="material-icons" data-toggle="modal" data-target="#kelurahanModal">attach_file</i>
                                                         </span>
                                                     </div>
                                                   </div>
                                                </div>
                                              </div>
                                          </div>

                                          <div class="right pendaftaran"><button class="btn btn-default waves-effect accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapsePendaftaran"></button></div>

                                          <hr>

                                          <div id="collapsePendaftaran" class="panel-collapse collapse" style="margin-top:40px;">
                                            <div class="row clearfix" style="margin-top:40px;">
                                                <div class="col-lg-6 col-md-6">
                                                   <div class="row clearfix">
                                                      <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                          <label for="email_address_2">Instansi :</label>
                                                      </div>
                                                      <div class="col-lg-8 col-md-8 col-sm-8">
                                                        <div class="input-group input-group-lg">
                                                            <div class="">
                                                                <select name="instansi" class="instansi" id="instansi" data-width="100%">
                                                         		    <?php
                                                         		    $query_instansi = query("SELECT * FROM perusahaan_pasien");
                                                         		    echo '<option>Pilih Perusahaan</option>';
                                                         		    while($data = fetch_assoc($query_instansi)) {
                                                         			      echo '<option value="'.$data['kode_perusahaan'].'">'.$data['nama_perusahaan'].'</option>';
                                                         		    }
                                                         		    ?>
                                                         		    </select>
                                                            </div>
                                                        </div>
                                                      </div>
                                                   </div>
                                                   <div class="row clearfix">
                                                      <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                          <label for="email_address_2">NIP/NRP :</label>
                                                      </div>
                                                      <div class="col-lg-8 col-md-8 col-sm-8">
                                                        <div class="input-group input-group-lg">
                                                            <div class="form-line">
                                                                <input type="text" class="form-control" id="no_rawat" placeholder="NIP / NRP">
                                                            </div>
                                                        </div>
                                                      </div>
                                                   </div>
                                                   <div class="row clearfix">
                                                      <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                          <label for="email_address_2">Suku Bangsa :</label>
                                                      </div>
                                                      <div class="col-lg-8 col-md-8 col-sm-8">
                                                        <div class="input-group input-group-lg">
                                                            <div class="form-line">
                                                                <select name="sukubangsa" class="sukubangsa" id="sukubangsa" data-width="100%">
                                                                <?php
                                                                $query_sukubangsa = query("SELECT * FROM suku_bangsa");
                                                                echo '<option>Pilih Suku Bangsa</option>';
                                                                while($data = fetch_assoc($query_sukubangsa)) {
                                                                    echo '<option value="'.$data['id'].'">'.$data['nama_suku_bangsa'].'</option>';
                                                                }
                                                                ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                      </div>
                                                   </div>
                                                   <div class="row clearfix">
                                                      <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                          <label for="email_address_2">Bahasa :</label>
                                                      </div>
                                                      <div class="col-lg-8 col-md-8 col-sm-8">
                                                        <div class="input-group input-group-lg">
                                                            <div class="form-line">
                                                                <select name="bahasa" class="bahasa" id="bahasa" data-width="100%">
                                                                <?php
                                                                $query_bahasa = query("SELECT * FROM bahasa_pasien");
                                                                echo '<option>Pilih Bahasa</option>';
                                                                while($data = fetch_assoc($query_bahasa)) {
                                                                    echo '<option value="'.$data['id'].'">'.$data['nama_bahasa'].'</option>';
                                                                }
                                                                ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                      </div>
                                                   </div>
                                                   <div class="row clearfix">
                                                      <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                          <label for="email_address_2">Cacat Fisik :</label>
                                                      </div>
                                                      <div class="col-lg-8 col-md-8 col-sm-8">
                                                        <div class="input-group input-group-lg">
                                                            <div class="form-line">
                                                                <select name="cacatfisik" class="cacatfisik" id="cacatfisik" data-width="100%">
                                                                <?php
                                                                $query_cacatfisik = query("SELECT * FROM cacat_fisik");
                                                                echo '<option>Pilih Cacat Fisik</option>';
                                                                while($data = fetch_assoc($query_cacatfisik)) {
                                                                    echo '<option value="'.$data['id'].'">'.$data['nama_cacat'].'</option>';
                                                                }
                                                                ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                      </div>
                                                   </div>
                                                   <div class="row clearfix">
                                                      <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                          <label for="email_address_2">Png. Jawab :</label>
                                                      </div>
                                                      <div class="col-lg-8 col-md-8 col-sm-8">
                                                        <div class="input-group input-group-lg">
                                                            <div class="form-line">
                                                                <input type="text" class="form-control" id="no_rawat" placeholder="Penanggung Jawab">
                                                            </div>
                                                        </div>
                                                      </div>
                                                   </div>
                                                   <div class="row clearfix">
                                                      <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                          <label for="email_address_2">Pekerjaan PJ. :</label>
                                                      </div>
                                                      <div class="col-lg-8 col-md-8 col-sm-8">
                                                        <div class="input-group input-group-lg">
                                                            <div class="form-line">
                                                                <input type="text" class="form-control" id="no_rawat" placeholder="Pekerjaan Penanggung Jawab">
                                                            </div>
                                                        </div>
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6">
                                                  <div class="row clearfix">
                                                     <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                         <label for="email_address_2">Alamat PJ. :</label>
                                                     </div>
                                                     <div class="col-lg-8 col-md-8 col-sm-8">
                                                       <div class="input-group input-group-lg">
                                                           <div class="form-line">
                                                               <textarea rows="4" class="form-control no-resize" placeholder="Alamat Lengkap ....."></textarea>
                                                           </div>
                                                       </div>
                                                     </div>
                                                  </div>
                                                  <div class="row clearfix">
                                                     <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                         <label for="email_address_2">Propinsi PJ. :</label>
                                                     </div>
                                                     <div class="col-lg-8 col-md-8 col-sm-8">
                                                       <div class="input-group input-group-lg">
                                                           <div class="form-line">
                                                               <input type="text" class="form-control" id="no_rawat" placeholder="Propinsi">
                                                           </div>
                                                       </div>
                                                     </div>
                                                  </div>
                                                  <div class="row clearfix">
                                                     <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                         <label for="email_address_2">Kabupaten PJ. :</label>
                                                     </div>
                                                     <div class="col-lg-8 col-md-8 col-sm-8">
                                                       <div class="input-group input-group-lg">
                                                           <div class="form-line">
                                                               <input type="text" class="form-control" id="no_rawat" placeholder="Kabupaten">
                                                           </div>
                                                       </div>
                                                     </div>
                                                  </div>
                                                  <div class="row clearfix">
                                                     <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                         <label for="email_address_2">Kecamatan PJ. :</label>
                                                     </div>
                                                     <div class="col-lg-8 col-md-8 col-sm-8">
                                                       <div class="input-group input-group-lg">
                                                           <div class="form-line">
                                                               <input type="text" class="form-control" id="no_rawat" placeholder="Kecamatan">
                                                           </div>
                                                       </div>
                                                     </div>
                                                  </div>
                                                  <div class="row clearfix">
                                                     <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                         <label for="email_address_2">Kelurahan PJ. :</label>
                                                     </div>
                                                     <div class="col-lg-8 col-md-8 col-sm-8">
                                                       <div class="input-group input-group-lg">
                                                           <div class="form-line">
                                                               <input type="text" class="form-control" id="no_rawat" placeholder="Kelurahan / Desa">
                                                           </div>
                                                       </div>
                                                     </div>
                                                  </div>
                                                </div>
                                            </div>
                                            <hr>
                                          </div>
                                          <div class="row clearfix" style="margin-top:40px;margin-bottom:40px;">
                                              <div class="col-lg-12 text-center">
                                                  <button type="button" class="btn btn-lg btn-primary m-t-15 m-l-15 waves-effect" id="simpan">SIMPAN</button>
                                                  <button type="button" class="btn btn-lg btn-info m-t-15 m-l-15 waves-effect" id="ganti">GANTI</button>
                                                  <button type="button" class="btn btn-lg btn-danger m-t-15 m-l-15 waves-effect" id="hapus">HAPUS</button>
                                              </div>
                                          </div>
                                      </form>
                                    </div>
                                </div>
                              </div>
                            </div>
                            <div role="tabpanel" class="tab-pane fade in" id="datapasien">
                                <div class="body">
                                    <table id="pasien" class="table table-bordered table-striped table-hover display nowrap" width="100%">
                                        <thead>
                                            <tr>
                                              <th>Nama Pasien</th>
                                              <th>No. RM</th>
                                              <th>No KTP/SIM</th>
                                              <th>J.K</th>
                                              <th>Tmp. Lahir</th>
                                              <th>Tgl. Lahir</th>
                                              <th>Nama Ibu</th>
                                              <th>Alamat</th>
                                              <th>Gol. Darah</th>
                                              <th>Pekerjaan</th>
                                              <th>Stts. Nikah</th>
                                              <th>Agama</th>
                                              <th>Tgl. Daftar</th>
                                              <th>No. Tlp</th>
                                              <th>Umur</th>
                                              <th>Pendidikan</th>
                                              <th>Keluarga</th>
                                              <th>Nama Keluarga</th>
                                              <th>Asuransi</th>
                                              <th>No. Asuransi</th>
                                              <th>Pekerjaan PJ</th>
                                              <th>Alamat PJ</th>
                                              <th>Suku Bangsa</th>
                                              <th>Bahasa</th>
                                              <th>Instansi/Perusahaan</th>
                                              <th>NIP/NRP</th>
                                              <th>E-Mail</th>
                                              <th>Cacat Fisik</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="kelurahanModal" tabindex="-1" role="dialog" aria-labelledby="kelurahanModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="width:800px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="kelurahanModalLabel">Database Poliklinik</h4>
                </div>
                <div class="modal-body">
                    <table id="kelurahan" class="table responsive table-bordered table-striped table-hover display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Kode Kelurahan</th>
                                <th>Nama Kelurahan</th>
                            </tr>
                        </thead>
                        <tbody>
                          <?php
                          $sql_kelurahan = "SELECT * FROM kelurahan";
                          $result_kelurahan = query($sql_kelurahan);
                          while($row = fetch_array($result_kelurahan)) {
                            echo '<tr class="pilihkelurahan" data-kdkel='.$row[0].' data-nmkel='.$row[1].'>';
                            echo '<td>'.$row[0].'</td>';
                            echo '<td>'.$row[1].'</td>';
                            echo '</tr>';
                          }
                          ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<?php
include_once('layout/footer.php');
?>
<script>
  $('#pasien').dataTable( {
        "bInfo" : true,
      	"scrollX": true,
        "processing": true,
        "serverSide": true,
        "responsive": false,
        "oLanguage": {
            "sProcessing":   "Sedang memproses...",
            "sLengthMenu":   "Tampilkan _MENU_ entri",
            "sZeroRecords":  "Tidak ditemukan data yang sesuai",
            "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
            "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
            "sInfoPostFix":  "",
            "sSearch":       "Cari:",
            "sUrl":          "",
            "oPaginate": {
                "sFirst":    "«",
                "sPrevious": "‹",
                "sNext":     "›",
                "sLast":     "»"
            }
        },
        "order": [[ 0, "asc" ]],
        "ajax": "includes/pasien.php"
  } );
  $('#kelurahan').dataTable( {
        "processing": true,
        "responsive": false,
        "oLanguage": {
            "sProcessing":   "Sedang memproses...",
            "sLengthMenu":   "Tampilkan _MENU_ entri",
            "sZeroRecords":  "Tidak ditemukan data yang sesuai",
            "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
            "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
            "sInfoPostFix":  "",
            "sSearch":       "Cari:",
            "sUrl":          "",
            "oPaginate": {
                "sFirst":    "«",
                "sPrevious": "‹",
                "sNext":     "›",
                "sLast":     "»"
            }
        },
        "order": [[ 0, "asc" ]]
  } );
</script>
<script>
    $(document).on('click', '.pilihkelurahan', function (e) {
        document.getElementById("kd_kel").value = $(this).attr('data-kdkel');
        document.getElementById("nm_kel").value = $(this).attr('data-nmkel');
        $('#kelurahanModal').modal('hide');
    });
</script>
<script>
    $(function() {
      $('body').on('change', '.provinsi', function(e) {
          e.preventDefault();
              var kode = $("#provinsi").val();
          $.ajax({
              type : "POST",
              url  : "includes/wilayah.php?page=cari-kota",
              data :  {kode : kode},
              success : function(data){
                  $("#kota").html(data).selectpicker('refresh');
                  getKota();
              }
          });
      });

      $('body').on('change', '.kota', getKota);
      function getKota(){
          var kode = $("#kota").val();
          $.ajax({
              type : "POST",
              url  : "includes/wilayah.php?page=cari-kecamatan",
              data :  {kode : kode},
              success : function(data){
                  $("#kecamatan").html(data).selectpicker('refresh');
                  getKecamatan();
              }
          });
      }

    });
</script>
