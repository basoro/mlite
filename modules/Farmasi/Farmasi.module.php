<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<ol class="breadcrumb breadcrumb-bg-grey" style="padding:10px !important;">
    <li><a href="<?php echo URL; ?>">Home</a></li>
    <li><a href="<?php echo URL; ?>/?module=Farmasi">Farmasi</a></li>
    <li class="active">Index</li>
</ol>

<?php
class Farmasi {
    function index() {
?>
<div class="body">
    <div class="content">
      <?php include('modules/Farmasi/dashboard.php'); ?>
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
