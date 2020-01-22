<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class RawatInap {
    function index() {
        global $dataSettings, $date, $time;
       include('modules/RawatInap/pasien.php');
    }
    function diet_pasien() {
      global $dataSettings, $date, $time;
       include('modules/RawatInap/diet_pasien.php');
    }
}
?>
