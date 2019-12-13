
<?php

// koneksi ke mysql
//include "koneksi.php";
//include "send.php";
//include "config.php";

// mencatat tanggal sekarang
$tgl = date("Y-m-d H:i:s");

// ---------------------- PROSEDUR AUTO RECEIVED TO INBOX START ------------------------------

$query = "SELECT * FROM inbox
          WHERE textdecoded NOT LIKE 'REG#%' AND textdecoded NOT LIKE 'FWD#%'  AND textdecoded NOT LIKE 'INFO#%' AND (UDH = '' OR UDH LIKE '%01') AND processed = 'false'";

$hasil = mysql_query($query);
while ($data = mysql_fetch_array($hasil))
{
   $sum = 0;
   $noTelp = $data['SenderNumber'];

   if ($data['UDH'] != '')
   {

      $chop = substr($data['UDH'], 0, 8);
	  $n = (int) substr($data['UDH'], 8, 2);
	  $text = "";
	  for ($i=1; $i<=$n; $i++)
	  {
	     $udh = $chop.sprintf("%02s", $n).sprintf("%02s", $i);
		 $query3 = "SELECT * FROM inbox WHERE udh = '$udh' AND SenderNumber = '$noTelp' AND processed = 'false'";
		 $hasil3 = mysql_query($query3);
		 if (mysql_num_rows($hasil3) > 0) $sum++;
	  }

	  if ($sum == $n)
	  {
	  	  for ($i=1; $i<=$n; $i++)
	      {
	         $udh = $chop.sprintf("%02s", $n).sprintf("%02s", $i);
		     $query3 = "SELECT * FROM inbox WHERE udh = '$udh' AND SenderNumber = '$noTelp' AND processed = 'false'";
		     $hasil3 = mysql_query($query3);
		     $data3 = mysql_fetch_array($hasil3);
			 $text .= $data3['TextDecoded'];
			 $id = $data3['ID'];
			 $query3 = "UPDATE inbox SET processed = 'true' WHERE ID = '$id'";
			 mysql_query($query3);
	      }

		  $notelp = $data['SenderNumber'];
		  $time = $data['ReceivingDateTime'];
		  $text = str_replace("'", "", $text);
		  $query2 = "INSERT INTO sms_inbox(msg, sender, time, flagRead, flagReply) VALUES ('$text', '$notelp', '$time', 0, 0)";
		  mysql_query($query2);
		  send($notelp, $msgINBOX);
	  }

   }
   else
   {
      $id = $data['ID'];
      $text = str_replace("'", "", $data['TextDecoded']);
      $query2 = "UPDATE inbox SET processed = 'true' WHERE ID = '$id'";
      mysql_query($query2);
	  $notelp = $data['SenderNumber'];
	  $time = $data['ReceivingDateTime'];
      $query2 = "INSERT INTO sms_inbox(msg, sender, time, flagRead, flagReply) VALUES ('$text', '$notelp', '$time', 0, 0)";
	  mysql_query($query2);
	  send($notelp, $msgINBOX);
   }

}

// ---------------------- PROSEDUR AUTO RECEIVED TO INBOX END ------------------------------


// ---------------------- PROSEDUR AUTO RESPONDER START ------------------------------

$query = "SELECT * FROM sms_phonebook";
$hasil = mysql_query($query);
while ($data = mysql_fetch_array($hasil))
{
   $notelp = $data['noTelp'];
   $tglJoin = $data['dateJoin'];
   $nama = $data['nama'];

   $query2 = "SELECT * FROM sms_autoresponder";
   $hasil2 = mysql_query($query2);
   while ($data2 = mysql_fetch_array($hasil2))
   {
	   $interval = $data2['interv'];
	   $id = $data2['id'];

	   $query3 = "SELECT datediff('$tgl', '$tglJoin') as selisih";
	   $hasil3 = mysql_query($query3);
	   $data3 = mysql_fetch_array($hasil3);

	   if ($data3['selisih'] >= $interval)
	   {
	      $query4 = "SELECT status FROM sms_autolist WHERE phoneNumber = '$notelp' AND id = '$id'";
		  $hasil4 = mysql_query($query4);
		  $data4  = mysql_fetch_array($hasil4);
		  if ($data4['status'] == '0')
		  {
  		     $msg = $data2['msg'];
		     $msg2 = str_replace('[nama]', $nama, $msg);

			 $pesan = str_replace("\r"," ",$msg2);
	         $pesan = str_replace("\n","",$pesan);
	         $pesan = str_replace('"','',$pesan);
             $pesan = str_replace("'","",$pesan);

			 send($notelp, $pesan);

             $query4 = "DELETE FROM sms_autolist WHERE phoneNumber = '$notelp' AND id = '$id'";
			 mysql_query($query4);
          }
	   }
   }

}

// ---------------------- PROSEDUR AUTO RESPONDER END ------------------------------


// ---------------------- PROSEDUR AUTO RECEIVED & REPLY SMS START-------------------------

// command SMS: REG#NAMA#ALAMAT

$query = "SELECT * FROM inbox WHERE (textdecoded LIKE 'REG#%' OR textdecoded LIKE 'FWD#%' OR textdecoded LIKE 'INFO#%')AND processed = 'false'";
$hasil = mysql_query($query);

