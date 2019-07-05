<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Data Pengguna';
include_once('config.php');
include_once('layout/header.php');
include_once('layout/sidebar.php');
?>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                DATA PENGGUNA
                            </h2>
                        </div>
                        <div class="body">
                            <form class="form-horizontal">
                                <div class="row clearfix">
                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-4 form-control-label">
                                        <label for="username">USERNAME</label>
                                    </div>
                                    <div class="col-lg-10 col-md-10 col-sm-8 col-xs-8">
                                        <div class="form-group">
                                            <div class="form-line">
                                                <input type="text" id="username" name="username" class="form-control" placeholder="Username">
                                            </div>
                                            <div class="m-t-15"><small>Username based on kode dokter and nik pegawai</small></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row clearfix">
                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-4 form-control-label">
                                        <label for="role">ROLE</label>
                                    </div>
                                    <div class="col-lg-10 col-md-10 col-sm-8 col-xs-8">
                                        <div class="form-group">
                                            <div class="form-line">
                                                <input type="text" id="role" name="role" class="form-control" placeholder="Role">
                                            </div>
                                            <div class="m-t-15"><small>Role as Admin, Manajemen, Medis, Paramedis, Paramedis_Ranap, Apotek, Kasir, RekamMedik</small></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row clearfix">
                                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-4 form-control-label">
                                        <label for="cap">CAPABILITY</label>
                                    </div>
                                    <div class="col-lg-10 col-md-10 col-sm-8 col-xs-8">
                                        <div class="form-group">
                                            <div class="form-line">
                                                <input type="text" id="cap" name="cap" class="form-control" placeholder="Capability">
                                            </div>
                                            <div class="m-t-15"><small>Capability based on kode poliklinik and kode bangsal</small></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row clearfix">
                                    <div class="col-lg-offset-2 col-md-offset-2 col-sm-offset-4 col-xs-offset-5">
                                        <button type="button" id="save" class="btn btn-primary m-t-15 waves-effect" onclick="saveData()">Save</button>
                                        <button type="button" id="update" class="btn btn-warning m-t-15 waves-effect" onclick="updateData()">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Striped Rows -->
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                DATA PENGGUNA
                            </h2>
                        </div>
                        <div class="body table-responsive">
                          <table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap" width="100%">
                                <thead>
                                    <tr>
                                        <th>USER NAME</th>
                                        <th>NAMA</th>
                                        <th>ROLE</th>
                                        <th>CAP</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php

                                $sql = query("SELECT * FROM roles ORDER BY username DESC");
        						while($row = fetch_array($sql)){
            						print "<tr>";
            						print "<td>".$row['username']."</td>";
            						$query1 = query("(SELECT nm_dokter AS nama FROM dokter WHERE kd_dokter = '$row[username]') UNION (SELECT nama AS nama FROM pegawai WHERE nik ='$row[username]') UNION (SELECT nama AS nama FROM petugas WHERE nip ='$row[username]')");
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
            						print "<td class='text-center'><div class='btn-group' role='group' aria-label='group-".$row['username']."'>";
            					?>
            						<button onclick="editData('<?php echo $row['username'] ?>','<?php echo $row['role'] ?>','<?php echo $row['cap'] ?>')" class='btn btn-warning'>Edit</button>
            						<button onclick="removeConfirm('<?php echo $row['username'] ?>')" class='btn btn-danger'>Trash</button>
            					<?php
            						print "</div></td>";
            						print "</tr>";
        						}

								?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- #END# Striped Rows -->
        </div>
    </section>

<?php
include_once('layout/footer.php');
?>

    <script>
    $(function() {
      $( "#username" ).autocomplete({
        source: 'includes/username.php',
      });
      $( "#cap" ).autocomplete({
        source: 'includes/capability.php'
      });
    });
    </script>
    <script>
    //$('table').dataTable();
    //viewData();
    $('#update').prop("disabled",true);

    //function viewData(){
    //    $.get('includes/crud-users.php', function(data){
    //        $('tbody').html(data)
    //    })
    //}

    function saveData(){
        var username = $('#username').val()
        var role = $('#role').val()
        var cap = $('#cap').val()
        $.post('includes/crud-users.php?p=add', {username:username, role:role, cap:cap}, function(data){
            viewData()
            $('#username').val(' ')
            $('#role').val(' ')
            $('#cap').val(' ')
        })
    }

    function editData(username, role, cap) {
        $('#username').val(username)
        $('#role').val(role)
        $('#cap').val(cap)
        $('#username').prop("readonly",true);
        $('#save').prop("disabled",true);
        $('#update').prop("disabled",false);
    }

    function updateData(){
        var username = $('#username').val()
        var role = $('#role').val()
        var cap = $('#cap').val()
        $.post('includes/crud-users.php?p=update', {username:username, role:role, cap:cap}, function(data){
            viewData()
            $('#username').val(' ')
            $('#role').val(' ')
            $('#cap').val(' ')
            $('#username').prop("readonly",false);
            $('#save').prop("disabled",false);
            $('#update').prop("disabled",true);
        })
    }

    function deleteData(username){
        $.post('includes/crud-users.php?p=delete', {username:username}, function(data){
            viewData()
        })
    }

    function removeConfirm(username){
        var con = confirm('Are you sure, want to delete this data!');
        if(con=='1'){
            deleteData(username);
        }
    }
    </script>
