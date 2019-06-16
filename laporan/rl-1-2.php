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
                                LAPORAN RL 1.2 (Indikator Pelayanan Rumah Sakit)
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
                                      <th>BOR</th>
                                      <th>LOS</th>
                                      <th>BTO</th>
                                      <th>TOI</th>
                                      <th>NDR</th>
                                      <th>GDR</th>
                                      <th>Rata Kunjungan</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <tr>
                                      <td><?php echo KODERS; ?></td>
                                      <td><?php echo KODEPROP; ?></td>
                                      <td><?php echo $dataSettings['kabupaten']; ?></td>
                                      <td><?php echo $tahun; ?></td>
                                      <td><?php $kamar = "SELECT COUNT(kd_kamar) as total FROM kamar WHERE statusdata = '1'"; $result1 = fetch_array(query($kamar));
                                        		$hari = "SELECT SUM(lama) as lama FROM kamar_inap WHERE tgl_masuk LIKE '%{$tahun}%'"; $result2 = fetch_array(query($hari)); 
                                        		$bor = $result2['lama']/($result1['total']*365); echo number_format($bor*100,2)."%";?></td>
                                      <td><?php $jml = "SELECT COUNT(no_rawat) as jml FROM kamar_inap WHERE tgl_masuk LIKE '%{$tahun}%'"; $jmlpsn = fetch_array(query($jml));
                                        		$alos = $result2['lama']/$jmlpsn['jml']; echo number_format($alos,2)." Hari";?></td>
                                      <td><?php $bto = $jmlpsn['jml']/$result1['total']; echo number_format($bto,2)." Kali";?></td>
                                      <td><?php $toi = (($result1['total']*365)-$result2['lama'])/$jmlpsn['jml']; echo number_format($toi,2)." Hari";?></td>
                                      <td><?php $mati = "SELECT COUNT(no_rawat) as mati FROM kamar_inap WHERE stts_pulang = 'Meninggal' AND lama > 2 AND tgl_masuk LIKE '%{$tahun}%'"; $death = fetch_array(query($mati)); $ndr = ($death['mati']/$jmlpsn['jml'])*1000; echo number_format($ndr,2)." Permil";?></td>
                                      <td><?php $die = "SELECT COUNT(no_rawat) as mati FROM kamar_inap WHERE stts_pulang = 'Meninggal' AND tgl_masuk LIKE '%{$tahun}%'"; $shi = fetch_array(query($die));$gdr = ($shi['mati']/$jmlpsn['jml'])*1000;echo number_format($gdr,2)." Permil";?></td>
                                      <td><?php $avg = $jmlpsn['jml']/365; echo number_format($avg,2);?></td>
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
