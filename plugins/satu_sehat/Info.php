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
    // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
    // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
    $settings = [
        'organizationid' => '',
        'clientid' => '',
        'secretkey' => '',
        'authurl' => 'https://api-satusehat-dev.dto.kemkes.go.id/oauth2/v1',
        'fhirurl' => 'https://api-satusehat-dev.dto.kemkes.go.id/fhir-r4/v1',
        'rme_authurl' => 'https://api-satusehat.kemkes.go.id/oauth2/v1',
        'chlurl' => 'https://api-satusehat.kemkes.go.id/ssrme/v2/ntl/chl',
        'shlurl' => 'https://api-satusehat.kemkes.go.id/ssrme/v2/ntl/shl',
        'kelurahan' => '',
        'kecamatan' => '',
        'kabupaten' => '',
        'propinsi' => '',
        'kodepos' => '',
        'longitude' => '',
        'latitude' => '',
        'zonawaktu' => 'WIB',
        'farmasi' => '',
        'laboratorium' => '',
        'radiologi' => '',
        'praktisiapotek' => '',
        'praktisilab' => '',
        'praktisirad' => '',
        'imaging' => 'mini_pacs',
    ];
    foreach ($settings as $field => $value) {
        $cek = $core->db('mlite_settings')->where('module', 'satu_sehat')->where('field', $field)->oneArray();
        if (!$cek) {
            $core->db('mlite_settings')->save(['module' => 'satu_sehat', 'field' => $field, 'value' => $value]);
        }
    }

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
