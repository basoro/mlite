<?php

return [
    'name'          =>  'Surat-Surat',
    'description'   =>  'Modul mLITE surat rujukan, sakit, sehat, dan bebas narkoba',
    'author'        =>  'Basoro',
    'category'      =>  'manajemen',
    'version'       =>  '1.1',
    'compatibility' =>  '5.*.*',
    'icon'          =>  'code',
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_surat_bebas_narkoba` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nomor_surat` varchar(100) DEFAULT NULL,
            `no_rawat` varchar(100) DEFAULT NULL,
            `no_rkm_medis` varchar(100) DEFAULT NULL,
            `nm_pasien` varchar(100) DEFAULT NULL,
            `tgl_lahir` varchar(100) DEFAULT NULL,
            `umur` varchar(100) DEFAULT NULL,
            `jk` varchar(100) DEFAULT NULL,
            `alamat` varchar(1000) DEFAULT NULL,
            `tanggal` varchar(100) DEFAULT NULL,
            `hasil_pemeriksaan` varchar(1000) DEFAULT NULL,
            `keperluan` varchar(250) DEFAULT NULL,
            `dokter` varchar(100) DEFAULT NULL,
            `petugas` varchar(100) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
    },
    'uninstall'     =>  function() use($core)
    {
    }
];
