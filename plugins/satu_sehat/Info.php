<?php

return [
  'name'          =>  'Satu Sehat',
  'description'   =>  'Modul Satu Sehat Kemkes',
  'author'        =>  'Basoro',
  'version'       =>  '1.0',
  'compatibility' =>  '4.0.*',
  'icon'          =>  'heartbeat',
  'install'       =>  function () use ($core) {
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'organizationid', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'clientid', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'secretkey', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'authurl', 'https://api-satusehat-dev.dto.kemkes.go.id/oauth2/v1')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'fhirurl', 'https://api-satusehat-dev.dto.kemkes.go.id/fhir-r4/v1')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'kelurahan', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'kecamatan', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'kabupaten', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'propinsi', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'kodepos', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'longitude', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'latitude', '')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'zonawaktu', 'WIB')");
    $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('satu_sehat', 'billing', 'mlite')");
  },
  'uninstall'     =>  function () use ($core) {
    $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'satu_sehat'");
  }
];
