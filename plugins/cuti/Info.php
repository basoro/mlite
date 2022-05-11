<?php

    return [
        'name'          =>  'Cuti',
        'description'   =>  'Modul Cuti Pegawai untuk KhanzaLITE',
        'author'        =>  'Adit',
        'version'       =>  '1.0',
        'compatibility' =>  '2022',
        'icon'          =>  'calendar-check-o',
        'install'       =>  function () use ($core) {

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `pengajuan_cuti` (
            `no_pengajuan` varchar(17) NOT NULL,
            `tanggal` date NOT NULL,
            `tanggal_awal` date NOT NULL,
            `tanggal_akhir` date NOT NULL,
            `nik` varchar(20) NOT NULL,
            `urgensi` enum('Tahunan','Besar','Sakit','Bersalin','Alasan Penting','Keterangan Lainnya') NOT NULL,
            `alamat` varchar(100) NOT NULL,
            `jumlah` int(11) NOT NULL,
            `kepentingan` varchar(70) NOT NULL,
            `nik_pj` varchar(20) NOT NULL,
            `status` enum('Proses Pengajuan','Disetujui','Ditolak') NOT NULL,
            PRIMARY KEY (`no_pengajuan`) USING BTREE,
            KEY `nik` (`nik`) USING BTREE,
            KEY `nik_pj` (`nik_pj`) USING BTREE,
            CONSTRAINT `pengajuan_cuti_ibfk_1` FOREIGN KEY (`nik`) REFERENCES `pegawai` (`nik`) ON UPDATE CASCADE,
            CONSTRAINT `pengajuan_cuti_ibfk_2` FOREIGN KEY (`nik_pj`) REFERENCES `pegawai` (`nik`) ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        },
        'uninstall'     =>  function() use($core)
        {
        }
    ];
