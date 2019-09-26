<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<ol class="breadcrumb breadcrumb-bg-grey" style="padding:10px !important;">
    <li><a href="<?php echo URL; ?>">Home</a></li>
    <li><a href="<?php echo URL; ?>/?module=BridgingBPJS">Bridging BPJS</a></li>
    <li class="active">Index</li>
</ol>

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
