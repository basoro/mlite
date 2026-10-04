<?php

return [
    'name'          =>  'Veronisa',
    'description'   =>  'Modul Verifikasi Obat Kronis',
    'author'        =>  'Basoro',
    'category'      =>  'bridging', 
    'version'       =>  '1.0',
    'compatibility' =>  '6.*.*',
    'icon'          =>  'medkit',
    'install'       =>  function () use ($core) {

      // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
      // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
      $settings = [
          'username' => '',
          'password' => '',
          'obat_kronis' => '',
          'cons_id' => '',
          'kode_ppk' => '',
          'user_key' => '',
          'secret_key' => '',
          'bpjs_api_url' => '',
      ];
      foreach ($settings as $field => $value) {
          $cek = $core->db('mlite_settings')->where('module', 'veronisa')->where('field', $field)->oneArray();
          if (!$cek) {
              $core->db('mlite_settings')->save(['module' => 'veronisa', 'field' => $field, 'value' => $value]);
          }
      }

    },
    'uninstall'     =>  function() use($core)
    {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'veronisa'");
    }
];
