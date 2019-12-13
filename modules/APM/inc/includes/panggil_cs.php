<audio id="antrian" src="suara/antrian.wav"></audio>
<audio id="notif" src="suara/notification.wav"></audio>
<audio id="b" src="suara/b.wav"  ></audio>
<audio id="counter" src="suara/counter.wav"  ></audio>
<audio id="nol" src="suara/nol.wav"  ></audio>
<audio id="belas" src="suara/belas.wav"  ></audio>
<audio id="sebelas" src="suara/sebelas.wav"  ></audio>
<audio id="puluh" src="suara/puluh.wav"  ></audio>
<audio id="sepuluh" src="suara/sepuluh.wav"  ></audio>
<audio id="ratus" src="suara/ratus.wav"  ></audio>
<audio id="seratus" src="suara/seratus.wav"  ></audio>
<?php foreach($loket_cs as $value){ ?>
<audio id="suarabelloket<?php echo $value; ?>" src="suara/<?php echo $value; ?>.wav"  ></audio>
<?php } ?>
<?php
 $get_antrian = fetch_assoc(query("SELECT antrian FROM antrics"));
 $tcounter = $get_antrian['antrian'];
 $_tcounter = $tcounter + 1;
 if(isset($_GET['loket'])) {
	 query("UPDATE antrian_loket SET end_time = CURRENT_TIME() WHERE type = 'CS' AND noantrian = '{$tcounter}'");
	 query("UPDATE antrics SET loket = '{$_GET['loket']}', antrian = '{$_tcounter}'");
 }
$panjang=strlen($tcounter);
$antrian=$tcounter;
for($i=0;$i<$panjang;$i++){
?>
	<audio id="suarabel<?php echo $i; ?>" src="suara/<?php echo substr($tcounter,$i,1); ?>.wav" ></audio>
<?php
}
?>
<?php
if($_SERVER['REQUEST_METHOD'] == "POST") {
	query("DELETE FROM `antrics`");
	query("INSERT INTO `antrics` (`loket`, `antrian`) VALUES ('1', '1')");
}
?>
<div align="center" style="font-size: 64px;color:white; text-shadow: 2px 2px 4px #000000;" class="mt-2 mb-5">Pemanggil Antrian CS</div>
<div class="container text-center">
  <div class="row">
		<div class="card-deck">
			<?php foreach($loket_cs as $value){ ?>
				<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 mb-5">
				  <div class="card">
			      <div class="card-header" style="font-size:32px;">Loket <?php echo $value; ?></div>
				    <div class="card-body">
							<h5 class="card-title" style="font-size:72px;">B<?php echo $antrian; ?></h5>
				    </div>
				    <div class="card-footer p-0">
							<div class="btn-group btn-group-justified">
							  <a href="#" class="btn btn-primary" style="font-size:32px;"><?php $q = fetch_assoc(query("SELECT MAX(noantrian) as q FROM antrian_loket WHERE type = 'CS' AND postdate LIKE current_date()")); if(empty($q['q'])) { echo '0'; } else { echo $q['q']; } ?></a>
							  <a href="#" class="btn btn-primary" style="font-size:32px;"><i class="fas fa-bullhorn" onclick="mulai<?php echo $value; ?>();"></i></a>
							  <a href="antrian.php?action=panggil_cs&loket=<?php echo $value; ?>" class="btn btn-primary" style="font-size:32px;"><i class="fas fa-forward"></i></a>
							</div>
				    </div>
				  </div>
					</div>
			<?php } ?>
		</div>
	</div>
</div>

<div class="text-center m-5">
<form method="POST" action="">
	<input type="submit" class="btn btn-lg" value="RESET">
</form>
</div>

<?php foreach($loket_cs as $value){ ?>
<script>
function mulai<?php echo $value; ?>(){
	$("#antrian")[0].play()
	totalwaktu=document.getElementById('antrian').duration*1200;
	setTimeout(function() { $("#b")[0].play() }, totalwaktu);
	totalwaktu=totalwaktu+1000;
	<?php if($antrian<10){ ?>
		setTimeout(function() { $("#suarabel0")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
	<?php } else if($antrian ==10){ ?>
		setTimeout(function() { $("#sepuluh")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
	<?php } else if($antrian ==11){ ?>
		setTimeout(function() { $("#sebelas")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
	<?php } else if($antrian < 20){ ?>
		setTimeout(function() { $("#suarabel1")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#belas")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
	<?php } else if($antrian ==20){ ?>
		setTimeout(function() { $("#suarabel0")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#puluh")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
	<?php } else if($antrian <= 99){ ?>
		setTimeout(function() { $("#suarabel0")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#puluh")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#suarabel1")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+900;
	<?php } else if($antrian ==100){ ?>
		setTimeout(function() { $("#seratus")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
	<?php } else if($antrian <= 109){ ?>
		setTimeout(function() { $("#seratus")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#suarabel2")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
	<?php } else if($antrian ==110){ ?>
		setTimeout(function() { $("#seratus")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#sepuluh")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
	<?php } else if($antrian ==111){ ?>
		setTimeout(function() { $("#seratus")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#sebelas")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
	<?php } else if($antrian <= 119){ ?>
		setTimeout(function() { $("#seratus")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#suarabel2")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#belas")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
	<?php } else if($antrian ==120 or $antrian ==130 or $antrian ==140 or $antrian ==150 or $antrian ==160 or $antrian ==170 or $antrian ==180 or $antrian ==190){ ?>
		setTimeout(function() { $("#seratus")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#suarabel1")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#puluh")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
	<?php } else if($antrian <= 199){ ?>
		setTimeout(function() { $("#seratus")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#suarabel1")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#puluh")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#suarabel2")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
  <?php } else if($antrian ==200){ ?>
		setTimeout(function() { $("#suarabel0")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#ratus")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
	<?php } else if($antrian <= 209){ ?>
		setTimeout(function() { $("#suarabel0")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#ratus")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#suarabel2")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000
	<?php } else if($antrian ==210){ ?>
		setTimeout(function() { $("#suarabel0")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#ratus")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#sepuluh")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
  <?php } else if($antrian ==211 ){ ?>
		setTimeout(function() { $("#suarabel0")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#ratus")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#sebelas")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
	<?php } else if($antrian ==220 or $antrian ==230 or $antrian ==240 or $antrian ==250 or $antrian ==260 or $antrian ==270 or $antrian ==280 or $antrian ==290){ ?>
		setTimeout(function() { $("#suarabel0")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#suarabel1")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#puluh")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
	<?php } else if($antrian <= 299){ ?>
		setTimeout(function() { $("#suarabel0")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#ratus")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#suarabel1")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#puluh")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#suarabel2")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
	<?php } else if($antrian ==300){ ?>
		setTimeout(function() { $("#suarabel0")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#ratus")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
	<?php } ?>
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#counter")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#suarabelloket<?php echo $value; ?>")[0].play() }, totalwaktu);
		totalwaktu=totalwaktu+1000;
		setTimeout(function() { $("#notif")[0].play() }, totalwaktu);
}
</script>
<?php } ?>
