<?php

return [
    'name'          =>  'Veronisa',
    'description'   =>  'Modul Verifikasi Obat Kronis',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2023',
    'icon'          =>  'medkit',
    'install'       =>  function () use ($core) {
      $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_veronisa` (
        `id` int(11) NOT NULL,
        `tanggal` date DEFAULT NULL,
        `no_rkm_medis` varchar(6) NOT NULL,
        `no_rawat` varchar(100) NOT NULL,
        `tgl_registrasi` varchar(100) NOT NULL,
        `nosep` varchar(100) NOT NULL,
        `status` varchar(100) NOT NULL,
        `username` varchar(100) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $core->mysql()->pdo()->exec("ALTER TABLE `mlite_veronisa`
        ADD PRIMARY KEY (`id`);");

      $core->mysql()->pdo()->exec("ALTER TABLE `mlite_veronisa`
        MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");

      $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_veronisa_feedback` (
        `id` int(11) NOT NULL,
        `nosep` varchar(100) NOT NULL,
        `tanggal` date DEFAULT NULL,
        `catatan` TEXT,
        `username` varchar(100) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $core->mysql()->pdo()->exec("ALTER TABLE `mlite_veronisa_feedback`
        ADD PRIMARY KEY (`id`);");

      $core->mysql()->pdo()->exec("ALTER TABLE `mlite_veronisa_feedback`
        MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");

      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('veronisa', 'username', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('veronisa', 'password', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('veronisa', 'obat_kronis', '')");

    },
    'uninstall'     =>  function() use($core)
    {
    }
];
