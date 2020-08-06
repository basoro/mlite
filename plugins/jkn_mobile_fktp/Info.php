<?php
return [
    'name'          =>  'JKN Mobile FKTP',
    'description'   =>  'Modul Khanza JKN Mobile API untuk FKTP',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '3.*',
    'icon'          =>  'tasks',
    'pages'         =>  ['JKN Mobile FKTP' => 'jknmobilefktp'],
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile_fktp', 'username', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile_fktp', 'password', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile_fktp', 'header', 'X-Token')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile_fktp', 'header_username', 'X-Username')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile_fktp', 'header_password', 'X-Password')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile_fktp', 'hari', '3')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile_fktp', 'display', '')");
    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `lite_options` WHERE `module` = 'jkn_mobile_fktp'");
    }
];
