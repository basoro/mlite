<?php
return [
    'name'          =>  'Anjungan',
    'description'   =>  'Modul anjungan pasien rawat jalan',
    'author'        =>  'Sruu.pl',
    'version'       =>  '1.0',
    'compatibility' =>  '3.*',
    'icon'          =>  'desktop',
    'pages'            =>  ['Anjungan Pasien Mandiri' => 'anjungan'],
    'install'       =>  function () use ($core) {
      $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('anjungan', 'display_poli', '')");
      $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('anjungan', 'carabayar_umum', '')");
      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `lite_antrian_loket` (
        `kd` int(50) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `type` varchar(50) NOT NULL,
        `noantrian` varchar(50) NOT NULL,
        `postdate` date NOT NULL,
        `start_time` time NOT NULL,
        `end_time` time NOT NULL DEFAULT '00:00:00'
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

    },
    'uninstall'     =>  function () use ($core) {
      $core->db()->pdo()->exec("DELETE FROM `lite_options` WHERE `module` = 'anjungan'");
    }
];
