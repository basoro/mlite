<?php

return [
    'name'          =>  'Orthanc',
    'description'   =>  'Bridging PACS via Orthanc',
    'author'        =>  'Basoro',
    'category'      =>  'bridging', 
    'version'       =>  '1.0',
    'compatibility' =>  '6.*.*',
    'icon'          =>  'bolt',
    'install'       =>  function () use ($core) {
      // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
      // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
      $settings = [
          'server' => 'http://localhost:8042',
          'username' => 'orthanc',
          'password' => 'orthanc',
      ];
      foreach ($settings as $field => $value) {
          $cek = $core->db('mlite_settings')->where('module', 'orthanc')->where('field', $field)->oneArray();
          if (!$cek) {
              $core->db('mlite_settings')->save(['module' => 'orthanc', 'field' => $field, 'value' => $value]);
          }
      }
    },
    'uninstall'     =>  function() use($core)
    {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'orthanc'");
    }
];
