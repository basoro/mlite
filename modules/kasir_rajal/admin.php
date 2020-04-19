<?php
if(!defined("INDEX")) header('location: ../index.php');

$link = "?module=kasir_rajal";
$show = isset($_GET['show']) ? $_GET['show'] : "";
switch($show){
	default:
?>
<div class="block-header">
		<h2>
				KASIR RAWAT JALAN
				<small>Periode <?php if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) { echo tgl_indonesia($_POST['tgl_awal'])." s/d ".tgl_indonesia($_POST['tgl_akhir']); } else { echo tgl_indonesia($date) . ' s/d ' . tgl_indonesia($date);} ?></small>
		</h2>
</div>

<div class="row clearfix">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="card">
						<div class="header">
								<h2>
										Tagihan Pembayaran
								</h2>
						</div>
						<div class="body">
						</div>
				</div>
		</div>
</div>

<?php
	break;
}
?>
