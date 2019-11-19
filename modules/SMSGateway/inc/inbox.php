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
			<h2>SMS Inbox <a class="pull-right" href="<?php echo URL; ?>/modules/SMSGateway/inc/export.php?op=inbox">Export</a></h2>
	</div>
	<div class="body">
<?php
		if (!$op) {
		?>
								<table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">
									<thead>
										<tr>
											<th>Isi SMS</th>
											<th>Status Reply</th>
											<th>Pengirim</th>
											<th>Waktu SMS</th>
											<th>Action</th>
										</tr>
									</thead>
									<tbody>
									<?php
									$query = "SELECT * FROM sms_inbox ORDER BY time DESC";
									$hasil = query($query);
									while ($data = fetch_array($hasil))
									{
									  $pesan = substr($data['msg'], 0, 50);

									  $nosender = $data['sender'];
									  $query3 = "SELECT nama FROM sms_phonebook WHERE noTelp = '$nosender'";
									  $hasil3 = query($query3);
									  $data3  = fetch_array($hasil3);

									  if ($data3['nama'] == "") $sendername = $data['sender'];
									  else $sendername = $data3['nama'];

									  if ($data['flagRead'] == 0) $color = "yellow";
									  else $color = "white";

									  if ($data['flagReply'] == 0) $status = "&nbsp;";
									  else $status = "<b>[R]</b>";

									  echo "<tr bgcolor='".$color."'><td><a href='".URL."/?module=SMSGateway&page=inbox&op=view&id=".$data['id']."'>".$pesan."...</a></td><td align='center'>".$status."</td><td>".$sendername."</td><td>".$data['time']."</td><td><a href='".str_replace("&id=".$id, "", $_SERVER['REQUEST_URI'])."&id=".$data['id']."'>Hapus</a></td></tr>";
									}
									?>
									</tbody>
								</table>
		<?php
		}
		else if ($op == "view")
		{
		   $id = $_GET['id'];
		   $query = "SELECT * FROM sms_inbox WHERE id = '$id'";
		   $hasil = query($query);
		   $data = fetch_array($hasil);
		   echo "<p><b>No. Pengirim:</b> ".$data['sender']."</p>";
		   echo "<p><b>Waktu Pengiriman:</b> ".$data['time']."</p>";
		   echo "<p><b>Message:</b></p>";
		   echo "<p>".$data['msg']."</p>";
		   echo "<p>[<b><a href='".URL."/?module=SMSGateway&page=inbox&op=view&act=reply&id=".$data['id']."'>REPLY SMS</a></b>] [<b><a href='".URL."/?module=SMSGateway&page=inbox&op=view&act=forward&id=".$data['id']."'>FORWARD SMS</a></b>]</p>";
		   $query = "UPDATE sms_inbox SET flagRead = '1' WHERE id = '$id'";
		   query($query);

		   $noSender = $data['sender'];


		   if ($_GET['act'] == "forward")
		   {
		   $id = $_GET['id'];
		   $query = "SELECT * FROM sms_inbox WHERE id = '$id'";
		   $hasil = query($query);
		   $data = fetch_array($hasil);
		?>
		<form name="formku" method="post" action="<?php echo URL;?>/?module=SMSGateway&page=inbox&op=send">
		Message : <br>
		<textarea name="pesan" rows="12" cols="50"><?php echo $data['msg']; ?></textarea>
		<br><br>
		Forward ke :
		<select name="sender">
		<?php
		$query = "SELECT * FROM sms_phonebook";
		$hasil = query($query);
		while ($data = fetch_array($hasil))
		{
		  echo "<option value='".$data['noTelp']."'>".$data['nama']."</option>";
		}
		?>
		</select><br>
		<br>
		<input type="submit" name="submit" value="Send SMS">
		</form>
		<?php
		   }
		   else
		   if ($_GET['act'] == "reply")
		   {
		   $id = $_GET['id'];
		?>
		<form name="formku" method="post" action="<?php echo URL;?>/?module=SMSGateway&page=inbox&op=send">
		Message : <br>
		<textarea name="pesan" rows="12" cols="50"></textarea>
		<br>
		<br>
		<input type="hidden" name="sender" value="<?php echo $data['sender']; ?>">
		<input type="hidden" name="id" value="<?php echo $id; ?>">
		<input type="submit" name="submit" value="Send SMS">
		</form>

		<?php
		   }
		   else
		   {
		   ?>
		   <hr>
		   <p><b>History SMS</b></p>
		   <table class="table table-bordered table-striped table-hover display nowrap">
				 <thead>
				   	<tr>
							<th>Isi SMS</th>
							<th>Pengirim</th>
							<th>Waktu SMS</th>
						</tr>
					</thead>
					<tbody>
			    <?php
					   $query = "SELECT * FROM sms_inbox WHERE sender = '$noSender' AND id <> '$id' ORDER BY time DESC";
						 $hasil = query($query);
						 while ($data = fetch_array($hasil))
						 {
						   echo "<tr>";
							 echo "  <td>".$data['msg']."</td>";
							 echo "  <td>".$data['sender']."</td>";
							 echo "  <td>".$data['time']."</td>";
							 echo "</tr>";
						 }
				  ?>
		 			</tbody>
		   </table>
		   <?php
		   }
		}
		else if ($op == "forward")
		{
		   $pesan = $_POST['pesan'];
		   $notelp = $_POST['sender'];

		   send($notelp, $pesan);

		   echo "<p>SMS sudah dikirim</p>";
		}
		else if ($op == "send")
		{
		   $pesan = $_POST['pesan'];
		   $id = $_POST['id'];
		   $notelp = $_POST['sender'];

		   $query = "UPDATE sms_inbox SET flagReply = '1' WHERE id = '$id'";
		   query($query);

		   send($notelp, $pesan);

		   echo "<p>SMS sudah dikirim</p>";
		}
		else if ($op == "delete")
		{
		   $id = $_GET['id'];
		   $query = "DELETE FROM sms_inbox WHERE id = '$id'";
		   query($query);
		   echo "SMS sudah dihapus";
		 }
}
?>
	</div>
</div>
		<!-- end #content -->
