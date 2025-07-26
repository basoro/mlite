<?php

return [
    'name'          =>  'Veronisa',
    'description'   =>  'Modul Verifikasi Obat Kronis',
    'author'        =>  'Basoro',
    'category'      =>  'bridging', 
    'version'       =>  '1.0',
    'compatibility' =>  '5.*.*',
    'icon'          =>  'medkit',
    'install'       =>  function () use ($core) {

      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('veronisa', 'username', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('veronisa', 'password', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('veronisa', 'obat_kronis', '')");
      
      // Tabel untuk log apotek online
      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_apotek_online_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `no_rawat` varchar(17) NOT NULL,
        `noresep` varchar(50) DEFAULT NULL,
        `tanggal_kirim` datetime NOT NULL,
        `status` enum('success','error') NOT NULL,
        `response_resep` text DEFAULT NULL,
        `response_obat` text DEFAULT NULL,
        `user` varchar(50) DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `no_rawat` (`no_rawat`),
        KEY `tanggal_kirim` (`tanggal_kirim`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    },
    'uninstall'     =>  function() use($core)
    {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'veronisa'");
      $core->db()->pdo()->exec("DROP TABLE IF EXISTS `mlite_apotek_online_log`");
    }
];
