<?php
include "send.php";
?>
<?php
if(num_rows(query("SHOW TABLES LIKE 'sms_inbox'")) !== 1) {
	echo '<div style="min-height:70vh; min-width:70vw;">';
	echo '<div class="alert bg-pink alert-dismissible text-center" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);">';
	echo '<p class="lead">Belum terinstall Database SMS Gateway</p>';
	echo '<a href="'.URL.'/?module=SMSGateway&page=index&op=install" class="btn btn-lg btn-primary m-t-20">Install Sekarang</a>';
	echo '</div>';
	echo '</div>';
} else {
?>
<div class="card">
    <div class="header">
        <h2>
            SMS KELUAR
        </h2>
    </div>
    <div class="body">
				<table id="datatable" class="table table-bordered display nowrap js-exportable" width="100%">
						<thead>
								<tr>
										<th>No. Tujuan</th>
										<th>Isi SMS</th>
										<th>Waktu</th>
										<th>Aksi</th>
								</tr>
						</thead>
						<tbody>
						<?php
						$sql = "SELECT * FROM sms";
						if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) {
							$sql .= " WHERE (tgl_sms BETWEEN '$_POST[tgl_awal] 00:00:01' AND '$_POST[tgl_akhir] 23:59:59')";
						}
						$sql .= " ORDER BY tgl_sms";
						$query = query($sql);
						$no = 1;
						while($row = fetch_array($query)) {
						?>
								<tr class="<?php echo $color; ?>">
										<td><?php echo $row['2']; ?></td>
										<td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=view&id=<?php echo $row['0'];?>"><?php echo $row['1']; ?></a></td>
										<td><?php echo $row['6']; ?></td>
										<td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=delete&id=<?php echo $row['0'];?>">Hapus</a></td>
								</tr>
						<?php
						}
						?>
						</tbody>
				</table>
				<div class="row clearfix">
						<form method="post" action="">
						<div class="col-sm-5">
								<div class="form-group">
										<div class="form-line">
												<input type="text" name="tgl_awal" class="datepicker form-control" placeholder="Pilih tanggal awal...">
										</div>
								</div>
						</div>
						<div class="col-sm-5">
								<div class="form-group">
										<div class="form-line">
												<input type="text" name="tgl_akhir" class="datepicker form-control" placeholder="Pilih tanggal akhir...">
										</div>
								</div>
						</div>
						<div class="col-sm-2">
								<div class="form-group">
										<div class="form-line">
												<input type="submit" class="btn bg-blue btn-block btn-lg waves-effect">
										</div>
								</div>
						</div>
						</form>
				</div>
    </div>
</div>
<?php
}
?>
