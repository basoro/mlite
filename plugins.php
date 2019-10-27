<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : dentix.id@gmail.com
* Licence under GPL
***/

$title = 'Pengaturan Plugins';
include_once('config.php');
include_once('layout/header.php');
include_once('layout/sidebar.php');

?>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                <?php echo $title; ?>
                            </h2>
                        </div>
                        <div class="body">
                          <?php
                          if($_SERVER['REQUEST_METHOD'] == "POST") {
                            if(isset($_FILES["zip_file"]["name"])) {
                            	$filename = $_FILES["zip_file"]["name"];
                            	$source = $_FILES["zip_file"]["tmp_name"];
                            	$type = $_FILES["zip_file"]["type"];

                            	$name = explode(".", $filename);
                            	$accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
                            	foreach($accepted_types as $mime_type) {
                            		if($mime_type == $type) {
                            			$okay = true;
                            			break;
                            		}
                            	}

                            	$continue = strtolower($name[1]) == 'zip' ? true : false;
                            	if(!$continue) {
                            		$message = "The file you are trying to upload is not a .zip file. Please try again.";
                            	}

                            	$target_path = ABSPATH.''.$filename;
                            	if(move_uploaded_file($source, $target_path)) {
                            		$zip = new ZipArchive();
                            		$x = $zip->open($target_path);
                            		if ($x === true) {
                            			$zip->extractTo(ABSPATH.'modules/');
                            			$zip->close();

                            			unlink($target_path);
                            		}
                            		$message = "Your .zip file was uploaded and unpacked.";
                            	} else {
                            		$message = "There was a problem with the upload. Please try again.";
                            	}
                            }
                            if(isset($_POST['dirmodule'])) {
                              remove_directory(ABSPATH."modules/".$_POST['dirmodule']);
                            }
                          }
                          ?>
                          <div class="card">
                            <div class="body">
                              <?php if(isset($message)) echo "<p>$message</p>"; ?>
                              <form enctype="multipart/form-data" method="post" action="">
                              <label>Choose a zip file to upload: <br><input type="file" name="zip_file" /></label>
                              <input class="btn btn-primary"type="submit" name="submit" value="Upload" />
                              </form>
                            </div>
                          </div>
                          <div class="module">
                            <table id="datatable" class="table table-bordered table-striped table-hover display" width="100%">
                              <thead>
                                <tr>
                                  <th>Plugins</th>
                                  <th>Deskripsi singkat</th>
                                  <th>Type</th>
                                  <th>Pembuat</th>
                                  <th>Aksi</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php
                                  foreach (glob("modules/*/index.php") as $filename) {
                                    include $filename;
                                  }
                                ?>
                              </tbody>
                            </table>
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
