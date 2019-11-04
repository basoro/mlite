<div class="card">
    <div class="header">
      <h2>Data SEP</h2>
    </div>
    <div class="body">
        <?php $action = isset($_GET['action'])?$_GET['action']:null;
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
        <?php if($action == "no_rkm_medis"){?>

      <?php } ?>
    </div>
</div>
