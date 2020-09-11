<?php
return [
    'name'          =>  'Rawat Jalan',
    'description'   =>  'Pengelolaan data pasien rawat jalan.',
    'author'        =>  'Basoro',
    'version'       =>  '1.2',
    'compatibility' =>  '3.*',
    'icon'          =>  'wheelchair',
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('ralan', 'tab_resep', '0')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('ralan', 'tab_laboratorium', '0')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('ralan', 'tab_radiologi', '0')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('ralan', 'tab_digital', '0')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('ralan', 'tab_kontrol', '0')");
    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `lite_options` WHERE `module` = 'ralan'");
    }
];
