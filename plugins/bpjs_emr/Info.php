<?php

return [
  'name'          =>  'BPJS E-Medical Records',
  'description'   =>  'Modul bridging E-Medical Records (Rekam Medis Elektronik) BPJS',
  'author'        =>  'Basoro',
  'category'      =>  'bridging', 
  'version'       =>  '1.0',
  'compatibility' =>  '6.*.*',
  'icon'          =>  'file-text',
  'install'       =>  function () use ($core) {
    // Add any necessary installation logic here, such as settings initialization
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('bpjs_emr', 'consid', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('bpjs_emr', 'secretkey', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('bpjs_emr', 'userkey', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('bpjs_emr', 'koders', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('bpjs_emr', 'kode_kemkes', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('bpjs_emr', 'kodepos', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('bpjs_emr', 'baseurl', 'https://apijkn-dev.bpjs-kesehatan.go.id/vclaim-rest-dev')");
    
    // Create mlite_bpjs_emr_logs table based on DB driver
    if (DBDRIVER == 'mysql') {
      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_bpjs_emr_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `no_sep` varchar(50) DEFAULT NULL,
        `no_rawat` varchar(50) DEFAULT NULL,
        `payload_json` longtext,
        `payload_encrypted` longtext,
        `response` longtext,
        `status` varchar(20) DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    } else {
      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_bpjs_emr_logs` (
        `id` INTEGER PRIMARY KEY AUTOINCREMENT,
        `no_sep` TEXT,
        `no_rawat` TEXT,
        `payload_json` TEXT,
        `payload_encrypted` TEXT,
        `response` TEXT,
        `status` TEXT,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
      )");
    }
  },
  'uninstall'     =>  function () use ($core) {
    $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'bpjs_emr'");
    $core->db()->pdo()->exec("DROP TABLE IF EXISTS `mlite_bpjs_emr_logs`");
  }
];
