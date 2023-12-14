<?php
return [
    'name'          =>  'JKN Mobile FKTP',
    'description'   =>  'Modul JKN Mobile API untuk FKTP',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '4.0.*',
    'icon'          =>  'tasks',
    'pages'         =>  ['JKN Mobile FKTP' => 'jknmobilefktp'],
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_fktp', 'username', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_fktp', 'password', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_fktp', 'header', 'X-Token')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_fktp', 'header_username', 'X-Username')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_fktp', 'header_password', 'X-Password')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_fktp', 'kd_pj', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_fktp', 'hari', '3')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile_fktp', 'display', '')");

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `maping_poliklinik_pcare` (
          `kd_poli_rs` char(5) NOT NULL,
          `kd_poli_pcare` char(5) DEFAULT NULL,
          `nm_poli_pcare` varchar(50) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        $core->db()->pdo()->exec("ALTER TABLE `maping_poliklinik_pcare`
          ADD PRIMARY KEY (`kd_poli_rs`);");

        $core->db()->pdo()->exec("ALTER TABLE `maping_poliklinik_pcare`
          ADD CONSTRAINT `maping_poliklinik_pcare_ibfk_1` FOREIGN KEY (`kd_poli_rs`) REFERENCES `poliklinik` (`kd_poli`) ON DELETE CASCADE ON UPDATE CASCADE;");

    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'jkn_mobile_fktp'");
    }
];
