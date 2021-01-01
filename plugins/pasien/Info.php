<?php

return [
    'name'          =>  'Pendaftaran Pasien',
    'description'   =>  'Modul data pasien untuk KhanzaLITE',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2021',
    'icon'          =>  'users',
    'install'       =>  function () use ($core) {

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `pasien` (
          `no_rkm_medis` varchar(15) NOT NULL,
          `nm_pasien` varchar(40) DEFAULT NULL,
          `no_ktp` varchar(20) DEFAULT NULL,
          `jk` enum('L','P') DEFAULT NULL,
          `tmp_lahir` varchar(15) DEFAULT NULL,
          `tgl_lahir` date DEFAULT NULL,
          `nm_ibu` varchar(40) NOT NULL,
          `alamat` varchar(200) DEFAULT NULL,
          `gol_darah` enum('A','B','O','AB','-') DEFAULT NULL,
          `pekerjaan` varchar(35) DEFAULT NULL,
          `stts_nikah` enum('BELUM MENIKAH','MENIKAH','JANDA','DUDHA','JOMBLO') DEFAULT NULL,
          `agama` varchar(12) DEFAULT NULL,
          `tgl_daftar` date DEFAULT NULL,
          `no_tlp` varchar(40) DEFAULT NULL,
          `umur` varchar(20) NOT NULL,
          `pnd` enum('TS','TK','SD','SMP','SMA','SLTA/SEDERAJAT','D1','D2','D3','D4','S1','S2','S3','-') NOT NULL,
          `keluarga` enum('AYAH','IBU','ISTRI','SUAMI','SAUDARA','ANAK') DEFAULT NULL,
          `namakeluarga` varchar(50) NOT NULL,
          `kd_pj` char(3) NOT NULL,
          `no_peserta` varchar(25) DEFAULT NULL,
          `kd_kel` int(11) NOT NULL,
          `kd_kec` int(11) NOT NULL,
          `kd_kab` int(11) NOT NULL,
          `pekerjaanpj` varchar(35) NOT NULL,
          `alamatpj` varchar(100) NOT NULL,
          `kelurahanpj` varchar(60) NOT NULL,
          `kecamatanpj` varchar(60) NOT NULL,
          `kabupatenpj` varchar(60) NOT NULL,
          `perusahaan_pasien` varchar(8) NOT NULL,
          `suku_bangsa` int(11) NOT NULL,
          `bahasa_pasien` int(11) NOT NULL,
          `cacat_fisik` int(11) NOT NULL,
          `email` varchar(50) NOT NULL,
          `nip` varchar(30) NOT NULL,
          `kd_prop` int(11) NOT NULL,
          `propinsipj` varchar(30) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        $core->db()->pdo()->exec("INSERT INTO `pasien` (`no_rkm_medis`, `nm_pasien`, `no_ktp`, `jk`, `tmp_lahir`, `tgl_lahir`, `nm_ibu`, `alamat`, `gol_darah`, `pekerjaan`, `stts_nikah`, `agama`, `tgl_daftar`, `no_tlp`, `umur`, `pnd`, `keluarga`, `namakeluarga`, `kd_pj`, `no_peserta`, `kd_kel`, `kd_kec`, `kd_kab`, `pekerjaanpj`, `alamatpj`, `kelurahanpj`, `kecamatanpj`, `kabupatenpj`, `perusahaan_pasien`, `suku_bangsa`, `bahasa_pasien`, `cacat_fisik`, `email`, `nip`, `kd_prop`, `propinsipj`) VALUES
        ('000001', 'Fulan Bin Fulan', '6307064910000007', 'L', '-', '2019-09-18', '-', '-', 'O', '-', 'JOMBLO', 'Islam', '2019-09-21', '0', '0', 'S1', 'AYAH', '-', 'BPJ', '0001535601993', 1, 1, 1, '-', '-', '-', '-', '-', '-', 1, 1, 1, '-', '0', 1, '-');");

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `personal_pasien` (
          `no_rkm_medis` varchar(15) NOT NULL,
          `gambar` varchar(1000) DEFAULT NULL,
          `password` varchar(1000) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        $core->db()->pdo()->exec("ALTER TABLE `pasien`
          ADD PRIMARY KEY (`no_rkm_medis`),
          ADD KEY `kd_pj` (`kd_pj`),
          ADD KEY `kd_kec` (`kd_kec`),
          ADD KEY `kd_kab` (`kd_kab`),
          ADD KEY `nm_pasien` (`nm_pasien`),
          ADD KEY `alamat` (`alamat`),
          ADD KEY `kd_kel_2` (`kd_kel`),
          ADD KEY `no_ktp` (`no_ktp`),
          ADD KEY `no_peserta` (`no_peserta`),
          ADD KEY `perusahaan_pasien` (`perusahaan_pasien`) USING BTREE,
          ADD KEY `suku_bangsa` (`suku_bangsa`) USING BTREE,
          ADD KEY `bahasa_pasien` (`bahasa_pasien`) USING BTREE,
          ADD KEY `cacat_fisik` (`cacat_fisik`) USING BTREE,
          ADD KEY `kd_prop` (`kd_prop`) USING BTREE;");

        $core->db()->pdo()->exec("ALTER TABLE `personal_pasien`
          ADD PRIMARY KEY (`no_rkm_medis`);");

        $core->db()->pdo()->exec("ALTER TABLE `pasien`
          ADD CONSTRAINT `pasien_ibfk_1` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON UPDATE CASCADE,
          ADD CONSTRAINT `pasien_ibfk_3` FOREIGN KEY (`kd_kec`) REFERENCES `kecamatan` (`kd_kec`) ON UPDATE CASCADE,
          ADD CONSTRAINT `pasien_ibfk_4` FOREIGN KEY (`kd_kab`) REFERENCES `kabupaten` (`kd_kab`) ON UPDATE CASCADE,
          ADD CONSTRAINT `pasien_ibfk_5` FOREIGN KEY (`perusahaan_pasien`) REFERENCES `perusahaan_pasien` (`kode_perusahaan`) ON UPDATE CASCADE,
          ADD CONSTRAINT `pasien_ibfk_6` FOREIGN KEY (`suku_bangsa`) REFERENCES `suku_bangsa` (`id`) ON UPDATE CASCADE,
          ADD CONSTRAINT `pasien_ibfk_7` FOREIGN KEY (`bahasa_pasien`) REFERENCES `bahasa_pasien` (`id`) ON UPDATE CASCADE,
          ADD CONSTRAINT `pasien_ibfk_8` FOREIGN KEY (`cacat_fisik`) REFERENCES `cacat_fisik` (`id`) ON UPDATE CASCADE,
          ADD CONSTRAINT `pasien_ibfk_9` FOREIGN KEY (`kd_prop`) REFERENCES `propinsi` (`kd_prop`) ON UPDATE CASCADE;");

        $core->db()->pdo()->exec("ALTER TABLE `personal_pasien`
          ADD CONSTRAINT `personal_pasien_ibfk_1` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON DELETE CASCADE ON UPDATE CASCADE;");

    },
    'uninstall'     =>  function() use($core)
    {
    }
];
