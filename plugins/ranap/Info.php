<?php
return [
    'name'          =>  'Rawat Inap',
    'description'   =>  'Modul pasien rawat inap',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '3.*',
    'icon'          =>  'hotel', // Icon from https://fontawesome.com/v4.7.0/icons/

    // Registering page for possible use as a homepage
    //'pages'            =>  ['Sample Page' => 'sample'],

    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('ranap', 'tab_resep', '0')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('ranap', 'tab_laboratorium', '0')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('ranap', 'tab_radiologi', '0')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('ranap', 'tab_digital', '0')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('ranap', 'tab_kontrol', '0')");
    },
    'uninstall'     =>  function () use ($core) {
    }
];
