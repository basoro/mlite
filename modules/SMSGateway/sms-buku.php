
				<h2 class="title">Daftar Phonebook</h2>

				<div class="entry">

<?php


if ($_GET['op'] == "send")
{
  $ph = $_POST['phone'];
  $sms = $_POST['pesan'];
  send($ph, $sms);
  echo "<br><p>Pesan SMS sudah dikirim....</p>";
}
else
if ($_GET['op'] == "instant")
{
  $ph = str_replace(" ", "+", $_GET['ph']);
  $query = "SELECT nama FROM petugas WHERE no_telp = '$ph'";
  $hasil = query($query);
  $data  = fetch_array($hasil);
  $nama = $data['nama'];
  echo "<br><p><b>Nomor Tujuan :</b> ".$nama." (".$ph.")</p>";
?>
<form name="formku" method="post" action="<?php $_SERVER['PHP_SELF'];?>?op=send">
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
else
{
// menampilkan seluruh data phonebook

$query = "SELECT * FROM petugas ORDER BY nama";
$hasil = query($query);
echo "<p>&nbsp;</p>";
echo "<table border='1'  width='100%'>";
echo "<tr><th>No.</th><th>Nama</th><th>Alamat</th><th>No. Telp</th><th>Group</th><th>Atur</th></tr>";
$i = ($noPage-1)*$dataPerPage;
while ($data = fetch_array($hasil))
{
   $i++;
   $idgroup = $data['kd_jbtn'];
   $query2 = "SELECT jabatan.nm_jbtn FROM jabatan WHERE kd_jbtn = '$idgroup'";
   $hasil2 = query($query2);
   $data2  = fetch_array($hasil2);

   echo "<tr><td>".$i."</td><td>".$data['nama']."</td><td>".$data['alamat']."</td><td>".$data['no_telp']."</td><td>".$data2['nm_jbtn']."</td><td>&nbsp;&nbsp;<a href='".URL."?module=SMSGateway&page=buku_telepon&op=instant&ph=".$data['no_telp']."'>Kirim SMS</a>&nbsp;&nbsp; </td></tr>";

}
echo "</table><br>";

}


					?></p>
				</div>
			</div>
			</div>
			</div>

		<div style="clear: both;">&nbsp;</div>
		</div>
		<!-- end #content -->
