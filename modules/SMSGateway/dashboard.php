<body onload="ajax()"></body>

        <?php
        if(num_rows(query("SHOW TABLES LIKE 'gammux'")) !== 1) {
          echo '<h3>Belum terinstall Database SMS Gateway</h3>';
        }
        ?>
				<div class="entry">

<?php

if (($_GET['op'] == 'main') || (!isset($_GET['op'])))
{
?>
					<h3>Fitur Utama:</h3>
<ul>
   <li>Manajemen Phonebook</li>
   <li>Manajemen Group</li>
   <li>Manajemen INBOX SMS</li>
   <li>Reply SMS INBOX</li>
   <li>Manajemen Auto Responder<br>Mendukung pesan SMS secara terjadwal seperti halnya auto responder di internet marketing, berdasarkan group</li>
   <li>Personalisasi SMS <br>Pesan SMS yang dikirimkan bisa berisi nama masing-masing pemilik nomor, sesuai yang ada di phonebook)</li>
   <li>Support Registrasi via SMS <br>Seseorang bisa melakukan registrasi ke dalam daftar phonebook melalui SMS</li>
   <li>Auto Confirm Registrasi via SMS <br>Seseorang yang telah melakukan registrasi ke phonebook via SMS akan mendapat balasan atau konfirmasi otomatis via SMS juga</li>
   <li>Customizable Auto Confirm SMS Message<br>Isi pesan konfirmasi ketika registrasi phonebook bisa diatur sendiri.</li>
   <li>Kirim SMS Instant ke semua nomor atau berdasar group</li>
   <li>On Scheduled SMS ke semua nomor atau berdasar group</li>
   <li>Support Long Text SMS Sending and Receive (unlimited character)</li>
   <li>SMS Sending Report</li>
   <li>SMS Inbox & Outbox Export ke Excel</li>
   <li>SMS broadcast via import file Excel</li>
   <li>SMS Autoforward</li>
</ul>

<h2>Status Service</h2>
<ul>
<li>
<div id="service">

</div>
</li>
</ul>

