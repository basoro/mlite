<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Pasien Rawat Inap';
include_once('config.php');
include_once('layout/header.php');
include_once('layout/sidebar.php');  

if($_SERVER['REQUEST_METHOD'] == "POST") {
	if ($_POST['stts_pindah'] == "2"){
			$no_rawat	 = $_POST['no_rawat'];
			$kd_kamar 	 = $_POST['kamar'];
			$kd_kmr_sbl	 = $_POST['kd_kamar_sebelumnya'];
			
			$sql_kamar   = "SELECT trf_kamar FROM kamar WHERE kd_kamar ='{$_POST['kamar']}'";
			$query		 =query($sql_kamar);
			$data		 =fetch_assoc($query);
			
			$tarif_kamar = $data['trf_kamar'];
			$tgl_masuk 	 = $_POST['tgl_masuk'];
			$jam		 = $_POST['jam_masuk'];
			
			$update = query("UPDATE kamar_inap SET kd_kamar = '".$_POST['kamar']."', trf_kamar ='".$data['trf_kamar']."', tgl_masuk ='".$_POST['tgl_masuk']."', lama = '1', ttl_biaya = '".$data['trf_kamar']."' WHERE no_rawat = '".$_POST['no_rawat']."' AND stts_pulang ='-' ");							
			$update_status_kamarbr = query("UPDATE kamar SET status = 'ISI' WHERE kd_kamar='$kd_kamar'");
			$update_status_kamarsbl = query("UPDATE kamar SET status = 'KOSONG' WHERE kd_kamar='$kd_kmr_sbl'");
			
			/**if($update){
				echo "berhasil";
			}else{
				echo "gagal";
				} **/
	} else {
			$no_rawat	 = $_POST['no_rawat'];
			$kd_kamar 	 = $_POST['kamar'];
			$kd_kmr_sbl	 = $_POST['kd_kamar_sebelumnya'];
			
			$sql_kamar   = "SELECT trf_kamar FROM kamar WHERE kd_kamar ='{$_POST['kamar']}'";
			$query		 =query($sql_kamar);
			$data		 =fetch_assoc($query);
			
			$tarif_kamar = $data['trf_kamar'];
			
			$tgl_masuk 	 = $_POST['tgl_masuk'];
			$jam		 = $_POST['jam_masuk'];
			
			$tambah = query("INSERT INTO kamar_inap VALUES ('$no_rawat','$kd_kamar','$tarif_kamar','{$_POST['diagnosa_awal']}','-','$tgl_masuk','$jam','0000-00-00','00:00:00','1','$tarif_kamar','-')");
			$update_status_kamarbr = query("UPDATE kamar SET status = 'ISI' WHERE kd_kamar='$kd_kamar'");
		
			$tgl_masuk2       = new DateTime($tgl_masuk);
			
			$sql_kamar_2     = "SELECT tgl_masuk,trf_kamar FROM kamar_inap WHERE kd_kamar ='{$_POST['kd_kamar_sebelumnya']}' AND no_rawat ='{$_POST['no_rawat']}'";
			$query2		     =query($sql_kamar_2);
			$data		     =fetch_assoc($query2);
			$data_tgl 		 = $data['tgl_masuk'];
			$tgl_keluar			= new DateTime($data_tgl); 
			$tgl_keluar_post 	= $tgl_masuk2->diff($tgl_keluar);
			$ttl_biaya 			= $tgl_keluar_post->days * $tarif_kamar;
			$tgl_keluar_bujur 	= $tgl_keluar->format('Y-m-d');
			
			$update_kamar_sebelumnya = query("UPDATE kamar_inap SET stts_pulang = 'Pindah Kamar', tgl_keluar = '$tgl_masuk',jam_keluar='$jam',lama = '".$tgl_keluar_post->d."', ttl_biaya = '$ttl_biaya' WHERE no_rawat = '".$_POST['no_rawat']."' AND kd_kamar='$kd_kmr_sbl' ");							
			$update_status_kamarsbl = query("UPDATE kamar SET status = 'KOSONG' WHERE kd_kamar='$kd_kmr_sbl'");
		
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
                                Pindah Pasien Ranap
                            </h2>
                        </div>
                        <div class="body">

				<?php
					$action = isset($_GET['action'])?$_GET['action']:null;
                    if(!$action){
						
						echo "Silahkan Pilih Dulu Pasiennya";	
						
						}
				
					if ($action == "pindah") {
					$nomor_rawat  = $_GET['no_rawat'];	
					?>		
						   <div class="row">
							   <div class="body">
                         		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                           		<div class="card">
								  <div class="card-body" style="padding-top:15px;padding-left:15px;">
								   <h5><?php echo $_GET['nm_pasien'];?></h5>
								   <?php 
								     $sql ="SELECT 
									a.no_rawat, 
									b.no_rkm_medis,
									c.nm_pasien,
									a.tgl_masuk,
									a.tgl_keluar,
									a.kd_kamar,
									d.kd_bangsal,
									e.nm_bangsal,
									a.stts_pulang
								  FROM 
									kamar_inap as a,
									reg_periksa as b,
									pasien as c,
									kamar as d,
									bangsal as e
								  WHERE
									a.no_rawat = b.no_rawat
									AND
									b.no_rkm_medis = c.no_rkm_medis
									AND
									a.kd_kamar=d.kd_kamar
									AND
									d.kd_bangsal=e.kd_bangsal
									AND
									a.no_rawat ='$nomor_rawat'
									";
							
									$query=query($sql);
								    $no=1;
									while($data=$query->fetch_assoc()){  ?>
									  <?php echo $no++;?>.
									  <?php echo $data['nm_bangsal'];?> 
										&nbsp;&nbsp;/&nbsp;&nbsp;<?php echo $data['kd_kamar'];?> 
										&nbsp;&nbsp;/&nbsp;&nbsp;<?php echo $data['tgl_masuk'];?> 
										&nbsp;&nbsp;/&nbsp;&nbsp;<?php if ($data['stts_pulang'] == "-"){echo "Belum Pulang";} else {echo $data['stts_pulang'];};?> 
								        <hr>
								      <?php }
								      $no_rkm_medis=$data['no_rkm_medis'];
								      ?>
								  </div> 
								  </div>
								</div>
								<form method="post" action="" style ="margin-left:5px;margin-right:5px;">
									<div class="col-sm-3">
										<div class="form-group">
											<div class="form-line">
												<input type="text" class="form-control" name="no_rawat" value="<?php echo $_GET['no_rawat'];?>">
												<label class="form-label">Nomor Rawat</label>
											</div>
										</div>
									</div>
									<div class="col-sm-2">
										<div class="form-group">
											<div class="form-line">
												<input type="text" class="form-control" name="no_rkm_medis" value="<?php echo $_GET['no_rkm_medis'];?>">
												<label class="form-label">Nomor Rekam Medik</label>
											</div>
										</div>
									</div>
									<div class="col-sm-2">
										<div class="form-group">
											<div class="form-line">
												<input type="text" class="form-control" name="kd_kamar_sebelumnya" value="<?php echo $_GET['kd_kmr_sblmny'];?>">
												<label class="form-label">Kd Kamar Sebelumnya</label>
											</div>
										</div>
									</div>
									<div class="col-sm-5">
										<div class="form-group">
											<div class="form-line">
												<input type="text" class="form-control" name="nm_pasien" value="<?php echo $_GET['nm_pasien'];?>">
												<label class="form-label">Nama Pasien</label>
											</div>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<div class="form-line">
												<label class="form-label">Tanggal Masuk</label>
												<input type="text" name="tgl_masuk" class="datepicker form-control" value="<?php echo date('Y-m-d');?>">
											</div>
										</div>
									</div>
									<div class="col-sm-2">
										<div class="form-group">
											<div class="form-line">
												<label class="form-label">Jam Masuk</label>
												<input type="text" name="jam_masuk" class="form-control" value="<?php echo date('H:m:s');?>">
											</div>
										</div>
									</div>
									<div class="col-sm-2">
										<div class="form-group">
											<div class="form-line">
												<label class="form-label">Diagnosa Awal</label>
												<input type="text" name="diagnosa_awal" class="form-control" value="<?php 
														$sql_kamar   = "SELECT diagnosa_awal FROM kamar_inap WHERE no_rawat ='{$_GET['no_rawat']}'";
														$query		 =query($sql_kamar);
														$data		 =fetch_assoc($query);
														echo $data['diagnosa_awal'];?> ">
											</div>
										</div>
									</div>
									<div class="col-sm-5">
										<div class="form-group">
											<div class="form-line">
												<select name="stts_pindah" style="width:100%" class="form-control" id="stts_pindah">
													<option value="2" selected="selected" >2. Kamar sebelumnya diganti dengan kamar yang baru</option>	
													<option value="3" >3. Kamar sebelumnya distatus pindah</option>
												</select>
											</div>
										</div>
									</div>
									<div class="form-group" style="width:97%; margin-left:15px;">
                                      <select name="kamar" class="form-control kamar" id="kamar" style="width:100%"></select>
                                       <!--   <br/>
										<input type="hidden" class="form-control" id="kamar" name="kamar"/> -->
                                    </div>	
                                 	<button type="submit" class="form-control btn bg-indigo waves-effect tombol-simpan" name="simpan">Simpan</button>
                                </form>
						        </div> 
						  </div>	
               <?php 
               
               }   
              
                ?>
                    </div>
                 </div>
            </div>
        </div>
    </section>


<?php
include_once('layout/footer.php');
?>

    <script type="text/javascript">

         function formatData2 (data) {
            var $data = $(
                '<b>'+ data.id +'</b>-'+ data.text +'-'+ data.text2 +'-<i>'+ data.text3 +'</i>'
            );
            return $data;
        };

        //tessssssssssss
       $('.kamar').select2({
          placeholder: 'Pilih kamar',
          ajax: {
            url: 'includes/select-pindah-kamar.php',
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
              return {
                results: data
              };
            },
            cache: true
          },
          templateResult: formatData2,
          minimumInputLength: 3
        });
      
     </script>
