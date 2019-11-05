<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class Farmasi {
    function index() {
      include('modules/Farmasi/dashboard.php');
    }
    function stok_opname() {
      include('modules/Farmasi/stok-opname.php');
    }
}
?>
