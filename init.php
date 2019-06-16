<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
*
* File : init.php
* Description : To check cookie and session
* Licence under GPL
***/

$data=fetch_array(query("SELECT AES_DECRYPT(a.id_user,'nur') as id_user, AES_DECRYPT(a.password,'windi') as password, b.cap as kd_poli, b.role as role FROM user a, roles b WHERE a.id_user = AES_ENCRYPT('{$_COOKIE['username']}','nur') AND b.username = '{$_COOKIE['username']}' AND a.password = AES_ENCRYPT('{$_COOKIE['password']}','windi')"));

if (!isset($_COOKIE['username']) && !isset($_COOKIE['password'])) {
    redirect(URL . '/login.php');
} else if (($_COOKIE['username'] !== $data[0]) || ($_COOKIE['password'] !== $data[1])) {
    redirect(URL . '/login.php?action=logout');
} else {
    $_SESSION['username'] = $data[0];
    $_SESSION['jenis_poli'] = $data[2];
    $_SESSION['role'] = $data[3];
}

$jenis_poli = isset($_SESSION['jenis_poli'])?$_SESSION['jenis_poli']:null;
$role = isset($_SESSION['role'])?$_SESSION['role']:null;

?>
