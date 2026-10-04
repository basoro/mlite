<?php
return [
    'name'          =>  'JKN Mobile FKTP',
    'description'   =>  'Modul JKN Mobile API untuk FKTP',
    'author'        =>  'Basoro',
    'category'      =>  'bridging', 
    'version'       =>  '1.0',
    'compatibility' =>  '6.*.*',
    'icon'          =>  'tasks',
    'pages'         =>  ['JKN Mobile FKTP' => 'jknmobilefktp'],
    'install'       =>  function () use ($core) {
        // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
        // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
        $settings = [
            'username' => '',
            'password' => '',
            'header' => 'X-Token',
            'header_username' => 'X-Username',
            'header_password' => 'X-Password',
            'kd_pj' => '',
            'hari' => '3',
            'display' => '',
            'kdkel' => '',
            'kdkec' => '',
            'kdkab' => '',
            'kdprop' => '',
        ];
        foreach ($settings as $field => $value) {
            $cek = $core->db('mlite_settings')->where('module', 'jkn_mobile_fktp')->where('field', $field)->oneArray();
            if (!$cek) {
                $core->db('mlite_settings')->save(['module' => 'jkn_mobile_fktp', 'field' => $field, 'value' => $value]);
            }
        }
    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'jkn_mobile_fktp'");
    }
];
