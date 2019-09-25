<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<div class="header">
    <h2>
        <a href="<?php echo URL; ?>/index.php">Home</a> &raquo; <a href="<?php echo $_SERVER['PHP_SELF']; ?>?module=BridgingBPJS" style="text-decoration:none;">Bridging BPJS</a>
    </h2>
</div>
<?php
class BridgingBPJS {
    function index() {
?>
<div class="body">
    <div class="content">
        <?php include('modules/BridgingBPJS/pasien.php'); ?>
    </div>
</div>
<?php
    }
    function data_sep() {
?>
<div class="body">
    <div class="content">
    </div>
</div>
<?php
    }
}
?>
