<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Bridging Ralan';
include_once('../config.php');
include_once('../layout/header.php');
include_once('../layout/sidebar.php');
?>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
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
                                    while($a = mysqli_fetch_assoc($list)) {
                                      
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
                                <td><?php echo SUBSTR($a['nm_poli'], 5, 16); ?></td>
                                <td><?php echo $a['png_jawab']; ?></td>
                                <td><a class="btn btn-primary" href="<?php echo $_SERVER['PHP_SELF']; ?>?action=sep&no_rawat=<?php echo $a['no_rawat'];?>">Cek Bridging PCare</a></td>
                                <td><a class="btn btn-primary" href="cetaksep.php?action=cetak&no_rawat=<?php echo $a['no_rawat']; ?>" target="_BLANK">Cetak</a></td>
                              </tr>
                                <?php } ?>
                            </tbody>
                          </table>
                        </div>
          				<?php } ?>
                      	<?php if($action == "sep"){?>
                      	<?php $sql = "SELECT pasien.nm_pasien , reg_periksa.no_rawat , reg_periksa.no_rkm_medis , pasien.tgl_lahir , pasien.no_tlp , pasien.no_peserta , pasien.jk , poliklinik.nm_poli
        						FROM reg_periksa , pasien , poliklinik WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.no_rawat = '{$_GET['no_rawat']}'";
							  	$data = query($sql);
								$b = mysqli_fetch_assoc($data);?>
                      	<div class="body">
                          <form method="post" action="buildsep.php">                            
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
                            <?php include_once 'bridging.php' ?>
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
                                        echo '<li><a href="'.$_SERVER['PHP_SELF'].'?action=sep&no_rawat='.$_GET['no_rawat'].'&no_rujuk='.$value['noKunjungan'].'" class="dropdown-item" name="nrjk">'.$value['noKunjungan'].'</a></li>';
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
                              <?php include_once 'dpjp.php'; ?>
                              <div class="col-md-4">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="dpjp">Nama DPJP</label>
                                    <select class="form-control" id="dpjp" onchange="myFunction()" name="dpjp">
                                      <option value="" selected>-------------------------------------</option>
                                      <?php if($polik == 'HDL'){?>
                             			<option value="9102">dr. Aris Sugiharjo, Sp. PD</option>
                             			<?php 
                                      	}else{
                                      	foreach($dpjp['response']['list'] as $kode => $val): ?>
                                      	<option value="<?php echo $val['kode']; ?>"><?php echo $val['nama']; ?></option>
                                      	<?php endforeach; }?>
                                    </select>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-2">
                                <div class="form-group">
                                  <div class="form-line">
                                    <?php include_once 'skdp.php'; ?>
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
                                <input type="submit" class="btn btn-success form-control" name="" value="SIMPAN SEP">
                              </div>
                            </div>
                            <?php } ?>
                          </form>
                		</div>
                      <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
include_once('../layout/footer.php');
?>
<script>
  function myFunction(){
		$("#dpjp").on("change",function(){
        //Getting Value
        var selValue = $("#dpjp :selected").text();
        //Setting Value
        $("#nmdp").val(selValue);
    });}
</script>