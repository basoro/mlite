<?php
include('config.php');
include('init.php');
if(isset($_GET['no_rawat'])) {
    $_sql = "SELECT a.no_rkm_medis, a.no_rawat, b.nm_pasien, b.umur, a.status_lanjut , a.kd_pj, c.png_jawab, b.alamat, b.no_tlp, b.email FROM reg_periksa a, pasien b, penjab c WHERE a.no_rkm_medis = b.no_rkm_medis AND a.kd_pj = c.kd_pj AND a.no_rawat = '$_GET[no_rawat]'";
    $found_pasien = query($_sql);
    if(num_rows($found_pasien) == 1) {
	     while($row = fetch_array($found_pasien)) {
	        $no_rkm_medis  = $row['0'];
	        $get_no_rawat	 = $row['1'];
          $no_rawat	     = $row['1'];
	        $nm_pasien     = $row['2'];
	        $umur          = $row['3'];
          $status_lanjut = $row['4'];
          $kd_pj         = $row['5'];
          $png_jawab     = $row['6'];
          $alamat        = $row['7'];
          $no_telp       = $row['8'];
          $email         = $row['9'];
	     }
    } else {
	     redirect ("{$_SERVER['PHP_SELF']}");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Cetak Billing SIMKES Khanza</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="<?php echo URL; ?>/assets/plugins/bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?php echo URL; ?>/assets/plugins/font-awesome/css/font-awesome.min.css">
  <!-- Theme style -->
  <style>
  /*
 * Page: Invoice
 * -------------
 */
.invoice {
  position: relative;
  background: #fff;
  border: 1px solid #f4f4f4;
  padding: 20px;
  margin: 10px 25px;
}
.invoice-title {
  margin-top: 0;
}
/*
 * Misc: print
 * -----------
 */
@media print {
  .no-print,
  .main-sidebar,
  .left-side,
  .main-header,
  .content-header {
    display: none !important;
  }
  .content-wrapper,
  .right-side,
  .main-footer {
    margin-left: 0 !important;
    min-height: 0 !important;
    -webkit-transform: translate(0, 0) !important;
    -ms-transform: translate(0, 0) !important;
    -o-transform: translate(0, 0) !important;
    transform: translate(0, 0) !important;
  }
  .fixed .content-wrapper,
  .fixed .right-side {
    padding-top: 0 !important;
  }
  .invoice {
    width: 100%;
    border: 0;
    margin: 0;
    padding: 0;
  }
  .invoice-col {
    float: left;
    width: 33.3333333%;
  }
  .table-responsive {
    overflow: auto;
  }
  .table-responsive > .table tr th,
  .table-responsive > .table tr td {
    white-space: normal !important;
  }
}
</style>

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>
<body onload="window.print();">
<div class="wrapper">
  <!-- Main content -->
  <section class="invoice">
    <!-- title row -->
    <div class="row">
      <div class="col-xs-12">
        <h2 class="page-header">
          <i class="fa fa-money"></i> Billing <?php echo $dataSettings['nama_instansi']; ?>
          <small class="pull-right">Tanggal: <?php echo date('d-m-Y'); ?></small>
        </h2>
      </div>
      <!-- /.col -->
    </div>
    <!-- info row -->
    <div class="row invoice-info">
      <div class="col-sm-4 invoice-col">
        Dari
        <address>
          <strong><?php echo $dataSettings['nama_instansi']; ?></strong><br>
          <?php echo $dataSettings['alamat_instansi']; ?><br>
          Phone: <?php echo $dataSettings['kontak']; ?><br>
          Email: <?php echo $dataSettings['email']; ?>
        </address>
      </div>
      <!-- /.col -->
      <div class="col-sm-4 invoice-col">
        Kepada
        <address>
          <strong><?php echo $nm_pasien; ?></strong><br>
          <?php echo $alamat; ?><br>
          Phone: <?php echo $no_telp; ?><br>
          Email: <?php echo $email; ?>
        </address>
      </div>
      <!-- /.col -->
      <div class="col-sm-4 invoice-col">
        <b>Order ID:</b> <?php echo $no_rawat; ?><br>
        <b>Account:</b> <?php echo $no_rkm_medis; ?>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->

    <!-- Table row -->
    <div class="row">
      <div class="col-xs-12 table-responsive">
        <table class="table table-striped">
          <thead>
              <tr>
                  <th>Nama Item</th>
                  <th>Jumlah</th>
                  <th>Biaya</th>
                  <th>Total Biaya</th>
              </tr>
          </thead>
          <tbody>
            <tr>
                <th>Tindakan</th><th></th><th></th><th></th>
            </tr>
          <?php
          $query_tindakan = query("SELECT a.kd_jenis_prw, a.tgl_perawatan, a.tarif_tindakandr, b.nm_perawatan  FROM rawat_jl_dr a, jns_perawatan b WHERE a.kd_jenis_prw = b.kd_jenis_prw AND a.no_rawat = '{$no_rawat}'");
          $total_tindakan = 0;
          while ($data_tindakan = fetch_array($query_tindakan)) {
              $total_tindakan += $data_tindakan['2'];
          ?>
              <tr>
                  <td><?php echo $data_tindakan['3']; ?></td>
                  <td></td>
                  <td>Rp. <span class="pull-right"><?php echo number_format($data_tindakan['2'],2,',','.'); ?></span></td>
                  <td>Rp. <span class="pull-right"><?php echo number_format($data_tindakan['2'],2,',','.'); ?></span></td>
              </tr>
          <?php
          }
          ?>
          <tr>
              <td>Sub total tindakan</td><td></td><td></td><td>Rp. <span class="pull-right"><?php echo number_format($total_tindakan,2,',','.'); ?></span></td>
          </tr>
          <tr>
              <th>Obat</th><th></th><th></th><th></th>
          </tr>
           <?php
           $query_resep = query("SELECT a.kode_brng, a.jml, a.aturan_pakai, b.nama_brng, a.no_resep, b.jualbebas FROM resep_dokter a, databarang b, resep_obat c WHERE a.kode_brng = b.kode_brng AND a.no_resep = c.no_resep AND c.no_rawat = '{$no_rawat}'");
           $total_obat = 0;
           while ($data_resep = fetch_array($query_resep)) {
             $total_obat += $data_resep['1']*$data_resep['5'];
           ?>
               <tr>
                   <td><?php echo $data_resep['3']; ?></td>
                   <td><?php echo $data_resep['1']; ?></td>
                   <td>Rp. <span class="pull-right"><?php echo number_format($data_resep['5'],2,',','.'); ?></span></td>
                   <td>Rp. <span class="pull-right"><?php echo number_format($data_resep['1']*$data_resep['5'],2,',','.'); ?></span></td>
               </tr>
           <?php
           }
           ?>
           <tr>
               <td>Sub total obat</td><td></td><td></td><td>Rp. <span class="pull-right"><?php echo number_format($total_obat,2,',','.'); ?></span></td>
           </tr>
           <tr>
               <th>Tambahan Biaya </th><th></th><th></th><th></th>
           </tr>
            <?php
            $query_tambahan_biaya = query("SELECT * FROM tambahan_biaya WHERE no_rawat = '{$no_rawat}'");
            $total_tambahan = 0;
            while ($data_tambahan_biaya = fetch_array($query_tambahan_biaya)) {
              $total_tambahan += $data_tambahan_biaya['2'];
            ?>
                <tr>
                    <td><?php echo $data_tambahan_biaya['1']; ?></td>
                    <td>-</td>
                    <td>Rp. <span class="pull-right"><?php echo number_format($data_tambahan_biaya['2'],2,',','.'); ?></span></td>
                    <td>Rp. <span class="pull-right"><?php echo number_format($data_tambahan_biaya['2'],2,',','.'); ?></span></td>
                </tr>
            <?php
            }
            ?>
          </tbody>
        </table>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->

    <div class="row">
      <!-- accepted payments column -->
      <div class="col-xs-6">
        <p class="lead">Cara Bayar:</p>
        <h3><?php echo $png_jawab; ?>
      </div>
      <!-- /.col -->
      <div class="col-xs-6">
        <div class="table-responsive">
          <table class="table">
            <tr>
              <th style="width:50%">Subtotal Tindakan</th>
              <td>Rp. <span class="pull-right"><?php echo number_format($total_tindakan,2,',','.'); ?></span></td>
            </tr>
            <tr>
              <th>Subtotal Obat</th>
              <td>Rp. <span class="pull-right"><?php echo number_format($total_obat,2,',','.'); ?></span></td>
            </tr>
            <tr>
              <th>Subtotal Tambahan Biaya</th>
              <td>Rp. <span class="pull-right"><?php echo number_format($total_tambahan,2,',','.'); ?></span></td>
            </tr>
            <tr>
              <th>Total:</th>
              <td><b>Rp. <span class="pull-right"><?php echo number_format($total_tindakan+$total_obat+$total_tambahan,2,',','.'); ?></span></b></td>
            </tr>
          </table>
        </div>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </section>
  <!-- /.content -->
</div>
<!-- ./wrapper -->
</body>
</html>
