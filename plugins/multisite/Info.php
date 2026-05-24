<?php

return [
    'name'          =>  'Multisite',
    'description'   =>  'Registrasi mandiri untuk membuat instance mLITE berbasis subdomain.',
    'author'        =>  'Basoro',
    'category'      =>  'manajemen',
    'version'       =>  '1.0',
    'compatibility' =>  '6.*.*',
    'icon'          =>  'sitemap',
    'hidden'        =>  true,
    'pages'         =>  ['Pendaftaran' => 'daftar'],
    'install'       =>  function () use ($core) {
        if (DBDRIVER === 'sqlite') {
            $core->db()->pdo()->exec("
                CREATE TABLE IF NOT EXISTS mlite_multisite_tenants (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    subdomain TEXT NOT NULL UNIQUE,
                    db_name TEXT NOT NULL,
                    admin_email TEXT,
                    admin_username TEXT DEFAULT 'admin',
                    status INTEGER DEFAULT 1,
                    created_at TEXT
                )
            ");
        } else {
            $core->db()->pdo()->exec("
                CREATE TABLE IF NOT EXISTS `mlite_multisite_tenants` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `subdomain` varchar(63) NOT NULL,
                    `db_name` varchar(128) NOT NULL,
                    `admin_email` varchar(191) DEFAULT NULL,
                    `admin_username` varchar(64) DEFAULT 'admin',
                    `admin_password_hash` varchar(255) DEFAULT NULL,
                    `install_token` varchar(128) DEFAULT NULL,
                    `install_token_expires_at` datetime DEFAULT NULL,
                    `is_installed` tinyint(1) NOT NULL DEFAULT 0,
                    `status` tinyint(1) NOT NULL DEFAULT 1,
                    `requested_at` datetime DEFAULT NULL,
                    `created_at` datetime DEFAULT NULL,
                    `installed_at` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `subdomain` (`subdomain`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC
            ");
        }
    }
];
