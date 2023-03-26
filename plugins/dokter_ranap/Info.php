<?php

return [
    'name'          =>  'Dokter Ranap',
    'description'   =>  'Modul dokter rawat inap untuk mLITE',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2023',
    'icon'          =>  'user-md',
    'install'       =>  function () use ($core) {
      $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `resume_pasien_ranap` (
        `no_rawat` varchar(17) NOT NULL,
        `kd_dokter` varchar(20) NOT NULL,
        `diagnosa_awal` varchar(100) NOT NULL,
        `alasan` varchar(100) NOT NULL,
        `keluhan_utama` text NOT NULL,
        `pemeriksaan_fisik` text NOT NULL,
        `jalannya_penyakit` text NOT NULL,
        `pemeriksaan_penunjang` text NOT NULL,
        `hasil_laborat` text NOT NULL,
        `tindakan_dan_operasi` text NOT NULL,
        `obat_di_rs` text NOT NULL,
        `diagnosa_utama` varchar(80) NOT NULL,
        `kd_diagnosa_utama` varchar(10) NOT NULL,
        `diagnosa_sekunder` varchar(80) NOT NULL,
        `kd_diagnosa_sekunder` varchar(10) NOT NULL,
        `diagnosa_sekunder2` varchar(80) NOT NULL,
        `kd_diagnosa_sekunder2` varchar(10) NOT NULL,
        `diagnosa_sekunder3` varchar(80) NOT NULL,
        `kd_diagnosa_sekunder3` varchar(10) NOT NULL,
        `diagnosa_sekunder4` varchar(80) NOT NULL,
        `kd_diagnosa_sekunder4` varchar(10) NOT NULL,
        `prosedur_utama` varchar(80) NOT NULL,
        `kd_prosedur_utama` varchar(8) NOT NULL,
        `prosedur_sekunder` varchar(80) NOT NULL,
        `kd_prosedur_sekunder` varchar(8) NOT NULL,
        `prosedur_sekunder2` varchar(80) NOT NULL,
        `kd_prosedur_sekunder2` varchar(8) NOT NULL,
        `prosedur_sekunder3` varchar(80) NOT NULL,
        `kd_prosedur_sekunder3` varchar(8) NOT NULL,
        `alergi` varchar(100) NOT NULL,
        `diet` text NOT NULL,
        `lab_belum` text NOT NULL,
        `edukasi` text NOT NULL,
        `cara_keluar` enum('Atas Izin Dokter','Pindah RS','Pulang Atas Permintaan Sendiri','Lainnya') NOT NULL,
        `ket_keluar` varchar(50) DEFAULT NULL,
        `keadaan` enum('Membaik','Sembuh','Keadaan Khusus','Meninggal') NOT NULL,
        `ket_keadaan` varchar(50) DEFAULT NULL,
        `dilanjutkan` enum('Kembali Ke RS','RS Lain','Dokter Luar','Puskesmes','Lainnya') NOT NULL,
        `ket_dilanjutkan` varchar(50) DEFAULT NULL,
        `kontrol` datetime DEFAULT NULL,
        `obat_pulang` text NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;");

      $core->mysql()->pdo()->exec("ALTER TABLE `resume_pasien_ranap`
        ADD PRIMARY KEY (`no_rawat`),
        ADD KEY `kd_dokter` (`kd_dokter`);");

      $core->mysql()->pdo()->exec("ALTER TABLE `resume_pasien_ranap`
        ADD CONSTRAINT `resume_pasien_ranap_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `resume_pasien_ranap_ibfk_2` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE;");

      $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `resep_pulang` (
        `no_rawat` varchar(17) NOT NULL,
        `kode_brng` varchar(15) NOT NULL,
        `jml_barang` double NOT NULL,
        `harga` double NOT NULL,
        `total` double NOT NULL,
        `dosis` varchar(150) NOT NULL,
        `tanggal` date NOT NULL,
        `jam` time NOT NULL,
        `kd_bangsal` varchar(5) NOT NULL,
        `no_batch` varchar(20) NOT NULL,
        `no_faktur` varchar(20) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

      $core->mysql()->pdo()->exec("ALTER TABLE `resep_pulang`
        ADD PRIMARY KEY (`no_rawat`,`kode_brng`,`tanggal`,`jam`,`no_batch`,`no_faktur`),
        ADD KEY `kode_brng` (`kode_brng`),
        ADD KEY `kd_bangsal` (`kd_bangsal`),
        ADD KEY `no_rawat` (`no_rawat`);");

      $core->mysql()->pdo()->exec("ALTER TABLE `resep_pulang`
        ADD CONSTRAINT `resep_pulang_ibfk_2` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `resep_pulang_ibfk_3` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
        ADD CONSTRAINT `resep_pulang_ibfk_4` FOREIGN KEY (`kd_bangsal`) REFERENCES `bangsal` (`kd_bangsal`) ON DELETE CASCADE ON UPDATE CASCADE;");

    },
    'uninstall'     =>  function() use($core)
    {
    }
];
