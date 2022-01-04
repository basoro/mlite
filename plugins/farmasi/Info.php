<?php
return [
    'name'          =>  'Farmasi',
    'description'   =>  'Pengelolaan data gudang farmasi.',
    'author'        =>  'Basoro',
    'version'       =>  '1.1',
    'compatibility' =>  '2022',
    'icon'          =>  'medkit',

    'install'       =>  function () use ($core) {

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `opname` (
          `kode_brng` varchar(15) NOT NULL,
          `h_beli` double DEFAULT NULL,
          `tanggal` date NOT NULL,
          `stok` double NOT NULL,
          `real` double NOT NULL,
          `selisih` double NOT NULL,
          `nomihilang` double NOT NULL,
          `lebih` double NOT NULL,
          `nomilebih` double NOT NULL,
          `keterangan` varchar(60) NOT NULL,
          `kd_bangsal` char(5) NOT NULL,
          `no_batch` varchar(20) NOT NULL,
          `no_faktur` varchar(20) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        $core->db()->pdo()->exec("ALTER TABLE `opname`
          ADD PRIMARY KEY (`kode_brng`,`tanggal`,`kd_bangsal`,`no_batch`,`no_faktur`) USING BTREE,
          ADD KEY `kd_bangsal` (`kd_bangsal`) USING BTREE,
          ADD KEY `stok` (`stok`) USING BTREE,
          ADD KEY `real` (`real`) USING BTREE,
          ADD KEY `selisih` (`selisih`) USING BTREE,
          ADD KEY `nomihilang` (`nomihilang`) USING BTREE,
          ADD KEY `keterangan` (`keterangan`) USING BTREE,
          ADD KEY `kode_brng` (`kode_brng`) USING BTREE;");

        $core->db()->pdo()->exec("ALTER TABLE `opname`
          ADD CONSTRAINT `opname_ibfk_1` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE,
          ADD CONSTRAINT `opname_ibfk_2` FOREIGN KEY (`kd_bangsal`) REFERENCES `bangsal` (`kd_bangsal`) ON DELETE CASCADE ON UPDATE CASCADE;");

        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'deporalan', '-')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'igd', '-')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'deporanap', '-')");
        $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'gudang', '-')");
    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'farmasi'");
    }
];
