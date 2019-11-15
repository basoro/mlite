<div class="card">
    <div class="header">
        <h2>
            SMS AUTORESPONDER
        </h2>
    </div>
    <div class="body">

      				<div class="entry">
      					<p>
      					<?php
      					if ($_GET['op'] == "update")
      {
      // proses update data
      ?>
      <h3>Edit Message</h3>
      <?php

      	$id = $_POST['id'];
      	$msg = $_POST['msg'];
      	$interval = $_POST['interval'];
      	$group = $_POST['group'];

      	$query = "UPDATE sms_autoresponder
      	          SET msg = '$msg', interv = '$interval', idgroup = '$group'
      			  WHERE id = '$id'";

      	query($query);

      	$query = "DELETE sms_autolist WHERE id = '$id'";
      	query($query);

      	$query = "SELECT * FROM sms_phonebook WHERE idgroup = '$group'";
          $hasil = query($query);
          while ($data  = fetch_array($hasil))
          {
             $notelp = $data['noTelp'];
             $query2 = "INSERT INTO sms_autolist VALUES ('$notelp', '$id', '0')";
      	   query($query2);
          }

      	echo "<p>&nbsp</p><p>Message sudah diupdate</p>";

      }

      if ($_GET['op'] == "simpan")
      {
      // proses penyimpanan data message autoresponder yang baru
         $msg = $_POST['msg'];
         $interval = $_POST['interval'];
         $group = $_POST['group'];

         $query = "INSERT INTO sms_autoresponder(msg, interv, idgroup) VALUES ('$msg', '$interval', '$group')";
         $hasil = query($query);
         if ($hasil) echo "<p>Data sudah disimpan</p>";
         else echo "<p>Data gagal disimpan</p>";

         $query = "SELECT max(id) as maks FROM sms_autoresponder";
         $hasil = query($query);
         $data  = fetch_array($hasil);
         $idmax = $data['maks'];

         $query = "SELECT * FROM sms_phonebook WHERE idgroup = '$group'";
         $hasil = query($query);
         while ($data  = fetch_array($hasil))
         {
             $notelp = $data['noTelp'];
             $query2 = "INSERT INTO sms_autolist VALUES ('$notelp', '$idmax', '0')";
      	   query($query2);
         }

      }

      if ($_GET['op'] == "hapus")
      {
      // proses menghapus data message
          $id = $_GET['id'];
      	$query = "DELETE FROM sms_autoresponder WHERE id = '$id'";
      	query($query);
      	$query = "DELETE FROM sms_autolist WHERE id = '$id'";
      	query($query);
      	echo "<p>Data auto responder sudah dihapus</p>";
      }

      if ($_GET['op'] == "edit")
      {
      // proses edit data message
          $id = $_GET['id'];
          $query = "SELECT * FROM sms_autoresponder WHERE id = '$id'";
      	$hasil = query($query);
      	$data = fetch_array($hasil);
      ?>

      <h3>Edit Message</h3>
      <p>&nbsp;</p>
      <form name="formku" method="post" action="<?php echo URL;?>/?module=SMSGateway&page=auto&op=update">
      Message : <br>
      <textarea name="msg" cols="60" rows="10"><?php echo $data['msg']; ?></textarea>
      <br><br>
      Interval (*) : <input type="text" name="interval" size="4" value="<?php echo $data['interv'];?>"> Pilih Group : <select name="group">
      <?php
      $query2 = "SELECT * FROM jabatan";
      $hasil2 = query($query2);
      while ($data2 = fetch_array($hasil2))
      {
        if ($data2['kd_jbtn'] == $data['kd_jbtn']) echo "<option value='".$data2['kd_jbtn']."' selected>".$data2['nm_jbtn']."</option>";
        else echo "<option value='".$data2['kd_jbtn']."'>".$data2['nm_jbtn']."</option>";
      }
      ?>
      </select>
      <br><br>
      (*) Waktu pengiriman pesan dalam hari, dihitung mulai dari tanggal registrasi. Contohnya bila diisi 30, maka pesan akan dikirim secara otomatis pada hari ke-30 setelah registrasi.<br><br>
      <input type="submit" name="submit" value="Submit">
      <input type="hidden" name="id" value="<?php echo $data['id'];?>">
      </form>


      <?php
      }
      else
      {

        // proses tambah data message auto responder
        ?>
        <h3>Tambah Message</h3>

        <form name="formku" method="post" action="<?php echo URL;?>/?module=SMSGateway&page=auto&op=simpan">
        Message : <br /><br />
        <textarea name="msg" cols="60" rows="10"></textarea>
        <br><br>
        Interval (*) : <input type="text" name="interval" size="4"> Pilih Group :

        <select name="group">
        <?php
        $query = "SELECT * FROM jabatan";
        $hasil = query($query);
        while ($data = fetch_array($hasil))
        {
          echo "<option value='".$data['kd_jbtn']."'>".$data['nm_jbtn']."</option>";
        }
        ?>
        </select>

        <input type="submit" name="submit" value="Simpan"><br><br>
        (*) Waktu pengiriman pesan dalam hari, dihitung mulai dari tanggal registrasi. Contohnya bila diisi 30, maka pesan akan dikirim secara otomatis pada hari ke-30 setelah registrasi.
        </form>

        <?php
      // menampilkan seluruh data message

      $query = "SELECT id, interv, msg, jabatan.nm_jbtn FROM sms_autoresponder, jabatan WHERE jabatan.kd_jbtn = sms_autoresponder.idgroup
                ORDER BY sms_autoresponder.idgroup, interv";
      $hasil = query($query);
      echo "<br>";
      echo "<table border='1' width='100%'>";
      echo "<tr><th>Interval (hari)</th><th>Message</th><th>Group</th><th>Atur</th></tr>";
      while ($data = fetch_array($hasil))
      {
         $i++;
         echo "<tr><td>".$data['interv']."</td><td>".$data['msg']."</td><td>".$data['nm_jbtn']."</td><td><a href='".URL."/?module=SMSGateway&page=auto&op=edit&id=".$data['id']."'>Edit</a> | <a href='".URL."/?module=SMSGateway&page=auto&op=hapus&id=".$data['id']."'>Hapus</a></td></tr>";
      }
      echo "</table>";
      }
      ?>
      					</p>
      				</div>
      			</div>
      			</div>
      			</div>

      		<div style="clear: both;">&nbsp;</div>
      		</div>
      		<!-- end #content -->

    </div>
</div>
