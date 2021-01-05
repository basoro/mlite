<?php
return [
    'name'          =>  'Web Site',
    'description'   =>  'Modul website untuk Rumah Sakit',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2021',
    'icon'          =>  'globe',
    'pages'         =>  ['Website' => 'website'],
    'install'       =>  function () use ($core) {
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
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_21', 'LABORATORIUM')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_22', 'Alat laboratorium terbaik untuk ketepatan diagnosa')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_31', 'DOKTER SPESIALIS')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_32', 'Dilayani 35 Dokter Spesialis dan Sub Spesialis')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_41', 'PERAWATAN ANAK')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_42', 'Deteksi dini dan pelayanan tumbuh kembang anak')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_51', 'LAYANAN FARMASI')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_52', 'Memastikan ketepatan indikasi, aturan dan dosis obat')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_61', 'BANK DARAH')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'services_62', 'Menjamin ketersediaan darah untuk transfusi yang aman')");
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

    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'website'");
        deleteDir(UPLOADS.'/website');
    }
];
