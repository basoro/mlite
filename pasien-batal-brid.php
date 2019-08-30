<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Pasien Batal';
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
                                PASIEN BATAL
                            </h2>
                        </div>
                        <div class="body">
                          <table id="datatable" class="table responsive table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                            <thead>
                              <tr>
                                <th>No RM</th>
                                <th>Nama</th>
                                <th>Poli</th>
                                <th>Tanggal</th>
                                <th>No Peserta</th>
                                <th>No SEP</th>
                                <th>Action</th>
                              </tr>
                            </thead>
                            <tbody>
                            <?php $hapus = query("SELECT reg_periksa.no_rkm_medis , pasien.nm_pasien , poliklinik.nm_poli , reg_periksa.tgl_registrasi , reg_periksa.no_rawat , pasien.no_peserta
                            FROM reg_periksa , poliklinik , pasien WHERE reg_periksa.kd_poli = poliklinik.kd_poli AND
                            reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.stts = 'Batal' AND reg_periksa.kd_pj IN ('A02','BPJ') AND reg_periksa.tgl_registrasi = '$date'");
                              		while($row = fetch_array($hapus)) {?>
                              <tr>
                                <td><?php echo $row['no_rkm_medis'];?></td>
                                <td><?php echo SUBSTR($row['nm_pasien'], 0, 15).' ...';?></td>
                                <td><?php echo $row['nm_poli'];?></td>
                                <td><?php echo $row['tgl_registrasi'];?></td>
                                <td><?php echo $row['no_peserta'];?></td>
                                <td><?php $sep = fetch_array(query("SELECT no_sep from bridging_sep where no_rawat = '".$row['no_rawat']."'"));echo $sep['no_sep'];?></td>
                                <td><a href="objek/hapus-brid.php?no_sep=<?php echo $sep['no_sep'];?>" class="btn btn-danger">X</a></td>
                              </tr><?php } ?>
                            </tbody>
                            
                          </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
include_once('layout/footer.php');
?>
<script>
$('#datatable_batal').dataTable( {
	          	responsive: true,
            } );
</script>