<?php

return [
    'name'          =>  'Modul-Modul',
    'description'   =>  'Pengelolaan modul',
    'author'        =>  'Basoro.ID',
    'version'       =>  '1.1',
    'compatibility' =>  '2023',
    'icon'          =>  'plug',

    'install'       =>  function () use ($core) {
        if(MULTI_APP) {
            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_modules` (
                `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT,
                `dir` text NOT NULL,
                `sequence` integer DEFAULT 0
            )");
        }
    },
    'uninstall'     =>  function () use ($core) {
    }
];
