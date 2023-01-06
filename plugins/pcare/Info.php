<?php

return [
    'name'          =>  'Bridging PCare',
    'description'   =>  'Modul pcare api untuk mLITE',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '2023',
    'icon'          =>  'database',
    'install'       =>  function () use ($core) {

      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_bridging_pcare` (
        `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `no_rawat` TEXT NOT NULL,
        `no_rkm_medis` TEXT DEFAULT NULL,
        `tgl_daftar` TEXT DEFAULT NULL,
        `nomor_kunjungan` TEXT DEFAULT NULL,
        `kode_provider_peserta` TEXT DEFAULT NULL,
        `nomor_jaminan` TEXT DEFAULT NULL,
        `kode_poli` TEXT DEFAULT NULL,
        `nama_poli` TEXT DEFAULT NULL,
        `kunjungan_sakit` TEXT DEFAULT NULL,
        `sistole` TEXT DEFAULT NULL,
        `diastole` TEXT DEFAULT NULL,
        `nadi` TEXT DEFAULT NULL,
        `respirasi` TEXT DEFAULT NULL,
        `tinggi` TEXT DEFAULT NULL,
        `berat` TEXT DEFAULT NULL,
        `lingkar_perut` TEXT DEFAULT NULL,
        `rujuk_balik` TEXT DEFAULT NULL,
        `subyektif` TEXT DEFAULT NULL,
        `kode_tkp` TEXT DEFAULT NULL,
        `nomor_urut` TEXT DEFAULT NULL,
        `kode_kesadaran` TEXT DEFAULT NULL,
        `nama_kesadaran` TEXT DEFAULT NULL,
        `terapi` TEXT DEFAULT NULL,
        `kode_status_pulang` TEXT DEFAULT NULL,
        `nama_status_pulang` TEXT DEFAULT NULL,
        `tgl_pulang` TEXT DEFAULT NULL,
        `tgl_kunjungan` TEXT DEFAULT NULL,
        `kode_dokter` TEXT DEFAULT NULL,
        `nama_dokter` TEXT DEFAULT NULL,
        `kode_diagnosa1` TEXT DEFAULT NULL,
        `nama_diagnosa1` TEXT DEFAULT NULL,
        `kode_diagnosa2` TEXT DEFAULT NULL,
        `nama_diagnosa2` TEXT DEFAULT NULL,
        `kode_diagnosa3` TEXT DEFAULT NULL,
        `nama_diagnosa3` TEXT DEFAULT NULL,
        `tgl_estimasi_rujuk` TEXT DEFAULT NULL,
        `kode_ppk` TEXT DEFAULT NULL,
        `nama_ppk` TEXT DEFAULT NULL,
        `kode_spesialis` TEXT DEFAULT NULL,
        `nama_spesialis` TEXT DEFAULT NULL,
        `kode_subspesialis` TEXT DEFAULT NULL,
        `nama_subspesialis` TEXT DEFAULT NULL,
        `kode_sarana` TEXT DEFAULT NULL,
        `nama_sarana` TEXT DEFAULT NULL,
        `kode_referensikhusus` TEXT DEFAULT NULL,
        `nama_referensikhusus` TEXT DEFAULT NULL,
        `kode_faskeskhusus` TEXT DEFAULT NULL,
        `nama_faskeskhusus` TEXT DEFAULT NULL,
        `catatan` TEXT DEFAULT NULL,
        `kode_tacc` TEXT DEFAULT NULL,
        `nama_tacc` TEXT DEFAULT NULL,
        `alasan_tacc` TEXT DEFAULT NULL,
        `id_user` TEXT NOT NULL,
        `tgl_input` TEXT NOT NULL,
        `status_kirim` TEXT NOT NULL
      )");

      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'usernamePcare', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'passwordPcare', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'consumerID', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'consumerSecret', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'consumerUserKey', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'PCareApiUrl', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'kode_fktp', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'nama_fktp', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'wilayah', 'REGIONAL VIII - Balikpapan')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'cabang', 'BARABAI')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'kabupatenkota', 'Kab. Hulu Sungai Tengah')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('pcare', 'kode_kabupatenkota', '0287')");
    },
    'uninstall'     =>  function() use($core)
    {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'pcare'");
    }
];
