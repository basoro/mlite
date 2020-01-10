<div class="card">
    <div class="header">
        <h2>
            Tambah Data Pegawai
        </h2>
    </div>
    <div class="body">
        <?php
      	if (isset($_POST['simpan'])) {

            if($_FILES['file']['name'] !=='') {
                $tmp_name = $_FILES["file"]["tmp_name"];
                $namefile = $_FILES["file"]["name"];
                $explode = explode(".", $namefile);
                $ext = end($explode);
                $image_name = "pages/pegawai/photo/".$_SESSION['username']."-".time().".".$ext;
                move_uploaded_file($tmp_name,WEBAPPS."/penggajian/".$image_name);
            } else {
              $image_name = '-';
            }

          	$insert = query("INSERT INTO `pegawai`
              (
                `id`, `nik`, `nama`, `jk`, `jbtn`, `jnj_jabatan`, `kode_kelompok`, `kode_resiko`, `kode_emergency`, `departemen`, `bidang`, `stts_wp`, `stts_kerja`, `npwp`, `pendidikan`, `gapok`, `tmp_lahir`, `tgl_lahir`, `alamat`, `kota`, `mulai_kerja`, `ms_kerja`, `indexins`, `bpd`, `rekening`, `stts_aktif`, `wajibmasuk`, `pengurang`, `indek`, `mulai_kontrak`, `cuti_diambil`, `dankes`, `photo`, `no_ktp`
              )
                VALUES
              (
                NULL, '{$_POST['NIP']}', '{$_POST['nama']}', '{$_POST['jk']}', '{$_POST['jbtn']}', '-', '-', '-', '-', '{$_POST['dpt']}', '{$_POST['bdng']}', '{$_POST['swp']}', '-', '{$_POST['npwp']}', '{$_POST['pddkn']}', '0', '{$_POST['tempat']}', '{$_POST['tanggal']}', '{$_POST['alamat']}', '-', '{$_POST['mker']}', '{$_POST['ms_kerja']}', '-', '{$_POST['bank']}', '0', '{$_POST['stts_aktif']}', '0', '0', '0', '{$_POST['mkon']}', '0', '0', '{$image_name}', '{$_POST['NIK']}'
              )
            ");

        };
        ?>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade in active" id="profile_settings">
                <form class="form-horizontal" method="post" action=""  enctype="multipart/form-data">
                    <div class="form-group">
                        <div class="col-sm-4"></div>
                        <div class="col-sm-4">
                            <div class="form">
                              <img id="image_upload_preview" width="200px" style="-webkit-border-radius: 50%; -moz-border-radius: 50%; -ms-border-radius: 50%; border-radius: 50%;" src="<?php echo URL; ?>/assets/images/no-photo.jpg" onclick="upload_berkas()" style="cursor:pointer;" alt="User" />
                              <br/>
                              <input name="file" id="inputFile" type="file" style="display:none;"/>
                            </div>
                        </div>
                        <div class="col-sm-4"></div>
                    </div>
                    <div class="form-group">
                        <label for="nama" class="col-sm-2 control-label">Nama Lengkap</label>
                        <div class="col-sm-10">
                            <div class="form-line">
                                <input type="text" class="form-control" id="NameSurname" name="nama" placeholder="Nama Lengkap" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                          <label for="NIP" class="col-sm-2 control-label">NIP</label>
                          <div class="col-sm-10">
                              <div class="form-line">
                                  <input type="text" class="form-control" id="NIP" name="NIP" placeholder="NIP">
                              </div>
                          </div>
                    </div>
                  	<div class="form-group">
                          <label for="NIK" class="col-sm-2 control-label">NIK / No KTP</label>
                          <div class="col-sm-10">
                              <div class="form-line">
                                  <input type="text" class="form-control" id="NIK" name="NIK" placeholder="NIK" required>
                              </div>
                          </div>
                    </div>
                    <div class="form-group">
                      	  <label for="jk" class="col-sm-2 control-label">Jenis Kelamin</label>
                          <div class="col-sm-10">
                              <div class="form-line">
                                  <input class="with-gap" type="radio" name="jk" id="Pria" value="Pria">
                              	  <label for="Pria">
                                      Pria
                            		  </label>
                            		  <input class="with-gap" type="radio" name="jk" id="Wanita" value="Wanita">
                            		  <label class="m-l-20" for="Wanita">
                              		    Wanita
                            		  </label>
                          		</div>
                      	  </div>
                    </div>
                    <div class="form-group">
                        <label for="tempat" class="col-sm-2 control-label">Tempat Lahir</label>
                        <div class="col-sm-10">
                            <div class="form-line">
                                <input type="text" class="form-control" id="tempat" name="tempat" placeholder="Tempat Lahir" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tanggal" class="col-sm-2 control-label">Tanggal Lahir</label>
                        <div class="col-sm-10">
                            <div class="form-line">
                                <input type="text" class="datepicker form-control" id="tanggal" name="tanggal" placeholder="Tanggal Lahir" required>
                            </div>
                        </div>
                    </div>
                  	<div class="form-group">
                        <label for="pddkn" class="col-sm-2 control-label">Pendidikan</label>
                        <div class="col-sm-10">
                            <div class="form-line">
                                <select name="pddkn" class="form-control">
                                    <?php $c = query("SELECT * FROM pendidikan"); while($d = fetch_assoc($c)){?>
                                    <option value="<?php echo $d['tingkat'];?>"><?php echo $d['tingkat'];?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="swp" class="col-sm-2 control-label">Status Keluarga</label>
                        <div class="col-sm-10">
                            <div class="form-line">
                                <select name="swp" class="form-control">
                                    <?php $c = query("SELECT * FROM stts_wp"); while($d = fetch_assoc($c)){?>
                                    <option value="<?php echo $d['stts'];?>"><?php echo $d['ktg'];?></option>
                                    <?php } ?>
                                </select>
                            </div>
                      	</div>
                    </div>
                  	<div class="form-group">
                        <label for="npwp" class="col-sm-2 control-label">NPWP</label>
                        <div class="col-sm-10">
                            <div class="form-line">
                                <input type="text" class="form-control" id="npwp" name="npwp" placeholder="NPWP" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="alamat" class="col-sm-2 control-label">Alamat</label>
                        <div class="col-sm-10">
                            <div class="form-line">
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" maxlength="60" placeholder="Alamat" value=""></textarea>
                            </div>
                        </div>
                    </div>
                  	<div class="form-group">
                        <label for="jbtn" class="col-sm-2 control-label">Jabatan</label>
                        <div class="col-sm-10">
                            <div class="form-line">
                                <input type="text" class="form-control" id="jbtn" name="jbtn" placeholder="Jabatan" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="bdng" class="col-sm-2 control-label">Bidang</label>
                        <div class="col-sm-10">
                            <div class="form-line">
                                <select name="bdng" class="form-control">
                                    <?php $c = query("SELECT * FROM bidang"); while($d = fetch_assoc($c)){?>
                                    <option value="<?php echo $d['nama'];?>"><?php echo $d['nama'];?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="dpt" class="col-sm-2 control-label">Departemen</label>
                        <div class="col-sm-10">
                            <div class="form-line">
                                <select name="dpt" class="form-control">
                                    <?php $c = query("SELECT * FROM departemen"); while($d = fetch_assoc($c)){?>
                                    <option value="<?php echo $d['dep_id'];?>"><?php echo $d['nama'];?></option>
                                    <?php } ?>
                                </select>
                            </div>
                      	</div>
                    </div>
                    <div class="form-group">
                        <label for="sk" class="col-sm-2 control-label">Status</label>
                        <div class="col-sm-10">
                            <div class="form-line">
                                <?php echo enumDropdown('pegawai', 'ms_kerja', '&nbsp;', ''); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="swp" class="col-sm-2 control-label">Akun Bank</label>
                        <div class="col-sm-10">
                            <div class="form-line">
                                <select name="bank" class="form-control">
                                    <?php $c = query("SELECT * FROM bank"); while($d = fetch_assoc($c)){?>
                                    <option value="<?php echo $d['namabank'];?>"><?php echo $d['namabank'];?></option>
                                    <?php } ?>
                                </select>
                            </div>
                      	</div>
                    </div>
                    <div class="form-group">
                        <label for="mker" class="col-sm-2 control-label">Mulai Kerja</label>
                        <div class="col-sm-10">
                            <div class="form-line">
                                <input type="text" class="datepicker form-control" id="mker" name="mker" placeholder="Mulai Kerja" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mkon" class="col-sm-2 control-label">Mulai Kontrak</label>
                        <div class="col-sm-10">
                            <div class="form-line">
                                <input type="text" class="datepicker form-control" id="mkon" name="mkon" placeholder="Mulai Kontrak" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="sak" class="col-sm-2 control-label">Status Keaktifan</label>
                        <div class="col-sm-10">
                            <div class="form-line">
                                <?php echo enumDropdown('pegawai', 'stts_aktif', '&nbsp;', ''); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <input type="submit" name="simpan" class="btn btn-danger" value="SIMPAN" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
