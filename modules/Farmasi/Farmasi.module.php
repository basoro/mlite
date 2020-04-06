<?php
if (!defined('IS_IN_MODULE')) {
    die("NO DIRECT FILE ACCESS!");
}
?>

<?php
class Farmasi
{
    public function index()
    {
        global $dataSettings, $date;
        include('modules/Farmasi/dashboard.php');
    }
    public function data_resep()
    {
        global $dataSettings, $date;
        include('modules/Farmasi/data-resep.php');
    }
    public function laporan_harian()
    {
        global $dataSettings, $date;
        include('modules/Farmasi/laporan-obat-harian.php');
    }
    public function laporan_ralan_ranap()
    {
        global $dataSettings, $date;
        include('modules/Farmasi/laporan-obat-ralan-ranap.php');
    }
    public function permintaan_resep()
    {
        global $dataSettings, $date;
        include('modules/Farmasi/data-permintaan-resep.php');
    }
    public function rekam_obat()
    {
        global $dataSettings, $date;
        include('modules/Farmasi/rekam-obat.php');
    }
    public function monitoring_obat()
    {
        global $dataSettings, $date;
        include('modules/Farmasi/monitoring-obat.php');
    }
    public function stok_opname()
    {
        global $dataSettings, $date;
        include('modules/Farmasi/stok-opname.php');
    }
    public function obat_expired()
    {
        global $dataSettings, $date;
        include('modules/Farmasi/obat-expired.php');
    }
    public function laporan_pemberian_obat()
    {
        global $dataSettings, $date;
        include('modules/Farmasi/laporan-pemberian-obat.php');
    }
    public function laporan_stok_opname()
    {
        global $dataSettings, $date;
        include('modules/Farmasi/laporan-stok-opname.php');
    }
    public function lappelfar()
    {
        global $dataSettings, $date;
        include('modules/Farmasi/lappelfar.php');
    }
}
?>
