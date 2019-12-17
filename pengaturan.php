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

if (isset($_POST['setting'])) {
  if(isset($_POST['fktl']) && $_POST['fktl'] == 'false') {
    file_put_contents('config.php', str_replace("\ndefine('FKTL', true)", "\ndefine('FKTL', false)", file_get_contents('config.php')));
  }
  if(isset($_POST['fktl']) && $_POST['fktl'] == 'true') {
    file_put_contents('config.php', str_replace("\ndefine('FKTL', false)", "\ndefine('FKTL', true)", file_get_contents('config.php')));
  }
  if(isset($_POST['pwa']) && $_POST['pwa'] == 'false') {
    file_put_contents('config.php', str_replace("\ndefine('PWA', true)", "\ndefine('PWA', false)", file_get_contents('config.php')));
  }
  if(isset($_POST['pwa']) && $_POST['pwa'] == 'true') {
    file_put_contents('config.php', str_replace("\ndefine('PWA', false)", "\ndefine('PWA', true)", file_get_contents('config.php')));
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

  $nama_instansi = $_POST['nama_instansi'];
  $alamat_instansi = $_POST['alamat_instansi'];
  $kabupaten =	$_POST['kabupaten'];
  $propinsi =	$_POST['propinsi'];
  $kontak =	$_POST['kontak'];
  $email =	$_POST['email'];
  $kode_ppk =	$_POST['kode_ppk'];
  $kode_ppkinhealth =	$_POST['kode_ppkinhealth'];
  $kode_ppkkemenkes =	$_POST['kode_ppkkemenkes'];

  $update = query("UPDATE setting
      SET
          nama_instansi     = '{$nama_instansi}',
          alamat_instansi   = '{$alamat_instansi}',
          propinsi          = '{$propinsi}',
          kabupaten         = '{$kabupaten}',
          kontak            = '{$kontak}',
          email             = '{$email}',
          aktifkan          = 'Yes',
          kode_ppk          = '{$kode_ppk}',
          kode_ppkinhealth  = '{$kode_ppkinhealth}',
          kode_ppkkemenkes  = '{$kode_ppkkemenkes}',
          logo              = '{$logo}'
      WHERE
          kode_ppk          = '{$kode_ppk}'
  ");
  if($update) {
    $url = "https://khanza.basoro.id/lisensi.php?action=insert";
    $curlHandle = curl_init();
    curl_setopt($curlHandle, CURLOPT_URL, $url);
    curl_setopt($curlHandle, CURLOPT_POSTFIELDS,"nama_instansi=".$nama_instansi."&alamat_instansi=".$alamat_instansi."&kabupaten=".$kabupaten."&propinsi=".$propinsi."&kontak=".$kontak."&email=".$email."&kode_ppk=".$kode_ppk."&kode_ppkinhealth=".$kode_ppkinhealth."&kode_ppkkemenkes=".$kode_ppkkemenkes);
    curl_setopt($curlHandle, CURLOPT_HEADER, 0);
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
    curl_setopt($curlHandle, CURLOPT_POST, 1);
    curl_exec($curlHandle);
    curl_close($curlHandle);
  }
}

if (isset($_POST['lisensi'])) {
  $url = "https://khanza.basoro.id/lisensi.php?action=aktifkan";
  $curlHandle = curl_init();
  curl_setopt($curlHandle, CURLOPT_URL, $url);
  curl_setopt($curlHandle, CURLOPT_POSTFIELDS,"kode_lisensi=".$_POST[kode_lisensi]."&email=".$dataSettings['email']);
  curl_setopt($curlHandle, CURLOPT_HEADER, 0);
  curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
  curl_setopt($curlHandle, CURLOPT_POST, 1);
  curl_exec($curlHandle);
  curl_close($curlHandle);
}

if (isset($_POST['gratis'])) {
  $url = "https://khanza.basoro.id/lisensi.php?action=gratis";
  $curlHandle = curl_init();
  curl_setopt($curlHandle, CURLOPT_URL, $url);
  curl_setopt($curlHandle, CURLOPT_POSTFIELDS,"email=".$dataSettings['email']);
  curl_setopt($curlHandle, CURLOPT_HEADER, 0);
  curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
  curl_setopt($curlHandle, CURLOPT_POST, 1);
  curl_exec($curlHandle);
  curl_close($curlHandle);
}

if (isset($_POST['error'])) {
  $url = "https://khanza.basoro.id/lisensi.php?action=insert";
  $curlHandle = curl_init();
  curl_setopt($curlHandle, CURLOPT_URL, $url);
  curl_setopt($curlHandle, CURLOPT_POSTFIELDS,"nama_instansi=".$dataSettings['nama_instansi']."&alamat_instansi=".$dataSettings['alamat_instansi']."&kabupaten=".$dataSettings['kabupaten']."&propinsi=".$dataSettings['propinsi']."&kontak=".$dataSettings['kontak']."&email=".$dataSettings['email']."&kode_ppk=".$dataSettings['kode_ppk']."&kode_ppkinhealth=".$dataSettings['kode_ppkinhealth']."&kode_ppkkemenkes=".$dataSettings['kode_ppkkemenkes']);
  curl_setopt($curlHandle, CURLOPT_HEADER, 0);
  curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
  curl_setopt($curlHandle, CURLOPT_POST, 1);
  curl_exec($curlHandle);
  curl_close($curlHandle);
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
                                    <label for="fktl" class="col-sm-4 control-label">FKTP/FKTL</label>
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
                                    <label for="pwa" class="col-sm-4 control-label">PWA</label>
                                    <div class="col-sm-8">
                                        <div class="form-line">
                                            <select name="pwa" class="form-control">
                                              <option value="false" <?php if(PWA == false) { echo 'selected'; } ?>>Disable</option>
                                              <option value="true" <?php if(PWA == true) { echo 'selected'; } ?>>Enable</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-offset-2 col-sm-10">
                                        <input type="submit" name="setting" class="btn btn-danger" value="SIMPAN" />
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
                                Status Apikasi
                            </h2>
                        </div>
                        <?php
                          $data = json_decode(file_get_contents_curl('https://khanza.basoro.id/lisensi.php?action=cek&email='.$dataSettings['email']), true);
                        ?>
                        <div class="body">
                            <div class="text-center">
                              <p>Informasi tentang status aplikasi digunakan untuk kebutuhan pendataan pengguna Khanza Lite. Licensi yang dianut di perangkat lunak tetap mengacu ke <a href="https://en.wikipedia.org/wiki/Aladdin_Free_Public_License">Aladdin Free Public License</a>. Tidak ada biaya yang dikenakan atas penggunaan aplikasi ini.</p>
                              <?php if($data['status'] == "verified") { ?>
                              <?php if($data['kode_lisensi'] == md5($dataSettings['email'])) { ?>
                              <i class="material-icons text-success" style="font-size:150px;">child_care</i>
                              <p>
                                  <h1>TERDAFTAR
                                </h1>
                              </p>
                                <a href="#gratis-modal" data-toggle="modal" class="btn btn-primary">Hapus Dari Daftar</a>
                              <?php } else { ?>
                                <i class="material-icons text-primary" style="font-size:150px;">check_circle_outline</i>
                                <p>
                                  <h1>TIDAK TERDAFTAR
                                  </h1>
                                </p>
                                  <a href="#license-modal" data-toggle="modal" class="btn btn-primary">Masukkan Dalam Daftar</a>
                              <?php } ?>
                            <?php } else { ?>
                              <i class="material-icons text-danger" style="font-size:150px;">cancel</i>
                              <p>
                                <h1>ERROR
                                </h1>
                              </p>
                                <a href="#error-modal" data-toggle="modal" class="btn btn-primary">Perbaiki ERROR</a>
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
                                <dt>Status</dt>
                                <dd>
                                    <?php if($data['status'] == "verified") { if($data['kode_lisensi'] == md5($dataSettings['email'])) { echo 'TERDAFTAR <a href="#gratis-modal" data-toggle="modal" class="small">(Sunting)</a>'; } else { echo 'TIDAK TERDAFTAR <a href="#license-modal" data-toggle="modal" class="small">(Sunting)</a>'; } } else { echo 'ERROR <a href="#gratis-modal" data-toggle="modal" class="small">(Sunting)</a>'; } ?>
                                </dd>
                            </dl>
                            <hr />
                            <dl class="dl-horizontal no-margin">
                                <dt>Versi PHP</dt>
                                <dd><?php echo phpversion(); ?></dd>
                                <dt>Versi MySQL</dt>
                                <dd><?php printf("%s\n", mysqli_get_server_info($connection)); ?></dd>
                            </dl>
                            <hr />
                            <dl class="dl-horizontal no-margin">
                                <dt>Ukuran System</dt>
                                <dd><?php echo roundSize(foldersize(ABSPATH)); ?></dd>
                                <dt>Ukuran database</dt>
                                <?php $mysql_size = fetch_assoc(query("SELECT ROUND(SUM(data_length + index_length), 1) AS mysql_size FROM information_schema.tables WHERE table_schema = '".DB_NAME."' GROUP BY table_schema")); ?>
                                <dd><?php echo roundSize($mysql_size['mysql_size']); ?></dd>
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
                    <h4 class="modal-title">Aktifasi Pendataan</h4>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <p>Anda belum terdaftar dan belum tervalidasi dalam Daftar Pengguna Aplikasi Khanza Lite. Apabila anda ingin mendapatkan layakan teknis dan bantuan saat ada perubahan (update), mintalah <a href="https://khanza.basoro.id/lisensi.php?action=request" target="_blank"><b>kode validasi Pendaftaran</b></a>.</p>
                        <p>Untuk mengaktifkan kode validasi pendaftaran, silahkan ketik kode validasi pendaftaran dalam isian dibawah. Anda bisa melihat kode validasi pendaftaran pada email konfirmasi <a href="https://khanza.basoro.id/lisensi.php?action=request" target="_blank"><b>permintaan kode validasi</b></a>.</p>
                        <input type="text" name="kode_lisensi" class="form-control" placeholder="Kode Validasi....." />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" name="lisensi">Aktifkan Kode Validasi</button>
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
                    <h4 class="modal-title">Status Aplikasi</h4>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <p class="text-center">Anda akan menghapus data pengguna Khanza Lite dari daftar Status Pengguna Aplikasi. Apakah anda yakin?</p>
                        <div class="text-center" style="margin-top:40px;margin-bottom:40px;">
                          <button type="submit" class="btn btn-lg btn-danger" style="padding:20px;font-size:24px;" name="gratis">Hapus Dari Daftar Pengguna</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
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
                    <h4 class="modal-title">Perbaiki Kesalahan</h4>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <p class="text-center">Terjadi kesalahan pada konfigurasi anda. Apakah anda akan memperbaikinnya? Silahkan klik tombol dibawah!</p>
                        <div class="text-center" style="margin-top:40px;margin-bottom:40px;">
                          <button type="submit" class="btn btn-lg btn-primary" style="padding:20px;font-size:24px;" name="error">Perbaikin ERROR</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php
include_once('layout/footer.php');
?>
