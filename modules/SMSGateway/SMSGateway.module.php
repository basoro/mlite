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
    function group() {
      include('modules/SMSGateway/inc/group.php');
    }
    function listphone() {
      include('modules/SMSGateway/inc/listphone.php');
    }
    function sendsms() {
      include('modules/SMSGateway/inc/sendsms.php');
    }
    function listmsg() {
      include('modules/SMSGateway/inc/listmsg.php');
    }
    function auto() {
      include('modules/SMSGateway/inc/auto.php');
    }
    function report() {
      include('modules/SMSGateway/inc/report.php');
    }
    function export() {
      include('modules/SMSGateway/inc/export.php');
    }
}
?>
