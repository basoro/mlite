<?php

return [
    'name'          =>  'JKN Mobile',
    'description'   =>  'Modul mLITE JKN Mobile API',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '4.0.*',
    'icon'          =>  'tasks',
    'pages'         =>  ['JKN Mobile' => 'jknmobile'],
    'install'       =>  function () use ($core) {
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'x_username', 'jkn')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'x_password', 'mobile')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'header_token', 'X-Token')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'header_username', 'X-Username')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'header_password', 'X-Password')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'BpjsConsID', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'BpjsSecretKey', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'BpjsUserKey', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'BpjsAntrianUrl', 'https://apijkn-dev.bpjs-kesehatan.go.id/antreanrs_dev/')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kd_pj_bpjs', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'exclude_taskid', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'display', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kdprop', '1')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kdkab', '1')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kdkec', '1')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kdkel', '1')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'perusahaan_pasien', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'suku_bangsa', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'bahasa_pasien', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'cacat_fisik', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'kirimantrian', 'tidak')");
    },
    'uninstall'     =>  function () use ($core) {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'jkn_mobile'");
    }
];
