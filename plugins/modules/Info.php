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
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `dir` text,
                `sequence` text
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

    }
];
