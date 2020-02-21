<?php
include "send.php";
$op = isset($_GET['op'])?$_GET['op']:null;
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
		<div class="card">
			<div class="header">
					<h2>SMS Phonebook</h2>
			</div>
			<div class="body">
		<?php
		if (!$op)
		{
		$query = "SELECT pegawai.nama, pegawai.alamat, pegawai.jbtn, petugas.no_telp FROM pegawai, petugas WHERE pegawai.nik = petugas.nip ORDER BY pegawai.nama";
		$hasil = query($query);
		echo '<table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">';
		echo "<thead><tr><th>Nama</th><th>Alamat</th><th>No. Telp</th><th>Group</th><th>Atur</th></tr></thead><tbody>";
		while ($data = fetch_array($hasil))
		{
		   echo "<tr><td>".$data['nama']."</td><td>".$data['alamat']."</td><td>".$data['no_telp']."</td><td>".$data['jbtn']."</td><td><a href='".URL."/index.php?module=SMSGateway&page=listphone&op=instant&ph=".$data['no_telp']."'>Kirim SMS</a>&nbsp;&nbsp; </td></tr>";

		}
		echo "</tbody></table>";
		}
		else if ($op == "send")
		{
		  $ph = $_POST['phone'];
		  $sms = $_POST['pesan'];
		  send($ph, $sms);
		  echo "<br><p>Pesan SMS sudah dikirim....</p>";
		}
		else
		if ($op == "instant")
		{
		  $ph = str_replace(" ", "+", $_GET['ph']);
		  $query = "SELECT nama FROM petugas WHERE no_telp = '$ph'";
		  $hasil = query($query);
		  $data  = fetch_array($hasil);
		  $nama = $data['nama'];
		  echo "<br><p><b>Nomor Tujuan :</b> ".$nama." (".$ph.")</p>";
		?>
		<form name="formku" method="post" action="<?php echo URL;?>/index.php?module=SMSGateway&page=listphone&op=send">
		<input type="hidden" name="phone" value="<?php echo $ph; ?>">
		Message : <br>
		<textarea name="pesan" cols="30" rows="5" class="form-control no-resize"></textarea>
		<br>
		Keterangan: Berikan string [nama] bila ingin menampilkan nama si penerima SMS pada pesan di atas.
		<br><br>
		<input type="submit" class="btn btn-primary m-t-15 waves-effect" value="KIRIM SMS">
		</form>
		<?php
		}
		else
		if ($op == "instant_pasien")
		{
		  $ph = str_replace(" ", "+", $_GET['ph']);
		  $query = "SELECT nm_pasien AS nama FROM pasien WHERE no_tlp = '$ph'";
		  $hasil = query($query);
		  $data  = fetch_array($hasil);
		  $nama = $data['nama'];
		  echo "<br><p><b>Nomor Tujuan :</b> ".$nama." (".$ph.")</p>";
		?>
		<form name="formku" method="post" action="<?php echo URL;?>/index.php?module=SMSGateway&page=listphone&op=send">
		<input type="hidden" name="phone" value="<?php echo $ph; ?>">
		Message : <br>
		<textarea name="pesan" cols="30" rows="5" class="form-control no-resize"></textarea>
		<br>
		Keterangan: Berikan string [nama] bila ingin menampilkan nama si penerima SMS pada pesan di atas.
		<br><br>
		<input type="submit" class="btn btn-primary m-t-15 waves-effect" value="KIRIM SMS">
		</form>
		<?php
		}
		echo "</div>";
		echo "</div>";

?>
		</div>
</div>
		<!-- end #content -->
<?php
}
?>
