<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Pulang';
include_once('../config.php');
include_once('../layout/header.php');
include_once('../layout/sidebar.php');
if($_SERVER['REQUEST_METHOD'] == "POST") {
  if($_POST['stts_pulang'] == "Membaik"){
    $update = query("UPDATE kamar_inap SET tgl_keluar = '".$_POST['tglplg']."' , jam_keluar = '".$time."' , diagnosa_akhir = '".$_POST['dx']."' , stts_pulang = '".$_POST['stts_pulang']."' WHERE no_rawat = '".$_POST['no_rawat']."'");
    if($update){ 
      $update1 = query("UPDATE kamar SET status = 'KOSONG' WHERE kd_kamar = '".$_POST['bed']."'");
      redirect('../pasien-ranap.php');
    }
  }else{echo "<script>swal({
                        title: 'Pilih Membaik untuk Memulangkan',
                        icon: 'warning',
                        dangerMode: true,
                      })</script>";}}
?>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                PASIEN PULANG
                            </h2>
                        </div>
                        <div class="body">
                          <form action="" method="POST">
                            <div class="modal-body">
                              <div class="form-group">
                                <div class="form-line">
                                    <label for="dx">Diagnosa</label>
                                    <input type="text" class="form-control" name="dx" value="">
                                    <input type="hidden" class="form-control" name="bed" value="<?php echo $_GET['bed'];?>">
                                </div>
                              </div>
                              <div class="form-group">
                                <div class="form-line">
                                    <label for="tglplg">Tanggal Pulang</label>
                                    <input type="text" class="datepicker form-control" name="tglplg" value="<?php echo $date; ?>">
                                </div>
                              </div>
                              <div class="form-group">
                                <div class="form-line">
                                  <label for="stts_pulang">Status Pulang</label>
                                  <select name="stts_pulang" class="form-control show-tick">
                                  <?php
                                  $result = query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'kamar_inap' AND COLUMN_NAME = 'stts_pulang'");
                                  $row = fetch_array($result);
                                  $enumList = explode(",", str_replace("'", "", substr($row['COLUMN_TYPE'], 5, (strlen($row['COLUMN_TYPE'])-6))));
                                  foreach($enumList as $value) {
                                      echo "<option value='$value'>$value</option>";
                                  }
                                  ?>
                                  </select>
                                </div>
                              </div>
                            </div>
                            <div class="modal-footer">
                                <input type="hidden" name="no_rawat" value="<?php echo $_GET['no_rawat'];?>">
                                <button type="submit" class="btn btn-success waves-effect" value="simpan_stts_pulang">SIMPAN</button><a href="../pasien-ranap.php" class="btn btn-link">KEMBALI</a>
                            </div>
                       	  </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
include_once('../layout/footer.php');
?>
  <script>
        $(document).ready(function() {
            $('.datepicker').bootstrapMaterialDatePicker({
                format: 'YYYY-MM-DD',
                clearButton: true,
                weekStart: 1,
                time: false
            });
        } );
  </script>