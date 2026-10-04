<?php

return [
    'name'          =>  'JKN Mobile',
    'description'   =>  'Modul mLITE JKN Mobile API',
    'author'        =>  'Basoro',
    'category'      =>  'bridging', 
    'version'       =>  '1.0',
    'compatibility' =>  '6.*.*',
    'icon'          =>  'tasks',
    'pages'         =>  ['JKN Mobile' => 'jknmobile'],
    'install'       =>  function () use ($core) {
      // Idempoten: jika settings sudah ada (sisa install sebelumnya), update nilainya,
      // jika belum ada maka insert. Aman untuk MySQL dan SQLite.
      $settings = [
        'x_username'          =>  'jkn',
        'x_password'          =>  'mobile',
        'header_token'        =>  'X-Token',
        'header_username'     =>  'X-Username',
        'header_password'     =>  'X-Password',
        'BpjsConsID'          =>  '',
        'BpjsSecretKey'       =>  '',
        'BpjsUserKey'         =>  '',
        'BpjsAntrianUrl'      =>  'https://apijkn-dev.bpjs-kesehatan.go.id/antreanrs_dev/',
        'kd_pj_bpjs'          =>  '',
        'exclude_taskid'      =>  '',
        'display'             =>  '',
        'kdprop'              =>  '1',
        'kdkab'               =>  '1',
        'kdkec'               =>  '1',
        'kdkel'               =>  '1',
        'perusahaan_pasien'   =>  '',
        'suku_bangsa'         =>  '',
        'bahasa_pasien'       =>  '',
        'cacat_fisik'         =>  '',
        'kirimantrian'        =>  'tidak',
        'ambil_antrian'       =>  'booking',
      ];
      foreach ($settings as $field => $value) {
        $cek = $core->db('mlite_settings')->where('module', 'jkn_mobile')->where('field', $field)->oneArray();
        if ($cek) {
          $core->db('mlite_settings')->where('module', 'jkn_mobile')->where('field', $field)->update(['value' => $value]);
        } else {
          $core->db('mlite_settings')->save(['module' => 'jkn_mobile', 'field' => $field, 'value' => $value]);
        }
      }
    },
    'uninstall'     =>  function () use ($core) {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'jkn_mobile'");
    }
];
