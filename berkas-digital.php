<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Berkas Digital';
include_once('config.php');
include_once('layout/header.php');
include_once('layout/sidebar.php');
if (isset($_GET['no_rawat'])) {
  $_sql = "SELECT a.no_rkm_medis, a.no_rawat, b.nm_pasien, b.umur FROM reg_periksa a, pasien b WHERE a.no_rkm_medis = b.no_rkm_medis AND a.no_rawat = '$_GET[no_rawat]'";
  $found_pasien = query($_sql);
  if (num_rows($found_pasien) == 1) {
      while ($row = fetch_array($found_pasien)) {
          $no_rkm_medis  = $row['0'];
          $get_no_rawat	     = $row['1'];
          $no_rawat	     = $row['1'];
          $nm_pasien     = $row['2'];
          $umur          = $row['3'];
      }
  } else {
      redirect('pasien-ralan.php');
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
                                BERKAS DIGITAL PERAWATAN
                            </h2>
                        </div>
                        <div class="body">
                          <?php
                            if (isset($_POST['ok_berdig'])) {
                              $periksa_radiologi = fetch_assoc(query("SELECT tgl_periksa, jam FROM periksa_radiologi WHERE no_rawat = '{$no_rawat}'"));
                              $date = $periksa_radiologi['tgl_periksa'];
                              $time = $periksa_radiologi['jam'];
                                    //$photo_berkas=fetch_array(query("SELECT lokasi_file FROM berkas_digital_perawatan WHERE kode = '{$kode_berkas}' AND no_rawat='{$no_rawat}'"));
                                    //$kode_berkas = $_POST['kode'];
                              if($_FILES['file']['name']!='') {
                                    //$file='../webapps/berkasrawat/'.$photo_berkas;
                                    //@unlink($file);
                                $tmp_name = $_FILES["file"]["tmp_name"];
                                $namefile = $_FILES["file"]["name"];
                                $explode = explode(".", $namefile);
                                $ext = end($explode);
                                if($_POST['masdig']=='001') {
                                    $image_name = "berkasdigital-".time().".".$ext;
                                }else{
                                    $image_name = "rujukanfktp-".time().".".$ext;
                                }
                                move_uploaded_file($tmp_name,"../berkasrawat/pages/upload/".$image_name);
                                $lokasi_berkas = 'pages/upload/'.$image_name;
                                $insert_berkas = query("INSERT INTO berkas_digital_perawatan VALUES('$no_rawat','{$_POST['masdig']}', '$lokasi_berkas')");
                                if($insert_berkas) {
                                  set_message('Berkas digital perawatan telah ditersimpan.');
                                  redirect("pasien-ralan.php");
                                }
                              }
                            }
                          ?>
                              <div class="row">
                              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="body">
                                <dl class="dl-horizontal">
                                    <dt>Nama Lengkap</dt>
                                    <dd><?php echo $nm_pasien; ?></dd>
                                    <dt>No. RM</dt>
                                    <dd><?php echo $no_rkm_medis; ?></dd>
                                    <dt>No. Rawat</dt>
                                    <dd><?php echo $no_rawat; ?></dd>
                                    <dt>Umur</dt>
                                    <dd><?php echo $umur; ?></dd>
                                </dl>
                              <hr>
                              <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                              <?php
                                $sql_rad = query("select * from berkas_digital_perawatan where no_rawat= '{$_GET['no_rawat']}'");
                                $no=1;
                                while ($row_rad = fetch_array($sql_rad)) {
                                  echo '<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">';
                                  echo '<a href="'.URLSIMRS.'/berkasrawat/'.$row_rad[2].'" data-sub-html=""><img class="img-responsive thumbnail"  src="'.URLSIMRS.'/radiologi/'.$row_rad[3].'"></a>';
                                  echo '</div>';
                                  $no++;
                                }
                              ?>
                              </div>
                            <hr>
                              </div>
                                <div class="body">
                                  <form id="form_validation" name="berdigi" action="" method="POST"  enctype="multipart/form-data">
                                      <label for="email_address">Unggah Berkas Digital Perawatan</label>
                                      <div class="form-group">
                                        <select class="form-control" name="masdig">
                                          <option value="001">Berkas SEP</option>
                                          <option value="002">Berkas Rujukan</option>
                                        </select>
                                          <img id="image_upload_preview" width="200px" src="images/upload_berkas.png" onclick="upload_berkas()" style="cursor:pointer;" />
                                          <br/>
                                          <input name="file" id="inputFile" type="file" style="display:none;"/>
                                      </div>
                                      <button type="submit" name="ok_berdig" value="ok_berdig" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_berdig\'">UPLOAD BERKAS</button>
                                  </form>
                                </div>
                              </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
include_once('layout/footer.php');
?>
