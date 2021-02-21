<?php
return [
    'name'          =>  'API',
    'description'   =>  'Katalog API KhanzaLITE',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2021',
    'icon'          =>  'database',
    'pages'         =>  ['API KhanzaLITE' => 'api'],
    'install'       =>  function () use ($core) {
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_key', 'jokowi')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_status_daftar', 'Terdaftar')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_status_dilayani', 'Anda siap dilayani')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_webappsurl', 'http://localhost/webapps/')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_normpetugas', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_limit', '2')");
    },
    'uninstall'     =>  function () use ($core) {
    }
];
