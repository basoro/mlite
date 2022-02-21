<?php
return [
    'name'          =>  'API',
    'description'   =>  'Katalog API KhanzaLITE',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2022',
    'icon'          =>  'database',
    'pages'         =>  ['API KhanzaLITE' => 'api'],
    'install'       =>  function () use ($core) {

      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_pengaduan` (
        `id` varchar(15) NOT NULL,
        `tanggal` datetime NOT NULL,
        `no_rkm_medis` varchar(15) NOT NULL,
        `pesan` varchar(255) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

      $core->db()->pdo()->exec("ALTER TABLE `mlite_pengaduan`
        ADD PRIMARY KEY (`id`),
        ADD KEY `no_rkm_medis` (`no_rkm_medis`);");

      $core->db()->pdo()->exec("ALTER TABLE `mlite_pengaduan`
        ADD CONSTRAINT `mlite_pengaduan_ibfk_1` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON DELETE CASCADE ON UPDATE CASCADE;");


      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_pengaduan_detail` (
        `id` int(10) NOT NULL,
        `pengaduan_id` varchar(15) NOT NULL,
        `tanggal` datetime NOT NULL,
        `no_rkm_medis` varchar(15) NOT NULL,
        `pesan` varchar(225) DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;");

      $core->db()->pdo()->exec("ALTER TABLE `mlite_pengaduan_detail`
        ADD PRIMARY KEY (`id`) USING BTREE,
        ADD KEY `pengaduan_detail_ibfk_1` (`pengaduan_id`);");

      $core->db()->pdo()->exec("ALTER TABLE `mlite_pengaduan_detail`
        MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;");

      $core->db()->pdo()->exec("ALTER TABLE `mlite_pengaduan_detail`
        ADD CONSTRAINT `mlite_pengaduan_detail_ibfk_1` FOREIGN KEY (`pengaduan_id`) REFERENCES `mlite_pengaduan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_notifications` (
        `id` int(11) NOT NULL,
        `judul` varchar(250) NOT NULL,
        `pesan` text NOT NULL,
        `tanggal` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `no_rkm_medis` varchar(255) NOT NULL,
        `status` varchar(250) NOT NULL DEFAULT 'unread'
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

      $core->db()->pdo()->exec("ALTER TABLE `mlite_notifications`
        ADD PRIMARY KEY (`id`);");

      $core->db()->pdo()->exec("ALTER TABLE `mlite_notifications`
        MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");

      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_apamregister` (
        `nama_lengkap` varchar(225) NOT NULL,
        `email` varchar(225) NOT NULL,
        `nomor_ktp` varchar(225) NOT NULL,
        `nomor_telepon` varchar(225) DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;");

      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_duitku` (
        `id` varchar(10) NOT NULL,
        `tanggal` datetime NOT NULL,
        `no_rkm_medis` varchar(15) NOT NULL,
        `paymentUrl` varchar(255) NOT NULL,
        `merchantCode` varchar(255) NOT NULL,
        `reference` varchar(255) NOT NULL,
        `vaNumber` varchar(255) NOT NULL,
        `amount` varchar(255) NOT NULL,
        `statusCode` varchar(255) NOT NULL,
        `statusMessage` varchar(255) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

      $core->db()->pdo()->exec("ALTER TABLE `mlite_duitku`
        ADD PRIMARY KEY (`id`),
        ADD KEY `reference` (`reference`);");

      $core->db()->pdo()->exec("ALTER TABLE `mlite_duitku`
        MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;");

      $core->db()->pdo()->exec("ALTER TABLE `mlite_duitku`
        ADD CONSTRAINT `mlite_duitku_ibfk_1` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON DELETE CASCADE ON UPDATE CASCADE;");

      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_key', 'qtbexUAxzqO3M8dCOo2vDMFvgYjdUEdMLVo341')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_status_daftar', 'Terdaftar')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_status_dilayani', 'Anda siap dilayani')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_webappsurl', 'http://localhost/webapps/')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_normpetugas', '000001,000002')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_limit', '2')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_smtp_host', 'ssl://smtp.gmail.com')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_smtp_port', '465')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_smtp_username', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_smtp_password', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_kdpj', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_kdprop', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_kdkab', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_kdkec', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'duitku_merchantCode', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'duitku_merchantKey', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'duitku_paymentAmount', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'duitku_paymentMethod', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'duitku_productDetails', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'duitku_expiryPeriod', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'duitku_kdpj', '')");
    },
    'uninstall'     =>  function () use ($core) {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'api'");
    }
];
