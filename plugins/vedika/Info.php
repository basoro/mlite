<?php
return [
    'name'          =>  'Vedika',
    'description'   =>  'Modul klaim online Vedika BPJS',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2023',
    'icon'          =>  'code',
    'pages'         =>  ['e-Vedika Dashboard' => 'vedika'],
    'install'       =>  function () use ($core) {

        $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_vedika` (
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

        $core->mysql()->pdo()->exec("ALTER TABLE `mlite_vedika`
          ADD PRIMARY KEY (`id`);");

        $core->mysql()->pdo()->exec("ALTER TABLE `mlite_vedika`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");

        $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_vedika_feedback` (
          `id` int(11) NOT NULL,
          `nosep` varchar(100) NOT NULL,
          `tanggal` date DEFAULT NULL,
          `catatan` TEXT,
          `username` varchar(100) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $core->mysql()->pdo()->exec("ALTER TABLE `mlite_vedika_feedback`
          ADD PRIMARY KEY (`id`);");

        $core->mysql()->pdo()->exec("ALTER TABLE `mlite_vedika_feedback`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");

        $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_users_vedika` (
          `id` int(11) NOT NULL,
          `username` text,
          `password` text,
          `fullname` text
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $core->mysql()->pdo()->exec("ALTER TABLE `mlite_users_vedika`
          ADD PRIMARY KEY (`id`);");

        $core->mysql()->pdo()->exec("ALTER TABLE `mlite_users_vedika`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");

        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'carabayar', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'sep', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'skdp', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'operasi', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'individual', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'billing', 'mlite')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'periode', '2023-01')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'verifikasi', '2023-01')");

        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'inacbgs_prosedur_bedah', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'inacbgs_prosedur_non_bedah', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'inacbgs_konsultasi', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'inacbgs_tenaga_ahli', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'inacbgs_keperawatan', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'inacbgs_penunjang', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'inacbgs_pelayanan_darah', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'inacbgs_rehabilitasi', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'inacbgs_rawat_intensif', '')");

        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'eklaim_url', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'eklaim_key', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'eklaim_kelasrs', 'CP')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'eklaim_payor_id', '3')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'eklaim_payor_cd', 'JKN')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('vedika', 'eklaim_cob_cd', '#')");

    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'vedika'");
    }
];
