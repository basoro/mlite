<?php

return [
  'name' => 'BPJS E-Medical Records',
  'description' => 'Modul bridging E-Medical Records (Rekam Medis Elektronik) BPJS',
  'author' => 'Basoro',
  'category' => 'bridging',
  'version' => '1.0',
  'compatibility' => '6.*.*',
  'icon' => 'file-text',
  'install' => function () use ($core) {
    // Add any necessary installation logic here, such as settings initialization
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('bpjs_emr', 'consid', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('bpjs_emr', 'secretkey', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('bpjs_emr', 'userkey', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('bpjs_emr', 'koders', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('bpjs_emr', 'kode_kemkes', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('bpjs_emr', 'kodepos', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('bpjs_emr', 'baseurl', 'https://apijkn-dev.bpjs-kesehatan.go.id/erekammedis_dev/')");
    $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_bpjs_emr_mapping_prosedur_ranap` (
      `kd_jenis_prw` varchar(20) NOT NULL,
      `snomed_code` varchar(20) NOT NULL,
      `snomed_display` varchar(255) DEFAULT NULL,
      PRIMARY KEY (`kd_jenis_prw`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC");
    $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_bpjs_emr_mapping_operasi` (
      `kode_paket` varchar(20) NOT NULL,
      `snomed_code` varchar(20) NOT NULL,
      `snomed_display` varchar(255) DEFAULT NULL,
      PRIMARY KEY (`kode_paket`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC");
  },
  'uninstall' => function () use ($core) {
    $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'bpjs_emr'");
  }
];
