<?php
include "cek.php";
include "menu.php";
include "koneksi.php";
include "send.php";
?>

				<h2 class="title">Atur Group Phonebook</h2>
				
				<div class="entry">
					<p>
				    <?php
					
					if ($_GET['op'] == "update")
{
// proses update data
?>
<h3>Edit Group</h3>
<?php

	$idgroup = $_POST['id'];
	$group = $_POST['group'];
	
	$query = "UPDATE sms_group SET sms_group.group = '$group' WHERE idgroup = '$idgroup'";
	mysql_query($query);
	echo "<p>&nbsp;</p><p>Nama group sudah diupdate</p>";

	
}

if ($_GET['op'] == "add")
{
// proses tambah data group
?>
<h3>Tambah Group</h3>
<p>&nbsp;</p>
<form name="formku" method="post" action="<?php $_SERVER['PHP_SELF'];?>?op=simpan">
Nama Group (tanpa spasi) : <input type="text" name="group"> 
<input type="submit" name="submit" value="Simpan">
</form>


<?php
}

if ($_GET['op'] == "simpan")
{
// proses penyimpanan data group yang baru
   $group = $_POST['group'];
   $query = "INSERT INTO sms_group(sms_group.group) VALUES ('$group')";
   $hasil = mysql_query($query);
   if ($hasil) echo "<p>Data sudah disimpan</p>";
   else echo "<p>Data gagal disimpan</p>";
}

if ($_GET['op'] == "hapus")
{
// proses menghapus data group
    $id = $_GET['id'];
	$query = "DELETE FROM sms_group WHERE idgroup = '$id'";
	mysql_query($query);
	echo "<p>Data group sudah dihapus</p>";
}

if ($_GET['op'] == "edit")
{
// proses edit data group
    $id = $_GET['id'];
    $query = "SELECT * FROM sms_group WHERE idgroup = '$id'";
	$hasil = mysql_query($query);
	$data = mysql_fetch_array($hasil);
?>

<h3>Edit Group</h3>
<p>&nbsp;</p>
<form name="formku" method="post" action="<?php $_SERVER['PHP_SELF'];?>?op=update">
Nama Group : <input type="text" name="group" value="<?php echo $data['group']; ?>"> 
<input type="submit" name="submit" value="Simpan">
<input type="hidden" name="id" value="<?php echo $data['idgroup'];?>">
</form>


<?php	
}
else if ($_GET['op'] == 'show')
{
// menampilkan seluruh data group

$query = "SELECT * FROM sms_group";
$hasil = mysql_query($query);
echo "<br>";
echo "<table border='1' width='100%'>";
echo "<tr><th>ID Group</th><th>Nama Group</th><th>Atur</th></tr>";
while ($data = mysql_fetch_array($hasil))
{
   $i++;
   echo "<tr><td>".$data['idgroup']."</td><td>".$data['group']."</td><td><a href='".$_SERVER['PHP_SELF']."?op=edit&id=".$data['idgroup']."'>Edit</a> | <a href='".$_SERVER['PHP_SELF']."?op=hapus&id=".$data['idgroup']."'>Hapus</a></td></tr>";
}
echo "</table>";
}
				?>	
					</p>
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
					    <li><a href="<?php echo $_SERVER['PHP_SELF']?>?op=show">Tampilkan Semua Group</a></li>
						<li><a href="<?php echo $_SERVER['PHP_SELF']?>?op=add">Tambah Group</a></li>
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