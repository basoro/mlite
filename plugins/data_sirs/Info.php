<?php

return [
    'name'          =>  'Data SIRS Online',
    'description'   =>  'Data laporan-laporan untuk SIRS Online Kementerian Kesehatan',
    'author'        =>  'Basoro',
    'version'       =>  '2.0',
    'compatibility' =>  '2022',
    'icon'          =>  'globe',
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `bridging_covid` (
                `id` varchar(15) NOT NULL,
                `no_rawat` varchar(17) NOT NULL,
                `no_passport` varchar(10) DEFAULT NULL,
                `inisial` varchar(10) NOT NULL,
                `tgl_onset` date NOT NULL,
                `warga` varchar(5) NOT NULL,
                `asal_pasien` varchar(2) NOT NULL,
                `jenis_pasien` varchar(2) NOT NULL,
                `status_pasien` varchar(2) NOT NULL,
                `status_rawat` varchar(3) NOT NULL,
                `pekerjaan` varchar(2) NOT NULL,
                `kelompok_gejala` varchar(2) NOT NULL,
                `varian_covid` varchar(3) NOT NULL,
                `alat_oksigen` varchar(3) DEFAULT NULL,
                `penyintas` varchar(2) NOT NULL,
                `status_co` varchar(2) NOT NULL,
                `demam` varchar(2) NOT NULL,
                `batuk` varchar(2) NOT NULL,
                `pilek` varchar(2) NOT NULL,
                `tenggorokan` varchar(2) NOT NULL,
                `sesak` varchar(2) NOT NULL,
                `lemas` varchar(2) NOT NULL,
                `nyeri` varchar(2) NOT NULL,
                `mual` varchar(2) NOT NULL,
                `diare` varchar(2) NOT NULL,
                `anosmia` varchar(2) NOT NULL,
                `nafas_cepat` varchar(2) NOT NULL,
                `lainnya` varchar(2) NOT NULL,
                `distres` varchar(2) NOT NULL,
                `frekuensi` varchar(2) NOT NULL
              ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        $core->db()->pdo()->exec("ALTER TABLE `bridging_covid`
        ADD PRIMARY KEY (`id`),
        ADD UNIQUE KEY `no_rawat` (`no_rawat`);");

        $core->db()->pdo()->exec("ALTER TABLE `bridging_covid`
        ADD CONSTRAINT `reg_periksa` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`);");

        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('sirs_online', 'email', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('sirs_online', 'password_v3', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('sirs_online', 'url_v3', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('sirs_online', 'url', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('sirs_online', 'id_sirs', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('sirs_online', 'password', '')");
    },
    'uninstall'     =>  function () use ($core) {
    }
];
