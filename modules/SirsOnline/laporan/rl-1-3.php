<div class="card">
    <div class="header">
        <h2>
            LAPORAN RL 1.3 (Fasilitas Tempat Tidur Rawat Inap)
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
                    echo '<li><a href="'.URL.'/index.php?module=SirsOnline&page=rl_1_3&tahun='.$year.'">'.$year.'</a></li>';
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
