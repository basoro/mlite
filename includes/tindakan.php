<?php
if($action == "view"){
  if (isset($_POST['ok_tdk'])) {
                if (($_POST['kd_tdk'] <> "") and ($no_rawat <> "")) {
                      $insert = query("INSERT INTO rawat_jl_dr VALUES ('{$no_rawat}','{$_POST['kd_tdk']}','{$_SESSION['username']}','$date','$time','0','0','{$_POST['kdtdk']}','0','0','{$_POST['kdtdk']}','Belum')");
                      if ($insert) {
                          redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}");
                      };
                };
          };
}
?>

<div class="body">
<form method="POST">
  <label for="email_address">Nama Tindakan</label>
  <div class="form-group">
     <select name="kd_tdk" class="form-control kd_tdk" id="kd_tdk" style="width:100%"></select>
     <br/>
     <input type="hidden" class="form-control" id="kdtdk" name="kdtdk"/>
  </div>
  <button type="submit" name="ok_tdk" value="ok_tdk" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_tdk\'">SIMPAN</button>
</form>
</div>
<div class="body">
<table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
    <thead>
        <tr>
            <th>Nama Tindakan</th>
            <th>Tanggal Tindakan</th>
            <th>Biaya</th>
            <th>Tools</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $query_tindakan = query("SELECT a.kd_jenis_prw, a.tgl_perawatan, a.tarif_tindakandr, b.nm_perawatan  FROM rawat_jl_dr a, jns_perawatan b WHERE a.kd_jenis_prw = b.kd_jenis_prw AND a.no_rawat = '{$no_rawat}'");
    while ($data_tindakan = fetch_array($query_tindakan)) {
    ?>
        <tr>
            <td><?php echo SUBSTR($data_tindakan['3'], 0, 20).' ...'; ?></td>
            <td><?php echo $data_tindakan['1']; ?></td>
            <td><?php echo $data_tindakan['2']; ?></td>
            <td><a href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_tindakan&kd_jenis_prw=<?php echo $data_tindakan['0']; ?>&no_rawat=<?php echo $no_rawat; ?>">Hapus</a></td>
        </tr>
    <?php
    }
    ?>
    </tbody>
</table>
</div>
