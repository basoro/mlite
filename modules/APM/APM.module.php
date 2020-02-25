<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class APM {
    function index() {
      if(num_rows(query("SHOW TABLES LIKE 'antrian_loket'")) !== 1) {
        echo '<div class="alert bg-pink alert-dismissible text-center">';
        echo '<p class="lead">Belum terinstall Database Antrian Loket & CS</p>';
        echo '<a href="'.URL.'/index.php?module=APM&page=install" class="btn btn-lg btn-primary m-t-20" style="color:#fff;">Install Sekarang</a>';
        echo '</div>';
      } else if(num_rows(query("SHOW TABLES LIKE 'antrics'")) !== 1) {
        echo '<div class="alert bg-pink alert-dismissible text-center">';
        echo '<p class="lead">Belum terinstall Database Antrian CS</p>';
        echo '<a href="'.URL.'/index.php?module=APM&page=install_antrics" class="btn btn-lg btn-primary m-t-20" style="color:#fff;">Install Sekarang</a>';
        echo '</div>';
      } else if(num_rows(query("SHOW TABLES LIKE 'antriprioritas'")) !== 1) {
        echo '<div class="alert bg-pink alert-dismissible text-center">';
        echo '<p class="lead">Belum terinstall Database Antrian Prioritas</p>';
        echo '<a href="'.URL.'/index.php?module=APM&page=install_antriprioritas" class="btn btn-lg btn-primary m-t-20" style="color:#fff;">Install Sekarang</a>';
        echo '</div>';
      } else {
?>
<?php display_message(); ?>
<div class="card">
  <div class="header">
      <h2>Anjungan Pasien Mandiri</h2>
  </div>
  <div class="body">
      <div class="row clearfix">
        <div class="col-lg-6 text-center">
          <div class="card">
            <img src="<?php echo URL; ?>/modules/APM/images/apm.png" class="img-responsive">
          </div>
          <a href="<?php echo URL; ?>/modules/APM/inc/index.php" class="btn btn-lg btn-primary" target="_blank">Antrian & APM</a>
        </div>
        <div class="col-lg-6 text-center">
          <div class="card">
            <img src="<?php echo URL; ?>/modules/APM/images/sep.png" class="img-responsive">
          </div>
          <a href="<?php echo URL; ?>/modules/APM/inc/ceksep.php" class="btn btn-lg btn-danger  " target="_blank">Cetak SEP Mandiri</a>
        </div>
      </div>
  </div>
</div>
<div class="card">
  <div class="header">
      <h2>Antrian Loket Rawat Jalan</h2>
  </div>
  <div class="body">
      <div class="row clearfix">
        <div class="col-lg-6 text-center">
          <div class="card">
            <img src="<?php echo URL; ?>/modules/APM/images/antrian.png" class="img-responsive">
          </div>
          <a href="<?php echo URL; ?>/modules/APM/inc/antrian.php" class="btn btn-lg btn-success" target="_blank">Antrian Loket</a>
          <a href="<?php echo URL; ?>/modules/APM/inc/antrian_poli.php" class="btn btn-lg btn-success" target="_blank">Antrian Poli</a>
          <a href="<?php echo URL; ?>/modules/APM/inc/antrian_apotek.php" class="btn btn-lg btn-success" target="_blank">Antrian Apotek</a>
        </div>
        <div class="col-lg-6 text-center">
          <div class="card">
            <img src="<?php echo URL; ?>/modules/APM/images/pemanggil.png" class="img-responsive">
          </div>
          <a href="<?php echo URL; ?>/modules/APM/inc/antrian.php?action=panggil_loket" class="btn btn-lg btn-success" target="_blank">Pemanggil Antrian</a>
          <a href="<?php echo URL; ?>/modules/APM/inc/antrian.php?action=panggil_cs" class="btn btn-lg btn-success" target="_blank">Pemanggil CS</a>
          <a href="<?php echo URL; ?>/modules/APM/inc/antrian.php?action=panggil_prioritas" class="btn btn-lg btn-success" target="_blank">Pemanggil Prioritas</a>
        </div>
      </div>
  </div>
</div>

<?php
      }
    }
    function install() {
      global $connection;
      $sql_userwall = "CREATE TABLE `antrian_loket` (
        `kd` int(50) NOT NULL,
        `type` varchar(50) NOT NULL,
        `noantrian` varchar(50) NOT NULL,
        `postdate` date NOT NULL,
        `start_time` time NOT NULL,
        `end_time` time NOT NULL DEFAULT '00:00:00'
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
      ALTER TABLE `antrian_loket` ADD PRIMARY KEY (`kd`);
      ALTER TABLE `antrian_loket` MODIFY `kd` int(50) NOT NULL AUTO_INCREMENT;
      CREATE TABLE `antrics` (
        `loket` int(11) NOT NULL,
        `antrian` int(11) NOT NULL
      ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
      ALTER TABLE `antrics`
        ADD KEY `loket` (`loket`),
        ADD KEY `antrian` (`antrian`);
      CREATE TABLE `antriprioritas` (
        `loket` int(11) NOT NULL,
        `antrian` int(11) NOT NULL
      ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
      ALTER TABLE `antriprioritas`
        ADD KEY `loket` (`loket`),
        ADD KEY `antrian` (`antrian`);";

      if(mysqli_multi_query($connection,$sql_userwall)){
          set_message ('Table created successfully.');
          redirect ('./index.php?module=APM&page=index');
      } else{
          echo "ERROR: Could not able to execute $sql. " . mysqli_error($con);
      }
    }
    function install_antrics() {
      global $connection;
      $sql_userwall = "CREATE TABLE `antrics` (
        `loket` int(11) NOT NULL,
        `antrian` int(11) NOT NULL
      ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
      ALTER TABLE `antrics`
        ADD KEY `loket` (`loket`),
        ADD KEY `antrian` (`antrian`);";

      if(mysqli_multi_query($connection,$sql_userwall)){
          set_message ('Table created successfully.');
          redirect ('./index.php?module=APM&page=index');
      } else{
          echo "ERROR: Could not able to execute $sql. " . mysqli_error($con);
      }
    }
    function install_antriprioritas() {
      global $connection;
      $sql_userwall = "CREATE TABLE `antriprioritas` (
        `loket` int(11) NOT NULL,
        `antrian` int(11) NOT NULL
      ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
      ALTER TABLE `antriprioritas`
        ADD KEY `loket` (`loket`),
        ADD KEY `antrian` (`antrian`);";

      if(mysqli_multi_query($connection,$sql_userwall)){
          set_message ('Table created successfully.');
          redirect ('./index.php?module=APM&page=index');
      } else{
          echo "ERROR: Could not able to execute $sql. " . mysqli_error($con);
      }
    }
}
?>
