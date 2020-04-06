<?php $action = isset($_GET['action'])?$_GET['action']:null; ?>
<div class="card">
  <div class="header">
    <div class="row">
    <h2 class="col-md-10 col-sm-10 col-xs-10">Data Pegawai</h2>
    <?php if ($_GET['action'] == "upload"): ?>
        <a href="<?php echo URL; ?>?module=Umpeg&page=index&action=upload" class="btn btn-error" onclick="return false;">Import Data</a>
    <?php else: ?>
        <a href="<?php echo URL; ?>?module=Umpeg&page=index&action=upload" class="btn btn-primary">Import Data</a>
    <?php endif; ?>
    </div>
  </div>
<div class="body">
  <?php if (!$action) {
    if (isset($_GET['berhasil'])) {
        echo "<p>".$_GET['berhasil']." Data Berhasil Di Import";
    } ?>
<table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">
<thead>
    <tr>
        <th>Nomor Induk</th>
        <th>Nama</th>
        <th>Jenis Kelamin</th>
        <th>Jabatan</th>
        <th>Bidang</th>
    </tr>
</thead>
<tbody>
<?php
    $sql = "SELECT * from pegawai";
    $query = query($sql);
    while ($row = fetch_array($query)) {
        ?>
    <tr>
        <td><?php echo $row['nik']; ?></td>
        <td><?php echo $row['nama']; ?></td>
        <td><?php echo $row['jk']; ?></td>
        <td><?php echo $row['jbtn']; ?></td>
        <td><?php echo $row['bidang']; ?></td>
    </tr>
<?php
    } ?>
</tbody>
</table>
<?php
} ?>
<?php if ($action == "upload") {
        if (isset($_POST['upload'])) {
            include 'spreadsheet-reader-master/php-excel-reader/excel_reader2.php';
            include 'spreadsheet-reader-master/SpreadsheetReader.php';

            $target = basename($_FILES['filepegawai']['name']);
            move_uploaded_files($_FILES['filepegawai']['tmp_name'], $target);

            chmod($_FILES['filepegawai']['name'], 0777);

            $data = new SpreadsheetReader($target);

            $berhasil = 0;
            foreach ($data as $Key => $value) {
                // import data excel mulai baris ke-2 (karena ada header pada baris 1)
                if ($Key < 1) {
                    continue;
                }
                $nik = $value['0'];
                $nama = $value['1'];
                $jk = $value['2'];
                $jbtn = $value['3'];
                $jnj = $value['4'];
                $dep = $value['5'];
                $bid = $value['6'];
                $stwp = $value['7'];
                $stkrj = $value['8'];
                $npwp = $value['9'];
                $pendik = $value['10'];
                $gapok = $value['11'];
                $tmplhr = $value['12'];
                $tgllhr = $value['13'];
                $alamat = $value['14'];
                $kota = $value['15'];
                $mulaikrj = $value['16'];
                $mskrj = $value['17'];
                $indexins = $value['18'];
                $bpd = $value['19'];
                $rek = $value['20'];
                $sttsakt = $value['21'];
                $wjbmsk = $value['22'];
                $kurang = $value['23'];
                $index = $value['24'];
                $mulaikntrk = $value['25'];
                $cuti = $value['26'];
                $dankes = $value['27'];
                $poto = $value['28'];
                $noktp = $value['29'];
                // }
                // for ($i=2; $i <= $jumlah_baris ; $i++) {
                //     $nik = $data->val($i, 1);
                //     $nama = $data->val($i, 2);
                //     $jk = $data->val($i, 3);
                //     $jbtn = $data->val($i, 4);
                //     $jnj = $data->val($i, 5);
                //     $dep = $data->val($i, 6);
                //     $bid = $data->val($i, 7);
                //     $stwp = $data->val($i, 8);
                //     $stkrj = $data->val($i, 9);
                //     $npwp = $data->val($i, 10);
                //     $pendik = $data->val($i, 11);
                //     $gapok = $data->val($i, 12);
                //     $tmplhr = $data->val($i, 13);
                //     $tgllhr = $data->val($i, 14);
                //     $alamat = $data->val($i, 15);
                //     $kota = $data->val($i, 16);
                //     $mulaikrj = $data->val($i, 17);
                //     $mskrj = $data->val($i, 18);
                //     $indexins = $data->val($i, 19);
                //     $bpd = $data->val($i, 20);
                //     $rek = $data->val($i, 21);
                //     $sttsakt = $data->val($i, 22);
                //     $wjbmsk = $data->val($i, 23);
                //     $kurang = $data->val($i, 24);
                //     $index = $data->val($i, 25);
                //     $mulaikntrk = $data->val($i, 26);
                //     $cuti = $data->val($i, 27);
                //     $dankes = $data->val($i, 28);
                //     $poto = $data->val($i, 29);
                //     $noktp = $data->val($i, 30);
                $query = query("INSERT INTO pegawai VALUES ('', '$nik', '$nama', '$jk', '$jbtn', '$jnj', '$dep', '$bid', '$stwp', '$stkrj', '$npwp', '$pendik', '$gapok', '$tmplhr', '$tgllhr', '$alamat', '$kota', '$mulaikrj', '$mskrj', '$indexins', '$bpd', '$rek', '$sttsakt', '$wjbmsk', '$kurang', '$index', '$mulaikntrk', '$cuti', '$dankes', '$poto', '$noktp') ");
            }
            if ($query) {
                echo "Import data berhasil";
            } else {
                echo mysql_error();
            }
        }

        unlink($_FILES['filepegawai']['name']);

        header("location:".URL.";?module=Umpeg&page=index&action=upload&berhasil=$berhasil"); ?>
  <div class="row">
    <div class="clearfix">
      <div class="form-group">
        <form method="post" enctype="multipart/form-data" action="">
          <div class="form-group">
              <p class="card-title">Pilih File :</p>
          </div>
          <div class="form-group">
              <input type="file" name="filepegawai" value="">
          </div>
          <a href="<?php echo URL; ?>?module=Umpeg&page=index" class="btn bg-red">Kembali</a>
          <button type="submit" name="upload" value="upload" class="btn waves-effect bg-green" onclick="this.value=\'upload\'">Import</button>
        </form>
      </div>
    </div>
  </div>
  <?php
    } ?>
</div>
</div>
