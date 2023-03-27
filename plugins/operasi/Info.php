<?php

return [
    'name'          =>  'Operasi',
    'description'   =>  'Modul operasi dan VK di mLITE',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2023',
    'icon'          =>  'bolt',
    'install'       =>  function () use ($core) {

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

    },
    'uninstall'     =>  function() use($core)
    {
    }
];
