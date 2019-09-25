<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<div class="header">
    <h2>
        <a href="<?php echo URL; ?>/index.php">Home</a> &raquo; <a href="<?php echo $_SERVER['PHP_SELF']; ?>?module=Farmasi" style="text-decoration:none;">Farmasi</a>
    </h2>
</div>
<?php
class Farmasi {
    function index() {
?>
<div class="body">
    <div class="content">
        <?php include('modules/Farmasi/pasien.php'); ?>
    </div>
</div>
<?php
    }
    function data_obat() {
?>
<div class="body">
    <div class="content">
    </div>
</div>
<?php
    }
}
?>
