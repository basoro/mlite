<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : dentix.id@gmail.com
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
                                <?php echo $title; ?> <div class="right pendaftaran" style="margin-top:-15px;"><button class="btn btn-default waves-effect accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapsePendaftaran"></button></div>
                            </h2>
                        </div>
                        <div class="panel-group" id="accordion">
                          <div class="panel panel-default" style="border: none !important;">
                            <div id="collapsePendaftaran" class="panel-collapse collapse in" style="margin-top:40px;">
                              <div class="panel-body">
                                <form class="form-horizontal">
                                    <div class="row clearfix">
                                        <div class="col-lg-2 col-md-2 col-sm-4 col-xs-4 form-control-label">
                                            <label for="username">USERNAME</label>
                                        </div>
                                        <div class="col-lg-10 col-md-10 col-sm-8 col-xs-8">
                                            <div class="input-group input-group-lg">
                                                <div class="form-line">
                                                    <input type="hidden" id="username" name="username" class="form-control"><input type="text" id="nama" name="nama" class="form-control" placeholder="Nama">
                                                </div>
                                                <span class="input-group-addon">
                                                    <i class="material-icons" data-toggle="modal" data-target="#usersModal">attach_file</i>
                                                </span>
                                            </div>
                                            <div style="margin-top:-25px;"><small>Username based on kode dokter and nik pegawai</small></div>
                                        </div>
                                    </div>
                                    <div class="row clearfix m-t-15">
                                        <div class="col-lg-2 col-md-2 col-sm-4 col-xs-4 form-control-label">
                                            <label for="role">ROLE</label>
                                        </div>
                                        <div class="col-lg-10 col-md-10 col-sm-8 col-xs-8">
                                            <div class="input-group input-group-lg">
                                                <div class="form-line">
                                                    <input type="text" id="role" name="role" class="form-control" placeholder="Role">
                                                </div>
                                                <span class="input-group-addon">
                                                    <i class="material-icons" data-toggle="modal" data-target="#rolesModal">attach_file</i>
                                                </span>
                                            </div>
                                            <div style="margin-top:-25px;"><small>Role as Admin, Manajemen, Medis, Paramedis_Ralan, Paramedis_Ranap, Apotek, Kasir, Rekam_Medis</small></div>
                                        </div>
                                    </div>
                                    <div class="row clearfix m-t-15">
                                        <div class="col-lg-2 col-md-2 col-sm-4 col-xs-4 form-control-label">
                                            <label for="cap">CAPABILITY</label>
                                        </div>
                                        <div class="col-lg-10 col-md-10 col-sm-8 col-xs-8">
                                          <div class="input-group input-group-lg">
                                                <div class="form-line">
                                                    <input type="hidden" id="cap" name="cap" class="form-control"><input type="text" id="capnama" name="capnama" class="form-control" placeholder="Capability">
                                                </div>
                                                <span class="input-group-addon">
                                                    <i class="material-icons" data-toggle="modal" data-target="#capsModal">attach_file</i>
                                                </span>
                                            </div>
                                            <div style="margin-top:-25px;"><small>Capability based on kode poliklinik and kode bangsal</small></div>
                                        </div>
                                    </div>
                                    <div class="row clearfix m-t-15">
                                        <div class="col-lg-2 col-md-2 col-sm-4 col-xs-4 form-control-label">
                                            <label for="cap">MODULE</label>
                                        </div>
                                        <div class="col-lg-10 col-md-10 col-sm-8 col-xs-8">
                                          <div class="input-group input-group-lg">
                                                <div class="form">
                                                  <select id="modulename" name="modulename[]" class="form-control" multiple="multiple">
                                                    <?php
                                                    foreach (glob("modules/*/index.php") as $filename) {
                                                      $filename = str_replace("modules/", "", $filename);
                                                      $filename = str_replace("/index.php", "", $filename);
                                                      echo '<option value="'.$filename.'">'.$filename.'</option>';
                                                    }
                                                    ?>
                                                  </select>
                                                </div>
                                            </div>
                                            <div style="margin-top:5px;"><small>Access to modules</small></div>
                                        </div>
                                    </div>
                                    <div class="row clearfix" style="margin-top:40px;margin-bottom:40px;">
                                        <div class="col-lg-12 text-center">
                                            <button type="button" class="btn btn-lg btn-primary m-t-15 m-l-15 waves-effect" id="simpan">SIMPAN</button>
                                            <button type="button" class="btn btn-lg btn-info m-t-15 m-l-15 waves-effect" id="ganti">GANTI</button>
                                            <button type="button" class="btn btn-lg btn-danger m-t-15 m-l-15 waves-effect" id="hapus">HAPUS</button>
                                        </div>
                                    </div>
                                 </form>
                              </div>
                            </div>
                          </div>
                          <div class="row clearfix">
                              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                  <div class="body">
                                      <table id="datapengguna" class="table table-bordered table-striped table-hover display nowrap" width="100%">
                                          <thead>
                                              <tr>
                                                  <th>NAMA</th>
                                                  <th>USER NAME</th>
                                                  <th>ROLE</th>
                                                  <th>CAP</th>
                                                  <th>MODULE</th>
                                              </tr>
                                          </thead>
                                          <tbody>
                                          <?php
                                          $sql = query("SELECT * FROM roles ORDER BY username DESC");
                                  				while($row = fetch_array($sql)){
                                            $query1 = fetch_assoc(query("(SELECT nm_dokter AS nama FROM dokter WHERE kd_dokter = '$row[username]') UNION (SELECT nama AS nama FROM pegawai WHERE nik ='$row[username]') UNION (SELECT nama AS nama FROM petugas WHERE nip ='$row[username]')"));
                                            $query2 = fetch_assoc(query("(SELECT nm_poli AS nama FROM poliklinik WHERE kd_poli = '$row[cap]') UNION (SELECT nm_bangsal AS nama FROM bangsal WHERE kd_bangsal = '$row[cap]')"));
                                            echo "<tr class='editusername'
                                              data-username='".$row['0']."'
                                              data-nama='".$query1['nama']."'
                                              data-role='".$row['1']."'
                                              data-cap='".$row['2']."'
                                              data-capnama='".$query2['nama']."'
                                              data-modulename='".$row['module']."'
                                            >";
                                    			    echo "<td>".$query1['nama']."</td>";
                                              echo "<td>".$row['username']."</td>";
                                      				echo "<td>".$row['role']."</td>";
                                  				    echo "<td>".$query2['nama']."</td>";
                                              echo "<td>".$row['module']."</td>";
                                      				echo "</tr>";
                                  				}
                          								?>
                                          </tbody>
                                      </table>
                                  </div>
                              </div>
                          </div>
                       </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="usersModal" tabindex="-1" role="dialog" aria-labelledby="usersModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="width:800px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="usersModalLabel">Database User</h4>
                </div>
                <div class="modal-body table-responsive">
                    <table id="users" class="table table-bordered table-striped table-hover display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>USER NAME</th>
                                <th>NAMA</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $sql = query("(SELECT kd_dokter AS username, nm_dokter AS nama FROM dokter) UNION (SELECT nik AS username, nama AS nama FROM pegawai) UNION (SELECT nip AS username, nama AS nama FROM petugas)");
                        while($row = fetch_array($sql)){
                            echo "<tr class='pilihusername'
                              data-username='".$row['0']."'
                              data-nama='".$row['1']."'
                            >";
                            echo "<td>".$row['username']."</td>";
                            echo "<td>".$row['nama']."</td>";
                            echo "</tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rolesModal" tabindex="-1" role="dialog" aria-labelledby="rolesModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="width:800px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="rolesModalLabel">User Role</h4>
                </div>
                <div class="modal-body">
                    <table id="roles" class="table table-bordered table-striped table-hover display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                          <tr class="pilihrole" data-role="Admin">
                            <td>Admin</td>
                          </tr>
                          <tr class="pilihrole" data-role="Manajemen">
                            <td>Manajemen</td>
                          </tr>
                          <tr class="pilihrole" data-role="Medis">
                            <td>Medis</td>
                          </tr>
                          <tr class="pilihrole" data-role="Paramedis_Ralan">
                            <td>Paramedis_Ralan</td>
                          </tr>
                          <tr class="pilihrole" data-role="Paramedis_Ranap">
                            <td>Paramedis_Ranap</td>
                          </tr>
                          <tr class="pilihrole" data-role="Apotek">
                            <td>Apotek</td>
                          </tr>
                          <tr class="pilihrole" data-role="Kasir">
                            <td>Kasir</td>
                          </tr>
                          <tr class="pilihrole" data-role="Rekam_Medis">
                            <td>Rekam_Medis</td>
                          </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="capsModal" tabindex="-1" role="dialog" aria-labelledby="capsModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="width:800px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="capsModalLabel">User Capability</h4>
                </div>
                <div class="modal-body">
                    <table id="capabilities" class="table table-bordered table-striped table-hover display nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                            </tr>
                        </thead>
                        <tbody>
                          <?php
                          $sql = query("(SELECT kd_poli AS kode, nm_poli AS nama FROM poliklinik) UNION (SELECT kd_bangsal AS kode, nm_bangsal AS nama FROM bangsal)");
                          while($row = fetch_array($sql)){
                              echo "<tr class='pilihcap'
                                data-cap='".$row['0']."'
                                data-capnama='".$row['1']."'
                              >";
                              echo "<td>".$row['kode']."</td>";
                              echo "<td>".$row['nama']."</td>";
                              echo "</tr>";
                          }
                          ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


<?php
include_once('layout/footer.php');
?>
<script>
  $('#datapengguna').dataTable( {
        "bInfo" : true,
      	"scrollX": true,
        "processing": true,
        "responsive": true,
        "oLanguage": {
            "sProcessing":   "Sedang memproses...",
            "sLengthMenu":   "Tampilkan _MENU_ entri",
            "sZeroRecords":  "Tidak ditemukan data yang sesuai",
            "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
            "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
            "sInfoPostFix":  "",
            "sSearch":       "Cari:",
            "sUrl":          "",
            "oPaginate": {
                "sFirst":    "«",
                "sPrevious": "‹",
                "sNext":     "›",
                "sLast":     "»"
            }
        },
        "order": [[ 0, "asc" ]]
  } );
  $('#users').dataTable({
    "processing": true,
    "responsive": true,
    "oLanguage": {
        "sProcessing":   "Sedang memproses...",
        "sLengthMenu":   "Tampilkan _MENU_ entri",
        "sZeroRecords":  "Tidak ditemukan data yang sesuai",
        "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
        "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
        "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
        "sInfoPostFix":  "",
        "sSearch":       "Cari:",
        "sUrl":          "",
        "oPaginate": {
            "sFirst":    "«",
            "sPrevious": "‹",
            "sNext":     "›",
            "sLast":     "»"
        }
    },
    "order": [[ 0, "asc" ]]
  });
  $('#roles').dataTable({
    "processing": true,
    "responsive": true,
    "oLanguage": {
        "sProcessing":   "Sedang memproses...",
        "sLengthMenu":   "Tampilkan _MENU_ entri",
        "sZeroRecords":  "Tidak ditemukan data yang sesuai",
        "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
        "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
        "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
        "sInfoPostFix":  "",
        "sSearch":       "Cari:",
        "sUrl":          "",
        "oPaginate": {
            "sFirst":    "«",
            "sPrevious": "‹",
            "sNext":     "›",
            "sLast":     "»"
        }
    },
    "order": [[ 0, "asc" ]]
  });
  $('#capabilities').dataTable({
    "processing": true,
    "responsive": true,
    "oLanguage": {
        "sProcessing":   "Sedang memproses...",
        "sLengthMenu":   "Tampilkan _MENU_ entri",
        "sZeroRecords":  "Tidak ditemukan data yang sesuai",
        "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
        "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
        "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
        "sInfoPostFix":  "",
        "sSearch":       "Cari:",
        "sUrl":          "",
        "oPaginate": {
            "sFirst":    "«",
            "sPrevious": "‹",
            "sNext":     "›",
            "sLast":     "»"
        }
    },
    "order": [[ 0, "asc" ]]
  });
  $(document).on('click', '.pilihusername', function (e) {
      document.getElementById("username").value = $(this).attr('data-username');
      document.getElementById("nama").value = $(this).attr('data-nama');
      $('#usersModal').modal('hide');
  });
  $(document).on('click', '.pilihrole', function (e) {
      document.getElementById("role").value = $(this).attr('data-role');
      $('#rolesModal').modal('hide');
  });
  $(document).on('click', '.pilihcap', function (e) {
      document.getElementById("cap").value = $(this).attr('data-cap');
      document.getElementById("capnama").value = $(this).attr('data-capnama');
      $('#capsModal').modal('hide');
  });
  $(document).on('click', '.editusername', function (e) {
      document.getElementById("username").value = $(this).attr('data-username');
      document.getElementById("nama").value = $(this).attr('data-nama');
      document.getElementById("role").value = $(this).attr('data-role');
      document.getElementById("cap").value = $(this).attr('data-cap');
      document.getElementById("capnama").value = $(this).attr('data-capnama');
  });
  $("#simpan").click(function(){
      var username = document.getElementById("username").value;
      var role = document.getElementById("role").value;
      var cap = document.getElementById("cap").value;
      var modulename = $("#modulename").val();
      $.ajax({
          url:'includes/pengguna.php?p=add',
          method:'POST',
          data:{
              username:username,
              role:role,
              cap:cap,
              modulename:modulename
          },
         success:function(data){
             window.location.reload(true)
         }
      });
  });
  $("#ganti").click(function(){
      var username = document.getElementById("username").value;
      var role = document.getElementById("role").value;
      var cap = document.getElementById("cap").value;
      var modulename = $("#modulename").val();
      $.ajax({
          url:'includes/pengguna.php?p=update',
          method:'POST',
          data:{
              username:username,
              role:role,
              cap:cap,
              modulename:modulename
          },
         success:function(data){
             window.location.reload(true)
         }
      });
  });
  $("#hapus").click(function(){
      var username = document.getElementById("username").value;
      $.ajax({
          url:'includes/pengguna.php?p=delete',
          method:'POST',
          data:{
              username:username,
          },
         success:function(data){
             window.location.reload(true)
         }
    });
  });
  $("#modulename").select2({
      multiple: true
  });
</script>
