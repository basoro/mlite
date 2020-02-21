<?php

if(isset($_GET['no_rkm_medis']) && $_GET['no_rkm_medis'] !=='') {
  $no_rkm_medis = $_GET['no_rkm_medis'];
} else if(isset($_GET['no_rawat']) && $_GET['no_rawat'] !=='') {
  $get_no_rkm_medis = fetch_assoc(query("SELECT no_rkm_medis FROM reg_periksa WHERE no_rawat = '{$_GET['no_rawat']}'"));
  $no_rkm_medis = $get_no_rkm_medis['no_rkm_medis'];
} else {
  redirect(URL.'/index.php?module=Odontogram&page=index');
} ?>

<?php
if(num_rows(query("SHOW TABLES LIKE 'pemeriksaan_odontogram'")) !== 1) {
  echo '<div class="alert bg-pink alert-dismissible text-center">';
  echo '<p class="lead">Belum terinstall Database Odontogram</p>';
  echo '<a href="'.URL.'/index.php?module=Odontogram&page=install" class="btn btn-lg btn-primary m-t-20" style="color:#fff;">Install Sekarang</a>';
  echo '</div>';
} else {

  $action = isset($_GET['action'])?$_GET['action']:null;

  $_sql = "SELECT * FROM pasien WHERE no_rkm_medis = '$no_rkm_medis'";
  $found_pasien = query($_sql);
  if(num_rows($found_pasien) == 1) {
     while($row = fetch_array($found_pasien)) {
        $no_rkm_medis  = $row['no_rkm_medis'];
        $nm_pasien     = $row['nm_pasien'];
        $umur          = $row['umur'];
     }
   }

  if($action == "delete_odontogram"){
        $hapus = "DELETE FROM pemeriksaan_odontogram WHERE no_rawat='{$_REQUEST['no_rawat']}' AND gg_xx = '{$_REQUEST['gg_xx']}'";
        $hasil = query($hapus);
  }

  if (isset($_POST['ok_odont'])) {
        if (($_POST['ok_odont'] <> "") and ($_POST['value'] <> "")) {
              $insert = query("INSERT INTO pemeriksaan_odontogram VALUES ('{$no_rkm_medis}','{$_GET['no_rawat']}',CURRENT_DATE(),CURRENT_TIME(),'{$_POST['gg_xx']}','{$_POST['value']}','{$_POST['catatan']}')");
        };
  };

?>

<?php if(isset($_GET['no_rkm_medis']) && $_GET['no_rkm_medis'] !=='') { ?>
<div class="body">
  <dl class="dl-horizontal">
    <dt>Nama Lengkap</dt>
    <dd><?php echo $nm_pasien; ?></dd>
    <dt>No. RM</dt>
    <dd><?php echo $no_rkm_medis; ?></dd>
    <dt>Umur</dt>
    <dd><?php echo $umur; ?> Th</dd>
  </dl>
</div>
<hr>
<?php } ?>

<div class="table-odontogram">
<table style="margin: 0 auto; width: 450px; text-align: center;">
   <tr>
   <td>8</td><td>7</td><td>6</td><td>5</td><td>4</td><td>3</td><td>2</td><td>1</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td>
  </tr>
  <tr>
  <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_18 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_18' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_18['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_18['value'].".png'>"; } ?></td>
  <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_17 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_17' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_17['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_17['value'].".png'>"; } ?></td>
  <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_16 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_16' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_16['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_16['value'].".png'>"; } ?></td>
  <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_15 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_15' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_15['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_15['value'].".png'>"; } ?></td>
  <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_14 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_14' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_14['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_14['value'].".png'>"; } ?></td>
  <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_13 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_13' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_13['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_13['value'].".png'>"; } ?></td>
  <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_12 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_12' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_12['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_12['value'].".png'>"; } ?></td>
  <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_11 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_11' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_11['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_11['value'].".png'>"; } ?></td>
  <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_21 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_21' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_21['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_21['value'].".png'>"; } ?></td>
  <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_22 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_22' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_22['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_22['value'].".png'>"; } ?></td>
  <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_23 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_23' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_23['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_23['value'].".png'>"; } ?></td>
  <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_24 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_24' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_24['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_24['value'].".png'>"; } ?></td>
  <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_25 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_25' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_25['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_25['value'].".png'>"; } ?></td>
  <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_26 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_26' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_26['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_26['value'].".png'>"; } ?></td>
  <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_27 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_27' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_27['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_27['value'].".png'>"; } ?></td>
  <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_28 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_28' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_28['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_28['value'].".png'>"; } ?></td>
  </tr>
  <tr>
    <td style="height: 5px;"> </td>
  </tr>
  <tr>
  <td> </td>
  <td> </td>
  <td> </td>
  <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_55 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_55' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_55['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_55['value'].".png'>"; } ?></td>
  <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_54 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_54' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_54['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_54['value'].".png'>"; } ?></td>
  <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_53 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_53' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_53['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_53['value'].".png'>"; } ?></td>
  <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_52 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_52' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_52['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_52['value'].".png'>"; } ?></td>
  <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_51 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_51' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_51['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_51['value'].".png'>"; } ?></td>
  <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_61 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_61' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_61['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_61['value'].".png'>"; } ?></td>
  <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_62 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_62' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_62['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_62['value'].".png'>"; } ?></td>
  <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_63 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_63' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_63['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_63['value'].".png'>"; } ?></td>
  <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_64 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_64' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_64['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_64['value'].".png'>"; } ?></td>
  <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_65 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_65' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_65['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_65['value'].".png'>"; } ?></td>
    <td> </td>
    <td> </td>
    <td> </td>

    </tr>
  <tr>
    <td> </td><td> </td><td> </td><td>5</td><td>4</td><td>3</td><td>2</td><td>1</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td> </td><td> </td><td> </td>
  </tr>
  <tr>
    <td> </td>
    <td> </td>
    <td> </td>
    <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_85 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_85' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_85['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_85['value'].".png'>"; } ?></td>
    <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_84 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_84' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_84['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_84['value'].".png'>"; } ?></td>
    <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_83 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_83' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_83['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_83['value'].".png'>"; } ?></td>
    <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_82 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_82' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_82['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_82['value'].".png'>"; } ?></td>
    <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_81 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_81' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_81['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_81['value'].".png'>"; } ?></td>
    <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_71 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_71' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_71['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_71['value'].".png'>"; } ?></td>
    <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_72 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_72' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_72['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_72['value'].".png'>"; } ?></td>
    <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_73 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_73' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_73['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_73['value'].".png'>"; } ?></td>
    <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_74 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_74' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_74['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_74['value'].".png'>"; } ?></td>
    <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_75 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_75' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_75['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_75['value'].".png'>"; } ?></td>
    <td> </td>
    <td> </td>
    <td> </td>
    </tr>
    <tr>
      <td style="height: 5px;"> </td>
    </tr>
    <tr>
    <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_48 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_48' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_48['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_48['value'].".png'>"; } ?></td>
    <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_47 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_47' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_47['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_47['value'].".png'>"; } ?></td>
    <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_46 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_46' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_46['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_46['value'].".png'>"; } ?></td>
    <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_45 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_45' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_45['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_45['value'].".png'>"; } ?></td>
    <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_44 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_44' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_44['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_44['value'].".png'>"; } ?></td>
    <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_43 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_43' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_43['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_43['value'].".png'>"; } ?></td>
    <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_42 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_42' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_42['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_42['value'].".png'>"; } ?></td>
    <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_41 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_41' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_41['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_41['value'].".png'>"; } ?></td>
    <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_31 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_31' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_31['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_31['value'].".png'>"; } ?></td>
    <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_32 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_32' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_32['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_32['value'].".png'>"; } ?></td>
    <td class="gigi_anterior" style="height: 25px; width: 25px;"><?php $gg_33 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_33' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_33['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_33['value'].".png'>"; } ?></td>
    <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_34 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_34' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_34['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_34['value'].".png'>"; } ?></td>
    <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_35 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_35' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_35['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_35['value'].".png'>"; } ?></td>
    <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_36 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_36' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_36['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_36['value'].".png'>"; } ?></td>
    <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_37 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_37' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_37['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_37['value'].".png'>"; } ?></td>
    <td class="gigi_posterior" style="height: 25px; width: 25px;"><?php $gg_38 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_38' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if(!empty($gg_38['value'])) { echo "<img src='".URL."/modules/Odontogram/img/".$gg_38['value'].".png'>"; } ?></td>
    </tr>

  <tr>
    <td>8</td><td>7</td><td>6</td><td>5</td><td>4</td><td>3</td><td>2</td><td>1</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td>
  </tr>
