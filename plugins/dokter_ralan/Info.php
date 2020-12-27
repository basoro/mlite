<?php

    return [
        'name'          =>  'Dokter Ralan',
        'description'   =>  'Modul dokter rawat jalan untuk mLITE',
        'author'        =>  'Basoro',
        'version'       =>  '1.0',
        'compatibility' =>  '2021',
        'icon'          =>  'user-md',
        'install'       =>  function () use ($core) {

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `jadwal` (
            `kd_dokter` varchar(20) NOT NULL,
            `hari_kerja` enum('SENIN','SELASA','RABU','KAMIS','JUMAT','SABTU','AKHAD') NOT NULL DEFAULT 'SENIN',
            `jam_mulai` time NOT NULL DEFAULT '00:00:00',
            `jam_selesai` time DEFAULT NULL,
            `kd_poli` char(5) DEFAULT NULL,
            `kuota` int(11) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `jadwal`
            ADD PRIMARY KEY (`kd_dokter`,`hari_kerja`,`jam_mulai`),
            ADD KEY `kd_dokter` (`kd_dokter`),
            ADD KEY `kd_poli` (`kd_poli`),
            ADD KEY `jam_mulai` (`jam_mulai`),
            ADD KEY `jam_selesai` (`jam_selesai`);");

          $core->db()->pdo()->exec("ALTER TABLE `jadwal`
            ADD CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`kd_poli`) REFERENCES `poliklinik` (`kd_poli`) ON UPDATE CASCADE;");

        },
        'uninstall'     =>  function() use($core)
        {
        }
    ];
