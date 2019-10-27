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
  if(isset($_POST['fktl']) && $_POST['fktl'] == 'false') {
    file_put_contents('config.php', str_replace("\ndefine('FKTL', true)", "\ndefine('FKTL', false)", file_get_contents('config.php')));
  }
  if(isset($_POST['fktl']) && $_POST['fktl'] == 'true') {
    file_put_contents('config.php', str_replace("\ndefine('FKTL', false)", "\ndefine('FKTL', true)", file_get_contents('config.php')));
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
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                <?php echo $title; ?>
                            </h2>
                        </div>
                        <div class="body">
                            <form class="form-horizontal" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="nama" class="col-sm-4 control-label">Nama Instansi</label>
                                    <div class="col-sm-8">
                                        <div class="form-line">
                                            <input type="text" class="form-control" id="nama_instansi" name="nama_instansi" placeholder="Nama Instansi" value="<?php echo $dataSettings['nama_instansi']; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="alamat" class="col-sm-4 control-label">Alamat Instansi</label>
                                    <div class="col-sm-8">
                                        <div class="form-line">
                                            <textarea class="form-control" id="alamat_instansi" name="alamat_instansi" rows="3" maxlength="60" placeholder="Alamat"><?php echo $dataSettings['alamat_instansi'];?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                      <label for="NIP" class="col-sm-4 control-label">Kabupaten</label>
                                      <div class="col-sm-8">
                                          <div class="form-line">
                                              <input type="text" class="form-control" id="kabupaten" name="kabupaten" placeholder="Kabupaten" value="<?php echo $dataSettings['kabupaten']; ?>">
                                          </div>
                                      </div>
                                </div>
                                <div class="form-group">
                                      <label for="NIK" class="col-sm-4 control-label">Propinsi</label>
                                      <div class="col-sm-8">
                                          <div class="form-line">
                                              <input type="text" class="form-control" id="propinsi" name="propinsi" placeholder="Propinsi" value="<?php echo $dataSettings['propinsi']; ?>">
                                          </div>
                                      </div>
                                </div>
                                <div class="form-group">
                                    <label for="tempat" class="col-sm-4 control-label">Telepon</label>
                                    <div class="col-sm-8">
                                        <div class="form-line">
                                            <input type="text" class="form-control" id="kontak" name="kontak" placeholder="Nomor Telepon" value="<?php echo $dataSettings['kontak'];?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="tanggal" class="col-sm-4 control-label">e-Mail</label>
                                    <div class="col-sm-8">
                                        <div class="form-line">
                                            <input type="text" class="form-control" id="email" name="email" placeholder="e-Mail" value="<?php echo $dataSettings['email'];?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="npwp" class="col-sm-4 control-label">FKTP/FKTL</label>
                                    <div class="col-sm-8">
                                        <div class="form-line">
                                            <select name="fktl" class="form-control">
                                              <option value="false" <?php if(FKTL == false) { echo 'selected'; } ?>>FKTP</option>
                                              <option value="true" <?php if(FKTL == true) { echo 'selected'; } ?>>FKTL</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="npwp" class="col-sm-4 control-label">Kode PPK</label>
                                    <div class="col-sm-8">
                                        <div class="form-line">
                                            <input type="text" class="form-control" id="kode_ppk" name="kode_ppk" placeholder="Kode PPK" value="<?php echo $dataSettings['kode_ppk'];?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="npwp" class="col-sm-4 control-label">Kode PPK Inhealth</label>
                                    <div class="col-sm-8">
                                        <div class="form-line">
                                            <input type="text" class="form-control" id="kode_ppkinhealth" name="kode_ppkinhealth" placeholder="Kode PPK Inhealth" value="<?php echo $dataSettings['kode_ppkinhealth'];?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="npwp" class="col-sm-4 control-label">Kode PPK Kemkes</label>
                                    <div class="col-sm-8">
                                        <div class="form-line">
                                            <input type="text" class="form-control" id="kode_ppkkemenkes" name="kode_ppkkemenkes" placeholder="Kode PPK Kemenkes" value="<?php echo $dataSettings['kode_ppkkemenkes'];?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="npwp" class="col-sm-4 control-label">Logo</label>
                                    <div class="col-sm-8">
                                        <div class="form">
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
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                LISENSI PAKAI
                            </h2>
                        </div>
                        <?php
                          $data = json_decode(file_get_contents('https://khanza.basoro.id/lisensi.php?action=cek&email='.$dataSettings['email']), true);
                        ?>
                        <div class="body">
                            <div class="text-center">
                              <?php if($data['status'] == "verified") { ?>
                              <?php if($data['kode_lisensi'] == md5($dataSettings['email'])) { ?>
                              <i class="material-icons text-success" style="font-size:150px;">check_circle_outline</i>
                              <p>
                                <h1>BERLISENSI
                                </h1>
                              </p>
                                <a href="#gratis-modal" data-toggle="modal" class="btn btn-primary">Turunkan ke lisensi Gratis</a>
                              <?php } else { ?>
                                <i class="material-icons text-success" style="font-size:150px;">check_circle_outline</i>
                                <p>
                                  <h1>GRATIS
                                  </h1>
                                </p>
                                  <a href="#license-modal" data-toggle="modal" class="btn btn-primary">Naikkan ke versi BERLISENSI</a>
                              <?php } ?>
                            <?php } else { ?>
                              <i class="material-icons text-success" style="font-size:150px;">check_circle_outline</i>
                              <p>
                                <h1>ERROR
                                </h1>
                              </p>
                                <a href="#error-modal" data-toggle="modal" class="btn btn-primary">Naikkan ke lisensi Gratis</a>
                            <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="header">
                            <h2>
                              Informasi sistem
                            </h2>
                        </div>
                        <div class="panel-body">
                            <dl class="dl-horizontal no-margin">
                                <dt>Versi</dt>
                                <dd><?php echo VERSION; ?></dd>
                                <dt>Lisensi</dt>
                                <dd>
                                    <?php if($data['status'] == "verified") { if($data['kode_lisensi'] == md5($dataSettings['email'])) { echo 'BERLISENSI <a href="#gratis-modal" data-toggle="modal" class="small">(Sunting)</a>'; } else { echo 'GRATIS <a href="#license-modal" data-toggle="modal" class="small">(Sunting)</a>'; } } else { echo 'ERROR <a href="#gratis-modal" data-toggle="modal" class="small">(Sunting)</a>'; } ?>
                                </dd>
                            </dl>
                            <hr />
                            <dl class="dl-horizontal no-margin">
                                <dt>Versi PHP</dt>
                                <dd>7.3.6</dd>
                                <dt>Versi MySQL</dt>
                                <dd>5.7.24</dd>
                            </dl>
                            <hr />
                            <dl class="dl-horizontal no-margin">
                                <dt>Ukuran System</dt>
                                <dd>1.54 MB</dd>
                                <dt>Ukuran database</dt>
                                <dd>3.83 GB</dd>
                            </dl>
                        </div>
                    </div>

               </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="license-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Lisensi</h4>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <p>Apabila anda ingin mendapatkan layakan teknis dan bantuan saat ada perubahan (update), pilihlah <a href="https://khanza.basoro.id/lisensi.php?action=request" target="_blank"><b>Versi BERLISENSI</b></a>.</p>
                        <p>Untuk mengaktifkan versi BERLISENSI, silahkan ketik kode lisensi. Anda bisa melihat kode lisensi pada email konfirmasi <a href="https://khanza.basoro.id/lisensi.php?action=request" target="_blank"><b>permintaan lisensi</b></a> SIMKES Khanza.</p>
                        <input type="text" name="license-key" class="form-control" placeholder="Kode lisensi (License Key)..." />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Naikkan ke versi BERLISENSI</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="gratis-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Lisensi</h4>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <p>Apabila anda ingin mendapatkan layakan teknis dan bantuan saat ada perubahan (update), pilihlah <a href="https://khanza.basoro.id/lisensi.php?action=request" target="_blank"><b>Versi BERLISENSI</b></a>.</p>
                        <p>Untuk mengaktifkan versi BERLISENSI, anda perlu mendapatkan kode lisensi. Anda bisa melakukan <a href="https://khanza.basoro.id/lisensi.php?action=request" target="_blank"><b>permintaan lisensi</b></a> SIMKES Khanza Lite tidak dipungut biaya.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Turunkan ke versi GRATIS</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="error-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Lisensi</h4>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <p>Apabila anda ingin mendapatkan layakan teknis dan bantuan saat ada perubahan (update), pilihlah <a href="https://khanza.basoro.id/lisensi.php?action=request" target="_blank"><b>Versi BERLISENSI</b></a>.</p>
                        <p>Untuk mengaktifkan versi BERLISENSI, anda perlu mendapatkan kode lisensi. Anda bisa melakukan <a href="https://khanza.basoro.id/lisensi.php?action=request" target="_blank"><b>permintaan lisensi</b></a> SIMKES Khanza Lite tidak dipungut biaya.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Naikkan ke versi GRATIS</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php
include_once('layout/footer.php');
?>
