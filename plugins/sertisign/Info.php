<?php

return [
    'name'          =>  'Sertisign',
    'description'   =>  'Modul integrasi TTE Sertisign',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'category'      =>  'bridging',
    'compatibility' =>  '5.*.*',
    'icon'          =>  'file-signature',
    'install'       =>  function() use($core) {
        $core->db('mlite_settings')->save(['module' => 'sertisign', 'field' => 'api_host', 'value' => 'https://api-stag.sertisign.id/']);
        $core->db('mlite_settings')->save(['module' => 'sertisign', 'field' => 'api_key', 'value' => '']);
    },
    'uninstall'     =>  function() use($core) {
        $core->db('mlite_settings')->where('module', 'sertisign')->delete();
    }
];
