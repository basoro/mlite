<?php

return [
    'name'          =>  'Keuangan',
    'description'   =>  'Modul Keuangan untuk mLITE',
    'author'        =>  'Basoro',
    'category'      =>  'keuangan', 
    'version'       =>  '1.0',
    'compatibility' =>  '6.*.*',
    'icon'          =>  'money',
    'install'       =>  function () use ($core) {
        // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
        // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
        $settings = [
            'jurnal_kasir' => '0',
            'akun_debet_kas' => '',
            'akun_kredit_pendaftaran' => '',
            'akun_kredit_tindakan' => '',
            'akun_kredit_obat_bhp' => '',
            'akun_kredit_laboratorium' => '',
            'akun_kredit_radiologi' => '',
        ];
        foreach ($settings as $field => $value) {
            $cek = $core->db('mlite_settings')->where('module', 'keuangan')->where('field', $field)->oneArray();
            if (!$cek) {
                $core->db('mlite_settings')->save(['module' => 'keuangan', 'field' => $field, 'value' => $value]);
            }
        }
    },
    'uninstall'     =>  function() use($core)
    {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'keuangan'");
    }
];
