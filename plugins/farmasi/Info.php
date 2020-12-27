<?php
return [
    'name'          =>  'Farmasi',
    'description'   =>  'Pengelolaan data gudang farmasi.',
    'author'        =>  'Basoro',
    'version'       =>  '1.1',
    'compatibility' =>  '1.0.*',
    'icon'          =>  'medkit',

    'install'       =>  function () use ($core) {

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `metode_racik` (
          `kd_racik` varchar(3) NOT NULL,
          `nm_racik` varchar(30) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'deporalan', '-')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'deporanap', '-')");
    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'farmasi'");
    }
];
