
<?php

// koneksi ke mysql
include "../../../config.php";
include "send.php";

// mencatat tanggal sekarang
$tgl = date("Y-m-d H:i:s");

// ---------------------- PROSEDUR AUTO RECEIVED TO INBOX START ------------------------------

$query = "SELECT * FROM inbox
          WHERE textdecoded NOT LIKE 'REG#%' AND textdecoded NOT LIKE 'FWD#%'  AND textdecoded NOT LIKE 'INFO#%' AND (UDH = '' OR UDH LIKE '%01') AND processed = 'false'";

$hasil = query($query);
while ($data = fetch_array($hasil))
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
		 $hasil3 = query($query3);
		 if (num_rows($hasil3) > 0) $sum++;
	  }

	  if ($sum == $n)
	  {
	  	  for ($i=1; $i<=$n; $i++)
	      {
	         $udh = $chop.sprintf("%02s", $n).sprintf("%02s", $i);
		     $query3 = "SELECT * FROM inbox WHERE udh = '$udh' AND SenderNumber = '$noTelp' AND processed = 'false'";
		     $hasil3 = query($query3);
		     $data3 = fetch_array($hasil3);
			 $text .= $data3['TextDecoded'];
			 $id = $data3['ID'];
			 $query3 = "UPDATE inbox SET processed = 'true' WHERE ID = '$id'";
			 query($query3);
	      }

		  $notelp = $data['SenderNumber'];
		  $time = $data['ReceivingDateTime'];
		  $text = str_replace("'", "", $text);
		  $query2 = "INSERT INTO sms_inbox(msg, sender, time, flagRead, flagReply) VALUES ('$text', '$notelp', '$time', 0, 0)";
		  query($query2);
		  send($notelp, $msgINBOX);
	  }

   }
   else
   {
      $id = $data['ID'];
      $text = str_replace("'", "", $data['TextDecoded']);
      $query2 = "UPDATE inbox SET processed = 'true' WHERE ID = '$id'";
      query($query2);
	  $notelp = $data['SenderNumber'];
	  $time = $data['ReceivingDateTime'];
      $query2 = "INSERT INTO sms_inbox(msg, sender, time, flagRead, flagReply) VALUES ('$text', '$notelp', '$time', 0, 0)";
	  query($query2);
	  send($notelp, $msgINBOX);
   }

}

// ---------------------- PROSEDUR AUTO RECEIVED TO INBOX END ------------------------------

// ---------------------- PROSEDUR AUTO RECEIVED & REPLY SMS START-------------------------

// command SMS: REG#NAMA#ALAMAT

$query = "SELECT * FROM inbox WHERE (textdecoded LIKE 'REG#%' OR textdecoded LIKE 'FWD#%' OR textdecoded LIKE 'INFO#%')AND processed = 'false'";
$hasil = query($query);

while ($data = fetch_array($hasil))
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

    $query2 = "INSERT INTO booking_registrasi VALUES ('$notelp', '$nama', '$alamat', '$defaultID', '$now')";
    $hasil2 = query($query2);

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
   $hasil2 = query($query2);

   if (num_rows($hasil2) > 0)
   {
   $data2  = fetch_array($hasil2);
   $template = $data2['template'];

   preg_match_all("|\[(.*)\]|U", $template, $string, PREG_PATTERN_ORDER);

   $query2 = "SELECT * FROM sms_data WHERE keyword = '$keyword' AND `key` = '$key'";
   $hasil2 = query($query2);
   if (num_rows($hasil2) > 0)
   {
   $data2 = fetch_array($hasil2);

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
  $hasil2 = query($query2);
  while ($data2 = fetch_array($hasil2))
  {
     send($data2['noTelp'], $pesan." (Dikirim oleh: ".$notelp.")");
  }
  $reply = $msgFWD.$idgroup;
  }
  else $reply = $msgErrorFWD;

}

send($notelp, $reply);

$query2 = "UPDATE inbox SET Processed = 'true' WHERE ID = '$idmsg'";
$hasil2 = query($query2);
}

// ---------------------- PROSEDUR AUTO RECEIVED & REPLY SMS FINISH -------------------------
?>
