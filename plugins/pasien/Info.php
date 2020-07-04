<?php

return [
    'name'          =>  'Pasien',
    'description'   =>  'Pengelolaan data pasien.',
    'author'        =>  'Basoro',
    'version'       =>  '1.3',
    'compatibility' =>  '3.*',
    'icon'          =>  'users',
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('pasien', 'ceknoktp', '0')");
    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `lite_options` WHERE `module` = 'pasien'");
    }
];
