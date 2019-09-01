<?php
/***
* SIMRS Khanza Lite from version 1.0
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'SMS Masuk';
include_once('../config.php');
include_once('../layout/header.php');
include_once('../layout/sidebar.php');

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM sms WHERE id_pesan = '$_GET[id]'";
    $found = query($sql);
    if(num_rows($found) == 1) {
	     while($row = fetch_array($found)) {
	        $sms_masuk    = $row['1'];
	        $no_hp	      = $row['2'];
          $tgl_sms      = $row['6'];
	        $stts_baca    = $row['7'];
	        $stts_balas   = $row['8'];
	     }
    } else {
	     redirect ($_SERVER['PHP_SELF']);
    }
}

if($_SERVER['REQUEST_METHOD'] == "POST") {

  $pesan = $_POST['pesan'];
  $id = $_POST['id'];
  $notelp = $_POST['sender'];

  $query = "UPDATE sms SET stts_balas = '1' WHERE id_pesan = '$id'";
  query($query);

  $pesan = str_replace("\r"," ",$pesan);
  $pesan = str_replace("\n","",$pesan);
  $pesan = str_replace('"','',$pesan);
  $pesan = str_replace("'","",$pesan);

  $insert = "INSERT INTO outbox (DestinationNumber, TextDecoded, CreatorID) VALUES ('$notelp', '$pesan', '$notelp')";
  query($insert);

  if($insert){
  	redirect($_SERVER['PHP_SELF']);
  }
}

?>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                SMS MASUK
                            </h2>
                        </div>
                        <?php display_message(); ?>
                        <?php
                        $action = isset($_GET['action'])?$_GET['action']:null;
                        $option = isset($_GET['option'])?$_GET['option']:null;
                        if(!$action){
                          if(!$option){
                        ?>
                        <div class="body">
                            <div id="buttons" class="align-center m-l-10 m-b-15 export-hidden"></div>
                            <table id="datatable" class="table table-bordered display nowrap js-exportable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Pengirim</th>
                                        <th>Isi SMS</th>
                                        <th>Waktu</th>
                                        <th>Status Balas</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $sql = "SELECT * FROM sms";
                                if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) {
                                	$sql .= " WHERE (tgl_sms BETWEEN '$_POST[tgl_awal] 00:00:01' AND '$_POST[tgl_akhir] 23:59:59')";
                                }
                                $sql .= " ORDER BY tgl_sms";
                                $query = query($sql);
                                $no = 1;
                                while($row = fetch_array($query)) {

                                  if ($row['7'] == 0) $color = "bg-warning";
                                  else $color = "";

                                  if ($row['8'] == 0) $status = "<b>Belum</b>";
                                  else $status = "<b>[Sudah]</b>";

                                ?>
                                    <tr class="<?php echo $color; ?>">
                                        <td><?php echo $row['2']; ?></td>
                                        <td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=view&id=<?php echo $row['0'];?>"><?php echo $row['1']; ?></a></td>
                                        <td><?php echo $row['6']; ?></td>
                                        <td><?php echo $status; ?></td>
                                        <td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=delete&id=<?php echo $row['0'];?>">Hapus</a></td>
                                    </tr>
                                <?php
                                }
                                ?>
                                </tbody>
                            </table>
                            <div class="row clearfix">
                                <form method="post" action="">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <input type="text" name="tgl_awal" class="datepicker form-control" placeholder="Pilih tanggal awal...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <input type="text" name="tgl_akhir" class="datepicker form-control" placeholder="Pilih tanggal akhir...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <input type="submit" class="btn bg-blue btn-block btn-lg waves-effect">
                                        </div>
                                    </div>
                                </div>
                                </form>
                            </div>
                        </div>
                        <?php }
                            }
                        ?>
                        <?php if($action == "view") { ?>
                        <div class="body">
                          <dl class="dl-horizontal">
                              <dt>Nomor Pengirim</dt>
                              <dd><?php echo $no_hp; ?></dd>
                              <dt>Waktu pengiriman</dt>
                              <dd><?php echo $tgl_sms; ?></dd>
                              <dt>Pesan</dt>
                              <dd><?php echo $sms_masuk; ?></dd>
                              <br><br>
                              <dt></dt>
                              <dd><a href="<?php echo $_SERVER['PHP_SELF']; ?>?option=reply&id=<?php echo $id; ?>" class="btn bg-blue btn-lg waves-effect">Reply</a></dd>
                          </dl>
                        </div>
                        <?php } ?>
                        <?php if($option == "reply") { ?>
                        <div class="body">
                          <h2 class="card-inside-title">Pesan SMS</h2>
                          <div class="row clearfix">
                            <form action="" method="post">
                              <div class="col-sm-12">
                                  <div class="form-group">
                                      <div class="form-line">
                                          <textarea name="pesan" rows="4" class="form-control no-resize" placeholder="Please type what you want..."></textarea>
                                          <input type="hidden" name="sender" value="<?php echo $no_hp; ?>">
                                          <input type="hidden" name="id" value="<?php echo $id; ?>">
                                      </div>
                                  </div>
                              </div>
                              <div class="col-sm-12">
                                <button type="submit" class="btn bg-indigo waves-effect">KIRIM</button>
                              </div>
                            </form>
                          </div>
                            <div class="header">
                              <h2>
                                  HISTORY SMS
                              </h2>
                          </div>
                          <br>
                          <table id="datatable" class="table table-bordered table-hover table-striped display nowrap js-exportable" width="100%">
                              <thead>
                                  <tr>
                                      <th>Pengirim</th>
                                      <th>Isi SMS</th>
                                      <th>Waktu</th>
                                  </tr>
                              </thead>
                              <tbody>
                              <?php
                              $sql = "SELECT * FROM sms WHERE no_hp = '$no_hp' AND id_pesan <> '$id' ORDER BY tgl_sms DESC";
                              $query = query($sql);
                              while($row = fetch_array($query)) {
                              ?>
                                  <tr>
                                      <td><?php echo $row['2']; ?></td>
                                      <td><?php echo $row['1']; ?></td>
                                      <td><?php echo $row['6']; ?></td>
                                  </tr>
                              <?php
                              }
                              ?>
                              </tbody>
                          </table>
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
