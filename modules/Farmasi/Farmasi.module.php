<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class Farmasi {
    function index() {
      include('modules/Farmasi/dashboard.php');
    }
    function data_obat() {
    }
}
?>
