<?php
if (!defined('IS_IN_MODULE')) {
    die("NO DIRECT FILE ACCESS!");
}
?>

<?php
class Laporan
{
    public function index()
    {
        global $dataSettings, $date , $dataGet;
        include('modules/Laporan/aps.php');
    }
    public function ralan()
    {
        global $dataSettings, $date , $dataGet;
        include('modules/Laporan/lapkelpenyakit.php');
    }
}
?>
