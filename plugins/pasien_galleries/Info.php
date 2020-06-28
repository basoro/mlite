<?php

return [
    'name'          =>  'Galeri Pasien',
    'description'   =>  'Kumpulan data digital pasien',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '3.*',
    'icon'          =>  'camera',

    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `lite_pasien_galleries` (
            `id` int(11) NOT NULL,
            `name` text NOT NULL,
            `slug` text NOT NULL,
            `img_per_page` varchar(50) NOT NULL DEFAULT 0,
            `sort` varchar(50) NOT NULL DEFAULT 'DESC'
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec('ALTER TABLE `lite_pasien_galleries`
            ADD PRIMARY KEY (`id`);');

        $core->db()->pdo()->exec('ALTER TABLE `lite_pasien_galleries`
            MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `lite_pasien_galleries_items` (
            `id` int(11) NOT NULL,
            `gallery` varchar(50) NOT NULL,
            `src` text NOT NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec('ALTER TABLE `lite_pasien_galleries_items`
            ADD PRIMARY KEY (`id`);');

        $core->db()->pdo()->exec('ALTER TABLE `lite_pasien_galleries_items`
            MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');

        if (!file_exists(UPLOADS.'/pasien_galleries')) {
            mkdir(UPLOADS.'/pasien_galleries', 0755, true);
        }
    },
    'uninstall'     => function () use ($core) {
        $core->db()->pdo()->exec("DROP TABLE `lite_pasien_galleries`");
        $core->db()->pdo()->exec("DROP TABLE `lite_pasien_galleries_items`");
        deleteDir(UPLOADS.'/pasien_galleries');
    }
];
