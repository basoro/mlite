SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `bridging_sep` MODIFY COLUMN `noskdp` varchar(40) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `nmkec`;

ALTER TABLE `dokter` MODIFY COLUMN `stts_nikah` enum('BELUM MENIKAH','MENIKAH','JANDA','DUDHA','JOMBLO') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL AFTER `no_telp`;

ALTER TABLE `jns_perawatan_lab` ADD COLUMN `kategori` enum('PK','PA') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `kelas`;

ALTER TABLE `metode_racik` ADD PRIMARY KEY (`kd_racik`) USING BTREE;

ALTER TABLE `pegawai` MODIFY COLUMN `wajibmasuk` tinyint(2) NOT NULL AFTER `stts_aktif`;

ALTER TABLE `pemeriksaan_ralan` ADD COLUMN `instruksi` varchar(400) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `penilaian`;

ALTER TABLE `pemeriksaan_ralan` ADD COLUMN `nip` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `instruksi`;

ALTER TABLE `pemeriksaan_ralan` ADD CONSTRAINT `pemeriksaan_ralan_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `pegawai` (`nik`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pemeriksaan_ralan` ADD INDEX `nip`(`nip`) USING BTREE;

ALTER TABLE `pemeriksaan_ranap` ADD COLUMN `kesadaran` enum('Compos Mentis','Somnolence','Sopor','Coma') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `gcs`;

ALTER TABLE `pemeriksaan_ranap` ADD COLUMN `instruksi` varchar(400) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `rtl`;

ALTER TABLE `pemeriksaan_ranap` ADD COLUMN `nip` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `instruksi`;

ALTER TABLE `pemeriksaan_ranap` MODIFY COLUMN `tensi` char(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `suhu_tubuh`;

ALTER TABLE `pemeriksaan_ranap` ADD CONSTRAINT `pemeriksaan_ranap_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `pegawai` (`nik`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pemeriksaan_ranap` ADD INDEX `nip`(`nip`) USING BTREE;

ALTER TABLE `petugas` MODIFY COLUMN `stts_nikah` enum('BELUM MENIKAH','MENIKAH','JANDA','DUDHA','JOMBLO') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL AFTER `agama`;

ALTER TABLE `rawat_jl_dr` MODIFY COLUMN `stts_bayar` enum('Sudah','Belum','Suspen') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL AFTER `biaya_rawat`;

ALTER TABLE `rawat_jl_drpr` MODIFY COLUMN `stts_bayar` enum('Sudah','Belum','Suspen') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL AFTER `biaya_rawat`;

ALTER TABLE `rawat_jl_pr` MODIFY COLUMN `stts_bayar` enum('Sudah','Belum','Suspen') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL AFTER `biaya_rawat`;

SET FOREIGN_KEY_CHECKS=1;
