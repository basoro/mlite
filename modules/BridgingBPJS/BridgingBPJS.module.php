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
?>
        <?php include('modules/BridgingBPJS/pasien.php'); ?>
<?php
    }
    function data_sep() {
?>
<?php
    }
}
?>
