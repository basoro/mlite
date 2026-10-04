<?php

return [
    'name'          =>  'WA Gateway',
    'description'   =>  'Modul Whatsapp Gateway mLITE',
    'author'        =>  'Basoro',
    'category'      =>  'bridging', 
    'version'       =>  '1.0',
    'compatibility' =>  '6.*.*',
    'icon'          =>  'whatsapp',
    'install'       =>  function () use ($core) {
        // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
        // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
        $settings = [
            'server' => 'https://mlite.id',
            'token' => '-',
            'phonenumber' => '-',
        ];
        foreach ($settings as $field => $value) {
            $cek = $core->db('mlite_settings')->where('module', 'wagateway')->where('field', $field)->oneArray();
            if (!$cek) {
                $core->db('mlite_settings')->save(['module' => 'wagateway', 'field' => $field, 'value' => $value]);
            }
        }
    },
    'uninstall'     =>  function() use($core)
    {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'wagateway'");
    }
];
