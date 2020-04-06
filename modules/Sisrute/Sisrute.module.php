<?php
if (!defined('IS_IN_MODULE')) {
    die("NO DIRECT FILE ACCESS!");
}
?>

<?php
class Sisrute
{
    public function index()
    {
        global $dataSettings, $date , $dataGet;
        include('modules/Sisrute/pasien_ranap.php');
    }
    public function rajal()
    {
        global $dataSettings, $date;
        include('modules/Sisrute/pasien_rajal.php');
    }
}
?>
