<?php

return [
    'name'          =>  'Pengguna',
    'description'   =>  'Pengelolaan akun pengguna.',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '3.*',
    'icon'          =>  'user-md',

    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `lite_roles` (
            `id` int(10) NOT NULL,
            `username` varchar(50) NOT NULL,
            `role` varchar(50) NOT NULL DEFAULT 'admin',
            `cap` varchar(50) NOT NULL DEFAULT '-',
            `access` varchar(250) NOT NULL DEFAULT 'all'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `lite_login_attempts` (
            `ip` TEXT NOT NULL,
            `attempts` int(11) NOT NULL,
            `expires` int(11) NOT NULL DEFAULT '0'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `lite_remember_me` (
            `id` int(10) NOT NULL,
            `token` text NOT NULL,
            `user_id` varchar(50) NOT NULL,
            `expiry` int(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec('ALTER TABLE `lite_remember_me`
            ADD PRIMARY KEY (`id`);');

        $core->db()->pdo()->exec('ALTER TABLE `lite_roles`
            ADD PRIMARY KEY (`id`);');

        $core->db()->pdo()->exec('ALTER TABLE `lite_remember_me`
            MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;');

        $core->db()->pdo()->exec('ALTER TABLE `lite_roles`
            MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;');

        $row = $this->db()->pdo()->prepare("SELECT AES_DECRYPT(usere,'nur') as username FROM admin");
        $row->execute();
        $row = $row->fetch();

        $core->db()->pdo()->exec("INSERT INTO `lite_roles` (`username`, `role`, `cap`, `access`)
            VALUES ('$row[username]', 'admin', '-', 'all')");

        $row = $this->db()->pdo()->prepare("SELECT AES_DECRYPT(id_user,'nur') as username FROM user LIMIT 1");
        $row->execute();
        $row = $row->fetch();

        $core->db()->pdo()->exec("INSERT INTO `lite_roles` (`username`, `role`, `cap`, `access`)
            VALUES ('$row[username]', 'admin', '-', 'all')");

    }

];
