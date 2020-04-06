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
  <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />

<style>
:root {
--input-padding-x: 1.5rem;
--input-padding-y: .75rem;
--select-padding-x: 1.5rem;
--select-padding-y: .75rem;

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
.form-label-group select {
height: auto;
border-radius: 2rem;
text-align: center;
}

</style>
</head>

<body class="login">
    <div class="px-3 py-3 pt-md-4 pb-md-4 mx-auto text-center text-white">
      <h3 class="display-6">MAPPING ANJUNGAN SEP MANDIRI</h3>
      <h2 class="display-5"><?php echo $dataSettings['nama_instansi']; ?></h2>
    </div>
	<?php if (isset($_POST['cekrm'])) {
    $sql = "SELECT pasien.no_peserta
			FROM pasien , reg_periksa WHERE pasien.no_rkm_medis = reg_periksa.no_rkm_medis AND reg_periksa.tgl_registrasi = '{$date}' AND pasien.no_rkm_medis = '{$_POST['no_mr']}'";
    $data = query($sql);
    if (num_rows($data) == 1) {
        $b = fetch_assoc($data);
        redirect("pilih_poli.php?no_peserta={$b['no_peserta']}&no_rkm_medis={$_POST['no_mr']}");
    } else {
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
                  <!-- <input type="text" name="no_mr" id="no_mr" class="form-control form-control-lg bg-dark text-white" placeholder="NO REKAM MEDIS" required> -->
                  <select class="dpjp form-control form-control-lg bg-dark text-white"  name="dpjp"></select>
                  <select class="drigd form-control form-control-lg bg-dark text-white"  name="drigd"></select>
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
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="js/bootstrap-3.3.7.min.js"></script>
    <script src="js/select2.min.js"></script>
    <script>
    function formatData (data) {
        var $data = $(
            '<b>'+ data.id +'</b> - <i>'+ data.text +'</i>'
        );
        return $data;
    };

    function formatInputData (data) {
          var $data = $(
              '<b>('+ data.id +')</b> Rp '+ data.tarif +' - <i>'+ data.text +'</i>'
          );
          return $data;
      };

    function formatDataTEXT (data) {
        var $data = $(
            '<b>'+ data.text +'</b>'
        );
        return $data;
    };
    $('.dpjp').select2({
      placeholder: 'Pilih Dokter',
      ajax: {
        url: '<?php echo URL; ?>/modules/RawatInap/includes/select-dokter.php',
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
          return {
            results: data
          };
        },
        cache: true
      },
      templateResult: formatData,
      minimumInputLength: 3
    });
    $('.drigd').select2({
      placeholder: 'Pilih Dokter BPJS',
      ajax: {
        url: '<?php echo URL; ?>/modules/APM/inc/bridging-dpjp.php',
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
          return {
            results: data
          };
        },
          cache: true
        },
      templateResult: formatData,
      minimumInputLength: 3
    });

    </script>
</body>
</html>



    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->

  </body>
</html>
