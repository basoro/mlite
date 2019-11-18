<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>
<?php
class BridgingBPJS {
    function index() {
        global $dataSettings, $date, $role;
        include('modules/BridgingBPJS/bridging.php');
    }
    function data_sep() {
        global $dataSettings, $date, $role;;
        include('modules/BridgingBPJS/data-sep.php');
    }
    function cetak_sep() {
        global $dataSettings, $date, $role;;
        include('modules/BridgingBPJS/cetaksep.php');
    }
    function pasien_batal() {
      global $dataSettings, $date, $role;;
        include('modules/BridgingBPJS/pasien-batal.php');
    }
    function cek_kepesertaan() {
      global $dataSettings, $date, $role;;
        include('modules/BridgingBPJS/cek-kepesertaan.php');
    }
}
?>
