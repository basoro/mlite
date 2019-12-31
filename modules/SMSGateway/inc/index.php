<body onload="ajax()"></body>
    <?php
    $op = isset($_GET['op'])?$_GET['op']:null;
    $action = isset($_GET['action'])?$_GET['action']:null;
    if ($op !== 'install') {
      if(num_rows(query("SHOW TABLES LIKE 'sms_inbox'")) !== 1) {
      	echo '<div class="alert bg-pink alert-dismissible text-center">';
      	echo '<p class="lead">Belum terinstall Database SMS Gateway</p>';
      	echo '<a href="'.URL.'/?module=SMSGateway&page=index&op=install" class="btn btn-lg btn-primary m-t-20">Install Sekarang</a>';
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
        <ul class="nav nav-tabs tab-nav-right" role="tablist">
            <li role="presentation" class="active"><a href="<?php echo URL; ?>/?module=SMSGateway&page=index">Dashboard</a></li>
            <li role="presentation"><a href="<?php echo URL; ?>/?module=SMSGateway&page=index&op=config">Pengaturan</a></li>
        </ul>
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
    }
    else if ($op == 'config')
    {
    ?>
    <div class="card">
      <div class="header">
          <h2>SMS Gateway</h2>
      </div>
      <div class="body">
        <ul class="nav nav-tabs tab-nav-right" role="tablist">
            <li role="presentation"><a href="<?php echo URL; ?>/?module=SMSGateway&page=index">Dashboard</a></li>
            <li role="presentation" class="active"><a href="<?php echo URL; ?>/?module=SMSGateway&page=index&op=config">Pengaturan</a></li>
        </ul>
        <?php
          include ABSPATH."/modules/SMSGateway/sms-config.php";

          if ($action == 'proses') {
            $path = $_POST['path'];
            $msgREG = $_POST['regsukses'];
            $msgErrorREG = $_POST['reggagal'];
            $msgFWD = $_POST['fwdsukses'];
            $msgErrorFWD = $_POST['fwdgagal'];
            $msgINBOX = $_POST['smsinbox'];
            $msgErrorData = $_POST['errordata'];
            $msgErrorKeyword = $_POST['errorkeyword'];
            $msgErrorInfo = $_POST['errorinfo'];
            $defaultID = $_POST['groupid'];


            $path = "\$path = \"".$path."\";\n";
            $msgREG = "\$msgREG = \"".$msgREG."\";\n";
            $msgErrorREG = "\$msgErrorREG = \"".$msgErrorREG."\";\n";
            $msgFWD = "\$msgFWD = \"".$msgFWD."\";\n";
            $msgErrorFWD = "\$msgErrorFWD = \"".$msgErrorFWD."\";\n";
            $msgINBOX = "\$msgINBOX = \"".$msgINBOX."\";\n";
            $msgErrorData = "\$msgErrorData = \"".$msgErrorData."\";\n";
            $msgErrorKeyword = "\$msgErrorKeyword = \"".$msgErrorKeyword."\";\n";
            $msgErrorInfo = "\$msgErrorInfo = \"".$msgErrorInfo."\";\n";
            $defaultID = "\$defaultID = \"".$defaultID."\";\n";

            $file = "modules/SMSGateway/sms-config.php";

            $arrayRead = file($file);

            $arrayRead[1] = $path;
            $arrayRead[2] = $msgREG;
            $arrayRead[3] = $msgErrorREG;
            $arrayRead[4] = $msgFWD;
            $arrayRead[5] = $msgErrorFWD;
            $arrayRead[6] = $msgINBOX;
            $arrayRead[7] = $msgErrorData;
            $arrayRead[8] = $msgErrorKeyword;
            $arrayRead[9] = $msgErrorInfo;
            $arrayRead[10] = $defaultID;

            $simpan = file_put_contents($file, implode($arrayRead));
            echo "<p>Konfigurasi sudah tersimpan</p>";
          } else {
          ?>
            <p class="lead m-t-20 m-b-10">Setting Konfigurasi</p>
            <form class="form-horizontal" method="post" action="<?php echo URL; ?>/?module=SMSGateway&page=index&op=config&action=proses">
              <div class="row clearfix">
                  <div class="col-lg-3 col-md-3 col-sm-3 col-xs-5 form-control-label">
                      <label for="path">Path ke folder Gammu</label>
                  </div>
                  <div class="col-lg-9 col-md-9 col-sm-8 col-xs-7">
                      <div class="form-group">
                          <div class="form-line">
                              <input type="text" id="path" class="form-control" value="<?php echo $path; ?>">
                          </div>
                      </div>
                  </div>
              </div>
              <div class="row clearfix">
                  <div class="col-lg-3 col-md-3 col-sm-3 col-xs-5 form-control-label">
                      <label for="reg_sukses">Reply REG (sukses)</label>
                  </div>
                  <div class="col-lg-9 col-md-9 col-sm-8 col-xs-7">
                      <div class="form-group">
                          <div class="form-line">
                              <input type="text" id="reg_sukses" class="form-control" value="<?php echo $msgREG; ?>">
                          </div>
                      </div>
                  </div>
              </div>
              <div class="row clearfix">
                  <div class="col-lg-3 col-md-3 col-sm-3 col-xs-5 form-control-label">
                      <label for="reg_gagal">Reply REG (gagal)</label>
                  </div>
                  <div class="col-lg-9 col-md-9 col-sm-8 col-xs-7">
                      <div class="form-group">
                          <div class="form-line">
                              <input type="text" id="reg_gagal" class="form-control" value="<?php echo $msgErrorREG; ?>">
                          </div>
                      </div>
                  </div>
              </div>
              <div class="row clearfix">
                  <div class="col-lg-3 col-md-3 col-sm-3 col-xs-5 form-control-label">
                      <label for="fwd_sukses">Reply FWD (sukses)</label>
                  </div>
                  <div class="col-lg-9 col-md-9 col-sm-8 col-xs-7">
                      <div class="form-group">
                          <div class="form-line">
                              <input type="text" id="fwd_sukses" class="form-control" value="<?php echo $msgFWD; ?>">
                          </div>
                      </div>
                  </div>
              </div>
              <div class="row clearfix">
                  <div class="col-lg-3 col-md-3 col-sm-3 col-xs-5 form-control-label">
                      <label for="fwd_gagal">Reply FWD (gagal)</label>
                  </div>
                  <div class="col-lg-9 col-md-9 col-sm-8 col-xs-7">
                      <div class="form-group">
                          <div class="form-line">
                              <input type="text" id="fwd_gagal" class="form-control" value="<?php echo $msgErrorFWD; ?>">
                          </div>
                      </div>
                  </div>
              </div>
              <div class="row clearfix">
                  <div class="col-lg-3 col-md-3 col-sm-3 col-xs-5 form-control-label">
                      <label for="inbox">Reply SMS Inbox</label>
                  </div>
                  <div class="col-lg-9 col-md-9 col-sm-8 col-xs-7">
                      <div class="form-group">
                          <div class="form-line">
                              <input type="text" id="inbox" class="form-control" value="<?php echo $msgINBOX; ?>">
                          </div>
                      </div>
                  </div>
              </div>
              <div class="row clearfix">
                  <div class="col-lg-3 col-md-3 col-sm-3 col-xs-5 form-control-label">
                      <label for="info_error_data">Reply INFO (Error Data)</label>
                  </div>
                  <div class="col-lg-9 col-md-9 col-sm-8 col-xs-7">
                      <div class="form-group">
                          <div class="form-line">
                              <input type="text" id="info_error_data" class="form-control" value="<?php echo $msgErrorData; ?>">
                          </div>
                      </div>
                  </div>
              </div>
              <div class="row clearfix">
                  <div class="col-lg-3 col-md-3 col-sm-3 col-xs-5 form-control-label">
                      <label for="info_error_keyword">Reply INFO (Error Keyword)</label>
                  </div>
                  <div class="col-lg-9 col-md-9 col-sm-8 col-xs-7">
                      <div class="form-group">
                          <div class="form-line">
                              <input type="text" id="info_error_keyword" class="form-control" value="<?php echo $msgErrorKeyword; ?>">
                          </div>
                      </div>
                  </div>
              </div>
              <div class="row clearfix">
                  <div class="col-lg-3 col-md-3 col-sm-3 col-xs-5 form-control-label">
                      <label for="info_error_info">Reply INFO (Error Info)</label>
                  </div>
                  <div class="col-lg-9 col-md-9 col-sm-8 col-xs-7">
                      <div class="form-group">
                          <div class="form-line">
                              <input type="text" id="info_error_info" class="form-control" value="<?php echo $msgErrorInfo; ?>">
                          </div>
                      </div>
                  </div>
              </div>
              <div class="row clearfix">
                  <div class="col-lg-3 col-md-3 col-sm-3 col-xs-5 form-control-label">
                      <label for="default_group">Default ID Group</label>
                  </div>
                  <div class="col-lg-9 col-md-9 col-sm-8 col-xs-7">
                      <div class="form-group">
                          <div class="form-line">
                              <input type="text" id="default_group" class="form-control" value="<?php echo $defaultID; ?>">
                          </div>
                      </div>
                  </div>
              </div>
              <div class="row clearfix">
                  <div class="col-lg-offset-3 col-md-offset-3 col-sm-offset-3 col-xs-offset-5">
                      <button type="button" name="proses" class="btn btn-primary m-t-15 waves-effect">SIMPAN</button>
                  </div>
              </div>
            </form>
          <?php
          }
        ?>
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
      CREATE TABLE `sms_outbox` (
        `id` int(11) NOT NULL,
        `msg` text,
        `destinaton` varchar(20) DEFAULT NULL,
        `time` datetime DEFAULT NULL,
        `status` varchar(11) DEFAULT NULL
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
      ALTER TABLE `sms_outbox`
        ADD PRIMARY KEY (`id`);
      ALTER TABLE `sms_outbox`
        MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

/*      CREATE TABLE `sms_autolist` (
        `phoneNumber` varchar(15) NOT NULL DEFAULT '',
        `id` int(11) NOT NULL DEFAULT '0',
        `status` int(11) DEFAULT NULL
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
      CREATE TABLE `sms_autoresponder` (
        `id` int(11) NOT NULL,
        `msg` text,
        `interv` int(11) DEFAULT NULL,
        `idgroup` int(11) DEFAULT NULL
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
      CREATE TABLE `sms_data` (
        `keyword` varchar(20) NOT NULL DEFAULT '',
        `key` varchar(100) NOT NULL DEFAULT '',
        `field1` varchar(100) DEFAULT NULL,
        `field2` varchar(100) DEFAULT NULL,
        `field3` varchar(100) DEFAULT NULL,
        `field4` varchar(100) DEFAULT NULL,
        `field5` varchar(100) DEFAULT NULL
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
      CREATE TABLE `sms_keyword` (
        `keyword` varchar(100) NOT NULL DEFAULT '',
        `template` varchar(500) DEFAULT NULL
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
      CREATE TABLE `sms_message` (
        `id` int(11) NOT NULL,
        `message` text,
        `pubdate` datetime DEFAULT NULL,
        `status` int(11) DEFAULT NULL,
        `idgroup` int(11) DEFAULT NULL
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
      CREATE TABLE `sms_sentmsg` (
        `id` int(11) NOT NULL,
        `msg` text
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
      ALTER TABLE `sms_autolist`
        ADD PRIMARY KEY (`phoneNumber`,`id`);
      ALTER TABLE `sms_autoresponder`
        ADD PRIMARY KEY (`id`);
      ALTER TABLE `sms_data`
        ADD PRIMARY KEY (`keyword`,`key`);
      ALTER TABLE `sms_keyword`
        ADD PRIMARY KEY (`keyword`);
      ALTER TABLE `sms_message`
        ADD PRIMARY KEY (`id`);
      ALTER TABLE `sms_sentmsg`
        ADD PRIMARY KEY (`id`);
      ALTER TABLE `sms_autoresponder`
        MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
      ALTER TABLE `sms_message`
        MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
      ALTER TABLE `sms_sentmsg`
        MODIFY `id` int(11) NOT NULL AUTO_INCREMENT; */
      ";

      if(mysqli_multi_query($connection,$sql_sms_gateway)){
          set_message ('Table created successfully.');
          redirect ('./?module=SMSGateway&page=index');
      } else{
          echo "ERROR: Could not able to execute $sql. " . mysqli_error($con);
      }
    }
    ?>
  </div>
</div>
