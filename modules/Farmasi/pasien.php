<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

?>

<?php
$action = isset($_GET['action'])?$_GET['action']:null;
$jenis_poli = isset($_SESSION['jenis_poli'])?$_SESSION['jenis_poli']:null;
$role = isset($_SESSION['role'])?$_SESSION['role']:null;
if(!$action){
?>
<div class="body table-responsive">
    <?php getPasien(); ?>
</div>
<?php } ?>
