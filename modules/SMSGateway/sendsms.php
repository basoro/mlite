<?php
include "cek.php";
include "menu.php";
include "koneksi.php";
include "send.php";
?>

				<h2 class="title">SMS Instant</h2>
				
				<div class="entry">
					<p>
<?php
if ($_GET['op'] == "send")
{
   // jika pengirimannya berdasarkan group
   if ($_POST['kirim'] == "group")
   {
   // membaca group
   $group = $_POST['group'];
   // membaca pesan yang akan dikirim dari form
   $pesan = $_POST['pesan'];
   
   // menyimpan pesan ke tabel sms_sentmsg
   $query = "INSERT INTO sms_sentmsg(msg) VALUES ('$pesan')";
   $hasil = mysql_query($query);
   
   // membaca no. ID pesan yang akan dikirim dari tabel sms_sentmsg
   $query = "SELECT max(id) as max FROM sms_sentmsg WHERE msg = '$pesan'";
   $hasil = mysql_query($query);
   $data = mysql_fetch_array($hasil);
   $idmsg = $data['max'];
   
     
   // membaca  no. telp dari phonebook berdasarkan group
   
   if ($group == 0) $query = "SELECT * FROM sms_phonebook";
   else if ($group > 0) $query = "SELECT * FROM sms_phonebook WHERE idgroup = '$group'";
   
   $hasil = mysql_query($query);
   
   while ($data = mysql_fetch_array($hasil))
   {
      // proses pengiriman pesan SMS ke semua no. telp
      $notelp = $data['noTelp'];
	  
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
   
   echo "<hr><p>SMS sudah dikirim....</p><hr>";
}
else if ($_GET['op'] == 'broadcast')
{
?>

<h2>SMS Broadcast Via Upload File</h2>
<p>&nbsp;</p>

<form method="post" enctype="multipart/form-data" action="sendsms.php?op=broadcast&action=proses">
Pilih file source<br>
<input type="hidden" name="MAX_FILE_SIZE" value="20000000">
<input name="userfile" type="file" size="50"><br><br>
Masukkan template SMS<br>
<textarea name="template" cols="50" rows="8"></textarea><br><br>
<input name="upload" type="submit" value="KIRIM SMS"></td>
</form>

<?php
   if ($_GET['action'] == 'proses')
   {
    error_reporting(E_ALL ^ E_NOTICE);
	require_once 'excel_reader2.php';

	$data = new Spreadsheet_Excel_Reader($_FILES['userfile']['tmp_name']);

	$baris = $data->rowcount($sheet_index=0);
	$kolom = $data->colcount($sheet_index=0);

	for ($i=2; $i<=$baris; $i++)
	{
		$string = $_POST['template'];
		preg_match_all("|\[(.*)\]|U", $string, $hasil, PREG_PATTERN_ORDER);

		for($j=1; $j<=$kolom; $j++)
		{
			$value[$data->val(1, $j)] = $data->val($i, $j);    
		}
     
		foreach($hasil[1] as $key => $nilai)
		{
   			$string = str_replace('['.$nilai.']', '['.strtoupper($nilai).']', $string);
			$kapital = strtoupper($nilai);
			$string = str_replace('['.$kapital.']', $value[$kapital], $string);
		}
  
		if ($value['NOHP'] != '')
		{
		  send($value['NOHP'], $string);
		}  
	}
    echo "<br><br><p>SMS telah dikirim....</p>";
   }

}
else if ($_GET['op'] == 'autoreply')
{
?>
<h2>Auto Reply SMS</h2>
<p>&nbsp;</p>
<form method="post" enctype="multipart/form-data" action="sendsms.php?op=autoreply&action=proses">
Pilih file source<br>
<input type="hidden" name="MAX_FILE_SIZE" value="20000000">
<input name="userfile" type="file" size="50"> <input name="upload" type="submit" value="Import Data"></td>
</form>
<p>&nbsp;</p>
<h3><small>Daftar Keyword</small></h3>
<br>
<table border='1' width='100%'>
<tr><th>NO</th><th>KEYWORD</th><th>ACTION</th></tr>
<?php

if ($_GET['action'] == 'delete')
{
   $key = $_GET['key'];
   $query = "DELETE FROM sms_data WHERE keyword = '$key'";
   mysql_query($query);
   $query = "DELETE FROM sms_keyword WHERE keyword = '$key'";
   mysql_query($query);
   echo "<br><p>Data Auto Reply Keyword ".$key." Sudah Dihapus</p>";
}
else if ($_GET['action'] == 'proses')
{
    error_reporting(E_ALL ^ E_NOTICE);
	require_once 'excel_reader2.php';

	$data = new Spreadsheet_Excel_Reader($_FILES['userfile']['tmp_name']);

	$baris = $data->rowcount($sheet_index=0);
	$kolom = $data->colcount($sheet_index=0);
	
	$sukses = 0;
	$gagal = 0;
	
	for ($i=2; $i<=$baris; $i++)
	{
	    $keyword = str_replace(" ", "", strtoupper($data->val($i, 1)));
		$key = strtoupper($data->val($i, 2));
		$field1 = $data->val($i, 3);
		$field2 = $data->val($i, 4);
		$field3 = $data->val($i, 5);
		$field4 = $data->val($i, 6);
		$field5 = $data->val($i, 7);
		
		if (($keyword != '') && ($key != ''))
		{
		$query = "INSERT INTO sms_data VALUES ('$keyword', '$key', '$field1', '$field2', '$field3', '$field4', '$field5')";
		$hasil = mysql_query($query);
		if ($hasil) $sukses++;
		else $gagal++;
		$katakunci = $keyword;
		}
	}
    echo "<br><p>Data telah diimport</p>";
	echo "<p>Jumlah Data: ".($gagal+$sukses).", Jumlah Data Sukses Diimport: ".$sukses.", Jumlah Data Gagal Diimport: ".$gagal."</p>";
	
	$query = "INSERT INTO sms_keyword VALUES ('$katakunci', '')";
	mysql_query($query);
	
}


$query = "SELECT keyword FROM sms_keyword ORDER BY keyword";
$hasil = mysql_query($query);
$i = 1;
while ($data = mysql_fetch_array($hasil))
{
   echo "<tr><td>".$i."</td><td>".$data['keyword']."</td><td> <a href='".$_SERVER['PHP_SELF']."?op=autoreply&action=view&key=".$data['keyword']."'>View Data</a> | <a href='".$_SERVER['PHP_SELF']."?op=autoreply&action=template&key=".$data['keyword']."'>Set Template</a> | <a href='".$_SERVER['PHP_SELF']."?op=autoreply&action=delete&key=".$data['keyword']."'>Hapus Data</a></td></tr>";
   $i++;
} 
echo "</table>";

if ($_GET['action'] == 'view')
{
   $key = $_GET['key'];
   $query = "SELECT * FROM sms_data WHERE keyword = '$key'";
   $hasil = mysql_query($query);
   $i = 1;
   echo "<br><br><table border='1' width='100%'>";
   echo "<tr><th>NO</th><th>KEYWORD</th><th>KEY</th><th>FIELD1</th><th>FIELD2</th><th>FIELD3</th><th>FIELD4</th><th>FIELD5</th></tr>";
   while ($data = mysql_fetch_array($hasil))
   {
     echo "<tr><td>".$i."</td><td>".$data['keyword']."</td><td>".$data['key']."</td><td>".$data['field1']."</td><td>".$data['field2']."</td><td>".$data['field3']."</td><td>".$data['field4']."</td><td>".$data['field5']."</td></tr>";
	 $i++;
   }
   echo "</table>";

}
else if ($_GET['action'] == 'updatetemplate')
{
   $key = $_POST['key'];
   $template = $_POST['template'];
   $query = "UPDATE sms_keyword SET template = '$template' WHERE keyword = '$key'";
   mysql_query($query);
   echo "<br><p>Template sudah diupdate</p>";
}
else if ($_GET['action'] == 'template')
{
   $key = $_GET['key'];
   $query = "SELECT * FROM sms_keyword WHERE keyword = '$key'";
   $hasil = mysql_query($query);
   $data = mysql_fetch_array($hasil);
   $template = $data['template'];
   echo "<br><br><p><b>SET TEMPLATE KEYWORD : ".$key."</b></p>";
?>
   <form method="post" action="sendsms.php?op=autoreply&action=updatetemplate">
   <textarea cols="50" rows="5" name="template"><?php echo $template; ?></textarea><br><br>
   <input type="hidden" name="key" value="<?php echo $key; ?>">
   <input type="submit" name="submit" value="Simpan">
   </form>
<?php
}

}
else if ($_GET['op'] == 'single')
{
?>
<h2>Single SMS</h2>
<p>&nbsp;</p>
<form name="formku" method="post" action="<?php $_SERVER['PHP_SELF'];?>?op=send">
Message : <br>
<textarea name="pesan" rows="8" cols="50"></textarea>
<br><br>
<b>Keterangan:</b> <br>Berikan string [nama] bila ingin menampilkan nama si penerima SMS pada pesan di atas. <br>Contoh: "Hallo [nama], apa kabar?"
<br><br>
<table>
<tr><td><input type="radio" name="kirim" value="group"></td><td>Kirim Ke Group :</td><td> 
<select name='group'>
<option value="0" selected>All</option>
<?php
$query = "SELECT * FROM sms_group";
$hasil = mysql_query($query);
while ($data = mysql_fetch_array($hasil))
{
  echo "<option value='".$data['idgroup']."'>".$data['group']."</option>";
}
?>
</select></td></tr>
<tr>
<td><input type="radio" name="kirim" value="single"></td><td>Kirim Ke Single :</td><td>
<select name='nohp'>
<?php
$query = "SELECT * FROM sms_phonebook";
$hasil = mysql_query($query);
while ($data = mysql_fetch_array($hasil))
{
  echo "<option value='".$data['noTelp']."'>".$data['nama']."</option>";
}
?> 
</select></td></tr>
</table>
<br>
<input type="submit" name="submit" value="Send SMS">
</form>
<?php
}
?></p>
				</div>
			</div>
			</div>
			</div>
			
		<div style="clear: both;">&nbsp;</div>
		</div>
		
		<div id="sidebar">
		<ul>
				<li>
					<h2>Sub menu</h2>
					<ul>
					    <li><a href="<?php echo $_SERVER['PHP_SELF']?>?op=single">Single SMS</a></li>
						<li><a href="<?php echo $_SERVER['PHP_SELF']?>?op=broadcast">Broadcast SMS via Import File</a></li>
						<li><a href="<?php echo $_SERVER['PHP_SELF']?>?op=autoreply">Import Data Auto Reply SMS</a></li>
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
		<p>&nbsp;</p>
			<img src="images/sms.jpg">
		</div>
	
<?php
include "footer.php";
?>