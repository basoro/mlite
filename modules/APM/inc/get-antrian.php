<?php
include('../../../config.php');

$aksi = $_REQUEST['aksi'];
if ($aksi == "tampilloket") {
  //ketahui jumlah total sehari...
	$sql = query("SELECT * FROM antrian_loket WHERE round(DATE_FORMAT(postdate, '%d')) = '$tanggal' AND round(DATE_FORMAT(postdate, '%m')) = '$bulan' AND round(DATE_FORMAT(postdate, '%Y')) = '$tahun' AND type = 'Loket' ORDER BY round(noantrian) DESC");
	$result = fetch_assoc($sql);
	$noantrian = $result['noantrian'];
	//nomor antrian, total yang ada + 1
	if($noantrian > 0) {
		$next_antrian = $noantrian + 1;
	} else {
		$next_antrian = 1;
	}
	echo '<div id="nomernya" align="center">';
  echo '<h1 class="display-1">';
  echo 'A'.$next_antrian;
  echo '</h1>';
  echo '['.$tanggal.'-'.$bulan.'-'.$tahun.']';
  echo '</div>';
  echo '<br>';

	exit();
}

if ($aksi == "printloket") {
  //ketahui jumlah total sehari...
	$sql = query("SELECT * FROM antrian_loket WHERE round(DATE_FORMAT(postdate, '%d')) = '$tanggal' AND round(DATE_FORMAT(postdate, '%m')) = '$bulan' AND round(DATE_FORMAT(postdate, '%Y')) = '$tahun' AND type = 'Loket' ORDER BY round(noantrian) DESC");
	$result = fetch_assoc($sql);
	$noantrian = $result['noantrian'];
	//nomor antrian, total yang ada + 1
	if($noantrian > 0) {
		$next_antrian = $noantrian + 1;
	} else {
		$next_antrian = 1;
	}
	echo '<div id="nomernya" align="center">';
  echo '<h1 class="display-1">';
  echo 'A'.$next_antrian;
  echo '</h1>';
  echo '['.$tanggal.'-'.$bulan.'-'.$tahun.']';
  echo '</div>';
  echo '<br>';

	?>

	<script>
	$(document).ready(function(){
		$("#btnKRM").on('click', function(){
			$("#formloket").submit(function(){
				$.ajax({
					url: "get-antrian.php?aksi=simpanloket&noantrian=<?php echo $next_antrian;?>",
					type:"POST",
					data:$(this).serialize(),
					success:function(data){
						setTimeout('$("#loading").hide()',1000);
						window.location.href = "index.php";
						}
					});
				return false;
			});
		});
	})
	</script>
	<?php
	exit();
}

//jika simpan
if ($aksi == "simpanloket") {
	//ambil nilai
	$noantrian = $_GET['noantrian'];
	//cek
	//$sql = query("SELECT * FROM antrian_loket WHERE round(DATE_FORMAT(postdate, '%d')) = '$tanggal' AND round(DATE_FORMAT(postdate, '%m')) = '$bulan' AND round(DATE_FORMAT(postdate, '%Y')) = '$tahun' AND noantrian = '$noantrian' AND type = 'Loket'");
	//if (empty(num_rows($sql))) {
		query("INSERT INTO antrian_loket(kd, type, noantrian, postdate, start_time, end_time) VALUES (NULL, 'Loket', '$noantrian', '$date_time', CURRENT_TIME(), '00:00:00')");
	//}
	?>
	<?php
	exit();
}

if ($aksi == "tampilcs") {
	  //ketahui jumlah total sehari...
		$sql = query("SELECT * FROM antrian_loket WHERE round(DATE_FORMAT(postdate, '%d')) = '$tanggal' AND round(DATE_FORMAT(postdate, '%m')) = '$bulan' AND round(DATE_FORMAT(postdate, '%Y')) = '$tahun' AND type = 'CS' ORDER BY round(noantrian) DESC");
		$result = fetch_assoc($sql);
		$noantrian = $result['noantrian'];
		//nomor antrian, total yang ada + 1
		if($noantrian > 0) {
			$next_antrian = $noantrian + 1;
		} else {
			$next_antrian = 1;
		}
		echo '<div id="nomernya" align="center">';
	  echo '<h1 class="display-1">';
	  echo 'B'.$next_antrian;
	  echo '</h1>';
	  echo '['.$tanggal.'-'.$bulan.'-'.$tahun.']';
	  echo '</div>';
	  echo '<br>';

		exit();
}

