<form method="post">
  <?php
  if(isset($_POST['ok_skdp'])){
    if(($no_rawat <> "")){
             
      $insert = query("INSERT INTO skdp_bpjs VALUES ('{$year}','{$no_rkm_medis}','{$_POST['dx']}','{$_POST['terapi']}','{$_POST['alasan']}','-','{$_POST['tlj']}','-','{$_POST['tgl']}'
                  ,'{$date}','{$_POST['noan']}','{$_SESSION['username']}','Menunggu')");
      if($insert){
        $insert2 = query("INSERT INTO booking_registrasi VALUES ('{$date}','{$time}','{$no_rkm_medis}','{$_POST['tgl']}','{$_SESSION['username']}','{$nmpoli}','{$_POST['noreg']}','{$kd_pj}','0'
                    ,'{$date_time}','Belum')");
        redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}");
      }
    }
    }
  
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
      <div class="form-line">
        <dt>No SKDP</dt>
        <dd><div id="antri" class="antri"></div>
        </dd>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>No Reg</dt>
        <dd><input type='text' id='noreg' class='form-control' name='noreg' placeholder='No Registrasi' required></dd>
      </div>
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
      <div class="form-line" id="tglperiksa">
        <dt>Tanggal Periksa</dt>
        <dd><input type="text" id="tglprk" class="form-control datepicker tglprk" name="tgl" ></dd>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>Poli</dt>
        <dd><input type="text" class="form-control" name="poli" value="<?php echo $nmpoli; ?>"></dd>
      </div>
    </div>
  </div>
</div>
<div class="row clearfix">
  <div class="col-md-3">
    <div class="form-group">
      <dd><button type="submit" name="ok_skdp" value="ok_skdp" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_skdp\'">OK</button></dd><br/>
    </div>
  </div>
</div>
<div class="row clearfix">
  <table id="databooking" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
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
      <td><a class="btn btn-danger btn-xs" href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_an&keluhan=<?php echo $data['0']; ?>&no_rawat=<?php echo $no_rawat; ?>">[X]</a></td>
    </tr>
    <?php
      $no++;}
    ?>
  </table>
</div>
</form>
