<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class Farmasi {
    function index() {
      include('modules/Farmasi/dashboard.php');
    }
    function data_resep() {
      include('modules/Farmasi/data-resep.php');
    }
    function laporan_harian() {
      include('modules/Farmasi/laporan-obat-harian.php');
    }
    function laporan_ralan_ranap() {
      include('modules/Farmasi/laporan-obat-ralan-ranap.php');
    }
    function permintaan_resep() {
      include('modules/Farmasi/data-permintaan-resep.php');
    }
    function rekam_obat() {
      include('modules/Farmasi/rekam-obat.php');
    }
    function monitoring_obat() {
      include('modules/Farmasi/monitoring-obat.php');
    }
    function stok_opname() {
      include('modules/Farmasi/stok-opname.php');
    }
    function obat_expired() {
      include('modules/Farmasi/obat-expired.php');
    }
}
?>
