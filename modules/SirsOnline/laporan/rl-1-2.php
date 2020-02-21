<div class="card">
    <div class="header">
        <h2>
            LAPORAN RL 1.2 (Indikator Pelayanan Rumah Sakit)
        </h2>
      <small><?php if(isset($_GET['tahun'])) { $tahun = $_GET['tahun']; } else { $date = date('Y-m-d'); $tahun = date("Y",strtotime($date)); }; echo "Periode ".$tahun; ?></small>
      <ul class="header-dropdown m-r--5">
    	<li class="dropdown">
        	<a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="material-icons">more_vert</i>
            </a>
                <ul class="dropdown-menu pull-right">
                  <?php
                  $current_year = date('Y');
                  $years = range($current_year-5, $current_year);
                  foreach ($years as $year) {
                    echo '<li><a href="'.URL.'/index.php?module=SirsOnline&page=rl_1_2&tahun='.$year.'">'.$year.'</a></li>';
                  }
                  ?>
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
              <?php
              $kamar = "SELECT COUNT(kd_kamar) as total FROM kamar WHERE statusdata = '1'";
              $result1 = fetch_array(query($kamar));
              $hari = "SELECT lama as lama FROM kamar_inap WHERE tgl_masuk LIKE '%{$tahun}%'";
              $result2 = fetch_array(query($hari));
              $bor = $result2['lama']/($result1['total']*365);
              $jml = "SELECT COUNT(no_rawat) as jml FROM kamar_inap WHERE tgl_masuk LIKE '%{$tahun}%'";
              $jmlpsn = fetch_array(query($jml));
              $alos = $result2['lama']/$jmlpsn['jml'];
              $bto = $jmlpsn['jml']/$result1['total'];
              $toi = (($result1['total']*365)-$result2['lama'])/$jmlpsn['jml'];
              $mati = "SELECT COUNT(no_rawat) as mati FROM kamar_inap WHERE stts_pulang = 'Meninggal' AND lama > 2 AND tgl_masuk LIKE '%{$tahun}%'";
              $death = fetch_array(query($mati));
              $ndr = ($death['mati']/$jmlpsn['jml'])*1000;
              $die = "SELECT COUNT(no_rawat) as mati FROM kamar_inap WHERE stts_pulang = 'Meninggal' AND tgl_masuk LIKE '%{$tahun}%'";
              $shi = fetch_array(query($die));
              $gdr = ($shi['mati']/$jmlpsn['jml'])*1000;
              $avg = $jmlpsn['jml']/365;
              ?>
              <tr>
                  <td><?php echo KODERS; ?></td>
                  <td><?php echo KODEPROP; ?></td>
                  <td><?php echo $dataSettings['kabupaten']; ?></td>
                  <td><?php echo $tahun; ?></td>
                  <td><?php echo number_format($bor*100,2)."%"; ?></td>
                  <td><?php echo number_format($alos,2)." Hari"; ?></td>
                  <td><?php echo number_format($bto,2)." Kali";?></td>
                  <td><?php echo number_format($toi,2)." Hari";?></td>
                  <td><?php  echo number_format($ndr,2)." Permil";?></td>
                  <td><?php echo number_format($gdr,2)." Permil";?></td>
                  <td><?php echo number_format($avg,2);?></td>
              </tr>
          </tbody>
      </table>
    </div>
</div>
