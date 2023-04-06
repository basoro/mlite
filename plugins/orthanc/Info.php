<?php

return [
    'name'          =>  'Orthanc',
    'description'   =>  'Bridging PACS via Orthanc',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2023',
    'icon'          =>  'bolt',
    'install'       =>  function () use ($core) {
      $core->db()->pdo()->exec("INSERT INTO `mlite__settings` (`module`, `field`, `value`) VALUES ('orthanc', 'server', 'http://localhost:8042')");
      $core->db()->pdo()->exec("INSERT INTO `mlite__settings` (`module`, `field`, `value`) VALUES ('orthanc', 'username', 'orthanc')");
      $core->db()->pdo()->exec("INSERT INTO `mlite__settings` (`module`, `field`, `value`) VALUES ('orthanc', 'password', 'orthanc')");
    },
    'uninstall'     =>  function() use($core)
    {
      $core->db()->pdo()->exec("DELETE FROM `mlite__settings` WHERE `module` = 'orthanc'");
    }
];
