<div class="card">
  <div class="header">
      <h2>Data Pegawai</h2>
  </div>
  <div class="body">
    <table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Nomor Induk</th>
                <th>Jenis Kelamin</th>
                <th>Jabatan</th>
                <th>Bidang</th>
            </tr>
        </thead>
        <tbody>
        <?php
            $sql = "SELECT * from pegawai";
            $query = query($sql);
            while($row = fetch_array($query)) {
        ?>
            <tr>
                <td><?php echo $row['nama'];?></td>
                <td><?php echo $row['nik'];?></td>
                <td><?php echo $row['jk'];?></td>
                <td><?php echo $row['jbtn'];?></td>
                <td><?php echo $row['bidang'];?></td>
            </tr>
        <?php
            }
        ?>
        </tbody>
    </table>
  </div>
</div>
