<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : dentix.id@gmail.com
* Licence under GPL
***/

if(!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
   header("HTTP/1.0 403 Forbidden");
   exit;
}

ob_start();
session_start();

include ('../config.php');
include ('../init.php');

$page = isset($_GET['p'])? $_GET['p'] : '';

if($page=='add'){
  if(!empty($_POST['username'])){
      $modulename = implode(', ', $_POST['modulename']);
      $data = array();
      $insert = query("INSERT
          INTO
              roles
          SET
              username  = '{$_POST['username']}',
              role      = '{$_POST['role']}',
              cap       = '{$_POST['cap']}',
              module    = '{$modulename}'
      ");
  }
}
if($page=='update'){
  if(!empty($_POST['username'])){
      $modulename = implode(', ', $_POST['modulename']);
      $insert_perujuk = query("
          UPDATE
              roles
          SET
              role      = '{$_POST['role']}',
              cap       = '{$_POST['cap']}',
              module    = '{$modulename}'
          WHERE
              username = '{$_POST['username']}'
      ");
  }
}
if($page=='delete'){
  query("DELETE FROM roles WHERE username='$_POST[username]'");
}
?>
