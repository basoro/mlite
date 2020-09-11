<?php
return [
    'name'          =>  'E-Pasien',
    'description'   =>  'Modul epasien',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '3.*',
    'icon'          =>  'heartbeat',
    'pages'         =>  ['e-Pasien' => 'pasien'],
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('epasien', 'smtp_host', 'ssl://smtp.gmail.com')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('epasien', 'smtp_port', '465')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('epasien', 'smtp_username', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('epasien', 'smtp_password', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('epasien', 'kdprop', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('epasien', 'kdkab', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('epasien', 'kdkec', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('epasien', 'kdkel', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('epasien', 'perusahaan_pasien', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('epasien', 'suku_bangsa', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('epasien', 'bahasa_pasien', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('epasien', 'cacat_fisik', '')");
    },
    'uninstall'     =>  function () use ($core) {
    }
];
