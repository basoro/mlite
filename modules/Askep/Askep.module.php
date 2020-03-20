<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class Askep {
    function index() {
      include('modules/Askep/inc/asuhan_keperawatan.php');
    }
    function pengkajian_awal() {
      include('modules/Askep/inc/pengkajian_awal.php');
    }
    function rencana_asuhan() {
      include('modules/Askep/inc/rencana_asuhan.php');
    }
}
?>
