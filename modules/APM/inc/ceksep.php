<?php
include('../../../config.php');
include('../../../init.php');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">

	<title>Anjungan Cetak SEP Mandiri</title>

	<!-- demo -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/fontawesome.min.css">
    <link href="css/gijgo.min.css" rel="stylesheet" type="text/css" />

	<!-- jQuery & jQuery UI + theme (required) -->
	<link href="css/jquery-ui.min.css" rel="stylesheet">
	<script src="js/jquery-latest-slim.min.js"></script>
	<script src="js/jquery-ui-custom.min.js"></script>
	<script src="js/bootstrap-3.3.7.min.js"></script>

	<!-- keyboard widget css & script (required) -->
	<link href="css/keyboard.css" rel="stylesheet">
	<script src="js/jquery.keyboard.js"></script>

	<!-- keyboard extensions (optional) -->
	<script src="js/jquery.mousewheel.js"></script>

	<!-- initialize keyboard (required) -->
	<script>
		$(function(){
      $('#no_rujuk')
      	.keyboard({
      		layout: 'custom',
      		customLayout: {
      			'normal' : [
      				'B G P U Y',
      				'5 6 7 8 9',
      				'0 1 2 3 4',
      				'{accept} {cancel} {b}'
      			]
      		},
      		maxLength : 19,
      		// Prevent keys not in the displayed keyboard from being typed in
      		restrictInput : true,
      		// include lower case characters (added v1.25.7)
      		restrictInclude : 'a b c d e f',
      		// don't use combos or A+E could become a ligature
      		useCombos : false,
      		// activate the "validate" callback function
      		acceptValid : true,
      		validate : function(keyboard, value, isClosing){
      			// only make valid if input is 6 characters in length
      			return value.length === 19;
      		}
      	})
      	.addTyping();
        });
		$(function(){
      $('#no_mr')
      	.keyboard({
      		layout: 'custom',
      		customLayout: {
      			'normal' : [
      				'8 9 . {b}',
      				'4 5 6 7',
      				'0 1 2 3',
      				'{accept} {cancel}'
      			]
      		},
      		maxLength : 6,
      		// Prevent keys not in the displayed keyboard from being typed in
      		restrictInput : true,
      		// include lower case characters (added v1.25.7)
      		restrictInclude : 'a b c d e f',
      		// don't use combos or A+E could become a ligature
      		useCombos : false,
      		// activate the "validate" callback function
      		acceptValid : true,
      		validate : function(keyboard, value, isClosing){
      			// only make valid if input is 6 characters in length
      			return value.length === 6;
      		}
      	})
      	.addTyping();
        });
	</script>

<style>
:root {
--input-padding-x: 1.5rem;
--input-padding-y: .75rem;
}
body {
font-size: 20px;
}
body.login {
background: #007bff;
background: linear-gradient(to right, #0062E6, #33AEFF);
}
.card-signin {
top: 5%;
border: 0;
border-radius: 1rem
box-shadow: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.1);
background: transparent;
}

.card-signin .card-title {
margin-bottom: 2rem;
font-weight: 300;
font-size: 2.5rem;
}

.card-signin .card-body {
padding: 2rem;
}

.form-signin {
width: 100%;
}

.form-signin .btn, .form-signin .form-control {
font-size: 160%;
border-radius: 5rem;
letter-spacing: .1rem;
font-weight: bold;
padding: 1rem;
transition: all 0.2s;
}
.form-label-group {
position: relative;
margin-bottom: 1rem;
}

.form-label-group input {
height: auto;
border-radius: 2rem;
text-align: center;
}

</style>
</head>

<body class="login">
    <div class="px-3 py-3 pt-md-4 pb-md-4 mx-auto text-center text-white">
      <h1 class="display-2">APM</h1>
      <h3 class="display-6">Anjungan Pasien Mandiri Pelayanan Rawat Jalan</h3>
      <h2 class="display-5"><?php echo $dataSettings['nama_instansi']; ?></h2>
    </div>
	<?php if (isset($_POST['cekrm'])) {
  			$sql = "SELECT pasien.no_peserta
			FROM pasien , reg_periksa WHERE pasien.no_rkm_medis = reg_periksa.no_rkm_medis AND reg_periksa.tgl_registrasi = '{$date}' AND pasien.no_rkm_medis = '{$_POST['no_mr']}'";
			$data = query($sql);
  			if(num_rows($data) == 1) {
            $b = fetch_assoc($data);
  			redirect("pilih_poli.php?no_peserta={$b['no_peserta']}&no_rkm_medis={$_POST['no_mr']}");
            }else{
            echo '<script>alert("Anda Tidak Ada Jadwal Periksa Hari Ini. Silahkan hubungi petugas!!");window.location = "'.URL.'/modules/APM/inc/ceksep.php"</script>';
            }
           };?>
    <div class="container">
      <div class="row">
        <div class="col-sm-9 col-md-8 col-lg-8 mx-auto">
          <div class="card card-signin my-5">
            <div class="card-body">
              <form class="form-signin" method="post">
                <div class="form-label-group">
                  <input type="text" name="no_mr" id="no_mr" class="form-control form-control-lg bg-dark text-white" placeholder="NO REKAM MEDIS" required>
                </div>
                <button class="btn btn-lg btn-dark btn-block shadow text-uppercase" name="cekrm" onclick="this.value=\'cekrm\'" type="submit">CEK</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <br>
    <div class="pricing-header mt-5 px-3 py-3 pt-md-3 pb-md-2 mx-auto text-center text-danger bg-white">
      <h3 class="display-6"><marquee>Silahkan hubungi petugas jika anda mengalami kesulitan</marquee></h3>
    </div>

</body>
</html>
