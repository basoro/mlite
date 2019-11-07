<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : dentix.id@gmail.com
* Licence under GPL
***/

$title = 'Pembayaran';
include_once('config.php');
include_once('layout/header.php');
include_once('layout/sidebar.php');

if(isset($_GET['no_rawat'])) {
    $_sql = "SELECT a.no_rkm_medis, a.no_rawat, b.nm_pasien, b.umur, a.status_lanjut , a.kd_pj, c.png_jawab FROM reg_periksa a, pasien b, penjab c WHERE a.no_rkm_medis = b.no_rkm_medis AND a.kd_pj = c.kd_pj AND a.no_rawat = '$_GET[no_rawat]'";
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
	     }
    } else {
	     redirect ("{$_SERVER['PHP_SELF']}");
    }
}

if($_SERVER['REQUEST_METHOD'] == "POST") {
  if($_POST['nama_biaya'] <> '' && $_POST['nama_biaya'] <> '') {
    query("INSERT INTO tambahan_biaya SET no_rawat = '{$_GET['no_rawat']}', nama_biaya = '{$_POST['nama_biaya']}', besar_biaya = '{$_POST['besar_biaya']}'");
  }
}

?>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                <?php echo $title; ?>
                                <small>Periode <?php if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) { echo date("d-m-Y",strtotime($_POST['tgl_awal']))." s/d ".date("d-m-Y",strtotime($_POST['tgl_akhir'])); } else { echo date("d-m-Y",strtotime($date)) . ' s/d ' . date("d-m-Y",strtotime($date));} ?></small>
                            </h2>
                        </div>
                        <?php
                        $action = isset($_GET['action'])?$_GET['action']:null;
                        if(!$action){
                        ?>
                        <div class="body">
                            <table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">
                                <thead>
                                    <tr>
                                        <th>Nama Pasien</th>
                                        <th>No. RM</th>
                                        <th>Alamat</th>
                                        <th>Jenis Bayar</th>
                                        <th>Status Bayar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $sql = "SELECT a.nm_pasien, b.no_rkm_medis, a.alamat, c.png_jawab, b.stts, b.status_bayar, b.no_rawat FROM pasien a, reg_periksa b, penjab c WHERE a.no_rkm_medis = b.no_rkm_medis AND b.kd_pj = c.kd_pj";
                                if(isset($_POST['status_lanjut']) && $_POST['status_lanjut'] == 'Ralan') {
                                	$sql .= " AND b.status_lanjut = 'Ralan'";
                                }
                                if(isset($_POST['status_lanjut']) && $_POST['status_lanjut'] == 'Ranap') {
                                  $sql .= " AND b.status_lanjut = 'Ranap'";
                                }
                                if(isset($_POST['tgl_awal']) && $_POST['tgl_awal'] !=="" && isset($_POST['tgl_akhir']) && $_POST['tgl_akhir'] !=="") {
                                	$sql .= " AND b.tgl_registrasi BETWEEN '$_POST[tgl_awal]' AND '$_POST[tgl_akhir]'";
                                } else {
                                  	$sql .= " AND b.tgl_registrasi = '$date'";
                                }
                                $query = query($sql);
                                while($row = fetch_array($query)) {
                                ?>
                                    <tr>
                                        <td><?php echo SUBSTR($row['0'],0,20); ?></td>
                                        <td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=view&no_rawat=<?php echo $row['6']; ?>"><?php echo $row['1']; ?></a></td>
                                        <td><?php echo $row['2']; ?></td>
                                        <td><?php echo $row['3']; ?></td>
                                        <td><?php echo $row['5']; ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                                </tbody>
                            </table>
                            <div class="row clearfix">
                                <form method="post" action="">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <input type="text" name="tgl_awal" class="datepicker form-control" placeholder="Pilih tanggal awal...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <input type="text" name="tgl_akhir" class="datepicker form-control" placeholder="Pilih tanggal akhir...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <div class="form-line">
                                          <select name="status_lanjut" class="form-control show-tick">
                                              <option>Semua</option>
                                              <option value="Ralan">Rawat Jalan</option>
                                              <option value="Ranap">Rawat Inap</option>
                                          </select>
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
                      <?php } ?>
                      <?php if($action == "view"){ ?>
                        <div class="body">
                          <dl class="dl-horizontal">
                            <dt class="col-1">Nama Lengkap</dt>
                            <dd class="col-1"><?php echo $nm_pasien; ?></dd>
                            <dt class="col-2">No. RM</dt>
                            <dd class="col-2"><?php echo $no_rkm_medis; ?></dd>
                            <dt>No. Rawat</dt>
                            <dd><?php echo $no_rawat; ?></dd>
                            <dt>Cara Bayar</dt>
                            <dd><?php echo $png_jawab; ?></dd>
                            <dt>Umur</dt>
                            <dd><?php echo $umur; ?> Th</dd>
                          </dl>
                        </div>
                        <div class="body">
                        <table class="table table-bordered table-hover display nowrap" width="100%">
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
                                 <th>Tambahan Biaya <button class="btn bg-orange waves-effect" data-toggle="modal" data-target="#unitModal">+</button></th><th></th><th></th><th></th>
                             </tr>
                              <?php
                              $query_tambahan_biaya = query("SELECT * FROM tambahan_biaya WHERE no_rawat = '{$no_rawat}'");
                              $total_tambahan = 0;
                              while ($data_tambahan_biaya = fetch_array($query_tambahan_biaya)) {
                                $total_tambahan += $data_tambahan_biaya['2'];
                              ?>
                                  <tr>
                                      <td><?php echo $data_tambahan_biaya['1']; ?> <a class="btn btn-danger waves-effect" href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_biaya&no_rawat=<?php echo $no_rawat; ?>">x</a></td>
                                      <td>-</td>
                                      <td>Rp. <span class="pull-right"><?php echo number_format($data_tambahan_biaya['2'],2,',','.'); ?></span></td>
                                      <td>Rp. <span class="pull-right"><?php echo number_format($data_tambahan_biaya['2'],2,',','.'); ?></span></td>
                                  </tr>
                              <?php
                              }
                              ?>
                              <tr>
                                  <th>Total</th><th></th><th></th><th>Rp. <span class="pull-right"><?php echo number_format($total_tindakan+$total_obat+$total_tambahan,2,',','.'); ?></span></th>
                              </tr>
                            </tbody>
                         </table>
                        </div>
                        <div class="body">
                          <a href="print_billing.php?no_rawat=<?php echo $no_rawat; ?>" class="btn bg-indigo waves-effect" target="_blank">CETAK</a>
                        </div>
                      <?php } ?>
                      <?php
                        if($action == "delete_biaya"){
                        	$hapus = "DELETE FROM tambahan_biaya WHERE no_rawat='{$_REQUEST['no_rawat']}'";
                        	$hasil = query($hapus);
                        	if (($hasil)) {
                        	    redirect("{$_SERVER['PHP_SELF']}?action=view&no_rawat={$no_rawat}");
                        	}
                        }
                      ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="unitModal" tabindex="-1" role="dialog" aria-labelledby="unitModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="width:800px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="unitModalLabel">Tambahan Biaya</h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal" method="POST" action="">
                        <div class="row clearfix">
                            <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                <label for="email_address_2">Nama :</label>
                            </div>
                            <div class="col-lg-4 col-md-10 col-sm-8">
                              <div class="input-group input-group-lg">
                                  <div class="form-line">
                                      <input type="text" class="form-control" name="nama_biaya" id="nama_biaya" placeholder="Item biaya">
                                  </div>
                              </div>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-4 form-control-label font-20 hidden-xs">
                                <label for="password_2">Biaya :</label>
                            </div>
                            <div class="col-lg-4 col-md-10 col-sm-8">
                              <div class="input-group input-group-lg">
                                  <div class="form-line">
                                      <input type="text" class="form-control" name="besar_biaya" id="besar_biaya" placeholder="Besar biaya">
                                  </div>
                              </div>
                            </div>
                        </div>
                        <div class="row clearfix" style="margin-bottom:40px;">
                            <div class="col-lg-12 text-center">
                                <button type="submit" class="btn btn-lg btn-primary m-t-15 m-l-15 waves-effect" id="simpan">SIMPAN</button>
                            </div>
                        </div>
                    </form>
                    <table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Nama Biaya</th>
                                <th>Besar Biaya</th>
                            </tr>
                        </thead>
                        <tbody>
                          <?php
                          $all_tambahan_biaya = query("SELECT * FROM tambahan_biaya");
                          while ($data_tambahan_biaya = fetch_array($all_tambahan_biaya)) {
                          ?>
                              <tr class="tambahan_biaya" data-namabiaya="<?php echo $data_tambahan_biaya['1']; ?>" data-besarbiaya="<?php echo $data_tambahan_biaya['2']; ?>">
                                  <td><?php echo $data_tambahan_biaya['1']; ?></td>
                                  <td>Rp. <?php echo number_format($data_tambahan_biaya['2'],2,',','.'); ?></td>
                              </tr>
                          <?php
                          }
                          ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<?php
include_once('layout/footer.php');
?>

<script>
$(document).on('click', '.tambahan_biaya', function (e) {
    document.getElementById("nama_biaya").value = $(this).attr('data-namabiaya');
    document.getElementById("besar_biaya").value = $(this).attr('data-besarbiaya');
});
</script>
