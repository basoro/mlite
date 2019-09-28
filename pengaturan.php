<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : dentix.id@gmail.com
* Licence under GPL
***/

$title = 'Pengaturan Aplikasi';
include_once('config.php');
include_once('layout/header.php');
include_once('layout/sidebar.php');

if($_SERVER['REQUEST_METHOD'] == "POST") {
  if(isset($_POST['fktl']) && $_POST['fktl'] == 'No') {
    file_put_contents('config.php', str_replace("\ndefine('FKTL', 'Yes')", "\ndefine('FKTL', 'No')", file_get_contents('config.php')));
  }
if(isset($_POST['fktl']) && $_POST['fktl'] == 'Yes') {
    file_put_contents('config.php', str_replace("\ndefine('FKTL', 'No')", "\ndefine('FKTL', 'Yes')", file_get_contents('config.php')));
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
                                <?php echo $title; ?>
                            </h2>
                        </div>
                        <form action="" method="POST">
                        <div class="body">
                          <h2 class="card-inside-title">FKTP atau FKTL</h2>
                          <select name="fktl">
                            <option value="No">FKTP</option>
                            <option value="Yes">FKTL</option>
                          </select>
                          <input type="submit" class="btn" value="Submit">
                        </div>
                        </form>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
include_once('layout/footer.php');
?>
