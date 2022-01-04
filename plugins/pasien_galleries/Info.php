<?php

return [
    'name'          =>  'Galeri Pasien',
    'description'   =>  'Kumpulan data digital pasien',
    'author'        =>  'Basoro',
    'version'       =>  '1.1',
    'compatibility' =>  '2022',
    'icon'          =>  'camera',

    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_pasien_galleries` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` text NOT NULL,
            `slug` text NOT NULL,
            `img_per_page` varchar(50) NOT NULL DEFAULT 0,
            `sort` varchar(50) NOT NULL DEFAULT 'DESC'
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_pasien_galleries_items` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `gallery` varchar(50) NOT NULL,
            `src` text NOT NULL,
            `title` varchar(50) DEFAULT NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        if (!file_exists(UPLOADS.'/pasien_galleries')) {
            mkdir(UPLOADS.'/pasien_galleries', 0755, true);
        }
    },
    'uninstall'     => function () use ($core) {
        $core->db()->pdo()->exec("DROP TABLE `mlite_pasien_galleries`");
        $core->db()->pdo()->exec("DROP TABLE `mlite_pasien_galleries_items`");
        deleteDir(UPLOADS.'/pasien_galleries');
    }
];
