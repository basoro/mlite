<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : dentix.id@gmail.com
* Licence under GPL
***/

$title = 'Catatan Pembaruan';
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
                          <p>Terima kasih, selalu perbarui sistem Khanza Lite anda agar selalu aman dan mendapat fitur-fitur terbaru!</p>
                          <h2>Versi anda: <?php echo VERSION; ?></h2>
                          <?php $last = json_decode($json_updates, true); $last[] = $last; if($last['0']['versi'] > VERSION) { echo '<div class="alert bg-pink alert-dismissible">Silahkan Update Ke V.'.$last[0]['versi'].'!<br> Backup dulu sistem anda. Khususnya file config.php. Kemudian download direpo Khanza Lite dan timpa semua file. <br>Sesuaikan kembali file config.php dengan pengaturan sebelumnya.</div>'; } else { echo '<div class="alert bg-green alert-dismissible">Selamat..!! Versi anda sudah terbaru!!</div>'; } ?>
                          <div class="col" style="padding-top:20px;">
                              <h4>Catatan Perubahan</h4>
                              <hr>
                          <?php
                          $results = json_decode($json_updates, true);
                          foreach($results as $key=>$value) {
                            echo '<dl class="dl-horizontal">';
                            echo "<dt>Versi :</dt> <dd>".$value['versi']."</dd>";
                            echo "<dt>Perubahan :</dt> <dd>".str_replace("\r\n","<br>", $value['perubahan'])."</dd>";
                            echo '</dl>';
                          }
                          ?>
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
