<?php

return [
    'name' => 'Mini PACS',
    'description' => 'Menyimpan dan menampilkan DICOM metadata dengan Cornerstone Viewer Native (Zoom, WW/WL, Pan, Cine, Gallery) dan DICOM C-STORE Receiver.',
    'author' => 'Antigravity',
    'version' => '1.0',
    'category' => 'rekammedik',
    'compatibility' => '6.*.*',
    'icon' => 'camera-retro',
    'pages' => ['Mini PACS' => 'mini_pacs'],
    'install' => function () use ($core) {
        // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
        // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
        $settings = [
            'ae_title' => 'MLITE_PACS',
            'target_aet' => 'TARGET_PACS',
            'target_ip' => '127.0.0.1',
            'target_port' => '104',
            'worklist_aet' => 'MINIPACS',
            'worklist_port' => '10104',
            'is_mono' => '1',
            'remote_ip' => '',
            'remote_api_key' => '',
            'remote_username' => '',
            'remote_password' => '',
        ];
        foreach ($settings as $field => $value) {
            $cek = $core->db('mlite_settings')->where('module', 'mini_pacs')->where('field', $field)->oneArray();
            if (!$cek) {
                $core->db('mlite_settings')->save(['module' => 'mini_pacs', 'field' => $field, 'value' => $value]);
            }
        }
    },
    'uninstall' => function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'mini_pacs'");
    }
];
