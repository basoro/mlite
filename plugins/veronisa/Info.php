<?php

return [
    'name'          =>  'Veronisa',
    'description'   =>  'Modul Verifikasi Obat Kronis',
    'author'        =>  'Basoro',
    'category'      =>  'bridging', 
    'version'       =>  '1.0',
    'compatibility' =>  '5.*.*',
    'icon'          =>  'medkit',
    'install'       =>  function () use ($core) {

      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('veronisa', 'username', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('veronisa', 'password', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('veronisa', 'obat_kronis', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('veronisa', 'cons_id', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('veronisa', 'kode_ppk', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('veronisa', 'user_key', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('veronisa', 'secret_key', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('veronisa', 'bpjs_api_url', '')");
      
      // Tabel untuk log apotek online
      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_apotek_online_maping_obat` (
        `kode_brng` varchar(40) NOT NULL,
        `kd_obat_bpjs` varchar(20) NOT NULL,
        `nama_obat_bpjs` varchar(200) NOT NULL,
        PRIMARY KEY (`kode_brng`),
        KEY `kd_obat_bpjs` (`kd_obat_bpjs`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

      // Tabel untuk log apotek online
      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_apotek_online_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `no_rawat` varchar(17) NOT NULL,
        `noresep` varchar(50) DEFAULT NULL,
        `tanggal_kirim` datetime NOT NULL,
        `status` enum('success','error') NOT NULL,
        `response_resep` text DEFAULT NULL,
        `response_obat` text DEFAULT NULL,
        `request` text DEFAULT NULL,
        `user` varchar(50) DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `no_rawat` (`no_rawat`),
        KEY `tanggal_kirim` (`tanggal_kirim`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
      
      // Tabel untuk menyimpan data SEP
      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_apotek_online_sep_data` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `no_sep` varchar(50) NOT NULL,
        `faskes_asal_resep` varchar(20) DEFAULT NULL,
        `nm_faskes_asal_resep` varchar(100) DEFAULT NULL,
        `no_kartu` varchar(20) DEFAULT NULL,
        `nama_peserta` varchar(100) DEFAULT NULL,
        `jns_kelamin` char(1) DEFAULT NULL,
        `tgl_lahir` date DEFAULT NULL,
        `pisat` varchar(10) DEFAULT NULL,
        `kd_jenis_peserta` varchar(10) DEFAULT NULL,
        `nm_jenis_peserta` varchar(50) DEFAULT NULL,
        `kode_bu` varchar(20) DEFAULT NULL,
        `nama_bu` varchar(50) DEFAULT NULL,
        `tgl_sep` date DEFAULT NULL,
        `tgl_plg_sep` date DEFAULT NULL,
        `jns_pelayanan` varchar(10) DEFAULT NULL,
        `nm_diag` varchar(200) DEFAULT NULL,
        `poli` varchar(50) DEFAULT NULL,
        `flag_prb` char(1) DEFAULT NULL,
        `nama_prb` varchar(100) DEFAULT NULL,
        `kode_dokter` varchar(20) DEFAULT NULL,
        `nama_dokter` varchar(100) DEFAULT NULL,
        `tanggal_simpan` datetime NOT NULL,
        `user_simpan` varchar(50) DEFAULT NULL,
        `raw_response` text DEFAULT NULL,
        `no_rawat` varchar(17) DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `no_sep` (`no_sep`),
        KEY `no_kartu` (`no_kartu`),
        KEY `nama_peserta` (`nama_peserta`),
        KEY `tanggal_simpan` (`tanggal_simpan`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_apotek_online_resep_response_log` (
      `id` int NOT NULL AUTO_INCREMENT,
      `no_rawat` varchar(17) DEFAULT NULL,
      `no_sep_kunjungan` varchar(50) DEFAULT NULL,
      `no_kartu` varchar(20) DEFAULT NULL,
      `nama` varchar(100) DEFAULT NULL,
      `faskes_asal` varchar(20) DEFAULT NULL,
      `no_apotik` varchar(30) DEFAULT NULL,
      `no_resep` varchar(20) DEFAULT NULL,
      `tgl_resep` date DEFAULT NULL,
      `kd_jns_obat` varchar(5) DEFAULT NULL,
      `by_tag_rsp` varchar(10) DEFAULT NULL,
      `by_ver_rsp` varchar(10) DEFAULT NULL,
      `tgl_entry` date DEFAULT NULL,
      `meta_code` varchar(10) DEFAULT NULL,
      `meta_message` text,
      `raw_response` text,
      `tanggal_simpan` datetime DEFAULT CURRENT_TIMESTAMP,
      `user` varchar(50) DEFAULT NULL,
      PRIMARY KEY (`id`) USING BTREE,
      KEY `idx_no_rawat` (`no_rawat`) USING BTREE,
      KEY `idx_no_sep_kunjungan` (`no_sep_kunjungan`) USING BTREE,
      KEY `idx_no_resep` (`no_resep`) USING BTREE,
      KEY `idx_tanggal_simpan` (`tanggal_simpan`) USING BTREE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    },
    'uninstall'     =>  function() use($core)
    {
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'veronisa'");
      $core->db()->pdo()->exec("DROP TABLE IF EXISTS `mlite_apotek_online_maping_obat`");
      $core->db()->pdo()->exec("DROP TABLE IF EXISTS `mlite_apotek_online_log`");
      $core->db()->pdo()->exec("DROP TABLE IF EXISTS `mlite_apotek_online_sep_data`");
    }
];
