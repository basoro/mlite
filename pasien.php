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
                                                              <input type="text" class="form-control" id="no_rkm_medis"  value="<?php echo $no_rm_next; ?>" placeholder="Nomor Rekam Medik">
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
                                                              <input type="text" class="form-control" id="nm_pasien" placeholder="Nama Lengkap Pasien">
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
                                                              <input type="text" class="form-control" id="no_ktp" placeholder="Nomor Induk Kependudukan">
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
                                                              <input type="text" class="form-control" id="tmp_lahir" placeholder="Tempat Lahir">
                                                          </div>
                                                      </div>
                                                    </div>
                                                    <div class="col-lg-3 col-md-3 col-sm-3">
                                                      <div class="input-group input-group-lg">
                                                          <div class="form-line">
                                                              <input type="text" class="form-control datepicker" id="tgl_lahir" placeholder="Tgl Lahir">
                                                          </div>
                                                      </div>
                                                    </div>
                                                 </div>
                                                 <div class="row clearfix">
                                                    <div class="col-lg-4 col-md-4 col-sm-4 form-control-label font-20 hidden-xs">
                                                        <label for="email_address_2">Pendidikan :</label>
                                                    </div>
                                                    <div class="col-lg-8 col-md-8 col-sm-8">
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
                                                              <select name="agama" id="agama" data-width="100%">
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
                                                              <input type="text" class="form-control" id="pekerjaan" placeholder="Pekerjaan">
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
                                                              <input type="text" class="form-control" id="nm_ibu" placeholder="Nama Ibu">
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
                                                            <input name="keluarga" type="radio" value="Ayah" id="ayah" class="with-gap radio-col-red" checked/>
                                                            <label for="ayah">Ayah</label>
                                                            <input name="keluarga" type="radio" value="Ibu" id="ibu" class="with-gap radio-col-pink" />
                                                            <label for="ibu">Ibu</label>
                                                            <input name="keluarga" type="radio" value="Suami" id="suami" class="with-gap radio-col-purple" />
                                                            <label for="suami">Suami</label>
                                                            <input name="keluarga" type="radio" value="Istri" id="istri" class="with-gap radio-col-deep-purple" />
                                                            <label for="istri">Istri</label>
                                                            <input name="keluarga" type="radio" value="Saudara" id="saudara" class="with-gap radio-col-indigo" />
                                                            <label for="saudara">Saudara</label>
                                                            <input name="keluarga" type="radio" value="Anak" id="anak" class="with-gap radio-col-blue" />
                                                            <label for="anak">Anak</label>
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
                                                             <select name="penjab" class="penjab" id="kd_pj" data-width="100%">
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
                                                             <input type="text" class="form-control" id="no_peserta" placeholder="Nomor Peserta">
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
                                                             <input type="text" class="form-control" id="no_tlp" placeholder="Nomor Telepon">
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
                                                             <input type="text" class="form-control" id="email" placeholder="Alamat e-Mail">
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
                                                             <textarea rows="4" class="form-control no-resize" id="alamat" placeholder="Alamat Lengkap ....."></textarea>
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
                                                            <select name="kd_prop" class="kd_prop" id="kd_prop" data-width="100%">
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
                                                             <select name="kd_kab" class="kd_kab" id="kd_kab" data-width="100%">
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
                                                             <select name="kd_kec" class="kd_kec" id="kd_kec" data-width="100%">
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
                                                                <select name="perusahaan_pasien" class="perusahaan_pasien" id="perusahaan_pasien" data-width="100%">
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
                                                                <input type="text" class="form-control" id="nip" placeholder="NIP / NRP">
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
                                                                <select name="suku_bangsa" class="suku_bangsa" id="suku_bangsa" data-width="100%">
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
                                                                <select name="bahasa_pasien" class="bahasa_pasien" id="bahasa_pasien" data-width="100%">
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
                                                                <select name="cacat_fisik" class="cacat_fisik" id="cacat_fisik" data-width="100%">
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
                                                                <input type="text" class="form-control" id="namakeluarga" placeholder="Penanggung Jawab">
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
                                                                <input type="text" class="form-control" id="pekerjaanpj" placeholder="Pekerjaan Penanggung Jawab">
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
                                                               <textarea rows="4" class="form-control no-resize" id="alamatpj" placeholder="Alamat Lengkap ....."></textarea>
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
                                                               <input type="text" class="form-control" id="propinsipj" placeholder="Propinsi">
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
                                                               <input type="text" class="form-control" id="kabupatenpj" placeholder="Kabupaten">
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
                                                               <input type="text" class="form-control" id="kecamatanpj" placeholder="Kecamatan">
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
                                                               <input type="text" class="form-control" id="kelurahanpj" placeholder="Kelurahan / Desa">
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
        "ajax": "<?php echo URL;?>/includes/pasien.php",
        "createdRow": function( row, data, index ) {
          	$(row).addClass('editpasien');
            $(row).attr('data-nm_pasien', data[0]);
            $(row).attr('data-no_rkm_medis', data[1]);
            $(row).attr('data-no_ktp', data[2]);
            $(row).attr('data-jk', data[3]);
            $(row).attr('data-tmp_lahir', data[4]);
            $(row).attr('data-tgl_lahir', data[5]);
            $(row).attr('data-nm_ibu', data[6]);
            $(row).attr('data-alamat', data[7]);
            $(row).attr('data-gol_darah', data[8]);
            $(row).attr('data-pekerjaan', data[9]);
            $(row).attr('data-stts_nikah', data[10]);
            $(row).attr('data-agama', data[11]);
            $(row).attr('data-tgl_daftar', data[12]);
            $(row).attr('data-no_tlp', data[13]);
            $(row).attr('data-umur', data[14]);
            $(row).attr('data-pnd', data[15]);
            $(row).attr('data-keluarga', data[16]);
            $(row).attr('data-namakeluarga', data[17]);
            $(row).attr('data-kd_pj', data[18]);
            $(row).attr('data-no_peserta', data[19]);
            $(row).attr('data-pekerjaanpj', data[20]);
            $(row).attr('data-alamatpj', data[21]);
            $(row).attr('data-nip', data[22]);
            $(row).attr('data-email', data[23]);
            $(row).attr('data-cacat_fisik', data[24]);
            $(row).attr('data-kelurahanpj', data[25]);
            $(row).attr('data-kecamatanpj', data[26]);
            $(row).attr('data-kabupatenpj', data[27]);
            $(row).attr('data-propinsipj', data[28]);
            $(row).attr('data-suku_bangsa', data[29]);
            $(row).attr('data-bahasa_pasien', data[30]);
            $(row).attr('data-perusahaan_pasien', data[31]);
        }
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
        "order": [[ 0, "asc" ]],
        "ajax": "includes/wilayah.php?page=kelurahan",
        "createdRow": function( row, data, index ) {
          	$(row).addClass('pilihkelurahan');
            $(row).attr('data-kdkel', data[0]);
            $(row).attr('data-nmkel', data[1]);
        }
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
      $('body').on('change', '.kd_prop', function(e) {
          e.preventDefault();
              var kode = $("#kd_prop").val();
          $.ajax({
              type : "POST",
              url  : "includes/wilayah.php?page=cari-kota",
              data :  {kode : kode},
              success : function(data){
                  $("#kd_kab").html(data).selectpicker('refresh');
                  getKota();
              }
          });
      });

      $('body').on('change', '.kd_kab', getKota);
      function getKota(){
          var kode = $("#kd_kab").val();
          $.ajax({
              type : "POST",
              url  : "includes/wilayah.php?page=cari-kecamatan",
              data :  {kode : kode},
              success : function(data){
                  $("#kd_kec").html(data).selectpicker('refresh');
                  getKecamatan();
              }
          });
      }

    });
    $(document).on('click', '.editpasien', function (e) {
      document.getElementById("no_rkm_medis").value = $(this).attr('data-no_rkm_medis');
      document.getElementById("nm_pasien").value = $(this).attr('data-nm_pasien');
      document.getElementById("no_ktp").value = $(this).attr('data-no_ktp');
      document.getElementById("jk").value = $(this).attr('data-jk');
      document.getElementById("tmp_lahir").value = $(this).attr('data-tmp_lahir');
      document.getElementById("tgl_lahir").value = $(this).attr('data-tgl_lahir');
      document.getElementById("nm_ibu").value = $(this).attr('data-nm_ibu');
      document.getElementById("alamat").value = $(this).attr('data-alamat');
      document.getElementById("gol_darah").value = $(this).attr('data-gol_darah');
      document.getElementById("pekerjaan").value = $(this).attr('data-pekerjaan');
      document.getElementById("stts_nikah").value = $(this).attr('data-stts_nikah');
      document.getElementById("agama").value = $(this).attr('data-agama');
      document.getElementById("no_tlp").value = $(this).attr('data-no_tlp');
      document.getElementById("pnd").value = $(this).attr('data-pnd');
      document.getElementById("namakeluarga").value = $(this).attr('data-namakeluarga');
      document.getElementById("kd_pj").value = $(this).attr('data-kd_pj');
      document.getElementById("no_peserta").value = $(this).attr('data-no_peserta');
      document.getElementById("kd_kel").value = $(this).attr('data-kd_kel');
      document.getElementById("kd_kec").value = $(this).attr('data-kd_kec');
      document.getElementById("kd_kab").value = $(this).attr('data-kd_kab');
      document.getElementById("pekerjaanpj").value = $(this).attr('data-pekerjaanpj');
      document.getElementById("alamatpj").value = $(this).attr('data-alamatpj');
      document.getElementById("kelurahanpj").value = $(this).attr('data-kelurahanpj');
      document.getElementById("kecamatanpj").value = $(this).attr('data-kecamatanpj');
      document.getElementById("kabupatenpj").value = $(this).attr('data-kabupatenpj');
      document.getElementById("perusahaan_pasien").value = $(this).attr('data-perusahaan_pasien');
      document.getElementById("suku_bangsa").value = $(this).attr('data-suku_bangsa');
      document.getElementById("bahasa_pasien").value = $(this).attr('data-bahasa_pasien');
      document.getElementById("cacat_fisik").value = $(this).attr('data-cacat_fisik');
      document.getElementById("email").value = $(this).attr('data-email');
      document.getElementById("nip").value = $(this).attr('data-nip');
      document.getElementById("kd_prop").value = $(this).attr('data-kd_prop');
      document.getElementById("propinsipj").value = $(this).attr('data-propinsipj');
    });
    $("#simpan").click(function(){
        var no_rkm_medis = document.getElementById("no_rkm_medis").value;
        var nm_pasien = document.getElementById("nm_pasien").value;
        var no_ktp = document.getElementById("no_ktp").value;
        var jk = document.getElementById("jk").value;
        var tmp_lahir = document.getElementById("tmp_lahir").value;
        var tgl_lahir = document.getElementById("tgl_lahir").value;
        var nm_ibu = document.getElementById("nm_ibu").value;
        var keluarga = document.querySelector('input[name="keluarga"]:checked').value;
        var alamat = document.getElementById("alamat").value;
        var gol_darah = document.getElementById("gol_darah").value;
        var pekerjaan = document.getElementById("pekerjaan").value;
        var stts_nikah = document.getElementById("stts_nikah").value;
        var agama = document.getElementById("agama").value;
        var no_tlp = document.getElementById("no_tlp").value;
        var pnd = document.getElementById("pnd").value;
        var namakeluarga = document.getElementById("namakeluarga").value;
        var kd_pj = document.getElementById("kd_pj").value;
        var no_peserta = document.getElementById("no_peserta").value;
        var kd_kel = document.getElementById("kd_kel").value;
        var kd_kec = document.getElementById("kd_kec").value;
        var kd_kab = document.getElementById("kd_kab").value;
        var pekerjaanpj = document.getElementById("pekerjaanpj").value;
        var alamatpj = document.getElementById("alamatpj").value;
        var kelurahanpj = document.getElementById("kelurahanpj").value;
        var kecamatanpj = document.getElementById("kecamatanpj").value;
        var kabupatenpj = document.getElementById("kabupatenpj").value;
        var perusahaan_pasien = document.getElementById("perusahaan_pasien").value;
        var suku_bangsa = document.getElementById("suku_bangsa").value;
        var bahasa_pasien = document.getElementById("bahasa_pasien").value;
        var cacat_fisik = document.getElementById("cacat_fisik").value;
        var email = document.getElementById("email").value;
        var nip = document.getElementById("nip").value;
        var kd_prop = document.getElementById("kd_prop").value;
        var propinsipj = document.getElementById("propinsipj").value;
        $.ajax({
            url:'includes/pasien.php?p=add',
            method:'POST',
            data:{
                no_rkm_medis:no_rkm_medis,
                nm_pasien:nm_pasien,
                no_ktp:no_ktp,
                jk:jk,
                tmp_lahir:tmp_lahir,
                tgl_lahir:tgl_lahir,
                nm_ibu:nm_ibu,
                keluarga:keluarga,
                alamat:alamat,
                gol_darah:gol_darah,
                pekerjaan:pekerjaan,
                stts_nikah:stts_nikah,
                agama:agama,
                no_tlp:no_tlp,
                pnd:pnd,
                namakeluarga:namakeluarga,
                kd_pj:kd_pj,
                no_peserta:no_peserta,
                kd_kel:kd_kel,
                kd_kec:kd_kec,
                kd_kab:kd_kab,
                pekerjaanpj:pekerjaanpj,
                alamatpj:alamatpj,
                kelurahanpj:kelurahanpj,
                kecamatanpj:kecamatanpj,
                kabupatenpj:kabupatenpj,
                perusahaan_pasien:perusahaan_pasien,
                suku_bangsa:suku_bangsa,
                bahasa_pasien:bahasa_pasien,
                cacat_fisik:cacat_fisik,
                email:email,
                nip:nip,
                kd_prop:kd_prop,
                propinsipj:propinsipj
            },
           success:function(data){
                window.location.reload(true)
                //$('#pasien').load("includes/pasien.php");
           }
        });
    });
    $("#ganti").click(function(){
        var no_rkm_medis = document.getElementById("no_rkm_medis").value;
        var nm_pasien = document.getElementById("nm_pasien").value;
        var no_ktp = document.getElementById("no_ktp").value;
        var jk = document.getElementById("jk").value;
        var tmp_lahir = document.getElementById("tmp_lahir").value;
        var tgl_lahir = document.getElementById("tgl_lahir").value;
        var nm_ibu = document.getElementById("nm_ibu").value;
        var keluarga = document.querySelector('input[name="keluarga"]:checked').value;
        var alamat = document.getElementById("alamat").value;
        var gol_darah = document.getElementById("gol_darah").value;
        var pekerjaan = document.getElementById("pekerjaan").value;
        var stts_nikah = document.getElementById("stts_nikah").value;
        var agama = document.getElementById("agama").value;
        var no_tlp = document.getElementById("no_tlp").value;
        var pnd = document.getElementById("pnd").value;
        var namakeluarga = document.getElementById("namakeluarga").value;
        var kd_pj = document.getElementById("kd_pj").value;
        var no_peserta = document.getElementById("no_peserta").value;
        var kd_kel = document.getElementById("kd_kel").value;
        var kd_kec = document.getElementById("kd_kec").value;
        var kd_kab = document.getElementById("kd_kab").value;
        var pekerjaanpj = document.getElementById("pekerjaanpj").value;
        var alamatpj = document.getElementById("alamatpj").value;
        var kelurahanpj = document.getElementById("kelurahanpj").value;
        var kecamatanpj = document.getElementById("kecamatanpj").value;
        var kabupatenpj = document.getElementById("kabupatenpj").value;
        var perusahaan_pasien = document.getElementById("perusahaan_pasien").value;
        var suku_bangsa = document.getElementById("suku_bangsa").value;
        var bahasa_pasien = document.getElementById("bahasa_pasien").value;
        var cacat_fisik = document.getElementById("cacat_fisik").value;
        var email = document.getElementById("email").value;
        var nip = document.getElementById("nip").value;
        var kd_prop = document.getElementById("kd_prop").value;
        var propinsipj = document.getElementById("propinsipj").value;
        $.ajax({
            url:'includes/pasien.php?p=update',
            method:'POST',
            data:{
                no_rkm_medis:no_rkm_medis,
                nm_pasien:nm_pasien,
                no_ktp:no_ktp,
                jk:jk,
                tmp_lahir:tmp_lahir,
                tgl_lahir:tgl_lahir,
                nm_ibu:nm_ibu,
                keluarga:keluarga,
                alamat:alamat,
                gol_darah:gol_darah,
                pekerjaan:pekerjaan,
                stts_nikah:stts_nikah,
                agama:agama,
                no_tlp:no_tlp,
                pnd:pnd,
                namakeluarga:namakeluarga,
                kd_pj:kd_pj,
                no_peserta:no_peserta,
                kd_kel:kd_kel,
                kd_kec:kd_kec,
                kd_kab:kd_kab,
                pekerjaanpj:pekerjaanpj,
                alamatpj:alamatpj,
                kelurahanpj:kelurahanpj,
                kecamatanpj:kecamatanpj,
                kabupatenpj:kabupatenpj,
                perusahaan_pasien:perusahaan_pasien,
                suku_bangsa:suku_bangsa,
                bahasa_pasien:bahasa_pasien,
                cacat_fisik:cacat_fisik,
                email:email,
                nip:nip,
                kd_prop:kd_prop,
                propinsipj:propinsipj
            },
           success:function(data){
                window.location.reload(true)
           }
        });
    });
    $("#hapus").click(function(){
        var no_rkm_medis = document.getElementById("no_rkm_medis").value;
        $.ajax({
            url:'includes/pasien.php?p=delete',
            method:'POST',
            data:{
              no_rkm_medis:no_rkm_medis
            },
            success:function(data){
               window.location.reload(true)
            }
        });
    });
</script>
