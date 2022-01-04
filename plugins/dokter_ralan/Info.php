<?php

    return [
        'name'          =>  'Dokter Ralan',
        'description'   =>  'Modul dokter rawat jalan untuk KhanzaLITE',
        'author'        =>  'Basoro',
        'version'       =>  '1.0',
        'compatibility' =>  '2022',
        'icon'          =>  'user-md',
        'install'       =>  function () use ($core) {

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `permintaan_lab` (
            `noorder` varchar(15) NOT NULL,
            `no_rawat` varchar(17) NOT NULL,
            `tgl_permintaan` date NOT NULL,
            `jam_permintaan` time NOT NULL,
            `tgl_sampel` date NOT NULL,
            `jam_sampel` time NOT NULL,
            `tgl_hasil` date NOT NULL,
            `jam_hasil` time NOT NULL,
            `dokter_perujuk` varchar(20) NOT NULL,
            `status` enum('ralan','ranap') NOT NULL,
            `informasi_tambahan` varchar(60) NOT NULL,
            `diagnosa_klinis` varchar(80) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `permintaan_pemeriksaan_lab` (
            `noorder` varchar(15) NOT NULL,
            `kd_jenis_prw` varchar(15) NOT NULL,
            `stts_bayar` enum('Sudah','Belum') DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `permintaan_pemeriksaan_radiologi` (
            `noorder` varchar(15) NOT NULL,
            `kd_jenis_prw` varchar(15) NOT NULL,
            `stts_bayar` enum('Sudah','Belum') DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `permintaan_radiologi` (
            `noorder` varchar(15) NOT NULL,
            `no_rawat` varchar(17) NOT NULL,
            `tgl_permintaan` date NOT NULL,
            `jam_permintaan` time NOT NULL,
            `tgl_sampel` date NOT NULL,
            `jam_sampel` time NOT NULL,
            `tgl_hasil` date NOT NULL,
            `jam_hasil` time NOT NULL,
            `dokter_perujuk` varchar(20) NOT NULL,
            `status` enum('ralan','ranap') NOT NULL,
            `informasi_tambahan` varchar(60) NOT NULL,
            `diagnosa_klinis` varchar(80) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `resep_dokter_racikan` (
            `no_resep` varchar(14) NOT NULL,
            `no_racik` varchar(2) NOT NULL,
            `nama_racik` varchar(100) NOT NULL,
            `kd_racik` varchar(3) NOT NULL,
            `jml_dr` int(11) NOT NULL,
            `aturan_pakai` varchar(150) NOT NULL,
            `keterangan` varchar(50) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `resep_dokter_racikan_detail` (
            `no_resep` varchar(14) NOT NULL,
            `no_racik` varchar(2) NOT NULL,
            `kode_brng` varchar(15) NOT NULL,
            `p1` double DEFAULT NULL,
            `p2` double DEFAULT NULL,
            `kandungan` varchar(10) DEFAULT NULL,
            `jml` double DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `permintaan_lab`
            ADD PRIMARY KEY (`noorder`),
            ADD KEY `dokter_perujuk` (`dokter_perujuk`),
            ADD KEY `no_rawat` (`no_rawat`);");

          $core->db()->pdo()->exec("ALTER TABLE `permintaan_pemeriksaan_lab`
            ADD PRIMARY KEY (`noorder`,`kd_jenis_prw`),
            ADD KEY `kd_jenis_prw` (`kd_jenis_prw`);");

          $core->db()->pdo()->exec("ALTER TABLE `permintaan_pemeriksaan_radiologi`
            ADD PRIMARY KEY (`noorder`,`kd_jenis_prw`),
            ADD KEY `kd_jenis_prw` (`kd_jenis_prw`);");

          $core->db()->pdo()->exec("ALTER TABLE `permintaan_radiologi`
            ADD PRIMARY KEY (`noorder`),
            ADD KEY `dokter_perujuk` (`dokter_perujuk`),
            ADD KEY `no_rawat` (`no_rawat`);");

          $core->db()->pdo()->exec("ALTER TABLE `resep_dokter_racikan`
            ADD PRIMARY KEY (`no_resep`,`no_racik`),
            ADD KEY `kd_racik` (`kd_racik`);");

          $core->db()->pdo()->exec("ALTER TABLE `resep_dokter_racikan_detail`
            ADD PRIMARY KEY (`no_resep`,`no_racik`,`kode_brng`),
            ADD KEY `kode_brng` (`kode_brng`);");

          $core->db()->pdo()->exec("ALTER TABLE `permintaan_lab`
            ADD CONSTRAINT `permintaan_lab_ibfk_2` FOREIGN KEY (`dokter_perujuk`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `permintaan_lab_ibfk_3` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("ALTER TABLE `permintaan_pemeriksaan_lab`
            ADD CONSTRAINT `permintaan_pemeriksaan_lab_ibfk_1` FOREIGN KEY (`noorder`) REFERENCES `permintaan_lab` (`noorder`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `permintaan_pemeriksaan_lab_ibfk_2` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_lab` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("ALTER TABLE `permintaan_pemeriksaan_radiologi`
            ADD CONSTRAINT `permintaan_pemeriksaan_radiologi_ibfk_1` FOREIGN KEY (`noorder`) REFERENCES `permintaan_radiologi` (`noorder`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `permintaan_pemeriksaan_radiologi_ibfk_2` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_radiologi` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("ALTER TABLE `permintaan_radiologi`
            ADD CONSTRAINT `permintaan_radiologi_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `permintaan_radiologi_ibfk_3` FOREIGN KEY (`dokter_perujuk`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("ALTER TABLE `resep_dokter_racikan`
            ADD CONSTRAINT `resep_dokter_racikan_ibfk_1` FOREIGN KEY (`no_resep`) REFERENCES `resep_obat` (`no_resep`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `resep_dokter_racikan_ibfk_2` FOREIGN KEY (`kd_racik`) REFERENCES `metode_racik` (`kd_racik`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("ALTER TABLE `resep_dokter_racikan_detail`
            ADD CONSTRAINT `resep_dokter_racikan_detail_ibfk_1` FOREIGN KEY (`no_resep`) REFERENCES `resep_obat` (`no_resep`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `resep_dokter_racikan_detail_ibfk_2` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE;");

        },
        'uninstall'     =>  function() use($core)
        {
        }
    ];
