<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Laporan Pasien APS';
include_once('../../config.php');
?>
<div class="card">
  <div class="header">
    <h2>
      <?php echo $title;?>
      <small><?php $date = date('Y-m-d'); if (isset($_POST['tahun'])) {
    $tahun = $_POST['tahun'];
} else {
    $tahun = date("Y", strtotime($date));
};
      if (isset($_POST['bulan'])) {
          $bulan = $_POST['bulan'];
      } else {
          $bulan = date("M", strtotime($date));
      };echo "Periode ".$tahun; ?></small>
    </h2>
  </div>
<div class="body">
  <div class="card">
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Jumlah</th>
        </tr>
      </thead>
      <tbody>
        <?php $sql = query("SELECT
          COUNT(no_rawat) as jml ,
          tgl_masuk
          FROM `kamar_inap`
          WHERE stts_pulang in ('APS','Permintaan Sendiri','Pulang Paksa')
          AND YEAR(tgl_masuk) = '{$tahun}' AND MONTH(tgl_masuk) = '{$bulan}' GROUP BY tgl_masuk");
          while ($a = fetch_array($sql)) {
              ?>
        <tr>
          <td><?php echo $a['tgl_masuk']; ?></td>
          <td><?php echo $a['jml']; ?></td>
        </tr>
        <?php
          }
        ?>
      </tbody>
    </table>
  </div>
  <div class="row clearfix">
  	<form method="post" action="">
      <div class="col-lg-5">
        <div class="form-group">
          <div class="form-line">
            <select name="bulan" class="form-control">
              <option value="01">Januari</option>
              <option value="02">Pebruari</option>
              <option value="03">Maret</option>
              <option value="04">April</option>
              <option value="05">Mei</option>
              <option value="06">Juni</option>
              <option value="07">Juli</option>
              <option value="08">Agustus</option>
              <option value="09">September</option>
              <option value="10">Oktober</option>
              <option value="11">November</option>
              <option value="12">Desember</option>
            </select>
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="form-group">
          <div class="form-line">
            <select name="tahun" class="form-control">
              <?php
                $current_year = date('Y');
                $years = range($current_year-5, $current_year);
                foreach ($years as $year) {
                    echo '<option value="'.$year.'">'.$year.'</option>';
                }
              ?>
            </select>
          </div>
        </div>
      </div>
      <div class="col-lg-2">
        <div class="form-group">
          <div class="form-line">
            <input type="submit" class="btn bg-blue btn-block btn-lg waves-effect" value="Submit">
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
</div>

<?php
include_once('layout/footer.php');
?>