</table>
<br/><br/>

<table style="margin: 0 auto; width: 450px;">
  <tr>
    <td><img src="<?php echo URL; ?>/modules/Odontogram/img/Erupsi.png"></td>
    <td> = Erupsi</td>
    <td><img src="<?php echo URL; ?>/modules/Odontogram/img/Tanggal.png"></td>
    <td> = Hilang</td>
    <td><img src="<?php echo URL; ?>/modules/Odontogram/img/Karies.png"></td>
    <td> = Karies</td>
  </tr>
  <tr>
    <td style="height: 5px;"> </td>
  </tr>
  <tr>
    <td><img src="<?php echo URL; ?>/modules/Odontogram/img/Akar.png"></td>
    <td> = Sisa Akar</td>
    <td><img src="<?php echo URL; ?>/modules/Odontogram/img/Tumpat.png"></td>
    <td> = Tumpatan</td>
    <td><img src="<?php echo URL; ?>/modules/Odontogram/img/Goyang.png"></td>
    <td> = Goyang</td>
  </tr>
</table>
</div>
<br><br>
<?php if(isset($_GET['no_rawat']) && $_GET['no_rawat'] !=='') { ?>
<form method="POST" action="">
  <div class="row clearfix">
      <div class="col-md-12">
          <p>
              <b>Pemeriksaan</b>
          </p>
          <select name="gg_xx" class="form-control show-tick" data-size="4" data-live-search="true">
            <option value="gg_18">Gigi 18</option>
            <option value="gg_17">Gigi 17</option>
            <option value="gg_16">Gigi 16</option>
            <option value="gg_15">Gigi 15</option>
            <option value="gg_14">Gigi 14</option>
            <option value="gg_13">Gigi 13</option>
            <option value="gg_12">Gigi 12</option>
            <option value="gg_11">Gigi 11</option>
            <option value="gg_21">Gigi 21</option>
            <option value="gg_22">Gigi 22</option>
            <option value="gg_23">Gigi 23</option>
            <option value="gg_24">Gigi 24</option>
            <option value="gg_25">Gigi 25</option>
            <option value="gg_26">Gigi 26</option>
            <option value="gg_27">Gigi 27</option>
            <option value="gg_28">Gigi 28</option>
            <option value="gg_38">Gigi 38</option>
            <option value="gg_37">Gigi 37</option>
            <option value="gg_36">Gigi 36</option>
            <option value="gg_35">Gigi 35</option>
            <option value="gg_34">Gigi 34</option>
            <option value="gg_33">Gigi 33</option>
            <option value="gg_32">Gigi 32</option>
            <option value="gg_31">Gigi 31</option>
            <option value="gg_41">Gigi 41</option>
            <option value="gg_42">Gigi 42</option>
            <option value="gg_43">Gigi 43</option>
            <option value="gg_44">Gigi 44</option>
            <option value="gg_45">Gigi 45</option>
            <option value="gg_46">Gigi 46</option>
            <option value="gg_47">Gigi 47</option>
            <option value="gg_48">Gigi 48</option>

            <option value="gg_55">Gigi 55</option>
            <option value="gg_54">Gigi 54</option>
            <option value="gg_53">Gigi 53</option>
            <option value="gg_52">Gigi 52</option>
            <option value="gg_51">Gigi 51</option>
            <option value="gg_61">Gigi 61</option>
            <option value="gg_62">Gigi 62</option>
            <option value="gg_63">Gigi 63</option>
            <option value="gg_64">Gigi 64</option>
            <option value="gg_65">Gigi 65</option>
            <option value="gg_75">Gigi 75</option>
            <option value="gg_74">Gigi 74</option>
            <option value="gg_73">Gigi 73</option>
            <option value="gg_72">Gigi 72</option>
            <option value="gg_71">Gigi 71</option>
            <option value="gg_81">Gigi 81</option>
            <option value="gg_82">Gigi 82</option>
            <option value="gg_83">Gigi 83</option>
            <option value="gg_84">Gigi 84</option>
            <option value="gg_85">Gigi 85</option>

          </select>
      </div>
      <div class="col-md-12">
          <p>
              <b>Hasil Pemeriksaan</b>
          </p>
          <select name="value" class="form-control show-tick" data-live-search="true">
            <option value="">------------</option>
            <option value="Erupsi">Erupsi</option>
            <option value="Tanggal">Tanggal</option>
            <option value="Karies">Karies</option>
            <option value="Akar">Sisa Akar</option>
            <option value="Tumpat">Tumpatan</option>
            <option value="Goyang">Goyang</option>
          </select>
      </div>
      <div class="col-md-12">
          <p>
              <b>Catatan Pemeriksaan</b>
          </p>
          <textarea name="catatan" class="form-control" col="4" row="4"></textarea>
      </div>

      <div class="col-md-12">
        <button type="submit" name="ok_odont" value="ok_odont" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_odont\'">SIMPAN</button>
      </div>

  </div>
</form>
<?php } ?>
<br>
<p class="lead">History Odontogram</p>
<table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Pemeriksaan</th>
            <th>Catatan</th>
            <?php if(isset($_GET['no_rawat']) && $_GET['no_rawat'] !=='') { ?>
            <th>Tools</th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
    <?php
    $query_odontogram = query("SELECT *  FROM pemeriksaan_odontogram WHERE no_rkm_medis = '{$no_rkm_medis}'");
    while ($data_odontogram = fetch_array($query_odontogram)) {
      if($data_odontogram['5'] == 'Akar') {
        $data_value = 'Sisa Akar';
      } else if($data_odontogram['5'] == 'Tumpat') {
        $data_value = 'Tumpatan';
      } else {
        $data_value = $data_odontogram['5'];
      }
    ?>
        <tr>
            <td><?php echo $data_odontogram['2']; ?></td>
            <td><?php echo "Gigi ".ltrim($data_odontogram['4'], 'gg_'); ?> <?php echo $data_value; ?></td>
            <td><?php echo $data_odontogram['6']; ?></td>
            <?php if(isset($_GET['no_rawat']) && $_GET['no_rawat'] !=='') { ?>
            <td><a href="./index.php?module=Odontogram&page=history&no_rawat=<?php echo $no_rawat; ?>&gg_xx=<?php echo $data_odontogram['4']; ?>&action=delete_odontogram">Hapus</a></td>
            <?php } ?>
        </tr>
    <?php
    }
    ?>
    </tbody>
</table>

<?php } ?>
