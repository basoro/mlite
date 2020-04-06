<?php
if (!defined('IS_IN_MODULE')) {
    die("NO DIRECT FILE ACCESS!");
}
?>

<?php
class RawatInap
{
    public function index()
    {
        global $dataSettings, $date;
        include('modules/RawatInap/pasien.php');
    }
    public function diet_pasien()
    {
        global $dataSettings, $date;
        include('modules/RawatInap/diet_pasien.php');
    }
    public function awkep()
    {
        global $dataSettings, $date;
        include 'modules/RawatInap/awkep.php';
    }
}
?>
