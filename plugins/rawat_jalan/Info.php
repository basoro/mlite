<?php

    return [
        'name'          =>  'Rawat Jalan',
        'description'   =>  'Modul pendaftaran layanan untuk KhanzaLITE',
        'author'        =>  'Basoro',
        'version'       =>  '1.0',
        'compatibility' =>  '2022',
        'icon'          =>  'wheelchair',
        'install'       =>  function () use ($core) {

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `dokter` (
            `kd_dokter` varchar(20) NOT NULL,
            `nm_dokter` varchar(50) DEFAULT NULL,
            `jk` enum('L','P') DEFAULT NULL,
            `tmp_lahir` varchar(20) DEFAULT NULL,
            `tgl_lahir` date DEFAULT NULL,
            `gol_drh` enum('A','B','O','AB','-') DEFAULT NULL,
            `agama` varchar(12) DEFAULT NULL,
            `almt_tgl` varchar(60) DEFAULT NULL,
            `no_telp` varchar(13) DEFAULT NULL,
            `stts_nikah` enum('BELUM MENIKAH','MENIKAH','JANDA','DUDHA','JOMBLO') DEFAULT NULL,
            `kd_sps` char(5) DEFAULT NULL,
            `alumni` varchar(60) DEFAULT NULL,
            `no_ijn_praktek` varchar(40) DEFAULT NULL,
            `status` enum('0','1') NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `dokter` (`kd_dokter`, `nm_dokter`, `jk`, `tmp_lahir`, `tgl_lahir`, `gol_drh`, `agama`, `almt_tgl`, `no_telp`, `stts_nikah`, `kd_sps`, `alumni`, `no_ijn_praktek`, `status`) VALUES
          ('DR001', 'dr. Ataaka Muhammad', 'L', 'Barabai', '2000-09-18', 'O', 'Islam', 'Barabai', '-', 'MENIKAH', 'UMUM', 'UI', '-', '1');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `pegawai` (
            `id` int(11) NOT NULL,
            `nik` varchar(20) NOT NULL,
            `nama` varchar(50) NOT NULL,
            `jk` enum('Pria','Wanita') NOT NULL,
            `jbtn` varchar(25) NOT NULL,
            `jnj_jabatan` varchar(5) NOT NULL,
            `kode_kelompok` varchar(3) NOT NULL,
            `kode_resiko` varchar(3) NOT NULL,
            `kode_emergency` varchar(3) NOT NULL,
            `departemen` char(4) NOT NULL,
            `bidang` varchar(15) NOT NULL,
            `stts_wp` char(5) NOT NULL,
            `stts_kerja` char(3) NOT NULL,
            `npwp` varchar(15) NOT NULL,
            `pendidikan` varchar(80) NOT NULL,
            `gapok` double NOT NULL,
            `tmp_lahir` varchar(20) NOT NULL,
            `tgl_lahir` date NOT NULL,
            `alamat` varchar(60) NOT NULL,
            `kota` varchar(20) NOT NULL,
            `mulai_kerja` date NOT NULL,
            `ms_kerja` enum('<1','PT','FT>1') NOT NULL,
            `indexins` char(4) NOT NULL,
            `bpd` varchar(50) NOT NULL,
            `rekening` varchar(25) NOT NULL,
            `stts_aktif` enum('AKTIF','CUTI','KELUAR','TENAGA LUAR') NOT NULL,
            `wajibmasuk` tinyint(2) NOT NULL,
            `pengurang` double NOT NULL,
            `indek` tinyint(4) NOT NULL,
            `mulai_kontrak` date DEFAULT NULL,
            `cuti_diambil` int(11) NOT NULL,
            `dankes` double NOT NULL,
            `photo` varchar(500) DEFAULT NULL,
            `no_ktp` varchar(20) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `pegawai` (`id`, `nik`, `nama`, `jk`, `jbtn`, `jnj_jabatan`, `kode_kelompok`, `kode_resiko`, `kode_emergency`, `departemen`, `bidang`, `stts_wp`, `stts_kerja`, `npwp`, `pendidikan`, `gapok`, `tmp_lahir`, `tgl_lahir`, `alamat`, `kota`, `mulai_kerja`, `ms_kerja`, `indexins`, `bpd`, `rekening`, `stts_aktif`, `wajibmasuk`, `pengurang`, `indek`, `mulai_kontrak`, `cuti_diambil`, `dankes`, `photo`, `no_ktp`) VALUES
          (1, 'DR001', 'dr. Ataaka Muhammad', 'Pria', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', 0, 'Barabai', '2016-06-10', '-', 'Barabai', '2019-09-18', '<1', '-', '-', '-', 'AKTIF', 0, 0, 0, '2019-09-18', 1, 0, '-', '0');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `poliklinik` (
            `kd_poli` char(5) NOT NULL DEFAULT '',
            `nm_poli` varchar(50) DEFAULT NULL,
            `registrasi` double NOT NULL,
            `registrasilama` double NOT NULL,
            `status` enum('0','1') NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `poliklinik` (`kd_poli`, `nm_poli`, `registrasi`, `registrasilama`, `status`) VALUES
          ('-', '-', 0, 0, '1'),
          ('IGDK', 'IGD', 0, 0, '1');");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `reg_periksa` (
            `no_reg` varchar(8) DEFAULT NULL,
            `no_rawat` varchar(17) NOT NULL,
            `tgl_registrasi` date DEFAULT NULL,
            `jam_reg` time DEFAULT NULL,
            `kd_dokter` varchar(20) DEFAULT NULL,
            `no_rkm_medis` varchar(15) DEFAULT NULL,
            `kd_poli` char(5) DEFAULT NULL,
            `p_jawab` varchar(100) DEFAULT NULL,
            `almt_pj` varchar(200) DEFAULT NULL,
            `hubunganpj` varchar(20) DEFAULT NULL,
            `biaya_reg` double DEFAULT NULL,
            `stts` enum('Belum','Sudah','Batal','Berkas Diterima','Dirujuk','Meninggal','Dirawat','Pulang Paksa') DEFAULT NULL,
            `stts_daftar` enum('-','Lama','Baru') NOT NULL,
            `status_lanjut` enum('Ralan','Ranap') NOT NULL,
            `kd_pj` char(3) NOT NULL,
            `umurdaftar` int(11) DEFAULT NULL,
            `sttsumur` enum('Th','Bl','Hr') DEFAULT NULL,
            `status_bayar` enum('Sudah Bayar','Belum Bayar') NOT NULL,
            `status_poli` enum('Lama','Baru') NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          /*
          $core->db()->pdo()->exec("INSERT INTO `reg_periksa` (`no_reg`, `no_rawat`, `tgl_registrasi`, `jam_reg`, `kd_dokter`, `no_rkm_medis`, `kd_poli`, `p_jawab`, `almt_pj`, `hubunganpj`, `biaya_reg`, `stts`, `stts_daftar`, `status_lanjut`, `kd_pj`, `umurdaftar`, `sttsumur`, `status_bayar`, `status_poli`) VALUES
          ('001', '2020/12/26/000001', '2020-12-26', '08:00:00', 'DR001', '000001', '-', '-', '-', 'AYAH', 0, 'Belum', 'Baru', 'Ralan', '-', 1, 'Th', 'Sudah Bayar', 'Baru');");
          */

          $core->db()->pdo()->exec("ALTER TABLE `dokter`
            ADD PRIMARY KEY (`kd_dokter`),
            ADD KEY `kd_sps` (`kd_sps`),
            ADD KEY `nm_dokter` (`nm_dokter`),
            ADD KEY `jk` (`jk`),
            ADD KEY `tmp_lahir` (`tmp_lahir`),
            ADD KEY `tgl_lahir` (`tgl_lahir`),
            ADD KEY `gol_drh` (`gol_drh`),
            ADD KEY `agama` (`agama`),
            ADD KEY `almt_tgl` (`almt_tgl`),
            ADD KEY `no_telp` (`no_telp`),
            ADD KEY `stts_nikah` (`stts_nikah`),
            ADD KEY `alumni` (`alumni`),
            ADD KEY `no_ijn_praktek` (`no_ijn_praktek`),
            ADD KEY `kd_dokter` (`kd_dokter`),
            ADD KEY `status` (`status`);");

          $core->db()->pdo()->exec("ALTER TABLE `pegawai`
            ADD PRIMARY KEY (`id`),
            ADD UNIQUE KEY `nik_2` (`nik`),
            ADD KEY `departemen` (`departemen`),
            ADD KEY `bidang` (`bidang`),
            ADD KEY `stts_wp` (`stts_wp`),
            ADD KEY `stts_kerja` (`stts_kerja`),
            ADD KEY `pendidikan` (`pendidikan`),
            ADD KEY `indexins` (`indexins`),
            ADD KEY `jnj_jabatan` (`jnj_jabatan`),
            ADD KEY `bpd` (`bpd`),
            ADD KEY `nama` (`nama`),
            ADD KEY `jbtn` (`jbtn`),
            ADD KEY `npwp` (`npwp`),
            ADD KEY `dankes` (`dankes`),
            ADD KEY `cuti_diambil` (`cuti_diambil`),
            ADD KEY `mulai_kontrak` (`mulai_kontrak`),
            ADD KEY `stts_aktif` (`stts_aktif`),
            ADD KEY `tmp_lahir` (`tmp_lahir`),
            ADD KEY `alamat` (`alamat`),
            ADD KEY `mulai_kerja` (`mulai_kerja`),
            ADD KEY `gapok` (`gapok`),
            ADD KEY `kota` (`kota`),
            ADD KEY `pengurang` (`pengurang`),
            ADD KEY `indek` (`indek`),
            ADD KEY `jk` (`jk`),
            ADD KEY `ms_kerja` (`ms_kerja`),
            ADD KEY `tgl_lahir` (`tgl_lahir`),
            ADD KEY `rekening` (`rekening`),
            ADD KEY `wajibmasuk` (`wajibmasuk`),
            ADD KEY `kode_emergency` (`kode_emergency`) USING BTREE,
            ADD KEY `kode_kelompok` (`kode_kelompok`) USING BTREE,
            ADD KEY `kode_resiko` (`kode_resiko`) USING BTREE;");

          $core->db()->pdo()->exec("ALTER TABLE `poliklinik`
            ADD PRIMARY KEY (`kd_poli`),
            ADD KEY `nm_poli` (`nm_poli`),
            ADD KEY `registrasi` (`registrasi`),
            ADD KEY `registrasilama` (`registrasilama`);");

          $core->db()->pdo()->exec("ALTER TABLE `reg_periksa`
            ADD PRIMARY KEY (`no_rawat`),
            ADD KEY `no_rkm_medis` (`no_rkm_medis`),
            ADD KEY `kd_poli` (`kd_poli`),
            ADD KEY `kd_pj` (`kd_pj`),
            ADD KEY `status_lanjut` (`status_lanjut`),
            ADD KEY `kd_dokter` (`kd_dokter`),
            ADD KEY `status_bayar` (`status_bayar`) USING BTREE;");

          $core->db()->pdo()->exec("ALTER TABLE `pegawai`
            MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;");

          $core->db()->pdo()->exec("ALTER TABLE `dokter`
            ADD CONSTRAINT `dokter_ibfk_2` FOREIGN KEY (`kd_sps`) REFERENCES `spesialis` (`kd_sps`) ON UPDATE CASCADE,
            ADD CONSTRAINT `dokter_ibfk_3` FOREIGN KEY (`kd_dokter`) REFERENCES `pegawai` (`nik`) ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("ALTER TABLE `pegawai`
            ADD CONSTRAINT `pegawai_ibfk_1` FOREIGN KEY (`jnj_jabatan`) REFERENCES `jnj_jabatan` (`kode`) ON UPDATE CASCADE,
            ADD CONSTRAINT `pegawai_ibfk_10` FOREIGN KEY (`kode_kelompok`) REFERENCES `kelompok_jabatan` (`kode_kelompok`) ON UPDATE CASCADE,
            ADD CONSTRAINT `pegawai_ibfk_11` FOREIGN KEY (`kode_resiko`) REFERENCES `resiko_kerja` (`kode_resiko`) ON UPDATE CASCADE,
            ADD CONSTRAINT `pegawai_ibfk_2` FOREIGN KEY (`departemen`) REFERENCES `departemen` (`dep_id`) ON UPDATE CASCADE,
            ADD CONSTRAINT `pegawai_ibfk_3` FOREIGN KEY (`bidang`) REFERENCES `bidang` (`nama`) ON UPDATE CASCADE,
            ADD CONSTRAINT `pegawai_ibfk_4` FOREIGN KEY (`stts_wp`) REFERENCES `stts_wp` (`stts`) ON UPDATE CASCADE,
            ADD CONSTRAINT `pegawai_ibfk_5` FOREIGN KEY (`stts_kerja`) REFERENCES `stts_kerja` (`stts`) ON UPDATE CASCADE,
            ADD CONSTRAINT `pegawai_ibfk_6` FOREIGN KEY (`pendidikan`) REFERENCES `pendidikan` (`tingkat`) ON UPDATE CASCADE,
            ADD CONSTRAINT `pegawai_ibfk_7` FOREIGN KEY (`indexins`) REFERENCES `departemen` (`dep_id`) ON UPDATE CASCADE,
            ADD CONSTRAINT `pegawai_ibfk_8` FOREIGN KEY (`bpd`) REFERENCES `bank` (`namabank`) ON UPDATE CASCADE,
            ADD CONSTRAINT `pegawai_ibfk_9` FOREIGN KEY (`kode_emergency`) REFERENCES `emergency_index` (`kode_emergency`) ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("ALTER TABLE `reg_periksa`
            ADD CONSTRAINT `reg_periksa_ibfk_3` FOREIGN KEY (`kd_poli`) REFERENCES `poliklinik` (`kd_poli`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `reg_periksa_ibfk_4` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `reg_periksa_ibfk_6` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON DELETE NO ACTION ON UPDATE CASCADE,
            ADD CONSTRAINT `reg_periksa_ibfk_7` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `petugas` (
            `nip` varchar(20) NOT NULL,
            `nama` varchar(50) DEFAULT NULL,
            `jk` enum('L','P') DEFAULT NULL,
            `tmp_lahir` varchar(20) DEFAULT NULL,
            `tgl_lahir` date DEFAULT NULL,
            `gol_darah` enum('A','B','O','AB','-') DEFAULT NULL,
            `agama` varchar(12) DEFAULT NULL,
            `stts_nikah` enum('BELUM MENIKAH','MENIKAH','JANDA','DUDHA','JOMBLO') DEFAULT NULL,
            `alamat` varchar(60) DEFAULT NULL,
            `kd_jbtn` char(4) DEFAULT NULL,
            `no_telp` varchar(13) DEFAULT NULL,
            `status` enum('0','1') DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `petugas` (`nip`, `nama`, `jk`, `tmp_lahir`, `tgl_lahir`, `gol_darah`, `agama`, `stts_nikah`, `alamat`, `kd_jbtn`, `no_telp`, `status`) VALUES
          ('DR001', 'dr. Ataaka Muhammad', 'L', 'Barabai', '2020-12-01', 'A', 'Islam', 'MENIKAH', '-', '-', '0', '1');");

          $core->db()->pdo()->exec("ALTER TABLE `petugas`
            ADD PRIMARY KEY (`nip`),
            ADD KEY `kd_jbtn` (`kd_jbtn`),
            ADD KEY `nama` (`nama`),
            ADD KEY `nip` (`nip`),
            ADD KEY `tmp_lahir` (`tmp_lahir`),
            ADD KEY `tgl_lahir` (`tgl_lahir`),
            ADD KEY `agama` (`agama`),
            ADD KEY `stts_nikah` (`stts_nikah`),
            ADD KEY `alamat` (`alamat`);");

          $core->db()->pdo()->exec("ALTER TABLE `petugas`
            ADD CONSTRAINT `petugas_ibfk_4` FOREIGN KEY (`nip`) REFERENCES `pegawai` (`nik`) ON UPDATE CASCADE,
            ADD CONSTRAINT `petugas_ibfk_5` FOREIGN KEY (`kd_jbtn`) REFERENCES `jabatan` (`kd_jbtn`) ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `pemeriksaan_ralan` (
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
            `imun_ke` enum('-','1','2','3','4','5','6','7','8','10','11','12','13') DEFAULT NULL,
            `rtl` varchar(400) NOT NULL,
            `penilaian` varchar(400) NOT NULL,
            `instruksi` varchar(400) NOT NULL,
            `nip` varchar(20) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `pemeriksaan_ralan`
            ADD PRIMARY KEY (`no_rawat`,`tgl_perawatan`,`jam_rawat`) USING BTREE,
            ADD KEY `no_rawat` (`no_rawat`),
            ADD KEY `nip` (`nip`) USING BTREE;");

          $core->db()->pdo()->exec("ALTER TABLE `pemeriksaan_ralan`
            ADD CONSTRAINT `pemeriksaan_ralan_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `pemeriksaan_ralan_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `pegawai` (`nik`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `rawat_jl_dr` (
            `no_rawat` varchar(17) NOT NULL DEFAULT '',
            `kd_jenis_prw` varchar(15) NOT NULL,
            `kd_dokter` varchar(20) NOT NULL,
            `tgl_perawatan` date NOT NULL,
            `jam_rawat` time NOT NULL,
            `material` double NOT NULL,
            `bhp` double NOT NULL,
            `tarif_tindakandr` double NOT NULL,
            `kso` double DEFAULT NULL,
            `menejemen` double DEFAULT NULL,
            `biaya_rawat` double DEFAULT NULL,
            `stts_bayar` enum('Sudah','Belum','Suspen') DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `jns_perawatan` (
            `kd_jenis_prw` varchar(15) NOT NULL,
            `nm_perawatan` varchar(80) DEFAULT NULL,
            `kd_kategori` char(5) DEFAULT NULL,
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
            `kd_poli` char(5) NOT NULL,
            `status` enum('0','1') NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `jns_perawatan`
            ADD PRIMARY KEY (`kd_jenis_prw`),
            ADD KEY `kd_kategori` (`kd_kategori`),
            ADD KEY `kd_pj` (`kd_pj`),
            ADD KEY `kd_poli` (`kd_poli`),
            ADD KEY `nm_perawatan` (`nm_perawatan`),
            ADD KEY `material` (`material`),
            ADD KEY `tarif_tindakandr` (`tarif_tindakandr`),
            ADD KEY `tarif_tindakanpr` (`tarif_tindakanpr`),
            ADD KEY `total_byrdr` (`total_byrdr`),
            ADD KEY `total_byrpr` (`total_byrpr`),
            ADD KEY `kso` (`kso`),
            ADD KEY `menejemen` (`menejemen`),
            ADD KEY `status` (`status`),
            ADD KEY `total_byrdrpr` (`total_byrdrpr`),
            ADD KEY `bhp` (`bhp`);");

          $core->db()->pdo()->exec("ALTER TABLE `jns_perawatan`
            ADD CONSTRAINT `jns_perawatan_ibfk_1` FOREIGN KEY (`kd_kategori`) REFERENCES `kategori_perawatan` (`kd_kategori`) ON UPDATE CASCADE,
            ADD CONSTRAINT `jns_perawatan_ibfk_2` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON UPDATE CASCADE,
            ADD CONSTRAINT `jns_perawatan_ibfk_3` FOREIGN KEY (`kd_poli`) REFERENCES `poliklinik` (`kd_poli`) ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `rawat_jl_drpr` (
            `no_rawat` varchar(17) NOT NULL DEFAULT '',
            `kd_jenis_prw` varchar(15) NOT NULL,
            `kd_dokter` varchar(20) NOT NULL,
            `nip` varchar(20) NOT NULL,
            `tgl_perawatan` date NOT NULL,
            `jam_rawat` time NOT NULL,
            `material` double DEFAULT NULL,
            `bhp` double NOT NULL,
            `tarif_tindakandr` double DEFAULT NULL,
            `tarif_tindakanpr` double DEFAULT NULL,
            `kso` double DEFAULT NULL,
            `menejemen` double DEFAULT NULL,
            `biaya_rawat` double DEFAULT NULL,
            `stts_bayar` enum('Sudah','Belum','Suspen') DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `rawat_jl_pr` (
            `no_rawat` varchar(17) NOT NULL DEFAULT '',
            `kd_jenis_prw` varchar(15) NOT NULL,
            `nip` varchar(20) NOT NULL DEFAULT '',
            `tgl_perawatan` date NOT NULL,
            `jam_rawat` time NOT NULL,
            `material` double NOT NULL,
            `bhp` double NOT NULL,
            `tarif_tindakanpr` double NOT NULL,
            `kso` double DEFAULT NULL,
            `menejemen` double DEFAULT NULL,
            `biaya_rawat` double DEFAULT NULL,
            `stts_bayar` enum('Sudah','Belum','Suspen') DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `rawat_jl_dr`
            ADD PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`kd_dokter`,`tgl_perawatan`,`jam_rawat`) USING BTREE,
            ADD KEY `no_rawat` (`no_rawat`),
            ADD KEY `kd_jenis_prw` (`kd_jenis_prw`),
            ADD KEY `kd_dokter` (`kd_dokter`),
            ADD KEY `biaya_rawat` (`biaya_rawat`);");

          $core->db()->pdo()->exec("ALTER TABLE `rawat_jl_drpr`
            ADD PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`kd_dokter`,`nip`,`tgl_perawatan`,`jam_rawat`) USING BTREE,
            ADD KEY `rawat_jl_drpr_ibfk_2` (`kd_jenis_prw`),
            ADD KEY `rawat_jl_drpr_ibfk_3` (`kd_dokter`),
            ADD KEY `rawat_jl_drpr_ibfk_4` (`nip`),
            ADD KEY `no_rawat` (`no_rawat`);");

          $core->db()->pdo()->exec("ALTER TABLE `rawat_jl_pr`
            ADD PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`nip`,`tgl_perawatan`,`jam_rawat`) USING BTREE,
            ADD KEY `no_rawat` (`no_rawat`),
            ADD KEY `kd_jenis_prw` (`kd_jenis_prw`),
            ADD KEY `nip` (`nip`),
            ADD KEY `biaya_rawat` (`biaya_rawat`);");

          $core->db()->pdo()->exec("ALTER TABLE `rawat_jl_dr`
            ADD CONSTRAINT `rawat_jl_dr_ibfk_2` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `rawat_jl_dr_ibfk_3` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `rawat_jl_dr_ibfk_5` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("ALTER TABLE `rawat_jl_drpr`
            ADD CONSTRAINT `rawat_jl_drpr_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
            ADD CONSTRAINT `rawat_jl_drpr_ibfk_2` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `rawat_jl_drpr_ibfk_3` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `rawat_jl_drpr_ibfk_4` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("ALTER TABLE `rawat_jl_pr`
            ADD CONSTRAINT `rawat_jl_pr_ibfk_10` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `rawat_jl_pr_ibfk_8` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
            ADD CONSTRAINT `rawat_jl_pr_ibfk_9` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `diagnosa_pasien` (
            `no_rawat` varchar(17) NOT NULL,
            `kd_penyakit` varchar(10) NOT NULL,
            `status` enum('Ralan','Ranap') NOT NULL,
            `prioritas` tinyint(4) NOT NULL,
            `status_penyakit` enum('Lama','Baru') DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `prosedur_pasien` (
            `no_rawat` varchar(17) NOT NULL,
            `kode` varchar(8) NOT NULL,
            `status` enum('Ralan','Ranap') NOT NULL,
            `prioritas` tinyint(4) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `diagnosa_pasien`
            ADD PRIMARY KEY (`no_rawat`,`kd_penyakit`,`status`),
            ADD KEY `kd_penyakit` (`kd_penyakit`),
            ADD KEY `status` (`status`),
            ADD KEY `prioritas` (`prioritas`),
            ADD KEY `no_rawat` (`no_rawat`);");

          $core->db()->pdo()->exec("ALTER TABLE `prosedur_pasien`
            ADD PRIMARY KEY (`no_rawat`,`kode`,`status`),
            ADD KEY `kode` (`kode`);");

          $core->db()->pdo()->exec("ALTER TABLE `diagnosa_pasien`
            ADD CONSTRAINT `diagnosa_pasien_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
            ADD CONSTRAINT `diagnosa_pasien_ibfk_2` FOREIGN KEY (`kd_penyakit`) REFERENCES `penyakit` (`kd_penyakit`) ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("ALTER TABLE `prosedur_pasien`
            ADD CONSTRAINT `prosedur_pasien_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `prosedur_pasien_ibfk_2` FOREIGN KEY (`kode`) REFERENCES `icd9` (`kode`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `jns_perawatan_lab` (
            `kd_jenis_prw` varchar(15) NOT NULL,
            `nm_perawatan` varchar(80) DEFAULT NULL,
            `bagian_rs` double DEFAULT NULL,
            `bhp` double NOT NULL,
            `tarif_perujuk` double NOT NULL,
            `tarif_tindakan_dokter` double NOT NULL,
            `tarif_tindakan_petugas` double DEFAULT NULL,
            `kso` double DEFAULT NULL,
            `menejemen` double DEFAULT NULL,
            `total_byr` double DEFAULT NULL,
            `kd_pj` char(3) NOT NULL,
            `status` enum('0','1') NOT NULL,
            `kelas` enum('-','Rawat Jalan','Kelas 1','Kelas 2','Kelas 3','Kelas Utama','Kelas VIP','Kelas VVIP') NOT NULL,
            `kategori` enum('PK','PA') NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `jns_perawatan_radiologi` (
            `kd_jenis_prw` varchar(15) NOT NULL,
            `nm_perawatan` varchar(80) DEFAULT NULL,
            `bagian_rs` double DEFAULT NULL,
            `bhp` double NOT NULL,
            `tarif_perujuk` double NOT NULL,
            `tarif_tindakan_dokter` double NOT NULL,
            `tarif_tindakan_petugas` double DEFAULT NULL,
            `kso` double DEFAULT NULL,
            `menejemen` double DEFAULT NULL,
            `total_byr` double DEFAULT NULL,
            `kd_pj` char(3) NOT NULL,
            `status` enum('0','1') NOT NULL,
            `kelas` enum('-','Rawat Jalan','Kelas 1','Kelas 2','Kelas 3','Kelas Utama','Kelas VIP','Kelas VVIP') NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `periksa_lab` (
            `no_rawat` varchar(17) NOT NULL,
            `nip` varchar(20) NOT NULL,
            `kd_jenis_prw` varchar(15) NOT NULL,
            `tgl_periksa` date NOT NULL,
            `jam` time NOT NULL,
            `dokter_perujuk` varchar(20) NOT NULL,
            `bagian_rs` double NOT NULL,
            `bhp` double NOT NULL,
            `tarif_perujuk` double NOT NULL,
            `tarif_tindakan_dokter` double NOT NULL,
            `tarif_tindakan_petugas` double NOT NULL,
            `kso` double DEFAULT NULL,
            `menejemen` double DEFAULT NULL,
            `biaya` double NOT NULL,
            `kd_dokter` varchar(20) NOT NULL,
            `status` enum('Ralan','Ranap') DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `periksa_radiologi` (
            `no_rawat` varchar(17) NOT NULL,
            `nip` varchar(20) NOT NULL,
            `kd_jenis_prw` varchar(15) NOT NULL,
            `tgl_periksa` date NOT NULL,
            `jam` time NOT NULL,
            `dokter_perujuk` varchar(20) NOT NULL,
            `bagian_rs` double NOT NULL,
            `bhp` double NOT NULL,
            `tarif_perujuk` double NOT NULL,
            `tarif_tindakan_dokter` double NOT NULL,
            `tarif_tindakan_petugas` double NOT NULL,
            `kso` double DEFAULT NULL,
            `menejemen` double DEFAULT NULL,
            `biaya` double NOT NULL,
            `kd_dokter` varchar(20) NOT NULL,
            `status` enum('Ranap','Ralan') DEFAULT NULL,
            `proyeksi` varchar(50) NOT NULL,
            `kV` varchar(10) NOT NULL,
            `mAS` varchar(10) NOT NULL,
            `FFD` varchar(10) NOT NULL,
            `BSF` varchar(10) NOT NULL,
            `inak` varchar(10) NOT NULL,
            `jml_penyinaran` varchar(10) NOT NULL,
            `dosis` varchar(20) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `template_laboratorium` (
            `kd_jenis_prw` varchar(15) NOT NULL,
            `id_template` int(11) NOT NULL,
            `Pemeriksaan` varchar(200) NOT NULL,
            `satuan` varchar(20) NOT NULL,
            `nilai_rujukan_ld` varchar(30) NOT NULL,
            `nilai_rujukan_la` varchar(30) NOT NULL,
            `nilai_rujukan_pd` varchar(30) NOT NULL,
            `nilai_rujukan_pa` varchar(30) NOT NULL,
            `bagian_rs` double NOT NULL,
            `bhp` double NOT NULL,
            `bagian_perujuk` double NOT NULL,
            `bagian_dokter` double NOT NULL,
            `bagian_laborat` double NOT NULL,
            `kso` double DEFAULT NULL,
            `menejemen` double DEFAULT NULL,
            `biaya_item` double NOT NULL,
            `urut` int(4) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `jns_perawatan_lab`
            ADD PRIMARY KEY (`kd_jenis_prw`),
            ADD KEY `kd_pj` (`kd_pj`),
            ADD KEY `nm_perawatan` (`nm_perawatan`),
            ADD KEY `tarif_perujuk` (`tarif_perujuk`),
            ADD KEY `tarif_tindakan_dokter` (`tarif_tindakan_dokter`),
            ADD KEY `tarif_tindakan_petugas` (`tarif_tindakan_petugas`),
            ADD KEY `total_byr` (`total_byr`),
            ADD KEY `bagian_rs` (`bagian_rs`),
            ADD KEY `bhp` (`bhp`),
            ADD KEY `kso` (`kso`),
            ADD KEY `menejemen` (`menejemen`),
            ADD KEY `status` (`status`);");

          $core->db()->pdo()->exec("ALTER TABLE `jns_perawatan_radiologi`
            ADD PRIMARY KEY (`kd_jenis_prw`),
            ADD KEY `kd_pj` (`kd_pj`),
            ADD KEY `nm_perawatan` (`nm_perawatan`),
            ADD KEY `bagian_rs` (`bagian_rs`),
            ADD KEY `tarif_perujuk` (`tarif_perujuk`),
            ADD KEY `tarif_tindakan_dokter` (`tarif_tindakan_dokter`),
            ADD KEY `tarif_tindakan_petugas` (`tarif_tindakan_petugas`),
            ADD KEY `total_byr` (`total_byr`),
            ADD KEY `bhp` (`bhp`),
            ADD KEY `kso` (`kso`),
            ADD KEY `menejemen` (`menejemen`),
            ADD KEY `status` (`status`);");

          $core->db()->pdo()->exec("ALTER TABLE `periksa_lab`
            ADD PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`tgl_periksa`,`jam`),
            ADD KEY `nip` (`nip`),
            ADD KEY `kd_jenis_prw` (`kd_jenis_prw`),
            ADD KEY `kd_dokter` (`kd_dokter`),
            ADD KEY `dokter_perujuk` (`dokter_perujuk`);");

          $core->db()->pdo()->exec("ALTER TABLE `periksa_radiologi`
            ADD PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`tgl_periksa`,`jam`),
            ADD KEY `nip` (`nip`),
            ADD KEY `kd_jenis_prw` (`kd_jenis_prw`),
            ADD KEY `kd_dokter` (`kd_dokter`),
            ADD KEY `dokter_perujuk` (`dokter_perujuk`);");

          $core->db()->pdo()->exec("ALTER TABLE `template_laboratorium`
            ADD PRIMARY KEY (`id_template`),
            ADD KEY `kd_jenis_prw` (`kd_jenis_prw`),
            ADD KEY `Pemeriksaan` (`Pemeriksaan`),
            ADD KEY `satuan` (`satuan`),
            ADD KEY `nilai_rujukan_ld` (`nilai_rujukan_ld`),
            ADD KEY `nilai_rujukan_la` (`nilai_rujukan_la`),
            ADD KEY `nilai_rujukan_pd` (`nilai_rujukan_pd`),
            ADD KEY `nilai_rujukan_pa` (`nilai_rujukan_pa`),
            ADD KEY `bagian_rs` (`bagian_rs`),
            ADD KEY `bhp` (`bhp`),
            ADD KEY `bagian_perujuk` (`bagian_perujuk`),
            ADD KEY `bagian_dokter` (`bagian_dokter`),
            ADD KEY `bagian_laborat` (`bagian_laborat`),
            ADD KEY `kso` (`kso`),
            ADD KEY `menejemen` (`menejemen`),
            ADD KEY `biaya_item` (`biaya_item`),
            ADD KEY `urut` (`urut`);");

          $core->db()->pdo()->exec("ALTER TABLE `template_laboratorium`
            MODIFY `id_template` int(11) NOT NULL AUTO_INCREMENT;");

          $core->db()->pdo()->exec("ALTER TABLE `jns_perawatan_lab`
            ADD CONSTRAINT `jns_perawatan_lab_ibfk_1` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("ALTER TABLE `jns_perawatan_radiologi`
            ADD CONSTRAINT `jns_perawatan_radiologi_ibfk_1` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("ALTER TABLE `periksa_lab`
            ADD CONSTRAINT `periksa_lab_ibfk_10` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `periksa_lab_ibfk_11` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_lab` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `periksa_lab_ibfk_12` FOREIGN KEY (`dokter_perujuk`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `periksa_lab_ibfk_13` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `periksa_lab_ibfk_9` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("ALTER TABLE `periksa_radiologi`
            ADD CONSTRAINT `periksa_radiologi_ibfk_4` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
            ADD CONSTRAINT `periksa_radiologi_ibfk_5` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `periksa_radiologi_ibfk_6` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_radiologi` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `periksa_radiologi_ibfk_7` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `periksa_radiologi_ibfk_8` FOREIGN KEY (`dokter_perujuk`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("ALTER TABLE `template_laboratorium`
            ADD CONSTRAINT `template_laboratorium_ibfk_1` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_lab` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `detail_periksa_lab` (
            `no_rawat` varchar(17) NOT NULL,
            `kd_jenis_prw` varchar(15) NOT NULL,
            `tgl_periksa` date NOT NULL,
            `jam` time NOT NULL,
            `id_template` int(11) NOT NULL,
            `nilai` varchar(60) NOT NULL,
            `nilai_rujukan` varchar(30) NOT NULL,
            `keterangan` varchar(60) NOT NULL,
            `bagian_rs` double NOT NULL,
            `bhp` double NOT NULL,
            `bagian_perujuk` double NOT NULL,
            `bagian_dokter` double NOT NULL,
            `bagian_laborat` double NOT NULL,
            `kso` double DEFAULT NULL,
            `menejemen` double DEFAULT NULL,
            `biaya_item` double NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `detail_periksa_lab`
            ADD PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`tgl_periksa`,`jam`,`id_template`),
            ADD KEY `id_template` (`id_template`),
            ADD KEY `kd_jenis_prw` (`kd_jenis_prw`),
            ADD KEY `tgl_periksa` (`tgl_periksa`),
            ADD KEY `jam` (`jam`),
            ADD KEY `nilai` (`nilai`),
            ADD KEY `nilai_rujukan` (`nilai_rujukan`),
            ADD KEY `keterangan` (`keterangan`),
            ADD KEY `biaya_item` (`biaya_item`),
            ADD KEY `menejemen` (`menejemen`),
            ADD KEY `kso` (`kso`),
            ADD KEY `bagian_rs` (`bagian_rs`),
            ADD KEY `bhp` (`bhp`),
            ADD KEY `bagian_perujuk` (`bagian_perujuk`),
            ADD KEY `bagian_dokter` (`bagian_dokter`),
            ADD KEY `bagian_laborat` (`bagian_laborat`);");

          $core->db()->pdo()->exec("ALTER TABLE `detail_periksa_lab`
            ADD CONSTRAINT `detail_periksa_lab_ibfk_10` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
            ADD CONSTRAINT `detail_periksa_lab_ibfk_11` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_lab` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `detail_periksa_lab_ibfk_12` FOREIGN KEY (`id_template`) REFERENCES `template_laboratorium` (`id_template`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `hasil_radiologi` (
            `no_rawat` varchar(17) NOT NULL,
            `tgl_periksa` date NOT NULL,
            `jam` time NOT NULL,
            `hasil` text NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `hasil_radiologi`
            ADD PRIMARY KEY (`no_rawat`,`tgl_periksa`,`jam`),
            ADD KEY `no_rawat` (`no_rawat`);");

          $core->db()->pdo()->exec("ALTER TABLE `hasil_radiologi`
            ADD CONSTRAINT `hasil_radiologi_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `databarang` (
            `kode_brng` varchar(15) NOT NULL DEFAULT '',
            `nama_brng` varchar(80) DEFAULT NULL,
            `kode_satbesar` char(4) NOT NULL,
            `kode_sat` char(4) DEFAULT NULL,
            `letak_barang` varchar(50) DEFAULT NULL,
            `dasar` double NOT NULL,
            `h_beli` double DEFAULT NULL,
            `ralan` double DEFAULT NULL,
            `kelas1` double DEFAULT NULL,
            `kelas2` double DEFAULT NULL,
            `kelas3` double DEFAULT NULL,
            `utama` double DEFAULT NULL,
            `vip` double DEFAULT NULL,
            `vvip` double DEFAULT NULL,
            `beliluar` double DEFAULT NULL,
            `jualbebas` double DEFAULT NULL,
            `karyawan` double DEFAULT NULL,
            `stokminimal` double DEFAULT NULL,
            `kdjns` char(4) DEFAULT NULL,
            `isi` double NOT NULL,
            `kapasitas` double NOT NULL,
            `expire` date DEFAULT NULL,
            `status` enum('0','1') NOT NULL,
            `kode_industri` char(5) DEFAULT NULL,
            `kode_kategori` char(4) DEFAULT NULL,
            `kode_golongan` char(4) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("INSERT INTO `databarang` (`kode_brng`, `nama_brng`, `kode_satbesar`, `kode_sat`, `letak_barang`, `dasar`, `h_beli`, `ralan`, `kelas1`, `kelas2`, `kelas3`, `utama`, `vip`, `vvip`, `beliluar`, `jualbebas`, `karyawan`, `stokminimal`, `kdjns`, `isi`, `kapasitas`, `expire`, `status`, `kode_industri`, `kode_kategori`, `kode_golongan`) VALUES
          ('B000000001', '-', '-', '-', '-', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '-', 0, 0, '2019-09-19', '1', '-', '-', '-');");

          $core->db()->pdo()->exec("ALTER TABLE `databarang`
            ADD PRIMARY KEY (`kode_brng`),
            ADD KEY `kode_sat` (`kode_sat`),
            ADD KEY `kdjns` (`kdjns`),
            ADD KEY `nama_brng` (`nama_brng`),
            ADD KEY `letak_barang` (`letak_barang`),
            ADD KEY `h_beli` (`h_beli`),
            ADD KEY `h_distributor` (`ralan`),
            ADD KEY `h_grosir` (`kelas1`),
            ADD KEY `h_retail` (`kelas2`),
            ADD KEY `stok` (`stokminimal`),
            ADD KEY `kapasitas` (`kapasitas`),
            ADD KEY `kode_industri` (`kode_industri`),
            ADD KEY `kelas3` (`kelas3`),
            ADD KEY `utama` (`utama`),
            ADD KEY `vip` (`vip`),
            ADD KEY `vvip` (`vvip`),
            ADD KEY `beliluar` (`beliluar`),
            ADD KEY `jualbebas` (`jualbebas`),
            ADD KEY `karyawan` (`karyawan`),
            ADD KEY `expire` (`expire`),
            ADD KEY `status` (`status`),
            ADD KEY `kode_kategori` (`kode_kategori`),
            ADD KEY `kode_golongan` (`kode_golongan`),
            ADD KEY `kode_satbesar` (`kode_satbesar`) USING BTREE;");

          $core->db()->pdo()->exec("ALTER TABLE `databarang`
            ADD CONSTRAINT `databarang_ibfk_2` FOREIGN KEY (`kdjns`) REFERENCES `jenis` (`kdjns`) ON UPDATE CASCADE,
            ADD CONSTRAINT `databarang_ibfk_3` FOREIGN KEY (`kode_sat`) REFERENCES `kodesatuan` (`kode_sat`) ON UPDATE CASCADE,
            ADD CONSTRAINT `databarang_ibfk_4` FOREIGN KEY (`kode_industri`) REFERENCES `industrifarmasi` (`kode_industri`) ON UPDATE CASCADE,
            ADD CONSTRAINT `databarang_ibfk_5` FOREIGN KEY (`kode_kategori`) REFERENCES `kategori_barang` (`kode`) ON UPDATE CASCADE,
            ADD CONSTRAINT `databarang_ibfk_6` FOREIGN KEY (`kode_golongan`) REFERENCES `golongan_barang` (`kode`) ON UPDATE CASCADE,
            ADD CONSTRAINT `databarang_ibfk_7` FOREIGN KEY (`kode_satbesar`) REFERENCES `kodesatuan` (`kode_sat`) ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `gudangbarang` (
            `kode_brng` varchar(15) NOT NULL,
            `kd_bangsal` char(5) NOT NULL DEFAULT '',
            `stok` double NOT NULL,
            `no_batch` varchar(20) NOT NULL,
            `no_faktur` varchar(20) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `gudangbarang`
            ADD PRIMARY KEY (`kode_brng`,`kd_bangsal`,`no_batch`,`no_faktur`) USING BTREE,
            ADD KEY `kode_brng` (`kode_brng`),
            ADD KEY `stok` (`stok`),
            ADD KEY `kd_bangsal` (`kd_bangsal`) USING BTREE;");

          $core->db()->pdo()->exec("ALTER TABLE `gudangbarang`
            ADD CONSTRAINT `gudangbarang_ibfk_1` FOREIGN KEY (`kd_bangsal`) REFERENCES `bangsal` (`kd_bangsal`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `gudangbarang_ibfk_2` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `resep_obat` (
            `no_resep` varchar(14) NOT NULL DEFAULT '',
            `tgl_perawatan` date DEFAULT NULL,
            `jam` time NOT NULL,
            `no_rawat` varchar(17) NOT NULL DEFAULT '',
            `kd_dokter` varchar(20) NOT NULL,
            `tgl_peresepan` date DEFAULT NULL,
            `jam_peresepan` time DEFAULT NULL,
            `status` enum('ralan','ranap') DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `resep_obat`
            ADD PRIMARY KEY (`no_resep`),
            ADD UNIQUE KEY `tgl_perawatan` (`tgl_perawatan`,`jam`,`no_rawat`),
            ADD KEY `no_rawat` (`no_rawat`),
            ADD KEY `kd_dokter` (`kd_dokter`);");

          $core->db()->pdo()->exec("ALTER TABLE `resep_obat`
            ADD CONSTRAINT `resep_obat_ibfk_3` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
            ADD CONSTRAINT `resep_obat_ibfk_4` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `resep_dokter` (
            `no_resep` varchar(14) DEFAULT NULL,
            `kode_brng` varchar(15) DEFAULT NULL,
            `jml` double DEFAULT NULL,
            `aturan_pakai` varchar(150) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `resep_dokter`
            ADD KEY `no_resep` (`no_resep`),
            ADD KEY `kode_brng` (`kode_brng`);");

          $core->db()->pdo()->exec("ALTER TABLE `resep_dokter`
            ADD CONSTRAINT `resep_dokter_ibfk_1` FOREIGN KEY (`no_resep`) REFERENCES `resep_obat` (`no_resep`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `resep_dokter_ibfk_2` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `aturan_pakai` (
            `tgl_perawatan` date NOT NULL DEFAULT '0000-00-00',
            `jam` time NOT NULL DEFAULT '00:00:00',
            `no_rawat` varchar(17) NOT NULL DEFAULT '',
            `kode_brng` varchar(15) NOT NULL DEFAULT '',
            `aturan` varchar(150) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `aturan_pakai`
            ADD PRIMARY KEY (`tgl_perawatan`,`jam`,`no_rawat`,`kode_brng`),
            ADD KEY `no_rawat` (`no_rawat`),
            ADD KEY `kode_brng` (`kode_brng`);");

          $core->db()->pdo()->exec("ALTER TABLE `aturan_pakai`
            ADD CONSTRAINT `aturan_pakai_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `aturan_pakai_ibfk_2` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `berkas_digital_perawatan` (
            `no_rawat` varchar(17) NOT NULL,
            `kode` varchar(10) NOT NULL,
            `lokasi_file` varchar(600) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `berkas_digital_perawatan`
            ADD PRIMARY KEY (`no_rawat`,`kode`,`lokasi_file`) USING BTREE,
            ADD KEY `kode` (`kode`);");

          $core->db()->pdo()->exec("ALTER TABLE `berkas_digital_perawatan`
            ADD CONSTRAINT `berkas_digital_perawatan_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `berkas_digital_perawatan_ibfk_2` FOREIGN KEY (`kode`) REFERENCES `master_berkas_digital` (`kode`) ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `detail_pemberian_obat` (
            `tgl_perawatan` date NOT NULL DEFAULT '0000-00-00',
            `jam` time NOT NULL DEFAULT '00:00:00',
            `no_rawat` varchar(17) NOT NULL DEFAULT '',
            `kode_brng` varchar(15) NOT NULL,
            `h_beli` double DEFAULT NULL,
            `biaya_obat` double DEFAULT NULL,
            `jml` double NOT NULL,
            `embalase` double DEFAULT NULL,
            `tuslah` double DEFAULT NULL,
            `total` double NOT NULL,
            `status` enum('Ralan','Ranap') DEFAULT NULL,
            `kd_bangsal` char(5) DEFAULT NULL,
            `no_batch` varchar(20) NOT NULL,
            `no_faktur` varchar(20) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `detail_pemberian_obat`
            ADD PRIMARY KEY (`tgl_perawatan`,`jam`,`no_rawat`,`kode_brng`,`no_batch`,`no_faktur`) USING BTREE,
            ADD KEY `no_rawat` (`no_rawat`),
            ADD KEY `kd_obat` (`kode_brng`),
            ADD KEY `tgl_perawatan` (`tgl_perawatan`),
            ADD KEY `jam` (`jam`),
            ADD KEY `jml` (`jml`),
            ADD KEY `tambahan` (`embalase`),
            ADD KEY `total` (`total`),
            ADD KEY `biaya_obat` (`biaya_obat`),
            ADD KEY `kd_bangsal` (`kd_bangsal`),
            ADD KEY `tuslah` (`tuslah`) USING BTREE,
            ADD KEY `status` (`status`) USING BTREE;");

          $core->db()->pdo()->exec("ALTER TABLE `detail_pemberian_obat`
            ADD CONSTRAINT `detail_pemberian_obat_ibfk_3` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `detail_pemberian_obat_ibfk_4` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `detail_pemberian_obat_ibfk_5` FOREIGN KEY (`kd_bangsal`) REFERENCES `bangsal` (`kd_bangsal`) ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `riwayat_barang_medis` (
            `kode_brng` varchar(15) DEFAULT NULL,
            `stok_awal` double DEFAULT NULL,
            `masuk` double DEFAULT NULL,
            `keluar` double DEFAULT NULL,
            `stok_akhir` double NOT NULL,
            `posisi` enum('Pemberian Obat','Pengadaan','Penerimaan','Piutang','Retur Beli','Retur Jual','Retur Piutang','Mutasi','Opname','Resep Pulang','Retur Pasien','Stok Pasien Ranap','Pengambilan Medis','Penjualan','Stok Keluar','Hibah') DEFAULT NULL,
            `tanggal` date DEFAULT NULL,
            `jam` time DEFAULT NULL,
            `petugas` varchar(20) DEFAULT NULL,
            `kd_bangsal` char(5) DEFAULT NULL,
            `status` enum('Simpan','Hapus') DEFAULT NULL,
            `no_batch` varchar(20) NOT NULL,
            `no_faktur` varchar(20) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;");

          $core->db()->pdo()->exec("ALTER TABLE `riwayat_barang_medis`
            ADD KEY `riwayat_barang_medis_ibfk_1` (`kode_brng`) USING BTREE,
            ADD KEY `kd_bangsal` (`kd_bangsal`) USING BTREE;");

          $core->db()->pdo()->exec("ALTER TABLE `riwayat_barang_medis`
            ADD CONSTRAINT `riwayat_barang_medis_ibfk_1` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `riwayat_barang_medis_ibfk_2` FOREIGN KEY (`kd_bangsal`) REFERENCES `bangsal` (`kd_bangsal`) ON DELETE CASCADE ON UPDATE CASCADE;");


          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `gambar_radiologi` (
            `no_rawat` varchar(17) NOT NULL,
            `tgl_periksa` date NOT NULL,
            `jam` time NOT NULL,
            `lokasi_gambar` varchar(500) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `gambar_radiologi`
            ADD PRIMARY KEY (`no_rawat`,`tgl_periksa`,`jam`,`lokasi_gambar`);");

          $core->db()->pdo()->exec("ALTER TABLE `gambar_radiologi`
            ADD CONSTRAINT `gambar_radiologi_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `catatan_perawatan` (
            `tanggal` date DEFAULT NULL,
            `jam` time DEFAULT NULL,
            `no_rawat` varchar(17) DEFAULT NULL,
            `kd_dokter` varchar(20) DEFAULT NULL,
            `catatan` varchar(700) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `catatan_perawatan`
            ADD KEY `no_rawat` (`no_rawat`),
            ADD KEY `kd_dokter` (`kd_dokter`);");

          $core->db()->pdo()->exec("ALTER TABLE `catatan_perawatan`
            ADD CONSTRAINT `catatan_perawatan_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `catatan_perawatan_ibfk_2` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `skdp_bpjs` (
            `tahun` year(4) NOT NULL,
            `no_rkm_medis` varchar(15) DEFAULT NULL,
            `diagnosa` varchar(50) NOT NULL,
            `terapi` varchar(50) NOT NULL,
            `alasan1` varchar(50) DEFAULT NULL,
            `alasan2` varchar(50) DEFAULT NULL,
            `rtl1` varchar(50) DEFAULT NULL,
            `rtl2` varchar(50) DEFAULT NULL,
            `tanggal_datang` date DEFAULT NULL,
            `tanggal_rujukan` date NOT NULL,
            `no_antrian` varchar(6) NOT NULL,
            `kd_dokter` varchar(20) DEFAULT NULL,
            `status` enum('Menunggu','Sudah Periksa','Batal Periksa') NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;");

          $core->db()->pdo()->exec("ALTER TABLE `skdp_bpjs`
            ADD PRIMARY KEY (`tahun`,`no_antrian`) USING BTREE,
            ADD KEY `no_rkm_medis` (`no_rkm_medis`) USING BTREE,
            ADD KEY `kd_dokter` (`kd_dokter`) USING BTREE;");

          $core->db()->pdo()->exec("ALTER TABLE `skdp_bpjs`
            ADD CONSTRAINT `skdp_bpjs_ibfk_1` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON UPDATE CASCADE,
            ADD CONSTRAINT `skdp_bpjs_ibfk_2` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `jadwal` (
            `kd_dokter` varchar(20) NOT NULL,
            `hari_kerja` enum('SENIN','SELASA','RABU','KAMIS','JUMAT','SABTU','AKHAD') NOT NULL DEFAULT 'SENIN',
            `jam_mulai` time NOT NULL DEFAULT '00:00:00',
            `jam_selesai` time DEFAULT NULL,
            `kd_poli` char(5) DEFAULT NULL,
            `kuota` int(11) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `jadwal`
            ADD PRIMARY KEY (`kd_dokter`,`hari_kerja`,`jam_mulai`),
            ADD KEY `kd_dokter` (`kd_dokter`),
            ADD KEY `kd_poli` (`kd_poli`),
            ADD KEY `jam_mulai` (`jam_mulai`),
            ADD KEY `jam_selesai` (`jam_selesai`);");

          $core->db()->pdo()->exec("ALTER TABLE `jadwal`
            ADD CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`kd_poli`) REFERENCES `poliklinik` (`kd_poli`) ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `booking_registrasi` (
            `tanggal_booking` date DEFAULT NULL,
            `jam_booking` time DEFAULT NULL,
            `no_rkm_medis` varchar(15) NOT NULL,
            `tanggal_periksa` date NOT NULL,
            `kd_dokter` varchar(20) DEFAULT NULL,
            `kd_poli` varchar(5) DEFAULT NULL,
            `no_reg` varchar(8) DEFAULT NULL,
            `kd_pj` char(3) DEFAULT NULL,
            `limit_reg` int(1) DEFAULT NULL,
            `waktu_kunjungan` datetime DEFAULT NULL,
            `status` enum('Terdaftar','Belum','Batal','Dokter Berhalangan') DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `booking_registrasi`
            ADD PRIMARY KEY (`no_rkm_medis`,`tanggal_periksa`),
            ADD KEY `kd_dokter` (`kd_dokter`),
            ADD KEY `kd_poli` (`kd_poli`),
            ADD KEY `no_rkm_medis` (`no_rkm_medis`),
            ADD KEY `kd_pj` (`kd_pj`);");

          $core->db()->pdo()->exec("ALTER TABLE `booking_registrasi`
            ADD CONSTRAINT `booking_registrasi_ibfk_1` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `booking_registrasi_ibfk_2` FOREIGN KEY (`kd_poli`) REFERENCES `poliklinik` (`kd_poli`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `booking_registrasi_ibfk_3` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `booking_registrasi_ibfk_4` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON DELETE CASCADE ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mutasi_berkas` (
            `no_rawat` varchar(17) NOT NULL,
            `status` enum('Sudah Dikirim','Sudah Diterima','Sudah Kembali','Tidak Ada','Masuk Ranap') DEFAULT NULL,
            `dikirim` datetime DEFAULT NULL,
            `diterima` datetime DEFAULT NULL,
            `kembali` datetime DEFAULT NULL,
            `tidakada` datetime DEFAULT NULL,
            `ranap` datetime NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `mutasi_berkas`
            ADD PRIMARY KEY (`no_rawat`);");

          $core->db()->pdo()->exec("ALTER TABLE `mutasi_berkas`
            ADD CONSTRAINT `mutasi_berkas_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE;");

        },
        'uninstall'     =>  function() use($core)
        {
        }
    ];
