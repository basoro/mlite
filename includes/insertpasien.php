<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Status';
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
                                Status Pasien
                            </h2>
                        </div>
                      <?php if(isset($_POST['submit'])){
  								if($_POST['stts_pulang'] == "Dirawat"){
                                  $sql = query("INSERT INTO `kamar_inap` (`no_rawat`, `kd_kamar`, `trf_kamar`, `diagnosa_awal`, `diagnosa_akhir`, `tgl_masuk`, 
                                  `jam_masuk`, `tgl_keluar`, `jam_keluar`, `lama`, `ttl_biaya`, `stts_pulang`) VALUES ('{$_POST['no_rawat']}','{$_POST['kamar']}',
                                  '{$_POST['hrgkmr']}','{$_POST['dx']}','-','{$_POST['tgl']}','$time','0000-00-00','00:00:00','0','0','-')");
                                   if($sql){$update = query("UPDATE kamar SET status = 'ISI' WHERE kd_kamar = '".$_POST['kamar']."'");
                                            $regs = query("UPDATE reg_periksa SET stts = '".$_POST['stts_pulang']."' WHERE no_rawat = '".$_POST['no_rawat']."'");
                                			}
                                }else{
                                  $status = query("UPDATE reg_periksa SET stts = '".$_POST['stts_pulang']."' WHERE no_rawat = '".$_POST['no_rawat']."'");
                                   }}?>
                        <div class="body">
                          <form method="POST">
                            <div class="modal-body">
                              <div class="form-group">
                                <div class="form-line">
                                  <label for="stts_pulang">Status</label>
                                  <select name="stts_pulang" id="stts_pulang"class="form-control show-tick">
                                  <?php
                                  $no_rawat = $_GET['no_rawat'];
                                  $result = query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'reg_periksa' AND COLUMN_NAME = 'stts'");
                                  $row = fetch_array($result);
                                  $enumList = explode(",", str_replace("'", "", substr($row['COLUMN_TYPE'], 5, (strlen($row['COLUMN_TYPE'])-6))));
                                  foreach($enumList as $value) {
                                      echo "<option value='$value'>$value</option>";
                                  }
                                  ?>
                                  </select>
                                </div>
                              </div>
                              <div class="form-group">
                                <div class="form-line">
                                    <label for="kamar">Kamar</label>
                                    <select name="kamar" class="form-control kamar" id="kamar" style="width:100%"></select>
                                            <br/>
                                    <input type="hidden" class="form-control" id="hrgkmr" name="hrgkmr"/>
                                </div>
                              </div>
                              <div class="form-group">
                                <div class="form-line">
                                    <label for="dx">Diagnosa</label>
                                    <input type="text" class="form-control" name="dx" value="">
                                </div>
                              </div>
                              <div class="form-group">
                                <div class="form-line">
                                    <label for="tglplg">Tanggal</label>
                                    <input type="text" class="datepicker form-control" name="tgl" value="<?php echo $date; ?>">
                                </div>
                              </div>                  
                            </div>
                            <div class="modal-footer">
                                <input type="hidden" name="no_rawat" value="<?php echo $no_rawat;?>">
                                <button type="submit" class="btn btn-success waves-effect" name="submit" onclick="this.value=\'submit\'">SIMPAN</button><a href="../pasien-ralan.php" class="btn btn-link">KEMBALI</a>
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
<script type="text/javascript">
  function formatInputData (data) {
              var $data = $(
                  '<b>Bed '+ data.id +'</b> '+ data.kelas +' - <i>'+ data.text +'</i>'
              );
              return $data;
          };
  function formatDataTEXT (data) {
            var $data = $(
                '<b>'+ data.id +'</b>'
            );
            return $data;
        };
  $('.kamar').select2({
          placeholder: 'Pilih kamar',
          ajax: {
            url: 'select-kamar.php',
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
              return {
                results: data
              };
            },
            cache: true
          },
          templateResult: formatInputData,
      	minimumInputLength: 3
        });
  
  	$('.kamar').on('change', function () {
         var kode = $("#kamar").val();
         $.ajax({
         	url: 'biayabed.php',
         	data: "kode="+kode,
         }).success(function (data){
           var json = data,
               obj = JSON.parse(json);
           		$('#kmr').val(obj.tarif);
           });
        });
</script>