if ($aksi == "printcs") {
	  //ketahui jumlah total sehari...
		$sql = query("SELECT * FROM antrian_loket WHERE round(DATE_FORMAT(postdate, '%d')) = '$tanggal' AND round(DATE_FORMAT(postdate, '%m')) = '$bulan' AND round(DATE_FORMAT(postdate, '%Y')) = '$tahun' AND type = 'CS' ORDER BY round(noantrian) DESC");
		$result = fetch_assoc($sql);
		$noantrian = $result['noantrian'];
		//nomor antrian, total yang ada + 1
		if($noantrian > 0) {
			$next_antrian = $noantrian + 1;
		} else {
			$next_antrian = 1;
		}
		echo '<div id="nomernya" align="center">';
	  echo '<h1 class="display-1">';
	  echo 'B'.$next_antrian;
	  echo '</h1>';
	  echo '['.$tanggal.'-'.$bulan.'-'.$tahun.']';
	  echo '</div>';
	  echo '<br>';

		?>

		<script>
		$(document).ready(function(){
			$("#btnKRMCS").on('click', function(){
				$("#formcs").submit(function(){
					$.ajax({
						url: "get-antrian.php?aksi=simpancs&noantrian=<?php echo $next_antrian;?>",
						type:"POST",
						data:$(this).serialize(),
						success:function(data){
							setTimeout('$("#loading").hide()',1000);
							window.location.href = "index.php";
							}
						});
					return false;
				});
			});
		})
		</script>
		<?php
		exit();
}

//jika simpan
if ($aksi == "simpancs") {
	//ambil nilai
	$noantrian = $_GET['noantrian'];
	//cek
	$sql = query("SELECT * FROM antrian_loket WHERE round(DATE_FORMAT(postdate, '%d')) = '$tanggal' AND round(DATE_FORMAT(postdate, '%m')) = '$bulan' AND round(DATE_FORMAT(postdate, '%Y')) = '$tahun' AND noantrian = '$noantrian' AND type = 'CS'");
	if (empty(num_rows($sql))) {
		query("INSERT INTO antrian_loket(kd, type, noantrian, postdate, start_time, end_time) VALUES (NULL, 'CS', '$noantrian', '$date_time', CURRENT_TIME(), '00:00:00')");
	}
	?>
	<?php
	exit();
}

if ($aksi == "tampilprioritas") {
	  //ketahui jumlah total sehari...
		$sql = query("SELECT * FROM antrian_loket WHERE round(DATE_FORMAT(postdate, '%d')) = '$tanggal' AND round(DATE_FORMAT(postdate, '%m')) = '$bulan' AND round(DATE_FORMAT(postdate, '%Y')) = '$tahun' AND type = 'Prioritas' ORDER BY round(noantrian) DESC");
		$result = fetch_assoc($sql);
		$noantrian = $result['noantrian'];
		//nomor antrian, total yang ada + 1
		if($noantrian > 0) {
			$next_antrian = $noantrian + 1;
		} else {
			$next_antrian = 1;
		}
		echo '<div id="nomernya" align="center">';
	  echo '<h1 class="display-1">';
	  echo 'C'.$next_antrian;
	  echo '</h1>';
	  echo '['.$tanggal.'-'.$bulan.'-'.$tahun.']';
	  echo '</div>';
	  echo '<br>';

		exit();
}

if ($aksi == "printprioritas") {
	  //ketahui jumlah total sehari...
		$sql = query("SELECT * FROM antrian_loket WHERE round(DATE_FORMAT(postdate, '%d')) = '$tanggal' AND round(DATE_FORMAT(postdate, '%m')) = '$bulan' AND round(DATE_FORMAT(postdate, '%Y')) = '$tahun' AND type = 'Prioritas' ORDER BY round(noantrian) DESC");
		$result = fetch_assoc($sql);
		$noantrian = $result['noantrian'];
		//nomor antrian, total yang ada + 1
		if($noantrian > 0) {
			$next_antrian = $noantrian + 1;
		} else {
			$next_antrian = 1;
		}
		echo '<div id="nomernya" align="center">';
	  echo '<h1 class="display-1">';
	  echo 'C'.$next_antrian;
	  echo '</h1>';
	  echo '['.$tanggal.'-'.$bulan.'-'.$tahun.']';
	  echo '</div>';
	  echo '<br>';

		?>

		<script>
		$(document).ready(function(){
			$("#btnKRMPrioritas").on('click', function(){
				$("#formprioritas").submit(function(){
					$.ajax({
						url: "get-antrian.php?aksi=simpanprioritas&noantrian=<?php echo $next_antrian;?>",
						type:"POST",
						data:$(this).serialize(),
						success:function(data){
							setTimeout('$("#loading").hide()',1000);
							window.location.href = "index.php";
							}
						});
					return false;
				});
			});
		})
		</script>
		<?php
		exit();
}

//jika simpan
if ($aksi == "simpanprioritas") {
	//ambil nilai
	$noantrian = $_GET['noantrian'];
	//cek
	$sql = query("SELECT * FROM antrian_loket WHERE round(DATE_FORMAT(postdate, '%d')) = '$tanggal' AND round(DATE_FORMAT(postdate, '%m')) = '$bulan' AND round(DATE_FORMAT(postdate, '%Y')) = '$tahun' AND noantrian = '$noantrian' AND type = 'Prioritas'");
	if (empty(num_rows($sql))) {
		query("INSERT INTO antrian_loket(kd, type, noantrian, postdate, start_time, end_time) VALUES (NULL, 'Prioritas', '$noantrian', '$date_time', CURRENT_TIME(), '00:00:00')");
	}
	?>
	<?php
	exit();
}

?>
