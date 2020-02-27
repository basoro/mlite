<?php
if(isset($_POST['simpan_imut'])){
  $signature = md5($sismadak_username.md5($sismadak_password));

  $json_n[] = ['modules'=>'imut_nasional','indicator_id'=>$_POST['id'],'date'=>$_POST['tanggal'],'department_id'=>$sismadak_department_id, 'variable_type'=>'N','value'=>$_POST['N']];
  $json_d[] = ['modules'=>'imut_nasional','indicator_id'=>$_POST['id'],'date'=>$_POST['tanggal'],'department_id'=>$sismadak_department_id, 'variable_type'=>'D','value'=>$_POST['D']];
  $data_n = json_encode($json_n);
  $data_d = json_encode($json_d);

  /** Lakukan penyimpanan data di Server SIKARS menggunakan Webservice **/
  $headers = array(
      'X-HOSCODE: '.$dataSettings['kode_ppkkemenkes'] .'',
      'X-SIKARS-KEY: '.$signature.'',
      'Content-Type: Application/x-www-form-urlencoded'
      );

  if(isset($_POST['N']) && $_POST['N'] !=='') {
    //curl_setopt($ch, CURLOPT_URL, "");
    $ch = curl_init($sismadak_url.'/application/ws/ws_imut_simrs.php');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //curl_setopt($ch, CURLOPT_HTTPGET,1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_n);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $content = curl_exec($ch);
    $err     = curl_error($ch);
    curl_close($ch);

    $response = json_decode($content,true);
    //print_r($response);
    if($response['status'] == 200) {
      echo '<div class="alert bg-green alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Nilai Numerator telah disimpan!</div>';
    } else {
      echo '<div class="alert bg-pink alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Error silahkan cek kembali konfigurasi!</div>';
    }
  }
  if(isset($_POST['D']) && $_POST['D'] !=='') {
    //curl_setopt($ch, CURLOPT_URL, "");
    $ch = curl_init($sismadak_url.'/application/ws/ws_imut_simrs.php');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //curl_setopt($ch, CURLOPT_HTTPGET,1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_d);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $content = curl_exec($ch);
    $err     = curl_error($ch);
    curl_close($ch);

    $response = json_decode($content,true);
    //print_r($response);
    if($response['status'] == 200) {
      echo '<div class="alert bg-green alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Nilai Denumerator telah disimpan!</div>';
    } else {
      echo '<div class="alert bg-pink alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Error silahkan cek kembali konfigurasi!</div>';
    }
  }
}
?>
<?php
$id = isset($_GET['id'])?$_GET['id']:null;
?>
<div class="card">
  <div class="header">
      <h2>Indikator Area Klinik</h2>
      <?php if($id) { echo '<small>Periode: '.$_GET['tanggal'].'</small>'; } ?>
  </div>
  <div class="body">
    <?php
    if(!$id){
    ?>
    <div class="row">
      <table class="table datatable">
        <thead>
          <tr>
            <th>Indikator</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $url = "modules/Sismadak/inc/imut.json";
          $json = file_get_contents($url);
          $datas = (array)json_decode($json, true);
          foreach($datas as $data) {
            if($data['indicator_category_id'] == 1) {
              echo '<form method="" action="">';
              echo '<input name="module" value="'.$_GET['module'].'" type="hidden">';
              echo '<input name="page" value="'.$_GET['page'].'" type="hidden">';
              echo '<tr>';
              echo '<td>'.$data['indicator_element'].'</td>';
              echo '<td><input class="datepicker form-control" name="tanggal" placeholder="Tanggal"></td>';
              echo '<td><button type="submit" name="id" value="'.$data['indicator_id'].'" class="btn btn-primary">INPUT</button></td>';
              echo '</tr>';
              echo '</form>';
            }
          }
          ?>
        </tbody>
      </table>
    </div>
    <?php
    } else {
    ?>
        <form method="POST" action="">
        <input name="tanggal" value="<?php echo $_GET['tanggal']; ?>" type="hidden">
        <input name="id" value="<?php echo $_GET['id']; ?>" type="hidden">
        <dl class="dl-horizontal">
        <?php
        $url = "modules/Sismadak/inc/imut_variabel.json";
        $json = file_get_contents($url);
        $datas = (array)json_decode($json, true);
        foreach($datas as $data) {
          if($data['variable_type'] == 'D') {
            $variable_type = 'Denumerator';
          } else {
            $variable_type = 'Numerator';
          }
          if($data['variable_indicator_id'] == $id) {
        ?>
          <dt>Indikator</dt>
          <dd><?php echo $data['variable_name']; ?></dd><br>
          <dd>Satuan : <strong><?php echo $data['variable_unit_name']; ?></strong>, Variabel: <strong><?php echo $variable_type; ?></strong></dd><br>
          <dt>Hasil</dt>
          <dd><input class="form-control" name="<?php echo $data['variable_type']; ?>" style="width:100%"></dd>
          <hr>
        <?php
          }
        }
        ?>
        <button type="submit" name="simpan_imut" value="simpan_imut" class="btn bg-indigo waves-effect" onclick="this.value=\'simpan_imut\'">SIMPAN</button>
        </dl>
        </form>
    <?php
    }
    ?>
  </div>
</div>
