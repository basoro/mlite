<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class Farmasi {
    function index() {
      global $dataSettings, $date;
      include('modules/Farmasi/dashboard.php');
    }
    function data_resep() {
      global $dataSettings, $date;
      include('modules/Farmasi/data-resep.php');
    }
    function laporan_harian() {
      global $dataSettings, $date;
      include('modules/Farmasi/laporan-obat-harian.php');
    }
    function laporan_ralan_ranap() {
      global $dataSettings, $date;
      include('modules/Farmasi/laporan-obat-ralan-ranap.php');
    }
    function permintaan_resep() {
      global $dataSettings, $date;
      include('modules/Farmasi/data-permintaan-resep.php');
    }
    function rekam_obat() {
      global $dataSettings, $date;
      include('modules/Farmasi/rekam-obat.php');
    }
    function monitoring_obat() {
      global $dataSettings, $date;
      include('modules/Farmasi/monitoring-obat.php');
    }
    function stok_opname() {
      global $dataSettings, $date;
      include('modules/Farmasi/stok-opname.php');
    }
    function obat_expired() {
      global $dataSettings, $date;
      include('modules/Farmasi/obat-expired.php');
    }
}
?>
