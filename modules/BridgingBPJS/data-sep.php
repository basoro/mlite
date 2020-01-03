<div class="card">
    <div class="header">
      <h2>Data SEP</h2>
    </div>
    <div class="body">
        <?php $action = isset($_GET['view'])?$_GET['view']:null;
        if(!$action){?>
          <table id="allsep" class="table table-bordered table-striped table-hover display nowrap" width="100%">
            <thead>
              <tr>
                <th>No SEP</th>
                <th>No Rawat</th>
                <th>Tangal SEP</th>
                <th>Tgl Rujukan</th>
                <th>No Rujukan</th>
                <th>No RM</th>
                <th>Nama Pasien</th>
                <th>Tanggal Lahir</th>
                <th>Peserta</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        <?php } ?>
        <?php if($action == "individual"){?>
          <table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">
            <thead>
              <tr>
                <th>No SEP</th>
                <th>No Rawat</th>
                <th>Tangal SEP</th>
                <th>Tgl Rujukan</th>
                <th>No Rujukan</th>
                <th>No RM</th>
                <th>Nama Pasien</th>
                <th>Tanggal Lahir</th>
                <th>Peserta</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $individual = query("SELECT no_sep, no_rawat, tglsep, tglrujukan, no_rujukan, nomr, nama_pasien, tanggal_lahir, peserta FROM bridging_sep WHERE nomr = '{$_GET['no_rkm_medis']}'");
              while($row = fetch_assoc($individual)) {
                echo '<tr>';
                echo '  <td>'.$row['no_sep'].'</td>';
                echo '  <td>'.$row['no_rawat'].'</td>';
                echo '  <td>'.$row['tglsep'].'</td>';
                echo '  <td>'.$row['tglrujukan'].'</td>';
                echo '  <td>'.$row['no_rujukan'].'</td>';
                echo '  <td>'.$row['nomr'].'</th>';
                echo '  <td>'.$row['nama_pasien'].'</td>';
                echo '  <td>'.$row['tanggal_lahir'].'</td>';
                echo '  <td>'.$row['peserta'].'</td>';
                echo '</tr>';
              }
              ?>
            </tbody>
          </table>
      <?php } ?>
    </div>
</div>
