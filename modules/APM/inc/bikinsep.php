<?php
include('../../../config.php');
include('../../../init.php');
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/fontawesome.min.css">
    <link href="css/gijgo.min.css" rel="stylesheet" type="text/css" />

    <style>
    .modal-full {
      min-width: 100%;
      margin: 0;
    }
    .modal-full .modal-content {
      min-height: 100vh;
    }
    .modal-fix {
      min-width: 1024px;
      margin: 0;
    }
    .modal-fix .modal-content {
      min-height: 100vh;
    }
    .modal .tab-content {
      min-height: 50vh;
    }
    .nav-pills.nav-wizard > li {
      position: relative;
      overflow: visible;
      border-right: 8px solid transparent;
      border-left: 8px solid transparent;
    }

    .nav-pills.nav-wizard > li + li {
      margin-left: 0;
    }

    .nav-pills.nav-wizard > li:first-child {
      border-left: 0;
    }

    .nav-pills.nav-wizard > li:first-child a {
      border-radius: 5px 0 0 5px;
    }

    .nav-pills.nav-wizard > li:last-child {
      border-right: 0;
    }

    .nav-pills.nav-wizard > li:last-child a {
      border-radius: 0 5px 5px 0;
    }

    .nav-pills.nav-wizard > li a {
      border-radius: 0;
      background-color: #eee;
    }

    .nav-pills.nav-wizard > li:not(:last-child) a:after {
      position: absolute;
      content: "";
      top: 0px;
      right: -20px;
      width: 0px;
      height: 0px;
      border-style: solid;
      border-width: 20px 0 20px 20px;
      border-color: transparent transparent transparent #eee;
      z-index: 150;
    }

    .nav-pills.nav-wizard > li:not(:first-child) a:before {
      position: absolute;
      content: "";
      top: 0px;
      left: -20px;
      width: 0px;
      height: 0px;
      border-style: solid;
      border-width: 20px 0 20px 20px;
      border-color: #eee #eee #eee transparent;
      z-index: 150;
    }

    .nav-pills.nav-wizard > li:hover:not(:last-child) a:after {
      border-color: transparent transparent transparent #aaa;
    }

    .nav-pills.nav-wizard > li:hover:not(:first-child) a:before {
      border-color: #aaa #aaa #aaa transparent;
    }

    .nav-pills.nav-wizard > li:hover a {
      background-color: #aaa;
      color: #fff;
    }

    .nav-pills.nav-wizard > li:not(:last-child) a.active:after {
      border-color: transparent transparent transparent #428bca;
    }

    .nav-pills.nav-wizard > li:not(:first-child) a.active:before {
      border-color: #428bca #428bca #428bca transparent;
    }

    .nav-pills.nav-wizard > li a.active {
      background-color: #428bca;
    }
.form-signin .btn {
  font-size: 160%;
  border-radius: 5rem;
  letter-spacing: .1rem;
  font-weight: bold;
  padding: 1rem;
  transition: all 0.2s;
}

    </style>
    <title>SKDP</title>
  </head>
  <body>
    <div class="px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
      <h1 class="display-2">APM</h1>
      <h3 class="display-6">Anjungan Pasien Mandiri Pelayanan Rawat Jalan</h3>
      <h2 class="display-5"><?php echo $dataSettings['nama_instansi']; ?></h2>
    </div>
    <br><br>
    <div class="container">
      <div class="body">
