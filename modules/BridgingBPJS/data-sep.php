<div class="body">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=BridgingBPJS">
                <i class="material-icons">home</i> <span class="hidden-xs">Index</span>
            </a>
        </li>
        <li role="presentation" class="active">
            <a href="<?php echo URL; ?>/?module=BridgingBPJS&page=data_sep">
                <i class="material-icons">assignment</i> <span class="hidden-xs">Data SEP</span>
            </a>
        </li>
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=BridgingBPJS&page=pasien_batal">
                <i class="material-icons">clear</i> <span class="hidden-xs">Pasien Batal</span>
            </a>
        </li>
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=BridgingBPJS&page=cek_kepesertaan">
                <i class="material-icons">assignment_ind</i> <span class="hidden-xs">Cek Kepesertaan</span>
            </a>
        </li>
    </ul>
    <div class="content m-t-30">
        <?php $action = isset($_GET['action'])?$_GET['action']:null;
        if(!$action){?>
          <table id="allsep" class="table responsive table-bordered table-striped table-hover display " width="100%">
            <thead>
              <tr>
                <th>No SEP</th>
                <th>No Rawat</th>
                <th>Tangal SEP</th>
                <th>Tgl Rujukan</th>
                <th>No Rujukan</th>
                <th>No RM</th>
                <th>Nama Pasien</th>
                <th>Tanggal Lahir</th>
                <th>Peserta</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
  <?php } ?>
        <?php if($action == "bridging"){?>
        <?php $sql = "SELECT pasien.nm_pasien , reg_periksa.no_rawat , reg_periksa.no_rkm_medis , pasien.tgl_lahir , pasien.no_tlp , pasien.no_peserta , pasien.jk
    FROM reg_periksa , pasien WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.no_rawat = '{$_GET['no_rawat']}'";
  $data = query($sql);
$b = fetch_assoc($data);?>
      <div class="body">
          <form method="post" action="build-igd.php">
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
              <?php //include_once 'bri-ranap.php';?>
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
                      <option value="1">Rawat Inap</option>
                      <option value="2" selected>Rawat Jalan</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <div class="form-line">
                      <label for="norm">Spesialis</label>
                      <input type="text" class="form-control" name="kdpoli" value="IGD" readonly>
                    </div>
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
                    <select type="text" class="drigd form-control" id="dpjp" name="dpjp" value=""></select>
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
