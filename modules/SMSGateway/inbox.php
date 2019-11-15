<?php
include "cek.php";
include "menu.php";
include "koneksi.php";
include "send.php";
?>
				<h2 class="title">INBOX</h2>
				
				<div class="entry">
					<p><?php
					
					if ($_GET['op'] == "delall")
{
   $query = "DELETE FROM inbox";
   mysql_query($query);
   $query = "DELETE FROM sms_inbox";
   mysql_query($query);
   echo "<p>Semua SMS inbox sudah dihapus</p>";
}
else if ($_GET['op'] == "view")
{
   $id = $_GET['id'];
   $query = "SELECT * FROM sms_inbox WHERE id = '$id'";
   $hasil = mysql_query($query);
   $data = mysql_fetch_array($hasil);
   echo "<p><b>No. Pengirim:</b> ".$data['sender']."</p>";
   echo "<p><b>Waktu Pengiriman:</b> ".$data['time']."</p>";
   echo "<p><b>Message:</b></p>";
   echo "<p>".$data['msg']."</p>";
   echo "<p>[<b><a href='".$_SERVER['PHP_SELF']."?op=view&act=reply&id=".$data['id']."'>REPLY SMS</a></b>] [<b><a href='".$_SERVER['PHP_SELF']."?op=view&act=forward&id=".$data['id']."'>FORWARD SMS</a></b>]</p>";
   $query = "UPDATE sms_inbox SET flagRead = '1' WHERE id = '$id'";
   mysql_query($query);
   
   $noSender = $data['sender'];
   
   
   if ($_GET['act'] == "forward")
   {
   $id = $_GET['id'];
   $query = "SELECT * FROM sms_inbox WHERE id = '$id'";
   $hasil = mysql_query($query);
   $data = mysql_fetch_array($hasil);
?>
<form name="formku" method="post" action="<?php $_SERVER['PHP_SELF'];?>?op=send">
Message : <br>
<textarea name="pesan" rows="12" cols="50"><?php echo $data['msg']; ?></textarea>
<br><br>
Forward ke : 
<select name="sender">
<?php
$query = "SELECT * FROM sms_phonebook";
$hasil = mysql_query($query);
while ($data = mysql_fetch_array($hasil))
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
<form name="formku" method="post" action="<?php $_SERVER['PHP_SELF'];?>?op=send">
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
   <table border="1" width="100%">
   <tr><th>Isi SMS</th><th>Pengirim</th><th>Waktu SMS</th></tr>
   <?php
     $query = "SELECT * FROM sms_inbox WHERE sender = '$noSender' AND id <> '$id' ORDER BY time DESC";
	 $hasil = mysql_query($query);
	 while ($data = mysql_fetch_array($hasil))
	 {
	   echo "<tr><td>".$data['msg']."</td><td>".$data['sender']."</td><td>".$data['time']."</td></tr>";
	 }
   ?>
   </table>
   <?php
   }
}
else if ($_GET['op'] == "forward")
{
   $pesan = $_POST['pesan'];
   $notelp = $_POST['sender'];
  
   send($notelp, $pesan);	  
   
   echo "<p>SMS sudah dikirim</p>";
}
else if ($_GET['op'] == "send")
{
   $pesan = $_POST['pesan'];
   $id = $_POST['id'];
   $notelp = $_POST['sender'];
   
   $query = "UPDATE sms_inbox SET flagReply = '1' WHERE id = '$id'";
   mysql_query($query);
    
   send($notelp, $pesan);
   
   echo "<p>SMS sudah dikirim</p>";
}
else if ($_GET['op'] == "delete")
{
   $id = $_GET['id'];
   $query = "DELETE FROM sms_inbox WHERE id = '$id'";
   mysql_query($query);
   echo "SMS sudah dihapus";
   
   $dataPerPage = 20;
 
if(isset($_GET['page']))
{
    $noPage = $_GET['page'];
} 
else $noPage = 1;
  
$offset = ($noPage - 1) * $dataPerPage;

?>
<table border="1" width="100%">
<tr><th>Isi SMS</th><th>Status Reply</th><th>Pengirim</th><th>Waktu SMS</th><th>Action</th></tr>

<?php
$query = "SELECT * FROM sms_inbox ORDER BY time DESC LIMIT $offset, $dataPerPage";
$hasil = mysql_query($query);
while ($data = mysql_fetch_array($hasil))
{
  $pesan = substr($data['msg'], 0, 50);
 
  $nosender = $data['sender'];
  $query3 = "SELECT nama FROM sms_phonebook WHERE noTelp = '$nosender'";
  $hasil3 = mysql_query($query3);
  $data3  = mysql_fetch_array($hasil3);
  
  if ($data3['nama'] == "") $sendername = $data['sender'];
  else $sendername = $data3['nama'];  
 
  if ($data['flagRead'] == 0) $color = "yellow";
  else $color = "white"; 
  
  if ($data['flagReply'] == 0) $status = "&nbsp;";
  else $status = "<b>[R]</b>";
    
  echo "<tr bgcolor='".$color."'><td><a href='".$_SERVER['PHP_SELF']."?op=view&id=".$data['id']."'>".$pesan."...</a></td><td align='center'>".$status."</td><td>".$sendername."</td><td>".$data['time']."</td><td><a href='".str_replace("&id=".$id, "", $_SERVER['REQUEST_URI'])."&id=".$data['id']."'>Hapus</a></td></tr>";
}
echo "</table><br>";

$query   = "SELECT COUNT(*) AS jumData FROM sms_inbox";
$hasil  = mysql_query($query);
$data     = mysql_fetch_array($hasil);
 
$jumData = $data['jumData'];
 
// menentukan jumlah halaman yang muncul berdasarkan jumlah semua data
 
$jumPage = ceil($jumData/$dataPerPage);
 
// menampilkan link previous

echo "Halaman: "; 
if ($noPage > 1) echo  "<a href='".$_SERVER['PHP_SELF']."?page=".($noPage-1)."'>&lt;&lt; Prev</a>";
 
// memunculkan nomor halaman dan linknya
 
for($page = 1; $page <= $jumPage; $page++)
{
         if ((($page >= $noPage - 3) && ($page <= $noPage + 3)) || ($page == 1) || ($page == $jumPage)) 
         {   
            if (($showPage == 1) && ($page != 2))  echo "..."; 
            if (($showPage != ($jumPage - 1)) && ($page == $jumPage))  echo "...";
            if ($page == $noPage) echo " <b>".$page."</b> ";
            else echo " <a href='".$_SERVER['PHP_SELF']."?page=".$page."'>".$page."</a> ";
            $showPage = $page;          
         }
}
 
// menampilkan link next
 
if ($noPage < $jumPage) echo "<a href='".$_SERVER['PHP_SELF']."?page=".($noPage+1)."'>Next &gt;&gt;</a>";
}

