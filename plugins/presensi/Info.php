<?php

return [
    'name'          =>  'Presensi',
    'description'   =>  'Modul presensi',
    'author'        =>  'Basoro.ID',
    'category'      =>  'manajemen', 
    'version'       =>  '1.2',
    'compatibility' =>  '6.*.*',
    'icon'          =>  'user-o',
    'install'       =>  function () use ($core) {
        // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
        // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
        $settings = [
            'lat' => '-2.58',
            'lon' => '115.37',
            'distance' => '2',
            'helloworld' => 'Jangan Lupa Bahagia; \nCara untuk memulai adalah berhenti berbicara dan mulai melakukan; \nWaktu yang hilang tidak akan pernah ditemukan lagi; \nKamu bisa membodohi semua orang, tetapi kamu tidak bisa membohongi pikiranmu; \nIni bukan tentang ide. Ini tentang mewujudkan ide; \nBekerja bukan hanya untuk mencari materi. Bekerja merupakan manfaat bagi banyak orang',
        ];
        foreach ($settings as $field => $value) {
            $cek = $core->db('mlite_settings')->where('module', 'presensi')->where('field', $field)->oneArray();
            if (!$cek) {
                $core->db('mlite_settings')->save(['module' => 'presensi', 'field' => $field, 'value' => $value]);
            }
        }
    },
    'uninstall'     =>  function() use($core)
    {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'presensi'");
    }
];
