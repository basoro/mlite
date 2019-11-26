<?php
	//Initialisasi nilai untuk nomor loket
	$loket="1";
	$loket2="2";
	$loket3="3";
	$loket4="4";
	$loket5="5";

?>
<script type="text/javascript" >
$(document).ready(function(){
	$("#play").click(function(){
		document.getElementById('suarabel').play();
	});


});
</script>
</head>
<body >
		<audio id="suarabel" src="suara/Airport_Bell.wav"></audio>
		<audio id="suarabeel" src="suara/Airport_Bell3.wav"></audio>
		<audio id="pasienlama" src="suara/Pasienlama.wav"></audio>
		<audio id="suarabelnomorurut" src="suara/no_urut.wav"  ></audio>
		<audio id="p" src="suara/l.wav"  ></audio>
		<audio id="suarabelsuarabelloket" src="suara/Diloket.wav"  ></audio>
		<audio id="nol" src="suara/nol.wav"  ></audio>
		<audio id="belas" src="suara/belas.wav"  ></audio>
		<audio id="sebelas" src="suara/sebelas.wav"  ></audio>
    <audio id="puluh" src="suara/puluh.wav"  ></audio>
    <audio id="sepuluh" src="suara/sepuluh.wav"  ></audio>
    <audio id="ratus" src="suara/ratus.wav"  ></audio>
    <audio id="seratus" src="suara/seratus.wav"  ></audio>
    <audio id="suarabelloket1" src="suara/<?php echo $loket; ?>.wav"  ></audio>
    <audio id="suarabelloket2" src="suara/<?php echo $loket2; ?>.wav"  ></audio>
    <audio id="suarabelloket3" src="suara/<?php echo $loket3; ?>.wav"  ></audio>

		<?php

			 $get_antrian = fetch_assoc(query("SELECT antrian FROM antriloket"));
			 $tcounter = $get_antrian['antrian'];
			 $_tcounter = $tcounter + 1;
			 if(isset($_GET['loket'])) {
				 query("UPDATE antrian_loket SET end_time = CURRENT_TIME() WHERE type = 'Loket' AND noantrian = '{$tcounter}'");
				 query("UPDATE antriloket SET loket = '{$_GET['loket']}', antrian = '{$_tcounter}'");
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
			query("DELETE FROM `antriloket`");
			query("INSERT INTO `antriloket` (`loket`, `antrian`) VALUES ('1', '1')");
		}
		 ?>
		<div align="center" style="font-size: 64px;color:white; text-shadow: 2px 2px 4px #000000;margin: 40px;">Sistem Antrian Loket <form method="POST" action=""><input type="submit" class="btn btn-lg" value="RESET"></form></div>

										<div class="container text-center">
									    <div class="row justify-content-center">
												<div class="card-deck">
												  <div class="card" style="width: 20rem;">
											      <div class="card-header" style="font-size:41px;">Loket 1</div>
												    <div class="card-body">
															<h5 class="card-title" style="font-size:100px;">L<?php echo $antrian; ?></h5>
												    </div>
												    <div class="card-footer p-0">
															<div class="btn-group btn-group-justified">
															  <a href="#" class="btn btn-primary" style="font-size:41px;"><?php $q = fetch_assoc(query("SELECT MAX(noantrian) as q FROM antrian_loket WHERE type = 'Loket' AND postdate LIKE current_date()")); if(empty($q['q'])) { echo '0'; } else { echo $q['q']; } ?></a>
															  <a href="#" class="btn btn-primary" style="font-size:41px;" style="font-size:41px;"><i class="fas fa-bullhorn" onclick="mulai();"></i></a>
															  <a href="antrian.php?action=lama&loket=1" class="btn btn-primary" style="font-size:41px;" style="font-size:41px;" style="font-size:41px;"><i class="fas fa-forward"></i></a>
															</div>
												    </div>
												  </div>
												  <div class="card" style="width: 20rem;">
														<div class="card-header" style="font-size:41px;">Loket 2</div>
												    <div class="card-body">
															<h5 class="card-title" style="font-size:100px;">L<?php echo $antrian; ?></h5>
												    </div>
												    <div class="card-footer p-0">
															<div class="btn-group btn-group-justified">
															  <a href="#" class="btn btn-primary" style="font-size:41px;"><?php $q = fetch_assoc(query("SELECT MAX(noantrian) as q FROM antrian_loket WHERE type = 'Loket' AND postdate LIKE current_date()")); if(empty($q['q'])) { echo '0'; } else { echo $q['q']; } ?></a>
															  <a href="#" class="btn btn-primary" style="font-size:41px;"><i class="fas fa-bullhorn" onclick="mulai2();"></i></a>
															  <a href="antrian.php?action=lama&loket=2" class="btn btn-primary" style="font-size:41px;"><i class="fas fa-forward"></i></a>
															</div>
												    </div>
												  </div>
												  <div class="card" style="width: 20rem;">
														<div class="card-header" style="font-size:41px;">Loket 3</div>
												    <div class="card-body">
												      <h5 class="card-title" style="font-size:100px;">L<?php echo $antrian; ?></h5>
												    </div>
												    <div class="card-footer p-0">
															<div class="btn-group btn-group-justified">
															  <a href="#" class="btn btn-primary" style="font-size:41px;"><?php $q = fetch_assoc(query("SELECT MAX(noantrian) as q FROM antrian_loket WHERE type = 'Loket' AND postdate LIKE current_date()")); if(empty($q['q'])) { echo '0'; } else { echo $q['q']; } ?></a>
															  <a href="#" class="btn btn-primary" style="font-size:41px;"><i class="fas fa-bullhorn" onclick="mulai3();"></i></a>
															  <a href="antrian.php?action=lama&loket=3" class="btn btn-primary" style="font-size:41px;"><i class="fas fa-forward"></i></a>
															</div>
												    </div>
												  </div>
												</div>
											</div>
										</div>


										<script type="text/javascript">
										function mulai(){
											//MAINKAN SUARA BEL PADA SAAT AWAL
											document.getElementById('suarabel').pause();
											document.getElementById('suarabel').currentTime=0;
											document.getElementById('suarabel').play();

											//SET DELAY UNTUK MEMAINKAN REKAMAN NOMOR URUT
											totalwaktu=document.getElementById('suarabel').duration*1200;
											//MAINKAN SUARA Pasienlam
											setTimeout(function() {
													document.getElementById('pasienlama').pause();
													document.getElementById('pasienlama').currentTime=0;
													document.getElementById('pasienlama').play();
											}, totalwaktu);
											totalwaktu=totalwaktu+1500;



											//MAINKAN SUARA NOMOR URUT
											setTimeout(function() {
													document.getElementById('suarabelnomorurut').pause();
													document.getElementById('suarabelnomorurut').currentTime=0;
													document.getElementById('suarabelnomorurut').play();
											}, totalwaktu);
											totalwaktu=totalwaktu+1000;

											//MAINKAN SUARA P
											setTimeout(function() {
													document.getElementById('p').pause();
													document.getElementById('p').currentTime=0;
													document.getElementById('p').play();
											}, totalwaktu);
											totalwaktu=totalwaktu+1000;

											<?php
												//JIKA KURANG DARI 10 MAKA MAIKAN SUARA ANGKA1
												if($antrian<10){
											?>

													setTimeout(function() {
															document.getElementById('suarabel0').pause();
															document.getElementById('suarabel0').currentTime=0;
															document.getElementById('suarabel0').play();
														}, totalwaktu);

													totalwaktu=totalwaktu+1000;
											<?php
												}elseif($antrian ==10){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
											?>
														setTimeout(function() {
																document.getElementById('sepuluh').pause();
																document.getElementById('sepuluh').currentTime=0;
																document.getElementById('sepuluh').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
												<?php
													}elseif($antrian ==11){
														//JIKA 11 MAKA MAIKAN SUARA SEBELAS
												?>
														setTimeout(function() {
																document.getElementById('sebelas').pause();
																document.getElementById('sebelas').currentTime=0;
																document.getElementById('sebelas').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
												<?php
													}elseif($antrian < 20){
														//JIKA 12-20 MAKA MAIKAN SUARA ANGKA2+"BELAS"
												?>
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('belas').pause();
																document.getElementById('belas').currentTime=0;
																document.getElementById('belas').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
												<?php
													}elseif($antrian <= 99){
														//JIKA PULUHAN MAKA MAINKAN SUARA ANGKA1+PULUH+AKNGKA2
												?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('puluh').pause();
																document.getElementById('puluh').currentTime=0;
																document.getElementById('puluh').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+900;

														<?php
													}elseif($antrian ==100){
														//JIKA 100 MAKA MAIKAN SUARA RATUS
												?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
												<?php
													}elseif($antrian <= 109){
														//JIKA 100 MAKA MAIKAN SUARA RATUS
												?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
															setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel2').pause();
																document.getElementById('suarabel2').currentTime=0;
																document.getElementById('suarabel2').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;


												<?php
												}elseif($antrian ==110){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
											?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
														}elseif($antrian ==111){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel2').pause();
																document.getElementById('suarabel2').currentTime=0;
																document.getElementById('suarabel2').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;


														<?php
														}elseif($antrian > 111){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel2').pause();
																document.getElementById('suarabel2').currentTime=0;
																document.getElementById('suarabel2').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
														}elseif($antrian ==120 or $antrian ==130 or $antrian ==140 or $antrian ==150 or $antrian ==160 or $antrian ==170 or $antrian ==180 or $antrian ==190){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
														}elseif($antrian ==200 or $antrian ==300){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
													}elseif($antrian < 209){
														//JIKA 100 MAKA MAIKAN SUARA RATUS
												?>
															setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel2').pause();
																document.getElementById('suarabel2').currentTime=0;
																document.getElementById('suarabel2').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														<?php
												}elseif($antrian ==210){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
											?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
															setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
														}elseif($antrian ==211 ){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
															setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel2').pause();
																document.getElementById('suarabel2').currentTime=0;
																document.getElementById('suarabel2').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
														}elseif($antrian ==220 or $antrian ==230 or $antrian ==240 or $antrian ==250 or $antrian ==260 or $antrian ==270 or $antrian ==280 or $antrian ==290){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
												<?php
													}else{
														//JIKA LEBIH DARI 100
														//Karena aplikasi ini masih sederhana maka logina konversi hanya sampai 100
														//Selebihnya akan langsung disebutkan angkanya saja
														//tanpa kata "RATUS", "PULUH", maupun "BELAS"
												?>

												<?php
													for($i=0;$i<$panjang;$i++){
												?>

												totalwaktu=totalwaktu+1000;
												setTimeout(function() {
																document.getElementById('suarabel<?php echo $i; ?>').pause();
																document.getElementById('suarabel<?php echo $i; ?>').currentTime=0;
																document.getElementById('suarabel<?php echo $i; ?>').play();
															}, totalwaktu);
												<?php
													}
													}
												?>


												totalwaktu=totalwaktu+1000;
												setTimeout(function() {
																document.getElementById('suarabelsuarabelloket').pause();
																document.getElementById('suarabelsuarabelloket').currentTime=0;
																document.getElementById('suarabelsuarabelloket').play();
															}, totalwaktu);

												totalwaktu=totalwaktu+1000;
												setTimeout(function() {
																document.getElementById('suarabelloket<?php echo $loket; ?>').pause();
																document.getElementById('suarabelloket<?php echo $loket; ?>').currentTime=0;
																document.getElementById('suarabelloket<?php echo $loket; ?>').play();
															}, totalwaktu);

															totalwaktu=totalwaktu+1000;
												setTimeout(function() {
																document.getElementById('suarabeel').pause();
																document.getElementById('suarabeel').currentTime=0;
																document.getElementById('suarabeel').play();
															}, totalwaktu);
										}

										function mulai2(){
											//MAINKAN SUARA BEL PADA SAAT AWAL
											document.getElementById('suarabel').pause();
											document.getElementById('suarabel').currentTime=0;
											document.getElementById('suarabel').play();

											//SET DELAY UNTUK MEMAINKAN REKAMAN NOMOR URUT
											totalwaktu=document.getElementById('suarabel').duration*1200;
											//MAINKAN SUARA Pasienlam
											setTimeout(function() {
													document.getElementById('pasienlama').pause();
													document.getElementById('pasienlama').currentTime=0;
													document.getElementById('pasienlama').play();
											}, totalwaktu);
											totalwaktu=totalwaktu+1500;



											//MAINKAN SUARA NOMOR URUT
											setTimeout(function() {
													document.getElementById('suarabelnomorurut').pause();
													document.getElementById('suarabelnomorurut').currentTime=0;
													document.getElementById('suarabelnomorurut').play();
											}, totalwaktu);
											totalwaktu=totalwaktu+1000;

											//MAINKAN SUARA P
											setTimeout(function() {
													document.getElementById('p').pause();
													document.getElementById('p').currentTime=0;
													document.getElementById('p').play();
											}, totalwaktu);
											totalwaktu=totalwaktu+1000;

											<?php
												//JIKA KURANG DARI 10 MAKA MAIKAN SUARA ANGKA1
												if($antrian<10){
											?>

													setTimeout(function() {
															document.getElementById('suarabel0').pause();
															document.getElementById('suarabel0').currentTime=0;
															document.getElementById('suarabel0').play();
														}, totalwaktu);

													totalwaktu=totalwaktu+1000;
											<?php
												}elseif($antrian ==10){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
											?>
														setTimeout(function() {
																document.getElementById('sepuluh').pause();
																document.getElementById('sepuluh').currentTime=0;
																document.getElementById('sepuluh').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
												<?php
													}elseif($antrian ==11){
														//JIKA 11 MAKA MAIKAN SUARA SEBELAS
												?>
														setTimeout(function() {
																document.getElementById('sebelas').pause();
																document.getElementById('sebelas').currentTime=0;
																document.getElementById('sebelas').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
												<?php
													}elseif($antrian < 20){
														//JIKA 12-20 MAKA MAIKAN SUARA ANGKA2+"BELAS"
												?>
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('belas').pause();
																document.getElementById('belas').currentTime=0;
																document.getElementById('belas').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
												<?php
													}elseif($antrian <= 99){
														//JIKA PULUHAN MAKA MAINKAN SUARA ANGKA1+PULUH+AKNGKA2
												?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('puluh').pause();
																document.getElementById('puluh').currentTime=0;
																document.getElementById('puluh').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+900;

														<?php
													}elseif($antrian ==100){
														//JIKA 100 MAKA MAIKAN SUARA RATUS
												?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
												<?php
													}elseif($antrian <= 109){
														//JIKA 100 MAKA MAIKAN SUARA RATUS
												?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
															setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel2').pause();
																document.getElementById('suarabel2').currentTime=0;
																document.getElementById('suarabel2').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;


												<?php
												}elseif($antrian ==110){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
											?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
														}elseif($antrian ==111){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel2').pause();
																document.getElementById('suarabel2').currentTime=0;
																document.getElementById('suarabel2').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;


														<?php
														}elseif($antrian > 111){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel2').pause();
																document.getElementById('suarabel2').currentTime=0;
																document.getElementById('suarabel2').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
														}elseif($antrian ==120 or $antrian ==130 or $antrian ==140 or $antrian ==150 or $antrian ==160 or $antrian ==170 or $antrian ==180 or $antrian ==190){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
														}elseif($antrian ==200 or $antrian ==300){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
													}elseif($antrian < 209){
														//JIKA 100 MAKA MAIKAN SUARA RATUS
												?>
															setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel2').pause();
																document.getElementById('suarabel2').currentTime=0;
																document.getElementById('suarabel2').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														<?php
												}elseif($antrian ==210){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
											?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
															setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
														}elseif($antrian ==211 ){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
															setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel2').pause();
																document.getElementById('suarabel2').currentTime=0;
																document.getElementById('suarabel2').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
														}elseif($antrian ==220 or $antrian ==230 or $antrian ==240 or $antrian ==250 or $antrian ==260 or $antrian ==270 or $antrian ==280 or $antrian ==290){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
												<?php
													}else{
														//JIKA LEBIH DARI 100
														//Karena aplikasi ini masih sederhana maka logina konversi hanya sampai 100
														//Selebihnya akan langsung disebutkan angkanya saja
														//tanpa kata "RATUS", "PULUH", maupun "BELAS"
												?>
												<?php
													for($i=0;$i<$panjang;$i++){
												?>

												totalwaktu=totalwaktu+1000;
												setTimeout(function() {
																document.getElementById('suarabel<?php echo $i; ?>').pause();
																document.getElementById('suarabel<?php echo $i; ?>').currentTime=0;
																document.getElementById('suarabel<?php echo $i; ?>').play();
															}, totalwaktu);
												<?php
													}
													}
												?>


												totalwaktu=totalwaktu+1000;
												setTimeout(function() {
																document.getElementById('suarabelsuarabelloket').pause();
																document.getElementById('suarabelsuarabelloket').currentTime=0;
																document.getElementById('suarabelsuarabelloket').play();
															}, totalwaktu);

												totalwaktu=totalwaktu+1000;
												setTimeout(function() {
																document.getElementById('suarabelloket<?php echo $loket2; ?>').pause();
																document.getElementById('suarabelloket<?php echo $loket2; ?>').currentTime=0;
																document.getElementById('suarabelloket<?php echo $loket2; ?>').play();
															}, totalwaktu);
										totalwaktu=totalwaktu+1000;
												setTimeout(function() {
																document.getElementById('suarabeel').pause();
																document.getElementById('suarabeel').currentTime=0;
																document.getElementById('suarabeel').play();
															}, totalwaktu);
										}

										function mulai3(){
											//MAINKAN SUARA BEL PADA SAAT AWAL
											document.getElementById('suarabel').pause();
											document.getElementById('suarabel').currentTime=0;
											document.getElementById('suarabel').play();

											//SET DELAY UNTUK MEMAINKAN REKAMAN NOMOR URUT
											totalwaktu=document.getElementById('suarabel').duration*1200;
											//MAINKAN SUARA Pasienlam
											setTimeout(function() {
													document.getElementById('pasienlama').pause();
													document.getElementById('pasienlama').currentTime=0;
													document.getElementById('pasienlama').play();
											}, totalwaktu);
											totalwaktu=totalwaktu+1500;



											//MAINKAN SUARA NOMOR URUT
											setTimeout(function() {
													document.getElementById('suarabelnomorurut').pause();
													document.getElementById('suarabelnomorurut').currentTime=0;
													document.getElementById('suarabelnomorurut').play();
											}, totalwaktu);
											totalwaktu=totalwaktu+1000;

											//MAINKAN SUARA P
											setTimeout(function() {
													document.getElementById('p').pause();
													document.getElementById('p').currentTime=0;
													document.getElementById('p').play();
											}, totalwaktu);
											totalwaktu=totalwaktu+1000;

											<?php
												//JIKA KURANG DARI 10 MAKA MAIKAN SUARA ANGKA1
												if($antrian<10){
											?>

													setTimeout(function() {
															document.getElementById('suarabel0').pause();
															document.getElementById('suarabel0').currentTime=0;
															document.getElementById('suarabel0').play();
														}, totalwaktu);

													totalwaktu=totalwaktu+1000;
											<?php
												}elseif($antrian ==10){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
											?>
														setTimeout(function() {
																document.getElementById('sepuluh').pause();
																document.getElementById('sepuluh').currentTime=0;
																document.getElementById('sepuluh').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
												<?php
													}elseif($antrian ==11){
														//JIKA 11 MAKA MAIKAN SUARA SEBELAS
												?>
														setTimeout(function() {
																document.getElementById('sebelas').pause();
																document.getElementById('sebelas').currentTime=0;
																document.getElementById('sebelas').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
												<?php
													}elseif($antrian < 20){
														//JIKA 12-20 MAKA MAIKAN SUARA ANGKA2+"BELAS"
												?>
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('belas').pause();
																document.getElementById('belas').currentTime=0;
																document.getElementById('belas').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
												<?php
													}elseif($antrian <= 99){
														//JIKA PULUHAN MAKA MAINKAN SUARA ANGKA1+PULUH+AKNGKA2
												?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('puluh').pause();
																document.getElementById('puluh').currentTime=0;
																document.getElementById('puluh').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+900;

														<?php
													}elseif($antrian ==100){
														//JIKA 100 MAKA MAIKAN SUARA RATUS
												?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
												<?php
													}elseif($antrian <= 109){
														//JIKA 100 MAKA MAIKAN SUARA RATUS
												?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
															setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel2').pause();
																document.getElementById('suarabel2').currentTime=0;
																document.getElementById('suarabel2').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;


												<?php
												}elseif($antrian ==110){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
											?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
														}elseif($antrian ==111){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel2').pause();
																document.getElementById('suarabel2').currentTime=0;
																document.getElementById('suarabel2').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;


														<?php
														}elseif($antrian > 111){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel2').pause();
																document.getElementById('suarabel2').currentTime=0;
																document.getElementById('suarabel2').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
														}elseif($antrian ==120 or $antrian ==130 or $antrian ==140 or $antrian ==150 or $antrian ==160 or $antrian ==170 or $antrian ==180 or $antrian ==190){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
														}elseif($antrian ==200 or $antrian ==300){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
													}elseif($antrian < 209){
														//JIKA 100 MAKA MAIKAN SUARA RATUS
												?>
															setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel2').pause();
																document.getElementById('suarabel2').currentTime=0;
																document.getElementById('suarabel2').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
														<?php
												}elseif($antrian ==210){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
											?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
															setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
														}elseif($antrian ==211 ){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
															setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel2').pause();
																document.getElementById('suarabel2').currentTime=0;
																document.getElementById('suarabel2').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;

														<?php
														}elseif($antrian ==220 or $antrian ==230 or $antrian ==240 or $antrian ==250 or $antrian ==260 or $antrian ==270 or $antrian ==280 or $antrian ==290){
													//JIKA 10 MAKA MAIKAN SUARA SEPULUH
														?>
														setTimeout(function() {
																document.getElementById('suarabel0').pause();
																document.getElementById('suarabel0').currentTime=0;
																document.getElementById('suarabel0').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('suarabel1').pause();
																document.getElementById('suarabel1').currentTime=0;
																document.getElementById('suarabel1').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
														setTimeout(function() {
																document.getElementById('nol').pause();
																document.getElementById('nol').currentTime=0;
																document.getElementById('nol').play();
															}, totalwaktu);
														totalwaktu=totalwaktu+1000;
												<?php
													}else{
														//JIKA LEBIH DARI 100
														//Karena aplikasi ini masih sederhana maka logina konversi hanya sampai 100
														//Selebihnya akan langsung disebutkan angkanya saja
														//tanpa kata "RATUS", "PULUH", maupun "BELAS"
												?>

												<?php
													for($i=0;$i<$panjang;$i++){
												?>

												totalwaktu=totalwaktu+1000;
												setTimeout(function() {
																document.getElementById('suarabel<?php echo $i; ?>').pause();
																document.getElementById('suarabel<?php echo $i; ?>').currentTime=0;
																document.getElementById('suarabel<?php echo $i; ?>').play();
															}, totalwaktu);
												<?php
													}
													}
												?>


												totalwaktu=totalwaktu+1000;
												setTimeout(function() {
																document.getElementById('suarabelsuarabelloket').pause();
																document.getElementById('suarabelsuarabelloket').currentTime=0;
																document.getElementById('suarabelsuarabelloket').play();
															}, totalwaktu);

												totalwaktu=totalwaktu+1000;
												setTimeout(function() {
																document.getElementById('suarabelloket<?php echo $loket3; ?>').pause();
																document.getElementById('suarabelloket<?php echo $loket3; ?>').currentTime=0;
																document.getElementById('suarabelloket<?php echo $loket3; ?>').play();
															}, totalwaktu);
															totalwaktu=totalwaktu+1000;
												setTimeout(function() {
																document.getElementById('suarabeel').pause();
																document.getElementById('suarabeel').currentTime=0;
																document.getElementById('suarabeel').play();
															}, totalwaktu);
										}

										</script>
