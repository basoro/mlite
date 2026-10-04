<?php

return [
    'name'          =>  'Dokter Ralan',
    'description'   =>  'Modul dokter rawat jalan untuk mLITE',
    'author'        =>  'Basoro',
    'category'      =>  'layanan', 
    'version'       =>  '1.0',
    'compatibility' =>  '6.*.*',
    'icon'          =>  'user-md',
    'install'       =>  function () use ($core) {
      // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
      // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
      $settings = [
          'set_sudah' => 'tidak',
      ];
      foreach ($settings as $field => $value) {
          $cek = $core->db('mlite_settings')->where('module', 'dokter_ralan')->where('field', $field)->oneArray();
          if (!$cek) {
              $core->db('mlite_settings')->save(['module' => 'dokter_ralan', 'field' => $field, 'value' => $value]);
          }
      }
    },
    'uninstall'     =>  function() use($core)
    {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'dokter_ralan'");
    }
];
