<?php

return [
    'name' => 'E-Signature',
    'description' => 'Modul Tanda Tangan Elektronik (TTE) Tersertifikasi',
    'author' => 'Basoro',
    'category' => 'manajemen',
    'version' => '1.0',
    'compatibility' => '6.*.*',
    'icon' => 'sticky-note-o',
    'install' => function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('esignature', 'kode_berkasdigital', '')");

    },
    'uninstall' => function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'esignature'");
    }
];
