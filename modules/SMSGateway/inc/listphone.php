<?php
include "send.php";
$op = isset($_GET['op'])?$_GET['op']:null;
?>
<?php
if(num_rows(query("SHOW TABLES LIKE 'sms_inbox'")) !== 1) {
	echo '<div style="min-height:70vh; min-width:70vw;">';
	echo '<div class="alert bg-pink alert-dismissible text-center" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);">';
	echo '<p class="lead">Belum terinstall SMS Gateway</p>';
	echo '<a href="'.URL.'/?module=SMSGateway&page=index&op=install" class="btn btn-lg btn-primary m-t-20">Install Sekarang</a>';
	echo '</div>';
	echo '</div>';
} else {
?>

				<h2 class="title">Daftar Phonebook</h2>

				<ul>
					<li><a href="<?php echo URL;?>/?module=SMSGateway&page=listphone&op=add">Tambah Phonebook</a></li>
					<li><a href="<?php echo URL;?>/?module=SMSGateway&page=listphone&op=import">Import Phonebook (From Excel)</a></li>
					<li><a href="<?php echo URL; ?>/modules/SMSGateway/inc/export.php?op=phonebook">Export Phonebook (To Excel)</a></li>
				</ul>

				<div class="entry">
					<p>
<?php
if(num_rows(query("SHOW TABLES LIKE 'sms_inbox'")) !== 1) {
	echo '<p class="lead">Belum terinstall SMS Gateway</p>';
	echo '<a href="'.URL.'/?module=SMSGateway&page=index&op=install" class="btn btn-lg btn-primary">Install Sekarang</a>';
} else {

			if (!$op)
			{
			$query = "SELECT * FROM sms_phonebook ORDER BY nama";
			$hasil = query($query);
			echo "<p>&nbsp;</p>";
			echo "<table border='1'  width='100%'>";
			echo "<tr><th>Nama</th><th>Alamat</th><th>No. Telp</th><th>Group</th><th>Atur</th></tr>";
			while ($data = fetch_array($hasil))
			{
			   $idgroup = $data['idgroup'];
			   $query2 = "SELECT sms_group.group FROM sms_group WHERE idgroup = '$idgroup'";
			   $hasil2 = query($query2);
			   $data2  = fetch_array($hasil2);

			   echo "<tr><td>".$data['nama']."</td><td>".$data['alamat']."</td><td>".$data['noTelp']."</td><td>".$data2['group']."</td><td>&nbsp;&nbsp;<a href='".URL."/?module=SMSGateway&page=listphone&op=edit&id=".$data['noTelp']."'>Edit</a> &nbsp;&nbsp;|&nbsp;&nbsp;<a href='".URL."/?module=SMSGateway&page=listphone&op=hapus&id=".$data['noTelp']."'>Hapus</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='".URL."/?module=SMSGateway&page=listphone&op=instant&ph=".$data['noTelp']."'>Kirim SMS</a>&nbsp;&nbsp; </td></tr>";

			}
			echo "</table><br>";
			}
			else if ($op == "search")
			{
			   echo "<p>&nbsp;</p>";
			   $nama = $_POST['nama'];
			   $query = "SELECT * FROM sms_phonebook WHERE nama LIKE '%$nama%' ORDER BY nama";
			   $hasil = query($query);
			   echo "<table border='1' width='100%'>";
			   echo "<tr><th>No.</th><th>Nama</th><th>Alamat</th><th>No. Telp</th><th>Group</th><th>Atur</th></tr>";

			   while ($data = fetch_array($hasil))
			   {
			   $i++;
			   $idgroup = $data['idgroup'];
			   $query2 = "SELECT sms_group.group FROM sms_group WHERE idgroup = '$idgroup'";
			   $hasil2 = query($query2);
			   $data2  = fetch_array($hasil2);

			   echo "<tr><td>".$i."</td><td>".$data['nama']."</td><td>".$data['alamat']."</td><td>".$data['noTelp']."</td><td>".$data2['group']."</td><td>&nbsp;&nbsp;<a href='".URL."/?module=SMSGateway&page=listphone&op=edit&id=".$data['noTelp']."'>Edit</a> &nbsp;&nbsp;|&nbsp;&nbsp;<a href='".URL."/?module=SMSGateway&page=listphone&op=hapus&id=".$data['noTelp']."'>Hapus</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='".URL."/?module=SMSGateway&page=listphone&op=instant&ph=".$data['noTelp']."'>Kirim SMS</a>&nbsp;&nbsp; </td></tr>";
			   }
			   echo "</table>";
			   // link tambah data phonebook

			}
			else
			if ($op == "update")
			{
			// proses update data
			?>
			<p>&nbsp;</p>
			<h3>Edit Phonebook</h3>
			<p>&nbsp;</p>
			<?php

			    $notelplama = str_replace(" ","+", $_POST['notelplama']);
				$notelp = str_replace(" ","+", $_POST['notelp']);
				$nama = $_POST['nama'];
				$alamat = $_POST['alamat'];
				$group = $_POST['group'];
				$grouplama = $_POST['grouplama'];

				if ($group != $grouplama)
				{
				   $query = "DELETE FROM sms_autolist WHERE phoneNumber = '$notelp' OR phoneNumber = '$notelplama'";
				   query($query);
				   $query = "SELECT id FROM sms_autoresponder WHERE idgroup = '$group'";
			       $hasil = query($query);
			       while ($data = fetch_array($hasil))
			       {
			          $idpesan = $data['id'];
			          $query2 = "INSERT INTO sms_autolist VALUES ('$notelp', '$idpesan', '0')";
			          query($query2);
			       }
				}
				else
				{
				   	$query = "UPDATE sms_autolist SET phoneNumber = '$notelp' WHERE phoneNumber = '$notelplama'";
				    query($query);
				}

				$query = "UPDATE sms_phonebook SET nama = '$nama', noTelp = '$notelp', alamat = '$alamat', idgroup = '$group' WHERE noTelp = '$notelplama'";
				query($query);


				echo "<p>Phonebook sudah diupdate</p>";
				echo "<hr>";

			}
			else
			if ($op == "add")
			{
			// proses tambah data phonebook
			?>
			<p>&nbsp;</p>
			<h3>Tambah Phonebook</h3>
			<p>&nbsp;</p>
			<form name="formku" method="post" action="<?php echo URL;?>/?module=SMSGateway&page=listphone&op=simpan">

			<table border="0">
			<tr><td>Nama</td><td>:</td><td><input type="text" name="nama" size="50"></td></tr>
			<tr><td>Alamat</td><td>:</td><td><input type="text" name="alamat" size="50"></td></tr>
			<tr><td>No. Telp</td><td>:</td><td><input type="text" name="notelp" value="+62"></td></tr>
			<tr><td>Group</td><td>:</td><td>
			<select name="group">
			<?php
			$query = "SELECT * FROM sms_group";
			$hasil = query($query);
			while ($data = fetch_array($hasil))
			{
			  echo "<option value='".$data['idgroup']."'>".$data['group']."</option>";
			}
			?>
			</select></td></tr>
			</table>
			<br><br>
			Kirim SMS konfirmasi?
			<input type="radio" name="confirm" value="1"> Ya <input type="radio" name="confirm" value="0" checked> Tidak
			<br><br>
			<input type="submit" name="submit" value="Simpan">

			</form>

			<?php
			}
			else
			if ($op == "simpan")
			{
			   echo "<p>&nbsp;</p>";
			// proses penyimpanan data phonebook yang baru
			   $nama = $_POST['nama'];
			   $alamat = $_POST['alamat'];
			   $group = $_POST['group'];
			   $notelp = $_POST['notelp'];
			   $confirm = $_POST['confirm'];
			   $now = date("Y-m-d");

			   $query = "INSERT INTO sms_phonebook VALUES ('$notelp', '$nama', '$alamat', '$group', '$now')";
			   $hasil = query($query);
			   if ($hasil) echo "<p>Data sudah disimpan</p>";
			   else echo "<p>Data gagal disimpan</p>";

			   $query = "SELECT id FROM sms_autoresponder WHERE idgroup = '$group'";
			   $hasil = query($query);
			   while ($data = fetch_array($hasil))
			   {
			      $idpesan = $data['id'];
			      $query2 = "INSERT INTO sms_autolist VALUES ('$notelp', '$idpesan', '0')";
			      query($query2);
			   }

			   if ($confirm == 1)
			   {
			      include "config.php";

			      send($notelp, $msgREG);
			   }

			}
			else
			if ($op == "hapus")
			{
			// proses menghapus data phonebook
			    $id = str_replace(" ","+", $_GET['id']);
				$query = "DELETE FROM sms_phonebook WHERE noTelp = '$id'";
				query($query);

				$query = "DELETE FROM sms_autolist WHERE phoneNumber = '$id'";
				query($query);
				echo "<p>&nbsp;</p><p>Data phonebook sudah dihapus</p>";
			}
			else
			if ($op == "edit")
			{
			// proses edit data phonebook
			    $id = str_replace(" ","+", $_GET['id']);
			    $query = "SELECT * FROM sms_phonebook WHERE noTelp = '$id'";
				$hasil = query($query);
				$data = fetch_array($hasil);
			?>

			<p>&nbsp;</p>
			<h3>Edit Phonebook</h3>
			<p>&nbsp;</p>
			<form name="formku" method="post" action="<?php echo URL;?>/?module=SMSGateway&page=listphone&op=update">
			<table border="0">
			<tr><td>Nama</td><td>:</td><td><input type="text" name="nama" value="<?php echo $data['nama']; ?>" size="50"></td></tr>
			<tr><td>Alamat</td><td>:</td><td><input type="text" name="alamat" value="<?php echo $data['alamat'];?>" size="50"></td></tr>
			<tr><td>No. Telp</td><td>:</td><td><input type="text" name="notelp" value="<?php echo $data['noTelp']?>"></td></tr>
			<tr><td>Group</td><td>:</td>
			<td><select name="group">
			<?php
			$query2 = "SELECT * FROM sms_group";
			$hasil2 = query($query2);
			while ($data2 = fetch_array($hasil2))
			{
			  if ($data2['idgroup'] == $data['idgroup']) echo "<option value='".$data2['idgroup']."' selected>".$data2['group']."</option>";
			  else echo "<option value='".$data2['idgroup']."'>".$data2['group']."</option>";
			}
			?>
			</select></td></tr>
			</table>
			<p>&nbsp;</p>
			<input type="submit" name="submit" value="Simpan">
			<input type="hidden" name="notelplama" value="<?php echo $data['noTelp'];?>">
			<input type="hidden" name="grouplama" value="<?php echo $data['idgroup'];?>">
			</form>

			<?php
			}
			else if ($op == "import")
			{
			?>
			<p>&nbsp;</p>
			<h2>Import Phone Book</h1>
			<p>&nbsp;</p>
			<form method="post" enctype="multipart/form-data" action="listphone.php?op=proses">
			Pilih file
			<input type="hidden" name="MAX_FILE_SIZE" value="20000000">
			<input name="userfile" type="file">
			<input name="upload" type="submit" value="Import"></td>
			</form>

			<?php
			}
			else if ($op == "proses")
			{

			error_reporting(E_ALL ^ E_NOTICE);
			require_once 'excel_reader2.php';

			// koneksi ke mysql
			include 'koneksi.php';

			// membaca file excel yang diimport
			$data = new Spreadsheet_Excel_Reader($_FILES['userfile']['tmp_name']);

			// membaca jumlah baros dari data excel
			$baris = $data->rowcount($sheet_index=0);

			// inisial counter untuk jumlah data yang sukses dan yang gagal diimport
			$countsukses = 0;
			$countgagal = 0;

			// import data excel mulai baris ke-2 (karena baris pertama adalah nama kolom)
			for ($i=2; $i<=$baris; $i++)
			{
			  // membaca data nama
			  $nama = $data->val($i, 1);

			  // membaca data no. telp
			  $telp = $data->val($i, 2);

			  // membaca data alamat
			  $alamat = $data->val($i, 3);

			  // membaca data group
			  $group = $data->val($i, 4);

			  // membaca data tanggal join
			  $tanggal = $data->val($i, 5);

			  // insert data nama dan telp ke tabel sms_phonebook
			  $query = "INSERT INTO sms_phonebook VALUES('$telp', '$nama', '$alamat', '$group', '$tanggal')";
			  $hasil = query($query);

			  $query = "SELECT id FROM sms_autoresponder WHERE idgroup = '$group'";
			  $hasil = query($query);
			  while ($dataku = fetch_array($hasil))
			  {
			      $idpesan = $dataku['id'];
			      $query2 = "INSERT INTO sms_autolist VALUES ('$telp', '$idpesan', '0')";
			      query($query2);
			  }

			  // menghitung jumlah data sukses dan gagal ketika import
			  if ($hasil) $countsukses++;
			  else $countgagal++;
			}

			// menampilkan jumlah data yang sukses dan gagal ketika import
			echo "<p>&nbsp</p>";
			echo "<h3>Proses import data selesai.</h3>";
			echo "<p>&nbsp</p>";
			echo "<p>Jumlah data yang sukses diimport : ".$countsukses."<br>";
			echo "Jumlah data yang gagal diimport : ".$countgagal."</p>";

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
			  $query = "SELECT nama FROM sms_phonebook WHERE noTelp = '$ph'";
			  $hasil = query($query);
			  $data  = fetch_array($hasil);
			  $nama = $data['nama'];
			  echo "<br><p><b>Nomor Tujuan :</b> ".$nama." (".$ph.")</p>";
			?>
			<form name="formku" method="post" action="<?php echo URL;?>/?module=SMSGateway&page=listphone&op=send">
			<input type="hidden" name="phone" value="<?php echo $ph; ?>">
			Message : <br>
			<textarea name="pesan" rows="10" cols="50"></textarea>
			<br>
			Keterangan: Berikan string [nama] bila ingin menampilkan nama si penerima SMS pada pesan di atas.
			<br><br>
			<input type="submit" value="KIRIM SMS">
			</form>
			<?php
			}
}
					?></p>
				</div>
			</div>
			</div>
			</div>
		</div>
		<!-- end #content -->
<?php } ?>
