<?php

return [
    'name'          =>  'Manajemen',
    'description'   =>  'Modul manajemen untuk mLITE',
    'author'        =>  'Basoro',
    'category'      =>  'manajemen', 
    'version'       =>  '1.0',
    'compatibility' =>  '6.*.*',
    'icon'          =>  'dashboard',
    'install'       =>  function () use ($core) {
      // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
      // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
      $settings = [
          'penjab_umum' => 'UMU',
          'penjab_bpjs' => 'BPJ',
      ];
      foreach ($settings as $field => $value) {
          $cek = $core->db('mlite_settings')->where('module', 'manajemen')->where('field', $field)->oneArray();
          if (!$cek) {
              $core->db('mlite_settings')->save(['module' => 'manajemen', 'field' => $field, 'value' => $value]);
          }
      }
    },
    'uninstall'     =>  function() use($core)
    {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'manajemen'");
    }
];
