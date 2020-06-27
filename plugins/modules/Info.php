<?php

return [
    'name'          =>  'Modul-Modul',
    'description'   =>  'Pengelolaan modul-modul Khanza LITE.',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '3.*',
    'icon'          =>  'plug',

    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `lite_modules` (
                `id` int(11) NOT NULL,
                `dir` text,
                `sequence` text
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec('ALTER TABLE `lite_modules`
            ADD PRIMARY KEY (`id`);');

        $core->db()->pdo()->exec('ALTER TABLE `lite_modules`
            MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');

    }
];
