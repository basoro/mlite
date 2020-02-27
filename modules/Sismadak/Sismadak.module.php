<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class Sismadak {
    function index() {
      global $dataSettings, $sismadak_username, $sismadak_password, $sismadak_department_id, $sismadak_url;
      include('modules/Sismadak/inc/imut_manajemen.php');
    }
    function imut_klinik() {
      global $dataSettings, $sismadak_username, $sismadak_password, $sismadak_department_id, $sismadak_url;
      include('modules/Sismadak/inc/imut_klinik.php');
    }
    function imut_wajib() {
      global $dataSettings, $sismadak_username, $sismadak_password, $sismadak_department_id, $sismadak_url;
      include('modules/Sismadak/inc/imut_wajib.php');
    }
}
?>
