<?php
return [
    'name'          =>  'Vedika',
    'description'   =>  'Modul klaim online Vedika BPJS',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2022',
    'icon'          =>  'code',
    'pages'         =>  ['e-Vedika Dashboard' => 'vedika'],
    'install'       =>  function () use ($core) {

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_vedika` (
          `id` int(11) NOT NULL,
          `tanggal` date DEFAULT NULL,
          `no_rkm_medis` varchar(6) NOT NULL,
          `no_rawat` varchar(100) NOT NULL,
          `tgl_registrasi` varchar(100) NOT NULL,
          `nosep` varchar(100) NOT NULL,
          `jenis` varchar(100) NOT NULL,
          `status` varchar(100) NOT NULL,
          `username` varchar(100) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec("ALTER TABLE `mlite_vedika`
          ADD PRIMARY KEY (`id`);");

        $core->db()->pdo()->exec("ALTER TABLE `mlite_vedika`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_vedika_feedback` (
          `id` int(11) NOT NULL,
          `nosep` varchar(100) NOT NULL,
          `tanggal` date DEFAULT NULL,
          `catatan` TEXT,
          `username` varchar(100) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec("ALTER TABLE `mlite_vedika_feedback`
          ADD PRIMARY KEY (`id`);");

        $core->db()->pdo()->exec("ALTER TABLE `mlite_vedika_feedback`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");

        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'username', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'password', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'sep', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'skdp', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'operasi', '')");
    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'vedika'");
    }
];
