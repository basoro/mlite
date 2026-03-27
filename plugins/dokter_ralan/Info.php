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

      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_mapping_snomed_icd` ( `no_rawat` varchar(17) NOT NULL, `kd_penyakit` varchar(10) NOT NULL, `snomed_concept_id` varchar(50) NOT NULL, `snomed_term` text DEFAULT NULL, `status_penyakit` varchar(10) DEFAULT 'Baru', PRIMARY KEY (`no_rawat`,`kd_penyakit`,`snomed_concept_id`) );");

      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('dokter_ralan', 'set_sudah', 'tidak')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('dokter_ralan', 'snomed_api_url', 'https://kodemedis.thing.my.id')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('dokter_ralan', 'snomed_username', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('dokter_ralan', 'snomed_password', '')");
    },
    'uninstall'     =>  function() use($core)
    {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'dokter_ralan'");
    }
];
