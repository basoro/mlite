<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Kesling';
include_once('config.php');
include_once('layout/header.php');
include_once('layout/sidebar.php');
if($_SERVER['REQUEST_METHOD'] == "POST") {
	$kd_bangsal = $_POST['ruangan'];
	$keterangan = $_POST['keterangan'];
	$nip 		= $_POST['nip'];
	if($_FILES['fileToUpload']['name']!='') {
        $tmp_name = $_FILES["fileToUpload"]["tmp_name"];
        $namefile = $_FILES["fileToUpload"]["name"];
        $ext = end(explode(".", $namefile));
        $image_name = "kesling-".time().".".$ext;
        move_uploaded_file($tmp_name,"images/UploadKesling/".$image_name);
        $lokasi_berkas = 'images/UploadKesling/'.$image_name;
        }
	$insert=query("INSERT INTO surveilans (nip,kd_bangsal,keterangan,foto) VALUES ('$nip','$kd_bangsal','$keterangan','$lokasi_berkas')");
	if($insert){
  	redirect('kesling.php');
  }
}
?>
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                SURVEILANS <a href='kesling.php?action=add' class='btn btn-primary pull-right'>Tambah Data</a>
                            </h2>
                        </div>
                        <div class="body">
						<?php
                        $action = isset($_GET['action'])?$_GET['action']:null;
                        if(!$action){
							?>
							
							<table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
								<thead>
								<tr>
									<th>ID</th>
									<th>NIP</th>
									<th>USER</th>
									<th>RUANGAN</th>
									<th>KETERANGAN</th>
									<th>FOTO</th>
								</tr>
								</thead>
								<tbody>
								<?php 
									$sql="SELECT 
										    a.id, 
										    a.nip,
											c.nama,
											b.nm_bangsal,
											a.keterangan,
											a.foto
											from 
											surveilans as a,
											bangsal as b,
											petugas as c
											where 
											a.kd_bangsal = b.kd_bangsal
											and
											a.nip = c.nip
											";
									$query=query($sql);
									while($baranghaja=$query->fetch_assoc()){
										echo "
										<tr>
										 <td>".$baranghaja['id']."</td>
										 <td>".$baranghaja['nip']."</td>
										 <td>".$baranghaja['nama']."</td>
										 <td>".$baranghaja['nm_bangsal']."</td>
										 <td>".$baranghaja['keterangan']."</td>
										 <td>".$baranghaja['foto']."</td>
										 </tr>";
									}
								?>
								</tbody>
							</table>
							
						<?php
                        } 
						if ($action == "view"){
							echo "ini viewnya";
						}
						if ($action == "add"){
						?>
							<form method="post" action="" enctype="multipart/form-data">
									<div class="form-group form-float">
                                    	<div class="form-line">
                                        	<input type="text" class="form-control" name="nip" value="<?php echo $_SESSION['username'];?>">
                                            <!-- <label class="form-label">Upload Foto</label> -->
                                        </div>
                                    </div>						   
									<div class="form-group">
                                    	<div class="form-line"> 
										<select name="ruangan" style="width:100%" class="form-control kd_tdk">
									    <option value="" selected="selected" >Pilih Ruangan</option>	
										<?php
										$query = "SELECT * FROM bangsal where kd_bangsal !='-' and kd_bangsal !='B0009' and kd_bangsal !='B0013' and kd_bangsal !='B0015'";
										$hasil = query($query);
									    while ($data = mysqli_fetch_array($hasil)){
											echo "<option value='".$data['kd_bangsal']."'>".$data['nm_bangsal']."</option>";
										}
										?>		
										</select>
                                        <label class="form-label">Ruangan</label>  
                                	    </div>
                                    </div>
									
                             		<div class="form-group form-float">
                                    	<div class="form-line">
                                        	<textarea class="form-control" name="keterangan"></textarea>
                                            <label class="form-label">Keterangan</label>
                                        </div>
                                    </div>
                             		<div class="form-group form-float">
                                    	<div class="form-line">
                                        	<input type="file" class="form-control" name="fileToUpload" id="fileToUpload">
                                            <!-- <label class="form-label">Upload Foto</label> -->
                                        </div>
                                    </div>
                                 	<button type="submit" class="form-control btn bg-indigo waves-effect tombol-simpan" name="simpan-sur">Simpan</button>
                                </form>
						<?php
						}
						?>  	
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
include_once('layout/footer.php');
?>
