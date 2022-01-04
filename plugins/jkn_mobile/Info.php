<?php
return [
    'name'          =>  'JKN Mobile',
    'description'   =>  'Modul Khanza JKN Mobile API',
    'author'        =>  'Basoro',
    'version'       =>  '1.1',
    'compatibility' =>  '2022',
    'icon'          =>  'tasks',
    'pages'         =>  ['JKN Mobile' => 'jknmobile'],
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'username', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'password', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'header', 'X-Token')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'autoregis', '0')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'display', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kdprop', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kdkab', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kdkec', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kdkel', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'perusahaan_pasien', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'suku_bangsa', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'bahasa_pasien', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'cacat_fisik', '')");

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_antrian_loket` (
          `kd` int(50) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `type` varchar(50) NOT NULL,
          `noantrian` varchar(50) NOT NULL,
          `postdate` date NOT NULL,
          `start_time` time NOT NULL,
          `end_time` time NOT NULL DEFAULT '00:00:00'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_antrian_referensi` (
          `tanggal_periksa` date NOT NULL,
          `nomor_kartu` varchar(50) NOT NULL,
          `nomor_referensi` varchar(50) NOT NULL PRIMARY KEY
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

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
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'jkn_mobile'");
    }
];
