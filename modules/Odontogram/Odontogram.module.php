<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class Odontogram {
    function index() {
      global $role, $date, $jenis_poli;
?>
<div class="card">
  <?php display_message(); ?>
  <div class="header">
      <h2>Odontogram List</h2>
  </div>
  <div class="body">
    <?php include('modules/Odontogram/inc/pasien.php'); ?>
  </div>
</div>
<?php
    }
    function history() {
      global $role, $date, $no_rkm_medis;
?>
<div class="card">
  <div class="header">
      <h2>Odontogram History</h2>
  </div>
  <div class="body">
    <?php include('modules/Odontogram/inc/history.php'); ?>
  </div>
</div>
<?php
    }
    function install() {
      global $connection;
      $sql_userwall = "CREATE TABLE `pemeriksaan_odontogram` (
        `no_rkm_medis` varchar(6) NOT NULL,
        `no_rawat` varchar(17) NOT NULL,
        `tgl_perawatan` date DEFAULT NULL,
        `jam_rawat` time DEFAULT NULL,
        `gg_xx` char(5) NOT NULL,
        `value` char(7) NOT NULL,
        `catatan` varchar(400) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
      ALTER TABLE `pemeriksaan_odontogram`
        ADD KEY `no_rawat` (`no_rawat`);
      ALTER TABLE `pemeriksaan_odontogram`
        ADD CONSTRAINT `pemeriksaan_odontogram_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE;";

      if(mysqli_multi_query($connection,$sql_userwall)){
          set_message ('Table created successfully.');
          redirect ('./index.php?module=Odontogram&page=index');
      } else{
          echo "ERROR: Could not able to execute $sql. " . mysqli_error($con);
      }

    }
}
?>
