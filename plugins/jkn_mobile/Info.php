<?php
return [
    'name'          =>  'JKN Mobile',
    'description'   =>  'Modul Khanza JKN Mobile API',
    'author'        =>  'Basoro',
    'version'       =>  '1.1',
    'compatibility' =>  '3.*',
    'icon'          =>  'tasks',
    'pages'         =>  ['JKN Mobile' => 'jknmobile'],
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'username', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'password', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'header', 'X-Token')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'autoregis', '0')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'display', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kdprop', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kdkab', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kdkec', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kdkel', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'perusahaan_pasien', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'suku_bangsa', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'bahasa_pasien', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'cacat_fisik', '')");

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `lite_antrian_loket` (
          `kd` int(50) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `type` varchar(50) NOT NULL,
          `noantrian` varchar(50) NOT NULL,
          `postdate` date NOT NULL,
          `start_time` time NOT NULL,
          `end_time` time NOT NULL DEFAULT '00:00:00'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `lite_antrian_referensi` (
          `tanggal_periksa` date NOT NULL,
          `nomor_kartu` varchar(50) NOT NULL,
          `nomor_referensi` varchar(50) NOT NULL PRIMARY KEY
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `lite_options` WHERE `module` = 'jkn_mobile'");
    }
];
