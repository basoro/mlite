<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>
<?php
class BridgingBPJS {
    function index() {
        global $dataSettings, $date;
        include('modules/BridgingBPJS/bridging.php');
    }
    function data_sep() {
        global $dataSettings;
        include('modules/BridgingBPJS/data-sep.php');
    }
    function pasien_batal() {
        include('modules/BridgingBPJS/pasien-batal.php');
    }
    function cek_kepesertaan() {
        global $date;
        include('modules/BridgingBPJS/cek-kepesertaan.php');
    }
}
?>
