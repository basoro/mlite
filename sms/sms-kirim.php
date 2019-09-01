<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Kirim SMS';
include_once('../config.php');
include_once('../layout/header.php');
include_once('../layout/sidebar.php');

if($_SERVER['REQUEST_METHOD'] == "POST") {

  $pesan = $_POST['pesan'];
  $notelp = $_POST['sender'];

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
                                KIRIM SMS
                            </h2>
                        </div>
                        <div class="body">
                          <div class="row clearfix">
                            <form action="" method="post">
                              <div class="col-sm-12">
                                  <div class="form-group form-float form-group-lg">
                                      <div class="form-line">
                                          <textarea name="pesan" rows="4" class="form-control no-resize"></textarea>
                                          <label class="form-label">Pesan SMS</label>
                                      </div>
                                  </div>
                                  <div class="form-group form-float form-group-lg">
                                      <div class="form-line">
                                          <input type="text" name="sender" class="form-control" />
                                          <label class="form-label">No Penerima</label>
                                      </div>
                                  </div>
                              </div>
                              <div class="col-sm-12">
                                <button type="submit" class="btn bg-indigo waves-effect">KIRIM</button>
                              </div>
                            </form>
                          </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
include_once('../layout/footer.php');
?>
