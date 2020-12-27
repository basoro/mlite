<?php

    return [
        'name'          =>  'Kasir Rawat Jalan',
        'description'   =>  'Modul kasir rawat jalan untuk mLITE',
        'author'        =>  'Basoro',
        'version'       =>  '1.0',
        'compatibility' =>  '1.0.*',
        'icon'          =>  'user-plus',
        'install'       =>  function () use ($core) {

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

        },
        'uninstall'     =>  function() use($core)
        {
        }
    ];
