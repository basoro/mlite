<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Set Kamar';
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
                                SET KAMAR
                            </h2>
                        </div>
                      	<?php
                        $action = isset($_GET['action'])?$_GET['action']:null;
                        if(!$action){?>
                        <div class="body">
                            <table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                              <thead>
                                <tr>
                                  <th>Nama Ruangan</th>
                                  <th>Bed</th>
                                  <th>Tarif</th>
                                  <th>Kelas</th>
                                  <th>Status</th>
                                  <th>Tools</th>
                                </tr>
                              </thead>
                              <tbody>
                                  <?php
                                      $sql = "SELECT b.nm_bangsal , a.kd_kamar , a.trf_kamar , a.kelas , a.status FROM kamar a, bangsal b WHERE a.kd_bangsal = b.kd_bangsal AND a.statusdata = '1'";
                                      $list = query($sql);
                                      while($a = mysqli_fetch_assoc($list)) {
                                  ?>
                                <tr>
                                  <td name="nm_bangsal"><?php echo $a['nm_bangsal'];?></td>
                                  <td name="kd_kamar"><?php echo $a['kd_kamar'];?></td>
                                  <td name="trf_kamar"><?php echo $a['trf_kamar'];?></td>
                                  <td name="kelas"><?php echo $a['kelas'];?></td>
                                  <td name="status"><?php echo $a['status'];?></td>
                                  <td><a class="btn btn-success"href="<?php echo $_SERVER['PHP_SELF']; ?>?action=ubah&kd_kamar=<?php echo $a['kd_kamar']; ?>">Ubah</a></td>
                                </tr>
                                <?php } ?>
                              </tbody>
                            </table>
                        </div>
                      <?php } ?>
                      <?php if($action == "ubah"){
                      	if (isset($_POST['ok_set'])) {
        		                if (($_POST['kd_kamar'] <> "")) {
        			                  $insert = query("UPDATE kamar SET status = '{$_POST['stts_pulang']}' WHERE kd_kamar = '{$_POST['kd_kamar']}'");
        			                  if ($insert) {
        			                      redirect("setkmr.php");
        			                  };
        		                };
        	              };?>
                      <div class="body">
                        <form method="POST">
                          <div class="row">
                              <div class="col-md-12">
                                <div class="form-group">
                                  <div class="form-line">
                                      <input type="text" class="form-control" name="kd_kamar" readonly value="<?php echo $_GET['kd_kamar'];?>" />
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-12">
                                <div class="form-group">
                                  <div class="form-line">
                                    <select name="stts_pulang" class="form-control show-tick">
                                      <option value='ISI'>ISI</option>
                                      <option value='KOSONG'>KOSONG</option>
                                    </select>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-12">
                                <div class="form-group">
                                  <button type="submit" name="ok_set" value="ok_set" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_set\'">SIMPAN</button>
                                </div>
                              </div>
                          </div>
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
