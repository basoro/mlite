<?php

return [
    'name'          =>  'Orthanc',
    'description'   =>  'Bridging PACS via Orthanc',
    'author'        =>  'Basoro',
    'category'      =>  'bridging', 
    'version'       =>  '1.0',
    'compatibility' =>  '5.*.*',
    'icon'          =>  'bolt',
    'install'       =>  function () use ($core) {
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('orthanc', 'server', 'http://localhost:8042')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('orthanc', 'username', 'orthanc')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('orthanc', 'password', 'orthanc')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('orthanc', 'ai_api_key', '*******')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('orthanc', 'ai_api_url', 'https://api.openai.com/v1/chat/completions')");
    },
    'uninstall'     =>  function() use($core)
    {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'orthanc'");
    }
];
