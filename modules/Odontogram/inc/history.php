<?php
if(isset($_GET['no_rkm_medis']) && $_GET['no_rkm_medis'] !=='') {
  $no_rkm_medis = $_GET['no_rkm_medis'];
} else if(isset($_GET['no_rawat']) && $_GET['no_rawat'] !=='') {
  $get_no_rkm_medis = fetch_assoc(query("SELECT no_rkm_medis FROM reg_periksa WHERE no_rawat = '{$_GET['no_rawat']}'"));
  $no_rkm_medis = $get_no_rkm_medis['no_rkm_medis'];
} else {
  //redirect(URL.'/?module=Odontogram&page=index');
} ?>

<?php
if(num_rows(query("SHOW TABLES LIKE 'pemeriksaan_odontogram'")) !== 1) {
  echo '<div class="alert bg-pink alert-dismissible text-center">';
  echo '<p class="lead">Belum terinstall Database Odontogram</p>';
  echo '<a href="'.URL.'/?module=Odontogram&page=install" class="btn btn-lg btn-primary m-t-20" style="color:#fff;">Install Sekarang</a>';
  echo '</div>';
} else {

?>
<div class="table-odontogram">
<table style="margin: 0 auto; width: 450px; text-align: center;">
   <tr>
   <td>8</td><td>7</td><td>6</td><td>5</td><td>4</td><td>3</td><td>2</td><td>1</td><td> </td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td>
  </tr>
  <tr>
  <td <?php $gg_18 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_18' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if($gg_18['value'] !='') { echo 'bgcolor="'.$gg_18['value'].'"'; } else { echo 'class="gigi_posterior"'; }; ?>></td>
  <td <?php $gg_17 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_17' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if($gg_17['value'] !='') { echo 'bgcolor="'.$gg_17['value'].'"'; } else { echo 'class="gigi_posterior"'; }; ?>></td>
  <td <?php $gg_16 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_16' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if($gg_16['value'] !='') { echo 'bgcolor="'.$gg_16['value'].'"'; } else { echo 'class="gigi_posterior"'; }; ?>></td>
  <td <?php $gg_15 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_15' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if($gg_15['value'] !='') { echo 'bgcolor="'.$gg_15['value'].'"'; } else { echo 'class="gigi_posterior"'; }; ?>></td>
  <td <?php $gg_14 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_14' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); if($gg_14['value'] !='') { echo 'bgcolor="'.$gg_14['value'].'"'; } else { echo 'class="gigi_posterior"'; }; ?>></td>
  <td class="gigi_anterior"><input type="text" name="gg_13" id="gg_13" value="<?php $gg_13 = fetch_assoc(query("SELECT value FROM pemeriksaan_odontogram WHERE gg_xx = 'gg_13' AND no_rkm_medis = '{$no_rkm_medis}' ORDER BY tgl_perawatan ASC LIMIT 1")); echo $gg_13['value']; ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_12" id="gg_12" value="<?php //echo @get_post_meta($post->ID, 'gg_12', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_11" id="gg_11" value="<?php //echo @get_post_meta($post->ID, 'gg_11', true); ?>" class="odont_input color"></td>
  <td> </td>
  <td class="gigi_anterior"><input type="text" name="gg_21" id="gg_21" value="<?php //echo @get_post_meta($post->ID, 'gg_21', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_22" id="gg_22" value="<?php //echo @get_post_meta($post->ID, 'gg_22', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_23" id="gg_23" value="<?php //echo @get_post_meta($post->ID, 'gg_23', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_24" id="gg_24" value="<?php //echo @get_post_meta($post->ID, 'gg_24', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_25" id="gg_25" value="<?php //echo @get_post_meta($post->ID, 'gg_25', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_26" id="gg_26" value="<?php //echo @get_post_meta($post->ID, 'gg_26', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_27" id="gg_27" value="<?php //echo @get_post_meta($post->ID, 'gg_27', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_28" id="gg_28" value="<?php //echo @get_post_meta($post->ID, 'gg_28', true);?>" class="odont_input color"></td>
    </tr>
  <tr>
    <td style="height: 5px;"> </td>
  </tr>
  <tr>
  <td> </td>
  <td> </td>
  <td> </td>
  <td class="gigi_posterior" bgcolor="#ffcc00"></td>
  <td class="gigi_posterior"><input type="text" name="gg_54" id="gg_54" value="<?php //echo @get_post_meta($post->ID, 'gg_54', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_53" id="gg_53" value="<?php //echo @get_post_meta($post->ID, 'gg_53', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_52" id="gg_52" value="<?php //echo @get_post_meta($post->ID, 'gg_52', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_51" id="gg_51" value="<?php //echo @get_post_meta($post->ID, 'gg_51', true); ?>" class="odont_input color"></td>
  <td> </td>
  <td class="gigi_anterior"><input type="text" name="gg_61" id="gg_61" value="<?php //echo @get_post_meta($post->ID, 'gg_61', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_62" id="gg_62" value="<?php //echo @get_post_meta($post->ID, 'gg_62', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_63" id="gg_63" value="<?php //echo @get_post_meta($post->ID, 'gg_63', true);?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_64" id="gg_64" value="<?php //echo @get_post_meta($post->ID, 'gg_64', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_65" id="gg_65" value="<?php //echo @get_post_meta($post->ID, 'gg_65', true); ?>" class="odont_input color"></td>
    <td> </td>
    <td> </td>
    <td> </td>

    </tr>
  <tr>
    <td> </td><td> </td><td> </td><td>V</td><td>IV</td><td>III</td><td>II</td><td>I</td><td> </td><td>I</td><td>II</td><td>III</td><td>IV</td><td>V</td><td> </td><td> </td><td> </td>
  </tr>
  <tr>
    <td> </td>
    <td> </td>
    <td> </td>
  <td class="gigi_posterior"><input type="text" name="gg_85" id="gg_85" value="<?php //echo @get_post_meta($post->ID, 'gg_85', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_84" id="gg_84" value="<?php //echo @get_post_meta($post->ID, 'gg_84', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_83" id="gg_83" value="<?php //echo @get_post_meta($post->ID, 'gg_83', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_82" id="gg_82" value="<?php //echo @get_post_meta($post->ID, 'gg_82', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_81" id="gg_81" value="<?php //echo @get_post_meta($post->ID, 'gg_81', true); ?>" class="odont_input color"></td>
  <td> </td>
  <td class="gigi_anterior"><input type="text" name="gg_71" id="gg_71" value="<?php //echo @get_post_meta($post->ID, 'gg_71', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_72" id="gg_72" value="<?php //echo @get_post_meta($post->ID, 'gg_72', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_73" id="gg_73" value="<?php //echo @get_post_meta($post->ID, 'gg_73', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_74" id="gg_74" value="<?php //echo @get_post_meta($post->ID, 'gg_74', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_75" id="gg_75" value="<?php //echo @get_post_meta($post->ID, 'gg_75', true); ?>" class="odont_input color"></td>
    <td> </td>
    <td> </td>
    <td> </td>
    </tr>
    <tr>
      <td style="height: 5px;"> </td>
    </tr>
  <tr>
  <td class="gigi_posterior"><input type="text" name="gg_48" id="gg_48" value="<?php //echo @get_post_meta($post->ID, 'gg_48', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_47" id="gg_47" value="<?php //echo @get_post_meta($post->ID, 'gg_47', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_46" id="gg_46" value="<?php //echo @get_post_meta($post->ID, 'gg_46', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_45" id="gg_45" value="<?php //echo @get_post_meta($post->ID, 'gg_45', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_44" id="gg_44" value="<?php //echo @get_post_meta($post->ID, 'gg_44', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_43" id="gg_43" value="<?php //echo @get_post_meta($post->ID, 'gg_43', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_42" id="gg_42" value="<?php //echo @get_post_meta($post->ID, 'gg_42', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_41" id="gg_41" value="<?php //echo @get_post_meta($post->ID, 'gg_41', true); ?>" class="odont_input color"></td>
  <td> </td>
  <td class="gigi_anterior"><input type="text" name="gg_31" id="gg_31" value="<?php //echo @get_post_meta($post->ID, 'gg_31', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_32" id="gg_32" value="<?php //echo @get_post_meta($post->ID, 'gg_32', true); ?>" class="odont_input color"></td>
  <td class="gigi_anterior"><input type="text" name="gg_33" id="gg_33" value="<?php //echo @get_post_meta($post->ID, 'gg_33', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_34" id="gg_34" value="<?php //echo @get_post_meta($post->ID, 'gg_34', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_35" id="gg_35" value="<?php //echo @get_post_meta($post->ID, 'gg_35', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_36" id="gg_36" value="<?php //echo @get_post_meta($post->ID, 'gg_36', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_37" id="gg_37" value="<?php //echo @get_post_meta($post->ID, 'gg_37', true); ?>" class="odont_input color"></td>
  <td class="gigi_posterior"><input type="text" name="gg_38" id="gg_38" value="<?php //echo @get_post_meta($post->ID, 'gg_38', true); ?>" class="odont_input color"></td>
    </tr>
  <tr>
    <td>8</td><td>7</td><td>6</td><td>5</td><td>4</td><td>3</td><td>2</td><td>1</td><td> </td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td>
  </tr>
</table>
<br/><br/>

<table style="margin: 0 auto; width: 450px;">
  <tr>
    <td style="height: 20px; width: 20px; background-color: #ffffff; border: 1px solid #000000; "></td>
    <td> = Normal</td>
    <td style="height: 20px; width: 20px; background-color: #FF0000; border: 1px solid #000000; "></td>
    <td> = Dicabut</td>
    <td style="height: 20px; width: 20px; background-color: #000000; border: 1px solid #000000; "></td>
    <td> = Hilang</td>
    <td style="height: 20px; width: 20px; background-color: #FFFF00; border: 1px solid #000000; "></td>
    <td> = Karies</td>
  </tr>
  <tr>
    <td style="height: 5px;"> </td>
  </tr>
  <tr>
    <td style="height: 20px; width: 20px; background-color: #FF6600; border: 1px solid #000000; "></td>
    <td> = Sisa Akar</td>
    <td style="height: 20px; width: 20px; background-color: #0000FF; border: 1px solid #000000; "></td>
    <td> = Tumpatan</td>
    <td style="height: 20px; width: 20px; background-color: #FF00FF; border: 1px solid #000000; "></td>
    <td> = Gigi Tiruan</td>
    <td style="height: 20px; width: 20px; background-color: #339966; border: 1px solid #000000; "></td>
    <td> = Goyang</td>
  </tr>
</table>
</div>
<br><br>
<form method="POST">
  <div class="row clearfix">
      <div class="col-md-6">
          <p>
              <b>Pemeriksaan</b>
          </p>
          <select class="form-control show-tick" data-live-search="true">
            <option value="gg_18">Gigi 18</option>
            <option value="gg_17">Gigi 17</option>
            <option value="gg_16">Gigi 16</option>
            <option value="gg_15">Gigi 15</option>
            <option value="gg_14">Gigi 14</option>
            <option value="gg_13">Gigi 13</option>
            <option value="gg_12">Gigi 12</option>
            <option value="gg_11">Gigi 11</option>
          </select>
      </div>
      <div class="col-md-6">
          <p>
              <b>Hasil Pemeriksaan</b>
          </p>
          <button class="btn odont_input color"></button>

      </div>
      <div class="col-md-12">
          <p>
              <b>Catatan Pemeriksaan</b>
          </p>
          <textarea name="catatan" class="form-control" col="4" row="4"></textarea>
      </div>
  </div>
</form>
<p class="lead">History Odontogram</p>
<table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">
    <thead>
        <tr>
            <th>Tanggal Tindakan</th>
            <th>Pemeriksaan</th>
            <th>Catatan</th>
            <th>Tools</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $query_odontogram = query("SELECT *  FROM pemeriksaan_odontogram WHERE no_rkm_medis = '{$no_rkm_medis}'");
    while ($data_odontogram = fetch_array($query_odontogram)) {
      if($data_odontogram['5'] == '#FF0000') {
        $data_value = 'Dicabut';
      } else if($data_odontogram['5'] == '#000000') {
        $data_value = 'Hilang';
      } else if($data_odontogram['5'] == '#FFFF00') {
        $data_value = 'Karies';
      } else if($data_odontogram['5'] == '#FF6600') {
        $data_value = 'Sisa Akar';
      } else if($data_odontogram['5'] == '#0000FF') {
        $data_value = 'Tumpatan';
      } else if($data_odontogram['5'] == '#FF00FF') {
        $data_value = 'Gigi Tiruan';
      } else if ($data_odontogram['5'] == '#339966') {
        $data_value = 'Goyang';
      } else {
        $data_value = 'Normal';
      }
    ?>
        <tr>
            <td><?php echo $data_odontogram['2']; ?></td>
            <td><?php echo "Gigi ".ltrim($data_odontogram['4'], 'gg_'); ?> <?php echo $data_value; ?></td>
            <td><?php echo $data_odontogram['6']; ?></td>
            <td><a href="./?module=Odontogram&page=index&action=delete_odontogram&no_rawat=<?php echo $no_rawat; ?>">Hapus</a></td>
        </tr>
    <?php
    }
    ?>
    </tbody>
</table>

<?php } ?>
