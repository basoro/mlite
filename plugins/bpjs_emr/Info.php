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
    // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
    // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
    $settings = [
        'consid' => '',
        'secretkey' => '',
        'userkey' => '',
        'koders' => '',
        'kode_kemkes' => '',
        'kecamatan' => '',
        'kodepos' => '',
        'baseurl' => 'https://apijkn-dev.bpjs-kesehatan.go.id/erekammedis_dev/',
    ];
    foreach ($settings as $field => $value) {
        $cek = $core->db('mlite_settings')->where('module', 'bpjs_emr')->where('field', $field)->oneArray();
        if (!$cek) {
            $core->db('mlite_settings')->save(['module' => 'bpjs_emr', 'field' => $field, 'value' => $value]);
        }
    }
  },
  'uninstall' => function () use ($core) {
    $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'bpjs_emr'");
  }
];
