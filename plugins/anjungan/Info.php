<?php
return [
    'name'          =>  'Anjungan',
    'description'   =>  'Modul anjungan pasien rawat jalan',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '4.0.*',
    'icon'          =>  'desktop',
    'pages'            =>  ['Anjungan Pasien Mandiri' => 'anjungan'],
    'install'       =>  function () use ($core) {
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'display_poli', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'carabayar', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'antrian_loket', '1')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'antrian_cs', '2')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'antrian_apotek', '3')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'panggil_loket', '1')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'panggil_loket_nomor', '1')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'panggil_cs', '1')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'panggil_cs_nomor', '1')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'panggil_apotek', '1')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'panggil_apotek_nomor', '1')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'text_anjungan', 'Running text anjungan pasien mandiri.....')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'text_loket', 'Running text display antrian loket.....')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'text_poli', 'Running text display antrian poliklinik.....')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'text_laboratorium', 'Running text display antrian laboratorium.....')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'text_apotek', 'Running text display antrian apotek.....')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'text_farmasi', 'Running text display antrian farmasi.....')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('anjungan', 'vidio', 'G4im8_n0OoI')");

    },
    'uninstall'     =>  function () use ($core) {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'anjungan'");
    }
];
