<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : dentix.id@gmail.com
* Licence under GPL
***/

$title = 'Pengaturan Aplikasi';
include_once('config.php');
include_once('layout/header.php');
include_once('layout/sidebar.php');

if($_SERVER['REQUEST_METHOD'] == "POST") {
  if(isset($_POST['fktl']) && $_POST['fktl'] == 'No') {
    file_put_contents('config.php', str_replace("\ndefine('FKTL', 'Yes')", "\ndefine('FKTL', 'No')", file_get_contents('config.php')));
  }
  if(isset($_POST['fktl']) && $_POST['fktl'] == 'Yes') {
    file_put_contents('config.php', str_replace("\ndefine('FKTL', 'No')", "\ndefine('FKTL', 'Yes')", file_get_contents('config.php')));
  }
  if(isset($_POST['kode_ppk']) && $_POST['kode_ppk'] !== '') {
    $kode_ppk = $dataSettings['kode_ppk'];
  } else {
    $kode_ppk = $_POST['kode_ppk'];
  }
  if($_FILES['file']['tmp_name']!='') {
    $logo = addslashes(file_get_contents($_FILES['file']['tmp_name']));
  } else {
    $logo = escape($dataSettings['logo']);
  }

  $update = query("UPDATE setting
      SET
          nama_instansi     = '{$_POST['nama_instansi']}',
          alamat_instansi   = '{$_POST['alamat_instansi']}',
          propinsi          = '{$_POST['propinsi']}',
          kabupaten         = '{$_POST['kabupaten']}',
          kontak            = '{$_POST['kontak']}',
          email             = '{$_POST['email']}',
          aktifkan          = 'Yes',
          kode_ppk          = '{$_POST['kode_ppk']}',
          kode_ppkinhealth  = '{$_POST['kode_ppkinhealth']}',
          kode_ppkkemenkes  = '{$_POST['kode_ppkkemenkes']}',
          logo              = '{$logo}'
      WHERE
          kode_ppk          = '{$kode_ppk}'
  ");
}

?>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                <?php echo $title; ?>
                            </h2>
                        </div>
                        <div class="body">
                            <form class="form-horizontal" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="nama" class="col-sm-2 control-label">Nama Instansi</label>
                                    <div class="col-sm-10">
                                        <div class="form-line">
                                            <input type="text" class="form-control" id="nama_instansi" name="nama_instansi" placeholder="Nama Instansi" value="<?php echo $dataSettings['nama_instansi']; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="alamat" class="col-sm-2 control-label">Alamat Instansi</label>
                                    <div class="col-sm-10">
                                        <div class="form-line">
                                            <textarea class="form-control" id="alamat_instansi" name="alamat_instansi" rows="3" maxlength="60" placeholder="Alamat"><?php echo $dataSettings['alamat_instansi'];?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                      <label for="NIP" class="col-sm-2 control-label">Kabupaten</label>
                                      <div class="col-sm-10">
                                          <div class="form-line">
                                              <input type="text" class="form-control" id="kabupaten" name="kabupaten" placeholder="Kabupaten" value="<?php echo $dataSettings['kabupaten']; ?>">
                                          </div>
                                      </div>
                                </div>
                                <div class="form-group">
                                      <label for="NIK" class="col-sm-2 control-label">Propinsi</label>
                                      <div class="col-sm-10">
                                          <div class="form-line">
                                              <input type="text" class="form-control" id="propinsi" name="propinsi" placeholder="Propinsi" value="<?php echo $dataSettings['propinsi']; ?>">
                                          </div>
                                      </div>
                                </div>
                                <div class="form-group">
                                    <label for="tempat" class="col-sm-2 control-label">Telepon</label>
                                    <div class="col-sm-10">
                                        <div class="form-line">
                                            <input type="text" class="form-control" id="kontak" name="kontak" placeholder="Nomor Telepon" value="<?php echo $dataSettings['kontak'];?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="tanggal" class="col-sm-2 control-label">e-Mail</label>
                                    <div class="col-sm-10">
                                        <div class="form-line">
                                            <input type="text" class="form-control" id="email" name="email" placeholder="e-Mail" value="<?php echo $dataSettings['email'];?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="npwp" class="col-sm-2 control-label">FKTP/FKTL</label>
                                    <div class="col-sm-10">
                                        <div class="form-line">
                                            <select name="fktl" class="form-control">
                                              <option value="No">FKTP</option>
                                              <option value="Yes">FKTL</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="npwp" class="col-sm-2 control-label">Kode PPK</label>
                                    <div class="col-sm-10">
                                        <div class="form-line">
                                            <input type="text" class="form-control" id="kode_ppk" name="kode_ppk" placeholder="Kode PPK" value="<?php echo $dataSettings['kode_ppk'];?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="npwp" class="col-sm-2 control-label">Kode PPK Inhealth</label>
                                    <div class="col-sm-10">
                                        <div class="form-line">
                                            <input type="text" class="form-control" id="kode_ppkinhealth" name="kode_ppkinhealth" placeholder="Kode PPK Inhealth" value="<?php echo $dataSettings['kode_ppkinhealth'];?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="npwp" class="col-sm-2 control-label">Kode PPK Kemenkes</label>
                                    <div class="col-sm-10">
                                        <div class="form-line">
                                            <input type="text" class="form-control" id="kode_ppkkemenkes" name="kode_ppkkemenkes" placeholder="Kode PPK Kemenkes" value="<?php echo $dataSettings['kode_ppkkemenkes'];?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="npwp" class="col-sm-2 control-label">Logo</label>
                                    <div class="col-sm-10">
                                        <div class="form-line">
                                          <?php if($dataSettings['logo'] !==''){
                                            echo '<img id="image_upload_preview" width="200px" src="data:image/jpeg;base64,'.base64_encode( $dataSettings['logo'] ).'" onclick="upload_berkas()" style="cursor:pointer;"/>';
                                          } else {
                                            echo '<img id="image_upload_preview" width="200px" src="'.URL.'/assets/images/yaski.png" onclick="upload_berkas()" style="cursor:pointer;" />';
                                          }
                                          ?>
                                          <br/>
                                          <input name="file" id="inputFile" type="file" style="display:none;"/>
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
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
include_once('layout/footer.php');
?>