while ($data = mysql_fetch_array($hasil))
{
$idmsg = $data['ID'];
$notelp = $data['SenderNumber'];
$split = explode("#", $data['TextDecoded']);
$command = strtoupper($split[0]);

$now = date("Y-m-d");

if ($command == "REG")
{
if (count($split) == 3)
{
$nama = $split[1];
$alamat = $split[2];

$query2 = "INSERT INTO sms_phonebook VALUES ('$notelp', '$nama', '$alamat', '$defaultID', '$now')";
$hasil2 = mysql_query($query2);

$reply = $msgREG;
}
else $reply = $msgErrorREG;
}
else if ($command == "INFO")
{
   if (count($split) == 3)
   {
   $keyword = strtoupper($split[1]);
   $key = strtoupper($split[2]);

   $query2 = "SELECT template FROM sms_keyword WHERE keyword = '$keyword'";
   $hasil2 = mysql_query($query2);

   if (mysql_num_rows($hasil2) > 0)
   {
   $data2  = mysql_fetch_array($hasil2);
   $template = $data2['template'];

   preg_match_all("|\[(.*)\]|U", $template, $string, PREG_PATTERN_ORDER);

   $query2 = "SELECT * FROM sms_data WHERE keyword = '$keyword' AND `key` = '$key'";
   $hasil2 = mysql_query($query2);
   if (mysql_num_rows($hasil2) > 0)
   {
   $data2 = mysql_fetch_array($hasil2);

   foreach($string[1] as $kunci => $nilai)
   {
      $template = str_replace('['.$nilai.']', '['.strtolower($nilai).']', $template);
	  $kapital = strtolower($nilai);
	  $template = str_replace('['.$kapital.']', $data2[$kapital], $template);
   }
   $reply = $template;
   }
   else $reply = $msgErrorData;
   }
   else $reply = $msgErrorKeyword;
   }
   else $reply = $msgErrorInfo;

}
else if ($command == "FWD")
{
  if (count($split) == 3)
  {
  $idgroup = $split[1];
  $pesan = $split[2];

  $query2 = "SELECT noTelp FROM sms_phonebook WHERE idgroup = '$idgroup'";
  $hasil2 = mysql_query($query2);
  while ($data2 = mysql_fetch_array($hasil2))
  {
     send($data2['noTelp'], $pesan." (Dikirim oleh: ".$notelp.")");
  }
  $reply = $msgFWD.$idgroup;
  }
  else $reply = $msgErrorFWD;

}

send($notelp, $reply);

$query2 = "UPDATE inbox SET Processed = 'true' WHERE ID = '$idmsg'";
$hasil2 = mysql_query($query2);
}

// ---------------------- PROSEDUR AUTO RECEIVED & REPLY SMS FINISH -------------------------


// ---------------------- PROSEDUR ON SCHEDULED SMS START-------------------------

// mencari message yang publish date nya tanggal sekarang dan statusnya masih = 0 (belum dikirim)
$query = "SELECT * FROM sms_message WHERE pubdate <= '$tgl' AND status = 0";
$hasil = mysql_query($query);
$data = mysql_fetch_array($hasil);

if (mysql_num_rows($hasil) > 0)
{
// jika ada message yang publish datenya tgl sekarang dan statusnya 0 maka dikirim

// membaca isi dan id message
$pesan = $data['message'];
$group = $data['idgroup'];
$id = $data['id'];

$pesan = str_replace("\r"," ",$pesan);
$pesan = str_replace("\n","",$pesan);
$pesan = str_replace('"','',$pesan);
$pesan = str_replace("'","",$pesan);

// mengubah status message menjadi 1 (telah dikirim)
$query = "DELETE FROM sms_message WHERE id = '$id'";
$hasil = mysql_query($query);

// menyimpan message yang dikirim ke tabel sms_sentmsg (berisi message2 yang pernah dikirim)
$query = "INSERT INTO sms_sentmsg(msg) VALUES ('$pesan')";
$hasil = mysql_query($query);

// membaca ID message yang baru saja dikirim dari tabel sms_sentmsg
$query = "SELECT max(id) as max FROM sms_sentmsg";
$hasil = mysql_query($query);
$data = mysql_fetch_array($hasil);
$idmsg = $data['max'];

// membaca seluruh no. telp dari tabel sms_phonebook

if ($group == 0) $query = "SELECT * FROM sms_phonebook";
else $query = "SELECT * FROM sms_phonebook WHERE idgroup = '$group'";

$hasil = mysql_query($query);
while ($data = mysql_fetch_array($hasil))
{
      // proses pengiriman pesan SMS ke semua no. telp
      $notelp = $data['noTelp'];
      $pesan2 = str_replace('[nama]', $data['nama'], $pesan);

	  send($notelp, $pesan2);
}
}

// ---------------------- PROSEDUR ON SCHEDULED SMS FINISH-------------------------
?>

<?php
echo "<table border='1' width='100%'>";
echo "<tr><th>Sending Date Time</th><th>Destination Number</th><th>Text Decoded</th><th>Status</th></tr>";
$query = "SELECT * FROM sentitems ORDER BY SendingDateTime DESC LIMIT 0, 100";
$hasil = mysql_query($query);

while ($data = mysql_fetch_array($hasil))
{
   echo "<tr><td>".$data['SendingDateTime']."</td><td>".$data['DestinationNumber']."</td><td>".$data['TextDecoded']."</td><td>".$data['Status']."</td></tr>";
}
echo "</table>";


?>
