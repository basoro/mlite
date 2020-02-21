<body onload="ajax()"></body>
    <?php
    $op = isset($_GET['op'])?$_GET['op']:null;
    $action = isset($_GET['action'])?$_GET['action']:null;
    if ($op !== 'install') {
      if(num_rows(query("SHOW TABLES LIKE 'sms_inbox'")) !== 1) {
      	echo '<div class="alert bg-pink alert-dismissible text-center">';
      	echo '<p class="lead">Belum terinstall Database SMS Gateway</p>';
      	echo '<a href="'.URL.'/index.php?module=SMSGateway&page=index&op=install" class="btn btn-lg btn-primary m-t-20">Install Sekarang</a>';
      	echo '</div>';
      }
    }

    if (!$op) {
    ?>
    <?php display_message(); ?>
    <div class="card">
      <div class="header">
          <h2>SMS Gateway</h2>
      </div>
      <div class="body">
        <div class="lead m-t-20 m-b-10">Fitur Utama:</div>
        <ul>
           <li>Manajemen nomor telepon pasien dan karyawan</li>
           <li>Manajemen berdasarkan group jabatan</li>
           <li>Manajemen INBOX SMS</li>
           <li>Reply SMS INBOX</li>
           <li>Manajemen Auto Responder<br>Mendukung pesan SMS secara terjadwal seperti halnya auto responder di internet marketing, berdasarkan group</li>
           <li>Personalisasi SMS <br>Pesan SMS yang dikirimkan bisa berisi nama masing-masing pemilik nomor, sesuai yang ada di daftar pasien atau karyawan.</li>
           <li>Support pendaftaran pasien baru via SMS <br>Seseorang bisa melakukan registrasi pasien baru ke dalam daftar pasien melalui SMS</li>
           <li>Auto Confirm pendaftaran pasien baru via SMS <br>Seseorang yang telah melakukan registrasi via SMS akan mendapat balasan atau konfirmasi otomatis via SMS juga</li>
           <li>Customizable Auto Confirm SMS Message<br>Isi pesan konfirmasi ketika registrasi phonebook bisa diatur sendiri.</li>
           <li>Kirim SMS Instant ke semua nomor atau berdasar group</li>
           <li>On Scheduled SMS ke semua nomor atau berdasar group</li>
           <li>Support Long Text SMS Sending and Receive (unlimited character)</li>
           <li>SMS Sending Report</li>
           <li>SMS Autoforward</li>
        </ul>
      </div>
    </div>
    <?php
    } else if ($op == 'install') {
      $sql_sms_gateway = "CREATE TABLE `sms_inbox` (
        `id` int(11) NOT NULL,
        `msg` text,
        `sender` varchar(20) DEFAULT NULL,
        `time` datetime DEFAULT NULL,
        `flagRead` int(11) DEFAULT NULL,
        `flagReply` int(11) DEFAULT NULL
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
      ALTER TABLE `sms_inbox`
        ADD PRIMARY KEY (`id`);
      ALTER TABLE `sms_inbox`
        MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
      ";

      if(mysqli_multi_query($connection,$sql_sms_gateway)){
          set_message ('Table created successfully.');
          redirect ('./index.php?module=SMSGateway&page=index');
      } else{
          echo "ERROR: Could not able to execute $sql. " . mysqli_error($con);
      }
    }
    ?>
  </div>
</div>
