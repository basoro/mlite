<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class Pmkp {
    function index() {
      include('modules/PMKP/dashboard.php');
    }
}
?>
