<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

ob_start();
session_start();

include ('../config.php');
include ('../init.php');

$page = isset($_GET['p'])? $_GET['p'] : '';
if($page=='add'){
        $username = $_POST['username'];
        $role     = $_POST['role'];
        $cap      = $_POST['cap'];
        $sql = query("INSERT INTO roles VALUES('$username', '$role', '$cap')");
        if($sql){
            print "<div class='alert alert-success' role='alert'>Data has been added</div>";
        } else{
            print "<div class='alert alert-danger' role='alert'>Failed to add data</div>";
        }
} else if($page=='update'){
        $username = $_POST['username'];
        $role     = $_POST['role'];
        $cap      = $_POST['cap'];
        $update = query("UPDATE roles SET role='$role', cap='$cap' WHERE username='$username'");
        if($update){
            print "<div class='alert alert-success' role='alert'>Data has been updated</div>";
        } else{
            print "<div class='alert alert-danger' role='alert'>Failed to update data</div>";
        }
} else if($page=='delete'){
        $username = $_POST['username'];
        $delete = query("DELETE FROM roles WHERE username='$username'");
        if($delete){
            print "<div class='alert alert-success' role='alert'>Data has been deleted</div>";
        } else{
            print "<div class='alert alert-danger' role='alert'>Failed to delete data</div>";
        }
} else{
        $sql = query("SELECT * FROM roles ORDER BY username DESC");
        while($row = fetch_array($sql)){
            print "<tr>";
            print "<td>".$row['username']."</td>";
            $query1 = query("(SELECT nm_dokter AS nama FROM dokter WHERE kd_dokter = '$row[username]') UNION (SELECT nama AS nama FROM pegawai WHERE nik ='$row[username]')");
            while ($row1 = fetch_assoc($query1)) {
              print "<td>".$row1['nama']."</td>";
            }
            print "<td>".$row['role']."</td>";
            $query2 = query("(SELECT nm_poli AS nama FROM poliklinik WHERE kd_poli = '$row[cap]') UNION (SELECT nm_bangsal AS nama FROM bangsal WHERE kd_bangsal = '$row[cap]')");
            if ($row['cap'] !== "") {
              while ($row2 = fetch_assoc($query2)) {
                print "<td>".$row['cap']." - ".$row2['nama']."</td>";
              }
            } else {
              print "<td>".$row['cap']."</td>";
            }
            print "<td class='text-center'><div class='btn-group' role='group' aria-label='group-".$row['id']."'>";
            ?>
            <button onclick="editData('<?php echo $row['username'] ?>','<?php echo $row['role'] ?>','<?php echo $row['cap'] ?>')" class='btn btn-warning'>Edit</button>
            <button onclick="removeConfirm('<?php echo $row['username'] ?>')" class='btn btn-danger'>Trash</button>
            <?php
            print "</div></td>";
            print "</tr>";
        }
}
?>
