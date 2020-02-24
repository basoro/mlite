<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : dentix.id@gmail.com
*
* File : init.php
* Description : To check cookie and session
* Licence under GPL
***/

if(num_rows(query("SHOW TABLES LIKE 'setting'")) !== 1) {
  header("Location: install.php");
}

$data_admin = fetch_array(query("SELECT AES_DECRYPT(usere,'nur') as id_user, AES_DECRYPT(passworde,'windi') as password FROM admin WHERE usere = AES_ENCRYPT('{$_COOKIE['username']}','nur') AND passworde = AES_ENCRYPT('{$_COOKIE['password']}','windi')"));
if(num_rows(query("SHOW TABLES LIKE 'roles'")) !== 1) {
    redirect(URL . '/login.php');
} else {
  $data = fetch_array(query("SELECT AES_DECRYPT(a.id_user,'nur') as id_user, AES_DECRYPT(a.password,'windi') as password, b.cap as kd_poli, b.role as role FROM user a, roles b WHERE a.id_user = AES_ENCRYPT('{$_COOKIE['username']}','nur') AND b.username = '{$_COOKIE['username']}' AND a.password = AES_ENCRYPT('{$_COOKIE['password']}','windi')"));
}

if (!isset($_COOKIE['username']) && !isset($_COOKIE['password'])) {
    redirect(URL . '/login.php');
} else if (!in_array($_COOKIE['username'], array($data[0],$data_admin[0]))) {
    redirect(URL . '/login.php?action=logout');
} else if (!in_array($_COOKIE['password'], array($data[1],$data_admin[1]))) {
    redirect(URL . '/login.php?action=logout');
} else {
    if($_COOKIE['username'] == $data_admin[0]) {
        $_SESSION['username'] = $data_admin[0];
        $_SESSION['jenis_poli'] = '';
        $_SESSION['role'] = 'Admin';
    } else {
        $_SESSION['username'] = $data[0];
        $_SESSION['jenis_poli'] = $data[2];
        $_SESSION['role'] = $data[3];
    }
}

$getUserModule = fetch_assoc(query("SELECT module FROM roles WHERE username = '{$_SESSION['username']}'"));
$getUserModule['module'] = str_replace(" ", "", $getUserModule['module']);
$userModules=explode(",",$getUserModule['module']);

$jenis_poli = isset($_SESSION['jenis_poli'])?$_SESSION['jenis_poli']:null;
$role = isset($_SESSION['role'])?$_SESSION['role']:null;

$json_updates = file_get_contents_curl("https://khanza.basoro.id/updates.php?action=changelog");

if(PRODUCTION == true) {
  ini_set('display_errors', 0);
  error_reporting(E_ERROR | E_WARNING | E_PARSE);
}

?>
