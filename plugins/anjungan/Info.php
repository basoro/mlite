<?php
return [
    'name'          =>  'Anjungan',
    'description'   =>  'Modul anjungan pasien rawat jalan',
    'author'        =>  'Sruu.pl',
    'version'       =>  '1.0',
    'compatibility' =>  '3.*',
    'icon'          =>  'desktop',
    'pages'            =>  ['Anjungan Pasien Mandiri' => 'anjungan'],
    'install'       =>  function () use ($core) {
      $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('anjungan', 'display_poli', '')");
      $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('anjungan', 'carabayar_umum', '')");
    },
    'uninstall'     =>  function () use ($core) {
      $core->db()->pdo()->exec("DELETE FROM `lite_options` WHERE `module` = 'anjungan'");
    }
];
