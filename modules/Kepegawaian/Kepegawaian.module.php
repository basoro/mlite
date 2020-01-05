<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class Kepegawaian {
    function index() {
      include ('pegawai.php');
    }
    function tambah() {
      include ('tambah.php');
    }
}
?>
