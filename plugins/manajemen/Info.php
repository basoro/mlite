<?php

return [
    'name'          =>  'Manajemen',
    'description'   =>  'Modul manajemen untuk mLITE',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2023',
    'icon'          =>  'dashboard',
    'install'       =>  function () use ($core) {
      $core->db()->pdo()->exec("INSERT INTO `mlite__settings` (`module`, `field`, `value`) VALUES ('manajemen', 'penjab_umum', 'UMU')");
      $core->db()->pdo()->exec("INSERT INTO `mlite__settings` (`module`, `field`, `value`) VALUES ('manajemen', 'penjab_bpjs', 'BPJ')");
    },
    'uninstall'     =>  function() use($core)
    {
      $core->db()->pdo()->exec("DELETE FROM `mlite__settings` WHERE `module` = 'manajemen'");
    }
];
