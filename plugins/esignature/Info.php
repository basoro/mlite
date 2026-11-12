<?php

return [
    'name' => 'E-Signature',
    'description' => 'Modul Tanda Tangan Elektronik (TTE) Tersertifikasi',
    'author' => 'Basoro',
    'category' => 'manajemen',
    'version' => '1.0',
    'compatibility' => '6.*.*',
    'icon' => 'sticky-note-o',
    'install' => function () use ($core) {
        // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
        // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
        $settings = [
            'kode_berkasdigital' => '',
        ];
        foreach ($settings as $field => $value) {
            $cek = $core->db('mlite_settings')->where('module', 'esignature')->where('field', $field)->oneArray();
            if (!$cek) {
                $core->db('mlite_settings')->save(['module' => 'esignature', 'field' => $field, 'value' => $value]);
            }
        }

        // Passphrase pribadi per pengguna untuk menandatangani dokumen
        // (pengganti gambar tanda tangan).
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_esignature_passphrase` (
          `user_id` varchar(50) NOT NULL,
          `passphrase` varchar(255) NOT NULL,
          `updated_at` datetime NOT NULL,
          PRIMARY KEY (`user_id`)
        )");

        // Pencatatan percobaan passphrase salah (pembatasan percobaan sign).
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_esignature_sign_attempt` (
          `user_id` varchar(50) NOT NULL,
          `attempts` int NOT NULL DEFAULT 0,
          `last_attempt` datetime NOT NULL,
          PRIMARY KEY (`user_id`)
        )");

        // Pembatasan percobaan saat mengganti passphrase lama.
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_esignature_passphrase_attempt` (
          `user_id` varchar(50) NOT NULL,
          `attempts` int NOT NULL DEFAULT 0,
          `last_attempt` datetime NOT NULL,
          PRIMARY KEY (`user_id`)
        )");

        // Audit aktivitas passphrase (mis. reset oleh admin).
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_esignature_passphrase_log` (
          `user_id` varchar(50) NOT NULL,
          `action` varchar(30) NOT NULL,
          `note` varchar(255) DEFAULT NULL,
          `actor_id` varchar(50) NOT NULL,
          `actor_name` varchar(255) NOT NULL,
          `ip_address` varchar(45) NOT NULL,
          `created_at` datetime NOT NULL
        )");
    },
    'uninstall' => function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'esignature'");
    }
];
