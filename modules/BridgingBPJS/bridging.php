<div class="card">
    <div class="header">
        <h2>
            BRIDGING RALAN
        </h2>
    </div>
    <?php $action = isset($_GET['action'])?$_GET['action']:null;
    if(!$action){?>
    <div class="body">
      <table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
        <thead>
          <tr>
            <th>No MR</th>
            <th>Nama</th>
            <th>Poli</th>
            <th>Jenis Bayar</th>
            <th>Bridging</th>
            <th>Cetak SEP</th>
          </tr>
        </thead>
        <tbody>
          <?php
              $sql = "SELECT reg_periksa.no_rkm_medis , pasien.nm_pasien , pasien.tgl_lahir , reg_periksa.no_rawat , poliklinik.nm_poli , penjab.png_jawab FROM reg_periksa , pasien , poliklinik , penjab WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_pj = penjab.kd_pj AND reg_periksa.kd_poli NOT IN('IGDK') AND reg_periksa.kd_poli = poliklinik.kd_poli AND tgl_registrasi = '{$date}' AND reg_periksa.kd_pj != 'A01'";
              if($role == 'Medis' || $role == 'Paramedis') {
                $sql .= " AND poliklinik.kd_poli = '$jenis_poli'";
              }
              $list = query($sql);
              while($a = fetch_assoc($list)) {

          ?>
          <tr>
            <td><?php echo $a['no_rkm_medis']; $sql = "SELECT no_rawat as jml FROM bridging_sep WHERE no_rawat = '{$a['no_rawat']}'";
                  $ceksep = query($sql);
                  if(num_rows($ceksep) == 1)
                  {
                    echo "<i class='material-icons'>done</i>";
                  }
                    else
                  {
                    echo "<i class='material-icons'>warning</i>";
                  }?></td>
            <td><?php echo SUBSTR($a['nm_pasien'], 0, 15).' ...'; ?></td>
            <td><?php echo $a['nm_poli']; ?></td>
            <td><?php echo $a['png_jawab']; ?></td>
            <td><a class="btn btn-primary" href="<?php echo URL; ?>/index.php?module=BridgingBPJS&page=index&action=bridging&no_rawat=<?php echo $a['no_rawat'];?>">Cek Bridging PCare</a></td>
            <td><a class="btn btn-primary" href="<?php echo URL; ?>/modules/BridgingBPJS/cetaksep.php?action=cetak&no_rawat=<?php echo $a['no_rawat']; ?>" target="_BLANK">Cetak</a></td>
          </tr>
            <?php } ?>
        </tbody>
      </table>
    </div>
<?php } ?>
    <?php if($action == "bridging"){ ?>
    <?php
    if(isset($_POST['build_sep'])) {
      $sup = new StdClass();
      $sup->noKartu = $_POST['nops'];
      $sup->tglSep = $_POST['tglsep'];
      $sup->ppkPelayanan = $_POST['ppk'];
      $sup->jnsPelayanan = $_POST['kdpl'];
      $sup->klsRawat = $_POST['kkls'];
      $sup->noMR = $_POST['norm'];
      $sup->rujukan = new StdClass();
      $sup->rujukan->asalRujukan = $_POST['fsks'];
      $sup->rujukan->tglRujukan = $_POST['tglrjk'];
      $sup->rujukan->noRujukan = $_POST['no_rujuk'];
      $sup->rujukan->ppkRujukan = $_POST['ppruj'];
      $sup->catatan = $_POST['cttn'];
      $sup->diagAwal = $_POST['kddx'];
      $sup->poli = new StdClass();
      $sup->poli->tujuan = $_POST['kdpoli'];
      $sup->poli->eksekutif = $_POST['eks'];
      $sup->cob = new StdClass();
      $sup->cob->cob = $_POST['cob'];
      $sup->katarak = new StdClass();
      $sup->katarak->katarak = $_POST['ktrk'];
      $sup->jaminan = new StdClass();
      $sup->jaminan->lakaLantas = $_POST['lkln'];
      $sup->jaminan->penjamin = new StdClass();
      $sup->jaminan->penjamin->penjamin = $_POST['pjlk'];
      $sup->jaminan->penjamin->tglKejadian = $_POST['tglkkl'];
      $sup->jaminan->penjamin->keterangan = $_POST['ktrg'];
      $sup->jaminan->penjamin->suplesi = new StdClass();
      $sup->jaminan->penjamin->suplesi->suplesi = $_POST['suplesi'];
      $sup->jaminan->penjamin->suplesi->noSepSuplesi = $_POST['sepsup'];
      $sup->jaminan->penjamin->suplesi->lokasiLaka = new StdClass();
      $sup->jaminan->penjamin->suplesi->lokasiLaka->kdPropinsi = $_POST['prop'];
      $sup->jaminan->penjamin->suplesi->lokasiLaka->kdKabupaten = $_POST['kbpt'];
      $sup->jaminan->penjamin->suplesi->lokasiLaka->kdKecamatan = $_POST['kec'];
      $sup->skdp = new StdClass();
      $sup->skdp->noSurat = $_POST['skdp'];
      $sup->skdp->kodeDPJP = $_POST['dpjp'];
      $sup->noTelp = $_POST['notlp'];
      $sup->user = 'loket1';

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
      curl_setopt($ch, CURLOPT_URL, BpjsApiUrl."SEP/1.1/insert");
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $sep);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      $content = curl_exec($ch);
      $err = curl_error($ch);

      curl_close($ch);
      $result = json_decode($content,true);
      $meta = $result['metaData']['code'];
      $mets = $result['metaData']['message'];
      $sep = $result['response']['sep']['noSep'];

        //echo "Kode : ".$meta."</br>";
        //echo "Pesan : ".$mets."</br>";
        //echo $sep;
      if ($meta == "200") {
        if ($_POST['suplesi'] == '0') {
          $supl = '0. Tidak';
        }else {
          $supl = '1. Ya';
        };
        if ($_POST['eks'] == '0') {
          $eks = '0. Tidak';
        }else {
          $eks = '1. Ya';
        };
        if ($_POST['cob'] == '0') {
          $cob = '0. Tidak';
        }else {
          $cob = '1. Ya';
        };
        if ($_POST['ktrk'] == '0') {
          $ktrk = '0. Tidak';
        }else {
          $ktrk = '1. Ya';
        };
        if ($_POST['fsks'] == '1') {
          $fskes = '1. Faskes 1';
        }else {
          $fskes = '2. Faskes 2(RS)';
        };
        $insert = query("INSERT INTO bridging_sep VALUES (
        '{$sep}','{$_POST['no_rawat']}','{$_POST['tglsep']}','{$_POST['tglrjk']}','{$_POST['no_rujuk']}','{$_POST['ppruj']}','{$_POST['nmruj']}',
        '{$_POST['ppk']}','{$_POST['nmrs']}','{$_POST['kdpl']}','{$_POST['cttn']}','{$_POST['kddx']}','{$_POST['nmdx']}','{$_POST['kdpoli']}',
        '{$_POST['nmpoli']}','{$_POST['kkls']}','{$_POST['lkln']}','loket1','{$_POST['norm']}','{$_POST['nmps']}','{$_POST['tgllhr']}',
        '{$_POST['psrt']}','{$_POST['jk']}','{$_POST['nops']}','{$_POST['tglplg']}','{$fskes}','{$eks}','{$cob}','','{$_POST['notlp']}',
        '{$ktrk}','{$_POST['tglkkl']}','{$_POST['ktrg']}','{$supl}','{$_POST['sepsup']}','','','','','','','{$_POST['skdp']}','{$_POST['dpjp']}','{$_POST['nmdpjp']}')");
    	}else {
      ?>
        <script>alert('<?php echo "Pesan : ".$mets; ?>')</script>
      <?php
      };
    }
      ?>
    <?php $sql = "SELECT pasien.nm_pasien , reg_periksa.no_rawat , reg_periksa.no_rkm_medis , pasien.tgl_lahir , pasien.no_tlp , pasien.no_peserta , pasien.jk , poliklinik.nm_poli
