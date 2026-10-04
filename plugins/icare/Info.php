<?php

return [
    'name'          =>  'iCare',
    'description'   =>  'Modul iCare BPJS',
    'author'        =>  'Basoro',
    'category'      =>  'bridging', 
    'version'       =>  '1.0',
    'compatibility' =>  '6.*.*',
    'icon'          =>  'plus-square',
    'install'       =>  function () use ($core) {
      // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
      // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
      $settings = [
          'url' => 'https://apijkn.bpjs-kesehatan.go.id/wsihs/api/rs/validate',
          'consid' => '',
          'secretkey' => '',
          'userkey' => '',
          'urlPCare' => 'https://apijkn.bpjs-kesehatan.go.id/wsihs/api/pcare/validate',
          'usernameICare' => '',
          'passwordICare' => '',
      ];
      foreach ($settings as $field => $value) {
          $cek = $core->db('mlite_settings')->where('module', 'icare')->where('field', $field)->oneArray();
          if (!$cek) {
              $core->db('mlite_settings')->save(['module' => 'icare', 'field' => $field, 'value' => $value]);
          }
      }
    },
    'uninstall'     =>  function() use($core)
    {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'icare'");
    }
];
