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
        } else {
            $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_users` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `username` text,
                `fullname` text,
                `description` text,
                `password` text,
                `avatar` text,
                `email` text,
                `role` VARCHAR(100) NOT NULL DEFAULT 'user',
                `cap` VARCHAR(100) NULL DEFAULT '',
                `access` VARCHAR(500) NOT NULL DEFAULT 'dashboard'
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

            $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_login_attempts` (
                `ip`    TEXT,
                `attempts`  INT(100) NOT NULL,
                `expires`   INT(100) NOT NULL DEFAULT 0
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

            $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_remember_me` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `token` text,
                `user_id` INT(10) NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                `expiry` INT(100) NOT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

            $core->mysql()->pdo()->exec("ALTER TABLE `mlite_remember_me`
              ADD CONSTRAINT `mlite_remember_me_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `mlite_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
            )");

        }

        $avatar = uniqid('avatar').'.png';
        $core->db()->pdo()->exec('INSERT INTO `mlite_users` (`username`, `fullname`, `description`, `password`, `avatar`, `email`, `role`, `cap`, `access`)
            VALUES ("admin", "Administrator", "Admin ganteng baik hati, suka menabung dan tidak sombong.", "$2y$10$pgRnDiukCbiYVqsamMM3ROWViSRqbyCCL33N8.ykBKZx0dlplXe9i", "'.$avatar.'", "info@mlite.id", "admin", "", "all")');

        if (!is_dir(UPLOADS."/users")) {
            mkdir(UPLOADS."/users", 0777);
        }

        copy(MODULES.'/users/img/default.png', UPLOADS.'/users/'.$avatar);
    },
    'uninstall'     =>  function () use ($core) {
    }
];
