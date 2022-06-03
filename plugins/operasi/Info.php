<?php

return [
    'name'          =>  'Operasi',
    'description'   =>  'Modul operasi dan VK di mLITE',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2022',
    'icon'          =>  'bolt',
    'install'       =>  function () use ($core) {

    $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `beri_obat_operasi` (
      `no_rawat` varchar(17) NOT NULL,
      `tanggal` datetime NOT NULL,
      `kd_obat` varchar(15) NOT NULL,
      `hargasatuan` double NOT NULL,
      `jumlah` double NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

    $core->mysql()->pdo()->exec("ALTER TABLE `beri_obat_operasi`
      ADD KEY `no_rawat` (`no_rawat`),
      ADD KEY `kd_obat` (`kd_obat`),
      ADD KEY `tanggal` (`tanggal`),
      ADD KEY `hargasatuan` (`hargasatuan`),
      ADD KEY `jumlah` (`jumlah`);");

    $core->mysql()->pdo()->exec("ALTER TABLE `beri_obat_operasi`
      ADD CONSTRAINT `beri_obat_operasi_ibfk_2` FOREIGN KEY (`kd_obat`) REFERENCES `obatbhp_ok` (`kd_obat`) ON DELETE CASCADE ON UPDATE CASCADE,
      ADD CONSTRAINT `beri_obat_operasi_ibfk_3` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `laporan_operasi` (
      `no_rawat` varchar(17) NOT NULL,
      `tanggal` datetime NOT NULL,
      `diagnosa_preop` varchar(100) NOT NULL,
      `diagnosa_postop` varchar(100) NOT NULL,
      `jaringan_dieksekusi` varchar(100) NOT NULL,
      `selesaioperasi` datetime NOT NULL,
      `permintaan_pa` enum('Ya','Tidak') NOT NULL,
      `laporan_operasi` text NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

    $core->mysql()->pdo()->exec("ALTER TABLE `laporan_operasi`
      ADD PRIMARY KEY (`no_rawat`,`tanggal`);");

    $core->mysql()->pdo()->exec("ALTER TABLE `laporan_operasi`
      ADD CONSTRAINT `laporan_operasi_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `paket_operasi` (
      `kode_paket` varchar(15) NOT NULL,
      `nm_perawatan` varchar(80) NOT NULL,
      `kategori` enum('Kebidanan','Operasi') DEFAULT NULL,
      `operator1` double NOT NULL,
      `operator2` double NOT NULL,
      `operator3` double NOT NULL,
      `asisten_operator1` double DEFAULT NULL,
      `asisten_operator2` double NOT NULL,
      `asisten_operator3` double DEFAULT NULL,
      `instrumen` double DEFAULT NULL,
      `dokter_anak` double NOT NULL,
      `perawaat_resusitas` double NOT NULL,
      `dokter_anestesi` double NOT NULL,
      `asisten_anestesi` double NOT NULL,
      `asisten_anestesi2` double DEFAULT NULL,
      `bidan` double NOT NULL,
      `bidan2` double DEFAULT NULL,
      `bidan3` double DEFAULT NULL,
      `perawat_luar` double NOT NULL,
      `sewa_ok` double NOT NULL,
      `alat` double NOT NULL,
      `akomodasi` double DEFAULT NULL,
      `bagian_rs` double NOT NULL,
      `omloop` double NOT NULL,
      `omloop2` double DEFAULT NULL,
      `omloop3` double DEFAULT NULL,
      `omloop4` double DEFAULT NULL,
      `omloop5` double DEFAULT NULL,
      `sarpras` double DEFAULT NULL,
      `dokter_pjanak` double DEFAULT NULL,
      `dokter_umum` double DEFAULT NULL,
      `kd_pj` char(3) DEFAULT NULL,
      `status` enum('0','1') DEFAULT NULL,
      `kelas` enum('-','Rawat Jalan','Kelas 1','Kelas 2','Kelas 3','Kelas Utama','Kelas VIP','Kelas VVIP') DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

    $core->mysql()->pdo()->exec("ALTER TABLE `paket_operasi`
      ADD PRIMARY KEY (`kode_paket`),
      ADD KEY `nm_perawatan` (`nm_perawatan`),
      ADD KEY `operator1` (`operator1`),
      ADD KEY `operator2` (`operator2`),
      ADD KEY `operator3` (`operator3`),
      ADD KEY `asisten_operator1` (`asisten_operator1`),
      ADD KEY `asisten_operator2` (`asisten_operator2`),
      ADD KEY `asisten_operator3` (`instrumen`),
      ADD KEY `dokter_anak` (`dokter_anak`),
      ADD KEY `perawat_resusitas` (`perawaat_resusitas`),
      ADD KEY `dokter_anestasi` (`dokter_anestesi`),
      ADD KEY `asisten_anastesi` (`asisten_anestesi`),
      ADD KEY `bidan` (`bidan`),
      ADD KEY `perawat_luar` (`perawat_luar`),
      ADD KEY `sewa_ok` (`sewa_ok`),
      ADD KEY `alat` (`alat`),
      ADD KEY `sewa_vk` (`akomodasi`),
      ADD KEY `bagian_rs` (`bagian_rs`),
      ADD KEY `omloop` (`omloop`),
      ADD KEY `kd_pj` (`kd_pj`),
      ADD KEY `asisten_anestesi2` (`asisten_anestesi2`),
      ADD KEY `omloop2` (`omloop2`),
      ADD KEY `omloop3` (`omloop3`),
      ADD KEY `omloop4` (`omloop4`),
      ADD KEY `omloop5` (`omloop5`),
      ADD KEY `status` (`status`),
      ADD KEY `kategori` (`kategori`),
      ADD KEY `bidan2` (`bidan2`),
      ADD KEY `bidan3` (`bidan3`),
      ADD KEY `asisten_operator3_2` (`asisten_operator3`);");

    $core->mysql()->pdo()->exec("ALTER TABLE `paket_operasi`
      ADD CONSTRAINT `paket_operasi_ibfk_1` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `obatbhp_ok` (
      `kd_obat` varchar(15) NOT NULL,
      `nm_obat` varchar(50) NOT NULL,
      `kode_sat` char(4) NOT NULL,
      `hargasatuan` double NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

    $core->mysql()->pdo()->exec("ALTER TABLE `obatbhp_ok`
      ADD PRIMARY KEY (`kd_obat`),
      ADD KEY `kode_sat` (`kode_sat`),
      ADD KEY `nm_obat` (`nm_obat`),
      ADD KEY `hargasatuan` (`hargasatuan`);");

    $core->mysql()->pdo()->exec("ALTER TABLE `obatbhp_ok`
      ADD CONSTRAINT `obatbhp_ok_ibfk_1` FOREIGN KEY (`kode_sat`) REFERENCES `kodesatuan` (`kode_sat`) ON UPDATE CASCADE;");

    },
    'uninstall'     =>  function() use($core)
    {
    }
];
