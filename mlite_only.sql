CREATE TABLE IF NOT EXISTS `mlite_akun_kegiatan` (
  `id` int(11) NOT NULL,
  `kegiatan` varchar(200) DEFAULT NULL,
  `kd_rek` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mlite_antrian_loket` (
  `kd` int(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `noantrian` varchar(50) NOT NULL,
  `no_rkm_medis` varchar(50) DEFAULT NULL,
  `postdate` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL DEFAULT '00:00:00',
  `status` varchar(10) NOT NULL DEFAULT '0',
  `loket` varchar(10) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mlite_antrian_referensi` (
  `tanggal_periksa` date NOT NULL,
  `no_rkm_medis` varchar(50) NOT NULL,
  `nomor_kartu` varchar(50) NOT NULL,
  `nomor_referensi` varchar(50) NOT NULL,
  `kodebooking` varchar(100) NOT NULL,
  `jenis_kunjungan` varchar(10) NOT NULL,
  `status_kirim` varchar(20) DEFAULT NULL,
  `keterangan` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mlite_antrian_referensi_batal` (
  `tanggal_batal` date NOT NULL,
  `nomor_referensi` varchar(50) NOT NULL,
  `kodebooking` varchar(100) NOT NULL,
  `keterangan` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mlite_antrian_referensi_taskid` (
  `tanggal_periksa` date NOT NULL,
  `nomor_referensi` varchar(50) NOT NULL,
  `taskid` varchar(50) NOT NULL,
  `waktu` varchar(50) NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  `keterangan` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mlite_apamregister` (
  `nama_lengkap` varchar(225) NOT NULL,
  `email` varchar(225) NOT NULL,
  `nomor_ktp` varchar(225) NOT NULL,
  `nomor_telepon` varchar(225) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `mlite_billing` (
  `id_billing` int(11) NOT NULL,
  `kd_billing` varchar(100) NOT NULL,
  `no_rawat` varchar(17) NOT NULL,
  `jumlah_total` int(100) NOT NULL,
  `potongan` int(100) NOT NULL,
  `jumlah_harus_bayar` int(100) NOT NULL,
  `jumlah_bayar` int(100) NOT NULL,
  `tgl_billing` date NOT NULL,
  `jam_billing` time NOT NULL,
  `id_user` int(11) NOT NULL,
  `keterangan` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mlite_bridging_pcare` (
  `id` int(11) NOT NULL,
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
  `status_kirim` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `mlite_detailjurnal` (
  `no_jurnal` varchar(20) DEFAULT NULL,
  `kd_rek` varchar(15) DEFAULT NULL,
  `arus_kas` int(10) NOT NULL,
  `debet` double NOT NULL,
  `kredit` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mlite_duitku` (
  `id` int(10) NOT NULL,
  `tanggal` datetime NOT NULL,
  `no_rkm_medis` varchar(15) NOT NULL,
  `paymentUrl` varchar(255) NOT NULL,
  `merchantCode` varchar(255) NOT NULL,
  `reference` varchar(255) NOT NULL,
  `vaNumber` varchar(255) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `statusCode` varchar(255) NOT NULL,
  `statusMessage` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mlite_geolocation_presensi` (
  `id` int(11) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `latitude` varchar(200) NOT NULL,
  `longitude` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mlite_jurnal` (
  `no_jurnal` varchar(20) NOT NULL,
  `no_bukti` varchar(20) DEFAULT NULL,
  `tgl_jurnal` date DEFAULT NULL,
  `jenis` enum('U','P') DEFAULT NULL,
  `kegiatan` varchar(250) NOT NULL,
  `keterangan` varchar(350) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mlite_login_attempts` (
  `ip` text,
  `attempts` int(100) NOT NULL,
  `expires` int(100) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mlite_modules` (
  `id` int(11) NOT NULL,
  `dir` text,
  `sequence` text
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;

INSERT INTO `mlite_modules` (`id`, `dir`, `sequence`) VALUES
(1, 'settings', '9'),
(2, 'dashboard', '0'),
(3, 'master', '1'),
(4, 'pasien', '2'),
(5, 'rawat_jalan', '3'),
(6, 'kasir_rawat_jalan', '4'),
(7, 'kepegawaian', '5'),
(8, 'farmasi', '6'),
(9, 'users', '8'),
(10, 'modules', '7'),
(11, 'wagateway', '10'),
(12, 'apotek_ralan', '11'),
(13, 'dokter_ralan', '12'),
(14, 'igd', '13'),
(15, 'dokter_igd', '14'),
(16, 'laboratorium', '15'),
(17, 'radiologi', '16'),
(18, 'icd', '17'),
(19, 'rawat_inap', '18'),
(20, 'apotek_ranap', '19'),
(21, 'dokter_ranap', '20'),
(22, 'kasir_rawat_inap', '21'),
(23, 'operasi', '22'),
(24, 'anjungan', '23'),
(25, 'api', '24'),
(26, 'jkn_mobile', '25'),
(27, 'vclaim', '26'),
(28, 'keuangan', '27'),
(29, 'manajemen', '28'),
(30, 'presensi', '29'),
(31, 'vedika', '30'),
(32, 'profil', '31'),
(33, 'orthanc', '32'),
(34, 'veronisa', '33');

CREATE TABLE IF NOT EXISTS `mlite_notifications` (
  `id` int(11) NOT NULL,
  `judul` varchar(250) NOT NULL,
  `pesan` text NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `no_rkm_medis` varchar(255) NOT NULL,
  `status` varchar(250) NOT NULL DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mlite_odontogram` (
  `id` int(11) NOT NULL,
  `no_rkm_medis` text NOT NULL,
  `pemeriksaan` text,
  `kondisi` text,
  `catatan` text,
  `id_user` text NOT NULL,
  `tgl_input` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mlite_ohis` (
  `id` int(11) NOT NULL,
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
  `tgl_input` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mlite_pengaduan` (
  `id` varchar(15) NOT NULL,
  `tanggal` datetime NOT NULL,
  `no_rkm_medis` varchar(15) NOT NULL,
  `pesan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mlite_pengaduan_detail` (
  `id` int(10) NOT NULL,
  `pengaduan_id` varchar(15) NOT NULL,
  `tanggal` datetime NOT NULL,
  `no_rkm_medis` varchar(15) NOT NULL,
  `pesan` varchar(225) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `mlite_penjualan` (
  `id` int(11) NOT NULL,
  `nama_pembeli` varchar(100) DEFAULT NULL,
  `alamat_pembeli` varchar(100) DEFAULT NULL,
  `nomor_telepon` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `id_user` varchar(50) NOT NULL,
  `keterangan` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mlite_penjualan_barang` (
  `id` int(11) NOT NULL,
  `nama_barang` varchar(100) DEFAULT NULL,
  `stok` varchar(100) DEFAULT NULL,
  `harga` varchar(100) DEFAULT NULL,
  `keterangan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mlite_penjualan_billing` (
  `id` int(11) NOT NULL,
  `id_penjualan` int(11) NOT NULL,
  `jumlah_total` int(100) NOT NULL,
  `potongan` int(100) DEFAULT NULL,
  `jumlah_harus_bayar` int(100) NOT NULL,
  `jumlah_bayar` int(100) NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `id_user` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mlite_penjualan_detail` (
  `id` int(11) NOT NULL,
  `id_penjualan` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL,
  `jumlah` int(100) NOT NULL,
  `harga_total` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `id_user` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mlite_rekening` (
  `kd_rek` varchar(15) NOT NULL DEFAULT '',
  `nm_rek` varchar(100) DEFAULT NULL,
  `tipe` enum('N','M','R') DEFAULT NULL,
  `balance` enum('D','K') DEFAULT NULL,
  `level` enum('0','1') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mlite_rekeningtahun` (
  `thn` year(4) NOT NULL,
  `kd_rek` varchar(15) NOT NULL DEFAULT '',
  `saldo_awal` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mlite_remember_me` (
  `id` int(11) NOT NULL,
  `token` text,
  `user_id` int(10) NOT NULL,
  `expiry` int(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mlite_satu_sehat_departemen` (
  `dep_id` char(4) NOT NULL,
  `id_organisasi_satusehat` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mlite_satu_sehat_lokasi` (
  `kode` char(5) NOT NULL,
  `lokasi` varchar(40) DEFAULT NULL,
  `id_organisasi_satusehat` varchar(40) DEFAULT NULL,
  `id_lokasi_satusehat` varchar(40) DEFAULT NULL,
  `longitude` varchar(30) NOT NULL,
  `latitude` varchar(30) NOT NULL,
  `altitude` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mlite_satu_sehat_mapping_praktisi` (
  `practitioner_id` varchar(40) NOT NULL,
  `kd_dokter` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mlite_satu_sehat_response` (
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
  `id_observation_ttvkesadaran` varchar(50) DEFAULT NULL
  `id_procedure` varchar(50) DEFAULT NULL
  `id_composition` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mlite_settings` (
  `id` int(11) NOT NULL,
  `module` text,
  `field` text,
  `value` text
) ENGINE=MyISAM AUTO_INCREMENT=151 DEFAULT CHARSET=utf8;

INSERT INTO `mlite_settings` (`id`, `module`, `field`, `value`) VALUES
(1, 'settings', 'logo', 'uploads/settings/logo.png'),
(2, 'settings', 'nama_instansi', 'mLITE Indonesia'),
(3, 'settings', 'alamat', 'Jl. Perintis Kemerdekaan 45'),
(4, 'settings', 'kota', 'Barabai'),
(5, 'settings', 'propinsi', 'Kalimantan Selatan'),
(6, 'settings', 'nomor_telepon', '0812345678'),
(7, 'settings', 'email', 'info@mlite.id'),
(8, 'settings', 'website', 'https://mlite.id'),
(9, 'settings', 'ppk_bpjs', '-'),
(10, 'settings', 'footer', 'Copyright {?=date("Y")?} &copy; by drg. F. Basoro. All rights reserved.'),
(11, 'settings', 'homepage', 'main'),
(12, 'settings', 'wallpaper', 'uploads/settings/wallpaper.jpg'),
(13, 'settings', 'text_color', '#44813e'),
(14, 'settings', 'igd', 'IGDK'),
(15, 'settings', 'laboratorium', '-'),
(16, 'settings', 'pj_laboratorium', 'DR001'),
(17, 'settings', 'radiologi', '-'),
(18, 'settings', 'pj_radiologi', 'DR001'),
(19, 'settings', 'dokter_ralan_per_dokter', 'false'),
(20, 'settings', 'cekstatusbayar', 'false'),
(21, 'settings', 'ceklimit', 'false'),
(22, 'settings', 'responsivevoice', 'false'),
(23, 'settings', 'notif_presensi', 'true'),
(24, 'settings', 'BpjsApiUrl', 'https://apijkn-dev.bpjs-kesehatan.go.id/vclaim-rest-dev/'),
(25, 'settings', 'BpjsConsID', '-'),
(26, 'settings', 'BpjsSecretKey', '-'),
(27, 'settings', 'BpjsUserKey', '-'),
(28, 'settings', 'timezone', 'Asia/Makassar'),
(29, 'settings', 'theme', 'default'),
(30, 'settings', 'theme_admin', 'mlite'),
(31, 'settings', 'admin_mode', 'complex'),
(32, 'settings', 'input_kasir', 'tidak'),
(33, 'settings', 'editor', 'wysiwyg'),
(34, 'settings', 'version', '4.0.4'),
(35, 'settings', 'update_check', '0'),
(36, 'settings', 'update_changelog', ''),
(37, 'settings', 'update_version', '0'),
(38, 'settings', 'license', ''),
(39, 'farmasi', 'deporalan', '-'),
(40, 'farmasi', 'igd', '-'),
(41, 'farmasi', 'deporanap', '-'),
(42, 'farmasi', 'gudang', '-'),
(43, 'wagateway', 'server', 'https://mlite.id'),
(44, 'wagateway', 'token', '-'),
(45, 'wagateway', 'phonenumber', '-'),
(46, 'anjungan', 'display_poli', ''),
(47, 'anjungan', 'carabayar', ''),
(48, 'anjungan', 'antrian_loket', '1'),
(49, 'anjungan', 'antrian_cs', '2'),
(50, 'anjungan', 'antrian_apotek', '3'),
(51, 'anjungan', 'panggil_loket', '1'),
(52, 'anjungan', 'panggil_loket_nomor', '1'),
(53, 'anjungan', 'panggil_cs', '1'),
(54, 'anjungan', 'panggil_cs_nomor', '1'),
(55, 'anjungan', 'panggil_apotek', '1'),
(56, 'anjungan', 'panggil_apotek_nomor', '1'),
(57, 'anjungan', 'text_anjungan', 'Running text anjungan pasien mandiri.....'),
(58, 'anjungan', 'text_loket', 'Running text display antrian loket.....'),
(59, 'anjungan', 'text_poli', 'Running text display antrian poliklinik.....'),
(60, 'anjungan', 'text_laboratorium', 'Running text display antrian laboratorium.....'),
(61, 'anjungan', 'text_apotek', 'Running text display antrian apotek.....'),
(62, 'anjungan', 'text_farmasi', 'Running text display antrian farmasi.....'),
(63, 'anjungan', 'vidio', 'G4im8_n0OoI'),
(64, 'api', 'apam_key', 'qtbexUAxzqO3M8dCOo2vDMFvgYjdUEdMLVo341'),
(65, 'api', 'apam_status_daftar', 'Terdaftar'),
(66, 'api', 'apam_status_dilayani', 'Anda siap dilayani'),
(67, 'api', 'apam_webappsurl', 'http://localhost/webapps/'),
(68, 'api', 'apam_normpetugas', '000001,000002'),
(69, 'api', 'apam_limit', '2'),
(70, 'api', 'apam_smtp_host', 'ssl://smtp.gmail.com'),
(71, 'api', 'apam_smtp_port', '465'),
(72, 'api', 'apam_smtp_username', ''),
(73, 'api', 'apam_smtp_password', ''),
(74, 'api', 'apam_kdpj', ''),
(75, 'api', 'apam_kdprop', ''),
(76, 'api', 'apam_kdkab', ''),
(77, 'api', 'apam_kdkec', ''),
(78, 'api', 'duitku_merchantCode', ''),
(79, 'api', 'duitku_merchantKey', ''),
(80, 'api', 'duitku_paymentAmount', ''),
(81, 'api', 'duitku_paymentMethod', ''),
(82, 'api', 'duitku_productDetails', ''),
(83, 'api', 'duitku_expiryPeriod', ''),
(84, 'api', 'duitku_kdpj', ''),
(85, 'api', 'berkasdigital_key', 'qtbexUAxzqO3M8dCOo2vDMFvgYjdUEdMLVo341'),
(86, 'jkn_mobile', 'x_username', 'jkn'),
(87, 'jkn_mobile', 'x_password', 'mobile'),
(88, 'jkn_mobile', 'header_token', 'X-Token'),
(89, 'jkn_mobile', 'header_username', 'X-Username'),
(90, 'jkn_mobile', 'header_password', 'X-Password'),
(91, 'jkn_mobile', 'BpjsConsID', ''),
(92, 'jkn_mobile', 'BpjsSecretKey', ''),
(93, 'jkn_mobile', 'BpjsUserKey', ''),
(94, 'jkn_mobile', 'BpjsAntrianUrl', 'https://apijkn-dev.bpjs-kesehatan.go.id/antreanrs_dev/'),
(95, 'jkn_mobile', 'kd_pj_bpjs', ''),
(96, 'jkn_mobile', 'exclude_taskid', ''),
(97, 'jkn_mobile', 'display', ''),
(98, 'jkn_mobile', 'kdprop', '1'),
(99, 'jkn_mobile', 'kdkab', '1'),
(100, 'jkn_mobile', 'kdkec', '1'),
(101, 'jkn_mobile', 'kdkel', '1'),
(102, 'jkn_mobile', 'perusahaan_pasien', ''),
(103, 'jkn_mobile', 'suku_bangsa', ''),
(104, 'jkn_mobile', 'bahasa_pasien', ''),
(105, 'jkn_mobile', 'cacat_fisik', ''),
(106, 'keuangan', 'jurnal_kasir', '0'),
(107, 'keuangan', 'akun_kredit_pendaftaran', ''),
(108, 'keuangan', 'akun_kredit_tindakan', ''),
(109, 'keuangan', 'akun_kredit_obat_bhp', ''),
(110, 'keuangan', 'akun_kredit_laboratorium', ''),
(111, 'keuangan', 'akun_kredit_radiologi', ''),
(112, 'keuangan', 'akun_kredit_tambahan_biaya', ''),
(113, 'manajemen', 'penjab_umum', 'UMU'),
(114, 'manajemen', 'penjab_bpjs', 'BPJ'),
(115, 'presensi', 'lat', '-2.58'),
(116, 'presensi', 'lon', '115.37'),
(117, 'presensi', 'distance', '2'),
(118, 'presensi', 'helloworld', 'Jangan Lupa Bahagia; \nCara untuk memulai adalah berhenti berbicara dan mulai melakukan; \nWaktu yang hilang tidak akan pernah ditemukan lagi; \nKamu bisa membodohi semua orang, tetapi kamu tidak bisa membohongi pikiranmu; \nIni bukan tentang ide. Ini tentang mewujudkan ide; \nBekerja bukan hanya untuk mencari materi. Bekerja merupakan manfaat bagi banyak orang'),
(119, 'vedika', 'carabayar', ''),
(120, 'vedika', 'sep', ''),
(121, 'vedika', 'skdp', ''),
(122, 'vedika', 'operasi', ''),
(123, 'vedika', 'individual', ''),
(124, 'vedika', 'billing', 'mlite'),
(125, 'vedika', 'periode', '2023-01'),
(126, 'vedika', 'verifikasi', '2023-01'),
(127, 'vedika', 'inacbgs_prosedur_bedah', ''),
(128, 'vedika', 'inacbgs_prosedur_non_bedah', ''),
(129, 'vedika', 'inacbgs_konsultasi', ''),
(130, 'vedika', 'inacbgs_tenaga_ahli', ''),
(131, 'vedika', 'inacbgs_keperawatan', ''),
(132, 'vedika', 'inacbgs_penunjang', ''),
(133, 'vedika', 'inacbgs_pelayanan_darah', ''),
(134, 'vedika', 'inacbgs_rehabilitasi', ''),
(135, 'vedika', 'inacbgs_rawat_intensif', ''),
(136, 'vedika', 'eklaim_url', ''),
(137, 'vedika', 'eklaim_key', ''),
(138, 'vedika', 'eklaim_kelasrs', 'CP'),
(139, 'vedika', 'eklaim_payor_id', '3'),
(140, 'vedika', 'eklaim_payor_cd', 'JKN'),
(141, 'vedika', 'eklaim_cob_cd', '#'),
(142, 'orthanc', 'server', 'http://localhost:8042'),
(143, 'orthanc', 'username', 'orthanc'),
(144, 'orthanc', 'password', 'orthanc'),
(145, 'veronisa', 'username', ''),
(146, 'veronisa', 'password', ''),
(147, 'veronisa', 'obat_kronis', ''),
(148, 'jkn_mobile', 'kirimantrian', 'tidak'),
(149, 'settings', 'keamanan', 'ya'),
(150, 'dokter_ralan', 'set_sudah', 'tidak');

CREATE TABLE IF NOT EXISTS `mlite_subrekening` (
  `kd_rek` varchar(15) NOT NULL,
  `kd_rek2` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mlite_temporary` (
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

CREATE TABLE IF NOT EXISTS `mlite_users` (
  `id` int(11) NOT NULL,
  `username` text,
  `fullname` text,
  `description` text,
  `password` text,
  `avatar` text,
  `email` text,
  `role` varchar(100) NOT NULL DEFAULT 'user',
  `cap` varchar(100) DEFAULT '',
  `access` varchar(500) NOT NULL DEFAULT 'dashboard'
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

INSERT INTO `mlite_users` (`id`, `username`, `fullname`, `description`, `password`, `avatar`, `email`, `role`, `cap`, `access`) VALUES
(1, 'admin', 'Administrator', 'Admin ganteng baik hati, suka menabung dan tidak sombong.', '$2y$10$pgRnDiukCbiYVqsamMM3ROWViSRqbyCCL33N8.ykBKZx0dlplXe9i', 'avatar6422cb573b50c.png', 'info@mlite.id', 'admin', '', 'all');

CREATE TABLE IF NOT EXISTS `mlite_users_vedika` (
  `id` int(11) NOT NULL,
  `username` text,
  `password` text,
  `fullname` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mlite_vedika` (
  `id` int(11) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `no_rkm_medis` varchar(6) NOT NULL,
  `no_rawat` varchar(100) NOT NULL,
  `tgl_registrasi` varchar(100) NOT NULL,
  `nosep` varchar(100) NOT NULL,
  `jenis` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mlite_vedika_feedback` (
  `id` int(11) NOT NULL,
  `nosep` varchar(100) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `catatan` text,
  `username` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mlite_veronisa` (
  `id` int(11) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `no_rkm_medis` varchar(6) NOT NULL,
  `no_rawat` varchar(100) NOT NULL,
  `tgl_registrasi` varchar(100) NOT NULL,
  `nosep` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mlite_veronisa_feedback` (
  `id` int(11) NOT NULL,
  `nosep` varchar(100) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `catatan` text,
  `username` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `mlite_akun_kegiatan`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mlite_antrian_loket`
  ADD PRIMARY KEY (`kd`);

ALTER TABLE `mlite_billing`
  ADD PRIMARY KEY (`id_billing`);

ALTER TABLE `mlite_bridging_pcare`
  ADD PRIMARY KEY (`id`) USING BTREE;

ALTER TABLE `mlite_detailjurnal`
  ADD KEY `no_jurnal` (`no_jurnal`),
  ADD KEY `kd_rek` (`kd_rek`),
  ADD KEY `debet` (`debet`),
  ADD KEY `kredit` (`kredit`);

ALTER TABLE `mlite_duitku`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reference` (`reference`),
  ADD KEY `mlite_duitku_ibfk_1` (`no_rkm_medis`);

ALTER TABLE `mlite_geolocation_presensi`
  ADD KEY `mlite_geolocation_presensi_ibfk_1` (`id`);

ALTER TABLE `mlite_jurnal`
  ADD PRIMARY KEY (`no_jurnal`),
  ADD KEY `no_bukti` (`no_bukti`),
  ADD KEY `tgl_jurnal` (`tgl_jurnal`),
  ADD KEY `jenis` (`jenis`),
  ADD KEY `keterangan` (`keterangan`);

ALTER TABLE `mlite_modules`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mlite_notifications`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mlite_odontogram`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mlite_ohis`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mlite_pengaduan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `no_rkm_medis` (`no_rkm_medis`);

ALTER TABLE `mlite_pengaduan_detail`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `pengaduan_detail_ibfk_1` (`pengaduan_id`);

ALTER TABLE `mlite_penjualan`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mlite_penjualan_barang`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mlite_penjualan_billing`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mlite_penjualan_detail`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mlite_rekening`
  ADD PRIMARY KEY (`kd_rek`),
  ADD KEY `nm_rek` (`nm_rek`),
  ADD KEY `tipe` (`tipe`),
  ADD KEY `balance` (`balance`);

ALTER TABLE `mlite_rekeningtahun`
  ADD PRIMARY KEY (`thn`,`kd_rek`),
  ADD KEY `kd_rek` (`kd_rek`),
  ADD KEY `saldo_awal` (`saldo_awal`);

ALTER TABLE `mlite_remember_me`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mlite_remember_me_ibfk_1` (`user_id`);

ALTER TABLE `mlite_satu_sehat_departemen`
  ADD PRIMARY KEY (`dep_id`),
  ADD UNIQUE KEY `id_organisasi_satusehat` (`id_organisasi_satusehat`);

ALTER TABLE `mlite_satu_sehat_lokasi`
  ADD PRIMARY KEY (`kode`),
  ADD UNIQUE KEY `id_lokasi_satusehat` (`id_lokasi_satusehat`),
  ADD KEY `id_organisasi_satusehat` (`id_organisasi_satusehat`);

ALTER TABLE `mlite_satu_sehat_mapping_praktisi`
  ADD PRIMARY KEY (`practitioner_id`),
  ADD KEY `kd_dokter` (`kd_dokter`);

ALTER TABLE `mlite_satu_sehat_response`
  ADD PRIMARY KEY (`no_rawat`);

ALTER TABLE `mlite_settings`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mlite_subrekening`
  ADD PRIMARY KEY (`kd_rek2`),
  ADD KEY `kd_rek` (`kd_rek`);

ALTER TABLE `mlite_users`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mlite_users_vedika`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mlite_vedika`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mlite_vedika_feedback`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mlite_veronisa`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mlite_veronisa_feedback`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mlite_akun_kegiatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_antrian_loket`
  MODIFY `kd` int(50) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_billing`
  MODIFY `id_billing` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_bridging_pcare`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_duitku`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=35;

ALTER TABLE `mlite_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_odontogram`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_ohis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_pengaduan_detail`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_penjualan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_penjualan_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_penjualan_billing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_penjualan_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_remember_me`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=151;

ALTER TABLE `mlite_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;

ALTER TABLE `mlite_users_vedika`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_vedika`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_vedika_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_veronisa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_veronisa_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mlite_detailjurnal`
  ADD CONSTRAINT `mlite_detailjurnal_ibfk_1` FOREIGN KEY (`no_jurnal`) REFERENCES `mlite_jurnal` (`no_jurnal`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mlite_detailjurnal_ibfk_2` FOREIGN KEY (`kd_rek`) REFERENCES `mlite_rekening` (`kd_rek`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mlite_duitku`
  ADD CONSTRAINT `mlite_duitku_ibfk_1` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mlite_geolocation_presensi`
  ADD CONSTRAINT `mlite_geolocation_presensi_ibfk_1` FOREIGN KEY (`id`) REFERENCES `pegawai` (`id`) ON UPDATE CASCADE;

ALTER TABLE `mlite_pengaduan`
  ADD CONSTRAINT `mlite_pengaduan_ibfk_1` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mlite_pengaduan_detail`
  ADD CONSTRAINT `mlite_pengaduan_detail_ibfk_1` FOREIGN KEY (`pengaduan_id`) REFERENCES `mlite_pengaduan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mlite_rekeningtahun`
  ADD CONSTRAINT `mlite_rekeningtahun_ibfk_1` FOREIGN KEY (`kd_rek`) REFERENCES `mlite_rekening` (`kd_rek`) ON UPDATE CASCADE;

ALTER TABLE `mlite_satu_sehat_lokasi`
  ADD CONSTRAINT `mlite_satu_sehat_lokasi_ibfk_2` FOREIGN KEY (`id_organisasi_satusehat`) REFERENCES `mlite_satu_sehat_departemen` (`id_organisasi_satusehat`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mlite_satu_sehat_mapping_praktisi`
  ADD CONSTRAINT `mlite_satu_sehat_mapping_praktisi_ibfk_1` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mlite_satu_sehat_response`
  ADD CONSTRAINT `mlite_satu_sehat_response_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mlite_subrekening`
  ADD CONSTRAINT `mlite_subrekening_ibfk_1` FOREIGN KEY (`kd_rek`) REFERENCES `mlite_rekening` (`kd_rek`) ON UPDATE CASCADE,
  ADD CONSTRAINT `mlite_subrekening_ibfk_2` FOREIGN KEY (`kd_rek2`) REFERENCES `mlite_rekening` (`kd_rek`) ON DELETE CASCADE ON UPDATE CASCADE;
