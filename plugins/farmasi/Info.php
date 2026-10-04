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
        // Idempoten: setting yang sudah ada dibiarkan (tidak menimpa konfigurasi);
        // yang belum ada disisipkan. Aman untuk MySQL dan SQLite.
        $settings = [
            'deporalan' => '-',
            'igd' => '-',
            'deporanap' => '-',
            'gudang' => '-',
            'keterangan_etiket' => '',
            'embalase' => '',
            'tuslah' => '',
            'pajak_obat_persen' => '',
        ];
        foreach ($settings as $field => $value) {
            $cek = $core->db('mlite_settings')->where('module', 'farmasi')->where('field', $field)->oneArray();
            if (!$cek) {
                $core->db('mlite_settings')->save(['module' => 'farmasi', 'field' => $field, 'value' => $value]);
            }
        }
        if (defined('DBDRIVER') && DBDRIVER === 'sqlite') {
            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS mlite_farmasi_pengajuan_obat (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            no_pengajuan TEXT NOT NULL,
            tanggal_pengajuan TEXT NOT NULL,
            kode_brng TEXT NOT NULL,
            jumlah INTEGER NOT NULL DEFAULT 0,
            status TEXT NOT NULL DEFAULT 'Menunggu',
            catatan TEXT,
            dibuat_oleh TEXT DEFAULT '-',
            disetujui_oleh TEXT,
            disetujui_at TEXT,
            created_at TEXT NOT NULL
        )");
            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS mlite_farmasi_pemesanan_obat (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            no_pemesanan TEXT NOT NULL,
            no_pengajuan TEXT NOT NULL,
            pengajuan_id INTEGER NOT NULL,
            kode_brng TEXT NOT NULL,
            tanggal_pemesanan TEXT NOT NULL,
            supplier_kode TEXT,
            supplier TEXT NOT NULL,
            jumlah_pengajuan INTEGER NOT NULL DEFAULT 0,
            jumlah_pesan INTEGER NOT NULL DEFAULT 0,
            status_pemesanan TEXT NOT NULL DEFAULT 'Draft',
            catatan TEXT,
            dibuat_oleh TEXT DEFAULT '-',
            created_at TEXT NOT NULL
        )");
            $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS mlite_farmasi_penerimaan_obat (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            pemesanan_id INTEGER NOT NULL,
            tanggal_penerimaan TEXT NOT NULL,
            jumlah_terima INTEGER NOT NULL DEFAULT 0,
            jenis_pembayaran TEXT NOT NULL DEFAULT 'Cash',
            tanggal_jatuh_tempo TEXT,
            nomor_faktur TEXT,
            catatan TEXT,
            dibuat_oleh TEXT DEFAULT '-',
            created_at TEXT NOT NULL
        )");
        } else {
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
        }
    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'farmasi'");
    }
];
