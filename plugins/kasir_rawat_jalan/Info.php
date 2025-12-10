<?php

return [
    'name'          =>  'Kasir Rawat Jalan',
    'description'   =>  'Modul kasir rawat jalan untuk mLITE',
    'author'        =>  'Basoro',
    'category'      =>  'keuangan', 
    'version'       =>  '1.0',
    'compatibility' =>  '5.*.*',
    'icon'          =>  'money',
    'install'       =>  function () use ($core) {
      if (!is_dir(UPLOADS."/invoices")) {
          mkdir(UPLOADS."/invoices", 0777);
      }
      $sql = "CREATE TABLE IF NOT EXISTS mlite_kasir_shift (
        id_shift INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(64) NOT NULL,
        waktu_buka DATETIME NOT NULL,
        waktu_tutup DATETIME NULL,
        kas_awal DECIMAL(14,2) DEFAULT 0,
        kas_akhir DECIMAL(14,2) DEFAULT 0,
        total_transaksi DECIMAL(14,2) DEFAULT 0,
        selisih DECIMAL(14,2) DEFAULT 0,
        keterangan VARCHAR(255) DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
      $core->db()->pdo()->exec($sql);
    },
    'uninstall'     =>  function() use($core)
    {
    }
];
