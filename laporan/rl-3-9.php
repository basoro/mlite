<?php
/***
* SIMRS Khanza Lite from version 0.1 Beta
* About : Porting of SIMRS Khanza by Windiarto a.k.a Mas Elkhanza as web and mobile app.
* Last modified: 02 Pebruari 2018
* Author : drg. Faisol Basoro
* Email : drg.faisol@basoro.org
* Licence under GPL
***/

$title = 'Laporan';
include_once('../config.php');
include_once('../layout/header.php');
include_once('../layout/sidebar.php');
?>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                LAPORAN RL 3.9 (Rehabilitasi Medik)
                            </h2>
                        </div>
                        <div class="body">
                          <table class="table table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                            <tr>
                              <th>Masuk</th>
                              <th>Keluar</th>
                            </tr>
                            <tr>
                              <td><?php $masuk = fetch_array(query("SELECT COUNT(kamar_inap.no_rawat) FROM kamar , kamar_inap , bangsal WHERE kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND kamar_inap.tgl_masuk = '$_POST[tgl_awal]' AND bangsal.nm_bangsal LIKE '%AL_-_AFIAT_LT.1%'"));echo $masuk[0];?></td>
                              <td><?php $keluar = fetch_array(query("SELECT COUNT(kamar_inap.no_rawat) FROM kamar , kamar_inap , bangsal WHERE kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND kamar_inap.tgl_keluar = '$_POST[tgl_awal]' AND bangsal.nm_bangsal LIKE '%AL_-_AFIAT_LT.1%'"));echo $keluar[0];?></td>
                            </tr>
							<div>
                            <form method="post" action="">
                             <div class="col-sm-5">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <input type="text" name="tgl_awal" class="datepicker form-control" placeholder="Pilih tanggal awal...">
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
                          </table>
                         
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
include_once('../layout/footer.php');
?>
