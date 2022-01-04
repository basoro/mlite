<?php

    return [
        'name'          =>  'Kasir Rawat Jalan',
        'description'   =>  'Modul kasir rawat jalan untuk KhanzaLITE',
        'author'        =>  'Basoro',
        'version'       =>  '1.0',
        'compatibility' =>  '2022',
        'icon'          =>  'money',
        'install'       =>  function () use ($core) {

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `tambahan_biaya` (
            `no_rawat` varchar(17) NOT NULL,
            `nama_biaya` varchar(60) NOT NULL,
            `besar_biaya` double NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

          $core->db()->pdo()->exec("ALTER TABLE `tambahan_biaya`
            ADD PRIMARY KEY (`no_rawat`,`nama_biaya`);");

          $core->db()->pdo()->exec("ALTER TABLE `tambahan_biaya`
            ADD CONSTRAINT `tambahan_biaya_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE;");

          $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_billing` (
            `id_billing` int(11) NOT NULL,
            `kd_billing` varchar(100) NOT NULL,
            `no_rawat` varchar(17) NOT NULL,
            `jumlah_total` int(100) NOT NULL,
            `potongan` int(100) NOT NULL,
            `jumlah_harus_bayar` int(100) NOT NULL,
            `jumlah_bayar` int(100) NOT NULL,
            `tgl_billing` date NOT NULL,
            `jam_billing` time NOT NULL,
            `id_user` int(11) NOT NULL,
            `keterangan` varchar(100) NOT NULL
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");


          $core->db()->pdo()->exec("ALTER TABLE `mlite_billing`
            ADD PRIMARY KEY (`id_billing`);");

          $core->db()->pdo()->exec("ALTER TABLE `mlite_billing`
            MODIFY `id_billing` int(11) NOT NULL AUTO_INCREMENT;");

          if (!is_dir(UPLOADS."/invoices")) {
              mkdir(UPLOADS."/invoices", 0777);
          }

        },
        'uninstall'     =>  function() use($core)
        {
        }
    ];
