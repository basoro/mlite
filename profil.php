<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : dentix.id@gmail.com
* Licence under GPL
***/

$title = 'Profile Anda';
include_once('config.php');
include_once('layout/header.php');
include_once('layout/sidebar.php');
$a = query("SELECT * FROM pegawai WHERE nik = '{$_SESSION['username']}'");
$b = fetch_assoc($a);
?>

<section class="content">
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-xs-12">
                <div class="card">
                    <div class="header">
                        <h2>
                            <?php echo $title; ?>
                        </h2>
                    </div>
                    <div class="body">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#profile_settings" aria-controls="settings" role="tab" data-toggle="tab">Biodata</a></li>
                            <li role="presentation"><a href="#work" aria-controls="settings" role="tab" data-toggle="tab">Status Kerja</a></li>
                            <li role="presentation"><a href="#change_password_settings" aria-controls="settings" role="tab" data-toggle="tab">Password</a></li>
                        </ul>
							          <?php
                      	if (isset($_POST['bio'])) {

                            if($_FILES['file']['name'] !=='') {
                                $file=WEBAPPS.'/penggajian/'.$b['photo'];
                                @unlink($file);
                                $tmp_name = $_FILES["file"]["tmp_name"];
                                $namefile = $_FILES["file"]["name"];
                                $explode = explode(".", $namefile);
                                $ext = end($explode);
                                $image_name = "pages/pegawai/photo/".$_SESSION['username']."-".time().".".$ext;
                                move_uploaded_file($tmp_name,WEBAPPS."/penggajian/".$image_name);
                            } else {
                                $image_name=$b['photo'];
                            }

                            if($_POST['jk'] == 'L') {
                                $jk = 'Pria';
                            } else {
                                $jk = 'Wanita';
                            }

                          	$insert = query("UPDATE
                                    pegawai
                                SET
                                    nama = '{$_POST['nama']}',
                                    jk = '{$jk}',
                                    tmp_lahir = '{$_POST['tempat']}',
                                    tgl_lahir = '{$_POST['tanggal']}',
                                    pendidikan = '{$_POST['pddkn']}',
                                    stts_wp = '{$_POST['swp']}',
                                    npwp = '{$_POST['npwp']}',
                                    alamat = '{$_POST['alamat']}',
                                    no_ktp = '{$_POST['NIK']}',
                                    photo = '{$image_name}'
                                WHERE
                                    nik = '{$_SESSION['username']}'
                            ");
                            if ($insert) {
                                $duainsert = query("UPDATE
                                        petugas
                                    SET
                                        nama = '{$_POST['nama']}'
                                    WHERE
                                        nip = '{$_SESSION['username']}'
                                ");
                                $duainsert = query("UPDATE
                                        dokter
                                    SET
                                        nm_dokter = '{$_POST['nama']}',
                                        jk = '{$_POST['jk']}'
                                    WHERE
                                        kd_dokter = '{$_SESSION['username']}'
                                ");
                                if ($duainsert){
                                    redirect("profil.php");
                                }
                            };
                        };
                        if(isset($_POST['stskrja'])){
                            $insert = query("UPDATE
                                    pegawai
                                SET
                                    jbtn = '{$_POST['jbtn']}',
                                    bidang = '{$_POST['bdng']}',
                                    departemen = '{$_POST['dpt']}',
                                    mulai_kerja = '{$_POST['mker']}',
                                    mulai_kontrak = '{$_POST['mkon']}'
                                WHERE
                                    nik = '{$_SESSION['username']}'

                            ");
                            if($insert){
                                redirect("profil.php");
                            };
                        };
                        if(isset($_POST['ganti_password'])){
                            $old_password   = $_POST['OldPassword'];
                            $new_password    = $_POST['NewPassword'];
                            $new_password2  = $_POST['NewPasswordConfirm'];

                            $cek_pass = fetch_assoc(query("SELECT AES_DECRYPT(id_user,'nur') as id_user, AES_DECRYPT(password,'windi') as password FROM user WHERE id_user = AES_ENCRYPT('{$_SESSION['username']}','nur')"));

                            if($old_password !== $cek_pass['password']) {
                                $errors[] = 'Recent password dont match';
                            }

                            if($new_password !== $new_password2) {
                                $errors[] = 'Password dont match confirm password';
                            }

                            if (!empty($errors)) {
                                foreach($errors as $error) {
                                   echo validation_errors($error);
                                }
                            } else {
                                $insert = query("UPDATE
                                        user
                                    SET
                                        password = AES_ENCRYPT('{$new_password}','windi')
                                    WHERE
                                        id_user = AES_ENCRYPT('{$_SESSION['username']}','nur')
                                ");
                                if($insert){
                                    redirect("profil.php");
                                };
                            }
                        };
                        ?>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane fade in active" id="profile_settings">
                                <form class="form-horizontal" method="post" action=""  enctype="multipart/form-data">
                                    <div class="form-group">
                                        <div class="col-sm-4"></div>
                                        <div class="col-sm-4">
                                            <div class="form">
                                              <?php
                                              if($b['photo'] == '') {
                                                if ($dataGet['1'] == 'L' || $dataGet['1'] == 'Pria') {
                                                    echo '<img id="image_upload_preview" width="200px" style="-webkit-border-radius: 50%; -moz-border-radius: 50%; -ms-border-radius: 50%; border-radius: 50%;" src="'.URL.'/assets/images/pria.png" onclick="upload_berkas()" style="cursor:pointer;" alt="User" />';
                                                } else if ($dataGet['1'] == 'P' || $dataGet['1'] == 'Wanita') {
                                                    echo '<img id="image_upload_preview" width="200px" style="-webkit-border-radius: 50%; -moz-border-radius: 50%; -ms-border-radius: 50%; border-radius: 50%;" src="'.URL.'/assets/images/wanita.png" onclick="upload_berkas()" style="cursor:pointer;" alt="User" />';
                                                } else {
                                                  echo '<img id="image_upload_preview" width="200px" style="-webkit-border-radius: 50%; -moz-border-radius: 50%; -ms-border-radius: 50%; border-radius: 50%;" src="'.URL.'/assets/images/no-photo.jpg" onclick="upload_berkas()" style="cursor:pointer;" alt="User" />';
                                                }
                                              } else {
                                                echo '<img id="image_upload_preview" width="200px" style="-webkit-border-radius: 50%; -moz-border-radius: 50%; -ms-border-radius: 50%; border-radius: 50%;" src="'.URLSIMRS.'/penggajian/'.$b['photo'].'" onclick="upload_berkas()" style="cursor:pointer;" alt="User" />';
                                              }
                                              ?>
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
                                                <input type="text" class="form-control" id="NameSurname" name="nama" placeholder="Name Surname" value="<?php echo $dataGet['0']; ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                          <label for="NIP" class="col-sm-2 control-label">NIP</label>
                                          <div class="col-sm-10">
                                              <div class="form-line">
                                                  <input type="text" class="form-control" id="NIP" name="NIP" placeholder="NIP" value="<?php echo $_SESSION['username']; ?>" readonly>
                                              </div>
                                          </div>
                                    </div>
                                  	<div class="form-group">
                                          <label for="NIK" class="col-sm-2 control-label">NIK / No KTP</label>
                                          <div class="col-sm-10">
                                              <div class="form-line">
                                                  <input type="text" class="form-control" id="NIK" name="NIK" placeholder="NIK" value="<?php echo $b['no_ktp']; ?>" required>
                                              </div>
                                          </div>
                                    </div>
                                    <div class="form-group">
                                      	  <label for="jk" class="col-sm-2 control-label">Jenis Kelamin</label>
                                          <div class="col-sm-10">
                                              <div class="form-line">
                                                  <input class="with-gap" type="radio" name="jk" id="Pria" value="L" <?php if ($dataGet['1'] == 'L') { echo "checked='true'";}?>>
                                              	  <label for="Pria">
                                                      Pria
                                            		  </label>
                                            		  <input class="with-gap" type="radio" name="jk" id="Wanita" value="P" <?php if ($dataGet['1'] == 'P') { echo "checked='true'";}?>>
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
                                                <input type="text" class="form-control" id="tempat" name="tempat" placeholder="Tempat Lahir" value="<?php echo $b['tmp_lahir'];?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="tanggal" class="col-sm-2 control-label">Tanggal Lahir</label>
                                        <div class="col-sm-10">
                                            <div class="form-line">
                                                <input type="text" class="datepicker form-control" id="tanggal" name="tanggal" placeholder="Tanggal Lahir" value="<?php echo $b['tgl_lahir'];?>" required>
                                            </div>
                                        </div>
                                    </div>
                                  	<div class="form-group">
                                        <label for="pddkn" class="col-sm-2 control-label">Pendidikan</label>
                                        <div class="col-sm-10">
                                            <div class="form-line">
                                                <select name="pddkn" class="form-control">
                                                    <?php $c = query("SELECT * FROM pendidikan"); while($d = fetch_assoc($c)){?>
                                                    <option value="<?php echo $d['tingkat'];?>" <?php if($d['tingkat'] == $b['pendidikan']){ echo "selected";};?> ><?php echo $d['tingkat'];?></option>
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
                                                    <option value="<?php echo $d['stts'];?>" <?php if($d['stts'] == $b['stts_wp']){ echo "selected";};?> ><?php echo $d['ktg'];?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                      	</div>
                                    </div>
                                  	<div class="form-group">
                                        <label for="npwp" class="col-sm-2 control-label">NPWP</label>
                                        <div class="col-sm-10">
                                            <div class="form-line">
                                                <input type="text" class="form-control" id="npwp" name="npwp" placeholder="NPWP" value="<?php echo $b['npwp'];?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="alamat" class="col-sm-2 control-label">Alamat</label>
                                        <div class="col-sm-10">
                                            <div class="form-line">
                                                <textarea class="form-control" id="alamat" name="alamat" rows="3" maxlength="60" placeholder="Alamat" value=""><?php echo $b['alamat'];?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-offset-2 col-sm-10">
                                            <input type="submit" name="bio" class="btn btn-danger" value="SIMPAN" />
                                        </div>
                                    </div>
                                </form>
                            </div>
                          	<div role="tabpanel" class="tab-pane fade in" id="work">
                                <form class="form-horizontal" method="post" action="">
                                  	<div class="form-group">
                                        <label for="jbtn" class="col-sm-2 control-label">Jabatan</label>
                                        <div class="col-sm-10">
                                            <div class="form-line">
                                                <input type="text" class="form-control" id="jbtn" name="jbtn" placeholder="Jabatan" value="<?php echo $b['jbtn'];?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="bdng" class="col-sm-2 control-label">Bidang</label>
                                        <div class="col-sm-10">
                                            <div class="form-line">
                                                <select name="bdng" class="form-control">
                                                    <?php $c = query("SELECT * FROM bidang"); while($d = fetch_assoc($c)){?>
                                                    <option value="<?php echo $d['nama'];?>" <?php if($d['nama'] == $b['bidang']){ echo "selected";};?> ><?php echo $d['nama'];?></option>
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
                                                    <option value="<?php echo $d['dep_id'];?>" <?php if($d['dep_id'] == $b['departemen']){ echo "selected";};?> ><?php echo $d['nama'];?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                      	</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="sk" class="col-sm-2 control-label">Status</label>
                                        <div class="col-sm-10">
                                            <div class="form-line">
                                                <input type="text" class="form-control" id="sk" name="sk" placeholder="Status Kerja" value="<?php if($b['stts_kerja'] == "FT") {echo "Kontrak";}else{echo $b['stts_kerja'];};?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="mker" class="col-sm-2 control-label">Mulai Kerja</label>
                                        <div class="col-sm-10">
                                            <div class="form-line">
                                                <input type="text" class="datepicker form-control" id="mker" name="mker" placeholder="Mulai Kerja" value="<?php echo $b['mulai_kerja'];?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="mkon" class="col-sm-2 control-label">Mulai Kontrak</label>
                                        <div class="col-sm-10">
                                            <div class="form-line">
                                                <input type="text" class="datepicker form-control" id="mkon" name="mkon" placeholder="Mulai Kontrak" value="<?php echo $b['mulai_kontrak'];?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="sak" class="col-sm-2 control-label">Status Keaktifan</label>
                                        <div class="col-sm-10">
                                            <div class="form-line">
                                                <input type="text" class="form-control" id="sak" name="sak" placeholder="Status Aktif" value="<?php echo $b['stts_aktif'];?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-offset-2 col-sm-10">
                                            <input type="submit" name="stskrja" class="btn btn-danger" value="SIMPAN" />
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div role="tabpanel" class="tab-pane fade in" id="change_password_settings">
                                <form class="form-horizontal" method="post" action="">
                                    <div class="form-group">
                                        <label for="OldPassword" class="col-sm-3 control-label">Password Lama</label>
                                        <div class="col-sm-9">
                                            <div class="form-line">
                                                <input type="password" class="form-control" id="OldPassword" name="OldPassword" placeholder="Old Password" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="NewPassword" class="col-sm-3 control-label">Password Baru</label>
                                        <div class="col-sm-9">
                                            <div class="form-line">
                                                <input type="password" class="form-control" id="NewPassword" name="NewPassword" placeholder="New Password" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="NewPasswordConfirm" class="col-sm-3 control-label">Password Baru(Konfirmasi)</label>
                                        <div class="col-sm-9">
                                            <div class="form-line">
                                                <input type="password" class="form-control" id="NewPasswordConfirm" name="NewPasswordConfirm" placeholder="New Password (Confirm)" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-offset-3 col-sm-9">
                                            <input type="submit" name="ganti_password" class="btn btn-danger" value="SUBMIT">
                                        </div>
                                    </div>
                                </form>
                            </div>
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
<script>
  $("input").maxlength();
</script>
