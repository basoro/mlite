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
                                LAPORAN RL 1.3 (Fasilitas Tempat Tidur Rawat Inap)
                            </h2>
                        <small><?php if(isset($_GET['tahun'])) { $tahun = $_GET['tahun']; } else { $tahun = date("Y",strtotime($date)); }; echo "Periode ".$tahun; ?></small>
                          <ul class="header-dropdown m-r--5">
                        	<li class="dropdown">
                            	<a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                	<i class="material-icons">arrow_drop_down_circle</i>
                                </a>
                                    <ul class="dropdown-menu pull-right">
                                        <li><a href="rl-1-2.php?tahun=2016">2016</a></li>
                                        <li><a href="rl-1-2.php?tahun=2017">2017</a></li>
                                        <li><a href="rl-1-2.php?tahun=2018">2018</a></li>
                                        <li><a href="rl-1-2.php?tahun=2019">2019</a></li>
                                    </ul>
                          	</li>
                        </ul>
                        </div>
                      	<div class="body">
                          <div id="buttons" class="align-center m-l-10 m-b-15 export-hidden"></div>
                          <table id="datatable" class="table table-bordered table-striped table-hover display nowrap js-exportable" width="100%">
                              <thead>
                                  <tr>
                                      <th>Kode RS</th>
                                      <th>Kode Prop</th>
                                      <th>Kab / Kota</th>
                                      <th>Tahun</th>
                                      <th>Jenis Pelayanan</th>
                                      <th>Jumlah TT</th>
                                      <th>VVIP</th>
                                      <th>VIP</th>
                                      <th>I</th>
                                      <th>II</th>
                                      <th>III</th>
                                      <th>Kelas Khusus</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <tr>
                                      <td><?php echo KODERS; ?></td>
                                      <td><?php echo KODEPROP; ?></td>
                                      <td><?php echo $dataSettings['kabupaten']; ?></td>
                                      <td><?php echo $tahun; ?></td>
                                      <th>Jenis Pelayanan</th>
                                      <th>Jumlah TT</th>
                                      <th>VVIP</th>
                                      <th>VIP</th>
                                      <th>I</th>
                                      <th>II</th>
                                      <th>III</th>
                                      <th>Kelas Khusus</th>
                                  </tr>
                              </tbody>
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
