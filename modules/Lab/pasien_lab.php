<?php

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
        redirect('./?module=Lab&page=index');
    }
}
?>
<div class="card">
  <div class="header">
      <h2>Data Permintaan Lab</h2>
  </div>
  <div class="body">
    <?php
    $action = isset($_GET['action'])?$_GET['action']:null;
        if (!$action) {
            ?>
    <table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">
        <thead>
            <tr>
                <th>Nama</th>
                <th>No Rekam Medis</th>
                <th>Tindakan</th>
                <th>Dokter Perujuk</th>
                <th>Poli / Kamar</th>
            </tr>
        </thead>
        <tbody>
        <?php
            $sql = "SELECT ps.nm_pasien , rp.no_rkm_medis , dr.nm_dokter , pl.status , rp.no_rawat , pl.noorder FROM permintaan_lab pl JOIN reg_periksa rp JOIN dokter dr JOIN pasien ps ON rp.no_rawat = pl.no_rawat AND rp.no_rkm_medis = ps.no_rkm_medis AND pl.dokter_perujuk = dr.kd_dokter WHERE ";
            if (isset($_POST['tgl_awal'])) {
                $sql .= "pl.tgl_permintaan = '$_POST[tgl_awal]'";
            } else {
                $sql .= "pl.tgl_permintaan = '$date'";
            }
            $sql .= "GROUP BY pl.noorder";
            $query = query($sql);
            while ($row = fetch_array($query)) {
                ?>
            <tr>
                <td><?php echo $row['nm_pasien']; ?></td>
                <td>
                    <div class="btn-group">
                        <button type="button" class="btn btn-secondary waves-effect dropdown-toggle" data-toggle="dropdown" data-disabled="true" aria-expanded="true"><?php echo $row['no_rkm_medis']; ?><span class="caret"></span></button>
                            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                <li><a href="<?php echo URL; ?>/?module=Lab&page=index&action=berkas_digital&no_rawat=<?php echo $row['no_rawat']; ?>">Berkas Digital Perawatan</a></li>
                            </ul>
                    </div>
                </td>
                <td><?php $queri = query("SELECT jpl.nm_perawatan FROM permintaan_pemeriksaan_lab ppl JOIN jns_perawatan_lab jpl ON ppl.kd_jenis_prw = jpl.kd_jenis_prw WHERE ppl.noorder = '{$row['noorder']}'");
                while ($a = fetch_array($queri)) {
                    echo $a['nm_perawatan'].
                    ' || <br>';
                } ?></td>
                <td><?php echo $row['nm_dokter']; ?></td>
                <td><?php if ($row['status'] == 'ralan') {
                    $pk = fetch_array(query("SELECT pk.nm_poli FROM poliklinik pk JOIN reg_periksa rp ON rp.kd_poli = pk.kd_poli WHERE rp.no_rawat = '{$row['no_rawat']}'"));
                    echo $pk['nm_poli'];
                } else {
                    $pk = fetch_array(query("SELECT b.nm_bangsal FROM bangsal b JOIN kamar_inap ki JOIN kamar k ON ki.kd_kamar = k.kd_kamar AND k.kd_bangsal = b.kd_bangsal WHERE ki.no_rawat = '{$row['no_rawat']}'"));
                    echo $pk['nm_bangsal'];
                } ?></td>

            </tr>
        <?php
            } ?>
        </tbody>
    </table>
    <div class="row clearfix">
        <form method="post" action="">
        <div class="col-sm-3">
            <div class="form-group">
                <div class="form-line">
                    <input type="text" name="tgl_awal" class="datepicker form-control" placeholder="Pilih tanggal awal...">
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <div class="form-line">
                    <input type="submit" class="btn bg-blue btn-block btn-lg waves-effect">
                </div>
            </div>
        </div>
        </form>
    </div>
  </div>
<?php
        }
        if ($action == "berkas_digital") {
            if (isset($_POST['ok_berdig'])) {
                $periksa_radiologi = fetch_assoc(query("SELECT tgl_periksa, jam FROM periksa_radiologi WHERE no_rawat = '{$no_rawat}'"));
                $date = $periksa_radiologi['tgl_periksa'];
                $time = $periksa_radiologi['jam'];
                if ($_FILES['file']['name']!=='') {
                    $tmp_name = $_FILES["file"]["tmp_name"];
                    $namefile = $_FILES["file"]["name"];
                    $explode = explode(".", $namefile);
                    $ext = end($explode);
                    if ($_POST['masdig']=='001') {
                        $image_name = "berkasdigital-".time().".".$ext;
                    } else {
                        $image_name = "rujukanfktp-".time().".".$ext;
                    }
                    move_uploaded_file($tmp_name, WEBAPPS."/berkasrawat/pages/upload/".$image_name);
                    $lokasi_berkas = 'pages/upload/'.$image_name;
                    $insert_berkas = query("INSERT INTO berkas_digital_perawatan VALUES('$no_rawat','{$_POST['masdig']}', '$lokasi_berkas')");
                    if ($insert_berkas) {
                        set_message('Berkas digital perawatan telah ditersimpan.');
                        redirect("./?module=Lab&page=index");
                    }
                }
            } ?>
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
    <div id="animated-thumbnails" class="list-unstyled row clearfix">
    <?php
    $sql_rad = query("select * from berkas_digital_perawatan where no_rawat= '{$_GET['no_rawat']}'");
            $no=1;
            while ($row_rad = fetch_array($sql_rad)) {
                echo '<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">';
                echo '<a href="'.URLSIMRS.'/berkasrawat/'.$row_rad[2].'" data-sub-html=""><img class="img-responsive thumbnail"  src="'.URLSIMRS.'/berkasrawat/'.$row_rad[2].'"></a>';
                echo '</div>';
                $no++;
            } ?>
    </div>
  <hr>
    </div>
    <div class="body">
        <form id="form_validation" name="berdigi" action="" method="POST"  enctype="multipart/form-data">
            <label for="email_address">Unggah Berkas Digital Perawatan</label>
                <div class="form-group">
                    <select class="form-control" name="masdig">
                        <?php $berkas = query("SELECT * FROM master_berkas_digital");
            foreach ($berkas as $berkas1):?>
                        <option value="<?php echo $berkas1['kode']; ?>"><?php echo $berkas1['nama']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <img id="image_upload_preview" width="200px" src="<?php echo URL; ?>/modules/RawatJalan/images/upload_berkas.png" onclick="upload_berkas()" style="cursor:pointer;" />
                    <br/>
                    <input name="file" id="inputFile" type="file" style="display:none;"/>
                </div>
            <button type="submit" name="ok_berdig" value="ok_berdig" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_berdig\'">UPLOAD BERKAS</button>
        </form>
    </div>
    </div>
    <?php
        } ?>
</div>
