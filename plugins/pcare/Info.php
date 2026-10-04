<?php

return [
    'name'          =>  'Bridging PCare',
    'description'   =>  'Modul pcare api untuk mLITE',
    'author'        =>  'Basoro',
    'category'      =>  'bridging', 
    'version'       =>  '1.0',
    'compatibility' =>  '6.*.*',
    'icon'          =>  'database',
    'install'       =>  function () use ($core) {

      // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
      // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
      $settings = [
          'usernamePcare' => '',
          'passwordPcare' => '',
          'consumerID' => '',
          'consumerSecret' => '',
          'consumerUserKey' => '',
          'consumerUserKeyAntrol' => '',
          'PCareApiUrl' => '',
          'kode_fktp' => '',
          'nama_fktp' => '',
          'wilayah' => 'REGIONAL VIII - Balikpapan',
          'cabang' => 'BARABAI',
          'kabupatenkota' => 'Kab. Hulu Sungai Tengah',
          'kode_kabupatenkota' => '0287',
      ];
      foreach ($settings as $field => $value) {
          $cek = $core->db('mlite_settings')->where('module', 'pcare')->where('field', $field)->oneArray();
          if (!$cek) {
              $core->db('mlite_settings')->save(['module' => 'pcare', 'field' => $field, 'value' => $value]);
          }
      }
    },
    'uninstall'     =>  function() use($core)
    {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'pcare'");
    }
];
