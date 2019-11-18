<?php
include "send.php";
$op = isset($_GET['op'])?$_GET['op']:null;
$action = isset($_GET['action'])?$_GET['action']:null;
?>
<?php
if(num_rows(query("SHOW TABLES LIKE 'sms_inbox'")) !== 1) {
	echo '<div style="min-height:70vh; min-width:70vw;">';
	echo '<div class="alert bg-pink alert-dismissible text-center" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);">';
	echo '<p class="lead">Belum terinstall SMS Gateway</p>';
	echo '<a href="'.URL.'/?module=SMSGateway&page=index&op=install" class="btn btn-lg btn-primary m-t-20">Install Sekarang</a>';
	echo '</div>';
	echo '</div>';
} else {
?>

<?php
		if (!$op)
		{
		?>
		<div class="card">
		    <div class="header">
		        <h2>
		            SMS INSTANT
		        </h2>
		    </div>
				<div class="body">
					<ul class="nav nav-tabs tab-nav-right" role="tablist">
							<li role="presentation" class="active"><a href="<?php echo URL; ?>/?module=SMSGateway&page=sendsms">SMS Instant</a></li>
							<li role="presentation"><a href="<?php echo URL; ?>/?module=SMSGateway&page=sendsms&op=broadcast">Broadcast</a></li>
							<li role="presentation"><a href="<?php echo URL; ?>/?module=SMSGateway&page=sendsms&op=autoreply">Auto Reply</a></li>
					</ul>
				  <form name="formku" method="POST" action="<?php echo URL; ?>/?module=SMSGateway&page=sendsms&op=send">
				      <div class="form-group form-float">
				          <div class="form-line">
				              <textarea name="pesan" cols="30" rows="5" class="form-control no-resize" required></textarea>
				              <label class="form-label">Pesan</label>
				          </div>
				      </div>
				      <div class="form-group form-float">
				              <p><b>Keterangan:</b> <br>Berikan string [nama] bila ingin menampilkan nama si penerima SMS pada pesan di atas. <br>Contoh: "Hallo [nama], apa kabar?"</p>
				      </div>

							<div class="form-group">
				          <input type="radio" name="kirim" value="group" id="group" class="with-gap">
				          <label for="group">Kirim ke Group</label>
				          <select class="form-control show-tick" name='group'>
				          <option value="0">All</option>
				          <?php
				          $query = "SELECT * FROM sms_group";
				          $hasil = query($query);
				          while ($data = fetch_array($hasil))
				          {
				            echo "<option value='".$data['idgroup']."'>".$data['idgroup']."</option>";
				          }
				          ?>
				          </select>
				      </div>

				      <div class="form-group">

				          <input type="radio" name="kirim" value="single" id="single" class="with-gap">
				          <label for="single">Kirim ke Single</label>
				          <select class="form-control show-tick" name='nohp'>
				          <?php
				          $query = "SELECT * FROM petugas";
				          $hasil = query($query);
				          while ($data = fetch_array($hasil))
				          {
				            echo "<option value='".$data['no_telp']."'>".$data['nama']."</option>";
				          }
				          ?>
				          </select>
				      </div>

							<div class="form-group">
				          <input type="radio" name="kirim" value="jabatan" id="jabatan" class="with-gap">
				          <label for="jabatan">Kirim ke Group Jabatan</label>
				          <select class="form-control show-tick" name='group'>
				          <option value="0">All</option>
				          <?php
				          $query = "SELECT * FROM jabatan";
				          $hasil = query($query);
				          while ($data = fetch_array($hasil))
				          {
				            echo "<option value='".$data['kd_jbtn']."'>".$data['nm_jbtn']."</option>";
				          }
				          ?>
				          </select>
				      </div>

				      <div class="form-group">
				      <input type="submit" name="submit" class="btn btn-primary m-t-15 waves-effect" value="Kirim SMS">
				      </div>
				  </form>
				</div>
		</div>
		<?php
		}
		if ($op == "send")
		{
		   // jika pengirimannya berdasarkan group
		   if ($_POST['kirim'] == "group")
		   {
		   // membaca group
		   $group = $_POST['group'];
		   // membaca pesan yang akan dikirim dari form
		   $pesan = $_POST['pesan'];

		   // menyimpan pesan ke tabel sms_sentmsg
		   $query = "INSERT INTO sms_sentmsg(msg) VALUES ('$pesan')";
		   $hasil = query($query);

		   // membaca no. ID pesan yang akan dikirim dari tabel sms_sentmsg
		   $query = "SELECT max(id) as max FROM sms_sentmsg WHERE msg = '$pesan'";
		   $hasil = query($query);
		   $data = fetch_array($hasil);
		   $idmsg = $data['max'];


		   // membaca  no. telp dari phonebook berdasarkan group

		   if ($group == 0) $query = "SELECT * FROM sms_phonebook";
		   else if ($group > 0) $query = "SELECT * FROM sms_phonebook WHERE idgroup = '$group'";

		   $hasil = query($query);

		   while ($data = fetch_array($hasil))
		   {
		      // proses pengiriman pesan SMS ke semua no. telp
		      $notelp = $data['noTelp'];

		      send($notelp, $pesan);
		   }
		   }
		   // jika pengirimannya berdasarkan single
		   else if ($_POST['kirim'] == "single")
		   {

		   // membaca no hp dari single
		   $notelp = $_POST['nohp'];
		   // membaca pesan yang akan dikirim dari form
		   $pesan = $_POST['pesan'];

		   send($notelp, $pesan);
		   }

		   echo "<hr><p>SMS sudah dikirim....</p><hr>";
		}
		else if ($op == 'broadcast')
		{
		?>

		<div class="card">
		    <div class="header">
		        <h2>
		            SMS INSTANT
		        </h2>
		    </div>
				<div class="body">
					<ul class="nav nav-tabs tab-nav-right" role="tablist">
							<li role="presentation"><a href="<?php echo URL; ?>/?module=SMSGateway&page=sendsms">SMS Instant</a></li>
							<li role="presentation" class="active"><a href="<?php echo URL; ?>/?module=SMSGateway&page=sendsms&op=broadcast">Broadcast</a></li>
							<li role="presentation"><a href="<?php echo URL; ?>/?module=SMSGateway&page=sendsms&op=autoreply">Auto Reply</a></li>
					</ul>
					<form method="post" enctype="multipart/form-data" action="sendsms.php?op=broadcast&action=proses">
					Pilih file source<br>
					<input type="hidden" name="MAX_FILE_SIZE" value="20000000">
					<input name="userfile" type="file" size="50"><br><br>
					Masukkan template SMS<br>
					<textarea name="template" cols="50" rows="8"></textarea><br><br>
					<input name="upload" type="submit" value="KIRIM SMS"></td>
					</form>
				</div>
		</div>

		<?php
		   if ($action == 'proses')
		   {
		    error_reporting(E_ALL ^ E_NOTICE);
			require_once 'excel_reader2.php';

			$data = new Spreadsheet_Excel_Reader($_FILES['userfile']['tmp_name']);

			$baris = $data->rowcount($sheet_index=0);
			$kolom = $data->colcount($sheet_index=0);

			for ($i=2; $i<=$baris; $i++)
			{
				$string = $_POST['template'];
				preg_match_all("|\[(.*)\]|U", $string, $hasil, PREG_PATTERN_ORDER);

				for($j=1; $j<=$kolom; $j++)
				{
					$value[$data->val(1, $j)] = $data->val($i, $j);
				}

				foreach($hasil[1] as $key => $nilai)
				{
		   			$string = str_replace('['.$nilai.']', '['.strtoupper($nilai).']', $string);
					$kapital = strtoupper($nilai);
					$string = str_replace('['.$kapital.']', $value[$kapital], $string);
				}

				if ($value['NOHP'] != '')
				{
				  send($value['NOHP'], $string);
				}
			}
		    echo "<br><br><p>SMS telah dikirim....</p>";
		   }

		}
		else if ($op == 'autoreply')
		{
		?>
		<div class="card">
		    <div class="header">
		        <h2>
		            SMS INSTANT
		        </h2>
		    </div>
				<div class="body">
					<ul class="nav nav-tabs tab-nav-right" role="tablist">
							<li role="presentation"><a href="<?php echo URL; ?>/?module=SMSGateway&page=sendsms">SMS Instant</a></li>
							<li role="presentation"><a href="<?php echo URL; ?>/?module=SMSGateway&page=sendsms&op=broadcast">Broadcast</a></li>
							<li role="presentation" class="active"><a href="<?php echo URL; ?>/?module=SMSGateway&page=sendsms&op=autoreply">Auto Reply</a></li>
					</ul>
					<form method="post" enctype="multipart/form-data" action="sendsms.php?op=autoreply&action=proses">
					Pilih file source<br>
					<input type="hidden" name="MAX_FILE_SIZE" value="20000000">
					<input name="userfile" type="file" size="50"> <input name="upload" type="submit" value="Import Data"></td>
					</form>
					<p>&nbsp;</p>
					<h3><small>Daftar Keyword</small></h3>
					<br>
					<table border='1' width='100%'>
					<tr><th>NO</th><th>KEYWORD</th><th>ACTION</th></tr>
					<?php

					if ($action == 'delete')
					{
					   $key = $_GET['key'];
					   $query = "DELETE FROM sms_data WHERE keyword = '$key'";
					   query($query);
					   $query = "DELETE FROM sms_keyword WHERE keyword = '$key'";
					   query($query);
					   echo "<br><p>Data Auto Reply Keyword ".$key." Sudah Dihapus</p>";
					}
					else if ($action == 'proses')
					{
					    error_reporting(E_ALL ^ E_NOTICE);
						require_once 'excel_reader2.php';

						$data = new Spreadsheet_Excel_Reader($_FILES['userfile']['tmp_name']);

						$baris = $data->rowcount($sheet_index=0);
						$kolom = $data->colcount($sheet_index=0);

						$sukses = 0;
						$gagal = 0;

						for ($i=2; $i<=$baris; $i++)
						{
						    $keyword = str_replace(" ", "", strtoupper($data->val($i, 1)));
							$key = strtoupper($data->val($i, 2));
							$field1 = $data->val($i, 3);
							$field2 = $data->val($i, 4);
							$field3 = $data->val($i, 5);
							$field4 = $data->val($i, 6);
							$field5 = $data->val($i, 7);

							if (($keyword != '') && ($key != ''))
							{
							$query = "INSERT INTO sms_data VALUES ('$keyword', '$key', '$field1', '$field2', '$field3', '$field4', '$field5')";
							$hasil = query($query);
							if ($hasil) $sukses++;
							else $gagal++;
							$katakunci = $keyword;
							}
						}
					    echo "<br><p>Data telah diimport</p>";
						echo "<p>Jumlah Data: ".($gagal+$sukses).", Jumlah Data Sukses Diimport: ".$sukses.", Jumlah Data Gagal Diimport: ".$gagal."</p>";

						$query = "INSERT INTO sms_keyword VALUES ('$katakunci', '')";
						query($query);

					}


					$query = "SELECT keyword FROM sms_keyword ORDER BY keyword";
					$hasil = query($query);
					$i = 1;
					while ($data = fetch_array($hasil))
					{
					   echo "<tr><td>".$i."</td><td>".$data['keyword']."</td><td> <a href='".URL."/?module=SMSGateway&page=sendsms&op=autoreply&action=view&key=".$data['keyword']."'>View Data</a> | <a href='".URL."/?module=SMSGateway&page=sendsms&op=autoreply&action=template&key=".$data['keyword']."'>Set Template</a> | <a href='".URL."/?module=SMSGateway&page=sendsms&op=autoreply&action=delete&key=".$data['keyword']."'>Hapus Data</a></td></tr>";
					   $i++;
					}
					echo "</table>";

					if ($action == 'view')
					{
					   $key = $_GET['key'];
					   $query = "SELECT * FROM sms_data WHERE keyword = '$key'";
					   $hasil = query($query);
					   $i = 1;
					   echo "<br><br><table border='1' width='100%'>";
					   echo "<tr><th>NO</th><th>KEYWORD</th><th>KEY</th><th>FIELD1</th><th>FIELD2</th><th>FIELD3</th><th>FIELD4</th><th>FIELD5</th></tr>";
					   while ($data = fetch_array($hasil))
					   {
					     echo "<tr><td>".$i."</td><td>".$data['keyword']."</td><td>".$data['key']."</td><td>".$data['field1']."</td><td>".$data['field2']."</td><td>".$data['field3']."</td><td>".$data['field4']."</td><td>".$data['field5']."</td></tr>";
						 $i++;
					   }
					   echo "</table>";

					}
					else if ($action == 'updatetemplate')
					{
					   $key = $_POST['key'];
					   $template = $_POST['template'];
					   $query = "UPDATE sms_keyword SET template = '$template' WHERE keyword = '$key'";
					   query($query);
					   echo "<br><p>Template sudah diupdate</p>";
					}
					else if ($action == 'template')
					{
					   $key = $_GET['key'];
					   $query = "SELECT * FROM sms_keyword WHERE keyword = '$key'";
					   $hasil = query($query);
					   $data = fetch_array($hasil);
					   $template = $data['template'];
					   echo "<br><br><p><b>SET TEMPLATE KEYWORD : ".$key."</b></p>";
					?>
					   <form method="post" action="sendsms.php?op=autoreply&action=updatetemplate">
					   <textarea cols="50" rows="5" name="template"><?php echo $template; ?></textarea><br><br>
					   <input type="hidden" name="key" value="<?php echo $key; ?>">
					   <input type="submit" name="submit" value="Simpan">
					   </form>
					<?php
					}
					?>
				</div>
		</div>
		<?php
		}
		?>
<?php } ?>