else 
{

$dataPerPage = 20;
 
if(isset($_GET['page']))
{
    $noPage = $_GET['page'];
} 
else $noPage = 1;
  
$offset = ($noPage - 1) * $dataPerPage;

?>
<table border="1" width="100%">
<tr><th>Isi SMS</th><th>Status Reply</th><th>Pengirim</th><th>Waktu SMS</th><th>Action</th></tr>

<?php
$query = "SELECT * FROM sms_inbox ORDER BY time DESC LIMIT $offset, $dataPerPage";
$hasil = mysql_query($query);
while ($data = mysql_fetch_array($hasil))
{
  $pesan = substr($data['msg'], 0, 50);
 
  $nosender = $data['sender'];
  $query3 = "SELECT nama FROM sms_phonebook WHERE noTelp = '$nosender'";
  $hasil3 = mysql_query($query3);
  $data3  = mysql_fetch_array($hasil3);
  
  if ($data3['nama'] == "") $sendername = $data['sender'];
  else $sendername = $data3['nama'];  
 
  if ($data['flagRead'] == 0) $color = "yellow";
  else $color = "white"; 
  
  if ($data['flagReply'] == 0) $status = "&nbsp;";
  else $status = "<b>[R]</b>";
    
  echo "<tr bgcolor='".$color."'><td><a href='".$_SERVER['PHP_SELF']."?op=view&id=".$data['id']."'>".$pesan."...</a></td><td align='center'>".$status."</td><td>".$sendername."</td><td>".$data['time']."</td><td><a href='".$_SERVER['REQUEST_URI']."&op=delete&id=".$data['id']."'>Hapus</a></td></tr>";
}
echo "</table><br>";

$query   = "SELECT COUNT(*) AS jumData FROM sms_inbox";
$hasil  = mysql_query($query);
$data     = mysql_fetch_array($hasil);
 
$jumData = $data['jumData'];
 
// menentukan jumlah halaman yang muncul berdasarkan jumlah semua data
 
$jumPage = ceil($jumData/$dataPerPage);
 
// menampilkan link previous

echo "Halaman: "; 
if ($noPage > 1) echo  "<a href='".$_SERVER['PHP_SELF']."?page=".($noPage-1)."'>&lt;&lt; Prev</a>";
 
// memunculkan nomor halaman dan linknya
 
for($page = 1; $page <= $jumPage; $page++)
{
         if ((($page >= $noPage - 3) && ($page <= $noPage + 3)) || ($page == 1) || ($page == $jumPage)) 
         {   
            if (($showPage == 1) && ($page != 2))  echo "..."; 
            if (($showPage != ($jumPage - 1)) && ($page == $jumPage))  echo "...";
            if ($page == $noPage) echo " <b>".$page."</b> ";
            else echo " <a href='".$_SERVER['PHP_SELF']."?page=".$page."'>".$page."</a> ";
            $showPage = $page;          
         }
}
 
// menampilkan link next
 
if ($noPage < $jumPage) echo "<a href='".$_SERVER['PHP_SELF']."?page=".($noPage+1)."'>Next &gt;&gt;</a>";

}
					
					
					
					?></p>
				</div>
			</div>
			</div>
			</div>
			
		<div style="clear: both;">&nbsp;</div>
		</div>
		<!-- end #content -->
		<div id="sidebar">
			<ul>
				<li>
					<h2>Sub menu</h2>
					<ul>
					    <li><a href="inbox.php?page=1">Lihat Semua INBOX</a></li>
						<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?op=delall">Hapus Semua Inbox</a></li>
						<li><a href="export.php?op=inbox">Export Inbox ke Excel</a></li>
						<?php
						if (isset($_SESSION['login']))
						{
						echo '<li><a href="index.php?op=logout">Logout</a></li>';
						}
						?>
					</ul>
				</li>
				
				<li>
					<h2>Status Service</h2>
					<ul>
					<li>
					<div id="service">
					
					</div>
					</li>
					</ul>
				</li>
					
			</ul>
			<img src="images/sms.jpg">
		</div>
		
<?php
include "footer.php";
?>