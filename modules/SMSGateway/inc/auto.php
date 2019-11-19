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

<div class="card">
	<div class="header">
			<h2>Atur Auto Responder <a class="pull-right" href="<?php echo URL; ?>/?module=SMSGateway&page=auto&op=add">Tambah</a></h2>
	</div>
	<div class="body">

<?php
	if (!$op)
	{
	// menampilkan seluruh data message

	$query = "SELECT id, interv, msg, sms_group.group FROM sms_autoresponder, sms_group WHERE sms_group.idgroup = sms_autoresponder.idgroup
	          ORDER BY sms_autoresponder.idgroup, interv";
	$hasil = query($query);
	echo "<br>";
	echo '<table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">';
	echo "<tr><th>Interval (hari)</th><th>Message</th><th>Group</th><th>Atur</th></tr>";
	while ($data = fetch_array($hasil))
	{
	   $i++;
	   echo "<tr><td>".$data['interv']."</td><td>".$data['msg']."</td><td>".$data['group']."</td><td><a href='".URL."/?module=SMSGateway&page=auto&op=edit&id=".$data['id']."'>Edit</a> | <a href='".URL."/?module=SMSGateway&page=auto&op=hapus&id=".$data['id']."'>Hapus</a></td></tr>";
	}
	echo "</table>";
	}

	else if ($op == "update")
	{
	// proses update data
	?>
	<h3>Edit Message</h3>
	<?php

		$id = $_POST['id'];
		$msg = $_POST['msg'];
		$interval = $_POST['interval'];
		$group = $_POST['group'];

		$query = "UPDATE sms_autoresponder
		          SET msg = '$msg', interv = '$interval', idgroup = '$group'
				  WHERE id = '$id'";

		query($query);

		$query = "DELETE sms_autolist WHERE id = '$id'";
		query($query);

		$query = "SELECT * FROM sms_phonebook WHERE idgroup = '$group'";
	    $hasil = query($query);
	    while ($data  = fetch_array($hasil))
	    {
	       $notelp = $data['noTelp'];
	       $query2 = "INSERT INTO sms_autolist VALUES ('$notelp', '$id', '0')";
		   query($query2);
	    }

		echo "<p>&nbsp</p><p>Message sudah diupdate</p>";

	}

	if ($op == "add")
	{
	// proses tambah data message auto responder
	?>
	<h3>Tambah Message</h3>

	<form name="formku" method="post" action="<?php echo URL; ?>/?module=SMSGateway&page=auto&op=simpan">
	Message : <br /><br />
	<textarea name="msg" cols="60" rows="10"></textarea>
	<br><br>
	Interval (*) : <input type="text" name="interval" size="4"> Pilih Group :

	<select name="group">
	<?php
	$query = "SELECT * FROM sms_group";
	$hasil = query($query);
	while ($data = fetch_array($hasil))
	{
	  echo "<option value='".$data['idgroup']."'>".$data['group']."</option>";
	}
	?>
	</select>

	<input type="submit" name="submit" value="Simpan"><br><br>
	(*) Waktu pengiriman pesan dalam hari, dihitung mulai dari tanggal registrasi. Contohnya bila diisi 30, maka pesan akan dikirim secara otomatis pada hari ke-30 setelah registrasi.
	</form>


	<?php
	}

	if ($op == "simpan")
	{
	// proses penyimpanan data message autoresponder yang baru
	   $msg = $_POST['msg'];
	   $interval = $_POST['interval'];
	   $group = $_POST['group'];

	   $query = "INSERT INTO sms_autoresponder(msg, interv, idgroup) VALUES ('$msg', '$interval', '$group')";
	   $hasil = query($query);
	   if ($hasil) echo "<p>Data sudah disimpan</p>";
	   else echo "<p>Data gagal disimpan</p>";

	   $query = "SELECT max(id) as maks FROM sms_autoresponder";
	   $hasil = query($query);
	   $data  = fetch_array($hasil);
	   $idmax = $data['maks'];

	   $query = "SELECT * FROM sms_phonebook WHERE idgroup = '$group'";
	   $hasil = query($query);
	   while ($data  = fetch_array($hasil))
	   {
	       $notelp = $data['noTelp'];
	       $query2 = "INSERT INTO sms_autolist VALUES ('$notelp', '$idmax', '0')";
		   query($query2);
	   }

	}

	if ($op == "hapus")
	{
	// proses menghapus data message
	    $id = $_GET['id'];
		$query = "DELETE FROM sms_autoresponder WHERE id = '$id'";
		query($query);
		$query = "DELETE FROM sms_autolist WHERE id = '$id'";
		query($query);
		echo "<p>Data auto responder sudah dihapus</p>";
	}

	if ($op == "edit")
	{
	// proses edit data message
	    $id = $_GET['id'];
	    $query = "SELECT * FROM sms_autoresponder WHERE id = '$id'";
		$hasil = query($query);
		$data = fetch_array($hasil);
	?>

	<h3>Edit Message</h3>
	<p>&nbsp;</p>
	<form name="formku" method="post" action="<?php echo URL; ?>/?module=SMSGateway&page=auto&op=update">
	Message : <br>
	<textarea name="msg" cols="60" rows="10"><?php echo $data['msg']; ?></textarea>
	<br><br>
	Interval (*) : <input type="text" name="interval" size="4" value="<?php echo $data['interv'];?>"> Pilih Group : <select name="group">
	<?php
	$query2 = "SELECT * FROM sms_group";
	$hasil2 = query($query2);
	while ($data2 = fetch_array($hasil2))
	{
	  if ($data2['idgroup'] == $data['idgroup']) echo "<option value='".$data2['idgroup']."' selected>".$data2['group']."</option>";
	  else echo "<option value='".$data2['idgroup']."'>".$data2['group']."</option>";
	}
	?>
	</select>
	<br><br>
	(*) Waktu pengiriman pesan dalam hari, dihitung mulai dari tanggal registrasi. Contohnya bila diisi 30, maka pesan akan dikirim secara otomatis pada hari ke-30 setelah registrasi.<br><br>
	<input type="submit" name="submit" value="Submit">
	<input type="hidden" name="id" value="<?php echo $data['id'];?>">
	</form>


	<?php
	}
}
?>
