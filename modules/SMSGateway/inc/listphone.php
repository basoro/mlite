<?php
include "send.php";
$op = isset($_GET['op'])?$_GET['op']:null;
?>
<?php
if(num_rows(query("SHOW TABLES LIKE 'sms_inbox'")) !== 1) {
	echo '<div style="min-height:70vh; min-width:70vw;">';
	echo '<div class="alert bg-pink alert-dismissible text-center" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);">';
	echo '<p class="lead">Belum terinstall Database SMS Gateway</p>';
	echo '<a href="'.URL.'/?module=SMSGateway&page=index&op=install" class="btn btn-lg btn-primary m-t-20">Install Sekarang</a>';
	echo '</div>';
	echo '</div>';
} else {
		if (!$op)
		{
?>
<div class="card">
	<div class="header">
			<h2>SMS Gateway</h2>
	</div>
	<div class="body">
		<ul class="nav nav-tabs tab-nav-right" role="tablist">
				<li role="presentation" class="active"><a href="<?php echo URL; ?>/?module=SMSGateway&page=listphone">Phonebook</a></li>
				<li role="presentation"><a href="<?php echo URL;?>/?module=SMSGateway&page=listphone&op=add">Tambah Phonebook</a></li>
		</ul>
		<div class="lead m-t-20 m-b-10">Fitur Utama:</div>
<?php
		$query = "SELECT * FROM sms_phonebook ORDER BY nama";
		$hasil = query($query);
		echo "<p>&nbsp;</p>";
		echo '<table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">';
		echo "<tr><th>Nama</th><th>Alamat</th><th>No. Telp</th><th>Group</th><th>Atur</th></tr>";
		while ($data = fetch_array($hasil))
		{
		   $idgroup = $data['idgroup'];
		   $query2 = "SELECT sms_group.group FROM sms_group WHERE idgroup = '$idgroup'";
		   $hasil2 = query($query2);
		   $data2  = fetch_array($hasil2);

		   echo "<tr><td>".$data['nama']."</td><td>".$data['alamat']."</td><td>".$data['noTelp']."</td><td>".$data2['group']."</td><td>&nbsp;&nbsp;<a href='".URL."/?module=SMSGateway&page=listphone&op=edit&id=".$data['noTelp']."'>Edit</a> &nbsp;&nbsp;|&nbsp;&nbsp;<a href='".URL."/?module=SMSGateway&page=listphone&op=hapus&id=".$data['noTelp']."'>Hapus</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='".URL."/?module=SMSGateway&page=listphone&op=instant&ph=".$data['noTelp']."'>Kirim SMS</a>&nbsp;&nbsp; </td></tr>";

		}
		echo "</table>";
		echo "</div>";
		echo "</div>";
		}
		else if ($op == "update")
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
		<div class="card">
			<div class="header">
					<h2>SMS Gateway</h2>
			</div>
			<div class="body">
				<ul class="nav nav-tabs tab-nav-right" role="tablist">
						<li role="presentation"><a href="<?php echo URL; ?>/?module=SMSGateway&page=listphone">Phonebook</a></li>
						<li role="presentation" class="active"><a href="<?php echo URL;?>/?module=SMSGateway&page=listphone&op=add">Tambah Phonebook</a></li>
				</ul>
				<div class="lead m-t-20 m-b-10">Tambah Phonebook</div>
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
			</div>
		</div>
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
		<div class="card">
			<div class="header">
					<h2>SMS Gateway</h2>
			</div>
			<div class="body">
				<ul class="nav nav-tabs tab-nav-right" role="tablist">
						<li role="presentation"><a href="<?php echo URL; ?>/?module=SMSGateway&page=listphone">Phonebook</a></li>
						<li role="presentation" class="active"><a href="<?php echo URL;?>/?module=SMSGateway&page=listphone&op=add">Tambah Phonebook</a></li>
				</ul>
				<div class="lead m-t-20 m-b-10">Edit Phonebook</div>
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
			</div>
		</div>

		<?php
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
?>
		</div>
</div>
		<!-- end #content -->
<?php
}
?>
