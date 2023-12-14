<?php

return [
    'name'          =>  'Bridging PCare',
    'description'   =>  'Modul pcare api untuk mLITE',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '4.0.*',
    'icon'          =>  'database',
    'install'       =>  function () use ($core) {

      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'usernamePcare', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'passwordPcare', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'consumerID', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'consumerSecret', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'consumerUserKey', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'PCareApiUrl', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'kode_fktp', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'nama_fktp', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'wilayah', 'REGIONAL VIII - Balikpapan')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'cabang', 'BARABAI')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'kabupatenkota', 'Kab. Hulu Sungai Tengah')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'kode_kabupatenkota', '0287')");
    },
    'uninstall'     =>  function() use($core)
    {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'pcare'");
    }
];
