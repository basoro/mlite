SET FOREIGN_KEY_CHECKS=0;

# Dump of table mlite_akun_kegiatan
# ------------------------------------------------------------

CREATE TABLE `mlite_akun_kegiatan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kegiatan` varchar(200) DEFAULT NULL,
  `kd_rek` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_antrian_loket
# ------------------------------------------------------------

CREATE TABLE `mlite_antrian_loket` (
  `kd` int(50) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `noantrian` varchar(50) NOT NULL,
  `no_rkm_medis` varchar(50) DEFAULT NULL,
  `postdate` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL DEFAULT '00:00:00',
  `status` varchar(10) NOT NULL DEFAULT '0',
  `loket` varchar(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`kd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mlite_antrian_referensi
# ------------------------------------------------------------

CREATE TABLE `mlite_antrian_referensi` (
  `tanggal_periksa` date NOT NULL,
  `no_rkm_medis` varchar(50) NOT NULL,
  `nomor_kartu` varchar(50) NOT NULL,
  `nomor_referensi` varchar(50) NOT NULL,
  `kodebooking` varchar(100) NOT NULL,
  `jenis_kunjungan` varchar(10) NOT NULL,
  `status_kirim` varchar(20) DEFAULT NULL,
  `keterangan` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mlite_antrian_referensi_batal
# ------------------------------------------------------------

CREATE TABLE `mlite_antrian_referensi_batal` (
  `tanggal_batal` date NOT NULL,
  `nomor_referensi` varchar(50) NOT NULL,
  `kodebooking` varchar(100) NOT NULL,
  `keterangan` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mlite_antrian_referensi_taskid
# ------------------------------------------------------------

CREATE TABLE `mlite_antrian_referensi_taskid` (
  `tanggal_periksa` date NOT NULL,
  `nomor_referensi` varchar(50) NOT NULL,
  `taskid` varchar(50) NOT NULL,
  `waktu` varchar(50) NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  `keterangan` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mlite_apamregister
# ------------------------------------------------------------

CREATE TABLE `mlite_apamregister` (
  `nama_lengkap` varchar(225) NOT NULL,
  `email` varchar(225) NOT NULL,
  `nomor_ktp` varchar(225) NOT NULL,
  `nomor_telepon` varchar(225) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_billing
# ------------------------------------------------------------

CREATE TABLE `mlite_billing` (
  `id_billing` int(11) NOT NULL AUTO_INCREMENT,
  `kd_billing` varchar(100) NOT NULL,
  `no_rawat` varchar(17) NOT NULL,
  `jumlah_total` int(100) NOT NULL,
  `potongan` int(100) NOT NULL,
  `jumlah_harus_bayar` int(100) NOT NULL,
  `jumlah_bayar` int(100) NOT NULL,
  `tgl_billing` date NOT NULL,
  `jam_billing` time NOT NULL,
  `id_user` int(11) NOT NULL,
  `keterangan` varchar(100) NOT NULL,
  PRIMARY KEY (`id_billing`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table mlite_bridging_pcare
# ------------------------------------------------------------

CREATE TABLE `mlite_bridging_pcare` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_rawat` text NOT NULL,
  `no_rkm_medis` text,
  `tgl_daftar` text,
  `nomor_kunjungan` text,
  `kode_provider_peserta` text,
  `nomor_jaminan` text,
  `kode_poli` text,
  `nama_poli` text,
  `kunjungan_sakit` text,
  `sistole` text,
  `diastole` text,
  `nadi` text,
  `respirasi` text,
  `tinggi` text,
  `berat` text,
  `lingkar_perut` text,
  `rujuk_balik` text,
  `subyektif` text,
  `kode_tkp` text,
  `nomor_urut` text,
  `kode_kesadaran` text,
  `nama_kesadaran` text,
  `terapi` text,
  `kode_status_pulang` text,
  `nama_status_pulang` text,
  `tgl_pulang` text,
  `tgl_kunjungan` text,
  `kode_dokter` text,
  `nama_dokter` text,
  `kode_diagnosa1` text,
  `nama_diagnosa1` text,
  `kode_diagnosa2` text,
  `nama_diagnosa2` text,
  `kode_diagnosa3` text,
  `nama_diagnosa3` text,
  `tgl_estimasi_rujuk` text,
  `kode_ppk` text,
  `nama_ppk` text,
  `kode_spesialis` text,
  `nama_spesialis` text,
  `kode_subspesialis` text,
  `nama_subspesialis` text,
  `kode_sarana` text,
  `nama_sarana` text,
  `kode_referensikhusus` text,
  `nama_referensikhusus` text,
  `kode_faskeskhusus` text,
  `nama_faskeskhusus` text,
  `catatan` text,
  `kode_tacc` text,
  `nama_tacc` text,
  `alasan_tacc` text,
  `id_user` text NOT NULL,
  `tgl_input` text NOT NULL,
  `status_kirim` text NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_detailjurnal
# ------------------------------------------------------------

CREATE TABLE `mlite_detailjurnal` (
  `no_jurnal` varchar(20) DEFAULT NULL,
  `kd_rek` varchar(15) DEFAULT NULL,
  `arus_kas` int(10) NOT NULL,
  `debet` double NOT NULL,
  `kredit` double NOT NULL,
  KEY `no_jurnal` (`no_jurnal`),
  KEY `kd_rek` (`kd_rek`),
  KEY `debet` (`debet`),
  KEY `kredit` (`kredit`),
  CONSTRAINT `mlite_detailjurnal_ibfk_1` FOREIGN KEY (`no_jurnal`) REFERENCES `mlite_jurnal` (`no_jurnal`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_detailjurnal_ibfk_2` FOREIGN KEY (`kd_rek`) REFERENCES `mlite_rekening` (`kd_rek`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_duitku
# ------------------------------------------------------------

CREATE TABLE `mlite_duitku` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `tanggal` datetime NOT NULL,
  `no_rkm_medis` varchar(15) NOT NULL,
  `paymentUrl` varchar(255) NOT NULL,
  `merchantCode` varchar(255) NOT NULL,
  `reference` varchar(255) NOT NULL,
  `vaNumber` varchar(255) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `statusCode` varchar(255) NOT NULL,
  `statusMessage` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `reference` (`reference`),
  KEY `mlite_duitku_ibfk_1` (`no_rkm_medis`),
  CONSTRAINT `mlite_duitku_ibfk_1` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_fenton
# ------------------------------------------------------------

CREATE TABLE `mlite_fenton` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `usia_kehamilan` int(11) NOT NULL,
  `tgl_lahir` date NOT NULL,
  `berat_badan` float NOT NULL,
  `lingkar_kepala` int(11) NOT NULL,
  `panjang_badan` int(11) NOT NULL,
  `petugas` varchar(60) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_geolocation_presensi
# ------------------------------------------------------------

CREATE TABLE `mlite_geolocation_presensi` (
  `id` int(11) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `latitude` varchar(200) NOT NULL,
  `longitude` varchar(200) NOT NULL,
  KEY `mlite_geolocation_presensi_ibfk_1` (`id`),
  CONSTRAINT `mlite_geolocation_presensi_ibfk_1` FOREIGN KEY (`id`) REFERENCES `pegawai` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mlite_jurnal
# ------------------------------------------------------------

CREATE TABLE `mlite_jurnal` (
  `no_jurnal` varchar(20) NOT NULL,
  `no_bukti` varchar(20) DEFAULT NULL,
  `tgl_jurnal` date DEFAULT NULL,
  `jenis` enum('U','P') DEFAULT NULL,
  `kegiatan` varchar(250) NOT NULL,
  `keterangan` varchar(350) DEFAULT NULL,
  PRIMARY KEY (`no_jurnal`),
  KEY `no_bukti` (`no_bukti`),
  KEY `tgl_jurnal` (`tgl_jurnal`),
  KEY `jenis` (`jenis`),
  KEY `keterangan` (`keterangan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_login_attempts
# ------------------------------------------------------------

CREATE TABLE `mlite_login_attempts` (
  `ip` text,
  `attempts` int(100) NOT NULL,
  `expires` int(100) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table mlite_modules
# ------------------------------------------------------------

CREATE TABLE `mlite_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dir` text,
  `sequence` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `mlite_modules` WRITE;
/*!40000 ALTER TABLE `mlite_modules` DISABLE KEYS */;

INSERT INTO `mlite_modules` (`id`, `dir`, `sequence`)
VALUES
	(1,'settings','9'),
	(2,'dashboard','0'),
	(3,'master','1'),
	(4,'pasien','2'),
	(5,'rawat_jalan','3'),
	(6,'kasir_rawat_jalan','4'),
	(7,'kepegawaian','5'),
	(8,'farmasi','6'),
	(9,'users','8'),
	(10,'modules','7'),
	(11,'wagateway','10'),
	(12,'apotek_ralan','11'),
	(13,'dokter_ralan','12'),
	(14,'igd','13'),
	(15,'dokter_igd','14'),
	(16,'laboratorium','15'),
	(17,'radiologi','16'),
	(18,'icd_10','17'),
	(19,'rawat_inap','18'),
	(20,'apotek_ranap','19'),
	(21,'dokter_ranap','20'),
	(22,'kasir_rawat_inap','21'),
	(23,'operasi','22'),
	(24,'anjungan','23'),
	(25,'api','24'),
	(26,'jkn_mobile','25'),
	(27,'vclaim','26'),
	(28,'keuangan','27'),
	(29,'manajemen','28'),
	(30,'presensi','29'),
	(31,'vedika','30'),
	(32,'profil','31'),
	(33,'orthanc','32'),
	(34,'veronisa','33'),
	(35,'icd_9','34');

/*!40000 ALTER TABLE `mlite_modules` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table mlite_notifications
# ------------------------------------------------------------

CREATE TABLE `mlite_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(250) NOT NULL,
  `pesan` text NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `no_rkm_medis` varchar(255) NOT NULL,
  `status` varchar(250) NOT NULL DEFAULT 'unread',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_odontogram
# ------------------------------------------------------------

CREATE TABLE `mlite_odontogram` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_rkm_medis` text NOT NULL,
  `pemeriksaan` text,
  `kondisi` text,
  `catatan` text,
  `id_user` text NOT NULL,
  `tgl_input` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



# Dump of table mlite_ohis
# ------------------------------------------------------------

CREATE TABLE `mlite_ohis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_rkm_medis` text NOT NULL,
  `d_16` text,
  `d_11` text,
  `d_26` text,
  `d_36` text,
  `d_31` text,
  `d_46` text,
  `c_16` text,
  `c_11` text,
  `c_26` text,
  `c_36` text,
  `c_31` text,
  `c_46` text,
  `debris` text,
  `calculus` text,
  `nilai` text,
  `kriteria` text,
  `id_user` text NOT NULL,
  `tgl_input` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



# Dump of table mlite_pendaftaran_oral_diagnostic
# ------------------------------------------------------------

CREATE TABLE `mlite_pendaftaran_oral_diagnostic` (
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
  `status_poli` enum('Lama','Baru') NOT NULL,
  PRIMARY KEY (`no_rawat`),
  KEY `no_rkm_medis` (`no_rkm_medis`),
  KEY `kd_poli` (`kd_poli`),
  KEY `kd_pj` (`kd_pj`),
  KEY `status_lanjut` (`status_lanjut`),
  KEY `kd_dokter` (`kd_dokter`),
  KEY `status_bayar` (`status_bayar`) USING BTREE,
  CONSTRAINT `mlite_pendaftaran_oral_diagnostic_ibfk_3` FOREIGN KEY (`kd_poli`) REFERENCES `poliklinik` (`kd_poli`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_pendaftaran_oral_diagnostic_ibfk_4` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_pendaftaran_oral_diagnostic_ibfk_6` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON UPDATE CASCADE,
  CONSTRAINT `mlite_pendaftaran_oral_diagnostic_ibfk_7` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_pengaduan
# ------------------------------------------------------------

CREATE TABLE `mlite_pengaduan` (
  `id` varchar(15) NOT NULL,
  `tanggal` datetime NOT NULL,
  `no_rkm_medis` varchar(15) NOT NULL,
  `pesan` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `no_rkm_medis` (`no_rkm_medis`),
  CONSTRAINT `mlite_pengaduan_ibfk_1` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_pengaduan_detail
# ------------------------------------------------------------

CREATE TABLE `mlite_pengaduan_detail` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `pengaduan_id` varchar(15) NOT NULL,
  `tanggal` datetime NOT NULL,
  `no_rkm_medis` varchar(15) NOT NULL,
  `pesan` varchar(225) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pengaduan_detail_ibfk_1` (`pengaduan_id`),
  CONSTRAINT `mlite_pengaduan_detail_ibfk_1` FOREIGN KEY (`pengaduan_id`) REFERENCES `mlite_pengaduan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_penilaian_awal_keperawatan_gigi
# ------------------------------------------------------------

CREATE TABLE `mlite_penilaian_awal_keperawatan_gigi` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `informasi` enum('Autoanamnesis','Alloanamnesis') NOT NULL,
  `td` varchar(8) NOT NULL DEFAULT '',
  `nadi` varchar(5) NOT NULL DEFAULT '',
  `rr` varchar(5) NOT NULL,
  `suhu` varchar(5) NOT NULL DEFAULT '',
  `bb` varchar(5) NOT NULL DEFAULT '',
  `tb` varchar(5) NOT NULL DEFAULT '',
  `bmi` varchar(10) NOT NULL,
  `keluhan_utama` varchar(150) NOT NULL DEFAULT '',
  `riwayat_penyakit` enum('Tidak Ada','Diabetes Melitus','Hipertensi','Penyakit Jantung','HIV','Hepatitis','Haemophilia','Lain-lain') DEFAULT NULL,
  `ket_riwayat_penyakit` varchar(30) NOT NULL,
  `alergi` varchar(25) NOT NULL DEFAULT '',
  `riwayat_perawatan_gigi` enum('Tidak','Ya, Kapan') NOT NULL,
  `ket_riwayat_perawatan_gigi` varchar(50) NOT NULL DEFAULT '',
  `kebiasaan_sikat_gigi` enum('1x','2x','3x','Mandi','Setelah Makan','Sebelum Tidur') NOT NULL,
  `kebiasaan_lain` enum('Tidak ada','Minum kopi/teh','Minum alkohol','Bruxism','Menggigit pensil','Mengunyah 1 sisi rahang','Merokok','Lain-lain') DEFAULT NULL,
  `ket_kebiasaan_lain` varchar(30) NOT NULL,
  `obat_yang_diminum_saatini` varchar(100) DEFAULT NULL,
  `alat_bantu` enum('Tidak','Ya') NOT NULL,
  `ket_alat_bantu` varchar(30) NOT NULL,
  `prothesa` enum('Tidak','Ya') NOT NULL,
  `ket_pro` varchar(50) NOT NULL,
  `status_psiko` enum('Tenang','Takut','Cemas','Depresi','Lain-lain') NOT NULL,
  `ket_psiko` varchar(70) NOT NULL,
  `hub_keluarga` enum('Baik','Tidak Baik') NOT NULL,
  `tinggal_dengan` enum('Sendiri','Orang Tua','Suami / Istri','Lainnya') NOT NULL,
  `ket_tinggal` varchar(40) NOT NULL,
  `ekonomi` enum('Baik','Cukup','Kurang') NOT NULL,
  `budaya` enum('Tidak Ada','Ada') NOT NULL,
  `ket_budaya` varchar(50) NOT NULL,
  `edukasi` enum('Pasien','Keluarga') NOT NULL,
  `ket_edukasi` varchar(50) NOT NULL,
  `berjalan_a` enum('Ya','Tidak') NOT NULL,
  `berjalan_b` enum('Ya','Tidak') NOT NULL,
  `berjalan_c` enum('Ya','Tidak') NOT NULL,
  `hasil` enum('Tidak beresiko (tidak ditemukan a dan b)','Resiko rendah (ditemukan a/b)','Resiko tinggi (ditemukan a dan b)') NOT NULL,
  `lapor` enum('Ya','Tidak') NOT NULL,
  `ket_lapor` varchar(15) NOT NULL,
  `nyeri` enum('Tidak Ada Nyeri','Nyeri Akut','Nyeri Kronis') NOT NULL,
  `lokasi` varchar(50) NOT NULL,
  `skala_nyeri` enum('0','1','2','3','4','5','6','7','8','9','10') NOT NULL,
  `durasi` varchar(25) NOT NULL,
  `frekuensi` varchar(25) NOT NULL,
  `nyeri_hilang` enum('Istirahat','Medengar Musik','Minum Obat','Tidak ada nyeri','Lain-lain') NOT NULL,
  `ket_nyeri` varchar(40) NOT NULL,
  `pada_dokter` enum('Tidak','Ya') NOT NULL,
  `ket_dokter` varchar(15) NOT NULL,
  `kebersihan_mulut` enum('Baik','Cukup','Kurang') NOT NULL,
  `mukosa_mulut` enum('Normal','Pigmentasi','Radang') NOT NULL,
  `karies` enum('Ada','Tidak') NOT NULL,
  `karang_gigi` enum('Ada','Tidak') NOT NULL,
  `gingiva` enum('Normal','Radang') NOT NULL,
  `palatum` enum('Normal','Radang') NOT NULL,
  `rencana` varchar(200) NOT NULL,
  `nip` varchar(20) NOT NULL,
  PRIMARY KEY (`no_rawat`) USING BTREE,
  KEY `nip` (`nip`) USING BTREE,
  CONSTRAINT `mlite_penilaian_awal_keperawatan_gigi_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_awal_keperawatan_gigi_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_penilaian_awal_keperawatan_ralan
# ------------------------------------------------------------

CREATE TABLE `mlite_penilaian_awal_keperawatan_ralan` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `informasi` enum('Autoanamnesis','Alloanamnesis') NOT NULL,
  `td` varchar(8) NOT NULL DEFAULT '',
  `nadi` varchar(5) NOT NULL DEFAULT '',
  `rr` varchar(5) NOT NULL,
  `suhu` varchar(5) NOT NULL DEFAULT '',
  `gcs` varchar(5) NOT NULL,
  `bb` varchar(5) NOT NULL DEFAULT '',
  `tb` varchar(5) NOT NULL DEFAULT '',
  `bmi` varchar(10) NOT NULL,
  `keluhan_utama` varchar(150) NOT NULL DEFAULT '',
  `rpd` varchar(100) NOT NULL DEFAULT '',
  `rpk` varchar(100) NOT NULL,
  `rpo` varchar(100) NOT NULL,
  `alergi` varchar(25) NOT NULL DEFAULT '',
  `alat_bantu` enum('Tidak','Ya') NOT NULL,
  `ket_bantu` varchar(50) NOT NULL DEFAULT '',
  `prothesa` enum('Tidak','Ya') NOT NULL,
  `ket_pro` varchar(50) NOT NULL,
  `adl` enum('Mandiri','Dibantu') NOT NULL,
  `status_psiko` enum('Tenang','Takut','Cemas','Depresi','Lain-lain') NOT NULL,
  `ket_psiko` varchar(70) NOT NULL,
  `hub_keluarga` enum('Baik','Tidak Baik') NOT NULL,
  `tinggal_dengan` enum('Sendiri','Orang Tua','Suami / Istri','Lainnya') NOT NULL,
  `ket_tinggal` varchar(40) NOT NULL,
  `ekonomi` enum('Baik','Cukup','Kurang') NOT NULL,
  `budaya` enum('Tidak Ada','Ada') NOT NULL,
  `ket_budaya` varchar(50) NOT NULL,
  `edukasi` enum('Pasien','Keluarga') NOT NULL,
  `ket_edukasi` varchar(50) NOT NULL,
  `berjalan_a` enum('Ya','Tidak') NOT NULL,
  `berjalan_b` enum('Ya','Tidak') NOT NULL,
  `berjalan_c` enum('Ya','Tidak') NOT NULL,
  `hasil` enum('Tidak beresiko (tidak ditemukan a dan b)','Resiko rendah (ditemukan a/b)','Resiko tinggi (ditemukan a dan b)') NOT NULL,
  `lapor` enum('Ya','Tidak') NOT NULL,
  `ket_lapor` varchar(15) NOT NULL,
  `sg1` enum('Tidak','Tidak Yakin','Ya, 1-5 Kg','Ya, 6-10 Kg','Ya, 11-15 Kg','Ya, >15 Kg') NOT NULL,
  `nilai1` enum('0','1','2','3','4') NOT NULL,
  `sg2` enum('Ya','Tidak') NOT NULL,
  `nilai2` enum('0','1') NOT NULL,
  `total_hasil` tinyint(4) NOT NULL,
  `nyeri` enum('Tidak Ada Nyeri','Nyeri Akut','Nyeri Kronis') NOT NULL,
  `provokes` enum('Proses Penyakit','Benturan','Lain-lain') NOT NULL,
  `ket_provokes` varchar(40) NOT NULL,
  `quality` enum('Seperti Tertusuk','Berdenyut','Teriris','Tertindih','Tertiban','Lain-lain') NOT NULL,
  `ket_quality` varchar(50) NOT NULL,
  `lokasi` varchar(50) NOT NULL,
  `menyebar` enum('Tidak','Ya') NOT NULL,
  `skala_nyeri` enum('0','1','2','3','4','5','6','7','8','9','10') NOT NULL,
  `durasi` varchar(25) NOT NULL,
  `nyeri_hilang` enum('Istirahat','Medengar Musik','Minum Obat') NOT NULL,
  `ket_nyeri` varchar(40) NOT NULL,
  `pada_dokter` enum('Tidak','Ya') NOT NULL,
  `ket_dokter` varchar(15) NOT NULL,
  `rencana` varchar(200) NOT NULL,
  `nip` varchar(20) NOT NULL,
  PRIMARY KEY (`no_rawat`) USING BTREE,
  KEY `nip` (`nip`) USING BTREE,
  CONSTRAINT `mlite_penilaian_awal_keperawatan_ralan_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_awal_keperawatan_ralan_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_penilaian_awal_keperawatan_ranap
# ------------------------------------------------------------

CREATE TABLE `mlite_penilaian_awal_keperawatan_ranap` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `informasi` enum('Autoanamnesis','Alloanamnesis') NOT NULL,
  `ket_informasi` varchar(30) NOT NULL,
  `tiba_diruang_rawat` enum('Jalan Tanpa Bantuan','Kursi Roda','Brankar') NOT NULL,
  `kasus_trauma` enum('Trauma','Non Trauma') DEFAULT NULL,
  `cara_masuk` enum('Poli','IGD','Lain-lain') NOT NULL,
  `rps` varchar(300) NOT NULL,
  `rpd` varchar(100) NOT NULL,
  `rpk` varchar(100) NOT NULL,
  `rpo` varchar(100) NOT NULL,
  `riwayat_pembedahan` varchar(40) NOT NULL,
  `riwayat_dirawat_dirs` varchar(40) NOT NULL,
  `alat_bantu_dipakai` enum('Kacamata','Prothesa','Alat Bantu Dengar','Lain-lain') NOT NULL,
  `riwayat_kehamilan` enum('Tidak','Ya') NOT NULL,
  `riwayat_kehamilan_perkiraan` varchar(30) NOT NULL,
  `riwayat_tranfusi` varchar(40) NOT NULL,
  `riwayat_alergi` varchar(40) NOT NULL,
  `riwayat_merokok` enum('Tidak','Ya') NOT NULL,
  `riwayat_merokok_jumlah` varchar(5) NOT NULL,
  `riwayat_alkohol` enum('Tidak','Ya') NOT NULL,
  `riwayat_alkohol_jumlah` varchar(5) NOT NULL,
  `riwayat_narkoba` enum('Tidak','Ya') NOT NULL,
  `riwayat_olahraga` enum('Tidak','Ya') NOT NULL,
  `pemeriksaan_mental` varchar(40) NOT NULL,
  `pemeriksaan_keadaan_umum` enum('Baik','Sedang','Buruk') NOT NULL,
  `pemeriksaan_gcs` varchar(10) NOT NULL,
  `pemeriksaan_td` varchar(8) NOT NULL,
  `pemeriksaan_nadi` varchar(5) NOT NULL,
  `pemeriksaan_rr` varchar(5) NOT NULL,
  `pemeriksaan_suhu` varchar(5) NOT NULL,
  `pemeriksaan_spo2` varchar(5) NOT NULL,
  `pemeriksaan_bb` varchar(5) NOT NULL,
  `pemeriksaan_tb` varchar(5) NOT NULL,
  `pemeriksaan_susunan_kepala` enum('TAK','Hydrocephalus','Hematoma','Lain-lain') NOT NULL,
  `pemeriksaan_susunan_wajah` enum('TAK','Asimetris','Kelainan Kongenital') NOT NULL,
  `pemeriksaan_susunan_leher` enum('TAK','Kaku Kuduk','Pembesaran Thyroid','Pembesaran KGB') NOT NULL,
  `pemeriksaan_susunan_kejang` enum('TAK','Kuat','Ada') NOT NULL,
  `pemeriksaan_susunan_sensorik` enum('TAK','Sakit Nyeri','Rasa kebas') NOT NULL,
  `pemeriksaan_kardiovaskuler_denyut_nadi` enum('Teratur','Tidak Teratur') NOT NULL,
  `pemeriksaan_kardiovaskuler_sirkulasi` enum('Akral Hangat','Akral Dingin','Edema') NOT NULL,
  `pemeriksaan_kardiovaskuler_pulsasi` enum('Kuat','Lemah','Lain-lain') NOT NULL,
  `pemeriksaan_respirasi_pola_nafas` enum('Normal','Bradipnea','Tachipnea') NOT NULL,
  `pemeriksaan_respirasi_retraksi` enum('Tidak Ada','Ringan','Berat') NOT NULL,
  `pemeriksaan_respirasi_suara_nafas` enum('Vesikuler','Wheezing','Rhonki') NOT NULL,
  `pemeriksaan_respirasi_volume_pernafasan` enum('Normal','Hiperventilasi','Hipoventilasi') NOT NULL,
  `pemeriksaan_respirasi_jenis_pernafasan` enum('Pernafasan Dada','Alat Bantu Pernafasaan') NOT NULL,
  `pemeriksaan_respirasi_irama_nafas` enum('Teratur','Tidak Teratur') NOT NULL,
  `pemeriksaan_respirasi_batuk` enum('Tidak','Ya : Produktif','Ya : Non Produktif') NOT NULL,
  `pemeriksaan_gastrointestinal_mulut` enum('TAK','Stomatitis','Mukosa Kering','Bibir Pucat','Lain-lain') NOT NULL,
  `pemeriksaan_gastrointestinal_gigi` enum('TAK','Karies','Goyang','Lain-lain') NOT NULL,
  `pemeriksaan_gastrointestinal_lidah` enum('TAK','Kotor','Gerak Asimetris','Lain-lain') NOT NULL,
  `pemeriksaan_gastrointestinal_tenggorokan` enum('TAK','Gangguan Menelan','Sakit Menelan','Lain-lain') NOT NULL,
  `pemeriksaan_gastrointestinal_abdomen` enum('Supel','Asictes',' Tegang','Nyeri Tekan/Lepas','Lain-lain') NOT NULL,
  `pemeriksaan_gastrointestinal_peistatik_usus` enum('TAK','Tidak Ada Bising Usus','Hiperistaltik') NOT NULL,
  `pemeriksaan_gastrointestinal_anus` enum('TAK','Atresia Ani') NOT NULL,
  `pemeriksaan_neurologi_pengelihatan` enum('TAK','Ada Kelainan') NOT NULL,
  `pemeriksaan_neurologi_alat_bantu_penglihatan` enum('Tidak','Kacamata','Lensa Kontak') NOT NULL,
  `pemeriksaan_neurologi_pendengaran` enum('TAK','Berdengung','Nyeri','Tuli','Keluar Cairan','Lain-lain') NOT NULL,
  `pemeriksaan_neurologi_bicara` enum('Jelas','Tidak Jelas') NOT NULL,
  `pemeriksaan_neurologi_sensorik` enum('TAK','Sakit Nyeri','Rasa Kebas','Lain-lain') NOT NULL,
  `pemeriksaan_neurologi_motorik` enum('TAK','Hemiparese','Tetraparese','Tremor','Lain-lain') NOT NULL,
  `pemeriksaan_neurologi_kekuatan_otot` enum('Kuat','Lemah') NOT NULL,
  `pemeriksaan_integument_warnakulit` enum('Pucat','Sianosis','Normal','Lain-lain') NOT NULL,
  `pemeriksaan_integument_turgor` enum('Baik','Sedang','Buruk') NOT NULL,
  `pemeriksaan_integument_kulit` enum('Normal','Rash/Kemerahan','Luka','Memar','Ptekie','Bula') NOT NULL,
  `pemeriksaan_integument_dekubitas` enum('Tidak Ada','Usia > 65 tahun','Obesitas','Imobilisasi','Paraplegi/Vegetative State','Dirawat Di HCU','Penyakit Kronis (DM, CHF, CKD)','Inkontinentia Uri/Alvi') NOT NULL,
  `pemeriksaan_muskuloskletal_pergerakan_sendi` enum('Bebas','Terbatas') NOT NULL,
  `pemeriksaan_muskuloskletal_kekauatan_otot` enum('Baik','Lemah','Tremor') NOT NULL,
  `pemeriksaan_muskuloskletal_nyeri_sendi` enum('Tidak Ada','Ada') NOT NULL,
  `pemeriksaan_muskuloskletal_oedema` enum('Tidak Ada','Ada') NOT NULL,
  `pemeriksaan_muskuloskletal_fraktur` enum('Tidak Ada','Ada') NOT NULL,
  `pemeriksaan_eliminasi_bab_frekuensi_jumlah` varchar(5) NOT NULL,
  `pemeriksaan_eliminasi_bab_frekuensi_durasi` varchar(10) NOT NULL,
  `pemeriksaan_eliminasi_bab_konsistensi` varchar(30) NOT NULL,
  `pemeriksaan_eliminasi_bab_warna` varchar(30) NOT NULL,
  `pemeriksaan_eliminasi_bak_frekuensi_jumlah` varchar(5) NOT NULL,
  `pemeriksaan_eliminasi_bak_frekuensi_durasi` varchar(10) NOT NULL,
  `pemeriksaan_eliminasi_bak_warna` varchar(30) NOT NULL,
  `pemeriksaan_eliminasi_bak_lainlain` varchar(30) NOT NULL,
  `pola_aktifitas_makanminum` enum('Mandiri','Bantuan Orang Lain') NOT NULL,
  `pola_aktifitas_mandi` enum('Mandiri','Bantuan Orang Lain') NOT NULL,
  `pola_aktifitas_eliminasi` enum('Mandiri','Bantuan Orang Lain') NOT NULL,
  `pola_aktifitas_berpakaian` enum('Mandiri','Bantuan Orang Lain') NOT NULL,
  `pola_aktifitas_berpindah` enum('Mandiri','Bantuan Orang Lain') NOT NULL,
  `pola_nutrisi_frekuesi_makan` varchar(3) NOT NULL,
  `pola_nutrisi_jenis_makanan` varchar(20) NOT NULL,
  `pola_nutrisi_porsi_makan` varchar(3) NOT NULL,
  `pola_tidur_lama_tidur` varchar(3) NOT NULL,
  `pola_tidur_gangguan` enum('Tidak Ada Gangguan','Insomnia') NOT NULL,
  `pengkajian_fungsi_kemampuan_sehari` enum('Mandiri','Bantuan Minimal','Bantuan Sebagian','Ketergantungan Total') NOT NULL,
  `pengkajian_fungsi_aktifitas` enum('Tirah Baring','Duduk','Berjalan') NOT NULL,
  `pengkajian_fungsi_berjalan` enum('TAK','Penurunan Kekuatan/ROM','Paralisis','Sering Jatuh','Deformitas','Hilang Keseimbangan','Riwayat Patah Tulang','Lain-lain') NOT NULL,
  `pengkajian_fungsi_ambulasi` enum('Walker','Tongkat','Kursi Roda','Tidak Menggunakan') NOT NULL,
  `pengkajian_fungsi_ekstrimitas_atas` enum('TAK','Lemah','Oedema','Tidak Simetris','Lain-lain') NOT NULL,
  `pengkajian_fungsi_ekstrimitas_bawah` enum('TAK','Varises','Oedema','Tidak Simetris','Lain-lain') NOT NULL,
  `pengkajian_fungsi_menggenggam` enum('Tidak Ada Kesulitan','Terakhir','Lain-lain') NOT NULL,
  `pengkajian_fungsi_koordinasi` enum('Tidak Ada Kesulitan','Ada Masalah') NOT NULL,
  `pengkajian_fungsi_kesimpulan` enum('Ya (Co DPJP)','Tidak (Tidak Perlu Co DPJP)') NOT NULL,
  `riwayat_psiko_kondisi_psiko` enum('Tidak Ada Masalah','Marah','Takut','Depresi','Cepat Lelah','Cemas','Gelisah','Sulit Tidur','Lain-lain') NOT NULL,
  `riwayat_psiko_gangguan_jiwa` enum('Ya','Tidak') NOT NULL,
  `riwayat_psiko_perilaku` enum('Tidak Ada Masalah','Perilaku Kekerasan','Gangguan Efek','Gangguan Memori','Halusinasi','Kecenderungan Percobaan Bunuh Diri','Lain-lain') NOT NULL,
  `riwayat_psiko_hubungan_keluarga` enum('Harmonis','Kurang Harmonis','Tidak Harmonis','Konflik Besar') NOT NULL,
  `riwayat_psiko_tinggal` enum('Sendiri','Orang Tua','Suami/Istri','Keluarga','Lain-lain') NOT NULL,
  `riwayat_psiko_nilai_kepercayaan` enum('Tidak Ada','Ada') NOT NULL,
  `riwayat_psiko_pendidikan_pj` enum('-','TS','TK','SD','SMP','SMA','SLTA/SEDERAJAT','D1','D2','D3','D4','S1','S2','S3') NOT NULL,
  `riwayat_psiko_edukasi_diberikan` enum('Pasien','Keluarga') NOT NULL,
  `penilaian_nyeri` enum('Tidak Ada Nyeri','Nyeri Akut','Nyeri Kronis') NOT NULL,
  `penilaian_nyeri_penyebab` enum('Proses Penyakit','Benturan','Lain-lain') NOT NULL,
  `penilaian_nyeri_kualitas` enum('Seperti Tertusuk','Berdenyut','Teriris','Tertindih','Tertiban','Lain-lain') NOT NULL,
  `penilaian_nyeri_lokasi` varchar(50) NOT NULL,
  `penilaian_nyeri_menyebar` enum('Tidak','Ya') NOT NULL,
  `penilaian_nyeri_skala` enum('0','1','2','3','4','5','6','7','8','9','10') NOT NULL,
  `penilaian_nyeri_waktu` varchar(5) NOT NULL,
  `penilaian_nyeri_hilang` enum('Istirahat','Medengar Musik','Minum Obat') NOT NULL,
  `penilaian_nyeri_diberitahukan_dokter` enum('Tidak','Ya') NOT NULL,
  `penilaian_nyeri_jam_diberitahukan_dokter` varchar(10) NOT NULL,
  `penilaian_jatuhmorse_skala1` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhmorse_nilai1` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhmorse_skala2` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhmorse_nilai2` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhmorse_skala3` enum('Tidak Ada/Kursi Roda/Perawat/Tirah Baring','Tongkat/Alat Penopang','Berpegangan Pada Perabot') DEFAULT NULL,
  `penilaian_jatuhmorse_nilai3` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhmorse_skala4` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhmorse_nilai4` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhmorse_skala5` enum('Normal/Tirah Baring/Imobilisasi','Lemah','Terganggu') DEFAULT NULL,
  `penilaian_jatuhmorse_nilai5` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhmorse_skala6` enum('Sadar Akan Kemampuan Diri Sendiri','Sering Lupa Akan Keterbatasan Yang Dimiliki') DEFAULT NULL,
  `penilaian_jatuhmorse_nilai6` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhmorse_totalnilai` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala1` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai1` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala2` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai2` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala3` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai3` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala4` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai4` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala5` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai5` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala6` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai6` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala7` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai7` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala8` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai8` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala9` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai9` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala10` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai10` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala11` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai11` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_totalnilai` tinyint(4) DEFAULT NULL,
  `skrining_gizi1` enum('Tidak ada penurunan berat badan','Tidak yakin/ tidak tahu/ terasa baju lebih longgar','Ya 1-5 kg','Ya 6-10 kg','Ya 11-15 kg','Ya > 15 kg') DEFAULT NULL,
  `nilai_gizi1` int(11) DEFAULT NULL,
  `skrining_gizi2` enum('Tidak','Ya') DEFAULT NULL,
  `nilai_gizi2` int(11) DEFAULT NULL,
  `nilai_total_gizi` double DEFAULT NULL,
  `skrining_gizi_diagnosa_khusus` enum('Tidak','Ya') DEFAULT NULL,
  `skrining_gizi_diketahui_dietisen` enum('Tidak','Ya') DEFAULT NULL,
  `skrining_gizi_jam_diketahui_dietisen` varchar(10) DEFAULT NULL,
  `rencana` varchar(200) DEFAULT NULL,
  `nip1` varchar(20) NOT NULL,
  `nip2` varchar(20) NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  PRIMARY KEY (`no_rawat`) USING BTREE,
  KEY `nip1` (`nip1`) USING BTREE,
  KEY `nip2` (`nip2`) USING BTREE,
  KEY `kd_dokter` (`kd_dokter`) USING BTREE,
  CONSTRAINT `mlite_penilaian_awal_keperawatan_ranap_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_awal_keperawatan_ranap_ibfk_2` FOREIGN KEY (`nip1`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_awal_keperawatan_ranap_ibfk_3` FOREIGN KEY (`nip2`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_awal_keperawatan_ranap_ibfk_4` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_penilaian_medis_igd
# ------------------------------------------------------------

CREATE TABLE `mlite_penilaian_medis_igd` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `anamnesis` enum('Autoanamnesis','Alloanamnesis') NOT NULL,
  `hubungan` varchar(100) NOT NULL,
  `keluhan_utama` varchar(2000) NOT NULL DEFAULT '',
  `rps` varchar(2000) NOT NULL,
  `rpd` varchar(1000) NOT NULL DEFAULT '',
  `rpk` varchar(1000) NOT NULL,
  `rpo` varchar(1000) NOT NULL,
  `alergi` varchar(100) NOT NULL DEFAULT '',
  `keadaan` enum('Sehat','Sakit Ringan','Sakit Sedang','Sakit Berat') NOT NULL,
  `gcs` varchar(10) NOT NULL,
  `kesadaran` enum('Compos Mentis','Apatis','Somnolen','Sopor','Koma') NOT NULL,
  `td` varchar(8) NOT NULL DEFAULT '',
  `nadi` varchar(5) NOT NULL DEFAULT '',
  `rr` varchar(5) NOT NULL,
  `suhu` varchar(5) NOT NULL DEFAULT '',
  `spo` varchar(5) NOT NULL,
  `bb` varchar(5) NOT NULL DEFAULT '',
  `tb` varchar(5) NOT NULL DEFAULT '',
  `kepala` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `mata` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `gigi` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `leher` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `thoraks` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `abdomen` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `genital` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `ekstremitas` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `ket_fisik` text NOT NULL,
  `ket_lokalis` text NOT NULL,
  `ekg` text NOT NULL,
  `rad` text NOT NULL,
  `lab` text NOT NULL,
  `diagnosis` varchar(500) NOT NULL,
  `tata` text NOT NULL,
  PRIMARY KEY (`no_rawat`) USING BTREE,
  KEY `kd_dokter` (`kd_dokter`) USING BTREE,
  CONSTRAINT `mlite_penilaian_medis_igd_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_medis_igd_ibfk_2` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_penilaian_medis_ralan
# ------------------------------------------------------------

CREATE TABLE `mlite_penilaian_medis_ralan` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `anamnesis` enum('Autoanamnesis','Alloanamnesis') NOT NULL,
  `hubungan` varchar(30) NOT NULL,
  `keluhan_utama` varchar(2000) NOT NULL DEFAULT '',
  `rps` varchar(2000) NOT NULL,
  `rpd` varchar(1000) NOT NULL DEFAULT '',
  `rpk` varchar(1000) NOT NULL,
  `rpo` varchar(1000) NOT NULL,
  `alergi` varchar(50) NOT NULL DEFAULT '',
  `keadaan` enum('Sehat','Sakit Ringan','Sakit Sedang','Sakit Berat') NOT NULL,
  `gcs` varchar(10) NOT NULL,
  `kesadaran` enum('Compos Mentis','Apatis','Somnolen','Sopor','Koma') NOT NULL,
  `td` varchar(8) NOT NULL DEFAULT '',
  `nadi` varchar(5) NOT NULL DEFAULT '',
  `rr` varchar(5) NOT NULL,
  `suhu` varchar(5) NOT NULL DEFAULT '',
  `spo` varchar(5) NOT NULL,
  `bb` varchar(5) NOT NULL DEFAULT '',
  `tb` varchar(5) NOT NULL DEFAULT '',
  `kepala` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `gigi` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `tht` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `thoraks` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `abdomen` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `genital` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `ekstremitas` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `kulit` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `ket_fisik` text NOT NULL,
  `ket_lokalis` text NOT NULL,
  `penunjang` text NOT NULL,
  `diagnosis` varchar(500) NOT NULL,
  `tata` text NOT NULL,
  `konsulrujuk` varchar(1000) NOT NULL,
  PRIMARY KEY (`no_rawat`) USING BTREE,
  KEY `kd_dokter` (`kd_dokter`) USING BTREE,
  CONSTRAINT `mlite_penilaian_medis_ralan_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_medis_ralan_ibfk_2` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_penilaian_medis_ranap
# ------------------------------------------------------------

CREATE TABLE `mlite_penilaian_medis_ranap` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `anamnesis` enum('Autoanamnesis','Alloanamnesis') NOT NULL,
  `hubungan` varchar(100) NOT NULL,
  `keluhan_utama` varchar(2000) NOT NULL DEFAULT '',
  `rps` varchar(2000) NOT NULL,
  `rpd` varchar(1000) NOT NULL DEFAULT '',
  `rpk` varchar(1000) NOT NULL,
  `rpo` varchar(1000) NOT NULL,
  `alergi` varchar(100) NOT NULL DEFAULT '',
  `keadaan` enum('Sehat','Sakit Ringan','Sakit Sedang','Sakit Berat') NOT NULL,
  `gcs` varchar(10) NOT NULL,
  `kesadaran` enum('Compos Mentis','Apatis','Somnolen','Sopor','Koma') NOT NULL,
  `td` varchar(8) NOT NULL DEFAULT '',
  `nadi` varchar(5) NOT NULL DEFAULT '',
  `rr` varchar(5) NOT NULL,
  `suhu` varchar(5) NOT NULL DEFAULT '',
  `spo` varchar(5) NOT NULL,
  `bb` varchar(5) NOT NULL DEFAULT '',
  `tb` varchar(5) NOT NULL DEFAULT '',
  `kepala` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `mata` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `gigi` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `tht` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `thoraks` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `jantung` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `paru` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `abdomen` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `genital` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `ekstremitas` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `kulit` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `ket_fisik` text NOT NULL,
  `ket_lokalis` text NOT NULL,
  `lab` text NOT NULL,
  `rad` text NOT NULL,
  `penunjang` text NOT NULL,
  `diagnosis` varchar(500) NOT NULL,
  `tata` text NOT NULL,
  `edukasi` varchar(1000) NOT NULL,
  PRIMARY KEY (`no_rawat`) USING BTREE,
  KEY `kd_dokter` (`kd_dokter`) USING BTREE,
  CONSTRAINT `mlite_penilaian_medis_ranap_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_medis_ranap_ibfk_2` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_penilaian_ulang_nyeri
# ------------------------------------------------------------

CREATE TABLE `mlite_penilaian_ulang_nyeri` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `nyeri` enum('Tidak Ada Nyeri','Nyeri Akut','Nyeri Kronis') NOT NULL,
  `provokes` enum('Proses Penyakit','Benturan','Lain-lain','-') NOT NULL,
  `ket_provokes` varchar(40) NOT NULL,
  `quality` enum('Seperti Tertusuk','Berdenyut','Teriris','Tertindih','Tertiban','Lain-lain','-') NOT NULL,
  `ket_quality` varchar(50) NOT NULL,
  `lokasi` varchar(50) NOT NULL,
  `menyebar` enum('Tidak','Ya') NOT NULL,
  `skala_nyeri` enum('0','1','2','3','4','5','6','7','8','9','10') NOT NULL,
  `durasi` varchar(25) NOT NULL,
  `nyeri_hilang` enum('Istirahat','Medengar Musik','Minum Obat','-') NOT NULL,
  `ket_nyeri` varchar(40) NOT NULL,
  `manajemen_nyeri` varchar(1000) DEFAULT NULL,
  `nip` varchar(20) NOT NULL,
  PRIMARY KEY (`no_rawat`,`tanggal`) USING BTREE,
  KEY `nip` (`nip`) USING BTREE,
  CONSTRAINT `mlite_penilaian_ulang_nyeri_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_ulang_nyeri_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_penjualan
# ------------------------------------------------------------

CREATE TABLE `mlite_penjualan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pembeli` varchar(100) DEFAULT NULL,
  `alamat_pembeli` varchar(100) DEFAULT NULL,
  `nomor_telepon` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `id_user` varchar(50) NOT NULL,
  `keterangan` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table mlite_penjualan_barang
# ------------------------------------------------------------

CREATE TABLE `mlite_penjualan_barang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_barang` varchar(100) DEFAULT NULL,
  `stok` varchar(100) DEFAULT NULL,
  `harga` varchar(100) DEFAULT NULL,
  `keterangan` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_penjualan_billing
# ------------------------------------------------------------

CREATE TABLE `mlite_penjualan_billing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_penjualan` int(11) NOT NULL,
  `jumlah_total` int(100) NOT NULL,
  `potongan` int(100) DEFAULT NULL,
  `jumlah_harus_bayar` int(100) NOT NULL,
  `jumlah_bayar` int(100) NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `id_user` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table mlite_penjualan_detail
# ------------------------------------------------------------

CREATE TABLE `mlite_penjualan_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_penjualan` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL,
  `jumlah` int(100) NOT NULL,
  `harga_total` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `id_user` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table mlite_peta_mukosa_rongga_mulut
# ------------------------------------------------------------

CREATE TABLE `mlite_peta_mukosa_rongga_mulut` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `kelainan` text,
  `gambar` text,
  `nip` varchar(20) NOT NULL,
  PRIMARY KEY (`no_rawat`) USING BTREE,
  KEY `nip` (`nip`) USING BTREE,
  CONSTRAINT `mlite_peta_mukosa_rongga_mulut_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_peta_mukosa_rongga_mulut_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_rekening
# ------------------------------------------------------------

CREATE TABLE `mlite_rekening` (
  `kd_rek` varchar(15) NOT NULL DEFAULT '',
  `nm_rek` varchar(100) DEFAULT NULL,
  `tipe` enum('N','M','R') DEFAULT NULL,
  `balance` enum('D','K') DEFAULT NULL,
  `level` enum('0','1') DEFAULT NULL,
  PRIMARY KEY (`kd_rek`),
  KEY `nm_rek` (`nm_rek`),
  KEY `tipe` (`tipe`),
  KEY `balance` (`balance`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_rekeningtahun
# ------------------------------------------------------------

CREATE TABLE `mlite_rekeningtahun` (
  `thn` year(4) NOT NULL,
  `kd_rek` varchar(15) NOT NULL DEFAULT '',
  `saldo_awal` double NOT NULL,
  PRIMARY KEY (`thn`,`kd_rek`),
  KEY `kd_rek` (`kd_rek`),
  KEY `saldo_awal` (`saldo_awal`),
  CONSTRAINT `mlite_rekeningtahun_ibfk_1` FOREIGN KEY (`kd_rek`) REFERENCES `mlite_rekening` (`kd_rek`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_remember_me
# ------------------------------------------------------------

CREATE TABLE `mlite_remember_me` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` text,
  `user_id` int(10) NOT NULL,
  `expiry` int(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mlite_remember_me_ibfk_1` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table mlite_satu_sehat_departemen
# ------------------------------------------------------------

CREATE TABLE `mlite_satu_sehat_departemen` (
  `dep_id` char(4) NOT NULL,
  `id_organisasi_satusehat` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`dep_id`),
  UNIQUE KEY `id_organisasi_satusehat` (`id_organisasi_satusehat`),
  CONSTRAINT `mlite_satu_sehat_departemen_ibfk_1` FOREIGN KEY (`dep_id`) REFERENCES `departemen` (`dep_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_satu_sehat_lokasi
# ------------------------------------------------------------

CREATE TABLE `mlite_satu_sehat_lokasi` (
  `kode` char(5) NOT NULL,
  `lokasi` varchar(40) DEFAULT NULL,
  `id_organisasi_satusehat` varchar(40) DEFAULT NULL,
  `id_lokasi_satusehat` varchar(40) DEFAULT NULL,
  `longitude` varchar(30) NOT NULL,
  `latitude` varchar(30) NOT NULL,
  `altitude` varchar(30) NOT NULL,
  PRIMARY KEY (`kode`),
  UNIQUE KEY `id_lokasi_satusehat` (`id_lokasi_satusehat`),
  KEY `id_organisasi_satusehat` (`id_organisasi_satusehat`),
  CONSTRAINT `mlite_satu_sehat_lokasi_ibfk_2` FOREIGN KEY (`id_organisasi_satusehat`) REFERENCES `mlite_satu_sehat_departemen` (`id_organisasi_satusehat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_satu_sehat_mapping_praktisi
# ------------------------------------------------------------

CREATE TABLE `mlite_satu_sehat_mapping_praktisi` (
  `practitioner_id` varchar(40) NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `jenis_praktisi` varchar(20) NOT NULL,
  PRIMARY KEY (`practitioner_id`),
  KEY `kd_dokter` (`kd_dokter`),
  CONSTRAINT `mlite_satu_sehat_mapping_praktisi_ibfk_1` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_satu_sehat_response
# ------------------------------------------------------------

CREATE TABLE `mlite_satu_sehat_response` (
  `no_rawat` varchar(17) NOT NULL,
  `id_encounter` varchar(50) DEFAULT NULL,
  `id_condition` varchar(50) DEFAULT NULL,
  `id_observation_ttvnadi` varchar(50) DEFAULT NULL,
  `id_observation_ttvrespirasi` varchar(50) DEFAULT NULL,
  `id_observation_ttvsuhu` varchar(50) DEFAULT NULL,
  `id_observation_ttvspo2` varchar(50) DEFAULT NULL,
  `id_observation_ttvgcs` varchar(50) DEFAULT NULL,
  `id_observation_ttvtinggi` varchar(50) DEFAULT NULL,
  `id_observation_ttvberat` varchar(50) DEFAULT NULL,
  `id_observation_ttvperut` varchar(50) DEFAULT NULL,
  `id_observation_ttvtensi` varchar(50) DEFAULT NULL,
  `id_observation_ttvkesadaran` varchar(50) DEFAULT NULL,
  `id_procedure` varchar(50) DEFAULT NULL,
  `id_clinical_impression` varchar(50) DEFAULT NULL,
  `id_composition` varchar(50) DEFAULT NULL,
  `id_immunization` varchar(50) DEFAULT NULL,
  `id_medication_request` varchar(50) DEFAULT NULL,
  `id_medication_dispense` varchar(50) DEFAULT NULL,
  `id_medication_statement` varchar(50) DEFAULT NULL,
  `id_rad_request` varchar(50) DEFAULT NULL,
  `id_rad_specimen` varchar(50) DEFAULT NULL,
  `id_rad_observation` varchar(50) DEFAULT NULL,
  `id_rad_diagnostic` varchar(50) DEFAULT NULL,
  `id_lab_pk_request` varchar(50) DEFAULT NULL,
  `id_lab_pk_specimen` varchar(50) DEFAULT NULL,
  `id_lab_pk_observation` varchar(50) DEFAULT NULL,
  `id_lab_pk_diagnostic` varchar(50) DEFAULT NULL,
  `id_lab_pa_request` varchar(50) DEFAULT NULL,
  `id_lab_pa_specimen` varchar(50) DEFAULT NULL,
  `id_lab_pa_observation` varchar(50) DEFAULT NULL,
  `id_lab_pa_diagnostic` varchar(50) DEFAULT NULL,
  `id_lab_mb_request` varchar(50) DEFAULT NULL,
  `id_lab_mb_specimen` varchar(50) DEFAULT NULL,
  `id_lab_mb_observation` varchar(50) DEFAULT NULL,
  `id_lab_mb_diagnostic` varchar(50) DEFAULT NULL,
  `id_careplan` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`no_rawat`),
  CONSTRAINT `mlite_satu_sehat_response_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

# Dump of table mlite_satu_sehat_mapping_obat
# ------------------------------------------------------------

CREATE TABLE `mlite_satu_sehat_mapping_obat` (
  `kode_brng` varchar(15) NOT NULL DEFAULT '',
  `kode_kfa` varchar(50) DEFAULT NULL,
  `nama_kfa` varchar(100) DEFAULT NULL,
  `kode_bahan` varchar(50) DEFAULT NULL,
  `nama_bahan` varchar(100) DEFAULT NULL,
  `numerator` varchar(10) DEFAULT NULL,
  `satuan_num` varchar(10) DEFAULT NULL,
  `denominator` varchar(10) DEFAULT NULL,
  `satuan_den` varchar(10) DEFAULT NULL,
  `nama_satuan_den` varchar(10) DEFAULT NULL,
  `kode_sediaan` varchar(50) DEFAULT NULL,
  `nama_sediaan` varchar(100) DEFAULT NULL,
  `kode_route` varchar(10) DEFAULT NULL,
  `nama_route` varchar(50) DEFAULT NULL,
  `type` enum('obat','vaksin') NOT NULL,
  `id_medication` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`kode_brng`),
  CONSTRAINT `mlite_satu_sehat_mapping_obat_ibfk_1` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `mlite_satu_sehat_mapping_lab` (
  `id_template` int(11) NOT NULL,
  `code` varchar(15) DEFAULT NULL,
  `system` varchar(100) NOT NULL,
  `display` varchar(80) DEFAULT NULL,
  `sampel_code` varchar(15) NOT NULL,
  `sampel_system` varchar(100) NOT NULL,
  `sampel_display` varchar(80) NOT NULL,
  PRIMARY KEY (`id_template`),
  CONSTRAINT `mlite_satu_sehat_mapping_lab_ibfk_1` FOREIGN KEY (`id_template`) REFERENCES `template_laboratorium` (`id_template`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `mlite_satu_sehat_mapping_rad` (
  `kd_jenis_prw` varchar(15) NOT NULL,
  `code` varchar(15) DEFAULT NULL,
  `system` varchar(100) NOT NULL,
  `display` varchar(80) DEFAULT NULL,
  `sampel_code` varchar(15) NOT NULL,
  `sampel_system` varchar(100) NOT NULL,
  `sampel_display` varchar(80) NOT NULL,
  PRIMARY KEY (`kd_jenis_prw`),
  CONSTRAINT `mlite_satu_sehat_mapping_rad_ibfk_1` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_radiologi` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

# Dump of table mlite_settings
# ------------------------------------------------------------

CREATE TABLE `mlite_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(100) NOT NULL,
  `field` varchar(100) NOT NULL,
  `value` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module` (`module`,`field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `mlite_settings` WRITE;
/*!40000 ALTER TABLE `mlite_settings` DISABLE KEYS */;

INSERT INTO `mlite_settings` (`id`, `module`, `field`, `value`)
VALUES
	(1,'settings','logo','uploads/settings/logo.png'),
	(2,'settings','nama_instansi','mLITE Indonesia'),
	(3,'settings','alamat','Jl. Perintis Kemerdekaan 45'),
	(4,'settings','kota','Barabai'),
	(5,'settings','propinsi','Kalimantan Selatan'),
	(6,'settings','nomor_telepon','0812345678'),
	(7,'settings','email','info@mlite.id'),
	(8,'settings','website','https://mlite.id'),
	(9,'settings','ppk_bpjs','-'),
	(10,'settings','footer','Copyright {?=date(\"Y\")?} &copy; by drg. F. Basoro. All rights reserved.'),
	(11,'settings','homepage','main'),
	(12,'settings','wallpaper','uploads/settings/wallpaper.jpg'),
	(13,'settings','text_color','#44813e'),
	(14,'settings','igd','IGDK'),
	(15,'settings','laboratorium','-'),
	(16,'settings','pj_laboratorium','DR001'),
	(17,'settings','radiologi','-'),
	(18,'settings','pj_radiologi','DR001'),
	(19,'settings','dokter_ralan_per_dokter','false'),
	(20,'settings','cekstatusbayar','false'),
	(21,'settings','ceklimit','false'),
	(22,'settings','responsivevoice','false'),
	(23,'settings','notif_presensi','true'),
	(24,'settings','BpjsApiUrl','https://apijkn-dev.bpjs-kesehatan.go.id/vclaim-rest-dev/'),
	(25,'settings','BpjsConsID','-'),
	(26,'settings','BpjsSecretKey','-'),
	(27,'settings','BpjsUserKey','-'),
	(28,'settings','timezone','Asia/Makassar'),
	(29,'settings','theme','default'),
	(30,'settings','theme_admin','mlite'),
	(31,'settings','admin_mode','complex'),
	(32,'settings','input_kasir','tidak'),
	(33,'settings','editor','wysiwyg'),
	(34,'settings','version','5.2.0'),
	(35,'settings','update_check','0'),
	(36,'settings','update_changelog',''),
	(37,'settings','update_version','0'),
	(38,'settings','license',''),
	(39,'farmasi','deporalan','-'),
	(40,'farmasi','igd','-'),
	(41,'farmasi','deporanap','-'),
	(42,'farmasi','gudang','-'),
	(43,'wagateway','server','https://mlite.id'),
	(44,'wagateway','token','-'),
	(45,'wagateway','phonenumber','-'),
	(46,'anjungan','display_poli',''),
	(47,'anjungan','carabayar',''),
	(48,'anjungan','antrian_loket','1'),
	(49,'anjungan','antrian_cs','2'),
	(50,'anjungan','antrian_apotek','3'),
	(51,'anjungan','panggil_loket','1'),
	(52,'anjungan','panggil_loket_nomor','1'),
	(53,'anjungan','panggil_cs','1'),
	(54,'anjungan','panggil_cs_nomor','1'),
	(55,'anjungan','panggil_apotek','1'),
	(56,'anjungan','panggil_apotek_nomor','1'),
	(57,'anjungan','text_anjungan','Running text anjungan pasien mandiri.....'),
	(58,'anjungan','text_loket','Running text display antrian loket.....'),
	(59,'anjungan','text_poli','Running text display antrian poliklinik.....'),
	(60,'anjungan','text_laboratorium','Running text display antrian laboratorium.....'),
	(61,'anjungan','text_apotek','Running text display antrian apotek.....'),
	(62,'anjungan','text_farmasi','Running text display antrian farmasi.....'),
	(63,'anjungan','vidio','G4im8_n0OoI'),
	(64,'api','apam_key','qtbexUAxzqO3M8dCOo2vDMFvgYjdUEdMLVo341'),
	(65,'api','apam_status_daftar','Terdaftar'),
	(66,'api','apam_status_dilayani','Anda siap dilayani'),
	(67,'api','apam_webappsurl','http://localhost/webapps/'),
	(68,'api','apam_normpetugas','000001,000002'),
	(69,'api','apam_limit','2'),
	(70,'api','apam_smtp_host','ssl://smtp.gmail.com'),
	(71,'api','apam_smtp_port','465'),
	(72,'api','apam_smtp_username',''),
	(73,'api','apam_smtp_password',''),
	(74,'api','apam_kdpj',''),
	(75,'api','apam_kdprop',''),
	(76,'api','apam_kdkab',''),
	(77,'api','apam_kdkec',''),
	(78,'api','duitku_merchantCode',''),
	(79,'api','duitku_merchantKey',''),
	(80,'api','duitku_paymentAmount',''),
	(81,'api','duitku_paymentMethod',''),
	(82,'api','duitku_productDetails',''),
	(83,'api','duitku_expiryPeriod',''),
	(84,'api','duitku_kdpj',''),
	(85,'api','berkasdigital_key','qtbexUAxzqO3M8dCOo2vDMFvgYjdUEdMLVo341'),
	(86,'jkn_mobile','x_username','jkn'),
	(87,'jkn_mobile','x_password','mobile'),
	(88,'jkn_mobile','header_token','X-Token'),
	(89,'jkn_mobile','header_username','X-Username'),
	(90,'jkn_mobile','header_password','X-Password'),
	(91,'jkn_mobile','BpjsConsID',''),
	(92,'jkn_mobile','BpjsSecretKey',''),
	(93,'jkn_mobile','BpjsUserKey',''),
	(94,'jkn_mobile','BpjsAntrianUrl','https://apijkn-dev.bpjs-kesehatan.go.id/antreanrs_dev/'),
	(95,'jkn_mobile','kd_pj_bpjs',''),
	(96,'jkn_mobile','exclude_taskid',''),
	(97,'jkn_mobile','display',''),
	(98,'jkn_mobile','kdprop','1'),
	(99,'jkn_mobile','kdkab','1'),
	(100,'jkn_mobile','kdkec','1'),
	(101,'jkn_mobile','kdkel','1'),
	(102,'jkn_mobile','perusahaan_pasien',''),
	(103,'jkn_mobile','suku_bangsa',''),
	(104,'jkn_mobile','bahasa_pasien',''),
	(105,'jkn_mobile','cacat_fisik',''),
	(106,'keuangan','jurnal_kasir','0'),
	(107,'keuangan','akun_kredit_pendaftaran',''),
	(108,'keuangan','akun_kredit_tindakan',''),
	(109,'keuangan','akun_kredit_obat_bhp',''),
	(110,'keuangan','akun_kredit_laboratorium',''),
	(111,'keuangan','akun_kredit_radiologi',''),
	(112,'keuangan','akun_kredit_tambahan_biaya',''),
	(113,'manajemen','penjab_umum','UMU'),
	(114,'manajemen','penjab_bpjs','BPJ'),
	(115,'presensi','lat','-2.58'),
	(116,'presensi','lon','115.37'),
	(117,'presensi','distance','2'),
	(118,'presensi','helloworld','Jangan Lupa Bahagia; \nCara untuk memulai adalah berhenti berbicara dan mulai melakukan; \nWaktu yang hilang tidak akan pernah ditemukan lagi; \nKamu bisa membodohi semua orang, tetapi kamu tidak bisa membohongi pikiranmu; \nIni bukan tentang ide. Ini tentang mewujudkan ide; \nBekerja bukan hanya untuk mencari materi. Bekerja merupakan manfaat bagi banyak orang'),
	(119,'vedika','carabayar',''),
	(120,'vedika','sep',''),
	(121,'vedika','skdp',''),
	(122,'vedika','operasi',''),
	(123,'vedika','individual',''),
	(124,'vedika','billing','mlite'),
	(125,'vedika','periode','2023-01'),
	(126,'vedika','verifikasi','2023-01'),
	(127,'vedika','inacbgs_prosedur_bedah',''),
	(128,'vedika','inacbgs_prosedur_non_bedah',''),
	(129,'vedika','inacbgs_konsultasi',''),
	(130,'vedika','inacbgs_tenaga_ahli',''),
	(131,'vedika','inacbgs_keperawatan',''),
	(132,'vedika','inacbgs_penunjang',''),
	(133,'vedika','inacbgs_pelayanan_darah',''),
	(134,'vedika','inacbgs_rehabilitasi',''),
	(135,'vedika','inacbgs_rawat_intensif',''),
	(136,'vedika','eklaim_url',''),
	(137,'vedika','eklaim_key',''),
	(138,'vedika','eklaim_kelasrs','CP'),
	(139,'vedika','eklaim_payor_id','3'),
	(140,'vedika','eklaim_payor_cd','JKN'),
	(141,'vedika','eklaim_cob_cd','#'),
	(142,'orthanc','server','http://localhost:8042'),
	(143,'orthanc','username','orthanc'),
	(144,'orthanc','password','orthanc'),
	(145,'veronisa','username',''),
	(146,'veronisa','password',''),
	(147,'veronisa','obat_kronis',''),
	(148,'jkn_mobile','kirimantrian','tidak'),
	(149,'settings','keamanan','ya'),
	(150,'dokter_ralan','set_sudah','tidak'),
	(151,'settings','websocket','tidak'),
	(152,'settings','websocket_proxy',''),
	(153,'settings','username_fp',''),
	(154,'settings','password_fp',''),
	(155,'settings','username_frista',''),
	(156,'settings','password_frista',''),
	(157,'settings','billing_obat','false'),
	(158,'settings','prefix_surat','RS'),
	(159,'farmasi','keterangan_etiket',''),
	(160,'pcare','consumerUserKeyAntrol','');

/*!40000 ALTER TABLE `mlite_settings` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table mlite_subrekening
# ------------------------------------------------------------

CREATE TABLE `mlite_subrekening` (
  `kd_rek` varchar(15) NOT NULL,
  `kd_rek2` varchar(15) NOT NULL,
  PRIMARY KEY (`kd_rek2`),
  KEY `kd_rek` (`kd_rek`),
  CONSTRAINT `mlite_subrekening_ibfk_1` FOREIGN KEY (`kd_rek`) REFERENCES `mlite_rekening` (`kd_rek`) ON UPDATE CASCADE,
  CONSTRAINT `mlite_subrekening_ibfk_2` FOREIGN KEY (`kd_rek2`) REFERENCES `mlite_rekening` (`kd_rek`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_surat_rujukan
# ------------------------------------------------------------

CREATE TABLE `mlite_surat_rujukan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_surat` varchar(100) DEFAULT NULL,
  `no_rawat` varchar(100) DEFAULT NULL,
  `no_rkm_medis` varchar(100) DEFAULT NULL,
  `nm_pasien` varchar(100) DEFAULT NULL,
  `tgl_lahir` varchar(100) DEFAULT NULL,
  `umur` varchar(100) DEFAULT NULL,
  `jk` varchar(100) DEFAULT NULL,
  `alamat` varchar(1000) DEFAULT NULL,
  `kepada` varchar(250) DEFAULT NULL,
  `di` varchar(250) DEFAULT NULL,
  `anamnesa` varchar(100) DEFAULT NULL,
  `pemeriksaan_fisik` varchar(100) DEFAULT NULL,
  `pemeriksaan_penunjang` varchar(100) DEFAULT NULL,
  `diagnosa` varchar(100) DEFAULT NULL,
  `terapi` varchar(100) DEFAULT NULL,
  `alasan_dirujuk` varchar(250) DEFAULT NULL,
  `dokter` varchar(100) DEFAULT NULL,
  `petugas` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_surat_sakit
# ------------------------------------------------------------

CREATE TABLE `mlite_surat_sakit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_surat` varchar(100) DEFAULT NULL,
  `no_rawat` varchar(100) DEFAULT NULL,
  `no_rkm_medis` varchar(100) DEFAULT NULL,
  `nm_pasien` varchar(100) DEFAULT NULL,
  `tgl_lahir` varchar(100) DEFAULT NULL,
  `umur` varchar(100) DEFAULT NULL,
  `jk` varchar(100) DEFAULT NULL,
  `alamat` varchar(1000) DEFAULT NULL,
  `keadaan` varchar(100) DEFAULT NULL,
  `diagnosa` varchar(100) DEFAULT NULL,
  `lama_angka` varchar(100) DEFAULT NULL,
  `lama_huruf` varchar(100) DEFAULT NULL,
  `tanggal_mulai` varchar(100) DEFAULT NULL,
  `tanggal_selesai` varchar(100) DEFAULT NULL,
  `dokter` varchar(100) DEFAULT NULL,
  `petugas` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_surat_sehat
# ------------------------------------------------------------

CREATE TABLE `mlite_surat_sehat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_surat` varchar(100) DEFAULT NULL,
  `no_rawat` varchar(100) DEFAULT NULL,
  `no_rkm_medis` varchar(100) DEFAULT NULL,
  `nm_pasien` varchar(100) DEFAULT NULL,
  `tgl_lahir` varchar(100) DEFAULT NULL,
  `umur` varchar(100) DEFAULT NULL,
  `jk` varchar(100) DEFAULT NULL,
  `alamat` varchar(1000) DEFAULT NULL,
  `tanggal` varchar(100) DEFAULT NULL,
  `berat_badan` varchar(100) DEFAULT NULL,
  `tinggi_badan` varchar(100) DEFAULT NULL,
  `tensi` varchar(100) DEFAULT NULL,
  `gol_darah` varchar(100) DEFAULT NULL,
  `riwayat_penyakit` varchar(100) DEFAULT NULL,
  `keperluan` varchar(100) DEFAULT NULL,
  `dokter` varchar(100) DEFAULT NULL,
  `petugas` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_temporary
# ------------------------------------------------------------

CREATE TABLE `mlite_temporary` (
  `temp1` text,
  `temp2` text,
  `temp3` text,
  `temp4` text,
  `temp5` text,
  `temp6` text,
  `temp7` text,
  `temp8` text,
  `temp9` text,
  `temp10` text,
  `temp11` text,
  `temp12` text,
  `temp13` text,
  `temp14` text,
  `temp15` text,
  `temp16` text,
  `temp17` text,
  `temp18` text,
  `temp19` text,
  `temp20` text,
  `temp21` text,
  `temp22` text,
  `temp23` text,
  `temp24` text,
  `temp25` text,
  `temp26` text,
  `temp27` text,
  `temp28` text,
  `temp29` text,
  `temp30` text,
  `temp31` text,
  `temp32` text,
  `temp33` text,
  `temp34` text,
  `temp35` text,
  `temp36` text,
  `temp37` text,
  `temp38` text,
  `temp39` text,
  `temp40` text,
  `temp41` text,
  `temp42` text,
  `temp43` text,
  `temp44` text,
  `temp45` text,
  `temp46` text,
  `temp47` text,
  `temp48` text,
  `temp49` text,
  `temp50` text,
  `temp51` text,
  `temp52` text,
  `temp53` text,
  `temp54` text,
  `temp55` text,
  `temp56` text,
  `temp57` text,
  `temp58` text,
  `temp59` text,
  `temp60` text,
  `temp61` text,
  `temp62` text,
  `temp63` text,
  `temp64` text,
  `temp65` text,
  `temp66` text,
  `temp67` text,
  `temp68` text,
  `temp69` text,
  `temp70` text,
  `temp71` text,
  `temp72` text,
  `temp73` text,
  `temp74` text,
  `temp75` text,
  `temp76` text,
  `temp77` text,
  `temp78` text,
  `temp79` text,
  `temp80` text,
  `temp81` text,
  `temp82` text,
  `temp83` text,
  `temp84` text,
  `temp85` text,
  `temp86` text,
  `temp87` text,
  `temp88` text,
  `temp89` text,
  `temp90` text,
  `temp91` text,
  `temp92` text,
  `temp93` text,
  `temp94` text,
  `temp95` text,
  `temp96` text,
  `temp97` text,
  `temp98` text,
  `temp99` text,
  `temp100` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table mlite_triase
# ------------------------------------------------------------

CREATE TABLE `mlite_triase` (
  `no_rawat` varchar(17) NOT NULL,
  `tgl_kunjungan` datetime NOT NULL,
  `cara_masuk` enum('Jalan','Brankar','Kursi Roda','Digendong') NOT NULL,
  `alat_transportasi` enum('-','AGD','Sendiri','Swasta') NOT NULL,
  `alasan_kedatangan` enum('Datang Sendiri','Polisi','Rujukan','-') NOT NULL,
  `keterangan_kedatangan` varchar(100) NOT NULL,
  `macam_kasus` enum('Trauma Kecelakaan Lalu Lintas','Trauma Kecelakaan Kerja','Trauma Kasus Unit Pelayanan Anak & Perempuan','Trauma Lainnya','Non Trauma') NOT NULL,
  `tekanan_darah` varchar(8) NOT NULL,
  `nadi` varchar(3) NOT NULL,
  `pernapasan` varchar(3) NOT NULL,
  `suhu` varchar(5) NOT NULL,
  `saturasi_o2` varchar(3) NOT NULL,
  `nyeri` varchar(5) NOT NULL,
  `jenis_triase` enum('Primer','Sekunder') NOT NULL,
  `keluhan_utama` varchar(500) NOT NULL,
  `kebutuhan_khusus` enum('-','UPPA','Airborne','Dekontaminan') NOT NULL,
  `catatan` varchar(100) NOT NULL,
  `plan` enum('Ruang Resusitasi','Ruang Kritis','Zona Kuning','Zona Hijau') NOT NULL,
  `nik` varchar(20) NOT NULL,
  PRIMARY KEY (`no_rawat`) USING BTREE,
  CONSTRAINT `mlite_triase_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_triase_detail
# ------------------------------------------------------------

CREATE TABLE `mlite_triase_detail` (
  `no_rawat` varchar(17) NOT NULL,
  `skala` varchar(3) NOT NULL,
  `kode_skala` varchar(3) NOT NULL,
  PRIMARY KEY (`no_rawat`,`skala`,`kode_skala`) USING BTREE,
  CONSTRAINT `mlite_triase_detail_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_triase_pemeriksaan
# ------------------------------------------------------------

CREATE TABLE `mlite_triase_pemeriksaan` (
  `kode_pemeriksaan` varchar(3) NOT NULL,
  `nama_pemeriksaan` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`kode_pemeriksaan`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_triase_skala
# ------------------------------------------------------------

CREATE TABLE `mlite_triase_skala` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_pemeriksaan` varchar(3) NOT NULL,
  `skala` int(11) NOT NULL,
  `kode_skala` varchar(3) NOT NULL,
  `pengkajian_skala` varchar(150) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `kode_pemeriksaan` (`kode_pemeriksaan`) USING BTREE,
  CONSTRAINT `mlite_triase_skala_ibfk_1` FOREIGN KEY (`kode_pemeriksaan`) REFERENCES `mlite_triase_pemeriksaan` (`kode_pemeriksaan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_users
# ------------------------------------------------------------

CREATE TABLE `mlite_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` text,
  `fullname` text,
  `description` text,
  `password` text,
  `avatar` text,
  `email` text,
  `role` varchar(100) NOT NULL DEFAULT 'user',
  `cap` varchar(100) DEFAULT '',
  `access` varchar(500) NOT NULL DEFAULT 'dashboard',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `mlite_users` WRITE;
/*!40000 ALTER TABLE `mlite_users` DISABLE KEYS */;

INSERT INTO `mlite_users` (`id`, `username`, `fullname`, `description`, `password`, `avatar`, `email`, `role`, `cap`, `access`)
VALUES
	(1,'admin','Administrator','Admin ganteng baik hati, suka menabung dan tidak sombong.','$2y$10$pgRnDiukCbiYVqsamMM3ROWViSRqbyCCL33N8.ykBKZx0dlplXe9i','avatar6422cb573b50c.png','info@mlite.id','admin','','all'),
	(2,'DR001','dr. Ataaka Muhammad','-','$2y$10$kuf2BxvViduBpUTn.6Nxsug3AskH/PGvXTSlfCfJqK8Ayb9a0.vqC','avatar643a104444515.png','info@mlite.id','admin','','all');

/*!40000 ALTER TABLE `mlite_users` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table mlite_users_vedika
# ------------------------------------------------------------

CREATE TABLE `mlite_users_vedika` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` text,
  `password` text,
  `fullname` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table mlite_vedika
# ------------------------------------------------------------

CREATE TABLE `mlite_vedika` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date DEFAULT NULL,
  `no_rkm_medis` varchar(6) NOT NULL,
  `no_rawat` varchar(100) NOT NULL,
  `tgl_registrasi` varchar(100) NOT NULL,
  `nosep` varchar(100) NOT NULL,
  `jenis` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mlite_vedika_feedback
# ------------------------------------------------------------

CREATE TABLE `mlite_vedika_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nosep` varchar(100) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `catatan` text,
  `username` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mlite_veronisa
# ------------------------------------------------------------

CREATE TABLE `mlite_veronisa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date DEFAULT NULL,
  `no_rkm_medis` varchar(6) NOT NULL,
  `no_rawat` varchar(100) NOT NULL,
  `tgl_registrasi` varchar(100) NOT NULL,
  `nosep` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mlite_veronisa_feedback
# ------------------------------------------------------------

CREATE TABLE `mlite_veronisa_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nosep` varchar(100) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `catatan` text,
  `username` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `mlite_set_nomor_surat` (
  `nomor_surat` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

SET FOREIGN_KEY_CHECKS=1;
