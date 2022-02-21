<?php
return [
    'name'          =>  'Web Site',
    'description'   =>  'Modul website untuk Rumah Sakit',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2022',
    'icon'          =>  'globe',
    'pages'         =>  ['Website' => 'website'],
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("CREATE TABLE `booking_periksa` (
          `no_booking` varchar(17) NOT NULL,
          `tanggal` date DEFAULT NULL,
          `nama` varchar(40) DEFAULT NULL,
          `alamat` varchar(200) DEFAULT NULL,
          `no_telp` varchar(40) DEFAULT NULL,
          `email` varchar(50) DEFAULT NULL,
          `kd_poli` varchar(5) DEFAULT NULL,
          `tambahan_pesan` varchar(400) DEFAULT NULL,
          `status` enum('Diterima','Ditolak','Belum Dibalas') NOT NULL,
          `tanggal_booking` datetime NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        $core->db()->pdo()->exec("ALTER TABLE `booking_periksa`
          ADD PRIMARY KEY (`no_booking`),
          ADD UNIQUE KEY `tanggal` (`tanggal`,`no_telp`),
          ADD KEY `kd_poli` (`kd_poli`);");

        $core->db()->pdo()->exec("ALTER TABLE `booking_periksa`
          ADD CONSTRAINT `booking_periksa_ibfk_1` FOREIGN KEY (`kd_poli`) REFERENCES `poliklinik` (`kd_poli`) ON DELETE CASCADE ON UPDATE CASCADE;");

        $core->db()->pdo()->exec("CREATE TABLE `booking_periksa_balasan` (
          `no_booking` varchar(17) NOT NULL,
          `balasan` text DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        $core->db()->pdo()->exec("ALTER TABLE `booking_periksa_balasan`
          ADD PRIMARY KEY (`no_booking`);");

        $core->db()->pdo()->exec("ALTER TABLE `booking_periksa_balasan`
          ADD CONSTRAINT `booking_periksa_balasan_ibfk_1` FOREIGN KEY (`no_booking`) REFERENCES `booking_periksa` (`no_booking`) ON DELETE CASCADE ON UPDATE CASCADE;");

        $core->db()->pdo()->exec("CREATE TABLE `booking_periksa_diterima` (
          `no_booking` varchar(17) NOT NULL,
          `no_rkm_medis` varchar(15) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        $core->db()->pdo()->exec("ALTER TABLE `booking_periksa_diterima`
          ADD PRIMARY KEY (`no_booking`),
          ADD KEY `no_rkm_medis` (`no_rkm_medis`);");

        $core->db()->pdo()->exec("ALTER TABLE `booking_periksa_diterima`
          ADD CONSTRAINT `booking_periksa_diterima_ibfk_1` FOREIGN KEY (`no_booking`) REFERENCES `booking_periksa` (`no_booking`) ON DELETE CASCADE ON UPDATE CASCADE,
          ADD CONSTRAINT `booking_periksa_diterima_ibfk_2` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON DELETE CASCADE ON UPDATE CASCADE;");

        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'login', '1')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'logo', 'website/logo.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'logo_icon', 'website/icon-logo.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'slider_bg', 'website/slider-bg.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'typewriter_1', 'Welcome to RS Khanza')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'typewriter_2', 'We Care Your Health')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'typewriter_3', 'We are Expert!')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'gawat_darurat', 'To save life and limb, itu adalah moto kami dalam layanan Gawat Darurat')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'rawat_jalan_1', '8.00 – 18.00')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'rawat_jalan_2', '8.00 – 16.00')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'rawat_jalan_3', '8.00 – 13.00')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'rawat_inap', 'Dengan 128 bed tersedia, 8 kamar VVIP, 24 Kelas 1, 56 Kelas 2 dan 50 Kelas 3')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'motto', 'Melayani Dengan Hati')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'about_title', 'MENGAPA MEMILIH KAMI')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'about_content', '<h2>Layanan Paripurna</h2>\r\nMemberi pelayanan kesehatan yang aman, bermutu, antidiskriminasi, dan efektif dengan mengutamakan kepentingan pasien sesuai dengan standar pelayanan Rumah Sakit.\r\n\r\nDengan Misi, Mendorong peningkatan kualitas kehidupan masyarakat Indonesia dengan menyediakan solusi bisnis kesehatan yang bernilai tambah dan mengutamakan prinsip kemanusiaan dan keselamatan.')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'about_bg', 'website/about_03.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'about_youtube', 'T0qagA4_eVQ')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'about_11', 'Peralatan Digital')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'about_12', 'website/clinic_01.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'about_21', 'Ruang Operasi Higienis')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'about_22', 'website/clinic_02.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'about_31', 'Spesialis Dibidangnya')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'about_32', 'website/clinic_03.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'about_41', 'Layanan Paripurna')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'about_42', 'website/clinic_01.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_11', 'FASILITAS PREMIUM')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_12', 'Untuk memastikan bahwa Anda diberi perawatan terbaik')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_13', 'website/service-icon1.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_14', 'Terbaik Dibidangnya')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_21', 'LABORATORIUM')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_22', 'Alat laboratorium terbaik untuk ketepatan diagnosa')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_23', 'website/service-icon2.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_24', 'Tekhnologi Terkini')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_31', 'DOKTER SPESIALIS')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_32', 'Dilayani 35 Dokter Spesialis dan Sub Spesialis')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_33', 'website/service-icon3.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_34', 'Akurat & Rendah Radiasi')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_41', 'PERAWATAN ANAK')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_42', 'Deteksi dini dan pelayanan tumbuh kembang anak')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_43', 'website/service-icon4.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_44', 'Untuk Buah Hati')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_51', 'LAYANAN FARMASI')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_52', 'Memastikan ketepatan indikasi, aturan dan dosis obat')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_53', 'website/service-icon5.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_54', 'Tepat & Cepat')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_61', 'BANK DARAH')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_62', 'Menjamin ketersediaan darah untuk transfusi yang aman')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_63', 'website/service-icon1.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_64', 'Aman dan Nyaman')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'footer_about', 'Rumah Sakit Pemerintah tipe C dengan layanan terdepan menggunakan tekhnologi terkini.')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'footer_informasi_11', 'Jadwal Dokter')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'footer_informasi_12', 'http://localhost/webapps/jadwal.php')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'footer_informasi_21', 'Ketersediaan Tempat Tidur')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'footer_informasi_22', 'http://localhost/webapps/bed5.php')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'footer_informasi_31', 'Display Antrian')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'footer_informasi_32', 'http://localhost/webapps/antrian.php')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'sosmed_facebook', 'basoro')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'sosmed_youtube', 'basoro')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'sosmed_instagram', 'basoro')");

        if (!is_dir(UPLOADS."/website")) {
            mkdir(UPLOADS."/website", 0777);
        }

        copy(MODULES.'/website/img/logo.png', UPLOADS.'/website/logo.png');
        copy(MODULES.'/website/img/icon-logo.png', UPLOADS.'/website/icon-logo.png');
        copy(MODULES.'/website/img/slider-bg.jpg', UPLOADS.'/website/slider-bg.jpg');
        copy(MODULES.'/website/img/about_03.jpg', UPLOADS.'/website/about_03.jpg');
        copy(MODULES.'/website/img/clinic_01.jpg', UPLOADS.'/website/clinic_01.jpg');
        copy(MODULES.'/website/img/clinic_02.jpg', UPLOADS.'/website/clinic_02.jpg');
        copy(MODULES.'/website/img/clinic_03.jpg', UPLOADS.'/website/clinic_03.jpg');
        copy(MODULES.'/website/img/service-icon1.png', UPLOADS.'/website/service-icon1.png');
        copy(MODULES.'/website/img/service-icon2.png', UPLOADS.'/website/service-icon2.png');
        copy(MODULES.'/website/img/service-icon3.png', UPLOADS.'/website/service-icon3.png');
        copy(MODULES.'/website/img/service-icon4.png', UPLOADS.'/website/service-icon4.png');
        copy(MODULES.'/website/img/service-icon5.png', UPLOADS.'/website/service-icon5.png');
        copy(MODULES.'/website/img/service-icon6.png', UPLOADS.'/website/service-icon6.png');

    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'website'");
        deleteDir(UPLOADS.'/website');
    }
];
