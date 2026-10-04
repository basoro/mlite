<?php
return [
    'name'          =>  'API',
    'description'   =>  'Katalog API mLITE',
    'author'        =>  'Basoro',
    'category'      =>  'bridging', 
    'version'       =>  '1.0',
    'compatibility' =>  '6.*.*',
    'icon'          =>  'database',
    'pages'         =>  ['API mLITE' => 'api'],
    'install'       =>  function () use ($core) {

      // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
      // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
      $settings = [
          'apam_key' => 'qtbexUAxzqO3M8dCOo2vDMFvgYjdUEdMLVo341',
          'apam_status_daftar' => 'Terdaftar',
          'apam_status_dilayani' => 'Anda siap dilayani',
          'apam_webappsurl' => 'http://localhost/webapps/',
          'apam_normpetugas' => '000001,000002',
          'apam_limit' => '2',
          'apam_smtp_host' => 'ssl://smtp.gmail.com',
          'apam_smtp_port' => '465',
          'apam_smtp_username' => '',
          'apam_smtp_password' => '',
          'apam_kdpj' => '',
          'apam_kdprop' => '',
          'apam_kdkab' => '',
          'apam_kdkec' => '',
          'duitku_merchantCode' => '',
          'duitku_merchantKey' => '',
          'duitku_paymentAmount' => '',
          'duitku_paymentMethod' => '',
          'duitku_productDetails' => '',
          'duitku_expiryPeriod' => '',
          'duitku_kdpj' => '',
          'berkasdigital_key' => 'qtbexUAxzqO3M8dCOo2vDMFvgYjdUEdMLVo341',
      ];
      foreach ($settings as $field => $value) {
          $cek = $core->db('mlite_settings')->where('module', 'api')->where('field', $field)->oneArray();
          if (!$cek) {
              $core->db('mlite_settings')->save(['module' => 'api', 'field' => $field, 'value' => $value]);
          }
      }
    },
    'uninstall'     =>  function () use ($core) {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'api'");
    }
];
