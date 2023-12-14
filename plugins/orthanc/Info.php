<?php

return [
    'name'          =>  'Orthanc',
    'description'   =>  'Bridging PACS via Orthanc',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '4.0.*',
    'icon'          =>  'bolt',
    'install'       =>  function () use ($core) {
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('orthanc', 'server', 'http://localhost:8042')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('orthanc', 'username', 'orthanc')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('orthanc', 'password', 'orthanc')");
    },
    'uninstall'     =>  function() use($core)
    {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'orthanc'");
    }
];
