<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<ol class="breadcrumb breadcrumb-bg-grey" style="padding:10px !important;">
    <li><a href="<?php echo URL; ?>">Home</a></li>
    <li><a href="<?php echo URL; ?>/?module=RawatJalan">Rawat Jalan</a></li>
    <li class="active">Index</li>
</ol>
<?php
class RawatJalan {
    function index() {
?>
        <?php include('modules/RawatJalan/pasien.php'); ?>
<?php
    }
    function berkas_digital() { // hello function called from modules.php?module=HelloWorld&page=world
?>
        <?php include('modules/RawatJalan/berkas-digital.php'); ?>
<?php
    }
    function status_pulang() { // hello function called from modules.php?module=HelloWorld&page=world
?>
        <?php include('modules/RawatJalan/status.php'); ?>
<?php
    }
}
?>
