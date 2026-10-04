<?php

return [
    'name' => 'E-Signature',
    'description' => 'Modul Tanda Tangan Elektronik (TTE) Tersertifikasi',
    'author' => 'Basoro',
    'category' => 'manajemen',
    'version' => '1.0',
    'compatibility' => '6.*.*',
    'icon' => 'sticky-note-o',
    'install' => function () use ($core) {
        // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
        // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
        $settings = [
            'kode_berkasdigital' => '',
        ];
        foreach ($settings as $field => $value) {
            $cek = $core->db('mlite_settings')->where('module', 'esignature')->where('field', $field)->oneArray();
            if (!$cek) {
                $core->db('mlite_settings')->save(['module' => 'esignature', 'field' => $field, 'value' => $value]);
            }
        }

    },
    'uninstall' => function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'esignature'");
    }
];
