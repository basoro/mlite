<?php

    return [
        'name'          =>  'Keuangan',
        'description'   =>  'Modul Keuangan untuk KhanzaLITE',
        'author'        =>  'Basoro',
        'version'       =>  '1.0',
        'compatibility' =>  '2021',
        'icon'          =>  'money',
        'install'       =>  function () use ($core) {
            $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('keuangan', 'jurnal_kasir', '0')");
        },
        'uninstall'     =>  function() use($core)
        {
        }
    ];
