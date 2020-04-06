<?php
if (!defined('IS_IN_MODULE')) {
    die("NO DIRECT FILE ACCESS!");
}
?>

<?php
class Inhealth
{
    public function index()
    {
        global $dataSettings, $date , $dataGet;
        include('modules/Inhealth/pasien_ranap.php');
    }
    public function rajal()
    {
        global $dataSettings, $date;
        include('modules/Inhealth/pasien_ralan.php');
    }
}
?>
