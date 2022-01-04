<?php

return [
    'name'          =>  'Modul-Modul',
    'description'   =>  'Pengelolaan modul',
    'author'        =>  'Basoro.ID',
    'version'       =>  '1.1',
    'compatibility' =>  '2022',
    'icon'          =>  'plug',

    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_modules` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `dir` text,
            `sequence` text
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DROP TABLE IF EXISTS `mlite_modules`");
    }
];
