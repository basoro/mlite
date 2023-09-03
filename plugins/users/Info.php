<?php

return [
    'name'          =>  'Pengguna',
    'description'   =>  'Pengelolaan pengguna',
    'author'        =>  'Basoro.ID',
    'version'       =>  '1.1',
    'compatibility' =>  '2023',
    'icon'          =>  'user',
    'pages'         =>  ['Login' => 'login'],

    'install'       =>  function () use ($core) {
        if (!is_dir(UPLOADS."/users")) {
            mkdir(UPLOADS."/users", 0777);
        }

        if(MULTI_APP) {
            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_users` (
                `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT,
                `username` text NOT NULL,
                `fullname` text NULL,
                `description` text NULL,
                `password` text NOT NULL,
                `avatar` text NOT NULL,
                `email` text NOT NULL,
                `role` text NOT NULL DEFAULT 'admin',
                `cap` text NULL,
                `access` text NOT NULL DEFAULT 'dashboard'
            )");

            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_login_attempts` (
                `ip` TEXT NOT NULL,
                `attempts` INTEGER NOT NULL,
                `expires` INTEGER NOT NULL DEFAULT 0
            )");

            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_remember_me` (
                `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT,
                `token` text NOT NULL,
                `user_id` integer NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                `expiry` integer NOT NULL
            )");

            $avatar = uniqid('avatar').'.png';
            $core->db()->pdo()->exec('INSERT INTO `mlite_users` (`username`, `fullname`, `description`, `password`, `avatar`, `email`, `role`, `cap`, `access`)
                VALUES ("admin", "Administrator", "Admin ganteng baik hati, suka menabung dan tidak sombong.", "$2y$10$pgRnDiukCbiYVqsamMM3ROWViSRqbyCCL33N8.ykBKZx0dlplXe9i", "'.$avatar.'", "info@mlite.id", "admin", "", "all")');

            copy(MODULES.'/users/img/default.png', UPLOADS.'/users/'.$avatar);

        }

    },
    'uninstall'     =>  function () use ($core) {
    }
];
