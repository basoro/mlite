<?php

return [
    'name'          =>  'Dashboard',
    'description'   =>  'Statistik, grafik dan akses modul.',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '3.*',
    'icon'          =>  'home',
    'pages'         =>  ['Dashboard' => 'dashboard'],
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('dashboard', 'umum', 'UMU')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('dashboard', 'bpjs', 'BPJ')");
    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `lite_options` WHERE `module` = 'dashboard'");
    }
];
