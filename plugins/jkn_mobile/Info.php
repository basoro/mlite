<?php

return [
    'name'          =>  'JKN Mobile',
    'description'   =>  'Modul mLITE JKN Mobile API',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2023',
    'icon'          =>  'tasks',
    'pages'         =>  ['JKN Mobile' => 'jknmobile'],
    'install'       =>  function () use ($core) {
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'x_username', 'jkn')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'x_password', 'mobile')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'header_token', 'X-Token')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'header_username', 'X-Username')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'header_password', 'X-Password')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'BpjsConsID', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'BpjsSecretKey', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'BpjsUserKey', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'BpjsAntrianUrl', 'https://apijkn-dev.bpjs-kesehatan.go.id/antreanrs_dev/')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kd_pj_bpjs', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'exclude_taskid', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'display', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kdprop', '1')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kdkab', '1')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kdkec', '1')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kdkel', '1')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'perusahaan_pasien', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'suku_bangsa', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'bahasa_pasien', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'cacat_fisik', '')");

      $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_antrian_referensi` (
        `tanggal_periksa` date NOT NULL,
        `nomor_kartu` varchar(50) NOT NULL,
        `nomor_referensi` varchar(50) NOT NULL PRIMARY KEY,
        `kodebooking` varchar(100) NOT NULL,
        `jenis_kunjungan` varchar(10) NOT NULL,
        `status_kirim` varchar(20) DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_antrian_referensi_batal` (
        `tanggal_batal` date NOT NULL,
        `nomor_referensi` varchar(50) NOT NULL,
        `kodebooking` varchar(100) NOT NULL,
        `keterangan` varchar(250) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_antrian_referensi_taskid` (
        `tanggal_periksa` date NOT NULL,
        `nomor_referensi` varchar(50) NOT NULL,
        `taskid` varchar(50) NOT NULL,
        `waktu` varchar(50) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `maping_dokter_dpjpvclaim` (
        `kd_dokter` varchar(20) NOT NULL,
        `kd_dokter_bpjs` varchar(20) DEFAULT NULL,
        `nm_dokter_bpjs` varchar(50) DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;");

      $core->mysql()->pdo()->exec("ALTER TABLE `maping_dokter_dpjpvclaim`
        ADD PRIMARY KEY (`kd_dokter`) USING BTREE;");

      $core->mysql()->pdo()->exec("ALTER TABLE `maping_dokter_dpjpvclaim`
        ADD CONSTRAINT `maping_dokter_dpjpvclaim_ibfk_1` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE;");

      $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `maping_poli_bpjs` (
        `kd_poli_rs` varchar(5) NOT NULL,
        `kd_poli_bpjs` varchar(15) NOT NULL,
        `nm_poli_bpjs` varchar(40) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

      $core->mysql()->pdo()->exec("ALTER TABLE `maping_poli_bpjs`
        ADD PRIMARY KEY (`kd_poli_rs`);");

      $core->mysql()->pdo()->exec("ALTER TABLE `maping_poli_bpjs`
        ADD CONSTRAINT `maping_poli_bpjs_ibfk_1` FOREIGN KEY (`kd_poli_rs`) REFERENCES `poliklinik` (`kd_poli`) ON DELETE CASCADE ON UPDATE CASCADE;");

    },
    'uninstall'     =>  function () use ($core) {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'jkn_mobile'");
    }
];
