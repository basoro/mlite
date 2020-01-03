<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class Master {
    function index() {
      include ('modules/Master/pages/poliklinik.php');
    }
    function poliklinik() {
      include ('modules/Master/pages/poliklinik.php');
    }
    function dokter() {
      include ('modules/Master/pages/dokter.php');
    }
    function carabayar() {
      include ('modules/Master/pages/carabayar.php');
    }
}
?>