<?php
}
else if ($_GET['op'] == 'onservice')
{
?>
<h2>Menjalankan Service GAMMU</h2>
<p>&nbsp;</p>
<p>Klik tombol di bawah ini untuk menjalankan GAMMU Service!</p>

<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>?op=onservice&action=start">
<input type="submit" name="submit" value="JALANKAN SERVICE GAMMU">
</form>

<?php
  if ($_GET['action'] == 'start')
  {
   echo "<p>&nbsp;</p>";
   echo "<b>Status :</b><br>";
   echo "<pre>";
   passthru($path."\gammu-smsd -c smsdrc -s");
   echo "</pre>";
  }
}
else if ($_GET['op'] == 'offservice')
{
?>
<h2>Menghentikan Service GAMMU</h2>
<p>&nbsp;</p>
<p>Klik tombol di bawah ini untuk menghentikan GAMMU Service!</p>

<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>?op=offservice&action=off">
<input type="submit" name="submit" value="MATIKAN SERVICE GAMMU">
</form>

<?php
  if ($_GET['action'] == 'off')
  {
   echo "<p>&nbsp;</p>";
   echo "<b>Status :</b><br>";
   echo "<pre>";
   passthru($path."\gammu-smsd -k");
   echo "</pre>";
  }
}
else if ($_GET['op'] == 'pulsa')
{
?>
<h2>Cek Pulsa</h2>
<p>&nbsp;</p>
<p><b>Penting!!!</b></p><p>Pastikan sebelum cek pulsa, service harus sudah dimatikan dahulu</p>
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>?op=pulsa&action=cek">
Masukkan perintah cek pulsa <input type="text" name="command"> Mis. *123#
<br><br><input type="submit" name="submit" value="CEK PULSA">
</form>

<?php
  if ($_GET['action'] == 'cek')
  {
  $command = $_POST['command'];
  $command = $path."\gammu -c ".$path."\gammurc getussd ".$command;

   // jalankan perintah cek pulsa via gammu
   exec($command, $hasil);

// proses filter hasil output
for ($i=0; $i<=count($hasil)-1; $i++)
{
   if (substr_count($hasil[$i], 'Service reply') > 0) $index = $i;
}

// menampilkan sisa pulsa
echo $hasil[$index];

  }
}
else if ($_GET['op'] == 'config')
{
include "sms-config.php";

if ($_GET['action'] == 'proses')
{
$path = $_POST['path'];
$msgREG = $_POST['regsukses'];
$msgErrorREG = $_POST['reggagal'];
$msgFWD = $_POST['fwdsukses'];
$msgErrorFWD = $_POST['fwdgagal'];
$msgINBOX = $_POST['smsinbox'];
$msgErrorData = $_POST['errordata'];
$msgErrorKeyword = $_POST['errorkeyword'];
$msgErrorInfo = $_POST['errorinfo'];
$defaultID = $_POST['groupid'];


$path = "\$path = \"".$path."\";\n";
$msgREG = "\$msgREG = \"".$msgREG."\";\n";
$msgErrorREG = "\$msgErrorREG = \"".$msgErrorREG."\";\n";
$msgFWD = "\$msgFWD = \"".$msgFWD."\";\n";
$msgErrorFWD = "\$msgErrorFWD = \"".$msgErrorFWD."\";\n";
$msgINBOX = "\$msgINBOX = \"".$msgINBOX."\";\n";
$msgErrorData = "\$msgErrorData = \"".$msgErrorData."\";\n";
$msgErrorKeyword = "\$msgErrorKeyword = \"".$msgErrorKeyword."\";\n";
$msgErrorInfo = "\$msgErrorInfo = \"".$msgErrorInfo."\";\n";
$defaultID = "\$defaultID = \"".$defaultID."\";\n";

$file = "modules/SMSGateway/sms-config.php";

$arrayRead = file($file);

$arrayRead[1] = $path;
$arrayRead[2] = $msgREG;
$arrayRead[3] = $msgErrorREG;
$arrayRead[4] = $msgFWD;
$arrayRead[5] = $msgErrorFWD;
$arrayRead[6] = $msgINBOX;
$arrayRead[7] = $msgErrorData;
$arrayRead[8] = $msgErrorKeyword;
$arrayRead[9] = $msgErrorInfo;
$arrayRead[10] = $defaultID;

$simpan = file_put_contents($file, implode($arrayRead));
echo "<p>Konfigurasi sudah tersimpan</p>";
}
else
{
?>

<h2>Setting Konfigurasi</h2>
<p>&nbsp;</p>
<form method="post" action="<?php echo URL; ?>/?module=SMSGateway&page=index&op=config&action=proses">
<table>
<tr><td>Path ke folder Gammu</td><td>:</td><td><input type="text" name="path" size="70" value="<?php echo $path; ?>"></td></tr>
<tr><td>Reply REG (sukses)</td><td>:</td><td><input type="text" name="regsukses" size="70" value="<?php echo $msgREG; ?>"></td></tr>
<tr><td>Reply REG (gagal)</td><td>:</td><td><input type="text" name="reggagal" size="70" value="<?php echo $msgErrorREG; ?>"></td></tr>
<tr><td>Reply FWD (sukses)</td><td>:</td><td><input type="text" name="fwdsukses" size="70" value="<?php echo $msgFWD; ?>"></td></tr>
<tr><td>Reply FWD (gagal)</td><td>:</td><td><input type="text" name="fwdgagal" size="70" value="<?php echo $msgErrorFWD; ?>"></td></tr>
<tr><td>Reply SMS INBOX </td><td>:</td><td><input type="text" name="smsinbox" size="70" value="<?php echo $msgINBOX; ?>"></td></tr>
<tr><td>Reply INFO (Error Data) </td><td>:</td><td><input type="text" name="errordata" size="70" value="<?php echo $msgErrorData; ?>"></td></tr>
<tr><td>Reply INFO (Error Keyword) </td><td>:</td><td><input type="text" name="errorkeyword" size="70" value="<?php echo $msgErrorKeyword; ?>"></td></tr>
<tr><td>Reply INFO (Error Info) </td><td>:</td><td><input type="text" name="errorinfo" size="70" value="<?php echo $msgErrorInfo; ?>"></td></tr>
<tr><td>Default ID Group</td><td>:</td><td><input type="text" name="groupid" size="10" value="<?php echo $defaultID; ?>"></td></tr>
</table>
<br>
<input type="submit" value="SIMPAN">
</form>

<?php
}
}
else if ($_GET['op'] == 'install')
{

$handle = @fopen("sms.sql", "r");
$content = fread($handle, filesize("sms.sql"));
$split = explode(";", $content);

mysql_select_db($dbname) or die(mysql_error());

for ($i=0; $i<=count($split)-1; $i++)
{
   mysql_query($split[$i]);
}

fclose($handle);

echo "<h3>Instalasi Sukses</h3>";
}

?>
				</div>
			</div>
			</div>
			</div>

		<div style="clear: both;">&nbsp;</div>
		</div>
		<!-- end #content -->
