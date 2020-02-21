<?php
include "send.php";
$op = isset($_GET['op'])?$_GET['op']:null;
$action = isset($_GET['action'])?$_GET['action']:null;
?>
<?php
if(num_rows(query("SHOW TABLES LIKE 'sms_inbox'")) !== 1) {
	echo '<div style="min-height:70vh; min-width:70vw;">';
	echo '<div class="alert bg-pink alert-dismissible text-center" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);">';
	echo '<p class="lead">Belum terinstall Database SMS Gateway</p>';
	echo '<a href="'.URL.'/index.php?module=SMSGateway&page=index&op=install" class="btn btn-lg btn-primary m-t-20">Install Sekarang</a>';
	echo '</div>';
	echo '</div>';
} else {
?>

<?php
		if (!$op)
		{
		?>
		<div class="card">
		    <div class="header">
		        <h2>
		            SMS Instant
		        </h2>
		    </div>
				<div class="body">
				  <form name="formku" method="POST" action="<?php echo URL; ?>/index.php?module=SMSGateway&page=sendsms&op=send">
				      <div class="form-group form-float">
				          <div class="form-line">
				              <textarea name="pesan" cols="30" rows="5" class="form-control no-resize" required></textarea>
				              <label class="form-label">Pesan</label>
				          </div>
				      </div>
				      <div class="form-group form-float">
				              <p><b>Keterangan:</b> <br>Berikan string [nama] bila ingin menampilkan nama si penerima SMS pada pesan di atas. <br>Contoh: "Hallo [nama], apa kabar?"</p>
				      </div>

							<div class="form-group">

				          <input type="radio" name="kirim" value="pasien" id="pasien" class="with-gap">
				          <label for="pasien">Kirim ke Pasien</label><br>
				          <select class="form-control show-tick pasiennotlp" name="nohp">
				          </select>
				      </div>

							<div class="form-group">
				          <input type="radio" name="kirim" value="group_pasien" id="group_pasien" class="with-gap">
				          <label for="group_pasien">Kirim ke Pasien Poliklinik</label>
				          <select class="form-control show-tick selectpicker" name="group_pasien" data-live-search="true" data-size="3">
				          <option value="">Pilih Poli</option>
				          <?php
				          $query = "SELECT * FROM poliklinik";
				          $hasil = query($query);
				          while ($data = fetch_array($hasil))
				          {
				            echo "<option value='".$data['kd_poli']."'>".$data['nm_poli']."</option>";
				          }
				          ?>
				          </select>
									<input type="text" name="tgl_registrasi" class="form-control form-line datepicker" placeholder="Tanggal Kunjungan">
				      </div>

				      <div class="form-group">

				          <input type="radio" name="kirim" value="single" id="single" class="with-gap">
				          <label for="single">Kirim ke Pegawai</label>
				          <select class="form-control show-tick pegawainotlp" name='nohp'>
				          </select>
				      </div>

							<div class="form-group">
				          <input type="radio" name="kirim" value="jabatan" id="jabatan" class="with-gap">
				          <label for="jabatan">Kirim ke Group Jabatan</label>
				          <select class="form-control show-tick selectpicker" name="group" data-live-search="true" data-size="3">
				          <option value="0">Semua Jabatan</option>
				          <?php
				          $query = "SELECT * FROM jabatan";
				          $hasil = query($query);
				          while ($data = fetch_array($hasil))
				          {
				            echo "<option value='".$data['kd_jbtn']."'>".$data['nm_jbtn']."</option>";
				          }
				          ?>
				          </select>
				      </div>

				      <div class="form-group">
				      <input type="submit" name="submit" class="btn btn-primary m-t-15 waves-effect" value="Kirim SMS">
				      </div>
				  </form>
				</div>
		</div>
		<?php
		}
		if ($op == "send")
		{
		   // jika pengirimannya berdasarkan group jabatan
		   if ($_POST['kirim'] == "jabatan")
		   {
		   // membaca group
		   $group = $_POST['group'];
		   // membaca pesan yang akan dikirim dari form
		   $pesan = $_POST['pesan'];

		   // membaca  no. telp dari phonebook berdasarkan group

		   if ($group == 0) $query = "SELECT no_telp FROM petugas";
		   else if ($group > 0) $query = "SELECT petugas.no_telp FROM petugas, pegawai WHERE petugas.nik = pegawai.nip  AND pegawai.jbtn = '$group'";

		   $hasil = query($query);

		   while ($data = fetch_array($hasil))
			   {
			      // proses pengiriman pesan SMS ke semua no. telp
			      $notelp = $data['no_telp'];

			      send($notelp, $pesan);
			   }
		   }
		   // jika pengirimannya berdasarkan single
		   else if ($_POST['kirim'] == "single")
		   {

		   // membaca no hp dari single
		   $notelp = $_POST['nohp'];
		   // membaca pesan yang akan dikirim dari form
		   $pesan = $_POST['pesan'];

		   send($notelp, $pesan);
		   }
			 // jika pengirimannya berdasarkan group pasien periksa
		   else if ($_POST['kirim'] == "group_pasien")
		   {
			 // membaca group pasien berdasarkan poli
		   $group = $_POST['group_pasien'];
		   // membaca tanggal periksa
		   $tgl_registrasi = $_POST['tgl_registrasi'];
		   // membaca pesan yang akan dikirim dari form
		   $pesan = $_POST['pesan'];

		   // membaca  no. telp dari phonebook berdasarkan group

		 	 $query = "SELECT pasien.no_tlp AS no_telp FROM pasien, reg_periksa WHERE pasien.no_rkm_medis = reg_periksa.no_rkm_medis AND reg_periksa.tgl_registrasi = '{$tgl_registrasi}' AND reg_periksa.kd_poli = '{$group}'";

		   $hasil = query($query);

		   while ($data = fetch_array($hasil))
			   {
			      // proses pengiriman pesan SMS ke semua no. telp
			      $notelp = $data['no_telp'];

			      send($notelp, $pesan);
			   }
		   }
			 // jika pengirimannya berdasarkan single
		   else if ($_POST['kirim'] == "pasien")
		   {

		   // membaca no hp dari single
		   $notelp = $_POST['nohp'];
		   // membaca pesan yang akan dikirim dari form
		   $pesan = $_POST['pesan'];

		   send($notelp, $pesan);
		   }
?>
		<div class="card">
				<div class="header">
						<h2>
								SMS Instant
						</h2>
				</div>
				<div class="body">
					SMS sudah dikirim....
				</div>
		</div>
<?php
		}
}
?>
