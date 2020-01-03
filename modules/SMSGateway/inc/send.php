<?php

function send($notelp, $msgReply)
{
   $query = "SELECT nm_pasien AS nama FROM pasien WHERE no_tlp = '$notelp'";
   $hasil = query($query);
   $data  = fetch_array($hasil);
   $nama  = strtoupper($data['nama']);

   $msgReply = str_replace('[nama]', $nama, $msgReply);
   $msgReply = str_replace("\r"," ",$msgReply);
   $msgReply = str_replace("\n","",$msgReply);
   $msgReply = str_replace("'","",$msgReply);
   $msgReply = str_replace('"','',$msgReply);

   if (strlen($msgReply)<=160)
   {
      $query = "INSERT INTO outbox (DestinationNumber, TextDecoded, CreatorID) VALUES ('$notelp', '$msgReply', 'Gammu 16')";
      query($query);

   }
   else
   {
   $jmlSMS = ceil(strlen($msgReply)/153);
   $pecah  = str_split($msgReply, 153);

   $query = "SHOW TABLE STATUS LIKE 'outbox'";
   $hasil = query($query);
   $data  = fetch_array($hasil);
   $newID = $data['Auto_increment'];

   for ($i=1; $i<=$jmlSMS; $i++)
   {
      $udh = "050003A7".sprintf("%02s", $jmlSMS).sprintf("%02s", $i);
      $msg = $pecah[$i-1];
      if ($i == 1) $query = "INSERT INTO outbox (DestinationNumber, UDH, TextDecoded, ID, MultiPart, CreatorID)
	                         VALUES ('$notelp', '$udh', '$msg', '$newID', 'true', 'Gammu 1.25')";
	    else $query = "INSERT INTO outbox_multipart(UDH, TextDecoded, ID, SequencePosition)
	                 VALUES ('$udh', '$msg', '$newID', '$i')";
      query($query);

   }
   }
}

?>
