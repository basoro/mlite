<?php

/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Pendaftaran Pasien';
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
	     redirect ('pendaftaran.php');
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
                                PENDAFTARAN PASIEN
                                <small><?php if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) { echo "Periode ".date("d-m-Y",strtotime($_POST['tgl_awal']))." s/d ".date("d-m-Y",strtotime($_POST['tgl_akhir'])); } ?></small>
                            </h2>
                        </div>
                        <?php
                        $action = isset($_GET['action'])?$_GET['action']:null;
                        if(!$action){
                        ?>
                        <div class="body table-responsive">
                            <div id="buttons" class="align-center m-l-10 m-b-15 export-hidden"></div>
                            <table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Nama Pasien</th>
                                        <th>No. RM</th>
                                        <th>No. Reg</th>
                                        <th>Tgl. Reg</th>
                                        <th>Jam Reg</th>
                                        <th>Alamat</th>
                                        <th>Jenis Bayar</th>
                                        <th>Poliklinik</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $sql = "SELECT a.nm_pasien, b.no_rkm_medis, a.alamat, c.png_jawab, d.nm_poli, b.no_rawat, b.no_reg, b.tgl_registrasi, b.jam_reg FROM pasien a, reg_periksa b, penjab c, poliklinik d WHERE a.no_rkm_medis = b.no_rkm_medis AND b.kd_pj = c.kd_pj AND b.kd_poli = d.kd_poli";
                                if($role == 'Medis' || $role == 'Paramedis') {
                                  $sql .= " AND b.kd_poli = '$jenis_poli'";
                                }
                                if(isset($_POST['tgl_awal']) && isset($_POST['tgl_akhir'])) {
                                	$sql .= " AND b.tgl_registrasi BETWEEN '$_POST[tgl_awal]' AND '$_POST[tgl_akhir]'";
                                } else {
                                  	$sql .= " AND b.tgl_registrasi = '$date'";
                                }
                                $query = query($sql);
                                while($row = fetch_array($query)) {
                                ?>
                                    <tr>
                                        <td><?php echo SUBSTR($row['0'], 0, 15).' ...'; ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-secondary waves-effect dropdown-toggle" data-toggle="dropdown" data-disabled="true" aria-expanded="true"><?php echo $row['1']; ?> <span class="caret"></span></button>
                                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                    <li><a href="javascript:void(0);">Assesment Awal</a></li>
                                                    <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=view&no_rawat=<?php echo $row['5']; ?>">e-Dokter</a></li>
                                                    <li><a href="javascript:void(0);">Input Tindakan</a></li>
                                                    <li><a href="javascript:void(0);">Input Obat</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#statuspulang">Set Status</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                        <td><?php echo $row['6']; ?></td>
                                        <td><?php echo $row['7']; ?></td>
                                        <td><?php echo $row['8']; ?></td>
                                        <td><?php echo $row['2']; ?></td>
                                        <td><?php echo $row['3']; ?></td>
                                        <td><?php echo $row['4']; ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                                </tbody>
                            </table>
                            <div class="row clearfix">
                                <form method="post" action="">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <input type="text" name="tgl_awal" class="datepicker form-control" placeholder="Pilih tanggal awal...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <input type="text" name="tgl_akhir" class="datepicker form-control" placeholder="Pilih tanggal akhir...">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <input type="submit" class="btn bg-blue btn-block btn-lg waves-effect">
                                        </div>
                                    </div>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if($action == "view"){ ?>
                    <!-- View Pasien -->
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
                    </div>
                    <div class="header">
                        <h2>
                            Catatan Medis
                        </h2>
                    </div>
                    <div class="body">
                      <div id="buttons" class="align-center m-l-10 m-b-15 export-hidden"></div>
                      <table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Poliklinik</th>
                                    <th>Keluhan</th>
                                    <th>Pemeriksaan</th>
                                    <th>Diagnosa</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $q_kunj = query ("SELECT a.tgl_registrasi, b.nm_poli, c.keluhan, c.pemeriksaan, a.no_rawat FROM reg_periksa a, poliklinik b, pemeriksaan_ralan c WHERE a.no_rkm_medis = '$no_rkm_medis' AND a.kd_poli = b.kd_poli AND a.no_rawat = c.no_rawat ORDER BY a.tgl_registrasi DESC");
                            while ($data_kunj = fetch_array($q_kunj)) {
                                $tanggal_kunj   = $data_kunj[0];
                                $nama_poli_kunj = $data_kunj[1];
                                $keluhan_kunj = $data_kunj[2];
                                $pemeriksaan_kunj = $data_kunj[3];
                                $no_rawat_kunj = $data_kunj[4];
                            ?>
                                <tr>
                                    <td><?php echo $tanggal_kunj; ?></td>
                                    <td><?php echo $nama_poli_kunj; ?></td>
                                    <td><?php echo $keluhan_kunj; ?></td>
                                    <td><?php echo $pemeriksaan_kunj; ?></td>
                                    <td>
                                        <ul style="list-style:none;">
                                        <?php
                                        $sql_dx = query("SELECT a.kd_penyakit, a.nm_penyakit FROM penyakit a, diagnosa_pasien b WHERE a.kd_penyakit = b.kd_penyakit AND b.no_rawat = '$no_rawat_kunj'");
                        	              $no=1;
                                        while ($row_dx = fetch_array($sql_dx)) {
                                            echo '<li>'.$no.'. '.$row_dx[1].' ('.$row_dx[0].')</li>';
        		                                $no++;
                                        }
                                        ?>
                                        </ul>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>

                 	  <form method="post" action="">
                        <div class="header">
                            <h2>
                                Detail e-Diagnosa
                            </h2>
                        </div>
                        <?php
                  	    if (isset($_POST['ok_diagnosa'])) {
        		                if (($_POST['kode_diagnosa'] <> "") and ($no_rawat <> "")) {
        			                  $insert = query("INSERT INTO diagnosa_pasien VALUES ('{$no_rawat}', '{$_POST['kode_diagnosa']}', 'Ralan', '{$_POST['prioritas']}')");
        			                  if ($insert) {
        			                      redirect("pendaftaran.php?action=view&no_rawat={$no_rawat}");
        			                  }
        		                }
        	              }
        	              ?>
                        <div class="body">
                            <dl class="dl-horizontal">
                                <dt>Diagnosa</dt>
                                <dd><select name="kode_diagnosa" class="kd_diagnosa" style="width:100%"></select></dd><br/>
                                <dt>Prioritas</dt>
                                <dd>
                                    <select name="prioritas" class="prioritas" style="width:100%">
                                        <option value="1">Diagnosa Ke-1</option>
                                        <option value="2">Diagnosa Ke-2</option>
                                        <option value="3">Diagnosa Ke-3</option>
                                        <option value="4">Diagnosa Ke-4</option>
                                        <option value="5">Diagnosa Ke-5</option>
                                    </select>
                                </dd><br/>
                                <dt></dt>
                                <dd><button type="submit" name="ok_diagnosa" value="ok_diagnosa" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_diagnosa\'">OK</button></dd><br/>
                                <dt></dt>
                                <dd>
        	                   		    <ul style="list-style:none;margin-left:0;padding-left:0;">
        	                    		  <?php
        	                          $query = query("SELECT a.kd_penyakit, b.nm_penyakit, a.prioritas FROM diagnosa_pasien a, penyakit b, reg_periksa c WHERE a.kd_penyakit = b.kd_penyakit AND a.no_rawat = '{$no_rawat}' AND a.no_rawat = c.no_rawat AND c.kd_dokter = '{$_SESSION['username']}' ORDER BY a.prioritas ASC");
                                		$no=1;
        	                    	    while ($data = fetch_array($query)) {
        	                    	    ?>
        	                              <li><?php echo $no; ?>. <?php echo $data['1']; ?> <a href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_diagnosa&kode=<?php echo $data['0']; ?>&prioritas=<?php echo $data['2']; ?>&no_rawat=<?php echo $no_rawat; ?>">[Hapus]</a></li>
        	                    		  <?php
                                  	   $no++;
        	                        	}
        	                        	?>
        	                        	</ul>
                                </dd>
                            </dl>
                        </div>
                        <div class="header">
                            <h2>
                                Detail e-Resep
                            </h2>
                        </div>
                    		<?php
                    		if (isset($_POST['ok_obat'])) {
                            if (($_POST['kode_obat'] <> "") and ($no_rawat <> "")) {
                      	    		$onhand = query("SELECT no_resep FROM resep_obat WHERE no_rawat = '{$no_rawat}'");
                          			$dtonhand = fetch_array($onhand);
                          			$get_number = fetch_array(query("SELECT max(no_resep) FROM resep_obat"));
                          			$lastNumber = substr($get_number[0], 0, 10);
                          			$next_no_resep = sprintf('%010s', ($lastNumber + 1));

                                if ($dtonhand['0'] > 1) {
                                  if ($_POST['aturan_pakai_lainnya'] == "") {
                          			    $insert = query("INSERT INTO resep_dokter VALUES ('{$dtonhand['0']}', '{$_POST['kode_obat']}', '{$_POST['jumlah']}', '{$_POST['aturan_pakai']}')");
                                  } else {
                          			    $insert = query("INSERT INTO resep_dokter VALUES ('{$dtonhand['0']}', '{$_POST['kode_obat']}', '{$_POST['jumlah']}', '{$_POST['aturan_pakai_lainnya']}')");
                                  }
                              		redirect("pendaftaran.php?action=view&no_rawat={$no_rawat}");
        								        } else {
                                		$insert = query("INSERT INTO resep_obat VALUES ('{$next_no_resep}', '{$date}', '{$time}', '{$no_rawat}', '{$_SESSION['username']}', '{$date}', '{$time}')");
                                    if ($_POST['aturan_pakai_lainnya'] == "") {
                            			    $insert2 = query("INSERT INTO resep_dokter VALUES ('{$next_no_resep}', '{$_POST['kode_obat']}', '{$_POST['jumlah']}', '{$_POST['aturan_pakai']}')");
                                    } else {
                            			    $insert2 = query("INSERT INTO resep_dokter VALUES ('{$next_no_resep}', '{$_POST['kode_obat']}', '{$_POST['jumlah']}', '{$_POST['aturan_pakai_lainnya']}')");
                                    }
                                		redirect("pendaftaran.php?action=view&no_rawat={$no_rawat}");
                            		}
                        	  }
                    		}
                    		?>
                        <div class="body">
                            <dl class="dl-horizontal">
                                <dt>Nama Obat</dt>
                                <dd><select name="kode_obat" class="kd_obat" style="width:100%"></select></dd><br>
                                <dt>Jumlah Obat</dt>
                                <dd><input name="jumlah" value="10" style="width:100%"></dd><br>
                                <dt>Aturan Pakai</dt>
                                <dd>
                                    <select name="aturan_pakai" class="aturan_pakai" id="lainnya" style="width:100%">
                                    <?php
                                    $sql = query("SELECT aturan FROM master_aturan_pakai");
                                    while($row = fetch_array($sql)){
                                        echo '<option value="'.$row[0].'">'.$row[0].'</option>';
                                    }
                                    ?>
                                    <option value="lainnya">Lainnya</option>
                                    </select>
                                </dd><br>
                                <div id="row_dim">
                                <dt></dt>
                                <dd><input name="aturan_pakai_lainnya" style="width:100%"></dd><br>
                                </div>
                                <dt></dt>
                                <dd><button type="submit" name="ok_obat" value="ok_obat" class="btn bg-indigo waves-effect" onclick="this.value=\'ok_diagnosa\'">OK</button></dd><br>
                                <dt></dt>
                            </dl>
                        </div>
                        <div class="body">
                            <div class="body table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nama Obat</th>
                                        <th>Jumlah</th>
                                        <th>Aturan Pakai</th>
                                        <th>Tools</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $query_resep = query("SELECT a.kode_brng, a.jml, a.aturan_pakai, b.nama_brng, a.no_resep FROM resep_dokter a, databarang b, resep_obat c WHERE a.kode_brng = b.kode_brng AND a.no_resep = c.no_resep AND c.no_rawat = '{$no_rawat}' AND c.kd_dokter = '{$_SESSION['username']}' ");
                                while ($data_resep = fetch_array($query_resep)) {
                                ?>
                                    <tr>
                                        <td><?php echo $data_resep['3']; ?></td>
                                        <td><?php echo $data_resep['1']; ?></td>
                                        <td><?php echo $data_resep['2']; ?></td>
                                        <td><a href="<?php $_SERVER['PHP_SELF']; ?>?action=delete_obat&kode_obat=<?php echo $data_resep['0']; ?>&no_resep=<?php echo $data_resep['4']; ?>&no_rawat=<?php echo $no_rawat; ?>">Hapus</a></td>
                                    </tr>
                                <?php
                                }
                                ?>
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </form>
                <?php } ?>
                <?php
                //delete
                if($action == "delete_diagnosa"){
        	          $hapus = "DELETE FROM diagnosa_pasien WHERE no_rawat='{$_REQUEST['no_rawat']}' AND kd_penyakit = '{$_REQUEST['kode']}' AND prioritas = '{$_REQUEST['prioritas']}'";
        	          $hasil = query($hapus);
        	          if (($hasil)) {
        	              redirect("pendaftaran.php?action=view&no_rawat={$no_rawat}");
        	          }
                }

                //delete
                if($action == "delete_obat"){
        	          $hapus = "DELETE FROM resep_dokter WHERE no_resep='{$_REQUEST['no_resep']}' AND kode_brng='{$_REQUEST['kode_obat']}'";
        	          $hasil = query($hapus);
        	          if (($hasil)) {
                      redirect("pendaftaran.php?action=view&no_rawat={$no_rawat}");
        	          }
                }
                ?>
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
