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
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `geolocation_presensi` (
            `id` int(11) NOT NULL PRIMARY KEY,
            `tanggal` date DEFAULT NULL,
            `latitude` varchar(200) NOT NULL,
            `longitude` varchar(200) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec('ALTER TABLE `geolocation_presensi`
            ADD CONSTRAINT `geolocation_presensi_ibfk_1` FOREIGN KEY (`id`) REFERENCES `pegawai` (`id`) ON UPDATE CASCADE;');

        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('dashboard', 'umum', 'UMU')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('dashboard', 'bpjs', 'BPJ')");
    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `lite_options` WHERE `module` = 'dashboard'");
    }
];
