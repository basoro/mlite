<?php

return [
    'name'          =>  'WA Gateway',
    'description'   =>  'Modul Whatsapp Gateway mLITE',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '4.0.*',
    'icon'          =>  'whatsapp',
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('wagateway', 'server', 'https://mlite.id')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('wagateway', 'token', '-')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('wagateway', 'phonenumber', '-')");
    },
    'uninstall'     =>  function() use($core)
    {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'wagateway'");
    }
];
