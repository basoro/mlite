<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class SMSGateway {
    function index() {
      include('modules/SMSGateway/dashboard.php');
    }
    function masuk() {
      include('modules/SMSGateway/sms-masuk.php');
    }
    function keluar() {
      include('modules/SMSGateway/sms-keluar.php');
    }
    function kirim() {
      include('modules/SMSGateway/sms-kirim.php');
    }
    function jadwal() {
      include('modules/SMSGateway/sms-jadwal.php');
    }
    function auto() {
      include('modules/SMSGateway/sms-auto.php');
    }
    function buku_telepon() {
      include('modules/SMSGateway/sms-buku.php');
    }
}
?>
