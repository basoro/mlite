<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class IGD {
    function index() {
        global $dataSettings, $date;
        include('modules/IGD/pasien.php');
    }
}
?>
