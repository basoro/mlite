<?php

    return [
        'name'          =>  'Master Data',
        'description'   =>  'Data master awal KhanzaLITE',
        'author'        =>  'Basoro',
        'version'       =>  '1.0',
        'compatibility' =>  '2022',
        'icon'          =>  'cubes',
        'install'       =>  function () use ($core) {

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `bahasa_pasien` (
            `id` int(11) NOT NULL,
            `nama_bahasa` varchar(30) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;");

          $core->db()->pdo()->exec("INSERT INTO `bahasa_pasien` (`id`, `nama_bahasa`) VALUES
          (1, '-');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `cacat_fisik` (
            `id` int(11) NOT NULL,
            `nama_cacat` varchar(30) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;");

          $core->db()->pdo()->exec("INSERT INTO `cacat_fisik` (`id`, `nama_cacat`) VALUES
          (1, '-');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `propinsi` (
            `kd_prop` int(11) NOT NULL,
            `nm_prop` varchar(60) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `propinsi` (`kd_prop`, `nm_prop`) VALUES
          (1, '-');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `kabupaten` (
            `kd_kab` int(11) NOT NULL,
            `nm_kab` varchar(60) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `kabupaten` (`kd_kab`, `nm_kab`) VALUES
          (1, '-');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `kecamatan` (
            `kd_kec` int(11) NOT NULL,
            `nm_kec` varchar(60) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `kecamatan` (`kd_kec`, `nm_kec`) VALUES
          (1, '-');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `kelurahan` (
            `kd_kel` varchar(11) NOT NULL,
            `nm_kel` varchar(60) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `kelurahan` (`kd_kel`, `nm_kel`) VALUES
          ('1', '-');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `penjab` (
            `kd_pj` char(3) NOT NULL,
            `png_jawab` varchar(30) NOT NULL,
            `nama_perusahaan` varchar(60) NOT NULL,
            `alamat_asuransi` varchar(130) NOT NULL,
            `no_telp` varchar(40) NOT NULL,
            `attn` varchar(60) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `penjab` (`kd_pj`, `png_jawab`, `nama_perusahaan`, `alamat_asuransi`, `no_telp`, `attn`) VALUES
          ('BPJ', 'BPJS Kesehatan', '', '', '', ''),
          ('-', '-', '', '', '', '');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `perusahaan_pasien` (
            `kode_perusahaan` varchar(8) NOT NULL,
            `nama_perusahaan` varchar(70) DEFAULT NULL,
            `alamat` varchar(100) DEFAULT NULL,
            `kota` varchar(40) DEFAULT NULL,
            `no_telp` varchar(27) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `perusahaan_pasien` (`kode_perusahaan`, `nama_perusahaan`, `alamat`, `kota`, `no_telp`) VALUES
          ('-', '-', '-', '-', '0');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `suku_bangsa` (
            `id` int(11) NOT NULL,
            `nama_suku_bangsa` varchar(30) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;");

          $core->db()->pdo()->exec("INSERT INTO `suku_bangsa` (`id`, `nama_suku_bangsa`) VALUES
          (1, '-');");

          $core->db()->pdo()->exec("ALTER TABLE `bahasa_pasien`
            ADD PRIMARY KEY (`id`) USING BTREE,
            ADD UNIQUE KEY `nama_bahasa` (`nama_bahasa`) USING BTREE;");

          $core->db()->pdo()->exec("ALTER TABLE `cacat_fisik`
            ADD PRIMARY KEY (`id`) USING BTREE,
            ADD UNIQUE KEY `nama_cacat` (`nama_cacat`) USING BTREE;");

          $core->db()->pdo()->exec("ALTER TABLE `propinsi`
            ADD PRIMARY KEY (`kd_prop`),
            ADD UNIQUE KEY `nm_prop` (`nm_prop`);");

          $core->db()->pdo()->exec("ALTER TABLE `kabupaten`
            ADD PRIMARY KEY (`kd_kab`),
            ADD UNIQUE KEY `nm_kab` (`nm_kab`);");

          $core->db()->pdo()->exec("ALTER TABLE `kecamatan`
            ADD PRIMARY KEY (`kd_kec`);");

          $core->db()->pdo()->exec("ALTER TABLE `kelurahan`
            ADD PRIMARY KEY (`kd_kel`);");

          $core->db()->pdo()->exec("ALTER TABLE `penjab`
            ADD PRIMARY KEY (`kd_pj`);");

          $core->db()->pdo()->exec("ALTER TABLE `perusahaan_pasien`
            ADD PRIMARY KEY (`kode_perusahaan`);");

          $core->db()->pdo()->exec("ALTER TABLE `suku_bangsa`
            ADD PRIMARY KEY (`id`) USING BTREE,
            ADD UNIQUE KEY `nama_suku_bangsa` (`nama_suku_bangsa`) USING BTREE;");

          $core->db()->pdo()->exec("ALTER TABLE `bahasa_pasien`
            MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;");

          $core->db()->pdo()->exec("ALTER TABLE `cacat_fisik`
            MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;");

          $core->db()->pdo()->exec("ALTER TABLE `propinsi`
            MODIFY `kd_prop` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;");

          $core->db()->pdo()->exec("ALTER TABLE `kabupaten`
            MODIFY `kd_kab` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;");

          $core->db()->pdo()->exec("ALTER TABLE `kecamatan`
            MODIFY `kd_kec` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;");

          $core->db()->pdo()->exec("ALTER TABLE `suku_bangsa`
            MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `bank` (
            `namabank` varchar(50) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `bank` (`namabank`) VALUES
          ('-'),
          ('T');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `bidang` (
            `nama` varchar(15) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `bidang` (`nama`) VALUES
          ('-');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `departemen` (
            `dep_id` char(4) NOT NULL,
            `nama` varchar(25) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `departemen` (`dep_id`, `nama`) VALUES
          ('-', '-');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `emergency_index` (
            `kode_emergency` varchar(3) NOT NULL,
            `nama_emergency` varchar(200) DEFAULT NULL,
            `indek` tinyint(4) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `emergency_index` (`kode_emergency`, `nama_emergency`, `indek`) VALUES
          ('-', '-', 1);");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `jnj_jabatan` (
            `kode` varchar(10) NOT NULL,
            `nama` varchar(50) NOT NULL,
            `tnj` double NOT NULL,
            `indek` tinyint(4) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `jnj_jabatan` (`kode`, `nama`, `tnj`, `indek`) VALUES
          ('-', '-', 0, 1);");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `kelompok_jabatan` (
            `kode_kelompok` varchar(3) NOT NULL,
            `nama_kelompok` varchar(100) DEFAULT NULL,
            `indek` tinyint(4) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `kelompok_jabatan` (`kode_kelompok`, `nama_kelompok`, `indek`) VALUES
          ('-', '-', 1);");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `pendidikan` (
            `tingkat` varchar(80) NOT NULL,
            `indek` tinyint(4) NOT NULL,
            `gapok1` double NOT NULL,
            `kenaikan` double NOT NULL,
            `maksimal` int(11) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `pendidikan` (`tingkat`, `indek`, `gapok1`, `kenaikan`, `maksimal`) VALUES
          ('-', 1, 0, 0, 1);");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `resiko_kerja` (
            `kode_resiko` varchar(3) NOT NULL,
            `nama_resiko` varchar(200) DEFAULT NULL,
            `indek` tinyint(4) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `resiko_kerja` (`kode_resiko`, `nama_resiko`, `indek`) VALUES
          ('-', '-', 1);");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `spesialis` (
            `kd_sps` char(5) NOT NULL DEFAULT '',
            `nm_sps` varchar(30) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `spesialis` (`kd_sps`, `nm_sps`) VALUES
          ('UMUM', 'Dokter Umum');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `stts_kerja` (
            `stts` char(3) NOT NULL,
            `ktg` varchar(20) NOT NULL,
            `indek` tinyint(4) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `stts_kerja` (`stts`, `ktg`, `indek`) VALUES
          ('-', '-', 1);");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `stts_wp` (
            `stts` char(5) NOT NULL,
            `ktg` varchar(50) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `stts_wp` (`stts`, `ktg`) VALUES
          ('-', '-');");

          $core->db()->pdo()->exec("ALTER TABLE `bank`
            ADD PRIMARY KEY (`namabank`);");

          $core->db()->pdo()->exec("ALTER TABLE `bidang`
            ADD PRIMARY KEY (`nama`);");

          $core->db()->pdo()->exec("ALTER TABLE `departemen`
            ADD PRIMARY KEY (`dep_id`),
            ADD KEY `nama` (`nama`);");

          $core->db()->pdo()->exec("ALTER TABLE `emergency_index`
            ADD PRIMARY KEY (`kode_emergency`);");

          $core->db()->pdo()->exec("ALTER TABLE `jnj_jabatan`
            ADD PRIMARY KEY (`kode`),
            ADD KEY `nama` (`nama`),
            ADD KEY `tnj` (`tnj`);");

          $core->db()->pdo()->exec("ALTER TABLE `kelompok_jabatan`
            ADD PRIMARY KEY (`kode_kelompok`);");

          $core->db()->pdo()->exec("ALTER TABLE `pendidikan`
            ADD PRIMARY KEY (`tingkat`);");

          $core->db()->pdo()->exec("ALTER TABLE `resiko_kerja`
            ADD PRIMARY KEY (`kode_resiko`);");

          $core->db()->pdo()->exec("ALTER TABLE `spesialis`
            ADD PRIMARY KEY (`kd_sps`);");

          $core->db()->pdo()->exec("ALTER TABLE `stts_kerja`
            ADD PRIMARY KEY (`stts`);");

          $core->db()->pdo()->exec("ALTER TABLE `stts_wp`
            ADD PRIMARY KEY (`stts`);");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `kategori_perawatan` (
            `kd_kategori` char(5) NOT NULL,
            `nm_kategori` varchar(30) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `kategori_perawatan`
            ADD PRIMARY KEY (`kd_kategori`),
            ADD KEY `nm_kategori` (`nm_kategori`);");

          $core->db()->pdo()->exec("INSERT INTO `kategori_perawatan` (`kd_kategori`, `nm_kategori`) VALUES
          ('-', '-');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `jabatan` (
            `kd_jbtn` char(4) NOT NULL DEFAULT '',
            `nm_jbtn` varchar(25) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `jabatan` (`kd_jbtn`, `nm_jbtn`) VALUES
          ('-', '-');");

          $core->db()->pdo()->exec("ALTER TABLE `jabatan`
            ADD PRIMARY KEY (`kd_jbtn`),
            ADD KEY `nm_jbtn` (`nm_jbtn`);");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `icd9` (
            `kode` varchar(8) NOT NULL,
            `deskripsi_panjang` varchar(250) DEFAULT NULL,
            `deskripsi_pendek` varchar(40) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `kategori_penyakit` (
            `kd_ktg` varchar(8) NOT NULL,
            `nm_kategori` varchar(30) DEFAULT NULL,
            `ciri_umum` varchar(200) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `kategori_penyakit` (`kd_ktg`, `nm_kategori`, `ciri_umum`) VALUES
          ('-', '-', '-');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `penyakit` (
            `kd_penyakit` varchar(10) NOT NULL,
            `nm_penyakit` varchar(100) DEFAULT NULL,
            `ciri_ciri` text,
            `keterangan` varchar(60) DEFAULT NULL,
            `kd_ktg` varchar(8) DEFAULT NULL,
            `status` enum('Menular','Tidak Menular') NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `icd9`
            ADD PRIMARY KEY (`kode`);");

          $core->db()->pdo()->exec("ALTER TABLE `kategori_penyakit`
            ADD PRIMARY KEY (`kd_ktg`),
            ADD KEY `nm_kategori` (`nm_kategori`),
            ADD KEY `ciri_umum` (`ciri_umum`);");

          $core->db()->pdo()->exec("ALTER TABLE `penyakit`
            ADD PRIMARY KEY (`kd_penyakit`),
            ADD KEY `kd_ktg` (`kd_ktg`),
            ADD KEY `nm_penyakit` (`nm_penyakit`),
            ADD KEY `status` (`status`);");

          $core->db()->pdo()->exec("ALTER TABLE `penyakit`
            ADD CONSTRAINT `penyakit_ibfk_1` FOREIGN KEY (`kd_ktg`) REFERENCES `kategori_penyakit` (`kd_ktg`) ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `golongan_barang` (
            `kode` char(4) NOT NULL,
            `nama` varchar(30) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `golongan_barang` (`kode`, `nama`) VALUES
          ('-', '-');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `industrifarmasi` (
            `kode_industri` char(5) NOT NULL DEFAULT '',
            `nama_industri` varchar(50) DEFAULT NULL,
            `alamat` varchar(50) DEFAULT NULL,
            `kota` varchar(20) DEFAULT NULL,
            `no_telp` varchar(20) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `industrifarmasi` (`kode_industri`, `nama_industri`, `alamat`, `kota`, `no_telp`) VALUES
          ('-', '-', '-', '-', '0');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `jenis` (
            `kdjns` char(4) NOT NULL,
            `nama` varchar(30) NOT NULL,
            `keterangan` varchar(50) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `jenis` (`kdjns`, `nama`, `keterangan`) VALUES
          ('-', '-', '-');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `kategori_barang` (
            `kode` char(4) NOT NULL,
            `nama` varchar(30) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `kategori_barang` (`kode`, `nama`) VALUES
          ('-', '-');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `kodesatuan` (
            `kode_sat` char(4) NOT NULL,
            `satuan` varchar(30) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `kodesatuan` (`kode_sat`, `satuan`) VALUES
          ('-', '-');");

          $core->db()->pdo()->exec("ALTER TABLE `golongan_barang`
            ADD PRIMARY KEY (`kode`);");

          $core->db()->pdo()->exec("ALTER TABLE `industrifarmasi`
            ADD PRIMARY KEY (`kode_industri`),
            ADD KEY `nama_industri` (`nama_industri`),
            ADD KEY `alamat` (`alamat`),
            ADD KEY `kota` (`kota`),
            ADD KEY `no_telp` (`no_telp`);");

          $core->db()->pdo()->exec("ALTER TABLE `jenis`
            ADD PRIMARY KEY (`kdjns`),
            ADD KEY `nama` (`nama`),
            ADD KEY `keterangan` (`keterangan`);");

          $core->db()->pdo()->exec("ALTER TABLE `kategori_barang`
            ADD PRIMARY KEY (`kode`);");

          $core->db()->pdo()->exec("ALTER TABLE `kodesatuan`
            ADD PRIMARY KEY (`kode_sat`),
            ADD KEY `satuan` (`satuan`);");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `bangsal` (
            `kd_bangsal` char(5) NOT NULL,
            `nm_bangsal` varchar(30) DEFAULT NULL,
            `status` enum('0','1') DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `bangsal` (`kd_bangsal`, `nm_bangsal`, `status`) VALUES
          ('-', '-', '1');");

          $core->db()->pdo()->exec("ALTER TABLE `bangsal`
            ADD PRIMARY KEY (`kd_bangsal`),
            ADD KEY `nm_bangsal` (`nm_bangsal`),
            ADD KEY `status` (`status`);");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `kamar` (
            `kd_kamar` varchar(15) NOT NULL,
            `kd_bangsal` char(5) DEFAULT NULL,
            `trf_kamar` double DEFAULT NULL,
            `status` enum('ISI','KOSONG','DIBERSIHKAN','DIBOOKING') DEFAULT NULL,
            `kelas` enum('Kelas 1','Kelas 2','Kelas 3','Kelas Utama','Kelas VIP','Kelas VVIP') DEFAULT NULL,
            `statusdata` enum('0','1') DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `kamar`
            ADD PRIMARY KEY (`kd_kamar`),
            ADD KEY `kd_bangsal` (`kd_bangsal`),
            ADD KEY `trf_kamar` (`trf_kamar`),
            ADD KEY `status` (`status`),
            ADD KEY `kelas` (`kelas`),
            ADD KEY `statusdata` (`statusdata`);");

          $core->db()->pdo()->exec("ALTER TABLE `kamar`
            ADD CONSTRAINT `kamar_ibfk_1` FOREIGN KEY (`kd_bangsal`) REFERENCES `bangsal` (`kd_bangsal`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `master_aturan_pakai` (
            `aturan` varchar(150) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `master_aturan_pakai` (`aturan`) VALUES
          ('3 x 1 Sehari');");

          $core->db()->pdo()->exec("ALTER TABLE `master_aturan_pakai`
            ADD PRIMARY KEY (`aturan`);");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `metode_racik` (
            `kd_racik` varchar(3) NOT NULL,
            `nm_racik` varchar(30) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `metode_racik`
            ADD PRIMARY KEY (`kd_racik`) USING BTREE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `master_berkas_digital` (
            `kode` varchar(10) NOT NULL,
            `nama` varchar(100) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `master_berkas_digital`
            ADD PRIMARY KEY (`kode`);");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `jns_perawatan_inap` (
            `kd_jenis_prw` varchar(15) NOT NULL,
            `nm_perawatan` varchar(80) DEFAULT NULL,
            `kd_kategori` char(5) NOT NULL,
            `material` double DEFAULT NULL,
            `bhp` double NOT NULL,
            `tarif_tindakandr` double DEFAULT NULL,
            `tarif_tindakanpr` double DEFAULT NULL,
            `kso` double DEFAULT NULL,
            `menejemen` double DEFAULT NULL,
            `total_byrdr` double DEFAULT NULL,
            `total_byrpr` double DEFAULT NULL,
            `total_byrdrpr` double NOT NULL,
            `kd_pj` char(3) NOT NULL,
            `kd_bangsal` char(5) NOT NULL,
            `status` enum('0','1') NOT NULL,
            `kelas` enum('-','Kelas 1','Kelas 2','Kelas 3','Kelas Utama','Kelas VIP','Kelas VVIP') NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `jns_perawatan_inap`
            ADD PRIMARY KEY (`kd_jenis_prw`),
            ADD KEY `kd_pj` (`kd_pj`),
            ADD KEY `kd_bangsal` (`kd_bangsal`),
            ADD KEY `kd_kategori` (`kd_kategori`),
            ADD KEY `nm_perawatan` (`nm_perawatan`),
            ADD KEY `material` (`material`),
            ADD KEY `tarif_tindakandr` (`tarif_tindakandr`),
            ADD KEY `tarif_tindakanpr` (`tarif_tindakanpr`),
            ADD KEY `total_byrdr` (`total_byrdr`),
            ADD KEY `total_byrpr` (`total_byrpr`),
            ADD KEY `bhp` (`bhp`),
            ADD KEY `kso` (`kso`),
            ADD KEY `menejemen` (`menejemen`),
            ADD KEY `status` (`status`),
            ADD KEY `total_byrdrpr` (`total_byrdrpr`);");

          $core->db()->pdo()->exec("ALTER TABLE `jns_perawatan_inap`
            ADD CONSTRAINT `jns_perawatan_inap_ibfk_7` FOREIGN KEY (`kd_kategori`) REFERENCES `kategori_perawatan` (`kd_kategori`) ON UPDATE CASCADE,
            ADD CONSTRAINT `jns_perawatan_inap_ibfk_8` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON UPDATE CASCADE,
            ADD CONSTRAINT `jns_perawatan_inap_ibfk_9` FOREIGN KEY (`kd_bangsal`) REFERENCES `bangsal` (`kd_bangsal`) ON DELETE CASCADE ON UPDATE CASCADE;");

        },
        'uninstall'     =>  function() use($core)
        {
        }
    ];
