<?php

return [
    'name'          =>  'WA Gateway',
    'description'   =>  'Modul Whatsapp Gateway mLITE',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2022',
    'icon'          =>  'whatsapp',
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('wagateway', 'waapiserver', 'https://mlite.id')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('wagateway', 'waapitoken', '-')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('wagateway', 'waapiphonenumber', '-')");
    },
    'uninstall'     =>  function() use($core)
    {
    }
];
