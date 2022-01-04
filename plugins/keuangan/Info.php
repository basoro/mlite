<?php

    return [
        'name'          =>  'Keuangan',
        'description'   =>  'Modul Keuangan untuk KhanzaLITE',
        'author'        =>  'Basoro',
        'version'       =>  '1.0',
        'compatibility' =>  '2022',
        'icon'          =>  'money',
        'install'       =>  function () use ($core) {

            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_jurnal` (
              `no_jurnal` varchar(20) NOT NULL,
              `no_bukti` varchar(20) DEFAULT NULL,
              `tgl_jurnal` date DEFAULT NULL,
              `jenis` enum('U','P') DEFAULT NULL,
              `keterangan` varchar(350) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

            $core->db()->pdo()->exec("ALTER TABLE `mlite_jurnal`
              ADD PRIMARY KEY (`no_jurnal`),
              ADD KEY `no_bukti` (`no_bukti`),
              ADD KEY `tgl_jurnal` (`tgl_jurnal`),
              ADD KEY `jenis` (`jenis`),
              ADD KEY `keterangan` (`keterangan`);");

            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_detailjurnal` (
              `no_jurnal` varchar(20) DEFAULT NULL,
              `kd_rek` varchar(15) DEFAULT NULL,
              `debet` double DEFAULT NULL,
              `kredit` double DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

            $core->db()->pdo()->exec("ALTER TABLE `mlite_detailjurnal`
              ADD KEY `no_jurnal` (`no_jurnal`),
              ADD KEY `kd_rek` (`kd_rek`),
              ADD KEY `debet` (`debet`),
              ADD KEY `kredit` (`kredit`);");

            $core->db()->pdo()->exec("ALTER TABLE `mlite_detailjurnal`
              ADD CONSTRAINT `mlite_detailjurnal_ibfk_1` FOREIGN KEY (`no_jurnal`) REFERENCES `mlite_jurnal` (`no_jurnal`) ON DELETE CASCADE ON UPDATE CASCADE,
              ADD CONSTRAINT `mlite_detailjurnal_ibfk_2` FOREIGN KEY (`kd_rek`) REFERENCES `mlite_rekening` (`kd_rek`) ON DELETE CASCADE ON UPDATE CASCADE;");

            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_rekening` (
              `kd_rek` varchar(15) NOT NULL DEFAULT '',
              `nm_rek` varchar(100) DEFAULT NULL,
              `tipe` enum('N','M','R') DEFAULT NULL,
              `balance` enum('D','K') DEFAULT NULL,
              `level` enum('0','1') DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

            $core->db()->pdo()->exec("ALTER TABLE `mlite_rekening`
              ADD PRIMARY KEY (`kd_rek`),
              ADD KEY `nm_rek` (`nm_rek`),
              ADD KEY `tipe` (`tipe`),
              ADD KEY `balance` (`balance`);");

            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_rekeningtahun` (
              `thn` year(4) NOT NULL,
              `kd_rek` varchar(15) NOT NULL DEFAULT '',
              `saldo_awal` double NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

            $core->db()->pdo()->exec("ALTER TABLE `mlite_rekeningtahun`
              ADD PRIMARY KEY (`thn`,`kd_rek`),
              ADD KEY `kd_rek` (`kd_rek`),
              ADD KEY `saldo_awal` (`saldo_awal`);");

            $core->db()->pdo()->exec("ALTER TABLE `mlite_rekeningtahun`
              ADD CONSTRAINT `mlite_rekeningtahun_ibfk_1` FOREIGN KEY (`kd_rek`) REFERENCES `mlite_rekening` (`kd_rek`) ON UPDATE CASCADE;");

            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_subrekening` (
              `kd_rek` varchar(15) NOT NULL,
              `kd_rek2` varchar(15) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

            $core->db()->pdo()->exec("ALTER TABLE `mlite_subrekening`
              ADD PRIMARY KEY (`kd_rek2`),
              ADD KEY `kd_rek` (`kd_rek`);");

            $core->db()->pdo()->exec("ALTER TABLE `mlite_subrekening`
              ADD CONSTRAINT `mlite_subrekening_ibfk_1` FOREIGN KEY (`kd_rek`) REFERENCES `mlite_rekening` (`kd_rek`) ON UPDATE CASCADE,
              ADD CONSTRAINT `mlite_subrekening_ibfk_2` FOREIGN KEY (`kd_rek2`) REFERENCES `mlite_rekening` (`kd_rek`) ON DELETE CASCADE ON UPDATE CASCADE;");


            $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('keuangan', 'jurnal_kasir', '0')");
            $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_pendaftaran', '')");
            $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_tindakan_ralan', '')");
            $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_obat_bhp', '')");
            $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_laboratorium', '')");
            $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_radiologi', '')");
            $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('keuangan', 'akun_kredit_tambahan_biaya', '')");
        },
        'uninstall'     =>  function() use($core)
        {
          $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'keuangan'");
        }
    ];
