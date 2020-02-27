<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class RawatJalan {
    function index() {
        global $dataSettings, $date, $time;
       include('modules/RawatJalan/pasien.php');
    }
    function rujuk_internal() {
       global $dataSettings, $date, $time;
       include('modules/RawatJalan/rujukan_internal.php');
    }
    function pasien_lanjutan() {
       global $dataSettings, $date, $time;
       include('modules/RawatJalan/pasien_lanjutan.php');
    }
}
?>
