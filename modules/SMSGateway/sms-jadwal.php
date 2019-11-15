<div class="card">
    <div class="header">
        <h2>
            PENJADWALAN SMS
        </h2>
    </div>
    <div class="body">
      				<h2 class="title">On Scheduled SMS</h2>

      				<div class="entry">
      					<p>
      <?php
      if ($_GET['op'] == "update")
      {
      // proses update message on schedule
      ?>
      <h3>Edit Message</h3>
      <?php

          $id = $_POST['id'];
      	$msg = $_POST['pesan'];
      	$pubdate = $_POST['pubdate'];
      	$group = $_POST['group'];

      	$query = "UPDATE sms_message SET message = '$msg', pubdate = '$pubdate', idgroup = '$group' WHERE id = '$id'";
      	query($query);
      	echo "<p>&nbsp;</p><p>Message sudah diupdate</p>";


      }

      if ($_GET['op'] == "add")
      {
      // proses tambah on scheduled message
      ?>
      <h3>Tambah Message</h3>
      <p>&nbsp;</p>
      <form name="formku" method="post" action="<?php echo URL;?>/?module=SMSGateway&page=jadwal&op=simpan">
      Message : <br>
      <textarea name="pesan" rows="10" cols="50"><?php echo $data['message']; ?></textarea>
      <br>
      Keterangan: Berikan string [nama] bila ingin menampilkan nama si penerima SMS pada pesan di atas.
      <br><br>
      Pilih Group :
      <select name='group'>
      <option value="0" selected>All</option>
      <?php
      $query = "SELECT * FROM sms_group";
      $hasil = query($query);
      while ($data = fetch_array($hasil))
      {
        echo "<option value='".$data['idgroup']."'>".$data['group']."</option>";
      }
      ?>
      </select><br>
      <br>
      Published Date Time (YYYY-MM-DD&lt;spasi&gt;hh:mm, contoh: 2010-05-27 21:30) : <br>
      <input type="text" name="pubdate" value="<?php echo $data['pubdate'];?>">
      <br><br>
      <input type="submit" name="submit" value="Simpan Message">
      </form>


      <?php
      }

      if ($_GET['op'] == "simpan")
      {
      // proses penyimpanan on scheduled message baru
         $pesan = $_POST['pesan'];
         $group = $_POST['group'];
         $pubdate = $_POST['pubdate'];
         $query = "INSERT INTO sms_message(message, pubdate, idgroup) VALUES ('$pesan', '$pubdate', '$group')";
         $hasil = query($query);
         if ($hasil) echo "<p>Message sudah disimpan</p>";
         else echo "<p>Message gagal disimpan</p>";
      }

      if ($_GET['op'] == "hapus")
      {
      // proses menghapus on scheduled message
          $id = $_GET['id'];
      	$query = "DELETE FROM sms_message WHERE id = $id";
      	query($query);
      	echo "<p>Message sudah dihapus</p>";
      }

      if ($_GET['op'] == "edit")
      {
      // proses edit on scheduled message
          $id = $_GET['id'];
          $query = "SELECT * FROM sms_message WHERE id = $id";
      	$hasil = query($query);
      	$data = fetch_array($hasil);
      ?>

      <h3>Edit Message</h3>
      <p>&nbsp;</p>
      <form name="formku" method="post" action="<?php $_SERVER['PHP_SELF'];?>?op=update">
      Message : <br>
      <textarea name="pesan" rows="10" cols="50"><?php echo $data['message']; ?></textarea><br><br>
      Published Date (yyyy-mm-dd): <br>
      <input type="text" name="pubdate" value="<?php echo $data['pubdate'];?>"><br><br>
      Pilih Group :
      <select name='group'>
      <?php
        if ($data['idgroup'] == 0) echo "<option value='0' selected>All</option>";
        else echo "<option value='0'>All</option>";
      ?>

      <?php
      $query2 = "SELECT * FROM sms_group";
      $hasil2 = query($query2);
      while ($data2 = fetch_array($hasil2))
      {
        if ($data2['idgroup'] == $data['idgroup']) echo "<option value='".$data2['idgroup']."' selected>".$data2['group']."</option>";
        else echo "<option value='".$data2['idgroup']."'>".$data2['group']."</option>";
      }
      ?>
      </select>

      <input type="hidden" name="id" value="<?php echo $data['id'];?>"> <br><br><input type="submit" name="submit" value="Update Message">
      </form>

      <?php
      }
      else
      {
      // menampilkan semua daftar on scheduled message yang belum terkirim (status = 0)
      $query = "SELECT * FROM sms_message ORDER BY id";
      $hasil = query($query);
      echo "<br>";
      echo "<table border='1' width='100%'>";
      echo "<tr><th>No.</th><th>Message</th><th>Group</th><th>Published Date</th><th>Atur</th></tr>";
      while ($data = fetch_array($hasil))
      {
         $i++;
         $idgroup = $data['idgroup'];
         if ($idgroup > 0)
         {
         $query2 = "SELECT `group` FROM sms_group WHERE idgroup = '$idgroup'";
         $hasil2 = query($query2);
         $data2  = fetch_array($hasil2);
         $namagroup = $data2['group'];
         }
         else $namagroup = "All";

         echo "<tr><td>".$i."</td><td>".$data['message']."</td><td>".$namagroup."</td><td>".$data['pubdate']."</td><td><a href='".$_SERVER['PHP_SELF']."?op=edit&id=".$data['id']."'>Edit</a> | <a href='".$_SERVER['PHP_SELF']."?op=hapus&id=".$data['id']."'>Hapus</a></td></tr>";
      }
      echo "</table>";
      }
      ?>

      					</p>
      				</div>
      			</div>
      			</div>
      			</div>

      		</div>
      		<!-- end #content -->
    </div>
</div>
