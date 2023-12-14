<?php

if (!defined("UPGRADABLE")) {
    exit();
}

function rrmdir($dir)
{
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        if (is_dir("$dir/$file")) {
            rrmdir("$dir/$file");
        } else {
            unlink("$dir/$file");
        }
    }
    return rmdir($dir);
}

switch ($version) {
    case '4.0.0':
        // $this->core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_test` (
        //         `id` int(11) NOT NULL AUTO_INCREMENT,
        //         `name` varchar(30) DEFAULT NULL,
        //         PRIMARY KEY (`id`) USING BTREE
        // ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
        // Upgrade version
        $return = '4.0.1';
}

return $return;