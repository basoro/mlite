<?php

return [
    'name'          =>  'Blog',
    'description'   =>  'Membuat artikel pada blog.',
    'author'        =>  'Basoro',
    'version'       =>  '1.3',
    'compatibility' =>  '2022',
    'icon'          =>  'pencil-square',
    'pages'         =>  ['Homepage' => 'homepage', 'Blog' => 'blog'],
    'install'       =>  function () use ($core) {

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_blog` (
          `id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
          `title`	TEXT NOT NULL,
          `slug`	TEXT NOT NULL,
          `user_id`	INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
          `content`	TEXT NOT NULL,
          `intro`	TEXT DEFAULT NULL,
          `cover_photo`	TEXT DEFAULT NULL,
          `status`	INTEGER NOT NULL,
          `comments`	INTEGER DEFAULT 1,
          `markdown`	INTEGER DEFAULT 0,
          `published_at`	INTEGER DEFAULT 0,
          `updated_at`	INTEGER NOT NULL,
          `created_at`	INTEGER NOT NULL
        );");

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_blog_tags` (
          `id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
          `name`	TEXT,
          `slug`	TEXT
        );");

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_blog_tags_relationship` (
          `blog_id`	INTEGER NOT NULL REFERENCES mlite_blog(id) ON DELETE CASCADE,
          `tag_id`	INTEGER NOT NULL REFERENCES mlite_blog_tags(id) ON DELETE CASCADE
        );");

        $core->db()->pdo()->exec("INSERT INTO `mlite_settings`
            (`module`, `field`, `value`)
            VALUES
            ('blog', 'perpage', '5'),
            ('blog', 'disqus', ''),
            ('blog', 'dateformat', 'M d, Y'),
            ('blog', 'title', 'Blog'),
            ('blog', 'desc', '... RS Masa Gitu ...'),
            ('blog', 'latestPostsCount', '5')
        ");

        if (!is_dir(UPLOADS."/blog")) {
            mkdir(UPLOADS."/blog", 0777);
        }

        copy(MODULES.'/blog/img/default.jpg', UPLOADS.'/blog/default.jpg');
        copy(MODULES.'/blog/img/default.jpg', UPLOADS.'/blog/default2.jpg');

        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_login', '1')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_logo', 'website/logo.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_logo_icon', 'website/icon-logo.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_slider_bg', 'website/slider-bg.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_typewriter_1', 'Welcome to RS Khanza')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_typewriter_2', 'We Care Your Health')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_typewriter_3', 'We are Expert!')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_gawat_darurat', 'To save life and limb, itu adalah moto kami dalam layanan Gawat Darurat')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_rawat_jalan_1', '8.00 – 18.00')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_rawat_jalan_2', '8.00 – 16.00')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_rawat_jalan_3', '8.00 – 13.00')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_rawat_inap', 'Dengan 128 bed tersedia, 8 kamar VVIP, 24 Kelas 1, 56 Kelas 2 dan 50 Kelas 3')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_motto', 'Melayani Dengan Hati')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_about_title', 'MENGAPA MEMILIH KAMI')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_about_content', '<h2>Layanan Paripurna</h2>\r\nMemberi pelayanan kesehatan yang aman, bermutu, antidiskriminasi, dan efektif dengan mengutamakan kepentingan pasien sesuai dengan standar pelayanan Rumah Sakit.\r\n\r\nDengan Misi, Mendorong peningkatan kualitas kehidupan masyarakat Indonesia dengan menyediakan solusi bisnis kesehatan yang bernilai tambah dan mengutamakan prinsip kemanusiaan dan keselamatan.')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_about_bg', 'website/about_03.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_about_youtube', 'T0qagA4_eVQ')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_about_11', 'Peralatan Digital')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_about_12', 'website/clinic_01.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_about_21', 'Ruang Operasi Higienis')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_about_22', 'website/clinic_02.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_about_31', 'Spesialis Dibidangnya')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_about_32', 'website/clinic_03.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_about_41', 'Layanan Paripurna')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_about_42', 'website/clinic_01.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_11', 'FASILITAS PREMIUM')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_12', 'Untuk memastikan bahwa Anda diberi perawatan terbaik')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_13', 'website/service-icon1.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_14', 'Terbaik Dibidangnya')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_21', 'LABORATORIUM')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_22', 'Alat laboratorium terbaik untuk ketepatan diagnosa')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_23', 'website/service-icon2.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_24', 'Tekhnologi Terkini')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_31', 'DOKTER SPESIALIS')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_32', 'Dilayani 35 Dokter Spesialis dan Sub Spesialis')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_33', 'website/service-icon3.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_34', 'Akurat & Rendah Radiasi')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_41', 'PERAWATAN ANAK')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_42', 'Deteksi dini dan pelayanan tumbuh kembang anak')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_43', 'website/service-icon4.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_44', 'Untuk Buah Hati')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_51', 'LAYANAN FARMASI')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_52', 'Memastikan ketepatan indikasi, aturan dan dosis obat')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_53', 'website/service-icon5.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_54', 'Tepat & Cepat')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_61', 'BANK DARAH')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_62', 'Menjamin ketersediaan darah untuk transfusi yang aman')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_63', 'website/service-icon1.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_services_64', 'Aman dan Nyaman')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_footer_about', 'Rumah Sakit Pemerintah tipe C dengan layanan terdepan menggunakan tekhnologi terkini.')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_footer_informasi_11', 'Jadwal Dokter')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_footer_informasi_12', 'http://localhost/webapps/jadwal.php')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_footer_informasi_21', 'Ketersediaan Tempat Tidur')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_footer_informasi_22', 'http://localhost/webapps/bed5.php')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_footer_informasi_31', 'Display Antrian')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_footer_informasi_32', 'http://localhost/webapps/antrian.php')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_sosmed_facebook', 'basoro')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_sosmed_youtube', 'basoro')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('blog', 'homepage_sosmed_instagram', 'basoro')");

        if (!is_dir(UPLOADS."/website")) {
            mkdir(UPLOADS."/website", 0777);
        }

        copy(MODULES.'/blog/website/img/logo.png', UPLOADS.'/website/logo.png');
        copy(MODULES.'/blog/website/img/icon-logo.png', UPLOADS.'/website/icon-logo.png');
        copy(MODULES.'/blog/website/img/slider-bg.jpg', UPLOADS.'/website/slider-bg.jpg');
        copy(MODULES.'/blog/website/img/about_03.jpg', UPLOADS.'/website/about_03.jpg');
        copy(MODULES.'/blog/website/img/clinic_01.jpg', UPLOADS.'/website/clinic_01.jpg');
        copy(MODULES.'/blog/website/img/clinic_02.jpg', UPLOADS.'/website/clinic_02.jpg');
        copy(MODULES.'/blog/website/img/clinic_03.jpg', UPLOADS.'/website/clinic_03.jpg');
        copy(MODULES.'/blog/website/img/service-icon1.png', UPLOADS.'/website/service-icon1.png');
        copy(MODULES.'/blog/website/img/service-icon2.png', UPLOADS.'/website/service-icon2.png');
        copy(MODULES.'/blog/website/img/service-icon3.png', UPLOADS.'/website/service-icon3.png');
        copy(MODULES.'/blog/website/img/service-icon4.png', UPLOADS.'/website/service-icon4.png');
        copy(MODULES.'/blog/website/img/service-icon5.png', UPLOADS.'/website/service-icon5.png');
        copy(MODULES.'/blog/website/img/service-icon6.png', UPLOADS.'/website/service-icon6.png');

    },
    'uninstall'     =>  function () use ($core) {
        //$core->db()->pdo()->exec("DROP TABLE `mlite_blog_tags_relationship`");
        //$core->db()->pdo()->exec("DROP TABLE `mlite_blog_tags`");
        //$core->db()->pdo()->exec("DROP TABLE `mlite_blog`");
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'blog'");
        deleteDir(UPLOADS."/blog");
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'website'");
        deleteDir(UPLOADS.'/website');
    }
];
