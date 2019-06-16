<?php
include('../config.php');

if($_SERVER['REQUEST_METHOD'] == "POST") {
  $update = query("UPDATE kamar_inap SET stts_pulang = '".$_POST['stts_pulang']."' WHERE no_rawat = '".$_POST['no_rawat']."'");
  if($update){ 
  	redirect('../pasien-ranap.php');
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Using Bootstrap modal</title>

    <!-- Bootstrap Core Css -->
    <link href="http://simrs.rshdbarabai.com/dashboard/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Bootstrap Select Css -->
    <link href="http://simrs.rshdbarabai.com/dashboard/plugins/bootstrap-select/css/bootstrap-select.css" rel="stylesheet" />

    <!-- Custom Css -->
    <link href="http://simrs.rshdbarabai.com/dashboard/css/style.css" rel="stylesheet">

    <!-- AdminBSB Themes. You can choose a theme from css/themes instead of get all themes -->
    <link href="http://simrs.rshdbarabai.com/dashboard/css/themes/all-themes.css" rel="stylesheet" />
</head>
<body class="theme-red">
      <div class="body">
              	<form action="includes/editsttspulang.php" method="POST">
                <div class="modal-header">
                    <h4 class="modal-title" id="smallModalLabel">Status Pulang</h4>
                </div>
                <div class="modal-body">
                    <select name="stts_pulang" class="form-control show-tick">
                    <?php
                    $no_rawat = $_GET['norawat'];
                    $result = query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'kamar_inap' AND COLUMN_NAME = 'stts_pulang'");
                    $row = fetch_array($result);
                    $enumList = explode(",", str_replace("'", "", substr($row['COLUMN_TYPE'], 5, (strlen($row['COLUMN_TYPE'])-6))));
                    foreach($enumList as $value) {
                        echo "<option value='$value'>$value</option>";
                    }
                    ?>
                    </select>
                </div>
                <div class="modal-footer">
                  	<input type="hidden" name="no_rawat" value="<?php echo $no_rawat;?>">
                    <button type="submit" class="btn btn-link waves-effect" value="simpan_stts_pulang">SIMPAN</button><button type="button" class="btn btn-link waves-effect" data-dismiss="modal">BATAL</button>
                </div>
                </form>
    </div>
</body>
</html>