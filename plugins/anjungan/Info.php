<?php
return [
    'name'          =>  'Anjungan',
    'description'   =>  'Modul anjungan pasien rawat jalan',
    'author'        =>  'Basoro',
    'category'      =>  'bridging', 
    'version'       =>  '1.0',
    'compatibility' =>  '6.*.*',
    'icon'          =>  'desktop',
    'pages'            =>  ['Anjungan Pasien Mandiri' => 'anjungan'],
    'install'       =>  function () use ($core) {
      // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
      // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
      $settings = [
          'display_poli' => '',
          'carabayar' => '',
          'antrian_loket' => '1',
          'antrian_cs' => '2',
          'antrian_apotek' => '3',
          'panggil_loket' => '1',
          'panggil_loket_nomor' => '1',
          'panggil_cs' => '1',
          'panggil_cs_nomor' => '1',
          'panggil_apotek' => '1',
          'panggil_apotek_nomor' => '1',
          'text_anjungan' => 'Running text anjungan pasien mandiri.....',
          'text_loket' => 'Running text display antrian loket.....',
          'text_poli' => 'Running text display antrian poliklinik.....',
          'text_laboratorium' => 'Running text display antrian laboratorium.....',
          'text_apotek' => 'Running text display antrian apotek.....',
          'text_farmasi' => 'Running text display antrian farmasi.....',
          'vidio' => 'G4im8_n0OoI',
      ];
      foreach ($settings as $field => $value) {
          $cek = $core->db('mlite_settings')->where('module', 'anjungan')->where('field', $field)->oneArray();
          if (!$cek) {
              $core->db('mlite_settings')->save(['module' => 'anjungan', 'field' => $field, 'value' => $value]);
          }
      }

    },
    'uninstall'     =>  function () use ($core) {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'anjungan'");
    }
];
