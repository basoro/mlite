<?php

if (!defined("UPGRADABLE")) {
    exit();
}

function rrmdir($dir)
{
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        if (is_dir("$dir/$file")) {
            rrmdir("$dir/$file");
        } else {
            unlink("$dir/$file");
        }
    }
    return rmdir($dir);
}

switch ($version) {
    case '4.0.0':
        $return = '4.0.1';
    case '4.0.1':
        $return = '4.0.2';
    case '4.0.2':
        $return = '4.0.3';
    case '4.0.3':
        $return = '4.0.4';
    case '4.0.4':
        $return = '4.0.5';
    case '4.0.5':
        $return = '4.0.6';
    case '4.0.6':
        $return = '4.0.7';
   case '4.0.7':
        $return = '4.0.8';
    case '4.0.8':
        $return = '4.0.9';
    case '4.0.9':
        $return = '4.1.0';
    case '4.1.0':
        $return = '4.1.1';
    case '4.1.1':
        $return = '4.1.2';
    case '4.1.2':
        $return = '4.1.3'; 
    case '4.1.3':
        $return = '4.1.4'; 
    case '4.1.4':
        $return = '4.1.5';        
    case '4.1.5':
        $this->core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('settings', 'websocket', 'tidak')");
        $this->core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('settings', 'websocket_proxy', '')");
        $this->core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('settings', 'username_fp', '')");
        $this->core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('settings', 'password_fp', '')");
        $this->core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('settings', 'username_frista', '')");
        $this->core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('settings', 'password_frista', '')");
        $return = '4.1.6';        
    case '4.1.6':
        $this->core->db()->pdo()->exec("ALTER TABLE `mlite_settings` CHANGE `module` `module` VARCHAR(100) NOT NULL");
        $this->core->db()->pdo()->exec("ALTER TABLE `mlite_settings` CHANGE `field` `field` VARCHAR(100) NOT NULL");
        $this->core->db()->pdo()->exec("ALTER TABLE `mlite_settings` CHANGE `value` `value` VARCHAR(1000) NOT NULL");
        $this->core->db()->pdo()->exec("ALTER TABLE `mlite_settings` ADD UNIQUE KEY `module` (`module`,`field`)");
        $this->core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('farmasi', 'keterangan_etiket', '')");
        $this->core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_surat_rujukan` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nomor_surat` varchar(100) DEFAULT NULL,
            `no_rawat` varchar(100) DEFAULT NULL,
            `no_rkm_medis` varchar(100) DEFAULT NULL,
            `nm_pasien` varchar(100) DEFAULT NULL,
            `tgl_lahir` varchar(100) DEFAULT NULL,
            `umur` varchar(100) DEFAULT NULL,
            `jk` varchar(100) DEFAULT NULL,
            `alamat` varchar(1000) DEFAULT NULL,
            `kepada` varchar(250) DEFAULT NULL,
            `di` varchar(250) DEFAULT NULL,
            `anamnesa` varchar(100) DEFAULT NULL,
            `pemeriksaan_fisik` varchar(100) DEFAULT NULL,
            `pemeriksaan_penunjang` varchar(100) DEFAULT NULL,
            `diagnosa` varchar(100) DEFAULT NULL,
            `terapi` varchar(100) DEFAULT NULL,
            `alasan_dirujuk` varchar(250) DEFAULT NULL,
            `dokter` varchar(100) DEFAULT NULL,
            `petugas` varchar(100) DEFAULT NULL, 
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
        $this->core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_surat_sakit` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nomor_surat` varchar(100) DEFAULT NULL,
            `no_rawat` varchar(100) DEFAULT NULL,
            `no_rkm_medis` varchar(100) DEFAULT NULL,
            `nm_pasien` varchar(100) DEFAULT NULL,
            `tgl_lahir` varchar(100) DEFAULT NULL,
            `umur` varchar(100) DEFAULT NULL,
            `jk` varchar(100) DEFAULT NULL,
            `alamat` varchar(1000) DEFAULT NULL,
            `keadaan` varchar(100) DEFAULT NULL,
            `diagnosa` varchar(100) DEFAULT NULL,
            `lama_angka` varchar(100) DEFAULT NULL,
            `lama_huruf` varchar(100) DEFAULT NULL,
            `tanggal_mulai` varchar(100) DEFAULT NULL,
            `tanggal_selesai` varchar(100) DEFAULT NULL,
            `dokter` varchar(100) DEFAULT NULL,
            `petugas` varchar(100) DEFAULT NULL, 
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
        $this->core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_surat_sehat` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nomor_surat` varchar(100) DEFAULT NULL,
            `no_rawat` varchar(100) DEFAULT NULL,
            `no_rkm_medis` varchar(100) DEFAULT NULL,
            `nm_pasien` varchar(100) DEFAULT NULL,
            `tgl_lahir` varchar(100) DEFAULT NULL,
            `umur` varchar(100) DEFAULT NULL,
            `jk` varchar(100) DEFAULT NULL,
            `alamat` varchar(1000) DEFAULT NULL,
            `tanggal` varchar(100) DEFAULT NULL,
            `berat_badan` varchar(100) DEFAULT NULL,
            `tinggi_badan` varchar(100) DEFAULT NULL,
            `tensi` varchar(100) DEFAULT NULL,
            `gol_darah` varchar(100) DEFAULT NULL,
            `riwayat_penyakit` varchar(100) DEFAULT NULL,
            `keperluan` varchar(100) DEFAULT NULL,
            `dokter` varchar(100) DEFAULT NULL,
            `petugas` varchar(100) DEFAULT NULL, 
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");        
        $return = '4.1.7';        
    }
return $return;
