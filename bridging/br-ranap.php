<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Bridging Ranap';
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
                                BRIDGING RANAP
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
                                <th>Bangsal</th>
                                <th>Jenis Bayar</th>
                                <th>Bridging</th>
                                <th>Cetak SEP</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php
                              		$sql = "SELECT reg_periksa.no_rawat , pasien.nm_pasien , reg_periksa.no_rkm_medis , penjab.png_jawab , bangsal.nm_bangsal FROM reg_periksa , pasien , bangsal , kamar , kamar_inap , penjab WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_pj = penjab.kd_pj AND reg_periksa.no_rawat = kamar_inap.no_rawat AND kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND reg_periksa.status_lanjut = 'Ranap' AND kamar_inap.stts_pulang = '-'";
									$list = query($sql);
                                    while($a = mysqli_fetch_assoc($list)) {
                              ?>
                              <tr>
                                <td><?php echo $a['no_rkm_medis']; ?></td>
                                <td><?php echo SUBSTR($a['nm_pasien'], 0, 15).' ...'; ?></td>
                                <td><?php echo $a['nm_bangsal']; ?></td>
                                <td><?php echo $a['png_jawab']; ?></td>
                                <td><a class="btn btn-primary" href="<?php echo $_SERVER['PHP_SELF']; ?>?action=sep&no_rawat=<?php echo $a['no_rawat'];?>">Cek Bridging</a></td>
                                <td><a class="btn btn-primary" href="cetaksep.php?action=cetak&no_rawat=<?php echo $a['no_rawat']; ?>" target="_BLANK">Cetak</a></td>
                              </tr>
                                <?php } ?>
                            </tbody>
                          </table>
                        </div>
          				<?php } ?>
                      	<?php if($action == "sep"){?>
                      	<?php $sql = "SELECT pasien.nm_pasien , reg_periksa.no_rawat , reg_periksa.no_rkm_medis , pasien.tgl_lahir , pasien.no_tlp , pasien.no_peserta , pasien.jk
        						FROM reg_periksa , pasien WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.no_rawat = '{$_GET['no_rawat']}'";
							  	$data = query($sql);
								$b = mysqli_fetch_assoc($data);?>
                      <div class="body">
                          <form method="post" action="buildsepranap.php">                       
                          <form method="get">                            
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
                                    <input type="text" class="form-control" name="notlp" value="<?php echo $b['no_tlp']; ?>" readonly>
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
                              <?php include_once 'bri-ranap.php';?>
                              <div class="col-md-2">
                              <div class="form-group">
                                <div class="form-line">
                                  <label>No Rujukan</label>
                                  <input type="text" class="form-control" name="no_rujuk" value="">
                                </div>
                              </div>
                             </div>
                              <div class="col-md-3" style="display:none">
                        		<div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Poli</label>
                                    <input type="text" class="form-control" name="nmpoli" value="<?php echo $b['nm_poli']; ?>" readonly>
                                  </div>
                                </div>
                              </div>
                      		</div>
                       		<div class="row clearfix">
                              <div class="col-md-2">
                              	<div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Tanggal SEP</label>
                                    <input type="text" class="datepicker form-control" name="tglsep" value="<?php echo $date; ?>">
                              	  </div>
                                </div>
                              </div>
                              <div class="col-md-2">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Tanggal Rujuk</label>
                                    <input type="text" class="datepicker form-control" name="tglrjk" value="<?php echo $date; ?>">
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
                              <div class="col-md-6">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Diagnosa</label>
                                    <select type="text" class="form-control kddx" name="kddx" value=""></select>
                              	  </div>
                                </div>
                              </div>
                              <div class="form-group col-md-2 col-sm-2" style="display:none;">
                                <label for="norm">Nama Diagnosa</label>
                                <input type="text" class="form-control" id="nmdx" name="nmdx" value="" readonly>
                              </div>
                              <div class="col-md-2">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Jenis Pelayanan</label>
                                    <select class="form-control" name="kdpl">
                                      <option value="1" selected>Rawat Inap</option>
                                      <option value="2">Rawat Jalan</option>
                                    </select>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-4" style="display:none;">
                                <div class="form-group">
                                	<div class="form-line">
                                      <label for="norm">Spesialis</label>
                                      <select type="text" class="poli form-control" name="poli" value=""></select>
                                    </div>
                                </div>
                              </div>
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                  
                                    <label for="norm">Kode Poli Tujuan</label>
                                    <input type="text" class="form-control" name="kdpoli"  id="kdpoli" value="" readonly>
                                
                                </div>
                              </div>
                              <div class="col-md-2" style="display:none;">
                                <div class="form-group">
                                	
                                      <label for="norm">Nama Poli Tujuan</label>
                                      <input type="text" class="form-control" name="nmpoli" value="" readonly>
                                    
                                </div>
                              </div>
                            </div>
                            <div class="row clearfix">
                              <div class="col-md-2">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">PPK Perujuk</label>
                                    <input type="text" class="form-control" name="ppruj" value="1708R008" readonly>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Nama Perujuk</label>
                                    <input type="text" class="form-control" name="nmruj" value="RSUD. H. DAMANHURI - KAB. HULU SUNGAI TENGAH" readonly>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="dpjp">Nama DPJP</label>
                                    <select type="text" class="dr form-control" id="dpjp" name="dpjp" value=""></select>
                                  </div>
                                </div>
                              </div>
                              <div class="form-group col-md-2 col-sm-2" style="display:none;">
                                <label for="norm">Nama Dokter</label>
                                <input type="text" class="form-control" id="nmdp" name="nmdpjp" value="" readonly>
                              </div>
                              <div class="col-md-2">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">No SKDP</label>
                                    <input type="text" class="form-control" name="skdp" value="" placeholder="No SKDP">
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
                              <div class="col-md-2">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label>Faskes</label>
                                    <select name="fsks" id="faskes" class="form-control">
                                      <option value="1">1. Faskes 1</option>
                                      <option value="2" selected>2. Faskes 2(RS)</option>
                                    </select>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-2">
                                <div class="form-group">
                                  <div class="form-line">
                                    <label for="norm">Tgl Pulang</label>
                                    <input type="text" class="datepicker form-control" name="tglpulang">
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
                                    <input type="text" class="datepicker form-control" name="tglkkl" >
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
                                <input type="submit" class="btn btn-success form-control" name="" value="SIMPAN">
                              </div>
                            </div>
                          </form>
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

<script type="text/javascript">

    function formatData (data) {
        var $data = $(
            '<b>'+ data.id +'</b> - <i>'+ data.text +'</i>'
        );
        return $data;
    };

    function formatDataTEXT (data) {
        var $data = $(
            '<b>'+ data.text +'</b>'
        );
        return $data;
    };
  
  $('.poli').select2({
        placeholder: 'Pilih Spesialis',
        ajax: {
          url: 'poli.php',
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
            return {
              results: data
            };
          },
          cache: true
        },
        templateResult: formatData,
        minimumInputLength: 3
      });
  $('.kddx').select2({
        placeholder: 'Pilih Diagnosa',
        ajax: {
          url: 'diagnosa.php',
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
            return {
              results: data
            };
          },
          cache: true
        },
        templateResult: formatData,
        minimumInputLength: 3
      });
  $('.dr').select2({
        placeholder: 'Pilih Dokter',
        ajax: {
          url: 'ranapdpjp.php',
          dataType: 'json',
          delay: 250,
          processResults: function (data) {
            return {
              results: data
            };
          },
          cache: true
        },
        templateResult: formatData,
        minimumInputLength: 3
      });
  
    // get nmdpjp from array of $('.dr').select2({ ...... } 
    $('.dr').on('change', function() {
      var nmdpjp = $(".dr option:selected").text();
      $("input[name*='nmdpjp']").val(nmdpjp);
    });
  	$('.kddx').on('change', function() {
      var nmdx = $(".kddx option:selected").text();
      $("input[name*='nmdx']").val(nmdx);
    })
  
</script>