FROM reg_periksa , pasien , poliklinik WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.no_rawat = '{$_GET['no_rawat']}'";
$data = query($sql);
$b = fetch_assoc($data);?>
    <div class="body">
      <form method="post" action="">
        <div class="row clearfix">
          <div class="col-md-2">
        <div class="form-group">
              <div class="form-line">
            <label for="norm">No Rawat</label>
            <input type="text" class="form-control" name="no_rawat" value="<?php echo $_GET['no_rawat']; ?>" readonly>
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
                <input type="number" class="form-control" name="notlp" required minlength=8 maxlength=13 value="<?php echo $b['no_tlp']; ?>">
              </div>
            </div>
          </div>
        </div>
        <div class="row clearfix">
          <div class="col-md-4">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">No Peserta</label>
                <input type="text" class="form-control" name="nops" value="<?php echo $b['no_peserta']; ?>" readonly>
              </div>
            </div>
          </div>
          <div class="col-md-2">
        <div class="form-group">
              <div class="form-line">
                <label for="norm">Kode PPK</label>
                <input type="text" class="form-control" name="ppk" value="<?php echo $dataSettings['kode_ppk']; ?>" readonly>
              </div>
            </div>
          </div>
          <div class="col-md-3">
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
      </div>
        <?php
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
        curl_setopt($ch, CURLOPT_URL, BpjsApiUrl."Rujukan/List/Peserta/".$b['no_peserta']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($ch);
        $err = curl_error($ch);

        curl_close($ch);
        $result = json_decode($content, true);
          ?>
      <div class="row clearfix">
         <div class="col-md-2">
          <div class="form-group">

            <div class="btn-group">
                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Pilih Nomor Rujukan <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                  <?php
                  foreach($result['response']['rujukan'] as $key => $value):
                    echo '<li><a href="./index.php?module=BridgingBPJS&page=index&action=bridging&no_rawat='.$_GET['no_rawat'].'&no_rujuk='.$value['noKunjungan'].'" class="dropdown-item" name="nrjk">'.$value['noKunjungan'].'</a></li>';
                  endforeach;
                  ?>
                </ul>
            </div>
            <div class="form-group">
            <?php $sql = "SELECT no_rawat as jml FROM bridging_sep WHERE no_rawat = '{$_GET['no_rawat']}'";
                  $ceksep = query($sql);
                  if(num_rows($ceksep) == 1){
                echo "<script type='text/javascript' class='alert alert-primary'>alert('Sudah Bridging');</script>";
                  }else{
                    echo "<script type='text/javascript' class='alert alert-danger'>alert('Belum Bridging');</script>";};
            ?>
            </div>
          </div>
         </div>
        </div>
        <?php
        if(isset($_GET['no_rujuk']) && $_GET['no_rujuk'] !='') {
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
          curl_setopt($ch, CURLOPT_URL, BpjsApiUrl."Rujukan/".$_GET['no_rujuk']);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_TIMEOUT, 3);
          curl_setopt($ch, CURLOPT_HTTPGET, 1);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          $content = curl_exec($ch);
          $err = curl_error($ch);

          curl_close($ch);
          $bri = json_decode($content, true);
          $status = $bri['response']['rujukan']['peserta']['statusPeserta']['keterangan'];
          $kelas = $bri['response']['rujukan']['peserta']['hakKelas']['keterangan'];
          $klask = $bri['response']['rujukan']['peserta']['hakKelas']['kode'];
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
         ?>
      <div class="row clearfix">
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
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Status</label>
                <input type="text" class="form-control" name="stts" value="<?php echo $status; ?>" readonly>
              </div>
            </div>
          </div>
          <div class="col-md-1">
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
        </div>
        <div class="row clearfix">
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Kode Diagnosa</label>
                <input type="text" class="form-control" name="kddx" value="<?php echo $diagk; ?>" readonly>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Nama Diagnosa</label>
                <input type="text" class="form-control" name="nmdx" value="<?php echo $diagn; ?>" readonly>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Kode Poli Tujuan</label>
                <input type="text" class="form-control" name="kdpoli"  id="kdpoli" value="<?php echo $polik; ?>" readonly>
              </div>
            </div>
          </div>
          <div class="col-md-2">
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
                <label for="norm">Jenis Pelayanan</label>
                <select class="form-control" name="kdpl">
                  <option value="<?php echo $plynk; ?>" selected><?php echo $plynn; ?></option>
                  <option value="1">Rawat Inap</option>
                  <option value="2">Rawat Jalan</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="row clearfix">
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">PPK Perujuk</label>
                <input type="text" class="form-control" name="ppruj" value="<?php echo $ppruj; ?>" readonly>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Nama Perujuk</label>
                <input type="text" class="form-control" name="nmruj" value="<?php echo $nmruj; ?>" readonly>
              </div>
            </div>
          </div>
          <?php
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
          ?>
          <div class="col-md-4">
            <div class="form-group">
              <div class="form-line">
                <label for="dpjp">Nama DPJP</label>
                <select class="form-control" id="dpjp" onchange="myFunction()" name="dpjp">
                  <option value="" selected>-------------------------------------</option>
                  <?php
                    foreach($dpjp['response']['list'] as $kode => $val): ?>
                    <option value="<?php echo $val['kode']; ?>"><?php echo $val['nama']; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <?php
                $sql = "SELECT no_antrian FROM skdp_bpjs WHERE no_rkm_medis = '{$b['no_rkm_medis']}' and tanggal_datang = '{$date}'";
                $skdp = query($sql);
                $sksk = fetch_assoc($skdp);
                ?>
                <label for="norm">No SKDP</label>
                <input type="text" class="form-control" name="skdp" value="<?php echo $sksk['no_antrian'];?>" placeholder="No SKDP">
              </div>
            </div>
          </div>
        </div>
        <div class="row clearfix">
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Eksekutif</label>
                <select class="form-control" id="eks" name="eks">
                  <option value="0" selected>0. Tidak</option>
                  <option value="1">1. Ya</option>
                </select>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">COB</label>
                <select class="form-control" id="cob" name="cob">
                  <option value="0" selected>0. Tidak</option>
                  <option value="1">1. Ya</option>
                </select>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Katarak</label>
                <select class="form-control" id="katara" name="ktrk">
                  <option value="0" selected>0. Tidak</option>
                  <option value="1">1. Ya</option>
                </select>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Suplesi</label>
                <select class="form-control" id="suple" name="suplesi">
                  <option value="0" selected>0. Tidak</option>
                  <option value="1">1. Ya</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group col-md-2 col-sm-2" style="display:none;">
            <label for="norm">Nama Dokter</label>
            <input type="text" class="form-control" id="nmdp" name="nmdpjp" value="" readonly>
          </div>
          <div class="form-group col-md-2 col-sm-2" style="display:none;">
            <label for="norm">No Rujukan</label>
            <input type="text" class="form-control" name="no_rujuk" value="<?php echo $_GET['no_rujuk']; ?>" readonly>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label>Faskes</label>
                <select name="fsks" id="faskes" class="form-control">
                  <option value="1" selected>1. Faskes 1</option>
                  <option value="2">2. Faskes 2(RS)</option>
                </select>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Tgl Pulang</label>
                <input type="text" class="tglplg form-control" name="tglplg" value="0000-00-00">
              </div>
            </div>
          </div>
        </div>
        <div class="row clearfix">
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">SEP Suplesi</label>
                <input type="text" class="form-control" name="sepsup" value="" placeholder="SEP Suplesi">
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Laka Lantas</label>
                <select class="form-control" name="lkln">
                  <option value="0" selected>0. Tidak</option>
                  <option value="1">1. Ya</option>
                </select>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Penjamin Laka</label>
                <select class="form-control" name="pjlk">
                  <option value="" selected>Tidak Ada</option>
                  <option value="1">Jasa Raharja</option>
                  <option value="2">BPJS Ketenagakerjaan</option>
                  <option value="3">TASPEN PT</option>
                  <option value="4">ASABRI PT</option>
                </select>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Tgl Kejadian</label>
                <input type="text" class="tglkkl form-control" name="tglkkl" value="0000-00-00">
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Keterangan</label>
                <input type="text" class="form-control" name="ktrg" value="" placeholder="Keterangan">
              </div>
            </div>
          </div>
        </div>
        <div class="row clearfix">
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Propinsi</label>
                <input type="text" class="form-control" name="prop" value="" placeholder="Propinsi">
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Kabupaten</label>
                <input type="text" class="form-control" name="kbpt" value="" placeholder="Kabupaten">
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Kecamatan</label>
                <input type="text" class="form-control" name="kec" value="" placeholder="Kecamatan">
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <div class="form-line">
                <label for="norm">Catatan</label>
                <input type="text" class="form-control" name="cttn" value="" placeholder="Catatan">
              </div>
            </div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col col">
            <input type="submit" class="btn btn-success form-control" name="build_sep" value="SIMPAN SEP">
          </div>
        </div>
        <?php } ?>
      </form>
</div>
  <?php } ?>
</div>
