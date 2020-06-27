<?php
return [
    'name'          =>  'Farmasi',
    'description'   =>  'Pengelolaan data gudang farmasi.',
    'author'        =>  'Basoro',
    'version'       =>  '1.1',
    'compatibility' =>  '3.*',
    'icon'          =>  'medkit',

    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('farmasi', 'deporalan', '-')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('farmasi', 'deporanap', '-')");
    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `lite_options` WHERE `module` = 'farmasi'");
    }
];
