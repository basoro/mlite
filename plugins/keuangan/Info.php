<?php

return [
    'name'          =>  'Keuangan',
    'description'   =>  'Modul Keuangan untuk mLITE',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2023',
    'icon'          =>  'money',
    'install'       =>  function () use ($core) {

        $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite__rekening` (
          `kd_rek` varchar(15) NOT NULL DEFAULT '',
          `nm_rek` varchar(100) DEFAULT NULL,
          `tipe` enum('N','M','R') DEFAULT NULL,
          `balance` enum('D','K') DEFAULT NULL,
          `level` enum('0','1') DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        $core->mysql()->pdo()->exec("ALTER TABLE `mlite__rekening`
          ADD PRIMARY KEY (`kd_rek`),
          ADD KEY `nm_rek` (`nm_rek`),
          ADD KEY `tipe` (`tipe`),
          ADD KEY `balance` (`balance`);");

        $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite__jurnal` (
          `no_jurnal` varchar(20) NOT NULL,
          `no_bukti` varchar(20) DEFAULT NULL,
          `tgl_jurnal` date DEFAULT NULL,
          `jenis` enum('U','P') DEFAULT NULL,
          `keterangan` varchar(350) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        $core->mysql()->pdo()->exec("ALTER TABLE `mlite__jurnal`
          ADD PRIMARY KEY (`no_jurnal`),
          ADD KEY `no_bukti` (`no_bukti`),
          ADD KEY `tgl_jurnal` (`tgl_jurnal`),
          ADD KEY `jenis` (`jenis`),
          ADD KEY `keterangan` (`keterangan`);");

        $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite__detailjurnal` (
          `no_jurnal` varchar(20) DEFAULT NULL,
          `kd_rek` varchar(15) DEFAULT NULL,
          `debet` double DEFAULT NULL,
          `kredit` double DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        $core->mysql()->pdo()->exec("ALTER TABLE `mlite__detailjurnal`
          ADD KEY `no_jurnal` (`no_jurnal`),
          ADD KEY `kd_rek` (`kd_rek`),
          ADD KEY `debet` (`debet`),
          ADD KEY `kredit` (`kredit`);");

        $core->mysql()->pdo()->exec("ALTER TABLE `mlite__detailjurnal`
          ADD CONSTRAINT `mlite__detailjurnal_ibfk_1` FOREIGN KEY (`no_jurnal`) REFERENCES `mlite__jurnal` (`no_jurnal`) ON DELETE CASCADE ON UPDATE CASCADE,
          ADD CONSTRAINT `mlite__detailjurnal_ibfk_2` FOREIGN KEY (`kd_rek`) REFERENCES `mlite__rekening` (`kd_rek`) ON DELETE CASCADE ON UPDATE CASCADE;");

        $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite__rekeningtahun` (
          `thn` year(4) NOT NULL,
          `kd_rek` varchar(15) NOT NULL DEFAULT '',
          `saldo_awal` double NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        $core->mysql()->pdo()->exec("ALTER TABLE `mlite__rekeningtahun`
          ADD PRIMARY KEY (`thn`,`kd_rek`),
          ADD KEY `kd_rek` (`kd_rek`),
          ADD KEY `saldo_awal` (`saldo_awal`);");

        $core->mysql()->pdo()->exec("ALTER TABLE `mlite__rekeningtahun`
          ADD CONSTRAINT `mlite__rekeningtahun_ibfk_1` FOREIGN KEY (`kd_rek`) REFERENCES `mlite__rekening` (`kd_rek`) ON UPDATE CASCADE;");

        $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite__subrekening` (
          `kd_rek` varchar(15) NOT NULL,
          `kd_rek2` varchar(15) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        $core->mysql()->pdo()->exec("ALTER TABLE `mlite__subrekening`
          ADD PRIMARY KEY (`kd_rek2`),
          ADD KEY `kd_rek` (`kd_rek`);");

        $core->mysql()->pdo()->exec("ALTER TABLE `mlite__subrekening`
          ADD CONSTRAINT `mlite__subrekening_ibfk_1` FOREIGN KEY (`kd_rek`) REFERENCES `mlite__rekening` (`kd_rek`) ON UPDATE CASCADE,
          ADD CONSTRAINT `mlite__subrekening_ibfk_2` FOREIGN KEY (`kd_rek2`) REFERENCES `mlite__rekening` (`kd_rek`) ON DELETE CASCADE ON UPDATE CASCADE;");

        $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite__akun_kegiatan` (
          `id` int(11) NOT NULL,
          `kegiatan` varchar(200) DEFAULT NULL,
          `kd_rek` varchar(20) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        $core->mysql()->pdo()->exec("ALTER TABLE `mlite__akun_kegiatan`
          ADD PRIMARY KEY (`id`);");

        $core->mysql()->pdo()->exec("ALTER TABLE `mlite__akun_kegiatan`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");

        $core->db()->pdo()->exec("INSERT INTO `mlite__settings` (`module`, `field`, `value`) VALUES ('keuangan', 'jurnal_kasir', '0')");
        $core->db()->pdo()->exec("INSERT INTO `mlite__settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_pendaftaran', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite__settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_tindakan', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite__settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_obat_bhp', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite__settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_laboratorium', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite__settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_radiologi', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite__settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_tambahan_biaya', '')");
    },
    'uninstall'     =>  function() use($core)
    {
      $core->db()->pdo()->exec("DELETE FROM `mlite__settings` WHERE `module` = 'keuangan'");
    }
];
