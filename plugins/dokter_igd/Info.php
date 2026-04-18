<?php

return [
    'name'          =>  'Dokter IGD',
    'description'   =>  'Modul dokter IGD untuk mLITE',
    'author'        =>  'Basoro',
    'category'      =>  'layanan', 
    'version'       =>  '1.0',
    'compatibility' =>  '6.*.*',
    'icon'          =>  'user-md',
    'install'       =>  function () use ($core) {
      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_mapping_snomed_icd` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `no_rawat` VARCHAR(20) NOT NULL,
        `kd_penyakit` VARCHAR(10) NOT NULL,
        `snomed_concept_id` BIGINT NOT NULL,
        `snomed_term` VARCHAR(255) NOT NULL,
        `status_penyakit` ENUM('Baru','Lama') DEFAULT 'Baru',
        UNIQUE KEY `uniq_mapping` (`no_rawat`,`kd_penyakit`,`snomed_concept_id`),
        INDEX (`no_rawat`),
        INDEX (`kd_penyakit`),
        INDEX (`snomed_concept_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC");
    },
    'uninstall'     =>  function() use($core)
    {
    }
];
