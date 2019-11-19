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
			<h2>On Scheduled SMS <a class="pull-right" href="<?php echo URL; ?>/?module=SMSGateway&page=listmsg&op=add">Tambah</a></h2>
	</div>
	<div class="body">

<?php
		if (!$op)
		{
		// menampilkan semua daftar on scheduled message yang belum terkirim (status = 0)
		$query = "SELECT * FROM sms_message ORDER BY id";
		$hasil = query($query);
		echo "<br>";
		echo '<table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">';
		echo "<tr><th>Message</th><th>Group</th><th>Published Date</th><th>Atur</th></tr>";
		while ($data = fetch_array($hasil))
		{
		   $idgroup = $data['idgroup'];
		   if ($idgroup > 0)
		   {
		   $query2 = "SELECT `group` FROM sms_group WHERE idgroup = '$idgroup'";
		   $hasil2 = query($query2);
		   $data2  = fetch_array($hasil2);
		   $namagroup = $data2['group'];
		   }
		   else $namagroup = "All";

		   echo "<tr><td>".$data['message']."</td><td>".$namagroup."</td><td>".$data['pubdate']."</td><td><a href='".URL."/?module=SMSGateway&page=listmsg&op=edit&id=".$data['id']."'>Edit</a> | <a href='".URL."/?module=SMSGateway&page=listmsg&op=hapus&id=".$data['id']."'>Hapus</a></td></tr>";
		}
		echo "</table>";
		}
		else if ($op == "update")
		{
		// proses update message on schedule
		?>
		<h3>Edit Message</h3>
		<?php

		    $id = $_POST['id'];
			$msg = $_POST['pesan'];
			$pubdate = $_POST['pubdate'];
			$group = $_POST['group'];

			$query = "UPDATE sms_message SET message = '$msg', pubdate = '$pubdate', idgroup = '$group' WHERE id = '$id'";
			query($query);
			echo "<p>&nbsp;</p><p>Message sudah diupdate</p>";


		}

		if ($op == "add")
		{
		// proses tambah on scheduled message
		?>
		<h3>Tambah Message</h3>
		<p>&nbsp;</p>
		<form name="formku" method="post" action="<?php echo URL;?>/?module=SMSGateway&page=listmsg&op=simpan">
		Message : <br>
		<textarea name="pesan" rows="10" cols="50"></textarea>
		<br>
		Keterangan: Berikan string [nama] bila ingin menampilkan nama si penerima SMS pada pesan di atas.
		<br><br>
		Pilih Group :
		<select name='group'>
		<option value="0" selected>All</option>
		<?php
		$query = "SELECT * FROM sms_group";
		$hasil = query($query);
		while ($data = fetch_array($hasil))
		{
		  echo "<option value='".$data['idgroup']."'>".$data['group']."</option>";
		}
		?>
		</select><br>
		<br>
		Published Date Time (YYYY-MM-DD&lt;spasi&gt;hh:mm, contoh: 2010-05-27 21:30) : <br>
		<input type="text" name="pubdate" value="<?php echo $data['pubdate'];?>">
		<br><br>
		<input type="submit" name="submit" value="Simpan Message">
		</form>


		<?php
		}

		if ($op == "simpan")
		{
		// proses penyimpanan on scheduled message baru
		   $pesan = $_POST['pesan'];
		   $group = $_POST['group'];
		   $pubdate = $_POST['pubdate'];
		   $query = "INSERT INTO sms_message(message, pubdate, idgroup) VALUES ('$pesan', '$pubdate', '$group')";
		   $hasil = query($query);
		   if ($hasil) echo "<p>Message sudah disimpan</p>";
		   else echo "<p>Message gagal disimpan</p>";
		}

		if ($op == "hapus")
		{
		// proses menghapus on scheduled message
		    $id = $_GET['id'];
			$query = "DELETE FROM sms_message WHERE id = $id";
			query($query);
			echo "<p>Message sudah dihapus</p>";
		}

		if ($op == "edit")
		{
		// proses edit on scheduled message
		    $id = $_GET['id'];
		    $query = "SELECT * FROM sms_message WHERE id = $id";
			$hasil = query($query);
			$data = fetch_array($hasil);
		?>

		<h3>Edit Message</h3>
		<p>&nbsp;</p>
		<form name="formku" method="post" action="<?php echo URL;?>/?module=SMSGateway&page=listmsg&op=update">
		Message : <br>
		<textarea name="pesan" rows="10" cols="50"><?php echo $data['message']; ?></textarea><br><br>
		Published Date (yyyy-mm-dd): <br>
		<input type="text" name="pubdate" value="<?php echo $data['pubdate'];?>"><br><br>
		Pilih Group :
		<select name='group'>
		<?php
		  if ($data['idgroup'] == 0) echo "<option value='0' selected>All</option>";
		  else echo "<option value='0'>All</option>";
		?>

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

		<input type="hidden" name="id" value="<?php echo $data['id'];?>"> <br><br><input type="submit" name="submit" value="Update Message">
		</form>

		<?php
		}
}
?>
