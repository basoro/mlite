<?php
return [
    'name'          =>  'API',
    'description'   =>  'Katalog API mLITE',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '4.0.*',
    'icon'          =>  'database',
    'pages'         =>  ['API mLITE' => 'api'],
    'install'       =>  function () use ($core) {

      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_key', 'qtbexUAxzqO3M8dCOo2vDMFvgYjdUEdMLVo341')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_status_daftar', 'Terdaftar')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_status_dilayani', 'Anda siap dilayani')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_webappsurl', 'http://localhost/webapps/')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_normpetugas', '000001,000002')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_limit', '2')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_smtp_host', 'ssl://smtp.gmail.com')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_smtp_port', '465')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_smtp_username', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_smtp_password', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_kdpj', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_kdprop', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_kdkab', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'apam_kdkec', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'duitku_merchantCode', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'duitku_merchantKey', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'duitku_paymentAmount', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'duitku_paymentMethod', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'duitku_productDetails', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'duitku_expiryPeriod', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'duitku_kdpj', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('api', 'berkasdigital_key', 'qtbexUAxzqO3M8dCOo2vDMFvgYjdUEdMLVo341')");
    },
    'uninstall'     =>  function () use ($core) {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'api'");
    }
];
