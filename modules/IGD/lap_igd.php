<div class="card">
  <div class="header">
    <h2>Laporan Pasien IGD <?php if (isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) {
    echo "Periode ".date("d-m-Y", strtotime($_POST['tgl_awal']))." s/d ".date("d-m-Y", strtotime($_POST['tgl_akhir']));
} ?></h2>
  </div>
  <div class="body">
    <table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">
      <thead>
        <tr>
          <th>Keterangan</th>
          <th>Jumlah</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Pasien IGD RAWAT INAP</td>
          <td><?php $ranap = fetch_array(query("SELECT COUNT(no_rawat) FROM reg_periksa WHERE kd_poli='IGDK' and  tgl_registrasi BETWEEN '{$_POST['tgl_awal']}' and '{$_POST['tgl_akhir']}' and status_lanjut='Ranap'"));echo $ranap['0']; ?></td>
        </tr>
        <tr>
          <td>Pasien IGD RAWAT JALAN</td>
          <td><?php $ranap = fetch_array(query("SELECT COUNT(no_rawat) FROM reg_periksa WHERE kd_poli='IGDK' and  tgl_registrasi BETWEEN '{$_POST['tgl_awal']}' and '{$_POST['tgl_akhir']}' and status_lanjut!='Ranap'"));echo $ranap['0']; ?></td>
        </tr>
        <tr>
          <td>Total Pasien IGD</td>
          <td><?php $ranap = fetch_array(query("SELECT COUNT(no_rawat) FROM reg_periksa WHERE kd_poli='IGDK' and  tgl_registrasi BETWEEN '{$_POST['tgl_awal']}' and '{$_POST['tgl_akhir']}'"));echo $ranap['0']; ?></td>
        </tr>
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
