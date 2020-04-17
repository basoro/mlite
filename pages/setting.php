<?php
if(!defined("INDEX")) header('location: ../index.php');
if(userroles('role')!="admin") header('location: index.php');

$show = isset($_GET['show']) ? $_GET['show'] : "";
$link = "?module=setting";
echo '<div class="block-header">';
echo '	<h2>';
echo '		PENGATURAN APLIKASI';
echo '		<small>Periode '.tgl_indonesia($date).'</small>';
echo '	</h2>';
echo '</div>';

switch($show){
	default:
		if (isset($_POST['setting'])) {

		  $nama_instansi = $_POST['nama_instansi'];
		  $alamat_instansi = $_POST['alamat_instansi'];
		  $kabupaten =	$_POST['kabupaten'];
		  $propinsi =	$_POST['propinsi'];
		  $kontak =	$_POST['kontak'];
		  $email =	$_POST['email'];
		  $kode_ppk =	$_POST['kode_ppk'];
		  $kode_ppkinhealth =	$_POST['kode_ppkinhealth'];
		  $kode_ppkkemenkes =	$_POST['kode_ppkkemenkes'];

		  $update = $mysqli->query("UPDATE setting
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
		<div class="row clearfix">
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
						<div class="card">
								<div class="header">
										<h2>
												Pengaturan Umum
										</h2>
								</div>
								<?php
								buka_form($link, "", "");
										buat_textbox_alt2("Nama Instansi", "nama_instansi", setting("nama_instansi"));
										buat_textarea_alt2("Alamat Instansi", "alamat_instansi", setting("alamat_instansi", "form-line"));
										buat_textbox_alt2("Kabupaten", "kabupaten", setting("kabupaten"));
										buat_textbox_alt2("Propinsi", "propinsi", setting("propinsi"));
										buat_textbox_alt2("Telepon", "kontak", setting("kontak"));
										buat_textbox_alt2("Email", "email", setting("email"));
										buat_textbox_alt2("Kode PPK", "kode_ppk", setting("kode_ppk"));
										buat_textbox_alt2("Kode Inhealth", "kode_ppkinhealth", setting("kode_ppkinhealth"));
										buat_textbox_alt2("Kode Kemkes", "kode_ppkkemenkes", setting("kode_ppkkemenkes"));
								tutup_form($link);
								?>
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
									$data = json_decode(file_get_contents_curl('https://khanza.basoro.id/lisensi.php?action=cek&email='.setting('email')), true);
									//print_r($data);
								?>
								<div class="body">
										<div class="text-center">
											<p>Informasi tentang status aplikasi digunakan untuk kebutuhan pendataan pengguna Khanza Lite. Licensi yang dianut di perangkat lunak tetap mengacu ke <a href="https://en.wikipedia.org/wiki/Aladdin_Free_Public_License">Aladdin Free Public License</a>. Tidak ada biaya yang dikenakan atas penggunaan aplikasi ini.</p>
											<?php if($data['status'] == "verified") { ?>
											<?php if($data['kode_lisensi'] == md5(setting('email'))) { ?>
											<i class="material-icons text-success" style="font-size:150px;">child_care</i>
											<p>
													<h1>TERDAFTAR
												</h1>
											</p>
												<a href="#gratis-modal" data-toggle="modal" class="btn btn-primary">Hapus Dari Daftar</a>
											<?php } else { ?>
												<i class="material-icons text-primary" style="font-size:150px;">check_circle</i>
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
														<?php if($data['status'] == "verified") { if($data['kode_lisensi'] == md5(setting('email'))) { echo 'TERDAFTAR <a href="#gratis-modal" data-toggle="modal" class="small">(Sunting)</a>'; } else { echo 'TIDAK TERDAFTAR <a href="#license-modal" data-toggle="modal" class="small">(Sunting)</a>'; } } else { echo 'ERROR <a href="#error-modal" data-toggle="modal" class="small">(Sunting)</a>'; } ?>
												</dd>
										</dl>
										<hr />
										<dl class="dl-horizontal no-margin">
												<dt>Versi PHP</dt>
												<dd><?php echo phpversion(); ?></dd>
												<dt>Versi MySQL</dt>
												<dd><?php printf("%s\n", mysqli_get_server_info($mysqli)); ?></dd>
										</dl>
										<hr />
										<dl class="dl-horizontal no-margin">
												<dt>Ukuran System</dt>
												<dd><?php echo roundSize(foldersize(ABSPATH)); ?></dd>
												<dt>Ukuran database</dt>
												<?php $mysql_size = $mysqli->query("SELECT ROUND(SUM(data_length + index_length), 1) AS mysql_size FROM information_schema.tables WHERE table_schema = '".DB_NAME."' GROUP BY table_schema")->fetch_assoc(); ?>
												<dd><?php echo roundSize($mysql_size['mysql_size']); ?></dd>
										</dl>
								</div>
						</div>

			 </div>
		</div>
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
	break;

	//Menyisipkan atau mengedit data di database
	case "action":
		$mysqli->query("UPDATE setting SET
			nama_instansi 	= '$_POST[nama_instansi]',
			alamat_instansi	 		= '$_POST[alamat_instansi]',
			kabupaten 	= '$_POST[kabupaten]',
			propinsi 	= '$_POST[propinsi]',
			kontak 	= '$_POST[kontak]',
			email 	= '$_POST[email]',
			kode_ppk 	= '$_POST[kode_ppk]',
			kode_ppkinhealth 	= '$_POST[kode_ppkinhealth]',
			kode_ppkkemenkes 	= '$_POST[kode_ppkkemenkes]'
		");
		header('location:'.$link);
	break;

}
?>
