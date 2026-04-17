<?php

return [
    'name'          =>  'Farmasi',
    'description'   =>  'Pengelolaan data gudang farmasi.',
    'author'        =>  'Basoro',
    'category'      =>  'farmasi', 
    'version'       =>  '1.1',
    'compatibility' =>  '6.*.*',
    'icon'          =>  'medkit',
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'deporalan', '-')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'igd', '-')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'deporanap', '-')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'gudang', '-')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'keterangan_etiket', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'embalase', '')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'tuslah', '')");
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_farmasi_pengajuan_obat` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `no_pengajuan` varchar(30) NOT NULL,
          `tanggal_pengajuan` date NOT NULL,
          `kode_brng` varchar(15) NOT NULL,
          `jumlah` int(11) NOT NULL DEFAULT 0,
          `status` varchar(20) NOT NULL DEFAULT 'Menunggu',
          `catatan` text,
          `dibuat_oleh` varchar(100) DEFAULT '-',
          `disetujui_oleh` varchar(100) DEFAULT NULL,
          `disetujui_at` datetime DEFAULT NULL,
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_no_pengajuan` (`no_pengajuan`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;");
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_farmasi_pemesanan_obat` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `no_pemesanan` varchar(30) NOT NULL,
          `no_pengajuan` varchar(30) NOT NULL,
          `pengajuan_id` int(11) NOT NULL,
          `kode_brng` varchar(15) NOT NULL,
          `tanggal_pemesanan` date NOT NULL,
          `supplier_kode` text,
          `supplier` varchar(255) NOT NULL,
          `jumlah_pengajuan` int(11) NOT NULL DEFAULT 0,
          `jumlah_pesan` int(11) NOT NULL DEFAULT 0,
          `status_pemesanan` varchar(20) NOT NULL DEFAULT 'Draft',
          `catatan` text,
          `dibuat_oleh` varchar(100) DEFAULT '-',
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_no_pemesanan` (`no_pemesanan`),
          KEY `idx_no_pengajuan_pemesanan` (`no_pengajuan`),
          KEY `idx_pengajuan_id` (`pengajuan_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;");
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_farmasi_penerimaan_obat` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `pemesanan_id` int(11) NOT NULL,
          `tanggal_penerimaan` date NOT NULL,
          `jumlah_terima` int(11) NOT NULL DEFAULT 0,
          `jenis_pembayaran` varchar(10) NOT NULL DEFAULT 'Cash',
          `tanggal_jatuh_tempo` date DEFAULT NULL,
          `nomor_faktur` varchar(100) DEFAULT NULL,
          `catatan` text,
          `dibuat_oleh` varchar(100) DEFAULT '-',
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_pemesanan_id` (`pemesanan_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;");
    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'farmasi'");
    }
];
