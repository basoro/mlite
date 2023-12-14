<?php

return [
    'name'          =>  'Website',
    'description'   =>  'Membuat website dan berita.',
    'author'        =>  'Basoro',
    'version'       =>  '1.3',
    'compatibility' =>  '4.0.*',
    'icon'          =>  'pencil-square',
    'pages'         =>  ['Homepage' => 'homepage', 'Berita' => 'news'],
    'install'       =>  function () use ($core) {

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_news` (
          `id` int(11) NOT NULL,
          `title` VARCHAR(225) NOT NULL,
          `slug` VARCHAR(225) NOT NULL,
          `user_id` int(11) NOT NULL,
          `content` text NOT NULL,
          `intro` text,
          `cover_photo` text,
          `status` int(11) NOT NULL,
          `comments` int(11) DEFAULT '1',
          `markdown` int(11) DEFAULT '0',
          `published_at` int(11) DEFAULT '0',
          `updated_at` int(11) NOT NULL,
          `created_at` int(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_news_tags` (
          `id` int(11) NOT NULL,
          `name` VARCHAR(225),
          `slug` VARCHAR(225)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_news_tags_relationship` (
          `news_id` int(11) NOT NULL,
          `tag_id` int(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $core->mysdbql()->pdo()->exec("ALTER TABLE `mlite_news`
          ADD PRIMARY KEY (`id`);");

        $core->db()->pdo()->exec("ALTER TABLE `mlite_news_tags`
          ADD PRIMARY KEY (`id`);");

        $core->db()->pdo()->exec("ALTER TABLE `mlite_news_tags_relationship`
          ADD KEY `mlite_news_tags_relationship_ibfk_1` (`news_id`),
          ADD KEY `tag_id` (`tag_id`);");

        $core->db()->pdo()->exec("ALTER TABLE `mlite_news`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");

        $core->db()->pdo()->exec("ALTER TABLE `mlite_news_tags`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");

        $core->db()->pdo()->exec("ALTER TABLE `mlite_news_tags_relationship`
          ADD CONSTRAINT `mlite_news_tags_relationship_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `mlite_news` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
          ADD CONSTRAINT `mlite_news_tags_relationship_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `mlite_news_tags` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;");

        $core->db()->pdo()->exec("INSERT INTO `mlite_settings`
            (`module`, `field`, `value`)
            VALUES
            ('website', 'perpage', '5'),
            ('website', 'disqus', ''),
            ('website', 'dateformat', 'M d, Y'),
            ('website', 'title', 'mLITE Indonesia'),
            ('website', 'desc', '... RS Masa Gitu ...'),
            ('website', 'latestPostsCount', '5')
        ");

        if (!is_dir(UPLOADS."/website/news")) {
            mkdir(UPLOADS."/website/news", 0777);
        }

        copy(MODULES.'/website/img/default.jpg', UPLOADS.'/website/default.jpg');

        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_login', '1')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_logo', 'website/logo.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_logo_icon', 'website/icon-logo.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_slider_bg', 'website/slider-bg.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_typewriter_1', 'Medic LITE Indonesia')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_typewriter_2', 'We Care Your Health')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_typewriter_3', 'We are Expert!')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_gawat_darurat', 'To save life and limb, itu adalah moto kami dalam layanan Gawat Darurat')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_rawat_jalan_1', '8.00 – 18.00')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_rawat_jalan_2', '8.00 – 16.00')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_rawat_jalan_3', '8.00 – 13.00')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_rawat_inap', 'Dengan 128 bed tersedia, 8 kamar VVIP, 24 Kelas 1, 56 Kelas 2 dan 50 Kelas 3')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_motto', 'Melayani Dengan Hati')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_about_title', 'MENGAPA MEMILIH KAMI')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_about_content', '<h2>Layanan Paripurna</h2>\r\nMemberi pelayanan kesehatan yang aman, bermutu, antidiskriminasi, dan efektif dengan mengutamakan kepentingan pasien sesuai dengan standar pelayanan Rumah Sakit.\r\n\r\nDengan Misi, Mendorong peningkatan kualitas kehidupan masyarakat Indonesia dengan menyediakan solusi bisnis kesehatan yang bernilai tambah dan mengutamakan prinsip kemanusiaan dan keselamatan.')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_about_bg', 'website/about_03.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_about_youtube', 'T0qagA4_eVQ')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_about_11', 'Peralatan Digital')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_about_12', 'website/clinic_01.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_about_21', 'Ruang Operasi Higienis')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_about_22', 'website/clinic_02.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_about_31', 'Spesialis Dibidangnya')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_about_32', 'website/clinic_03.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_about_41', 'Layanan Paripurna')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_about_42', 'website/clinic_01.jpg')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_11', 'FASILITAS PREMIUM')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_12', 'Untuk memastikan bahwa Anda diberi perawatan terbaik')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_13', 'website/service-icon1.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_14', 'Terbaik Dibidangnya')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_15', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_21', 'LABORATORIUM')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_22', 'Alat laboratorium terbaik untuk ketepatan diagnosa')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_23', 'website/service-icon2.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_24', 'Tekhnologi Terkini')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_25', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_31', 'DOKTER SPESIALIS')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_32', 'Dilayani 35 Dokter Spesialis dan Sub Spesialis')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_33', 'website/service-icon3.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_34', 'Akurat & Rendah Radiasi')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_35', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_41', 'PERAWATAN ANAK')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_42', 'Deteksi dini dan pelayanan tumbuh kembang anak')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_43', 'website/service-icon4.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_44', 'Untuk Buah Hati')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_45', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_51', 'LAYANAN FARMASI')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_52', 'Memastikan ketepatan indikasi, aturan dan dosis obat')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_53', 'website/service-icon5.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_54', 'Tepat & Cepat')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_55', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_61', 'BANK DARAH')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_62', 'Menjamin ketersediaan darah untuk transfusi yang aman')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_63', 'website/service-icon1.png')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_64', 'Aman dan Nyaman')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_services_65', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_footer_about', 'Rumah Sakit Pemerintah tipe C dengan layanan terdepan menggunakan tekhnologi terkini.')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_footer_informasi_11', 'Jadwal Dokter')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_footer_informasi_12', 'http://localhost/webapps/jadwal.php')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_footer_informasi_21', 'Ketersediaan Tempat Tidur')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_footer_informasi_22', 'http://localhost/webapps/bed5.php')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_footer_informasi_31', 'Display Antrian')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_footer_informasi_32', 'http://localhost/webapps/antrian.php')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_sosmed_facebook', 'basoro')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_sosmed_youtube', 'basoro')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('website', 'homepage_sosmed_instagram', 'basoro')");

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
    }
];
