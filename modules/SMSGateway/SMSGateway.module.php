<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class SMSGateway {
    function index() {
      global $connection;
      include('modules/SMSGateway/inc/index.php');
    }
    function inbox() {
      include('modules/SMSGateway/inc/inbox.php');
    }
    function listphone() {
      include('modules/SMSGateway/inc/listphone.php');
    }
    function sendsms() {
      include('modules/SMSGateway/inc/instant.php');
    }
    function outbox() {
      include('modules/SMSGateway/inc/outbox.php');
    }
}
?>
