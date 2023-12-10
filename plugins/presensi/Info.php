<?php

return [
    'name'          =>  'Presensi',
    'description'   =>  'Modul presensi',
    'author'        =>  'Basoro.ID',
    'version'       =>  '1.2',
    'compatibility' =>  '4.0.*',
    'icon'          =>  'user-o',
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('presensi', 'lat', '-2.58')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('presensi', 'lon', '115.37')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('presensi', 'distance', '2')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('presensi', 'helloworld', 'Jangan Lupa Bahagia; \nCara untuk memulai adalah berhenti berbicara dan mulai melakukan; \nWaktu yang hilang tidak akan pernah ditemukan lagi; \nKamu bisa membodohi semua orang, tetapi kamu tidak bisa membohongi pikiranmu; \nIni bukan tentang ide. Ini tentang mewujudkan ide; \nBekerja bukan hanya untuk mencari materi. Bekerja merupakan manfaat bagi banyak orang')");
    },
    'uninstall'     =>  function() use($core)
    {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'presensi'");
    }
];
