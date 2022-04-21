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
                `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT,
                `dir` text NOT NULL,
                `sequence` integer DEFAULT 0
            )");
    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DROP TABLE IF EXISTS `mlite_modules`");
    }
];