<form class="form-signin" method="post" action="buildsep.php">
	<?php
    	$no_rujuk = $_GET['rujukan'];
    	date_default_timezone_set('UTC');
		$tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
		$signature = hash_hmac('sha256', ConsID."&".$tStamp, SecretKey, true);
		$encodedSignature = base64_encode($signature);
		$ch = curl_init();
		$headers = array(
		'X-cons-id: '.ConsID.'',
		'X-timestamp: '.$tStamp.'' ,
		'X-signature: '.$encodedSignature.'',
		'Content-Type:application/json',
		);
		curl_setopt($ch, CURLOPT_URL, BpjsApiUrl."Rujukan/".$no_rujuk);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$content = curl_exec($ch);
		$err = curl_error($ch);
		curl_close($ch);
		$bri = json_decode($content, true);
        $meta = $bri['metaData']['code'];
        $mets = $bri['metaData']['message'];
        if ($meta == "200") {
          $status = $bri['response']['rujukan']['peserta']['statusPeserta']['keterangan'];
          $noruj = $bri['response']['rujukan']['noKunjungan'];
          $kelas = $bri['response']['rujukan']['peserta']['hakKelas']['keterangan'];
          $klask = $bri['response']['rujukan']['peserta']['hakKelas']['kode'];
          $nokar = $bri['response']['rujukan']['peserta']['noKartu'];
          $polik = $bri['response']['rujukan']['poliRujukan']['kode'];
          $polin = $bri['response']['rujukan']['poliRujukan']['nama'];
          $plynk = $bri['response']['rujukan']['pelayanan']['kode'];
          $plynn = $bri['response']['rujukan']['pelayanan']['nama'];
          $diagk = $bri['response']['rujukan']['diagnosa']['kode'];
          $diagn = $bri['response']['rujukan']['diagnosa']['nama'];
          $ppruj = $bri['response']['rujukan']['provPerujuk']['kode'];
          $nmruj = $bri['response']['rujukan']['provPerujuk']['nama'];
          $tglkn = $bri['response']['rujukan']['tglKunjungan'];
          $jnspe = $bri['response']['rujukan']['peserta']['jenisPeserta']['keterangan'];
         }else{
          echo "<script>alert('".$mets."');window.location = '".URL."/modules/APM/inc/ceksep.php'</script>";
         }
          if($polik != ""){
          date_default_timezone_set('UTC');
          $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
          $signature = hash_hmac('sha256', ConsID."&".$tStamp, SecretKey, true);
          $encodedSignature = base64_encode($signature);
          $ch = curl_init();
          $headers = array(
            'X-cons-id: '.ConsID.'',
            'X-timestamp: '.$tStamp.'' ,
            'X-signature: '.$encodedSignature.'',
            'Content-Type:application/json',
          );
          curl_setopt($ch, CURLOPT_URL, BpjsApiUrl."referensi/dokter/pelayanan/".$plynk."/tglPelayanan/".$date."/Spesialis/".$polik);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_TIMEOUT, 3);
          curl_setopt($ch, CURLOPT_HTTPGET, 1);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          $content = curl_exec($ch);
          $err = curl_error($ch);
          curl_close($ch);
          $dpjp = json_decode($content, true);
          }
		 $sql = "SELECT pasien.nm_pasien , reg_periksa.no_rawat , reg_periksa.no_rkm_medis , pasien.tgl_lahir , pasien.no_tlp , pasien.no_peserta , pasien.jk , poliklinik.nm_poli , poliklinik.kd_poli
          FROM reg_periksa , pasien , poliklinik WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.no_rkm_medis = '{$_GET['no_mr']}' AND reg_periksa.tgl_registrasi = '{$date}'";
	      $data = query($sql);
		  $b = fetch_array($data);
		 $drdr = fetch_array(query("SELECT dokter.kd_dokter , dokter.nm_dokter from dokter , jadwal WHERE dokter.kd_dokter = jadwal.kd_dokter AND jadwal.hari_kerja ='{$namahari}' AND jadwal.kd_poli = '{$b['kd_poli']}'"));
         foreach($dpjp['response']['list'] as $kode):
         $int1 = $kode['kode'] == "9102" && $drdr['0'] == "DR00015"; //dr aris
         $int2 = $kode['kode'] == "9102" && $drdr['0'] == "D0000068"; // dr arif
         $bed1 = $kode['kode'] == "2518" && $drdr['0'] == "D0000045"; // dr asnal
         $bed2 = $kode['kode'] == "9201" && $drdr['0'] == "DR00025"; // dr nanda
         $bed3 = $kode['kode'] == "9107" && $drdr['0'] == "DR00002"; // dr priha
         $ana1 = $kode['kode'] == "9062" && $drdr['0'] == "D0000039"; // dr yusuf
         $ana2 = $kode['kode'] == "9129" && $drdr['0'] == "DR00016"; // dr iin
         $obg1 = $kode['kode'] == "329928" && $drdr['0'] == "D0000066"; // dr redi
         $obg2 = $kode['kode'] == "9271" && $drdr['0'] == "DR00014"; // dr eny
         $jiwa = $kode['kode'] == "198987" && $drdr['0'] == "D0000046"; // dr danu
         $jant = $kode['kode'] == "336737" && $drdr['0'] == "D0000069"; // dr surya
         $mata = $kode['kode'] == "211337" && $drdr['0'] == "D0000052"; // dr arya
         $gig1 = $kode['kode'] == "99094" && $drdr['0'] == "D0000040"; // dr basoro
         $gig2 = $kode['kode'] == "9200" && $drdr['0'] == "DR00024"; // dr tami
         $tht1 = $kode['kode'] == "284453" && $drdr['0'] == "D0000056"; // dr anto
         $klt1 = $kode['kode'] == "9050" && $drdr['0'] == "DR00007"; // dr kristina
         $ort1 = $kode['kode'] == "9132" && $drdr['0'] == "DR00003"; // dr hendra
         $sar1 = $kode['kode'] == "2529" && $drdr['0'] == "D0000060"; // dr darma
         $bdm1 = $kode['kode'] == "329009" && $drdr['0'] == "D0000065"; // dr barra
         $gnd1 = $kode['kode'] == "274754" && $drdr['0'] == "D0000055"; // dr juli
          switch(true){
            case $int1:echo '<input type="hidden" value="9102" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $int2:echo '<input type="hidden" value="9102" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $bed1:echo '<input type="hidden" value="2518" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $bed2:echo '<input type="hidden" value="9201" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $bed3:echo '<input type="hidden" value="9107" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $ana1:echo '<input type="hidden" value="9062" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $ana2:echo '<input type="hidden" value="9129" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $obg1:echo '<input type="hidden" value="329928" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $obg2:echo '<input type="hidden" value="9271" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $jiwa:echo '<input type="hidden" value="198987" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $jant:echo '<input type="hidden" value="336737" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $mata:echo '<input type="hidden" value="211337" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $gig1:echo '<input type="hidden" value="99094" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $gig2:echo '<input type="hidden" value="9200" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $tht1:echo '<input type="hidden" value="284453" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $klt1:echo '<input type="hidden" value="9050" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $ort1:echo '<input type="hidden" value="9132" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $sar1:echo '<input type="hidden" value="2529" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $bdm1:echo '<input type="hidden" value="329009" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
            case $gnd1:echo '<input type="hidden" value="274754" id="dpjp" name="dpjp"><input type="hidden" value="'.$drdr['1'].'" id="dpjp" name="nmdpjp">';break;
          }
          endforeach;
          $int = $polik == "INT" && $b['kd_poli'] == "U0002";
         $jan = $polik == "JAN" && $b['kd_poli'] == "U0012";
		 $mat = $polik == "MAT" && $b['kd_poli'] == "U0003";
         $obg = $polik == "OBG" && $b['kd_poli'] == "U0001";
         $tht = $polik == "THT" && $b['kd_poli'] == "U0009";
         $ana = $polik == "ANA" && $b['kd_poli'] == "U0004";
         $bed = $polik == "BED" && $b['kd_poli'] == "U0005";
         $sya = $polik == "SAR" && $b['kd_poli'] == "U0020";
         $gig = $polik == "GIG" && $b['kd_poli'] == "U0017";
         $jiw = $polik == "JIW" && $b['kd_poli'] == "U0036";
         $ort = $polik == "ORT" && $b['kd_poli'] == "U0023";
         $kul = $polik == "KLT" && $b['kd_poli'] == "U0016";
         $end = $polik == "GND" && $b['kd_poli'] == "U0041";
         $bdm = $polik == "BDM" && $b['kd_poli'] == "U0042";
         switch(true){
           case $int: ;break;
           case $jan: ;break;
           case $mat: ;break;
           case $obg: ;break;
           case $tht: ;break;
           case $bed: ;break;
           case $jiw: ;break;
           case $sya: ;break;
           case $ana: ;break;
           case $end: ;break;
           case $ort: ;break;
           case $kul: ;break;
           case $gig: ;break;
           case $bdm: ;break;
           default:echo '<script>alert("Nomor Rujukan Yang Anda Masukkan Tidak Sama Dengan Poli Tujuan Anda. Silahkan hubungi petugas!!");window.location = "'.URL.'/modules/APM/inc/ceksep.php"</script>';break;
         }



    ?>
						<div class="body">

                            <div class="row clearfix">
                              <div class="col-md-2">
                        		<div class="form-group">
                                  <div class="form-line">
                          			<label for="norm">No Rawat</label>
                          			<input type="text" class="form-control" name="no_rawat" value="<?php echo $b['no_rawat']; ?>" readonly>
                                  </div>
                        		</div>
                              </div>
                              <div class="col-md-2">
                                <div class="form-group">
                                  <div class="form-line">
                                	<label for="norm">No Rekam Medis</label>
                                    <input type="text" class="form-control" name="norm" value="<?php echo $b['no_rkm_medis']; ?>" readonly>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-3">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Nama</label>
                                    <input type="text" class="form-control" name="nmps" value="<?php echo $b['nm_pasien']; ?>" readonly>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-1">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">JK</label>
                                    <input type="text" class="form-control" name="jk" value="<?php echo $b['jk']; ?>" readonly>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-2">
                        		<div class="form-group">
                                  <div class="form-line">
                          			<label for="norm">Tanggal Lahir</label>
                          			<input type="text" class="form-control" name="tgllhr" value="<?php echo $b['tgl_lahir']; ?>" readonly>
                        		  </div>
                                </div>
                              </div>
                              <div class="col-md-2">
                                <div class="form-group">
                                  <div class="form-line">
                                	<label for="norm">No Telp</label>
                                    <input type="number" class="form-control" name="notlp" required minlength=8 maxlength=13 value="<?php if($b['no_tlp'] == ""){echo "000000000000";}{echo $b['no_tlp'];} ?>">
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-3">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">No Peserta</label>
                                    <input type="text" class="form-control" name="nops" value="<?php echo $b['no_peserta']; ?>" readonly>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-2" style="display:none;">
                        		<div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Kode PPK</label>
                                    <input type="text" class="form-control" name="ppk" value="<?php echo $dataSettings['kode_ppk']; ?>" readonly>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-3" style="display:none;">
                        		<div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">PPK Pelayanan</label>
                                    <input type="text" class="form-control" name="nmrs" value="<?php echo $dataSettings['nama_instansi']; ?>" readonly>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-3">
                        		<div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Poli</label>
                                    <input type="text" class="form-control" name="nmpoli" value="<?php echo $b['nm_poli']; ?>" readonly>
                                  </div>
                                </div>
                              </div>
                              <?php $sql = "SELECT no_antrian FROM skdp_bpjs WHERE no_rkm_medis = '{$b['no_rkm_medis']}' and tanggal_datang = '{$date}'";
									$skdp = query($sql);
									$sksk = fetch_assoc($skdp);
							  ?>
                              <div class="col-md-2">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">No SKDP</label>
                                    <input type="text" class="form-control" name="skdp" value="<?php if($sksk['no_antrian'] == ""){echo $nonbooking;}{echo $sksk['no_antrian'];};?>" placeholder="No SKDP">
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                	<div class="form-line">
                                      <label for="norm">Nama Poli Tujuan</label>
                                      <input type="text" class="form-control" name="nmpoli" value="<?php echo $polin; ?>" readonly>
                                    </div>
                                </div>
                              </div>
                              <div class="col-md-2">
                              	<div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Tanggal SEP</label>
                                    <input type="text" class="form-control" name="tglsep" value="<?php echo $date; ?>" readonly>
                              	  </div>
                                </div>
                              </div>
                              <div class="col-md-2">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Tanggal Rujuk</label>
                                    <input type="text" class="form-control" name="tglrjk" value="<?php echo $tglkn; ?>" readonly>
                              	  </div>
                                </div>
                              </div>
                      		</div>
                       		<div class="row clearfix">
                              <div class="col-md-2">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Status</label>
                                    <input type="text" class="form-control" name="stts" value="<?php echo $status; ?>" readonly>
                              	  </div>
                                </div>
                              </div>
                              <div class="col-md-1" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label>Kode</label>
                                    <input type="text" class="form-control" name="kkls" value="<?php echo $klask; ?>" readonly>
                                    </div>
                                </div>
                              </div>
                              <div class="col-md-2">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label>Kelas</label>
                                    <input type="text" class="form-control" name="kls" value="<?php echo $kelas; ?>" readonly>
                              	  </div>
                                </div>
                              </div>
                              <div class="col-md-3">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Peserta</label>
                                    <input type="text" class="form-control" name="psrt" value="<?php echo $jnspe; ?>" readonly>
                              	  </div>
                                </div>
                              </div>
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Jenis Pelayanan</label>
                                    <input type="hidden" class="form-control" name="kdpl" value="<?php echo $plynk; ?>" readonly>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-5">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Nama Perujuk</label>
                                    <input type="text" class="form-control" name="nmruj" value="<?php echo $nmruj; ?>" readonly>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Kode Diagnosa</label>
                                    <input type="text" class="form-control" name="kddx" value="<?php echo $diagk; ?>" readonly>
                              	  </div>
                                </div>
                              </div>
                              <div class="col-md-4" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Nama Diagnosa</label>
                                    <input type="text" class="form-control" name="nmdx" value="<?php echo $diagn; ?>" readonly>
                              	  </div>
                                </div>
                              </div>
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Kode Poli Tujuan</label>
                                    <input type="text" class="form-control" name="kdpoli"  id="kdpoli" value="<?php echo $polik; ?>" readonly>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">PPK Perujuk</label>
                                    <input type="text" class="form-control" name="ppruj" value="<?php echo $ppruj; ?>" readonly>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Eksekutif</label>
                                    <select class="form-control" id="eks" name="eks" readonly>
                                      <option value="0" selected>0. Tidak</option>
                                      <option value="1">1. Ya</option>
                                    </select>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">COB</label>
                                    <select class="form-control" id="cob" name="cob" readonly>
                                      <option value="0" selected>0. Tidak</option>
                                      <option value="1">1. Ya</option>
                                    </select>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Katarak</label>
                                    <select class="form-control" id="katara" name="ktrk" readonly>
                                      <option value="0" selected>0. Tidak</option>
                                      <option value="1">1. Ya</option>
                                    </select>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Suplesi</label>
                                    <select class="form-control" id="suple" name="suplesi" readonly>
                                      <option value="0" selected>0. Tidak</option>
                                      <option value="1">1. Ya</option>
                                    </select>
                                  </div>
                                </div>
                              </div>
                              <div class="form-group col-md-2 col-sm-2" style="display:none;">
                                <label for="norm">No Rujukan</label>
                                <input type="text" class="form-control" name="no_rujuk" value="<?php echo $no_rujuk; ?>" readonly>
                              </div>
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label>Faskes</label>
                                    <select name="fsks" id="faskes" class="form-control" readonly>
                                      <option value="1" selected>1. Faskes 1</option>
                                      <option value="2">2. Faskes 2(RS)</option>
                                    </select>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Tgl Pulang</label>
                                    <input type="text" class="tglplg form-control" name="tglplg" value="0000-00-00" readonly>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">SEP Suplesi</label>
                                    <input type="text" class="form-control" name="sepsup" value="" placeholder="SEP Suplesi">
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Laka Lantas</label>
                                    <select class="form-control" name="lkln" readonly>
                                      <option value="0" selected>0. Tidak</option>
                                      <option value="1">1. Ya</option>
                                    </select>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Penjamin Laka</label>
                                    <select class="form-control" name="pjlk" readonly>
                                      <option value="" selected>Tidak Ada</option>
                                      <option value="1">Jasa Raharja</option>
                                      <option value="2">BPJS Ketenagakerjaan</option>
                                      <option value="3">TASPEN PT</option>
                                      <option value="4">ASABRI PT</option>
                                    </select>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Tgl Kejadian</label>
                                    <input type="text" class="tglkkl form-control" name="tglkkl" value="0000-00-00" readonly>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Keterangan</label>
                                    <input type="text" class="form-control" name="ktrg" value="" placeholder="Keterangan" readonly>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Propinsi</label>
                                    <input type="text" class="form-control" name="prop" value="" placeholder="Propinsi" readonly>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Kabupaten</label>
                                    <input type="text" class="form-control" name="kbpt" value="" placeholder="Kabupaten" readonly>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Kecamatan</label>
                                    <input type="text" class="form-control" name="kec" value="" placeholder="Kecamatan">
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-4" style="display:none;">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Catatan</label>
                                    <input type="text" class="form-control" name="cttn" value="" placeholder="Catatan">
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="form-row mt-5">
                              <div class="col-md-4"></div>
                              <div class="col-md-4">
                              <div class="form-group">
                                <input type="submit" class="btn btn-lg btn-primary btn-block text-uppercase" name="" value="SIMPAN SEP">
                              </div>
                              </div>
                              <div class="col-md-4"></div>
                            </div>
                          </form>
                		</div>

      </div>
    </div>
    <div class="pricing-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center text-danger">
      <h3 class="display-6">Silahkan hubungi petugas jika anda mengalami kesulitan.</h3>
    </div>

  </body>
</html>
