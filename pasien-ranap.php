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
if(isset($_GET['no_rawat'])) {
    $_sql = "SELECT a.no_rkm_medis, a.no_rawat, b.nm_pasien, b.umur FROM reg_periksa a, pasien b WHERE a.no_rkm_medis = b.no_rkm_medis AND a.no_rawat = '$_GET[no_rawat]'";
    $found_pasien = query($_sql);
    if(num_rows($found_pasien) == 1) {
	     while($row = fetch_array($found_pasien)) {
	        $no_rkm_medis  = $row['0'];
	        $get_no_rawat	     = $row['1'];
          $no_rawat	     = $row['1'];
	        $nm_pasien     = $row['2'];
	        $umur          = $row['3'];
	     }
    } else {
	     redirect ('pasien-ranap.php');
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
                                INFORMASI KAMAR <?php if($role == 'Paramedis_Ranap') { echo $dataGetBangsal['nm_bangsal']; } else { echo 'RANAP'; } ?>
                              	<?php if($role == 'Admin'){ echo "<a href='/inlcudes/setkmr.php' class='btn btn-primary'>Edit Kamar</a>";}?>
                            </h2>
                        </div>
                        <?php
                        $action = isset($_GET['action'])?$_GET['action']:null;
                        if(!$action){
                        ?>
                        <div class="body">
                            <table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th width = "1%">No<br>MR</th>
                                        <th>Kamar</th>
                                        <th>Bed</th>
                                        <th width = "10px">Tanggal<br>Masuk</th>
                                        <th width = "10px">Cara<br>Bayar</th>
                                      	<th>DPJP</th>
                                     </tr>
                                </thead>
                                <tbody>
                                <!-- This query based on Adly's (Adly Hidayat S.KOM) query. Thanks bro -->
                                <?php
                                $sql = "
                                	SELECT
                                		pasien.nm_pasien,
                                    	reg_periksa.no_rkm_medis,
                                    	bangsal.nm_bangsal,
                                    	kamar_inap.kd_kamar,
                                    	kamar_inap.tgl_masuk,
                                    	penjab.png_jawab,
                                    	reg_periksa.no_rawat
                                    FROM
                                    	kamar_inap,
                                        reg_periksa,
                                        pasien,
                                        bangsal,
                                        kamar,
                                        penjab
                                    WHERE
                                    	kamar_inap.no_rawat = reg_periksa.no_rawat
                                    AND
                                    	reg_periksa.no_rkm_medis = pasien.no_rkm_medis
                                    AND
                                    	kamar_inap.kd_kamar = kamar.kd_kamar
                                    AND
                                    	kamar.kd_bangsal = bangsal.kd_bangsal
                                    AND
                                    	kamar_inap.stts_pulang = '-'
                                    AND
                                    	reg_periksa.kd_pj = penjab.kd_pj

                                ";
                                if($role == 'Paramedis_Ranap') {
                                	$sql .= " AND bangsal.kd_bangsal = '$jenis_poli'";
                                }
                                $sql .= " ORDER BY kamar_inap.kd_kamar ASC";
                                $result = query($sql);
                                while($row = fetch_array($result)) {
                                  $get_no_rawat = $row['6'];
                                ?>
                                    <tr>
                                        <td><?php echo SUBSTR($row['0'], 0, 15).' ...'; ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-info waves-effect dropdown-toggle" data-toggle="dropdown" data-disabled="true" aria-expanded="true"><?php echo $row['1']; ?> <span class="caret"></span></button>
                                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                    <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=tindakan&no_rawat=<?php echo $row['6']; ?>&bed=<?php echo $row['3']; ?>">Assesment & Tindakan</a></li>
                                                    <li><a href="berkas-digital/berkas-digital-ranap.php?no_rawat=<?php echo $row['6']; ?>">Berkas Digital Perawatan</a></li>
                                                    <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=radiologi&no_rawat=<?php echo $row['6']; ?>">Berkas Radiologi</a></li>
                                                    <li><a href="includes/editsttspulang.php?no_rawat=<?php echo $row['6']; ?>&bed=<?php echo $row['3']?>">Status Pulang</a></li>
                                              		<li><a href="pindah-kamar-pasien2.php?action=pindah&no_rawat=<?php echo $row['6'];?>&nm_pasien=<?php echo $row['nm_pasien'];?>&no_rkm_medis=<?php echo $row['no_rkm_medis'];?>&kd_kmr_sblmny=<?php echo $row['3'];?>">Pindah Kamar</a></li>
                                              </ul>
                                            </div>
                                        </td>
                                        <td><?php echo $row['2']; ?></td>
                                        <td><?php echo $row['3']; ?></td>
                                        <td><?php echo $row['4']; ?></td>
                                        <td><?php echo $row['5']; ?></td>
                                      	<td><?php $dpjp = query("SELECT dokter.nm_dokter FROM dpjp_ranap , dokter WHERE dpjp_ranap.kd_dokter = dokter.kd_dokter AND dpjp_ranap.no_rawat = '".$row['6']."'");$dpjpp = fetch_array($dpjp);echo $dpjpp['0'];?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <?php } ?>

                    <?php
                    if($action == "radiologi"){
                        		if (isset($_POST['ok_radiologi'])) {
                              $periksa_radiologi = fetch_assoc(query("SELECT tgl_periksa, jam FROM periksa_radiologi WHERE no_rawat = '{$no_rawat}'"));
                              $date = $periksa_radiologi['tgl_periksa'];
                              $time = $periksa_radiologi['jam'];
                //$photo_berkas=fetch_array(query("SELECT lokasi_file FROM berkas_digital_perawatan WHERE kode = '{$kode_berkas}' AND no_rawat='{$no_rawat}'"));
    			//$kode_berkas = $_POST['kode'];
                if($_FILES['file']['name']!='') {
                //$file='../webapps/berkasrawat/'.$photo_berkas;
                //@unlink($file);
                $tmp_name = $_FILES["file"]["tmp_name"];
                $namefile = $_FILES["file"]["name"];
                $explode = explode(".", $namefile);
                $ext = end($explode);
                $image_name = "berkasradiologi-".time().".".$ext;
                move_uploaded_file($tmp_name,"../radiologi/pages/upload/".$image_name);
                $lokasi_berkas = 'pages/upload/'.$image_name;
    		    $insert_berkas = query("INSERT INTO gambar_radiologi VALUES('$no_rawat', '$date', '$time', '$lokasi_berkas')");
    		    if($insert_berkas) {
                set_message('Berkas digital radiologi telah ditersimpan.');
    		        redirect("pasien-ralan.php?action=radiologi&no_rawat=$no_rawat");
    	    	}


                }


                                }
    				?>
    		            <div class="row">
    		            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <div class="body">
                          <dl class="dl-horizontal">
                              <dt>Nama Lengkap</dt>
                              <dd><?php echo $nm_pasien; ?></dd>
                              <dt>No. RM</dt>
                              <dd><?php echo $no_rkm_medis; ?></dd>
                              <dt>No. Rawat</dt>
                              <dd><?php echo $no_rawat; ?></dd>
                              <dt>Umur</dt>
                              <dd><?php echo $umur; ?></dd>
                          </dl>
              <hr>
                                      <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                                      <?php
                                      $sql_rad = query("select * from gambar_radiologi where no_rawat= '{$_GET['no_rawat']}'");
                                      $no=1;
                                      while ($row_rad = fetch_array($sql_rad)) {
                                          echo '<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">';
                                          echo '<a href="'.URLSIMRS.'/radiologi/'.$row_rad[3].'" data-sub-html=""><img class="img-responsive thumbnail"  src="'.URLSIMRS.'/radiologi/'.$row_rad[3].'"></a>';
                                          echo '</div>';
                                          $no++;
                                      }
                                      ?>

                                    </div>
                      <hr>
                      </div>

                            <div class="body">

                                <form id="form_validation" name="pilihan" action="" method="POST"  enctype="multipart/form-data">
                                    <label for="email_address">Unggah Berkas Radiologi</label>
                                    <div class="form-group">
                                          <img id="image_upload_preview" width="200px" src="assets/images/upload_berkas.png" onclick="upload_berkas()" style="cursor:pointer;" />
                                              		<br/>
                                                    <input name="file" id="inputFile" type="file" style="display:none;"/>
                                    </div>
                                    <button type="submit" name="ok_radiologi" value="ok_radiologi" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_radiologi\'">UPLOAD BERKAS</button>
                                </form>
                          </div>

                          </div>
                      </div>
                    <?php } ?>
                    <?php
                    if($action == "tindakan"){
                      if (isset($_POST['ok_tdk'])) {
                                    if (($_POST['kd_tdk'] <> "") and ($no_rawat <> "")) {
                                          $insert = query("INSERT INTO rawat_inap_pr VALUES ('{$no_rawat}','{$_POST['kd_tdk']}','{$_SESSION['username']}','$date','$time','0','0','{$_POST['kdtdk']}','0','0','{$_POST['kdtdk']}')");
                                          if ($insert) {
                                              redirect("pasien-ranap.php?action=tindakan&no_rawat={$no_rawat}#data");
                                          };
                                    };
                              };
                      if (isset($_POST['ok_per'])){
                            if(($no_rawat <> "")){
                              $insert2 = query("INSERT INTO pemeriksaan_ranap VALUES ('{$no_rawat}','{$date}','{$time}','{$_POST['suhu']}','{$_POST['tensi']}','{$_POST['nadi']}','{$_POST['respirasi']}','{$_POST['tinggi']}','{$_POST['berat']}'
                                          ,'{$_POST['gcs']}','{$_POST['keluhan']}','{$_POST['pemeriksaan']}','{$_POST['alergi']}','-','-')");
                              if($insert2){
                                redirect("pasien-ranap.php?action=tindakan&no_rawat={$no_rawat}#datapem");
                              };
                            };
                          };
                      ?>
                       <div class="row">
                         <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                           <div class="body">
                               <dl class="dl-horizontal">
                                   <dt>Nama Lengkap</dt>
                                   <dd><?php echo $nm_pasien; ?></dd>
                                   <dt>No. RM</dt>
                                   <dd><?php echo $no_rkm_medis; ?></dd>
                                   <dt>No. Rawat</dt>
                                   <dd><?php echo $no_rawat; ?></dd>
                                   <dt>Umur</dt>
                                   <dd><?php echo $umur; ?></dd>
                               </dl>
                               <ul class="nav nav-tabs tab-nav-right" role="tablist">
                                 <li role="presentation" class="active"><a href="includes/tindakan-ranap.php#datapem" data-toggle="tab">PEMERIKSAAN</a></li>
                                 <li role="presentation"><a href="includes/tindakan-ranap.php#data" data-toggle="tab">TINDAKAN</a></li>
                                 <li role="presentation"><a href="includes/tindakan-ranap.php#dpjp" data-toggle="tab">DPJP</a></li>
                                 <li role="presentation"><a href="includes/tindakan-ranap.php#hais" data-toggle="tab">HAIs</a></li>
                               </ul>
                             <?php include_once "includes/tindakan-ranap.php";?>
                           </div>
                         </div>
                      </div>

                      <?php } ?>
                <?php
                //delete
                if($action == "delete_diagnosa"){
                      $hapus = "DELETE FROM diagnosa_pasien WHERE no_rawat='{$_REQUEST['no_rawat']}' AND kd_penyakit = '{$_REQUEST['kode']}' AND prioritas = '{$_REQUEST['prioritas']}'";
                      $hasil = query($hapus);
                      if (($hasil)) {
                          redirect("pasien-ranap.php?action=view&no_rawat={$no_rawat}");
                      }
                }

                //delete
                if($action == "delete_obat"){
                      $hapus = "DELETE FROM resep_dokter WHERE no_resep='{$_REQUEST['no_resep']}' AND kode_brng='{$_REQUEST['kode_obat']}'";
                      $hasil = query($hapus);
                      if (($hasil)) {
                      redirect("pasien-ranap.php?action=view&no_rawat={$no_rawat}");
                      }
                }
                  if ($action == "delete_tindakan") {
                  $hapus = "DELETE FROM rawat_inap_pr WHERE kd_jenis_prw='{$_REQUEST['kd_jenis_prw']}' AND no_rawat='{$_REQUEST['no_rawat']}'";
                  $hasil = query($hapus);
                  if (($hasil)) {
                    redirect("pasien-ranap.php?action=tindakan&no_rawat={$no_rawat}");
                  }
                }
                  if ($action == "delete_pemeriksaan") {
                  $hapus = "DELETE FROM pemeriksaan_ranap WHERE keluhan='{$_REQUEST['keluhan']}' AND no_rawat='{$_REQUEST['no_rawat']}'";
                  $hasil = query($hapus);
                  if (($hasil)) {
                    redirect("pasien-ranap.php?action=tindakan&no_rawat={$no_rawat}");
                  }
                }
                if ($action == "delete_hais") {
                    $hapus = "DELETE FROM data_HAIs WHERE tanggal='{$_REQUEST['tanggal']}' AND no_rawat='{$_REQUEST['no_rawat']}'";
                    $hasil = query($hapus);
                    if (($hasil)) {
                      redirect("pasien-ranap.php?action=tindakan&no_rawat={$no_rawat}&bed={$row['3']}");
                    }
                  }
                  if ($action == "delete_dpjp") {
                  $hapus = "DELETE FROM dpjp_ranap WHERE no_rawat='{$_REQUEST['no_rawat']}' AND kd_dokter='{$_REQUEST['kd_dokter']}'";
                  $hasil = query($hapus);
                  if ($hasil) {
                    redirect("pasien-ranap.php?action=tindakan&no_rawat={$no_rawat}#dpjp");
                  }
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

        function formatData (data) {
            var $data = $(
                '<b>'+ data.id +'</b> - <i>'+ data.text +'</i>'
            );
            return $data;
        };

      	function formatInputData (data) {
              var $data = $(
                  '<b>('+ data.id +')</b> Rp '+ data.tarif +' - <i>'+ data.text +'</i>'
              );
              return $data;
          };


        function formatDataTEXT (data) {
            var $data = $(
                '<b>'+ data.text +'</b>'
            );
            return $data;
        };

        $('.kd_diagnosa').select2({
            placeholder: 'Pilih diagnosa',
            ajax: {
                url: 'includes/select-diagnosa.php',
                dataType: 'json',
                delay: 250,
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                cache: true
            },
            templateResult: formatData,
            minimumInputLength: 3
        });

        $('.prioritas').select2({
            placeholder: 'Pilih prioritas diagnosa'
        });

        $('.kd_obat').select2({
          placeholder: 'Pilih obat',
          ajax: {
            url: 'includes/select-obat.php',
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
              return {
                results: data
              };
            },
            cache: true
          },
          templateResult: formatData,
      	minimumInputLength: 3
        });

        $('.aturan_pakai').select2({
            placeholder: 'Pilih aturan pakai'
        });

        $('.pasien').select2({
          placeholder: 'Pilih nama/no.RM pasien',
          ajax: {
            url: 'includes/select-pasien.php',
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
              return {
                results: data
              };
            },
            cache: true
          },
          templateResult: formatData,
          minimumInputLength: 3
        });

      $('.kd_tdk').select2({
          placeholder: 'Pilih tindakan',
          ajax: {
            url: 'includes/select-tindakan-ranap.php',
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
              return {
                results: data
              };
            },
            cache: true
          },
          templateResult: formatInputData,
      	minimumInputLength: 3
        });

        $('.kd_tdk').on('change', function () {
         var kode = $("#kd_tdk").val();
         $.ajax({
         	url: 'includes/biayaranap.php',
         	data: "kode="+kode,
         }).success(function (data){
           var json = data,
               obj = JSON.parse(json);
           		$('#kdtdk').val(obj.tarif);
           });
        });

        $(function () {
             $('#row_dim').hide();
             $('#lainnya').change(function () {
                 $('#row_dim').hide();
                 if (this.options[this.selectedIndex].value == 'lainnya') {
                     $('#row_dim').show();
                 }
             });
         });

    </script>
	 <script>

        $(document).ready(function() {

            //var url = window.location.pathname; //sets the variable "url" to the pathname of the current window
            //var activePage = url.substring(url.lastIndexOf('/') + 1); //sets the variable "activePage" as the substring after the last "/" in the "url" variable
            //if($('.active').length > 0){
            //   $('.active').removeClass('active');//remove current active element if there's
            //}

            //$('.menu li a').each(function () { //looks in each link item within the primary-nav list
            //    var linkPage = this.href.substring(this.href.lastIndexOf('/') + 1); //sets the variable "linkPage" as the substring of the url path in each &lt;a&gt;

            //    if (activePage == linkPage) { //compares the path of the current window to the path of the linked page in the nav item
            //        $(this).parent().addClass('active'); //if the above is true, add the "active" class to the parent of the &lt;a&gt; which is the &lt;li&gt; in the nav list
            //    }
            //});



            $('#riwayatmedis').dataTable( {
	          	responsive: true
				/*
                "responsive": {
                   "details": {
                       "display": $.fn.dataTable.Responsive.display.modal( {
                            "header": function ( row ) {
                                var data = row.data();
                                return '<h3>Riwayat Medis</h3><br>';
                            }
                        } ),
                        "renderer": $.fn.dataTable.Responsive.renderer.tableAll()
                    }
                }
                */
            } );


            $('#datatable_ralan').dataTable( {
	          	responsive: true,
				order: [[ 2, 'asc' ]]
            } );
            $('#datatable_ranap').dataTable( {
	          	responsive: true,
				order: [[ 4, 'asc' ]]
            } );
            $('#datatable_booking').dataTable( {
	          	responsive: true,
				order: [[ 1, 'asc' ]]
            } );

        } );
      	 $('.kd_jenis_prw_lab').select2({
            placeholder: 'Pilih Jenis',
            ajax: {
                url: '/includes/select-laboratorium.php',
                dataType: 'json',
                delay: 250,
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                cache: true
            },
            templateResult: formatData,
            minimumInputLength: 3
        });

        $('.kd_jenis_prw_rad').select2({
            placeholder: 'Pilih Jenis',
            ajax: {
                url: '/includes/select-radiology.php',
                dataType: 'json',
                delay: 250,
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                cache: true
            },
            templateResult: formatData,
            minimumInputLength: 3
        });

       $('.dpjp').select2({
            placeholder: 'Pilih Dokter',
            ajax: {
                url: 'includes/select-dokter.php',
                dataType: 'json',
                delay: 250,
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                cache: true
            },
            templateResult: formatData,
            minimumInputLength: 3
        });
	</script>
