<?php
if (!defined('IS_IN_MODULE')) {
    die("NO DIRECT FILE ACCESS!");
}
?>

<?php
class IGD
{
    public function index()
    {
        global $dataSettings, $date;
        include('modules/IGD/pasien.php');
    }
    public function lap_igd()
    {
        global $dataSettings, $date;
        include('modules/IGD/lap_igd.php');
    }
}
?>
