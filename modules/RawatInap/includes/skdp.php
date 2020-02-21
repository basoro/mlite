<form method="post">
  <?php
  if(isset($_POST['ok_skdp'])){
    if(($no_rawat <> "")){
      $year       = date("Y");
      $date       = date('Y-m-d');
      $time       = date('H:i:s');
      $date_time  = date('Y-m-d H:i:s');
      $nomor = query("SELECT no_antrian from skdp_bpjs WHERE no_antrian = '{$_POST['noan']}'");
      if(num_rows($nomor) > 0 ){
        echo "<script type='text/javascript'>alert(\"No SKDP Sudah Terpakai\");window.location=history.go(-1)</script>";
      } else {
        $insert = query("INSERT INTO skdp_bpjs VALUES ('{$year}','{$no_rkm_medis}','{$_POST['dx']}','{$_POST['terapi']}','{$_POST['alasan']}','-','{$_POST['tlj']}','-','{$_POST['tgl']}'
                    ,'{$date}','{$_POST['noan']}','{$_POST['dpjp']}','Menunggu')");
        if($insert){
          $insert2 = query("INSERT INTO booking_registrasi VALUES ('{$date}','{$time}','{$no_rkm_medis}','{$_POST['tgl']}','{$_POST['dpjp']}','{$_POST['kd_poli']}','{$_POST['noreg']}','{$_POST['kd_pj']}','0'
                      ,'{$date_time}','Belum')");
          if($insert2){
            redirect("./index.php?module=RawatInap&page=index&action=tindakan&no_rawat={$no_rawat}");
          }
        }
      }
      // echo "{$get_tahun},{$date},{$time},{$date_time},{$no_rkm_medis},{$_POST['dx']},{$_POST['terapi']},{$_POST['alasan']},-,{$_POST['tlj']},'-',{$_POST['tgl']},'  {$date}','{$_POST['noan']}','{$_POST['dpjp']}','Menunggu'";
    }
  };
  ?>
<div class="row clearfix">
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>Diagnosa</dt>
        <dd><input type="text" class="form-control" name="dx" ></dd>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>Alasan</dt>
        <dd><input type="text" class="form-control" name="alasan"></dd>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line" id="antri">
        <dt>No SKDP</dt>
        <dd><input type='text' id="antri" class='form-control antri' name='noan' value="" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" maxlength="6" required>
        </dd>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <dt>Dokter PJ</dt>
      <dd><select name="dpjp" class="form-control dpjp" id="dpjp" style="width:100%"></select></dd><br/>
    </div>
  </div>
</div>
<div class="row clearfix">
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>Tindak Lanjut</dt>
        <dd><input type="text" class="form-control" name="tlj"></dd>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>Terapi</dt>
        <dd><input type="text" class="form-control" name="terapi"></dd>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
        <dt>Poli</dt>
        <dd><select name="kd_poli" class="form-control kd_poli" id="kd_poli" style="width:100%"></select></dd>
    </div>
  </div>
  <div class="col-md-2">
    <div class="form-group">
      <div class="form-line" id="tglperiksa">
        <dt>Tanggal Periksa</dt>
        <dd><input type="text" id="tglprk" class="form-control datepicker tglprk" name="tgl" ></dd>
      </div>
    </div>
  </div>
  <div class="col-md-1">
    <div class="form-group">
      <dd><input type="hidden" class="form-control" name="kd_pj" value="<?php $pj = fetch_array(query("SELECT kd_pj FROM reg_periksa WHERE no_rawat = '{$no_rawat}'"));echo $pj['0'];?>"></dd>
    </div>
  </div>
</div>
<div class="row clearfix">
  <div class="col-md-2">
    <div class="form-group">
      <div class="form-line">
        <dt>No Reg</dt>
        <dd><input type='text' id='noreg' class='form-control' name='noreg' placeholder='No Registrasi' required></dd>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <dd><button type="submit" name="ok_skdp" value="ok_skdp" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_skdp\'">SIMPAN</button></dd><br/>
    </div>
  </div>

</div>
<div class="row clearfix">
  <table class="table datatable responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
    <tr>
      <th>No</th>
      <th>Tanggal Booking</th>
      <th>Tanggal Pemeriksaan</th>
      <th>Poli</th>
      <th>No Reg</th>
      <th>Hapus</th>
    </tr>
    <?php
    $query = query("SELECT tanggal_booking , tanggal_periksa , nm_poli , no_reg FROM booking_registrasi a , poliklinik b WHERE a.kd_poli = b.kd_poli AND no_rkm_medis = '{$no_rkm_medis}'");
    $no=1;
    while ($data = fetch_array($query)) {
    ?>
    <tr>
      <td><?php echo $no; ?></td>
      <td><?php echo $data['0']; ?></td>
      <td><?php echo $data['1']; ?></td>
      <td><?php echo $data['2']; ?></td>
      <td><?php echo $data['3']; ?></td>
      <td><a class="btn bg-red waves-effect" href="./index.php?module=RawatInap&page=index&action=delete_skdp&no_reg=<?php echo $data['3']; ?>&no_rkm_medis=<?php echo $no_rkm_medis; ?>">Hapus</a></td>
    </tr>
    <?php
      $no++;}
    ?>
  </table>
</div>
</form>
