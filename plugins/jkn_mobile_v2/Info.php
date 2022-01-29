<?php
return [
    'name'          =>  'JKN Mobile V2',
    'description'   =>  'Modul Khanza JKN Mobile API Versi 2',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2022',
    'icon'          =>  'tasks',
    'pages'         =>  ['JKN Mobile' => 'jknmobile_v2'],
    'install'       =>  function () use ($core) {
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'x_username', 'jkn')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'x_password', 'mobile')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'header_token', 'X-Token')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'header_username', 'X-Username')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'header_password', 'X-Password')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'BpjsConsID', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'BpjsSecretKey', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'BpjsUserKey', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'BpjsAntrianUrl', 'https://dvlp.bpjs-kesehatan.go.id:8887/arsws/rest/v1/')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'display', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'kdprop', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'kdkab', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'kdkec', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'kdkel', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'perusahaan_pasien', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'suku_bangsa', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'bahasa_pasien', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_v2', 'cacat_fisik', '')");

      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_antrian_referensi` (
        `tanggal_periksa` date NOT NULL,
        `nomor_kartu` varchar(50) NOT NULL,
        `nomor_referensi` varchar(50) NOT NULL PRIMARY KEY,
        `jenis_kunjungan` varchar(10) NOT NULL,
        `status_kirim` varchar(20) DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_antrian_referensi_batal` (
        `tanggal_batal` date NOT NULL,
        `nomor_referensi` varchar(50) NOT NULL,
        `keterangan` varchar(250) NOT NULL PRIMARY KEY
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `maping_dokter_dpjpvclaim` (
        `kd_dokter` varchar(20) NOT NULL,
        `kd_dokter_bpjs` varchar(20) DEFAULT NULL,
        `nm_dokter_bpjs` varchar(50) DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;");

      $core->db()->pdo()->exec("ALTER TABLE `maping_dokter_dpjpvclaim`
        ADD PRIMARY KEY (`kd_dokter`) USING BTREE;");

      $core->db()->pdo()->exec("ALTER TABLE `maping_dokter_dpjpvclaim`
        ADD CONSTRAINT `maping_dokter_dpjpvclaim_ibfk_1` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE;");

      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `maping_poli_bpjs` (
        `kd_poli_rs` varchar(5) NOT NULL,
        `kd_poli_bpjs` varchar(15) NOT NULL,
        `nm_poli_bpjs` varchar(40) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

      $core->db()->pdo()->exec("ALTER TABLE `maping_poli_bpjs`
        ADD PRIMARY KEY (`kd_poli_rs`);");

      $core->db()->pdo()->exec("ALTER TABLE `maping_poli_bpjs`
        ADD CONSTRAINT `maping_poli_bpjs_ibfk_1` FOREIGN KEY (`kd_poli_rs`) REFERENCES `poliklinik` (`kd_poli`) ON DELETE CASCADE ON UPDATE CASCADE;");

    },
    'uninstall'     =>  function () use ($core) {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'jkn_mobile_v2'");
    }
];
