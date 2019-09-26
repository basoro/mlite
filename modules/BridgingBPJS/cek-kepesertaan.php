<div class="body">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=BridgingBPJS">
                <i class="material-icons">home</i> <span class="hidden-xs">Index</span>
            </a>
        </li>
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=BridgingBPJS&page=data_sep">
                <i class="material-icons">assignment</i> <span class="hidden-xs">Data SEP</span>
            </a>
        </li>
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=BridgingBPJS&page=pasien_batal">
                <i class="material-icons">clear</i> <span class="hidden-xs">Pasien Batal</span>
            </a>
        </li>
        <li role="presentation" class="active">
            <a href="<?php echo URL; ?>/?module=BridgingBPJS&page=cek_kepesertaan">
                <i class="material-icons">assignment_ind</i> <span class="hidden-xs">Cek Kepesertaan</span>
            </a>
        </li>
    </ul>
    <div class="content m-t-30">
      <?php if($_SERVER['REQUEST_METHOD'] == "POST"){
if($_POST['nops'] !== ""){
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
             curl_setopt($ch, CURLOPT_URL, BpjsApiUrl."Peserta/nokartu/".$_POST['nops']."/tglSEP/".$date);
             curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
             curl_setopt($ch, CURLOPT_TIMEOUT, 3);
             curl_setopt($ch, CURLOPT_HTTPGET, 1);
             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
             $content = curl_exec($ch);
             $err = curl_error($ch);

             curl_close($ch);
             $result = json_decode($content, true);
           $status = $result['response']['peserta']['statusPeserta']['keterangan'];
$nama = $result['response']['peserta']['nama'];
             $tgllhr = $result['response']['peserta']['tglLahir'];
     $kelas = $result['response']['peserta']['hakKelas']['keterangan'];
             $peserta = $result['response']['peserta']['jenisPeserta']['keterangan'];
$prov = $result['response']['peserta']['provUmum']['nmProvider'];
               echo "<h5 class='card-title'>Data Kepesertaan</h5>";
                 echo "<p class='card-text'>Nama Peserta : ".$nama."</p>";
                 echo "<p class='card-text'>Status Peserta : ".$status."</p>";
                 echo "<p class='card-text'>Kelas Peserta : ".$kelas."</p>";
                 echo "<p class='card-text'>Tanggal Lahir Peserta : ".$tgllhr."</p>";
                 echo "<p class='card-text'>Peserta : ".$peserta."</p>";
 echo "<p class='card-text'>Faskes Peserta : ".$prov."</p>";
}else{
             if($_POST['nik'] !== ""){
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
             curl_setopt($ch, CURLOPT_URL, BpjsApiUrl."Peserta/nik/".$_POST['nik']."/tglSEP/".$date);
             curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
             curl_setopt($ch, CURLOPT_TIMEOUT, 3);
             curl_setopt($ch, CURLOPT_HTTPGET, 1);
             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
             $content = curl_exec($ch);
             $err = curl_error($ch);

             curl_close($ch);
             $result = json_decode($content, true);
           $status = $result['response']['peserta']['statusPeserta']['keterangan'];
$nama = $result['response']['peserta']['nama'];
             $tgllhr = $result['response']['peserta']['tglLahir'];
     $kelas = $result['response']['peserta']['hakKelas']['keterangan'];
             $peserta = $result['response']['peserta']['jenisPeserta']['keterangan'];
$prov = $result['response']['peserta']['provUmum']['nmProvider'];
               echo "<h5 class='card-title'>Data Kepesertaan</h5>";
                 echo "<p class='card-text'>Nama Peserta : ".$nama."</p>";
                 echo "<p class='card-text'>Status Peserta : ".$status."</p>";
                 echo "<p class='card-text'>Kelas Peserta : ".$kelas."</p>";
                 echo "<p class='card-text'>Tanggal Lahir Peserta : ".$tgllhr."</p>";
                 echo "<p class='card-text'>Peserta : ".$peserta."</p>";
 echo "<p class='card-text'>Faskes Peserta : ".$prov."</p>";
             }}}?>

             <form method="POST" action="">
               <div class="form-group form-float">
                   <div class="form-line">
                       <input type="text" class="form-control"name="nops">
                         <label class="form-label">Masukkan No Peserta</label>
                     </div>
                 </div>
               <div class="form-group form-float">
                   <div class="form-line">
                       <input type="text" class="form-control"name="nik">
                         <label class="form-label">Masukkan NIK Peserta</label>
                     </div>
                 </div>
               <button type="submit" class="form-control btn bg-indigo waves-effect">CEK</button>
             </form>
    </div>
</div>
