<form method="post">
  <?php
  if(isset($_POST['ok_an'])){
    if(($no_rawat <> "")){
      $insert = query("INSERT INTO pemeriksaan_ralan VALUE ('{$no_rawat}','{$date}','{$time}','{$_POST['suhu']}','{$_POST['tensi']}','{$_POST['nadi']}','{$_POST['respirasi']}','{$_POST['tinggi']}','{$_POST['berat']}'
                  ,'{$_POST['gcs']}','{$_POST['keluhan']}','{$_POST['pemeriksaan']}','{$_POST['alergi']}','-','{$_POST['tndklnjt']}')");
      if($insert){
        redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}");
      }
    }
  }
  ?>
<div class="row clearfix">
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>Keluhan</dt>
        <dd><textarea rows="4" name="keluhan" class="form-control"></textarea></dd>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>Pemeriksaan</dt>
        <dd><textarea rows="4" name="pemeriksaan" class="form-control"></textarea></dd>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>Alergi</dt>
        <dd><input type="text" class="form-control" name="alergi"></dd>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>Tindak Lanjut</dt>
        <dd><input type="text" class="form-control" name="tndklnjt"></dd>
      </div>
    </div>
  </div>
</div>
<div class="row clearfix">
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>Suhu Badan (C)</dt>
        <dd><input type="text" class="form-control" name="suhu"></dd>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>Tinggi Badan (Cm)</dt>
        <dd><input type="text" class="form-control" name="tinggi"></dd>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>Tensi</dt>
        <dd><input type="text" class="form-control" name="tensi"></dd>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>Respirasi (per Menit)</dt>
        <dd><input type="text" class="form-control" name="respirasi"></dd>
      </div>
    </div>
  </div>
</div>
<div class="row clearfix">
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>Berat (Kg)</dt>
        <dd><input type="text" class="form-control" name="berat"></dd>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>Nadi (per Menit)</dt>
        <dd><input type="text" class="form-control" name="nadi"></dd>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>Imun Ke</dt>
        <dd><input type="text" class="form-control" name="imun"></dd>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <div class="form-line">
        <dt>GCS(E , V , M)</dt>
        <dd><input type="text" class="form-control" name="gcs"></dd>
      </div>
    </div>
  </div>
</div>
<div class="row clearfix">
  <div class="col-md-3">
    <div class="form-group">
      <dd><button type="submit" name="ok_an" value="ok_an" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_an\'">OK</button></dd><br/>
    </div>
  </div>
</div>
  <div class="row clearfix">
    <table id="keluhan" class="table striped">
      <tr>
        <th>No</th>
        <th>Keluhan</th>
        <th>Pemeriksaan</th>
        <th>Hapus</th>
      </tr>
      <?php
      $query = query("SELECT keluhan , pemeriksaan FROM pemeriksaan_ralan WHERE no_rawat = '{$no_rawat}'");
      $no=1;
       while ($data = fetch_array($query)) {
      ?>
      <tr>
        <td><?php echo $no; ?></td>
        <td><?php echo $data['0']; ?></td>
        <td><?php echo $data['1']; ?></td>
        <td><a class="btn btn-danger btn-xs" href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_an&keluhan=<?php echo $data['0']; ?>&no_rawat=<?php echo $no_rawat; ?>">[X]</a></td>
      </tr>
      <?php
        $no++;}
      ?>
    </table>
  </div>
</form>
