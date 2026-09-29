<?php

return [
  'name' => 'Satu Sehat',
  'description' => 'Modul Satu Sehat Kemkes',
  'author' => 'Basoro',
  'category' => 'bridging',
  'version' => '1.0',
  'compatibility' => '6.*.*',
  'icon' => 'heartbeat',
  'install' => function () use ($core) {
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'organizationid', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'clientid', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'secretkey', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'authurl', 'https://api-satusehat-dev.dto.kemkes.go.id/oauth2/v1')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'fhirurl', 'https://api-satusehat-dev.dto.kemkes.go.id/fhir-r4/v1')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'rme_authurl', 'https://api-satusehat.kemkes.go.id/oauth2/v1')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'chlurl', 'https://api-satusehat.kemkes.go.id/ssrme/v2/ntl/chl')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'shlurl', 'https://api-satusehat.kemkes.go.id/ssrme/v2/ntl/shl')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'kelurahan', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'kecamatan', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'kabupaten', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'propinsi', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'kodepos', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'longitude', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'latitude', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'zonawaktu', 'WIB')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'farmasi', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'laboratorium', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'radiologi', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'praktisiapotek', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'praktisilab', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'praktisirad', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'imaging', 'mini_pacs')");

    // Tabel milik sub modul ERM (Rawat Jalan / Rawat Inap / IGD) dan mapping tindakan KPTL.
    $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_satu_sehat_erm_ralan` (
      `no_rawat` varchar(20) NOT NULL,
      `patient_id` varchar(64) DEFAULT '',
      `encounter_id` varchar(64) DEFAULT '',
      `practitioner_id` varchar(64) DEFAULT '',
      `location_id` varchar(64) DEFAULT '',
      `organization_id` varchar(64) DEFAULT '',
      `resource_map` text,
      `status_kirim` varchar(20) DEFAULT 'belum',
      `tgl_kirim` datetime DEFAULT NULL,
      `keterangan` text,
      PRIMARY KEY (`no_rawat`)
    )");
    $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_satu_sehat_erm_log` (
      `id` varchar(40) NOT NULL,
      `no_rawat` varchar(20) DEFAULT '',
      `status` varchar(20) DEFAULT '',
      `http_code` integer DEFAULT 0,
      `duration_ms` integer DEFAULT 0,
      `jumlah_resource` integer DEFAULT 0,
      `message` text,
      `request` text,
      `response` text,
      `created_at` datetime DEFAULT NULL,
      PRIMARY KEY (`id`)
    )");
    $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_satu_sehat_mapping_tindakan` (
      `kd_jenis_prw` varchar(15) NOT NULL,
      `kode_ktpl` varchar(50) DEFAULT '',
      `nama_ktpl` varchar(255) DEFAULT '',
      PRIMARY KEY (`kd_jenis_prw`)
    )");
  },
  'uninstall' => function () use ($core) {
    $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'satu_sehat'");
  }
];
