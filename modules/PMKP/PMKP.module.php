<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class PMKP {
    function index() {
      include('modules/PMKP/dashboard.php');
    }
}
?>
