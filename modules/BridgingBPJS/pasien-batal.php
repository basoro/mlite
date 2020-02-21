<?php
if(isset($_GET['no_sep']) && $_GET['no_sep'] !=="") {
  $sup = new StdClass();
  $sup->noSep = $_GET['no_sep'];
  $sup->user = $_SESSION['username'];

  $data = new StdClass();
  $data->request = new StdClass();
  $data->request->t_sep = $sup;

  $sep = json_encode($data);

  date_default_timezone_set('UTC');
  $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
  $signature = hash_hmac('sha256', ConsID."&".$tStamp, SecretKey, true);
  $encodedSignature = base64_encode($signature);
  $ch = curl_init();
  $headers = array(
    'X-cons-id: '.ConsID.'',
    'X-timestamp: '.$tStamp.'' ,
    'X-signature: '.$encodedSignature.'',
    'Content-Type:Application/x-www-form-urlencoded',
  );
  curl_setopt($ch, CURLOPT_URL, BpjsApiUrl."SEP/Delete");
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 3);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $sep);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $content = curl_exec($ch);
  $err = curl_error($ch);

  curl_close($ch);
  $result = json_decode($content,true);
  $meta = $result['metaData']['code'];
  $mets = $result['metaData']['message'];
  if ($meta == "200") {

    ?>
      <script>confirm('Apakah anda ingin menghapus SEP pasien dengan nomor jaminan <?php echo $_GET['no_sep']; ?>')</script>
    <?php

    $insert = query("DELETE FROM bridging_sep WHERE no_sep = '".$_GET['no_sep']."'");
    if($insert) {
      redirect(URL.'/index.php?module=BridgingBPJS&page=pasien_batal');
    }
  	}else {
    ?>
      <script>alert('<?php echo "Pesan : ".$mets; ?>')</script>
    <?php
      redirect(URL.'/index.php?module=BridgingBPJS&page=pasien_batal');
    };

  }
  ?>

<div class="card">
    <div class="header">
      <h2>Pasien Batal</h2>
    </div>
    <div class="body">
      <table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">
        <thead>
          <tr>
            <th>No RM</th>
            <th>Nama</th>
            <th>Poli</th>
            <th>Tanggal</th>
            <th>No Peserta</th>
            <th>No SEP</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php $hapus = query("SELECT reg_periksa.no_rkm_medis , pasien.nm_pasien , poliklinik.nm_poli , reg_periksa.tgl_registrasi , reg_periksa.no_rawat , pasien.no_peserta
        FROM reg_periksa , poliklinik , pasien WHERE reg_periksa.kd_poli = poliklinik.kd_poli AND
        reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.stts = 'Batal' AND reg_periksa.kd_pj IN ('A02','BPJ') AND reg_periksa.tgl_registrasi = CURRENT_DATE()");
              while($row = fetch_array($hapus)) { ?>
                <tr>
                  <td><?php echo $row['no_rkm_medis'];?></td>
                  <td><?php echo SUBSTR($row['nm_pasien'], 0, 15).' ...';?></td>
                  <td><?php echo $row['nm_poli'];?></td>
                  <td><?php echo $row['tgl_registrasi'];?></td>
                  <td><?php echo $row['no_peserta'];?></td>
                  <td><?php $sep = fetch_array(query("SELECT no_sep from bridging_sep where no_rawat = '".$row['no_rawat']."'"));echo $sep['no_sep'];?></td>
                  <td><a href="<?php echo URL; ?>/index.php?module=BridgingBPJS&page=pasien_batal&no_sep=<?php echo $sep['no_sep'];?>" class="btn btn-danger">Hapus SEP</a></td>
                </tr>
              <?php } ?>
        </tbody>
      </table>
    </div>
</div>
