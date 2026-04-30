<?php

return [
  'name' => 'BPJS E-MR',
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
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('bpjs_emr', 'kecamatan', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('bpjs_emr', 'kodepos', '')");    
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('bpjs_emr', 'baseurl', 'https://apijkn-dev.bpjs-kesehatan.go.id/erekammedis_dev/')");
    $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_bpjs_emr_mapping_prosedur_ranap` (
      `kd_jenis_prw` varchar(20) NOT NULL,
      `snomed_code` varchar(20) NOT NULL,
      `snomed_display` varchar(255) DEFAULT NULL,
      `focal_device_code` varchar(255) DEFAULT NULL,
      `focal_device_display` varchar(255) DEFAULT NULL,
      `focal_device_action` varchar(20) DEFAULT NULL,
      PRIMARY KEY (`kd_jenis_prw`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC");
    $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_bpjs_emr_mapping_operasi` (
      `kode_paket` varchar(20) NOT NULL,
      `snomed_code` varchar(20) NOT NULL,
      `snomed_display` varchar(255) DEFAULT NULL,
      `focal_device_code` varchar(255) DEFAULT NULL,
      `focal_device_display` varchar(255) DEFAULT NULL,
      `focal_device_action` varchar(20) DEFAULT NULL,
      PRIMARY KEY (`kode_paket`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC");

    $driver = $core->db()->pdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_bpjs_emr_device` (
        `id` INTEGER PRIMARY KEY AUTOINCREMENT,
        `device_id` TEXT NOT NULL UNIQUE,
        `nama_alkes` TEXT NOT NULL,
        `kategori` TEXT DEFAULT 'tindakan',
        `kode_produk` TEXT DEFAULT NULL,
        `manufacturer` TEXT DEFAULT NULL,
        `model` TEXT DEFAULT NULL,
        `keterangan` TEXT DEFAULT NULL
      )");
    } else {
      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_bpjs_emr_device` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `device_id` varchar(255) NOT NULL,
        `nama_alkes` varchar(255) NOT NULL,
        `kategori` varchar(50) DEFAULT 'tindakan',
        `kode_produk` varchar(255) DEFAULT NULL,
        `manufacturer` varchar(255) DEFAULT NULL,
        `model` varchar(255) DEFAULT NULL,
        `keterangan` text DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `device_id` (`device_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC");
    }

    $alterStatements = [
      "ALTER TABLE `mlite_bpjs_emr_mapping_lab` ADD COLUMN `focal_device_code` varchar(255) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_lab` ADD COLUMN `focal_device_display` varchar(255) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_lab` ADD COLUMN `focal_device_action` varchar(20) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_radiologi` ADD COLUMN `focal_device_code` varchar(255) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_radiologi` ADD COLUMN `focal_device_display` varchar(255) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_radiologi` ADD COLUMN `focal_device_action` varchar(20) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_prosedur` ADD COLUMN `focal_device_code` varchar(255) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_prosedur` ADD COLUMN `focal_device_display` varchar(255) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_prosedur` ADD COLUMN `focal_device_action` varchar(20) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_prosedur_ranap` ADD COLUMN `focal_device_code` varchar(255) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_prosedur_ranap` ADD COLUMN `focal_device_display` varchar(255) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_prosedur_ranap` ADD COLUMN `focal_device_action` varchar(20) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_operasi` ADD COLUMN `focal_device_code` varchar(255) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_operasi` ADD COLUMN `focal_device_display` varchar(255) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_operasi` ADD COLUMN `focal_device_action` varchar(20) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_lab` MODIFY COLUMN `focal_device_code` varchar(255) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_radiologi` MODIFY COLUMN `focal_device_code` varchar(255) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_prosedur` MODIFY COLUMN `focal_device_code` varchar(255) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_prosedur_ranap` MODIFY COLUMN `focal_device_code` varchar(255) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_mapping_operasi` MODIFY COLUMN `focal_device_code` varchar(255) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_device` ADD COLUMN `manufacturer` varchar(255) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_device` ADD COLUMN `model` varchar(255) DEFAULT NULL",
      "ALTER TABLE `mlite_bpjs_emr_device` ADD COLUMN `kategori` varchar(50) DEFAULT 'tindakan'"
    ];
    foreach ($alterStatements as $sql) {
      try {
        $core->db()->pdo()->exec($sql);
      } catch (\Throwable $e) {
        // ignore if column already exists or table not available
      }
    }
  },
  'uninstall' => function () use ($core) {
    $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'bpjs_emr'");
  }
];
