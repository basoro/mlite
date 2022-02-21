<?php

    return [
        'name'          =>  'Rawat Inap',
        'description'   =>  'Modul rawat inap untuk KhanzaLITE',
        'author'        =>  'Basoro',
        'version'       =>  '1.0',
        'compatibility' =>  '2022',
        'icon'          =>  'hotel',
        'install'       =>  function () use ($core) {

            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `pemeriksaan_ranap` (
              `no_rawat` varchar(17) NOT NULL,
              `tgl_perawatan` date NOT NULL,
              `jam_rawat` time NOT NULL,
              `suhu_tubuh` char(5) DEFAULT NULL,
              `tensi` char(8) NOT NULL,
              `nadi` char(3) DEFAULT NULL,
              `respirasi` char(3) DEFAULT NULL,
              `tinggi` char(5) DEFAULT NULL,
              `berat` char(5) DEFAULT NULL,
              `gcs` varchar(10) DEFAULT NULL,
              `kesadaran` enum('Compos Mentis','Somnolence','Sopor','Coma') NOT NULL,
              `keluhan` varchar(400) DEFAULT NULL,
              `pemeriksaan` varchar(400) DEFAULT NULL,
              `alergi` varchar(50) DEFAULT NULL,
              `penilaian` varchar(400) NOT NULL,
              `rtl` varchar(400) NOT NULL,
              `instruksi` varchar(400) NOT NULL,
              `nip` varchar(20) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `rawat_inap_dr` (
              `no_rawat` varchar(17) NOT NULL DEFAULT '',
              `kd_jenis_prw` varchar(15) NOT NULL,
              `kd_dokter` varchar(20) NOT NULL,
              `tgl_perawatan` date NOT NULL DEFAULT '0000-00-00',
              `jam_rawat` time NOT NULL DEFAULT '00:00:00',
              `material` double NOT NULL,
              `bhp` double NOT NULL,
              `tarif_tindakandr` double NOT NULL,
              `kso` double DEFAULT NULL,
              `menejemen` double DEFAULT NULL,
              `biaya_rawat` double DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `rawat_inap_drpr` (
              `no_rawat` varchar(17) NOT NULL DEFAULT '',
              `kd_jenis_prw` varchar(15) NOT NULL,
              `kd_dokter` varchar(20) NOT NULL,
              `nip` varchar(20) NOT NULL DEFAULT '',
              `tgl_perawatan` date NOT NULL DEFAULT '0000-00-00',
              `jam_rawat` time NOT NULL DEFAULT '00:00:00',
              `material` double NOT NULL,
              `bhp` double NOT NULL,
              `tarif_tindakandr` double DEFAULT NULL,
              `tarif_tindakanpr` double DEFAULT NULL,
              `kso` double DEFAULT NULL,
              `menejemen` double DEFAULT NULL,
              `biaya_rawat` double DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `rawat_inap_pr` (
              `no_rawat` varchar(17) NOT NULL DEFAULT '',
              `kd_jenis_prw` varchar(15) NOT NULL,
              `nip` varchar(20) NOT NULL DEFAULT '',
              `tgl_perawatan` date NOT NULL DEFAULT '0000-00-00',
              `jam_rawat` time NOT NULL DEFAULT '00:00:00',
              `material` double NOT NULL,
              `bhp` double NOT NULL,
              `tarif_tindakanpr` double NOT NULL,
              `kso` double DEFAULT NULL,
              `menejemen` double DEFAULT NULL,
              `biaya_rawat` double DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

            $core->db()->pdo()->exec("ALTER TABLE `pemeriksaan_ranap`
              ADD PRIMARY KEY (`no_rawat`,`tgl_perawatan`,`jam_rawat`),
              ADD KEY `no_rawat` (`no_rawat`),
              ADD KEY `nip` (`nip`);");

            $core->db()->pdo()->exec("ALTER TABLE `pemeriksaan_ranap`
              ADD CONSTRAINT `pemeriksaan_ranap_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
              ADD CONSTRAINT `pemeriksaan_ranap_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `pegawai` (`nik`) ON DELETE CASCADE ON UPDATE CASCADE;");

            $core->db()->pdo()->exec("ALTER TABLE `rawat_inap_dr`
              ADD PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`kd_dokter`,`tgl_perawatan`,`jam_rawat`),
              ADD KEY `no_rawat` (`no_rawat`),
              ADD KEY `kd_jenis_prw` (`kd_jenis_prw`),
              ADD KEY `kd_dokter` (`kd_dokter`),
              ADD KEY `tgl_perawatan` (`tgl_perawatan`),
              ADD KEY `biaya_rawat` (`biaya_rawat`),
              ADD KEY `jam_rawat` (`jam_rawat`);");

            $core->db()->pdo()->exec("ALTER TABLE `rawat_inap_drpr`
              ADD PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`kd_dokter`,`nip`,`tgl_perawatan`,`jam_rawat`),
              ADD KEY `rawat_inap_drpr_ibfk_2` (`kd_jenis_prw`),
              ADD KEY `rawat_inap_drpr_ibfk_3` (`kd_dokter`),
              ADD KEY `rawat_inap_drpr_ibfk_4` (`nip`);");

            $core->db()->pdo()->exec("ALTER TABLE `rawat_inap_pr`
              ADD PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`nip`,`tgl_perawatan`,`jam_rawat`),
              ADD KEY `no_rawat` (`no_rawat`),
              ADD KEY `kd_jenis_prw` (`kd_jenis_prw`),
              ADD KEY `nip` (`nip`),
              ADD KEY `biaya_rawat` (`biaya_rawat`);");

            $core->db()->pdo()->exec("ALTER TABLE `rawat_inap_dr`
              ADD CONSTRAINT `rawat_inap_dr_ibfk_3` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
              ADD CONSTRAINT `rawat_inap_dr_ibfk_6` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_inap` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE,
              ADD CONSTRAINT `rawat_inap_dr_ibfk_7` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE;");

            $core->db()->pdo()->exec("ALTER TABLE `rawat_inap_drpr`
              ADD CONSTRAINT `rawat_inap_drpr_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
              ADD CONSTRAINT `rawat_inap_drpr_ibfk_2` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_inap` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE,
              ADD CONSTRAINT `rawat_inap_drpr_ibfk_3` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
              ADD CONSTRAINT `rawat_inap_drpr_ibfk_4` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE;");

            $core->db()->pdo()->exec("ALTER TABLE `rawat_inap_pr`
              ADD CONSTRAINT `rawat_inap_pr_ibfk_3` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE,
              ADD CONSTRAINT `rawat_inap_pr_ibfk_6` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_inap` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE,
              ADD CONSTRAINT `rawat_inap_pr_ibfk_7` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE;");

            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `kamar_inap` (
              `no_rawat` varchar(17) NOT NULL DEFAULT '',
              `kd_kamar` varchar(15) NOT NULL,
              `trf_kamar` double DEFAULT NULL,
              `diagnosa_awal` varchar(100) DEFAULT NULL,
              `diagnosa_akhir` varchar(100) DEFAULT NULL,
              `tgl_masuk` date NOT NULL DEFAULT '0000-00-00',
              `jam_masuk` time NOT NULL DEFAULT '00:00:00',
              `tgl_keluar` date DEFAULT NULL,
              `jam_keluar` time DEFAULT NULL,
              `lama` double DEFAULT NULL,
              `ttl_biaya` double DEFAULT NULL,
              `stts_pulang` enum('Sehat','Rujuk','APS','+','Meninggal','Sembuh','Membaik','Pulang Paksa','-','Pindah Kamar','Status Belum Lengkap','Atas Persetujuan Dokter','Atas Permintaan Sendiri','Lain-lain') NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

            $core->db()->pdo()->exec("ALTER TABLE `kamar_inap`
              ADD PRIMARY KEY (`no_rawat`,`tgl_masuk`,`jam_masuk`),
              ADD KEY `kd_kamar` (`kd_kamar`),
              ADD KEY `diagnosa_awal` (`diagnosa_awal`),
              ADD KEY `diagnosa_akhir` (`diagnosa_akhir`),
              ADD KEY `tgl_keluar` (`tgl_keluar`),
              ADD KEY `jam_keluar` (`jam_keluar`),
              ADD KEY `lama` (`lama`),
              ADD KEY `ttl_biaya` (`ttl_biaya`),
              ADD KEY `stts_pulang` (`stts_pulang`),
              ADD KEY `trf_kamar` (`trf_kamar`);");

            $core->db()->pdo()->exec("ALTER TABLE `kamar_inap`
              ADD CONSTRAINT `kamar_inap_ibfk_2` FOREIGN KEY (`kd_kamar`) REFERENCES `kamar` (`kd_kamar`) ON DELETE CASCADE ON UPDATE CASCADE,
              ADD CONSTRAINT `kamar_inap_ibfk_3` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE;");

            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `dpjp_ranap` (
              `no_rawat` varchar(17) NOT NULL,
              `kd_dokter` varchar(20) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

            $core->db()->pdo()->exec("ALTER TABLE `dpjp_ranap`
              ADD PRIMARY KEY (`no_rawat`,`kd_dokter`),
              ADD KEY `dpjp_ranap_ibfk_2` (`kd_dokter`);");

            $core->db()->pdo()->exec("ALTER TABLE `dpjp_ranap`
              ADD CONSTRAINT `dpjp_ranap_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
              ADD CONSTRAINT `dpjp_ranap_ibfk_2` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE;");

            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `paket_operasi` (
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

            $core->db()->pdo()->exec("ALTER TABLE `paket_operasi`
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

            $core->db()->pdo()->exec("ALTER TABLE `paket_operasi`
              ADD CONSTRAINT `paket_operasi_ibfk_1` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON DELETE CASCADE ON UPDATE CASCADE;");

            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `booking_operasi` (
              `no_rawat` varchar(17) DEFAULT NULL,
              `kode_paket` varchar(15) DEFAULT NULL,
              `tanggal` date DEFAULT NULL,
              `jam_mulai` time DEFAULT NULL,
              `jam_selesai` time DEFAULT NULL,
              `status` enum('Menunggu','Proses Operasi','Selesai') DEFAULT NULL,
              `kd_dokter` varchar(20) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

            $core->db()->pdo()->exec("ALTER TABLE `booking_operasi`
              ADD KEY `no_rawat` (`no_rawat`),
              ADD KEY `kode_paket` (`kode_paket`),
              ADD KEY `kd_dokter` (`kd_dokter`);");

            $core->db()->pdo()->exec("ALTER TABLE `booking_operasi`
              ADD CONSTRAINT `booking_operasi_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
              ADD CONSTRAINT `booking_operasi_ibfk_2` FOREIGN KEY (`kode_paket`) REFERENCES `paket_operasi` (`kode_paket`) ON DELETE CASCADE ON UPDATE CASCADE,
              ADD CONSTRAINT `booking_operasi_ibfk_3` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE;");


        },
        'uninstall'     =>  function() use($core)
        {
        }
    ];
