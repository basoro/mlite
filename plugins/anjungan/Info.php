<?php
return [
    'name'          =>  'Anjungan',
    'description'   =>  'Modul anjungan pasien rawat jalan',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2020',
    'icon'          =>  'desktop',
    'pages'            =>  ['Anjungan Pasien Mandiri' => 'anjungan'],
    'install'       =>  function () use ($core) {
      $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('anjungan', 'display_poli', '')");
      $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('anjungan', 'carabayar_umum', '')");
      $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('anjungan', 'antrian_loket', '')");
      $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('anjungan', 'antrian_cs', '')");
      $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('anjungan', 'antrian_prioritas', '')");
      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `lite_antrian_loket` (
        `kd` int(50) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `type` varchar(50) NOT NULL,
        `noantrian` varchar(50) NOT NULL,
        `postdate` date NOT NULL,
        `start_time` time NOT NULL,
        `end_time` time NOT NULL DEFAULT '00:00:00'
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      //$core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `antriloket` (
      //  `loket` int(11) NOT NULL,
      //  `antrian` int(11) NOT NULL
      //) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

      //$core->db()->pdo()->exec("ALTER TABLE `antriloket`
      //  ADD KEY `loket` (`loket`),
      //  ADD KEY `antrian` (`antrian`);");

      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `antrics` (
        `loket` int(11) NOT NULL,
        `antrian` int(11) NOT NULL
      ) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

      $core->db()->pdo()->exec("ALTER TABLE `antrics`
        ADD KEY `loket` (`loket`),
        ADD KEY `antrian` (`antrian`);");

      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `antriprioritas` (
        `loket` int(11) NOT NULL,
        `antrian` int(11) NOT NULL
      ) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

      $core->db()->pdo()->exec("ALTER TABLE `antriprioritas`
        ADD KEY `loket` (`loket`),
        ADD KEY `antrian` (`antrian`);");

    },
    'uninstall'     =>  function () use ($core) {
      $core->db()->pdo()->exec("DELETE FROM `lite_options` WHERE `module` = 'anjungan'");
    }
];
