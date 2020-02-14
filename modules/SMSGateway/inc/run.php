
<?php

// koneksi ke mysql
include "../../../config.php";
include "send.php";

// mencatat tanggal sekarang
$tgl = date("Y-m-d H:i:s");

// ---------------------- PROSEDUR AUTO RECEIVED TO INBOX START ------------------------------

$query = "SELECT * FROM inbox
          WHERE textdecoded NOT LIKE 'REG#%' AND textdecoded NOT LIKE 'INFO#%' AND (UDH = '' OR UDH LIKE '%01') AND processed = 'false'";

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
		  //send($notelp, 'Terimakasih sudah mengirim SMS ke kami');
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
	  //send($notelp, 'Terimakasih sudah mengirim SMS ke kami');
   }

}

// ---------------------- PROSEDUR AUTO RECEIVED TO INBOX END ------------------------------

// ---------------------- PROSEDUR AUTO RECEIVED & REPLY SMS START-------------------------

// command SMS: REG#NAMA#ALAMAT

$query = "SELECT * FROM inbox WHERE (textdecoded LIKE 'REG#%' OR textdecoded LIKE 'INFO#%') AND processed = 'false'";
$hasil = query($query);

while ($data = fetch_array($hasil))
{
$idmsg = $data['ID'];
$notelp = substr_replace($data['SenderNumber'],'0',0,3);
$split = explode("#", $data['TextDecoded']);
$command = strtoupper($split[0]);

$now = date("Y-m-d");

if ($command == "REG")
{
  if (count($split) == 5)
  {
    $no_rkm_medis = $split[1];
    $kd_poli = $split[2];
    $tgl_registrasi = $split[3];
    $cara_bayar = $split[4];


    //cek biar ga double datanya
    $cek = fetch_array(query("SELECT no_reg FROM booking_registrasi WHERE no_rkm_medis='$no_rkm_medis' AND tanggal_periksa='$tgl_registrasi'"));
    $cek_hp = fetch_array(query("SELECT no_tlp AS nama FROM pasien WHERE no_rkm_medis = '$no_rkm_medis' AND no_tlp = '$notelp'"));
    //if($cek_hp['no_tlp'] == '') {
    //  $reply = 'Anda tidak terdaftar sebagai pasien. Cek nomor kartu dan telepon anda';
    //} else
    //if($cek['no_reg'] > 0){
    //  $reply = 'Anda sudah terdaftar dalam antrian. Silahkan pilih hari lain atau hubungi petugas.';
    //} else {
    if($cek == ''){
      $tentukan_hari=date('D',strtotime($tgl_registrasi));
      $day = array(
        'Sun' => 'AKHAD',
        'Mon' => 'SENIN',
        'Tue' => 'SELASA',
        'Wed' => 'RABU',
        'Thu' => 'KAMIS',
        'Fri' => 'JUMAT',
        'Sat' => 'SABTU'
      );
      $hari=$day[$tentukan_hari];

      $sql = "SELECT a.kd_dokter, c.nm_dokter, a.kuota FROM jadwal a, poliklinik b, dokter c WHERE a.kd_poli = b.kd_poli AND a.kd_dokter = c.kd_dokter AND a.kd_poli = '$kd_poli' AND a.hari_kerja LIKE '%$hari%'";

      $result = fetch_assoc(query($sql));

        $check_kuota = fetch_assoc(query("SELECT COUNT(*) AS count FROM booking_registrasi WHERE kd_poli = '$kd_poli' AND tanggal_periksa = '$tgl_registrasi'"));
        $curr_count = $check_kuota['count'];
        $curr_kuota = $result['kuota'];
        $online = $curr_kuota / LIMIT;
        $kd_dokter = $result['kd_dokter'];

        if($curr_count > $online) {
          $reply = 'Limit pendaftaran online telah terpenuhi. Silahkan pilih hari lain.';
        } else {

                  $get_pasien = fetch_assoc(query("SELECT * FROM pasien WHERE no_rkm_medis = '$no_rkm_medis'"));
      $get_kd_pj = fetch_assoc(query("SELECT * FROM penjab WHERE png_jawab LIKE '%$cara_bayar%'"));

            //mencari no reg terakhir
            $no_reg_akhir = fetch_assoc(query("SELECT max(no_reg) FROM booking_registrasi WHERE kd_dokter='$kd_dokter' and tanggal_periksa='$tgl_registrasi'"));
        $no_urut_reg = substr($no_reg_akhir[0], 0, 3);
        $no_reg = sprintf('%03s', ($no_urut_reg + 1));

               $insert = query("
            INSERT INTO booking_registrasi
            SET no_rkm_medis    = '$no_rkm_medis',
                tanggal_periksa = '$tgl_registrasi',
                kd_poli         = '$kd_poli',
                kd_dokter       = '$kd_dokter',
                kd_pj           = '{$get_kd_pj['kd_pj']}',
                no_reg          = '$no_reg',
                tanggal_booking = '$date',
                jam_booking     = '$time',
                waktu_kunjungan = '$date_time',
                limit_reg       = '1',
                status          = 'Belum'
        ");

      $reply = 'Terimakasih [nama], registrasi berhasil. No. Antri: '.$no_reg.', Tgl berobat: '.$tgl_registrasi.'.  Tunjukkan SMS pada petugas.';
      }
    }
  }
  else $reply = 'Format REG salah. Format yang benar REG#NO_KARTU#KODE_POLI#TGL_BEROBAT#CARA_BAYAR. Untuk info kode poli INFO#POLI.';
} else ($command == "INFO") {
  if (count($split) == 2) {
    $keyword = strtoupper($split[1]);

    $query = "SELECT * FROM poliklinik WHERE nm_poli LIKE 'POLI%'";
    $hasil = query($query);

    $result = '';
    if (num_rows($hasil) > 0) {
      while ($row = fetch_array($hasil)) {
        $result .= $row['kd_poli'].': '.$row['nm_poli'].', ';
      }
      $reply = $result;
    } else {
      $reply = 'Data tidak ditemukan';
    }
  } else {
    $reply = 'Format SMS Info salah';
  }
}

send($notelp, $reply);

$query2 = "UPDATE inbox SET Processed = 'true' WHERE ID = '$idmsg'";
$hasil2 = query($query2);
}

// ---------------------- PROSEDUR AUTO RECEIVED & REPLY SMS FINISH -------------------------
?>
