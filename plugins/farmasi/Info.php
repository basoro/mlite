<?php

return [
    'name'          =>  'Farmasi',
    'description'   =>  'Pengelolaan data gudang farmasi.',
    'author'        =>  'Basoro',
    'version'       =>  '1.1',
    'compatibility' =>  '4.0.*',
    'icon'          =>  'medkit',
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'deporalan', '-')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'igd', '-')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'deporanap', '-')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'gudang', '-')");
    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'farmasi'");
    }
];
