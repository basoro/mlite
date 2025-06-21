# ************************************************************
# Host: localhost (MySQL 5.7.44)
# Database: mlite
# Generation Time: 2024-12-01 05:02:39 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table aturan_pakai
# ------------------------------------------------------------

CREATE TABLE `aturan_pakai` (
  `tgl_perawatan` date NOT NULL DEFAULT '0000-00-00',
  `jam` time NOT NULL DEFAULT '00:00:00',
  `no_rawat` varchar(17) NOT NULL DEFAULT '',
  `kode_brng` varchar(15) NOT NULL DEFAULT '',
  `aturan` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`tgl_perawatan`,`jam`,`no_rawat`,`kode_brng`),
  KEY `no_rawat` (`no_rawat`),
  KEY `kode_brng` (`kode_brng`),
  CONSTRAINT `aturan_pakai_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `aturan_pakai_ibfk_2` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table bahasa_pasien
# ------------------------------------------------------------

CREATE TABLE `bahasa_pasien` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_bahasa` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `nama_bahasa` (`nama_bahasa`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

LOCK TABLES `bahasa_pasien` WRITE;
/*!40000 ALTER TABLE `bahasa_pasien` DISABLE KEYS */;

INSERT INTO `bahasa_pasien` (`id`, `nama_bahasa`)
VALUES
	(1,'-');

/*!40000 ALTER TABLE `bahasa_pasien` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table bangsal
# ------------------------------------------------------------

CREATE TABLE `bangsal` (
  `kd_bangsal` char(5) NOT NULL,
  `nm_bangsal` varchar(30) DEFAULT NULL,
  `status` enum('0','1') DEFAULT NULL,
  PRIMARY KEY (`kd_bangsal`),
  KEY `nm_bangsal` (`nm_bangsal`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `bangsal` WRITE;
/*!40000 ALTER TABLE `bangsal` DISABLE KEYS */;

INSERT INTO `bangsal` (`kd_bangsal`, `nm_bangsal`, `status`)
VALUES
	('-','-','1'),
	('ANG','Anggrek','1'),
	('APT','Apotek','1'),
	('GF','Gudang Farmasi','1');

/*!40000 ALTER TABLE `bangsal` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table bank
# ------------------------------------------------------------

CREATE TABLE `bank` (
  `namabank` varchar(50) NOT NULL,
  PRIMARY KEY (`namabank`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `bank` WRITE;
/*!40000 ALTER TABLE `bank` DISABLE KEYS */;

INSERT INTO `bank` (`namabank`)
VALUES
	('-'),
	('T');

/*!40000 ALTER TABLE `bank` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table barcode
# ------------------------------------------------------------

CREATE TABLE `barcode` (
  `id` int(11) NOT NULL,
  `barcode` varchar(25) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `barcode` (`barcode`),
  CONSTRAINT `barcode_ibfk_1` FOREIGN KEY (`id`) REFERENCES `pegawai` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table beri_obat_operasi
# ------------------------------------------------------------

CREATE TABLE `beri_obat_operasi` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `kd_obat` varchar(15) NOT NULL,
  `hargasatuan` double NOT NULL,
  `jumlah` double NOT NULL,
  KEY `no_rawat` (`no_rawat`),
  KEY `kd_obat` (`kd_obat`),
  KEY `tanggal` (`tanggal`),
  KEY `hargasatuan` (`hargasatuan`),
  KEY `jumlah` (`jumlah`),
  CONSTRAINT `beri_obat_operasi_ibfk_2` FOREIGN KEY (`kd_obat`) REFERENCES `obatbhp_ok` (`kd_obat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `beri_obat_operasi_ibfk_3` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table berkas_digital_perawatan
# ------------------------------------------------------------

CREATE TABLE `berkas_digital_perawatan` (
  `no_rawat` varchar(17) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `lokasi_file` varchar(600) NOT NULL,
  PRIMARY KEY (`no_rawat`,`kode`,`lokasi_file`) USING BTREE,
  KEY `kode` (`kode`),
  CONSTRAINT `berkas_digital_perawatan_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `berkas_digital_perawatan_ibfk_2` FOREIGN KEY (`kode`) REFERENCES `master_berkas_digital` (`kode`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table bidang
# ------------------------------------------------------------

CREATE TABLE `bidang` (
  `nama` varchar(15) NOT NULL,
  PRIMARY KEY (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `bidang` WRITE;
/*!40000 ALTER TABLE `bidang` DISABLE KEYS */;

INSERT INTO `bidang` (`nama`)
VALUES
	('-');

/*!40000 ALTER TABLE `bidang` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table booking_operasi
# ------------------------------------------------------------

CREATE TABLE `booking_operasi` (
  `no_rawat` varchar(17) DEFAULT NULL,
  `kode_paket` varchar(15) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `status` enum('Menunggu','Proses Operasi','Selesai') DEFAULT NULL,
  `kd_dokter` varchar(20) DEFAULT NULL,
  `kd_ruang_ok` varchar(3) NOT NULL,
  KEY `no_rawat` (`no_rawat`),
  KEY `kode_paket` (`kode_paket`),
  KEY `kd_dokter` (`kd_dokter`),
  KEY `kd_ruang_ok` (`kd_ruang_ok`),
  CONSTRAINT `booking_operasi_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
  CONSTRAINT `booking_operasi_ibfk_2` FOREIGN KEY (`kode_paket`) REFERENCES `paket_operasi` (`kode_paket`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `booking_operasi_ibfk_3` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `booking_operasi_ibfk_4` FOREIGN KEY (`kd_ruang_ok`) REFERENCES `ruang_ok` (`kd_ruang_ok`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table booking_periksa
# ------------------------------------------------------------

CREATE TABLE `booking_periksa` (
  `no_booking` varchar(17) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `nama` varchar(40) DEFAULT NULL,
  `alamat` varchar(200) DEFAULT NULL,
  `no_telp` varchar(40) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `kd_poli` varchar(5) DEFAULT NULL,
  `tambahan_pesan` varchar(400) DEFAULT NULL,
  `status` enum('Diterima','Ditolak','Belum Dibalas') NOT NULL,
  `tanggal_booking` datetime NOT NULL,
  PRIMARY KEY (`no_booking`),
  UNIQUE KEY `tanggal` (`tanggal`,`no_telp`),
  KEY `kd_poli` (`kd_poli`),
  CONSTRAINT `booking_periksa_ibfk_1` FOREIGN KEY (`kd_poli`) REFERENCES `poliklinik` (`kd_poli`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table booking_periksa_balasan
# ------------------------------------------------------------

CREATE TABLE `booking_periksa_balasan` (
  `no_booking` varchar(17) NOT NULL,
  `balasan` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`no_booking`),
  CONSTRAINT `booking_periksa_balasan_ibfk_1` FOREIGN KEY (`no_booking`) REFERENCES `booking_periksa` (`no_booking`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table booking_periksa_diterima
# ------------------------------------------------------------

CREATE TABLE `booking_periksa_diterima` (
  `no_booking` varchar(17) NOT NULL,
  `no_rkm_medis` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`no_booking`),
  KEY `no_rkm_medis` (`no_rkm_medis`),
  CONSTRAINT `booking_periksa_diterima_ibfk_1` FOREIGN KEY (`no_booking`) REFERENCES `booking_periksa` (`no_booking`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `booking_periksa_diterima_ibfk_2` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table booking_registrasi
# ------------------------------------------------------------

CREATE TABLE `booking_registrasi` (
  `tanggal_booking` date DEFAULT NULL,
  `jam_booking` time DEFAULT NULL,
  `no_rkm_medis` varchar(15) NOT NULL,
  `tanggal_periksa` date NOT NULL,
  `kd_dokter` varchar(20) DEFAULT NULL,
  `kd_poli` varchar(5) DEFAULT NULL,
  `no_reg` varchar(8) DEFAULT NULL,
  `kd_pj` char(3) DEFAULT NULL,
  `limit_reg` int(1) DEFAULT NULL,
  `waktu_kunjungan` datetime DEFAULT NULL,
  `status` enum('Terdaftar','Belum','Batal','Dokter Berhalangan') DEFAULT NULL,
  PRIMARY KEY (`no_rkm_medis`,`tanggal_periksa`),
  KEY `kd_dokter` (`kd_dokter`),
  KEY `kd_poli` (`kd_poli`),
  KEY `no_rkm_medis` (`no_rkm_medis`),
  KEY `kd_pj` (`kd_pj`),
  CONSTRAINT `booking_registrasi_ibfk_1` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `booking_registrasi_ibfk_2` FOREIGN KEY (`kd_poli`) REFERENCES `poliklinik` (`kd_poli`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `booking_registrasi_ibfk_3` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `booking_registrasi_ibfk_4` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table bpjs_prb
# ------------------------------------------------------------

CREATE TABLE `bpjs_prb` (
  `no_sep` varchar(40) NOT NULL,
  `prb` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`no_sep`),
  CONSTRAINT `bpjs_prb_ibfk_1` FOREIGN KEY (`no_sep`) REFERENCES `bridging_sep` (`no_sep`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table bridging_rujukan_bpjs
# ------------------------------------------------------------

CREATE TABLE `bridging_rujukan_bpjs` (
  `no_sep` varchar(40) NOT NULL,
  `tglRujukan` date DEFAULT NULL,
  `tglRencanaKunjungan` date NOT NULL,
  `ppkDirujuk` varchar(20) DEFAULT NULL,
  `nm_ppkDirujuk` varchar(100) DEFAULT NULL,
  `jnsPelayanan` enum('1','2') DEFAULT NULL,
  `catatan` varchar(200) DEFAULT NULL,
  `diagRujukan` varchar(10) DEFAULT NULL,
  `nama_diagRujukan` varchar(400) DEFAULT NULL,
  `tipeRujukan` enum('0. Penuh','1. Partial','2. Rujuk Balik') DEFAULT NULL,
  `poliRujukan` varchar(15) DEFAULT NULL,
  `nama_poliRujukan` varchar(50) DEFAULT NULL,
  `no_rujukan` varchar(40) NOT NULL,
  `user` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`no_rujukan`),
  KEY `no_sep` (`no_sep`),
  CONSTRAINT `bridging_rujukan_bpjs_ibfk_1` FOREIGN KEY (`no_sep`) REFERENCES `bridging_sep` (`no_sep`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table bridging_sep
# ------------------------------------------------------------

CREATE TABLE `bridging_sep` (
  `no_sep` varchar(40) NOT NULL DEFAULT '',
  `no_rawat` varchar(17) DEFAULT NULL,
  `tglsep` date DEFAULT NULL,
  `tglrujukan` date DEFAULT NULL,
  `no_rujukan` varchar(40) DEFAULT NULL,
  `kdppkrujukan` varchar(12) DEFAULT NULL,
  `nmppkrujukan` varchar(200) DEFAULT NULL,
  `kdppkpelayanan` varchar(12) DEFAULT NULL,
  `nmppkpelayanan` varchar(200) DEFAULT NULL,
  `jnspelayanan` enum('1','2') DEFAULT NULL,
  `catatan` varchar(100) DEFAULT NULL,
  `diagawal` varchar(10) DEFAULT NULL,
  `nmdiagnosaawal` varchar(400) DEFAULT NULL,
  `kdpolitujuan` varchar(15) DEFAULT NULL,
  `nmpolitujuan` varchar(50) DEFAULT NULL,
  `klsrawat` enum('1','2','3') DEFAULT NULL,
  `klsnaik` enum('','1','2','3','4','5','6','7') NOT NULL,
  `pembiayaan` enum('','1','2','3') NOT NULL,
  `pjnaikkelas` varchar(100) NOT NULL,
  `lakalantas` enum('0','1','2','3') DEFAULT NULL,
  `user` varchar(25) DEFAULT NULL,
  `nomr` varchar(15) DEFAULT NULL,
  `nama_pasien` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `peserta` varchar(100) DEFAULT NULL,
  `jkel` enum('L','P') DEFAULT NULL,
  `no_kartu` varchar(25) DEFAULT NULL,
  `tglpulang` datetime DEFAULT NULL,
  `asal_rujukan` enum('1. Faskes 1','2. Faskes 2(RS)') NOT NULL,
  `eksekutif` enum('0. Tidak','1.Ya') NOT NULL,
  `cob` enum('0. Tidak','1.Ya') NOT NULL,
  `notelep` varchar(40) NOT NULL,
  `katarak` enum('0. Tidak','1.Ya') NOT NULL,
  `tglkkl` date NOT NULL,
  `keterangankkl` varchar(100) NOT NULL,
  `suplesi` enum('0. Tidak','1.Ya') NOT NULL,
  `no_sep_suplesi` varchar(40) NOT NULL,
  `kdprop` varchar(10) NOT NULL,
  `nmprop` varchar(50) NOT NULL,
  `kdkab` varchar(10) NOT NULL,
  `nmkab` varchar(50) NOT NULL,
  `kdkec` varchar(10) NOT NULL,
  `nmkec` varchar(50) NOT NULL,
  `noskdp` varchar(40) NOT NULL,
  `kddpjp` varchar(10) NOT NULL,
  `nmdpdjp` varchar(100) NOT NULL,
  `tujuankunjungan` enum('0','1','2') NOT NULL,
  `flagprosedur` enum('','0','1') NOT NULL,
  `penunjang` enum('','1','2','3','4','5','6','7','8','9','10','11','12') NOT NULL,
  `asesmenpelayanan` enum('','1','2','3','4','5') NOT NULL,
  `kddpjplayanan` varchar(10) NOT NULL,
  `nmdpjplayanan` varchar(100) NOT NULL,
  PRIMARY KEY (`no_sep`),
  KEY `no_rawat` (`no_rawat`),
  CONSTRAINT `bridging_sep_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table bridging_sep_internal
# ------------------------------------------------------------

CREATE TABLE `bridging_sep_internal` (
  `no_sep` varchar(40) NOT NULL DEFAULT '',
  `no_rawat` varchar(17) DEFAULT NULL,
  `tglsep` date DEFAULT NULL,
  `tglrujukan` date DEFAULT NULL,
  `no_rujukan` varchar(40) DEFAULT NULL,
  `kdppkrujukan` varchar(12) DEFAULT NULL,
  `nmppkrujukan` varchar(200) DEFAULT NULL,
  `kdppkpelayanan` varchar(12) DEFAULT NULL,
  `nmppkpelayanan` varchar(200) DEFAULT NULL,
  `jnspelayanan` enum('1','2') DEFAULT NULL,
  `catatan` varchar(100) DEFAULT NULL,
  `diagawal` varchar(10) DEFAULT NULL,
  `nmdiagnosaawal` varchar(400) DEFAULT NULL,
  `kdpolitujuan` varchar(15) DEFAULT NULL,
  `nmpolitujuan` varchar(50) DEFAULT NULL,
  `klsrawat` enum('1','2','3') DEFAULT NULL,
  `klsnaik` enum('','1','2','3','4','5','6','7') NOT NULL,
  `pembiayaan` enum('','1','2','3') NOT NULL,
  `pjnaikkelas` varchar(100) NOT NULL,
  `lakalantas` enum('0','1','2','3') DEFAULT NULL,
  `user` varchar(25) DEFAULT NULL,
  `nomr` varchar(15) DEFAULT NULL,
  `nama_pasien` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `peserta` varchar(100) DEFAULT NULL,
  `jkel` enum('L','P') DEFAULT NULL,
  `no_kartu` varchar(25) DEFAULT NULL,
  `tglpulang` datetime DEFAULT NULL,
  `asal_rujukan` enum('1. Faskes 1','2. Faskes 2(RS)') NOT NULL,
  `eksekutif` enum('0. Tidak','1.Ya') NOT NULL,
  `cob` enum('0. Tidak','1.Ya') NOT NULL,
  `notelep` varchar(40) NOT NULL,
  `katarak` enum('0. Tidak','1.Ya') NOT NULL,
  `tglkkl` date NOT NULL,
  `keterangankkl` varchar(100) NOT NULL,
  `suplesi` enum('0. Tidak','1.Ya') NOT NULL,
  `no_sep_suplesi` varchar(40) NOT NULL,
  `kdprop` varchar(10) NOT NULL,
  `nmprop` varchar(50) NOT NULL,
  `kdkab` varchar(10) NOT NULL,
  `nmkab` varchar(50) NOT NULL,
  `kdkec` varchar(10) NOT NULL,
  `nmkec` varchar(50) NOT NULL,
  `noskdp` varchar(40) NOT NULL,
  `kddpjp` varchar(10) NOT NULL,
  `nmdpdjp` varchar(100) NOT NULL,
  `tujuankunjungan` enum('0','1','2') NOT NULL,
  `flagprosedur` enum('','0','1') NOT NULL,
  `penunjang` enum('','1','2','3','4','5','6','7','8','9','10','11','12') NOT NULL,
  `asesmenpelayanan` enum('','1','2','3','4','5') NOT NULL,
  `kddpjplayanan` varchar(10) NOT NULL,
  `nmdpjplayanan` varchar(100) NOT NULL,
  KEY `no_rawat` (`no_rawat`),
  KEY `no_sep` (`no_sep`),
  CONSTRAINT `bridging_sep_internal_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bridging_sep_internal_ibfk_2` FOREIGN KEY (`no_sep`) REFERENCES `bridging_sep` (`no_sep`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table bridging_srb_bpjs
# ------------------------------------------------------------

CREATE TABLE `bridging_srb_bpjs` (
  `no_sep` varchar(40) NOT NULL,
  `no_srb` varchar(10) NOT NULL,
  `tgl_srb` date DEFAULT NULL,
  `alamat` varchar(200) DEFAULT NULL,
  `email` varchar(40) DEFAULT NULL,
  `kodeprogram` varchar(3) DEFAULT NULL,
  `namaprogram` varchar(70) DEFAULT NULL,
  `kodedpjp` varchar(10) DEFAULT NULL,
  `nmdpjp` varchar(100) DEFAULT NULL,
  `user` varchar(25) DEFAULT NULL,
  `keterangan` varchar(100) DEFAULT NULL,
  `saran` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`no_sep`,`no_srb`),
  CONSTRAINT `bridging_srb_bpjs_ibfk_1` FOREIGN KEY (`no_sep`) REFERENCES `bridging_sep` (`no_sep`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table bridging_surat_kontrol_bpjs
# ------------------------------------------------------------

CREATE TABLE `bridging_surat_kontrol_bpjs` (
  `no_sep` varchar(40) DEFAULT NULL,
  `tgl_surat` date NOT NULL,
  `no_surat` varchar(40) NOT NULL,
  `tgl_rencana` date DEFAULT NULL,
  `kd_dokter_bpjs` varchar(20) DEFAULT NULL,
  `nm_dokter_bpjs` varchar(50) DEFAULT NULL,
  `kd_poli_bpjs` varchar(15) DEFAULT NULL,
  `nm_poli_bpjs` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`no_surat`),
  KEY `bridging_surat_kontrol_bpjs_ibfk_1` (`no_sep`),
  CONSTRAINT `bridging_surat_kontrol_bpjs_ibfk_1` FOREIGN KEY (`no_sep`) REFERENCES `bridging_sep` (`no_sep`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table bridging_surat_pri_bpjs
# ------------------------------------------------------------

CREATE TABLE `bridging_surat_pri_bpjs` (
  `no_rawat` varchar(17) DEFAULT NULL,
  `no_kartu` varchar(25) DEFAULT NULL,
  `tgl_surat` date NOT NULL,
  `no_surat` varchar(40) NOT NULL,
  `tgl_rencana` date DEFAULT NULL,
  `kd_dokter_bpjs` varchar(20) DEFAULT NULL,
  `nm_dokter_bpjs` varchar(50) DEFAULT NULL,
  `kd_poli_bpjs` varchar(15) DEFAULT NULL,
  `nm_poli_bpjs` varchar(40) DEFAULT NULL,
  `diagnosa` varchar(130) NOT NULL,
  `no_sep` varchar(40) NOT NULL,
  PRIMARY KEY (`no_surat`),
  KEY `no_rawat` (`no_rawat`),
  CONSTRAINT `bridging_surat_pri_bpjs_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table cacat_fisik
# ------------------------------------------------------------

CREATE TABLE `cacat_fisik` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_cacat` varchar(30) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `nama_cacat` (`nama_cacat`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

LOCK TABLES `cacat_fisik` WRITE;
/*!40000 ALTER TABLE `cacat_fisik` DISABLE KEYS */;

INSERT INTO `cacat_fisik` (`id`, `nama_cacat`)
VALUES
	(1,'-');

/*!40000 ALTER TABLE `cacat_fisik` ENABLE KEYS */;
UNLOCK TABLES;

# Dump of table catatan_adime_gizi
# ------------------------------------------------------------

CREATE TABLE `catatan_adime_gizi` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `asesmen` varchar(1000) DEFAULT NULL,
  `diagnosis` varchar(1000) DEFAULT NULL,
  `intervensi` varchar(1000) DEFAULT NULL,
  `monitoring` varchar(1000) DEFAULT NULL,
  `evaluasi` varchar(1000) DEFAULT NULL,
  `instruksi` varchar(1000) DEFAULT NULL,
  `nip` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`no_rawat`,`tanggal`),
  KEY `nip` (`nip`),
  CONSTRAINT `catatan_adime_gizi_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `catatan_adime_gizi_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `mlite_satu_sehat_mapping_lab` (
  `id_template` int(11) NOT NULL,
  `code` varchar(15) DEFAULT NULL,
  `system` varchar(100) NOT NULL,
  `display` varchar(80) DEFAULT NULL,
  `sampel_code` varchar(15) NOT NULL,
  `sampel_system` varchar(100) NOT NULL,
  `sampel_display` varchar(80) NOT NULL,
  PRIMARY KEY (`id_template`),
  CONSTRAINT `mlite_satu_sehat_mapping_lab_ibfk_1` FOREIGN KEY (`id_template`) REFERENCES `template_laboratorium` (`id_template`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `mlite_satu_sehat_mapping_rad` (
  `kd_jenis_prw` varchar(15) NOT NULL,
  `code` varchar(15) DEFAULT NULL,
  `system` varchar(100) NOT NULL,
  `display` varchar(80) DEFAULT NULL,
  `sampel_code` varchar(15) NOT NULL,
  `sampel_system` varchar(100) NOT NULL,
  `sampel_display` varchar(80) NOT NULL,
  PRIMARY KEY (`kd_jenis_prw`),
  CONSTRAINT `mlite_satu_sehat_mapping_rad_ibfk_1` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_radiologi` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

# Dump of table catatan_perawatan
# ------------------------------------------------------------

CREATE TABLE `catatan_perawatan` (
  `tanggal` date DEFAULT NULL,
  `jam` time DEFAULT NULL,
  `no_rawat` varchar(17) DEFAULT NULL,
  `kd_dokter` varchar(20) DEFAULT NULL,
  `catatan` varchar(700) DEFAULT NULL,
  KEY `no_rawat` (`no_rawat`),
  KEY `kd_dokter` (`kd_dokter`),
  CONSTRAINT `catatan_perawatan_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `catatan_perawatan_ibfk_2` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table databarang
# ------------------------------------------------------------

CREATE TABLE `databarang` (
  `kode_brng` varchar(15) NOT NULL DEFAULT '',
  `nama_brng` varchar(80) DEFAULT NULL,
  `kode_satbesar` char(4) NOT NULL,
  `kode_sat` char(4) DEFAULT NULL,
  `letak_barang` varchar(100) DEFAULT NULL,
  `dasar` double NOT NULL,
  `h_beli` double DEFAULT NULL,
  `ralan` double DEFAULT NULL,
  `kelas1` double DEFAULT NULL,
  `kelas2` double DEFAULT NULL,
  `kelas3` double DEFAULT NULL,
  `utama` double DEFAULT NULL,
  `vip` double DEFAULT NULL,
  `vvip` double DEFAULT NULL,
  `beliluar` double DEFAULT NULL,
  `jualbebas` double DEFAULT NULL,
  `karyawan` double DEFAULT NULL,
  `stokminimal` double DEFAULT NULL,
  `kdjns` char(4) DEFAULT NULL,
  `isi` double NOT NULL,
  `kapasitas` double NOT NULL,
  `expire` date DEFAULT NULL,
  `status` enum('0','1') NOT NULL,
  `kode_industri` char(5) DEFAULT NULL,
  `kode_kategori` char(4) DEFAULT NULL,
  `kode_golongan` char(4) DEFAULT NULL,
  PRIMARY KEY (`kode_brng`),
  KEY `kode_sat` (`kode_sat`),
  KEY `kdjns` (`kdjns`),
  KEY `nama_brng` (`nama_brng`),
  KEY `letak_barang` (`letak_barang`),
  KEY `h_beli` (`h_beli`),
  KEY `h_distributor` (`ralan`),
  KEY `h_grosir` (`kelas1`),
  KEY `h_retail` (`kelas2`),
  KEY `stok` (`stokminimal`),
  KEY `kapasitas` (`kapasitas`),
  KEY `kode_industri` (`kode_industri`),
  KEY `kelas3` (`kelas3`),
  KEY `utama` (`utama`),
  KEY `vip` (`vip`),
  KEY `vvip` (`vvip`),
  KEY `beliluar` (`beliluar`),
  KEY `jualbebas` (`jualbebas`),
  KEY `karyawan` (`karyawan`),
  KEY `expire` (`expire`),
  KEY `status` (`status`),
  KEY `kode_kategori` (`kode_kategori`),
  KEY `kode_golongan` (`kode_golongan`),
  KEY `kode_satbesar` (`kode_satbesar`) USING BTREE,
  CONSTRAINT `databarang_ibfk_2` FOREIGN KEY (`kdjns`) REFERENCES `jenis` (`kdjns`) ON UPDATE CASCADE,
  CONSTRAINT `databarang_ibfk_3` FOREIGN KEY (`kode_sat`) REFERENCES `kodesatuan` (`kode_sat`) ON UPDATE CASCADE,
  CONSTRAINT `databarang_ibfk_4` FOREIGN KEY (`kode_industri`) REFERENCES `industrifarmasi` (`kode_industri`) ON UPDATE CASCADE,
  CONSTRAINT `databarang_ibfk_5` FOREIGN KEY (`kode_kategori`) REFERENCES `kategori_barang` (`kode`) ON UPDATE CASCADE,
  CONSTRAINT `databarang_ibfk_6` FOREIGN KEY (`kode_golongan`) REFERENCES `golongan_barang` (`kode`) ON UPDATE CASCADE,
  CONSTRAINT `databarang_ibfk_7` FOREIGN KEY (`kode_satbesar`) REFERENCES `kodesatuan` (`kode_sat`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `databarang` WRITE;
/*!40000 ALTER TABLE `databarang` DISABLE KEYS */;

INSERT INTO `databarang` (`kode_brng`, `nama_brng`, `kode_satbesar`, `kode_sat`, `letak_barang`, `dasar`, `h_beli`, `ralan`, `kelas1`, `kelas2`, `kelas3`, `utama`, `vip`, `vvip`, `beliluar`, `jualbebas`, `karyawan`, `stokminimal`, `kdjns`, `isi`, `kapasitas`, `expire`, `status`, `kode_industri`, `kode_kategori`, `kode_golongan`)
VALUES
	('B00001','Paracetamol 500mg','-','-','-',5000,5000,5000,5000,5000,5000,5000,5000,5000,5000,5000,5000,100,'-',10,500,'2024-06-10','1','-','-','-');

/*!40000 ALTER TABLE `databarang` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table departemen
# ------------------------------------------------------------

CREATE TABLE `departemen` (
  `dep_id` char(4) NOT NULL,
  `nama` varchar(25) NOT NULL,
  PRIMARY KEY (`dep_id`),
  KEY `nama` (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `departemen` WRITE;
/*!40000 ALTER TABLE `departemen` DISABLE KEYS */;

INSERT INTO `departemen` (`dep_id`, `nama`)
VALUES
	('-','-');

/*!40000 ALTER TABLE `departemen` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table detail_pemberian_obat
# ------------------------------------------------------------

CREATE TABLE `detail_pemberian_obat` (
  `tgl_perawatan` date NOT NULL DEFAULT '0000-00-00',
  `jam` time NOT NULL DEFAULT '00:00:00',
  `no_rawat` varchar(17) NOT NULL DEFAULT '',
  `kode_brng` varchar(15) NOT NULL,
  `h_beli` double DEFAULT NULL,
  `biaya_obat` double DEFAULT NULL,
  `jml` double NOT NULL,
  `embalase` double DEFAULT NULL,
  `tuslah` double DEFAULT NULL,
  `total` double NOT NULL,
  `status` enum('Ralan','Ranap') DEFAULT NULL,
  `kd_bangsal` char(5) DEFAULT NULL,
  `no_batch` varchar(20) NOT NULL,
  `no_faktur` varchar(20) NOT NULL,
  PRIMARY KEY (`tgl_perawatan`,`jam`,`no_rawat`,`kode_brng`,`no_batch`,`no_faktur`) USING BTREE,
  KEY `no_rawat` (`no_rawat`),
  KEY `kd_obat` (`kode_brng`),
  KEY `tgl_perawatan` (`tgl_perawatan`),
  KEY `jam` (`jam`),
  KEY `jml` (`jml`),
  KEY `tambahan` (`embalase`),
  KEY `total` (`total`),
  KEY `biaya_obat` (`biaya_obat`),
  KEY `kd_bangsal` (`kd_bangsal`),
  KEY `tuslah` (`tuslah`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  CONSTRAINT `detail_pemberian_obat_ibfk_3` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detail_pemberian_obat_ibfk_4` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detail_pemberian_obat_ibfk_5` FOREIGN KEY (`kd_bangsal`) REFERENCES `bangsal` (`kd_bangsal`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table detail_periksa_lab
# ------------------------------------------------------------

CREATE TABLE `detail_periksa_lab` (
  `no_rawat` varchar(17) NOT NULL,
  `kd_jenis_prw` varchar(15) NOT NULL,
  `tgl_periksa` date NOT NULL,
  `jam` time NOT NULL,
  `id_template` int(11) NOT NULL,
  `nilai` varchar(200) NOT NULL,
  `nilai_rujukan` varchar(30) NOT NULL,
  `keterangan` varchar(60) NOT NULL,
  `bagian_rs` double NOT NULL,
  `bhp` double NOT NULL,
  `bagian_perujuk` double NOT NULL,
  `bagian_dokter` double NOT NULL,
  `bagian_laborat` double NOT NULL,
  `kso` double DEFAULT NULL,
  `menejemen` double DEFAULT NULL,
  `biaya_item` double NOT NULL,
  PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`tgl_periksa`,`jam`,`id_template`),
  KEY `id_template` (`id_template`),
  KEY `kd_jenis_prw` (`kd_jenis_prw`),
  KEY `tgl_periksa` (`tgl_periksa`),
  KEY `jam` (`jam`),
  KEY `nilai` (`nilai`),
  KEY `nilai_rujukan` (`nilai_rujukan`),
  KEY `keterangan` (`keterangan`),
  KEY `biaya_item` (`biaya_item`),
  KEY `menejemen` (`menejemen`),
  KEY `kso` (`kso`),
  KEY `bagian_rs` (`bagian_rs`),
  KEY `bhp` (`bhp`),
  KEY `bagian_perujuk` (`bagian_perujuk`),
  KEY `bagian_dokter` (`bagian_dokter`),
  KEY `bagian_laborat` (`bagian_laborat`),
  CONSTRAINT `detail_periksa_lab_ibfk_10` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
  CONSTRAINT `detail_periksa_lab_ibfk_11` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_lab` (`kd_jenis_prw`) ON UPDATE CASCADE,
  CONSTRAINT `detail_periksa_lab_ibfk_12` FOREIGN KEY (`id_template`) REFERENCES `template_laboratorium` (`id_template`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table diagnosa_pasien
# ------------------------------------------------------------

CREATE TABLE `diagnosa_pasien` (
  `no_rawat` varchar(17) NOT NULL,
  `kd_penyakit` varchar(10) NOT NULL,
  `status` enum('Ralan','Ranap') NOT NULL,
  `prioritas` tinyint(4) NOT NULL,
  `status_penyakit` enum('Lama','Baru') DEFAULT NULL,
  PRIMARY KEY (`no_rawat`,`kd_penyakit`,`status`),
  KEY `kd_penyakit` (`kd_penyakit`),
  KEY `status` (`status`),
  KEY `prioritas` (`prioritas`),
  KEY `no_rawat` (`no_rawat`),
  CONSTRAINT `diagnosa_pasien_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
  CONSTRAINT `diagnosa_pasien_ibfk_2` FOREIGN KEY (`kd_penyakit`) REFERENCES `penyakit` (`kd_penyakit`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table dokter
# ------------------------------------------------------------

CREATE TABLE `dokter` (
  `kd_dokter` varchar(20) NOT NULL,
  `nm_dokter` varchar(50) DEFAULT NULL,
  `jk` enum('L','P') DEFAULT NULL,
  `tmp_lahir` varchar(20) DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `gol_drh` enum('A','B','O','AB','-') DEFAULT NULL,
  `agama` varchar(12) DEFAULT NULL,
  `almt_tgl` varchar(60) DEFAULT NULL,
  `no_telp` varchar(13) DEFAULT NULL,
  `stts_nikah` enum('BELUM MENIKAH','MENIKAH','JANDA','DUDHA','JOMBLO') DEFAULT NULL,
  `kd_sps` char(5) DEFAULT NULL,
  `alumni` varchar(60) DEFAULT NULL,
  `no_ijn_praktek` varchar(120) DEFAULT NULL,
  `status` enum('0','1') NOT NULL,
  PRIMARY KEY (`kd_dokter`),
  KEY `kd_sps` (`kd_sps`),
  KEY `nm_dokter` (`nm_dokter`),
  KEY `jk` (`jk`),
  KEY `tmp_lahir` (`tmp_lahir`),
  KEY `tgl_lahir` (`tgl_lahir`),
  KEY `gol_drh` (`gol_drh`),
  KEY `agama` (`agama`),
  KEY `almt_tgl` (`almt_tgl`),
  KEY `no_telp` (`no_telp`),
  KEY `stts_nikah` (`stts_nikah`),
  KEY `alumni` (`alumni`),
  KEY `no_ijn_praktek` (`no_ijn_praktek`),
  KEY `kd_dokter` (`kd_dokter`),
  KEY `status` (`status`),
  CONSTRAINT `dokter_ibfk_2` FOREIGN KEY (`kd_sps`) REFERENCES `spesialis` (`kd_sps`) ON UPDATE CASCADE,
  CONSTRAINT `dokter_ibfk_3` FOREIGN KEY (`kd_dokter`) REFERENCES `pegawai` (`nik`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `dokter` WRITE;
/*!40000 ALTER TABLE `dokter` DISABLE KEYS */;

INSERT INTO `dokter` (`kd_dokter`, `nm_dokter`, `jk`, `tmp_lahir`, `tgl_lahir`, `gol_drh`, `agama`, `almt_tgl`, `no_telp`, `stts_nikah`, `kd_sps`, `alumni`, `no_ijn_praktek`, `status`)
VALUES
	('DR001','dr. Ataaka Muhammad','L','Barabai','2000-09-18','O','Islam','Barabai','-','MENIKAH','UMUM','UI','-','1');

/*!40000 ALTER TABLE `dokter` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table dpjp_ranap
# ------------------------------------------------------------

CREATE TABLE `dpjp_ranap` (
  `no_rawat` varchar(17) NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  PRIMARY KEY (`no_rawat`,`kd_dokter`),
  KEY `dpjp_ranap_ibfk_2` (`kd_dokter`),
  CONSTRAINT `dpjp_ranap_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
  CONSTRAINT `dpjp_ranap_ibfk_2` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table emergency_index
# ------------------------------------------------------------

CREATE TABLE `emergency_index` (
  `kode_emergency` varchar(3) NOT NULL,
  `nama_emergency` varchar(200) DEFAULT NULL,
  `indek` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`kode_emergency`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `emergency_index` WRITE;
/*!40000 ALTER TABLE `emergency_index` DISABLE KEYS */;

INSERT INTO `emergency_index` (`kode_emergency`, `nama_emergency`, `indek`)
VALUES
	('-','-',1);

/*!40000 ALTER TABLE `emergency_index` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table gambar_radiologi
# ------------------------------------------------------------

CREATE TABLE `gambar_radiologi` (
  `no_rawat` varchar(17) NOT NULL,
  `tgl_periksa` date NOT NULL,
  `jam` time NOT NULL,
  `lokasi_gambar` varchar(500) NOT NULL,
  PRIMARY KEY (`no_rawat`,`tgl_periksa`,`jam`,`lokasi_gambar`),
  CONSTRAINT `gambar_radiologi_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table golongan_barang
# ------------------------------------------------------------

CREATE TABLE `golongan_barang` (
  `kode` char(4) NOT NULL,
  `nama` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `golongan_barang` WRITE;
/*!40000 ALTER TABLE `golongan_barang` DISABLE KEYS */;

INSERT INTO `golongan_barang` (`kode`, `nama`)
VALUES
	('-','-');

/*!40000 ALTER TABLE `golongan_barang` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table gudangbarang
# ------------------------------------------------------------

CREATE TABLE `gudangbarang` (
  `kode_brng` varchar(15) NOT NULL,
  `kd_bangsal` char(5) NOT NULL DEFAULT '',
  `stok` double NOT NULL,
  `no_batch` varchar(20) NOT NULL,
  `no_faktur` varchar(20) NOT NULL,
  PRIMARY KEY (`kode_brng`,`kd_bangsal`,`no_batch`,`no_faktur`) USING BTREE,
  KEY `kode_brng` (`kode_brng`),
  KEY `stok` (`stok`),
  KEY `kd_bangsal` (`kd_bangsal`) USING BTREE,
  CONSTRAINT `gudangbarang_ibfk_1` FOREIGN KEY (`kd_bangsal`) REFERENCES `bangsal` (`kd_bangsal`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `gudangbarang_ibfk_2` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table hasil_radiologi
# ------------------------------------------------------------

CREATE TABLE `hasil_radiologi` (
  `no_rawat` varchar(17) NOT NULL,
  `tgl_periksa` date NOT NULL,
  `jam` time NOT NULL,
  `hasil` text NOT NULL,
  PRIMARY KEY (`no_rawat`,`tgl_periksa`,`jam`),
  KEY `no_rawat` (`no_rawat`),
  CONSTRAINT `hasil_radiologi_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table icd9
# ------------------------------------------------------------

CREATE TABLE `icd9` (
  `kode` varchar(8) NOT NULL,
  `deskripsi_panjang` varchar(250) DEFAULT NULL,
  `deskripsi_pendek` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table industrifarmasi
# ------------------------------------------------------------

CREATE TABLE `industrifarmasi` (
  `kode_industri` char(5) NOT NULL DEFAULT '',
  `nama_industri` varchar(50) DEFAULT NULL,
  `alamat` varchar(50) DEFAULT NULL,
  `kota` varchar(20) DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`kode_industri`),
  KEY `nama_industri` (`nama_industri`),
  KEY `alamat` (`alamat`),
  KEY `kota` (`kota`),
  KEY `no_telp` (`no_telp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `industrifarmasi` WRITE;
/*!40000 ALTER TABLE `industrifarmasi` DISABLE KEYS */;

INSERT INTO `industrifarmasi` (`kode_industri`, `nama_industri`, `alamat`, `kota`, `no_telp`)
VALUES
	('-','-','-','-','0');

/*!40000 ALTER TABLE `industrifarmasi` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table inventaris
# ------------------------------------------------------------

CREATE TABLE `inventaris` (
  `no_inventaris` varchar(30) NOT NULL,
  `kode_barang` varchar(20) DEFAULT NULL,
  `asal_barang` enum('Beli','Bantuan','Hibah','-') DEFAULT NULL,
  `tgl_pengadaan` date DEFAULT NULL,
  `harga` double DEFAULT NULL,
  `status_barang` enum('Ada','Rusak','Hilang','Perbaikan','Dipinjam','-') DEFAULT NULL,
  `id_ruang` char(5) DEFAULT NULL,
  `no_rak` char(3) DEFAULT NULL,
  `no_box` char(3) DEFAULT NULL,
  PRIMARY KEY (`no_inventaris`),
  KEY `kode_barang` (`kode_barang`),
  KEY `kd_ruang` (`id_ruang`),
  KEY `asal_barang` (`asal_barang`),
  KEY `tgl_pengadaan` (`tgl_pengadaan`),
  KEY `harga` (`harga`),
  KEY `status_barang` (`status_barang`),
  KEY `no_rak` (`no_rak`),
  KEY `no_box` (`no_box`),
  CONSTRAINT `inventaris_ibfk_1` FOREIGN KEY (`kode_barang`) REFERENCES `inventaris_barang` (`kode_barang`) ON UPDATE CASCADE,
  CONSTRAINT `inventaris_ibfk_2` FOREIGN KEY (`id_ruang`) REFERENCES `inventaris_ruang` (`id_ruang`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table inventaris_barang
# ------------------------------------------------------------

CREATE TABLE `inventaris_barang` (
  `kode_barang` varchar(20) NOT NULL,
  `nama_barang` varchar(60) DEFAULT NULL,
  `jml_barang` int(11) DEFAULT NULL,
  `kode_produsen` varchar(10) DEFAULT NULL,
  `id_merk` varchar(10) DEFAULT NULL,
  `thn_produksi` year(4) DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `id_kategori` char(10) DEFAULT NULL,
  `id_jenis` char(10) DEFAULT NULL,
  PRIMARY KEY (`kode_barang`),
  KEY `kode_produsen` (`kode_produsen`),
  KEY `id_merk` (`id_merk`),
  KEY `id_kategori` (`id_kategori`),
  KEY `id_jenis` (`id_jenis`),
  KEY `nama_barang` (`nama_barang`),
  KEY `jml_barang` (`jml_barang`),
  KEY `thn_produksi` (`thn_produksi`),
  KEY `isbn` (`isbn`),
  CONSTRAINT `inventaris_barang_ibfk_5` FOREIGN KEY (`kode_produsen`) REFERENCES `inventaris_produsen` (`kode_produsen`) ON UPDATE CASCADE,
  CONSTRAINT `inventaris_barang_ibfk_6` FOREIGN KEY (`id_merk`) REFERENCES `inventaris_merk` (`id_merk`) ON UPDATE CASCADE,
  CONSTRAINT `inventaris_barang_ibfk_7` FOREIGN KEY (`id_kategori`) REFERENCES `inventaris_kategori` (`id_kategori`) ON UPDATE CASCADE,
  CONSTRAINT `inventaris_barang_ibfk_8` FOREIGN KEY (`id_jenis`) REFERENCES `inventaris_jenis` (`id_jenis`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table inventaris_jenis
# ------------------------------------------------------------

CREATE TABLE `inventaris_jenis` (
  `id_jenis` char(10) NOT NULL,
  `nama_jenis` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id_jenis`),
  KEY `nama_jenis` (`nama_jenis`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table inventaris_kategori
# ------------------------------------------------------------

CREATE TABLE `inventaris_kategori` (
  `id_kategori` char(10) NOT NULL,
  `nama_kategori` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id_kategori`),
  KEY `nama_kategori` (`nama_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table inventaris_merk
# ------------------------------------------------------------

CREATE TABLE `inventaris_merk` (
  `id_merk` varchar(10) NOT NULL,
  `nama_merk` varchar(40) NOT NULL,
  PRIMARY KEY (`id_merk`),
  KEY `nama_merk` (`nama_merk`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table inventaris_peminjaman
# ------------------------------------------------------------

CREATE TABLE `inventaris_peminjaman` (
  `peminjam` varchar(50) NOT NULL DEFAULT '',
  `tlp` varchar(13) NOT NULL,
  `no_inventaris` varchar(30) NOT NULL DEFAULT '',
  `tgl_pinjam` date NOT NULL DEFAULT '0000-00-00',
  `tgl_kembali` date DEFAULT NULL,
  `nip` varchar(20) NOT NULL DEFAULT '',
  `status_pinjam` enum('Masih Dipinjam','Sudah Kembali') DEFAULT NULL,
  PRIMARY KEY (`peminjam`,`no_inventaris`,`tgl_pinjam`,`nip`) USING BTREE,
  KEY `no_inventaris` (`no_inventaris`) USING BTREE,
  KEY `nip` (`nip`) USING BTREE,
  KEY `tgl_kembali` (`tgl_kembali`) USING BTREE,
  KEY `status_pinjam` (`status_pinjam`) USING BTREE,
  CONSTRAINT `inventaris_peminjaman_ibfk_1` FOREIGN KEY (`no_inventaris`) REFERENCES `inventaris` (`no_inventaris`) ON UPDATE CASCADE,
  CONSTRAINT `inventaris_peminjaman_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table inventaris_produsen
# ------------------------------------------------------------

CREATE TABLE `inventaris_produsen` (
  `kode_produsen` varchar(10) NOT NULL,
  `nama_produsen` varchar(40) DEFAULT NULL,
  `alamat_produsen` varchar(70) DEFAULT NULL,
  `no_telp` varchar(13) DEFAULT NULL,
  `email` varchar(25) DEFAULT NULL,
  `website_produsen` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`kode_produsen`),
  KEY `nama_produsen` (`nama_produsen`),
  KEY `alamat_produsen` (`alamat_produsen`),
  KEY `no_telp` (`no_telp`),
  KEY `email` (`email`),
  KEY `website_produsen` (`website_produsen`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table inventaris_ruang
# ------------------------------------------------------------

CREATE TABLE `inventaris_ruang` (
  `id_ruang` varchar(5) NOT NULL,
  `nama_ruang` varchar(40) NOT NULL,
  PRIMARY KEY (`id_ruang`),
  KEY `nama_ruang` (`nama_ruang`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table jabatan
# ------------------------------------------------------------

CREATE TABLE `jabatan` (
  `kd_jbtn` char(4) NOT NULL DEFAULT '',
  `nm_jbtn` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`kd_jbtn`),
  KEY `nm_jbtn` (`nm_jbtn`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `jabatan` WRITE;
/*!40000 ALTER TABLE `jabatan` DISABLE KEYS */;

INSERT INTO `jabatan` (`kd_jbtn`, `nm_jbtn`)
VALUES
	('-','-');

/*!40000 ALTER TABLE `jabatan` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table jadwal
# ------------------------------------------------------------

CREATE TABLE `jadwal` (
  `kd_dokter` varchar(20) NOT NULL,
  `hari_kerja` enum('SENIN','SELASA','RABU','KAMIS','JUMAT','SABTU','AKHAD') NOT NULL DEFAULT 'SENIN',
  `jam_mulai` time NOT NULL DEFAULT '00:00:00',
  `jam_selesai` time DEFAULT NULL,
  `kd_poli` char(5) DEFAULT NULL,
  `kuota` int(11) DEFAULT NULL,
  PRIMARY KEY (`kd_dokter`,`hari_kerja`,`jam_mulai`),
  KEY `kd_dokter` (`kd_dokter`),
  KEY `kd_poli` (`kd_poli`),
  KEY `jam_mulai` (`jam_mulai`),
  KEY `jam_selesai` (`jam_selesai`),
  CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`kd_poli`) REFERENCES `poliklinik` (`kd_poli`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table jadwal_pegawai
# ------------------------------------------------------------

CREATE TABLE `jadwal_pegawai` (
  `id` int(11) NOT NULL,
  `tahun` year(4) NOT NULL,
  `bulan` enum('01','02','03','04','05','06','07','08','09','10','11','12') NOT NULL,
  `h1` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h2` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h3` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h4` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h5` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h6` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h7` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h8` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h9` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h10` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h11` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h12` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h13` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h14` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h15` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h16` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h17` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h18` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h19` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h20` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h21` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h22` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h23` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h24` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h25` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h26` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h27` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h28` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h29` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h30` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h31` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  PRIMARY KEY (`id`,`tahun`,`bulan`),
  KEY `h1` (`h1`),
  KEY `h2` (`h2`),
  KEY `h3` (`h3`),
  KEY `h4` (`h4`),
  KEY `h30` (`h30`),
  KEY `h31` (`h31`),
  KEY `h29` (`h29`),
  KEY `h28` (`h28`),
  KEY `h18` (`h18`),
  KEY `h9` (`h9`),
  CONSTRAINT `jadwal_pegawai_ibfk_1` FOREIGN KEY (`id`) REFERENCES `pegawai` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table jadwal_tambahan
# ------------------------------------------------------------

CREATE TABLE `jadwal_tambahan` (
  `id` int(11) NOT NULL,
  `tahun` year(4) NOT NULL,
  `bulan` enum('01','02','03','04','05','06','07','08','09','10','11','12') NOT NULL,
  `h1` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h2` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h3` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h4` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h5` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h6` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h7` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h8` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h9` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h10` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h11` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h12` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h13` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h14` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h15` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h16` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h17` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h18` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h19` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h20` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h21` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h22` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h23` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h24` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h25` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h26` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h27` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h28` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h29` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h30` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  `h31` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10','') NOT NULL,
  PRIMARY KEY (`id`,`tahun`,`bulan`),
  CONSTRAINT `jadwal_tambahan_ibfk_1` FOREIGN KEY (`id`) REFERENCES `pegawai` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table jam_jaga
# ------------------------------------------------------------

CREATE TABLE `jam_jaga` (
  `no_id` int(11) NOT NULL AUTO_INCREMENT,
  `dep_id` char(4) NOT NULL,
  `shift` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10') NOT NULL,
  `jam_masuk` time NOT NULL,
  `jam_pulang` time NOT NULL,
  PRIMARY KEY (`no_id`),
  UNIQUE KEY `dep_id_2` (`dep_id`,`shift`),
  KEY `dep_id` (`dep_id`),
  KEY `shift` (`shift`),
  KEY `jam_masuk` (`jam_masuk`),
  KEY `jam_pulang` (`jam_pulang`),
  CONSTRAINT `jam_jaga_ibfk_1` FOREIGN KEY (`dep_id`) REFERENCES `departemen` (`dep_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table jam_masuk
# ------------------------------------------------------------

CREATE TABLE `jam_masuk` (
  `shift` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10') NOT NULL,
  `jam_masuk` time NOT NULL,
  `jam_pulang` time NOT NULL,
  PRIMARY KEY (`shift`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `jam_masuk` WRITE;
/*!40000 ALTER TABLE `jam_masuk` DISABLE KEYS */;

INSERT INTO `jam_masuk` (`shift`, `jam_masuk`, `jam_pulang`)
VALUES
	('Pagi','06:00:00','16:00:00'),
	('Pagi2','08:00:00','14:00:00'),
	('Pagi3','10:00:00','17:00:00'),
	('Siang','14:00:00','08:00:00'),
	('Siang2','14:00:00','21:00:00'),
	('Malam','20:00:00','02:00:00'),
	('Midle Siang1','00:00:00','06:00:00'),
	('Midle Siang3','00:00:00','00:00:00'),
	('Midle Siang4','04:00:00','16:00:00'),
	('Midle Malam1','00:00:00','06:00:00'),
	('Midle Malam5','22:00:00','07:00:00');

/*!40000 ALTER TABLE `jam_masuk` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table jenis
# ------------------------------------------------------------

CREATE TABLE `jenis` (
  `kdjns` char(4) NOT NULL,
  `nama` varchar(30) NOT NULL,
  `keterangan` varchar(50) NOT NULL,
  PRIMARY KEY (`kdjns`),
  KEY `nama` (`nama`),
  KEY `keterangan` (`keterangan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `jenis` WRITE;
/*!40000 ALTER TABLE `jenis` DISABLE KEYS */;

INSERT INTO `jenis` (`kdjns`, `nama`, `keterangan`)
VALUES
	('-','-','-');

/*!40000 ALTER TABLE `jenis` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table jnj_jabatan
# ------------------------------------------------------------

CREATE TABLE `jnj_jabatan` (
  `kode` varchar(10) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `tnj` double NOT NULL,
  `indek` tinyint(4) NOT NULL,
  PRIMARY KEY (`kode`),
  KEY `nama` (`nama`),
  KEY `tnj` (`tnj`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `jnj_jabatan` WRITE;
/*!40000 ALTER TABLE `jnj_jabatan` DISABLE KEYS */;

INSERT INTO `jnj_jabatan` (`kode`, `nama`, `tnj`, `indek`)
VALUES
	('-','-',0,1);

/*!40000 ALTER TABLE `jnj_jabatan` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table jns_perawatan
# ------------------------------------------------------------

CREATE TABLE `jns_perawatan` (
  `kd_jenis_prw` varchar(15) NOT NULL,
  `nm_perawatan` varchar(80) DEFAULT NULL,
  `kd_kategori` char(5) DEFAULT NULL,
  `material` double DEFAULT NULL,
  `bhp` double NOT NULL,
  `tarif_tindakandr` double DEFAULT NULL,
  `tarif_tindakanpr` double DEFAULT NULL,
  `kso` double DEFAULT NULL,
  `menejemen` double DEFAULT NULL,
  `total_byrdr` double DEFAULT NULL,
  `total_byrpr` double DEFAULT NULL,
  `total_byrdrpr` double NOT NULL,
  `kd_pj` char(3) NOT NULL,
  `kd_poli` char(5) NOT NULL,
  `status` enum('0','1') NOT NULL,
  PRIMARY KEY (`kd_jenis_prw`),
  KEY `kd_kategori` (`kd_kategori`),
  KEY `kd_pj` (`kd_pj`),
  KEY `kd_poli` (`kd_poli`),
  KEY `nm_perawatan` (`nm_perawatan`),
  KEY `material` (`material`),
  KEY `tarif_tindakandr` (`tarif_tindakandr`),
  KEY `tarif_tindakanpr` (`tarif_tindakanpr`),
  KEY `total_byrdr` (`total_byrdr`),
  KEY `total_byrpr` (`total_byrpr`),
  KEY `kso` (`kso`),
  KEY `menejemen` (`menejemen`),
  KEY `status` (`status`),
  KEY `total_byrdrpr` (`total_byrdrpr`),
  KEY `bhp` (`bhp`),
  CONSTRAINT `jns_perawatan_ibfk_1` FOREIGN KEY (`kd_kategori`) REFERENCES `kategori_perawatan` (`kd_kategori`) ON UPDATE CASCADE,
  CONSTRAINT `jns_perawatan_ibfk_2` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON UPDATE CASCADE,
  CONSTRAINT `jns_perawatan_ibfk_3` FOREIGN KEY (`kd_poli`) REFERENCES `poliklinik` (`kd_poli`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `jns_perawatan` WRITE;
/*!40000 ALTER TABLE `jns_perawatan` DISABLE KEYS */;

INSERT INTO `jns_perawatan` (`kd_jenis_prw`, `nm_perawatan`, `kd_kategori`, `material`, `bhp`, `tarif_tindakandr`, `tarif_tindakanpr`, `kso`, `menejemen`, `total_byrdr`, `total_byrpr`, `total_byrdrpr`, `kd_pj`, `kd_poli`, `status`)
VALUES
	('RJ001','Pemeriksaan rutin','-',0,0,50000,0,0,0,50000,0,50000,'-','-','1');

/*!40000 ALTER TABLE `jns_perawatan` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table jns_perawatan_inap
# ------------------------------------------------------------

CREATE TABLE `jns_perawatan_inap` (
  `kd_jenis_prw` varchar(15) NOT NULL,
  `nm_perawatan` varchar(80) DEFAULT NULL,
  `kd_kategori` char(5) NOT NULL,
  `material` double DEFAULT NULL,
  `bhp` double NOT NULL,
  `tarif_tindakandr` double DEFAULT NULL,
  `tarif_tindakanpr` double DEFAULT NULL,
  `kso` double DEFAULT NULL,
  `menejemen` double DEFAULT NULL,
  `total_byrdr` double DEFAULT NULL,
  `total_byrpr` double DEFAULT NULL,
  `total_byrdrpr` double NOT NULL,
  `kd_pj` char(3) NOT NULL,
  `kd_bangsal` char(5) NOT NULL,
  `status` enum('0','1') NOT NULL,
  `kelas` enum('-','Kelas 1','Kelas 2','Kelas 3','Kelas Utama','Kelas VIP','Kelas VVIP') NOT NULL,
  PRIMARY KEY (`kd_jenis_prw`),
  KEY `kd_pj` (`kd_pj`),
  KEY `kd_bangsal` (`kd_bangsal`),
  KEY `kd_kategori` (`kd_kategori`),
  KEY `nm_perawatan` (`nm_perawatan`),
  KEY `material` (`material`),
  KEY `tarif_tindakandr` (`tarif_tindakandr`),
  KEY `tarif_tindakanpr` (`tarif_tindakanpr`),
  KEY `total_byrdr` (`total_byrdr`),
  KEY `total_byrpr` (`total_byrpr`),
  KEY `bhp` (`bhp`),
  KEY `kso` (`kso`),
  KEY `menejemen` (`menejemen`),
  KEY `status` (`status`),
  KEY `total_byrdrpr` (`total_byrdrpr`),
  CONSTRAINT `jns_perawatan_inap_ibfk_7` FOREIGN KEY (`kd_kategori`) REFERENCES `kategori_perawatan` (`kd_kategori`) ON UPDATE CASCADE,
  CONSTRAINT `jns_perawatan_inap_ibfk_8` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON UPDATE CASCADE,
  CONSTRAINT `jns_perawatan_inap_ibfk_9` FOREIGN KEY (`kd_bangsal`) REFERENCES `bangsal` (`kd_bangsal`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `jns_perawatan_inap` WRITE;
/*!40000 ALTER TABLE `jns_perawatan_inap` DISABLE KEYS */;

INSERT INTO `jns_perawatan_inap` (`kd_jenis_prw`, `nm_perawatan`, `kd_kategori`, `material`, `bhp`, `tarif_tindakandr`, `tarif_tindakanpr`, `kso`, `menejemen`, `total_byrdr`, `total_byrpr`, `total_byrdrpr`, `kd_pj`, `kd_bangsal`, `status`, `kelas`)
VALUES
	('RI001','Pasang Infus','-',0,0,0,25000,0,0,0,25000,25000,'-','-','1','Kelas 1');

/*!40000 ALTER TABLE `jns_perawatan_inap` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table jns_perawatan_lab
# ------------------------------------------------------------

CREATE TABLE `jns_perawatan_lab` (
  `kd_jenis_prw` varchar(15) NOT NULL,
  `nm_perawatan` varchar(80) DEFAULT NULL,
  `bagian_rs` double DEFAULT NULL,
  `bhp` double NOT NULL,
  `tarif_perujuk` double NOT NULL,
  `tarif_tindakan_dokter` double NOT NULL,
  `tarif_tindakan_petugas` double DEFAULT NULL,
  `kso` double DEFAULT NULL,
  `menejemen` double DEFAULT NULL,
  `total_byr` double DEFAULT NULL,
  `kd_pj` char(3) NOT NULL,
  `status` enum('0','1') NOT NULL,
  `kelas` enum('-','Rawat Jalan','Kelas 1','Kelas 2','Kelas 3','Kelas Utama','Kelas VIP','Kelas VVIP') NOT NULL,
  `kategori` enum('PK','PA','MB') NOT NULL,
  PRIMARY KEY (`kd_jenis_prw`),
  KEY `kd_pj` (`kd_pj`),
  KEY `nm_perawatan` (`nm_perawatan`),
  KEY `tarif_perujuk` (`tarif_perujuk`),
  KEY `tarif_tindakan_dokter` (`tarif_tindakan_dokter`),
  KEY `tarif_tindakan_petugas` (`tarif_tindakan_petugas`),
  KEY `total_byr` (`total_byr`),
  KEY `bagian_rs` (`bagian_rs`),
  KEY `bhp` (`bhp`),
  KEY `kso` (`kso`),
  KEY `menejemen` (`menejemen`),
  KEY `status` (`status`),
  CONSTRAINT `jns_perawatan_lab_ibfk_1` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `jns_perawatan_lab` WRITE;
/*!40000 ALTER TABLE `jns_perawatan_lab` DISABLE KEYS */;

INSERT INTO `jns_perawatan_lab` (`kd_jenis_prw`, `nm_perawatan`, `bagian_rs`, `bhp`, `tarif_perujuk`, `tarif_tindakan_dokter`, `tarif_tindakan_petugas`, `kso`, `menejemen`, `total_byr`, `kd_pj`, `status`, `kelas`, `kategori`)
VALUES
	('LAB001','Pemeriksaan Darah',0,0,0,100000,0,0,0,100000,'-','1','Kelas 1','PK');

/*!40000 ALTER TABLE `jns_perawatan_lab` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table jns_perawatan_radiologi
# ------------------------------------------------------------

CREATE TABLE `jns_perawatan_radiologi` (
  `kd_jenis_prw` varchar(15) NOT NULL,
  `nm_perawatan` varchar(80) DEFAULT NULL,
  `bagian_rs` double DEFAULT NULL,
  `bhp` double NOT NULL,
  `tarif_perujuk` double NOT NULL,
  `tarif_tindakan_dokter` double NOT NULL,
  `tarif_tindakan_petugas` double DEFAULT NULL,
  `kso` double DEFAULT NULL,
  `menejemen` double DEFAULT NULL,
  `total_byr` double DEFAULT NULL,
  `kd_pj` char(3) NOT NULL,
  `status` enum('0','1') NOT NULL,
  `kelas` enum('-','Rawat Jalan','Kelas 1','Kelas 2','Kelas 3','Kelas Utama','Kelas VIP','Kelas VVIP') NOT NULL,
  PRIMARY KEY (`kd_jenis_prw`),
  KEY `kd_pj` (`kd_pj`),
  KEY `nm_perawatan` (`nm_perawatan`),
  KEY `bagian_rs` (`bagian_rs`),
  KEY `tarif_perujuk` (`tarif_perujuk`),
  KEY `tarif_tindakan_dokter` (`tarif_tindakan_dokter`),
  KEY `tarif_tindakan_petugas` (`tarif_tindakan_petugas`),
  KEY `total_byr` (`total_byr`),
  KEY `bhp` (`bhp`),
  KEY `kso` (`kso`),
  KEY `menejemen` (`menejemen`),
  KEY `status` (`status`),
  CONSTRAINT `jns_perawatan_radiologi_ibfk_1` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `jns_perawatan_radiologi` WRITE;
/*!40000 ALTER TABLE `jns_perawatan_radiologi` DISABLE KEYS */;

INSERT INTO `jns_perawatan_radiologi` (`kd_jenis_prw`, `nm_perawatan`, `bagian_rs`, `bhp`, `tarif_perujuk`, `tarif_tindakan_dokter`, `tarif_tindakan_petugas`, `kso`, `menejemen`, `total_byr`, `kd_pj`, `status`, `kelas`)
VALUES
	('RAD001','Thorax',0,0,0,150000,0,0,0,150000,'-','1','Kelas 1');

/*!40000 ALTER TABLE `jns_perawatan_radiologi` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table kabupaten
# ------------------------------------------------------------

CREATE TABLE `kabupaten` (
  `kd_kab` int(11) NOT NULL AUTO_INCREMENT,
  `nm_kab` varchar(60) NOT NULL,
  PRIMARY KEY (`kd_kab`),
  UNIQUE KEY `nm_kab` (`nm_kab`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `kabupaten` WRITE;
/*!40000 ALTER TABLE `kabupaten` DISABLE KEYS */;

INSERT INTO `kabupaten` (`kd_kab`, `nm_kab`)
VALUES
	(1,'-');

/*!40000 ALTER TABLE `kabupaten` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table kamar
# ------------------------------------------------------------

CREATE TABLE `kamar` (
  `kd_kamar` varchar(15) NOT NULL,
  `kd_bangsal` char(5) DEFAULT NULL,
  `trf_kamar` double DEFAULT NULL,
  `status` enum('ISI','KOSONG','DIBERSIHKAN','DIBOOKING') DEFAULT NULL,
  `kelas` enum('Kelas 1','Kelas 2','Kelas 3','Kelas Utama','Kelas VIP','Kelas VVIP') DEFAULT NULL,
  `statusdata` enum('0','1') DEFAULT NULL,
  PRIMARY KEY (`kd_kamar`),
  KEY `kd_bangsal` (`kd_bangsal`),
  KEY `trf_kamar` (`trf_kamar`),
  KEY `status` (`status`),
  KEY `kelas` (`kelas`),
  KEY `statusdata` (`statusdata`),
  CONSTRAINT `kamar_ibfk_1` FOREIGN KEY (`kd_bangsal`) REFERENCES `bangsal` (`kd_bangsal`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `kamar` WRITE;
/*!40000 ALTER TABLE `kamar` DISABLE KEYS */;

INSERT INTO `kamar` (`kd_kamar`, `kd_bangsal`, `trf_kamar`, `status`, `kelas`, `statusdata`)
VALUES
	('ANG01','ANG',100000,'KOSONG','Kelas 1','1'),
	('ANG02','ANG',100000,'KOSONG','Kelas 1','1');

/*!40000 ALTER TABLE `kamar` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table kamar_inap
# ------------------------------------------------------------

CREATE TABLE `kamar_inap` (
  `no_rawat` varchar(17) NOT NULL DEFAULT '',
  `kd_kamar` varchar(15) NOT NULL,
  `trf_kamar` double DEFAULT NULL,
  `diagnosa_awal` varchar(100) DEFAULT NULL,
  `diagnosa_akhir` varchar(100) DEFAULT NULL,
  `tgl_masuk` date NOT NULL DEFAULT '0000-00-00',
  `jam_masuk` time NOT NULL DEFAULT '00:00:00',
  `tgl_keluar` date DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `lama` double DEFAULT NULL,
  `ttl_biaya` double DEFAULT NULL,
  `stts_pulang` enum('Sehat','Rujuk','APS','+','Meninggal','Sembuh','Membaik','Pulang Paksa','-','Pindah Kamar','Status Belum Lengkap','Atas Persetujuan Dokter','Atas Permintaan Sendiri','Isoman','Lain-lain') NOT NULL,
  PRIMARY KEY (`no_rawat`,`tgl_masuk`,`jam_masuk`),
  KEY `kd_kamar` (`kd_kamar`),
  KEY `diagnosa_awal` (`diagnosa_awal`),
  KEY `diagnosa_akhir` (`diagnosa_akhir`),
  KEY `tgl_keluar` (`tgl_keluar`),
  KEY `jam_keluar` (`jam_keluar`),
  KEY `lama` (`lama`),
  KEY `ttl_biaya` (`ttl_biaya`),
  KEY `stts_pulang` (`stts_pulang`),
  KEY `trf_kamar` (`trf_kamar`),
  CONSTRAINT `kamar_inap_ibfk_2` FOREIGN KEY (`kd_kamar`) REFERENCES `kamar` (`kd_kamar`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kamar_inap_ibfk_3` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table kategori_barang
# ------------------------------------------------------------

CREATE TABLE `kategori_barang` (
  `kode` char(4) NOT NULL,
  `nama` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `kategori_barang` WRITE;
/*!40000 ALTER TABLE `kategori_barang` DISABLE KEYS */;

INSERT INTO `kategori_barang` (`kode`, `nama`)
VALUES
	('-','-');

/*!40000 ALTER TABLE `kategori_barang` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table kategori_penyakit
# ------------------------------------------------------------

CREATE TABLE `kategori_penyakit` (
  `kd_ktg` varchar(8) NOT NULL,
  `nm_kategori` varchar(30) DEFAULT NULL,
  `ciri_umum` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`kd_ktg`),
  KEY `nm_kategori` (`nm_kategori`),
  KEY `ciri_umum` (`ciri_umum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `kategori_penyakit` WRITE;
/*!40000 ALTER TABLE `kategori_penyakit` DISABLE KEYS */;

INSERT INTO `kategori_penyakit` (`kd_ktg`, `nm_kategori`, `ciri_umum`)
VALUES
	('-','-','-');

/*!40000 ALTER TABLE `kategori_penyakit` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table kategori_perawatan
# ------------------------------------------------------------

CREATE TABLE `kategori_perawatan` (
  `kd_kategori` char(5) NOT NULL,
  `nm_kategori` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`kd_kategori`),
  KEY `nm_kategori` (`nm_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `kategori_perawatan` WRITE;
/*!40000 ALTER TABLE `kategori_perawatan` DISABLE KEYS */;

INSERT INTO `kategori_perawatan` (`kd_kategori`, `nm_kategori`)
VALUES
	('-','-');

/*!40000 ALTER TABLE `kategori_perawatan` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table kecamatan
# ------------------------------------------------------------

CREATE TABLE `kecamatan` (
  `kd_kec` int(11) NOT NULL AUTO_INCREMENT,
  `nm_kec` varchar(60) NOT NULL,
  PRIMARY KEY (`kd_kec`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `kecamatan` WRITE;
/*!40000 ALTER TABLE `kecamatan` DISABLE KEYS */;

INSERT INTO `kecamatan` (`kd_kec`, `nm_kec`)
VALUES
	(1,'-');

/*!40000 ALTER TABLE `kecamatan` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table kelompok_jabatan
# ------------------------------------------------------------

CREATE TABLE `kelompok_jabatan` (
  `kode_kelompok` varchar(3) NOT NULL,
  `nama_kelompok` varchar(100) DEFAULT NULL,
  `indek` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`kode_kelompok`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `kelompok_jabatan` WRITE;
/*!40000 ALTER TABLE `kelompok_jabatan` DISABLE KEYS */;

INSERT INTO `kelompok_jabatan` (`kode_kelompok`, `nama_kelompok`, `indek`)
VALUES
	('-','-',1);

/*!40000 ALTER TABLE `kelompok_jabatan` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table kelurahan
# ------------------------------------------------------------

CREATE TABLE `kelurahan` (
  `kd_kel` varchar(11) NOT NULL,
  `nm_kel` varchar(60) NOT NULL,
  PRIMARY KEY (`kd_kel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `kelurahan` WRITE;
/*!40000 ALTER TABLE `kelurahan` DISABLE KEYS */;

INSERT INTO `kelurahan` (`kd_kel`, `nm_kel`)
VALUES
	('1','-');

/*!40000 ALTER TABLE `kelurahan` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table kodesatuan
# ------------------------------------------------------------

CREATE TABLE `kodesatuan` (
  `kode_sat` char(4) NOT NULL,
  `satuan` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`kode_sat`),
  KEY `satuan` (`satuan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `kodesatuan` WRITE;
/*!40000 ALTER TABLE `kodesatuan` DISABLE KEYS */;

INSERT INTO `kodesatuan` (`kode_sat`, `satuan`)
VALUES
	('-','-');

/*!40000 ALTER TABLE `kodesatuan` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table laporan_operasi
# ------------------------------------------------------------

CREATE TABLE `laporan_operasi` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `diagnosa_preop` varchar(100) NOT NULL,
  `diagnosa_postop` varchar(100) NOT NULL,
  `jaringan_dieksekusi` varchar(100) NOT NULL,
  `selesaioperasi` datetime NOT NULL,
  `permintaan_pa` enum('Ya','Tidak') NOT NULL,
  `laporan_operasi` text NOT NULL,
  PRIMARY KEY (`no_rawat`,`tanggal`),
  CONSTRAINT `laporan_operasi_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table maping_dokter_dpjpvclaim
# ------------------------------------------------------------

CREATE TABLE `maping_dokter_dpjpvclaim` (
  `kd_dokter` varchar(20) NOT NULL,
  `kd_dokter_bpjs` varchar(20) DEFAULT NULL,
  `nm_dokter_bpjs` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`kd_dokter`) USING BTREE,
  CONSTRAINT `maping_dokter_dpjpvclaim_ibfk_1` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table maping_dokter_pcare
# ------------------------------------------------------------

CREATE TABLE `maping_dokter_pcare` (
  `kd_dokter` varchar(20) NOT NULL,
  `kd_dokter_pcare` varchar(20) DEFAULT NULL,
  `nm_dokter_pcare` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`kd_dokter`) USING BTREE,
  CONSTRAINT `maping_dokter_pcare_ibfk_1` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table maping_poli_bpjs
# ------------------------------------------------------------

CREATE TABLE `maping_poli_bpjs` (
  `kd_poli_rs` varchar(5) NOT NULL,
  `kd_poli_bpjs` varchar(15) NOT NULL,
  `nm_poli_bpjs` varchar(40) NOT NULL,
  PRIMARY KEY (`kd_poli_rs`),
  UNIQUE KEY `kd_poli_bpjs` (`kd_poli_bpjs`) USING BTREE,
  CONSTRAINT `maping_poli_bpjs_ibfk_1` FOREIGN KEY (`kd_poli_rs`) REFERENCES `poliklinik` (`kd_poli`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table maping_poliklinik_pcare
# ------------------------------------------------------------

CREATE TABLE `maping_poliklinik_pcare` (
  `kd_poli_rs` char(5) NOT NULL,
  `kd_poli_pcare` char(5) DEFAULT NULL,
  `nm_poli_pcare` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`kd_poli_rs`) USING BTREE,
  CONSTRAINT `maping_poliklinik_pcare_ibfk_1` FOREIGN KEY (`kd_poli_rs`) REFERENCES `poliklinik` (`kd_poli`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table master_aturan_pakai
# ------------------------------------------------------------

CREATE TABLE `master_aturan_pakai` (
  `aturan` varchar(150) NOT NULL,
  PRIMARY KEY (`aturan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `master_aturan_pakai` WRITE;
/*!40000 ALTER TABLE `master_aturan_pakai` DISABLE KEYS */;

INSERT INTO `master_aturan_pakai` (`aturan`)
VALUES
	('3 x 1 Sehari');

/*!40000 ALTER TABLE `master_aturan_pakai` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table master_berkas_digital
# ------------------------------------------------------------

CREATE TABLE `master_berkas_digital` (
  `kode` varchar(10) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `master_berkas_digital` WRITE;
/*!40000 ALTER TABLE `master_berkas_digital` DISABLE KEYS */;

INSERT INTO `master_berkas_digital` (`kode`, `nama`)
VALUES
	('DIG001','Berkas Digital');

/*!40000 ALTER TABLE `master_berkas_digital` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table master_masalah_keperawatan
# ------------------------------------------------------------

CREATE TABLE `master_masalah_keperawatan` (
  `kode_masalah` varchar(3) NOT NULL,
  `nama_masalah` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`kode_masalah`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table metode_racik
# ------------------------------------------------------------

CREATE TABLE `metode_racik` (
  `kd_racik` varchar(3) NOT NULL,
  `nm_racik` varchar(30) NOT NULL,
  PRIMARY KEY (`kd_racik`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `metode_racik` WRITE;
/*!40000 ALTER TABLE `metode_racik` DISABLE KEYS */;

INSERT INTO `metode_racik` (`kd_racik`, `nm_racik`)
VALUES
	('1','Puyer');

/*!40000 ALTER TABLE `metode_racik` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table mlite_akun_kegiatan
# ------------------------------------------------------------

CREATE TABLE `mlite_akun_kegiatan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kegiatan` varchar(200) DEFAULT NULL,
  `kd_rek` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_antrian_loket
# ------------------------------------------------------------

CREATE TABLE `mlite_antrian_loket` (
  `kd` int(50) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `noantrian` varchar(50) NOT NULL,
  `no_rkm_medis` varchar(50) DEFAULT NULL,
  `postdate` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL DEFAULT '00:00:00',
  `status` varchar(10) NOT NULL DEFAULT '0',
  `loket` varchar(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`kd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mlite_antrian_referensi
# ------------------------------------------------------------

CREATE TABLE `mlite_antrian_referensi` (
  `tanggal_periksa` date NOT NULL,
  `no_rkm_medis` varchar(50) NOT NULL,
  `nomor_kartu` varchar(50) NOT NULL,
  `nomor_referensi` varchar(50) NOT NULL,
  `kodebooking` varchar(100) NOT NULL,
  `jenis_kunjungan` varchar(10) NOT NULL,
  `status_kirim` varchar(20) DEFAULT NULL,
  `keterangan` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mlite_antrian_referensi_batal
# ------------------------------------------------------------

CREATE TABLE `mlite_antrian_referensi_batal` (
  `tanggal_batal` date NOT NULL,
  `nomor_referensi` varchar(50) NOT NULL,
  `kodebooking` varchar(100) NOT NULL,
  `keterangan` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mlite_antrian_referensi_taskid
# ------------------------------------------------------------

CREATE TABLE `mlite_antrian_referensi_taskid` (
  `tanggal_periksa` date NOT NULL,
  `nomor_referensi` varchar(50) NOT NULL,
  `taskid` varchar(50) NOT NULL,
  `waktu` varchar(50) NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  `keterangan` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mlite_apamregister
# ------------------------------------------------------------

CREATE TABLE `mlite_apamregister` (
  `nama_lengkap` varchar(225) NOT NULL,
  `email` varchar(225) NOT NULL,
  `nomor_ktp` varchar(225) NOT NULL,
  `nomor_telepon` varchar(225) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_billing
# ------------------------------------------------------------

CREATE TABLE `mlite_billing` (
  `id_billing` int(11) NOT NULL AUTO_INCREMENT,
  `kd_billing` varchar(100) NOT NULL,
  `no_rawat` varchar(17) NOT NULL,
  `jumlah_total` int(100) NOT NULL,
  `potongan` int(100) NOT NULL,
  `jumlah_harus_bayar` int(100) NOT NULL,
  `jumlah_bayar` int(100) NOT NULL,
  `tgl_billing` date NOT NULL,
  `jam_billing` time NOT NULL,
  `id_user` int(11) NOT NULL,
  `keterangan` varchar(100) NOT NULL,
  PRIMARY KEY (`id_billing`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table mlite_bridging_pcare
# ------------------------------------------------------------

CREATE TABLE `mlite_bridging_pcare` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_rawat` text NOT NULL,
  `no_rkm_medis` text,
  `tgl_daftar` text,
  `nomor_kunjungan` text,
  `kode_provider_peserta` text,
  `nomor_jaminan` text,
  `kode_poli` text,
  `nama_poli` text,
  `kunjungan_sakit` text,
  `sistole` text,
  `diastole` text,
  `nadi` text,
  `respirasi` text,
  `tinggi` text,
  `berat` text,
  `lingkar_perut` text,
  `rujuk_balik` text,
  `subyektif` text,
  `kode_tkp` text,
  `nomor_urut` text,
  `kode_kesadaran` text,
  `nama_kesadaran` text,
  `terapi` text,
  `kode_status_pulang` text,
  `nama_status_pulang` text,
  `tgl_pulang` text,
  `tgl_kunjungan` text,
  `kode_dokter` text,
  `nama_dokter` text,
  `kode_diagnosa1` text,
  `nama_diagnosa1` text,
  `kode_diagnosa2` text,
  `nama_diagnosa2` text,
  `kode_diagnosa3` text,
  `nama_diagnosa3` text,
  `tgl_estimasi_rujuk` text,
  `kode_ppk` text,
  `nama_ppk` text,
  `kode_spesialis` text,
  `nama_spesialis` text,
  `kode_subspesialis` text,
  `nama_subspesialis` text,
  `kode_sarana` text,
  `nama_sarana` text,
  `kode_referensikhusus` text,
  `nama_referensikhusus` text,
  `kode_faskeskhusus` text,
  `nama_faskeskhusus` text,
  `catatan` text,
  `kode_tacc` text,
  `nama_tacc` text,
  `alasan_tacc` text,
  `id_user` text NOT NULL,
  `tgl_input` text NOT NULL,
  `status_kirim` text NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_detailjurnal
# ------------------------------------------------------------

CREATE TABLE `mlite_detailjurnal` (
  `no_jurnal` varchar(20) DEFAULT NULL,
  `kd_rek` varchar(15) DEFAULT NULL,
  `arus_kas` int(10) NOT NULL,
  `debet` double NOT NULL,
  `kredit` double NOT NULL,
  KEY `no_jurnal` (`no_jurnal`),
  KEY `kd_rek` (`kd_rek`),
  KEY `debet` (`debet`),
  KEY `kredit` (`kredit`),
  CONSTRAINT `mlite_detailjurnal_ibfk_1` FOREIGN KEY (`no_jurnal`) REFERENCES `mlite_jurnal` (`no_jurnal`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_detailjurnal_ibfk_2` FOREIGN KEY (`kd_rek`) REFERENCES `mlite_rekening` (`kd_rek`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_duitku
# ------------------------------------------------------------

CREATE TABLE `mlite_duitku` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `tanggal` datetime NOT NULL,
  `no_rkm_medis` varchar(15) NOT NULL,
  `paymentUrl` varchar(255) NOT NULL,
  `merchantCode` varchar(255) NOT NULL,
  `reference` varchar(255) NOT NULL,
  `vaNumber` varchar(255) NOT NULL,
  `amount` varchar(255) NOT NULL,
  `statusCode` varchar(255) NOT NULL,
  `statusMessage` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `reference` (`reference`),
  KEY `mlite_duitku_ibfk_1` (`no_rkm_medis`),
  CONSTRAINT `mlite_duitku_ibfk_1` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_fenton
# ------------------------------------------------------------

CREATE TABLE `mlite_fenton` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `usia_kehamilan` int(11) NOT NULL,
  `tgl_lahir` date NOT NULL,
  `berat_badan` float NOT NULL,
  `lingkar_kepala` int(11) NOT NULL,
  `panjang_badan` int(11) NOT NULL,
  `petugas` varchar(60) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_geolocation_presensi
# ------------------------------------------------------------

CREATE TABLE `mlite_geolocation_presensi` (
  `id` int(11) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `latitude` varchar(200) NOT NULL,
  `longitude` varchar(200) NOT NULL,
  KEY `mlite_geolocation_presensi_ibfk_1` (`id`),
  CONSTRAINT `mlite_geolocation_presensi_ibfk_1` FOREIGN KEY (`id`) REFERENCES `pegawai` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mlite_jurnal
# ------------------------------------------------------------

CREATE TABLE `mlite_jurnal` (
  `no_jurnal` varchar(20) NOT NULL,
  `no_bukti` varchar(20) DEFAULT NULL,
  `tgl_jurnal` date DEFAULT NULL,
  `jenis` enum('U','P') DEFAULT NULL,
  `kegiatan` varchar(250) NOT NULL,
  `keterangan` varchar(350) DEFAULT NULL,
  PRIMARY KEY (`no_jurnal`),
  KEY `no_bukti` (`no_bukti`),
  KEY `tgl_jurnal` (`tgl_jurnal`),
  KEY `jenis` (`jenis`),
  KEY `keterangan` (`keterangan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_login_attempts
# ------------------------------------------------------------

CREATE TABLE `mlite_login_attempts` (
  `ip` text,
  `attempts` int(100) NOT NULL,
  `expires` int(100) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table mlite_modules
# ------------------------------------------------------------

CREATE TABLE `mlite_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dir` text,
  `sequence` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `mlite_modules` WRITE;
/*!40000 ALTER TABLE `mlite_modules` DISABLE KEYS */;

INSERT INTO `mlite_modules` (`id`, `dir`, `sequence`)
VALUES
	(1,'settings','9'),
	(2,'dashboard','0'),
	(3,'master','1'),
	(4,'pasien','2'),
	(5,'rawat_jalan','3'),
	(6,'kasir_rawat_jalan','4'),
	(7,'kepegawaian','5'),
	(8,'farmasi','6'),
	(9,'users','8'),
	(10,'modules','7'),
	(11,'wagateway','10'),
	(12,'apotek_ralan','11'),
	(13,'dokter_ralan','12'),
	(14,'igd','13'),
	(15,'dokter_igd','14'),
	(16,'laboratorium','15'),
	(17,'radiologi','16'),
	(18,'icd_10','17'),
	(19,'rawat_inap','18'),
	(20,'apotek_ranap','19'),
	(21,'dokter_ranap','20'),
	(22,'kasir_rawat_inap','21'),
	(23,'operasi','22'),
	(24,'anjungan','23'),
	(25,'api','24'),
	(26,'jkn_mobile','25'),
	(27,'vclaim','26'),
	(28,'keuangan','27'),
	(29,'manajemen','28'),
	(30,'presensi','29'),
	(31,'vedika','30'),
	(32,'profil','31'),
	(33,'orthanc','32'),
	(34,'veronisa','33'),
	(35,'icd_9','34');

/*!40000 ALTER TABLE `mlite_modules` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table mlite_notifications
# ------------------------------------------------------------

CREATE TABLE `mlite_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(250) NOT NULL,
  `pesan` text NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `no_rkm_medis` varchar(255) NOT NULL,
  `status` varchar(250) NOT NULL DEFAULT 'unread',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_odontogram
# ------------------------------------------------------------

CREATE TABLE `mlite_odontogram` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_rkm_medis` text NOT NULL,
  `pemeriksaan` text,
  `kondisi` text,
  `catatan` text,
  `id_user` text NOT NULL,
  `tgl_input` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



# Dump of table mlite_ohis
# ------------------------------------------------------------

CREATE TABLE `mlite_ohis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_rkm_medis` text NOT NULL,
  `d_16` text,
  `d_11` text,
  `d_26` text,
  `d_36` text,
  `d_31` text,
  `d_46` text,
  `c_16` text,
  `c_11` text,
  `c_26` text,
  `c_36` text,
  `c_31` text,
  `c_46` text,
  `debris` text,
  `calculus` text,
  `nilai` text,
  `kriteria` text,
  `id_user` text NOT NULL,
  `tgl_input` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



# Dump of table mlite_pendaftaran_oral_diagnostic
# ------------------------------------------------------------

CREATE TABLE `mlite_pendaftaran_oral_diagnostic` (
  `no_reg` varchar(8) DEFAULT NULL,
  `no_rawat` varchar(17) NOT NULL,
  `tgl_registrasi` date DEFAULT NULL,
  `jam_reg` time DEFAULT NULL,
  `kd_dokter` varchar(20) DEFAULT NULL,
  `no_rkm_medis` varchar(15) DEFAULT NULL,
  `kd_poli` char(5) DEFAULT NULL,
  `p_jawab` varchar(100) DEFAULT NULL,
  `almt_pj` varchar(200) DEFAULT NULL,
  `hubunganpj` varchar(20) DEFAULT NULL,
  `biaya_reg` double DEFAULT NULL,
  `stts` enum('Belum','Sudah','Batal','Berkas Diterima','Dirujuk','Meninggal','Dirawat','Pulang Paksa') DEFAULT NULL,
  `stts_daftar` enum('-','Lama','Baru') NOT NULL,
  `status_lanjut` enum('Ralan','Ranap') NOT NULL,
  `kd_pj` char(3) NOT NULL,
  `umurdaftar` int(11) DEFAULT NULL,
  `sttsumur` enum('Th','Bl','Hr') DEFAULT NULL,
  `status_bayar` enum('Sudah Bayar','Belum Bayar') NOT NULL,
  `status_poli` enum('Lama','Baru') NOT NULL,
  PRIMARY KEY (`no_rawat`),
  KEY `no_rkm_medis` (`no_rkm_medis`),
  KEY `kd_poli` (`kd_poli`),
  KEY `kd_pj` (`kd_pj`),
  KEY `status_lanjut` (`status_lanjut`),
  KEY `kd_dokter` (`kd_dokter`),
  KEY `status_bayar` (`status_bayar`) USING BTREE,
  CONSTRAINT `mlite_pendaftaran_oral_diagnostic_ibfk_3` FOREIGN KEY (`kd_poli`) REFERENCES `poliklinik` (`kd_poli`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_pendaftaran_oral_diagnostic_ibfk_4` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_pendaftaran_oral_diagnostic_ibfk_6` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON UPDATE CASCADE,
  CONSTRAINT `mlite_pendaftaran_oral_diagnostic_ibfk_7` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_pengaduan
# ------------------------------------------------------------

CREATE TABLE `mlite_pengaduan` (
  `id` varchar(15) NOT NULL,
  `tanggal` datetime NOT NULL,
  `no_rkm_medis` varchar(15) NOT NULL,
  `pesan` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `no_rkm_medis` (`no_rkm_medis`),
  CONSTRAINT `mlite_pengaduan_ibfk_1` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_pengaduan_detail
# ------------------------------------------------------------

CREATE TABLE `mlite_pengaduan_detail` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `pengaduan_id` varchar(15) NOT NULL,
  `tanggal` datetime NOT NULL,
  `no_rkm_medis` varchar(15) NOT NULL,
  `pesan` varchar(225) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pengaduan_detail_ibfk_1` (`pengaduan_id`),
  CONSTRAINT `mlite_pengaduan_detail_ibfk_1` FOREIGN KEY (`pengaduan_id`) REFERENCES `mlite_pengaduan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_penilaian_awal_keperawatan_gigi
# ------------------------------------------------------------

CREATE TABLE `mlite_penilaian_awal_keperawatan_gigi` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `informasi` enum('Autoanamnesis','Alloanamnesis') NOT NULL,
  `td` varchar(8) NOT NULL DEFAULT '',
  `nadi` varchar(5) NOT NULL DEFAULT '',
  `rr` varchar(5) NOT NULL,
  `suhu` varchar(5) NOT NULL DEFAULT '',
  `bb` varchar(5) NOT NULL DEFAULT '',
  `tb` varchar(5) NOT NULL DEFAULT '',
  `bmi` varchar(10) NOT NULL,
  `keluhan_utama` varchar(150) NOT NULL DEFAULT '',
  `riwayat_penyakit` enum('Tidak Ada','Diabetes Melitus','Hipertensi','Penyakit Jantung','HIV','Hepatitis','Haemophilia','Lain-lain') DEFAULT NULL,
  `ket_riwayat_penyakit` varchar(30) NOT NULL,
  `alergi` varchar(25) NOT NULL DEFAULT '',
  `riwayat_perawatan_gigi` enum('Tidak','Ya, Kapan') NOT NULL,
  `ket_riwayat_perawatan_gigi` varchar(50) NOT NULL DEFAULT '',
  `kebiasaan_sikat_gigi` enum('1x','2x','3x','Mandi','Setelah Makan','Sebelum Tidur') NOT NULL,
  `kebiasaan_lain` enum('Tidak ada','Minum kopi/teh','Minum alkohol','Bruxism','Menggigit pensil','Mengunyah 1 sisi rahang','Merokok','Lain-lain') DEFAULT NULL,
  `ket_kebiasaan_lain` varchar(30) NOT NULL,
  `obat_yang_diminum_saatini` varchar(100) DEFAULT NULL,
  `alat_bantu` enum('Tidak','Ya') NOT NULL,
  `ket_alat_bantu` varchar(30) NOT NULL,
  `prothesa` enum('Tidak','Ya') NOT NULL,
  `ket_pro` varchar(50) NOT NULL,
  `status_psiko` enum('Tenang','Takut','Cemas','Depresi','Lain-lain') NOT NULL,
  `ket_psiko` varchar(70) NOT NULL,
  `hub_keluarga` enum('Baik','Tidak Baik') NOT NULL,
  `tinggal_dengan` enum('Sendiri','Orang Tua','Suami / Istri','Lainnya') NOT NULL,
  `ket_tinggal` varchar(40) NOT NULL,
  `ekonomi` enum('Baik','Cukup','Kurang') NOT NULL,
  `budaya` enum('Tidak Ada','Ada') NOT NULL,
  `ket_budaya` varchar(50) NOT NULL,
  `edukasi` enum('Pasien','Keluarga') NOT NULL,
  `ket_edukasi` varchar(50) NOT NULL,
  `berjalan_a` enum('Ya','Tidak') NOT NULL,
  `berjalan_b` enum('Ya','Tidak') NOT NULL,
  `berjalan_c` enum('Ya','Tidak') NOT NULL,
  `hasil` enum('Tidak beresiko (tidak ditemukan a dan b)','Resiko rendah (ditemukan a/b)','Resiko tinggi (ditemukan a dan b)') NOT NULL,
  `lapor` enum('Ya','Tidak') NOT NULL,
  `ket_lapor` varchar(15) NOT NULL,
  `nyeri` enum('Tidak Ada Nyeri','Nyeri Akut','Nyeri Kronis') NOT NULL,
  `lokasi` varchar(50) NOT NULL,
  `skala_nyeri` enum('0','1','2','3','4','5','6','7','8','9','10') NOT NULL,
  `durasi` varchar(25) NOT NULL,
  `frekuensi` varchar(25) NOT NULL,
  `nyeri_hilang` enum('Istirahat','Medengar Musik','Minum Obat','Tidak ada nyeri','Lain-lain') NOT NULL,
  `ket_nyeri` varchar(40) NOT NULL,
  `pada_dokter` enum('Tidak','Ya') NOT NULL,
  `ket_dokter` varchar(15) NOT NULL,
  `kebersihan_mulut` enum('Baik','Cukup','Kurang') NOT NULL,
  `mukosa_mulut` enum('Normal','Pigmentasi','Radang') NOT NULL,
  `karies` enum('Ada','Tidak') NOT NULL,
  `karang_gigi` enum('Ada','Tidak') NOT NULL,
  `gingiva` enum('Normal','Radang') NOT NULL,
  `palatum` enum('Normal','Radang') NOT NULL,
  `rencana` varchar(200) NOT NULL,
  `nip` varchar(20) NOT NULL,
  PRIMARY KEY (`no_rawat`) USING BTREE,
  KEY `nip` (`nip`) USING BTREE,
  CONSTRAINT `mlite_penilaian_awal_keperawatan_gigi_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_awal_keperawatan_gigi_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_penilaian_awal_keperawatan_ralan
# ------------------------------------------------------------

CREATE TABLE `mlite_penilaian_awal_keperawatan_ralan` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `informasi` enum('Autoanamnesis','Alloanamnesis') NOT NULL,
  `td` varchar(8) NOT NULL DEFAULT '',
  `nadi` varchar(5) NOT NULL DEFAULT '',
  `rr` varchar(5) NOT NULL,
  `suhu` varchar(5) NOT NULL DEFAULT '',
  `gcs` varchar(5) NOT NULL,
  `bb` varchar(5) NOT NULL DEFAULT '',
  `tb` varchar(5) NOT NULL DEFAULT '',
  `bmi` varchar(10) NOT NULL,
  `keluhan_utama` varchar(150) NOT NULL DEFAULT '',
  `rpd` varchar(100) NOT NULL DEFAULT '',
  `rpk` varchar(100) NOT NULL,
  `rpo` varchar(100) NOT NULL,
  `alergi` varchar(25) NOT NULL DEFAULT '',
  `alat_bantu` enum('Tidak','Ya') NOT NULL,
  `ket_bantu` varchar(50) NOT NULL DEFAULT '',
  `prothesa` enum('Tidak','Ya') NOT NULL,
  `ket_pro` varchar(50) NOT NULL,
  `adl` enum('Mandiri','Dibantu') NOT NULL,
  `status_psiko` enum('Tenang','Takut','Cemas','Depresi','Lain-lain') NOT NULL,
  `ket_psiko` varchar(70) NOT NULL,
  `hub_keluarga` enum('Baik','Tidak Baik') NOT NULL,
  `tinggal_dengan` enum('Sendiri','Orang Tua','Suami / Istri','Lainnya') NOT NULL,
  `ket_tinggal` varchar(40) NOT NULL,
  `ekonomi` enum('Baik','Cukup','Kurang') NOT NULL,
  `budaya` enum('Tidak Ada','Ada') NOT NULL,
  `ket_budaya` varchar(50) NOT NULL,
  `edukasi` enum('Pasien','Keluarga') NOT NULL,
  `ket_edukasi` varchar(50) NOT NULL,
  `berjalan_a` enum('Ya','Tidak') NOT NULL,
  `berjalan_b` enum('Ya','Tidak') NOT NULL,
  `berjalan_c` enum('Ya','Tidak') NOT NULL,
  `hasil` enum('Tidak beresiko (tidak ditemukan a dan b)','Resiko rendah (ditemukan a/b)','Resiko tinggi (ditemukan a dan b)') NOT NULL,
  `lapor` enum('Ya','Tidak') NOT NULL,
  `ket_lapor` varchar(15) NOT NULL,
  `sg1` enum('Tidak','Tidak Yakin','Ya, 1-5 Kg','Ya, 6-10 Kg','Ya, 11-15 Kg','Ya, >15 Kg') NOT NULL,
  `nilai1` enum('0','1','2','3','4') NOT NULL,
  `sg2` enum('Ya','Tidak') NOT NULL,
  `nilai2` enum('0','1') NOT NULL,
  `total_hasil` tinyint(4) NOT NULL,
  `nyeri` enum('Tidak Ada Nyeri','Nyeri Akut','Nyeri Kronis') NOT NULL,
  `provokes` enum('Proses Penyakit','Benturan','Lain-lain') NOT NULL,
  `ket_provokes` varchar(40) NOT NULL,
  `quality` enum('Seperti Tertusuk','Berdenyut','Teriris','Tertindih','Tertiban','Lain-lain') NOT NULL,
  `ket_quality` varchar(50) NOT NULL,
  `lokasi` varchar(50) NOT NULL,
  `menyebar` enum('Tidak','Ya') NOT NULL,
  `skala_nyeri` enum('0','1','2','3','4','5','6','7','8','9','10') NOT NULL,
  `durasi` varchar(25) NOT NULL,
  `nyeri_hilang` enum('Istirahat','Medengar Musik','Minum Obat') NOT NULL,
  `ket_nyeri` varchar(40) NOT NULL,
  `pada_dokter` enum('Tidak','Ya') NOT NULL,
  `ket_dokter` varchar(15) NOT NULL,
  `rencana` varchar(200) NOT NULL,
  `nip` varchar(20) NOT NULL,
  PRIMARY KEY (`no_rawat`) USING BTREE,
  KEY `nip` (`nip`) USING BTREE,
  CONSTRAINT `mlite_penilaian_awal_keperawatan_ralan_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_awal_keperawatan_ralan_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_penilaian_awal_keperawatan_ranap
# ------------------------------------------------------------

CREATE TABLE `mlite_penilaian_awal_keperawatan_ranap` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `informasi` enum('Autoanamnesis','Alloanamnesis') NOT NULL,
  `ket_informasi` varchar(30) NOT NULL,
  `tiba_diruang_rawat` enum('Jalan Tanpa Bantuan','Kursi Roda','Brankar') NOT NULL,
  `kasus_trauma` enum('Trauma','Non Trauma') DEFAULT NULL,
  `cara_masuk` enum('Poli','IGD','Lain-lain') NOT NULL,
  `rps` varchar(300) NOT NULL,
  `rpd` varchar(100) NOT NULL,
  `rpk` varchar(100) NOT NULL,
  `rpo` varchar(100) NOT NULL,
  `riwayat_pembedahan` varchar(40) NOT NULL,
  `riwayat_dirawat_dirs` varchar(40) NOT NULL,
  `alat_bantu_dipakai` enum('Kacamata','Prothesa','Alat Bantu Dengar','Lain-lain') NOT NULL,
  `riwayat_kehamilan` enum('Tidak','Ya') NOT NULL,
  `riwayat_kehamilan_perkiraan` varchar(30) NOT NULL,
  `riwayat_tranfusi` varchar(40) NOT NULL,
  `riwayat_alergi` varchar(40) NOT NULL,
  `riwayat_merokok` enum('Tidak','Ya') NOT NULL,
  `riwayat_merokok_jumlah` varchar(5) NOT NULL,
  `riwayat_alkohol` enum('Tidak','Ya') NOT NULL,
  `riwayat_alkohol_jumlah` varchar(5) NOT NULL,
  `riwayat_narkoba` enum('Tidak','Ya') NOT NULL,
  `riwayat_olahraga` enum('Tidak','Ya') NOT NULL,
  `pemeriksaan_mental` varchar(40) NOT NULL,
  `pemeriksaan_keadaan_umum` enum('Baik','Sedang','Buruk') NOT NULL,
  `pemeriksaan_gcs` varchar(10) NOT NULL,
  `pemeriksaan_td` varchar(8) NOT NULL,
  `pemeriksaan_nadi` varchar(5) NOT NULL,
  `pemeriksaan_rr` varchar(5) NOT NULL,
  `pemeriksaan_suhu` varchar(5) NOT NULL,
  `pemeriksaan_spo2` varchar(5) NOT NULL,
  `pemeriksaan_bb` varchar(5) NOT NULL,
  `pemeriksaan_tb` varchar(5) NOT NULL,
  `pemeriksaan_susunan_kepala` enum('TAK','Hydrocephalus','Hematoma','Lain-lain') NOT NULL,
  `pemeriksaan_susunan_wajah` enum('TAK','Asimetris','Kelainan Kongenital') NOT NULL,
  `pemeriksaan_susunan_leher` enum('TAK','Kaku Kuduk','Pembesaran Thyroid','Pembesaran KGB') NOT NULL,
  `pemeriksaan_susunan_kejang` enum('TAK','Kuat','Ada') NOT NULL,
  `pemeriksaan_susunan_sensorik` enum('TAK','Sakit Nyeri','Rasa kebas') NOT NULL,
  `pemeriksaan_kardiovaskuler_denyut_nadi` enum('Teratur','Tidak Teratur') NOT NULL,
  `pemeriksaan_kardiovaskuler_sirkulasi` enum('Akral Hangat','Akral Dingin','Edema') NOT NULL,
  `pemeriksaan_kardiovaskuler_pulsasi` enum('Kuat','Lemah','Lain-lain') NOT NULL,
  `pemeriksaan_respirasi_pola_nafas` enum('Normal','Bradipnea','Tachipnea') NOT NULL,
  `pemeriksaan_respirasi_retraksi` enum('Tidak Ada','Ringan','Berat') NOT NULL,
  `pemeriksaan_respirasi_suara_nafas` enum('Vesikuler','Wheezing','Rhonki') NOT NULL,
  `pemeriksaan_respirasi_volume_pernafasan` enum('Normal','Hiperventilasi','Hipoventilasi') NOT NULL,
  `pemeriksaan_respirasi_jenis_pernafasan` enum('Pernafasan Dada','Alat Bantu Pernafasaan') NOT NULL,
  `pemeriksaan_respirasi_irama_nafas` enum('Teratur','Tidak Teratur') NOT NULL,
  `pemeriksaan_respirasi_batuk` enum('Tidak','Ya : Produktif','Ya : Non Produktif') NOT NULL,
  `pemeriksaan_gastrointestinal_mulut` enum('TAK','Stomatitis','Mukosa Kering','Bibir Pucat','Lain-lain') NOT NULL,
  `pemeriksaan_gastrointestinal_gigi` enum('TAK','Karies','Goyang','Lain-lain') NOT NULL,
  `pemeriksaan_gastrointestinal_lidah` enum('TAK','Kotor','Gerak Asimetris','Lain-lain') NOT NULL,
  `pemeriksaan_gastrointestinal_tenggorokan` enum('TAK','Gangguan Menelan','Sakit Menelan','Lain-lain') NOT NULL,
  `pemeriksaan_gastrointestinal_abdomen` enum('Supel','Asictes',' Tegang','Nyeri Tekan/Lepas','Lain-lain') NOT NULL,
  `pemeriksaan_gastrointestinal_peistatik_usus` enum('TAK','Tidak Ada Bising Usus','Hiperistaltik') NOT NULL,
  `pemeriksaan_gastrointestinal_anus` enum('TAK','Atresia Ani') NOT NULL,
  `pemeriksaan_neurologi_pengelihatan` enum('TAK','Ada Kelainan') NOT NULL,
  `pemeriksaan_neurologi_alat_bantu_penglihatan` enum('Tidak','Kacamata','Lensa Kontak') NOT NULL,
  `pemeriksaan_neurologi_pendengaran` enum('TAK','Berdengung','Nyeri','Tuli','Keluar Cairan','Lain-lain') NOT NULL,
  `pemeriksaan_neurologi_bicara` enum('Jelas','Tidak Jelas') NOT NULL,
  `pemeriksaan_neurologi_sensorik` enum('TAK','Sakit Nyeri','Rasa Kebas','Lain-lain') NOT NULL,
  `pemeriksaan_neurologi_motorik` enum('TAK','Hemiparese','Tetraparese','Tremor','Lain-lain') NOT NULL,
  `pemeriksaan_neurologi_kekuatan_otot` enum('Kuat','Lemah') NOT NULL,
  `pemeriksaan_integument_warnakulit` enum('Pucat','Sianosis','Normal','Lain-lain') NOT NULL,
  `pemeriksaan_integument_turgor` enum('Baik','Sedang','Buruk') NOT NULL,
  `pemeriksaan_integument_kulit` enum('Normal','Rash/Kemerahan','Luka','Memar','Ptekie','Bula') NOT NULL,
  `pemeriksaan_integument_dekubitas` enum('Tidak Ada','Usia > 65 tahun','Obesitas','Imobilisasi','Paraplegi/Vegetative State','Dirawat Di HCU','Penyakit Kronis (DM, CHF, CKD)','Inkontinentia Uri/Alvi') NOT NULL,
  `pemeriksaan_muskuloskletal_pergerakan_sendi` enum('Bebas','Terbatas') NOT NULL,
  `pemeriksaan_muskuloskletal_kekauatan_otot` enum('Baik','Lemah','Tremor') NOT NULL,
  `pemeriksaan_muskuloskletal_nyeri_sendi` enum('Tidak Ada','Ada') NOT NULL,
  `pemeriksaan_muskuloskletal_oedema` enum('Tidak Ada','Ada') NOT NULL,
  `pemeriksaan_muskuloskletal_fraktur` enum('Tidak Ada','Ada') NOT NULL,
  `pemeriksaan_eliminasi_bab_frekuensi_jumlah` varchar(5) NOT NULL,
  `pemeriksaan_eliminasi_bab_frekuensi_durasi` varchar(10) NOT NULL,
  `pemeriksaan_eliminasi_bab_konsistensi` varchar(30) NOT NULL,
  `pemeriksaan_eliminasi_bab_warna` varchar(30) NOT NULL,
  `pemeriksaan_eliminasi_bak_frekuensi_jumlah` varchar(5) NOT NULL,
  `pemeriksaan_eliminasi_bak_frekuensi_durasi` varchar(10) NOT NULL,
  `pemeriksaan_eliminasi_bak_warna` varchar(30) NOT NULL,
  `pemeriksaan_eliminasi_bak_lainlain` varchar(30) NOT NULL,
  `pola_aktifitas_makanminum` enum('Mandiri','Bantuan Orang Lain') NOT NULL,
  `pola_aktifitas_mandi` enum('Mandiri','Bantuan Orang Lain') NOT NULL,
  `pola_aktifitas_eliminasi` enum('Mandiri','Bantuan Orang Lain') NOT NULL,
  `pola_aktifitas_berpakaian` enum('Mandiri','Bantuan Orang Lain') NOT NULL,
  `pola_aktifitas_berpindah` enum('Mandiri','Bantuan Orang Lain') NOT NULL,
  `pola_nutrisi_frekuesi_makan` varchar(3) NOT NULL,
  `pola_nutrisi_jenis_makanan` varchar(20) NOT NULL,
  `pola_nutrisi_porsi_makan` varchar(3) NOT NULL,
  `pola_tidur_lama_tidur` varchar(3) NOT NULL,
  `pola_tidur_gangguan` enum('Tidak Ada Gangguan','Insomnia') NOT NULL,
  `pengkajian_fungsi_kemampuan_sehari` enum('Mandiri','Bantuan Minimal','Bantuan Sebagian','Ketergantungan Total') NOT NULL,
  `pengkajian_fungsi_aktifitas` enum('Tirah Baring','Duduk','Berjalan') NOT NULL,
  `pengkajian_fungsi_berjalan` enum('TAK','Penurunan Kekuatan/ROM','Paralisis','Sering Jatuh','Deformitas','Hilang Keseimbangan','Riwayat Patah Tulang','Lain-lain') NOT NULL,
  `pengkajian_fungsi_ambulasi` enum('Walker','Tongkat','Kursi Roda','Tidak Menggunakan') NOT NULL,
  `pengkajian_fungsi_ekstrimitas_atas` enum('TAK','Lemah','Oedema','Tidak Simetris','Lain-lain') NOT NULL,
  `pengkajian_fungsi_ekstrimitas_bawah` enum('TAK','Varises','Oedema','Tidak Simetris','Lain-lain') NOT NULL,
  `pengkajian_fungsi_menggenggam` enum('Tidak Ada Kesulitan','Terakhir','Lain-lain') NOT NULL,
  `pengkajian_fungsi_koordinasi` enum('Tidak Ada Kesulitan','Ada Masalah') NOT NULL,
  `pengkajian_fungsi_kesimpulan` enum('Ya (Co DPJP)','Tidak (Tidak Perlu Co DPJP)') NOT NULL,
  `riwayat_psiko_kondisi_psiko` enum('Tidak Ada Masalah','Marah','Takut','Depresi','Cepat Lelah','Cemas','Gelisah','Sulit Tidur','Lain-lain') NOT NULL,
  `riwayat_psiko_gangguan_jiwa` enum('Ya','Tidak') NOT NULL,
  `riwayat_psiko_perilaku` enum('Tidak Ada Masalah','Perilaku Kekerasan','Gangguan Efek','Gangguan Memori','Halusinasi','Kecenderungan Percobaan Bunuh Diri','Lain-lain') NOT NULL,
  `riwayat_psiko_hubungan_keluarga` enum('Harmonis','Kurang Harmonis','Tidak Harmonis','Konflik Besar') NOT NULL,
  `riwayat_psiko_tinggal` enum('Sendiri','Orang Tua','Suami/Istri','Keluarga','Lain-lain') NOT NULL,
  `riwayat_psiko_nilai_kepercayaan` enum('Tidak Ada','Ada') NOT NULL,
  `riwayat_psiko_pendidikan_pj` enum('-','TS','TK','SD','SMP','SMA','SLTA/SEDERAJAT','D1','D2','D3','D4','S1','S2','S3') NOT NULL,
  `riwayat_psiko_edukasi_diberikan` enum('Pasien','Keluarga') NOT NULL,
  `penilaian_nyeri` enum('Tidak Ada Nyeri','Nyeri Akut','Nyeri Kronis') NOT NULL,
  `penilaian_nyeri_penyebab` enum('Proses Penyakit','Benturan','Lain-lain') NOT NULL,
  `penilaian_nyeri_kualitas` enum('Seperti Tertusuk','Berdenyut','Teriris','Tertindih','Tertiban','Lain-lain') NOT NULL,
  `penilaian_nyeri_lokasi` varchar(50) NOT NULL,
  `penilaian_nyeri_menyebar` enum('Tidak','Ya') NOT NULL,
  `penilaian_nyeri_skala` enum('0','1','2','3','4','5','6','7','8','9','10') NOT NULL,
  `penilaian_nyeri_waktu` varchar(5) NOT NULL,
  `penilaian_nyeri_hilang` enum('Istirahat','Medengar Musik','Minum Obat') NOT NULL,
  `penilaian_nyeri_diberitahukan_dokter` enum('Tidak','Ya') NOT NULL,
  `penilaian_nyeri_jam_diberitahukan_dokter` varchar(10) NOT NULL,
  `penilaian_jatuhmorse_skala1` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhmorse_nilai1` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhmorse_skala2` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhmorse_nilai2` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhmorse_skala3` enum('Tidak Ada/Kursi Roda/Perawat/Tirah Baring','Tongkat/Alat Penopang','Berpegangan Pada Perabot') DEFAULT NULL,
  `penilaian_jatuhmorse_nilai3` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhmorse_skala4` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhmorse_nilai4` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhmorse_skala5` enum('Normal/Tirah Baring/Imobilisasi','Lemah','Terganggu') DEFAULT NULL,
  `penilaian_jatuhmorse_nilai5` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhmorse_skala6` enum('Sadar Akan Kemampuan Diri Sendiri','Sering Lupa Akan Keterbatasan Yang Dimiliki') DEFAULT NULL,
  `penilaian_jatuhmorse_nilai6` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhmorse_totalnilai` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala1` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai1` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala2` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai2` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala3` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai3` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala4` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai4` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala5` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai5` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala6` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai6` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala7` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai7` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala8` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai8` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala9` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai9` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala10` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai10` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_skala11` enum('Tidak','Ya') DEFAULT NULL,
  `penilaian_jatuhsydney_nilai11` tinyint(4) DEFAULT NULL,
  `penilaian_jatuhsydney_totalnilai` tinyint(4) DEFAULT NULL,
  `skrining_gizi1` enum('Tidak ada penurunan berat badan','Tidak yakin/ tidak tahu/ terasa baju lebih longgar','Ya 1-5 kg','Ya 6-10 kg','Ya 11-15 kg','Ya > 15 kg') DEFAULT NULL,
  `nilai_gizi1` int(11) DEFAULT NULL,
  `skrining_gizi2` enum('Tidak','Ya') DEFAULT NULL,
  `nilai_gizi2` int(11) DEFAULT NULL,
  `nilai_total_gizi` double DEFAULT NULL,
  `skrining_gizi_diagnosa_khusus` enum('Tidak','Ya') DEFAULT NULL,
  `skrining_gizi_diketahui_dietisen` enum('Tidak','Ya') DEFAULT NULL,
  `skrining_gizi_jam_diketahui_dietisen` varchar(10) DEFAULT NULL,
  `rencana` varchar(200) DEFAULT NULL,
  `nip1` varchar(20) NOT NULL,
  `nip2` varchar(20) NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  PRIMARY KEY (`no_rawat`) USING BTREE,
  KEY `nip1` (`nip1`) USING BTREE,
  KEY `nip2` (`nip2`) USING BTREE,
  KEY `kd_dokter` (`kd_dokter`) USING BTREE,
  CONSTRAINT `mlite_penilaian_awal_keperawatan_ranap_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_awal_keperawatan_ranap_ibfk_2` FOREIGN KEY (`nip1`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_awal_keperawatan_ranap_ibfk_3` FOREIGN KEY (`nip2`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_awal_keperawatan_ranap_ibfk_4` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_penilaian_medis_igd
# ------------------------------------------------------------

CREATE TABLE `mlite_penilaian_medis_igd` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `anamnesis` enum('Autoanamnesis','Alloanamnesis') NOT NULL,
  `hubungan` varchar(100) NOT NULL,
  `keluhan_utama` varchar(2000) NOT NULL DEFAULT '',
  `rps` varchar(2000) NOT NULL,
  `rpd` varchar(1000) NOT NULL DEFAULT '',
  `rpk` varchar(1000) NOT NULL,
  `rpo` varchar(1000) NOT NULL,
  `alergi` varchar(100) NOT NULL DEFAULT '',
  `keadaan` enum('Sehat','Sakit Ringan','Sakit Sedang','Sakit Berat') NOT NULL,
  `gcs` varchar(10) NOT NULL,
  `kesadaran` enum('Compos Mentis','Apatis','Somnolen','Sopor','Koma') NOT NULL,
  `td` varchar(8) NOT NULL DEFAULT '',
  `nadi` varchar(5) NOT NULL DEFAULT '',
  `rr` varchar(5) NOT NULL,
  `suhu` varchar(5) NOT NULL DEFAULT '',
  `spo` varchar(5) NOT NULL,
  `bb` varchar(5) NOT NULL DEFAULT '',
  `tb` varchar(5) NOT NULL DEFAULT '',
  `kepala` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `mata` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `gigi` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `leher` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `thoraks` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `abdomen` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `genital` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `ekstremitas` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `ket_fisik` text NOT NULL,
  `ket_lokalis` text NOT NULL,
  `ekg` text NOT NULL,
  `rad` text NOT NULL,
  `lab` text NOT NULL,
  `diagnosis` varchar(500) NOT NULL,
  `tata` text NOT NULL,
  PRIMARY KEY (`no_rawat`) USING BTREE,
  KEY `kd_dokter` (`kd_dokter`) USING BTREE,
  CONSTRAINT `mlite_penilaian_medis_igd_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_medis_igd_ibfk_2` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_penilaian_medis_ralan
# ------------------------------------------------------------

CREATE TABLE `mlite_penilaian_medis_ralan` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `anamnesis` enum('Autoanamnesis','Alloanamnesis') NOT NULL,
  `hubungan` varchar(30) NOT NULL,
  `keluhan_utama` varchar(2000) NOT NULL DEFAULT '',
  `rps` varchar(2000) NOT NULL,
  `rpd` varchar(1000) NOT NULL DEFAULT '',
  `rpk` varchar(1000) NOT NULL,
  `rpo` varchar(1000) NOT NULL,
  `alergi` varchar(50) NOT NULL DEFAULT '',
  `keadaan` enum('Sehat','Sakit Ringan','Sakit Sedang','Sakit Berat') NOT NULL,
  `gcs` varchar(10) NOT NULL,
  `kesadaran` enum('Compos Mentis','Apatis','Somnolen','Sopor','Koma') NOT NULL,
  `td` varchar(8) NOT NULL DEFAULT '',
  `nadi` varchar(5) NOT NULL DEFAULT '',
  `rr` varchar(5) NOT NULL,
  `suhu` varchar(5) NOT NULL DEFAULT '',
  `spo` varchar(5) NOT NULL,
  `bb` varchar(5) NOT NULL DEFAULT '',
  `tb` varchar(5) NOT NULL DEFAULT '',
  `kepala` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `gigi` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `tht` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `thoraks` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `abdomen` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `genital` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `ekstremitas` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `kulit` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `ket_fisik` text NOT NULL,
  `ket_lokalis` text NOT NULL,
  `penunjang` text NOT NULL,
  `diagnosis` varchar(500) NOT NULL,
  `tata` text NOT NULL,
  `konsulrujuk` varchar(1000) NOT NULL,
  PRIMARY KEY (`no_rawat`) USING BTREE,
  KEY `kd_dokter` (`kd_dokter`) USING BTREE,
  CONSTRAINT `mlite_penilaian_medis_ralan_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_medis_ralan_ibfk_2` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_penilaian_medis_ranap
# ------------------------------------------------------------

CREATE TABLE `mlite_penilaian_medis_ranap` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `anamnesis` enum('Autoanamnesis','Alloanamnesis') NOT NULL,
  `hubungan` varchar(100) NOT NULL,
  `keluhan_utama` varchar(2000) NOT NULL DEFAULT '',
  `rps` varchar(2000) NOT NULL,
  `rpd` varchar(1000) NOT NULL DEFAULT '',
  `rpk` varchar(1000) NOT NULL,
  `rpo` varchar(1000) NOT NULL,
  `alergi` varchar(100) NOT NULL DEFAULT '',
  `keadaan` enum('Sehat','Sakit Ringan','Sakit Sedang','Sakit Berat') NOT NULL,
  `gcs` varchar(10) NOT NULL,
  `kesadaran` enum('Compos Mentis','Apatis','Somnolen','Sopor','Koma') NOT NULL,
  `td` varchar(8) NOT NULL DEFAULT '',
  `nadi` varchar(5) NOT NULL DEFAULT '',
  `rr` varchar(5) NOT NULL,
  `suhu` varchar(5) NOT NULL DEFAULT '',
  `spo` varchar(5) NOT NULL,
  `bb` varchar(5) NOT NULL DEFAULT '',
  `tb` varchar(5) NOT NULL DEFAULT '',
  `kepala` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `mata` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `gigi` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `tht` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `thoraks` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `jantung` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `paru` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `abdomen` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `genital` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `ekstremitas` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `kulit` enum('Normal','Abnormal','Tidak Diperiksa') NOT NULL,
  `ket_fisik` text NOT NULL,
  `ket_lokalis` text NOT NULL,
  `lab` text NOT NULL,
  `rad` text NOT NULL,
  `penunjang` text NOT NULL,
  `diagnosis` varchar(500) NOT NULL,
  `tata` text NOT NULL,
  `edukasi` varchar(1000) NOT NULL,
  PRIMARY KEY (`no_rawat`) USING BTREE,
  KEY `kd_dokter` (`kd_dokter`) USING BTREE,
  CONSTRAINT `mlite_penilaian_medis_ranap_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_medis_ranap_ibfk_2` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_penilaian_ulang_nyeri
# ------------------------------------------------------------

CREATE TABLE `mlite_penilaian_ulang_nyeri` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `nyeri` enum('Tidak Ada Nyeri','Nyeri Akut','Nyeri Kronis') NOT NULL,
  `provokes` enum('Proses Penyakit','Benturan','Lain-lain','-') NOT NULL,
  `ket_provokes` varchar(40) NOT NULL,
  `quality` enum('Seperti Tertusuk','Berdenyut','Teriris','Tertindih','Tertiban','Lain-lain','-') NOT NULL,
  `ket_quality` varchar(50) NOT NULL,
  `lokasi` varchar(50) NOT NULL,
  `menyebar` enum('Tidak','Ya') NOT NULL,
  `skala_nyeri` enum('0','1','2','3','4','5','6','7','8','9','10') NOT NULL,
  `durasi` varchar(25) NOT NULL,
  `nyeri_hilang` enum('Istirahat','Medengar Musik','Minum Obat','-') NOT NULL,
  `ket_nyeri` varchar(40) NOT NULL,
  `manajemen_nyeri` varchar(1000) DEFAULT NULL,
  `nip` varchar(20) NOT NULL,
  PRIMARY KEY (`no_rawat`,`tanggal`) USING BTREE,
  KEY `nip` (`nip`) USING BTREE,
  CONSTRAINT `mlite_penilaian_ulang_nyeri_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_penilaian_ulang_nyeri_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_penjualan
# ------------------------------------------------------------

CREATE TABLE `mlite_penjualan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pembeli` varchar(100) DEFAULT NULL,
  `alamat_pembeli` varchar(100) DEFAULT NULL,
  `nomor_telepon` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `id_user` varchar(50) NOT NULL,
  `keterangan` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table mlite_penjualan_barang
# ------------------------------------------------------------

CREATE TABLE `mlite_penjualan_barang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_barang` varchar(100) DEFAULT NULL,
  `stok` varchar(100) DEFAULT NULL,
  `harga` varchar(100) DEFAULT NULL,
  `keterangan` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_penjualan_billing
# ------------------------------------------------------------

CREATE TABLE `mlite_penjualan_billing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_penjualan` int(11) NOT NULL,
  `jumlah_total` int(100) NOT NULL,
  `potongan` int(100) DEFAULT NULL,
  `jumlah_harus_bayar` int(100) NOT NULL,
  `jumlah_bayar` int(100) NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `id_user` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table mlite_penjualan_detail
# ------------------------------------------------------------

CREATE TABLE `mlite_penjualan_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_penjualan` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL,
  `jumlah` int(100) NOT NULL,
  `harga_total` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `id_user` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table mlite_peta_mukosa_rongga_mulut
# ------------------------------------------------------------

CREATE TABLE `mlite_peta_mukosa_rongga_mulut` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `kelainan` text,
  `gambar` text,
  `nip` varchar(20) NOT NULL,
  PRIMARY KEY (`no_rawat`) USING BTREE,
  KEY `nip` (`nip`) USING BTREE,
  CONSTRAINT `mlite_peta_mukosa_rongga_mulut_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mlite_peta_mukosa_rongga_mulut_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_rekening
# ------------------------------------------------------------

CREATE TABLE `mlite_rekening` (
  `kd_rek` varchar(15) NOT NULL DEFAULT '',
  `nm_rek` varchar(100) DEFAULT NULL,
  `tipe` enum('N','M','R') DEFAULT NULL,
  `balance` enum('D','K') DEFAULT NULL,
  `level` enum('0','1') DEFAULT NULL,
  PRIMARY KEY (`kd_rek`),
  KEY `nm_rek` (`nm_rek`),
  KEY `tipe` (`tipe`),
  KEY `balance` (`balance`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_rekeningtahun
# ------------------------------------------------------------

CREATE TABLE `mlite_rekeningtahun` (
  `thn` year(4) NOT NULL,
  `kd_rek` varchar(15) NOT NULL DEFAULT '',
  `saldo_awal` double NOT NULL,
  PRIMARY KEY (`thn`,`kd_rek`),
  KEY `kd_rek` (`kd_rek`),
  KEY `saldo_awal` (`saldo_awal`),
  CONSTRAINT `mlite_rekeningtahun_ibfk_1` FOREIGN KEY (`kd_rek`) REFERENCES `mlite_rekening` (`kd_rek`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_remember_me
# ------------------------------------------------------------

CREATE TABLE `mlite_remember_me` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` text,
  `user_id` int(10) NOT NULL,
  `expiry` int(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mlite_remember_me_ibfk_1` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table mlite_satu_sehat_departemen
# ------------------------------------------------------------

CREATE TABLE `mlite_satu_sehat_departemen` (
  `dep_id` char(4) NOT NULL,
  `id_organisasi_satusehat` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`dep_id`),
  UNIQUE KEY `id_organisasi_satusehat` (`id_organisasi_satusehat`),
  CONSTRAINT `mlite_satu_sehat_departemen_ibfk_1` FOREIGN KEY (`dep_id`) REFERENCES `departemen` (`dep_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_satu_sehat_lokasi
# ------------------------------------------------------------

CREATE TABLE `mlite_satu_sehat_lokasi` (
  `kode` char(5) NOT NULL,
  `lokasi` varchar(40) DEFAULT NULL,
  `id_organisasi_satusehat` varchar(40) DEFAULT NULL,
  `id_lokasi_satusehat` varchar(40) DEFAULT NULL,
  `longitude` varchar(30) NOT NULL,
  `latitude` varchar(30) NOT NULL,
  `altitude` varchar(30) NOT NULL,
  PRIMARY KEY (`kode`),
  UNIQUE KEY `id_lokasi_satusehat` (`id_lokasi_satusehat`),
  KEY `id_organisasi_satusehat` (`id_organisasi_satusehat`),
  CONSTRAINT `mlite_satu_sehat_lokasi_ibfk_2` FOREIGN KEY (`id_organisasi_satusehat`) REFERENCES `mlite_satu_sehat_departemen` (`id_organisasi_satusehat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_satu_sehat_mapping_praktisi
# ------------------------------------------------------------

CREATE TABLE `mlite_satu_sehat_mapping_praktisi` (
  `practitioner_id` varchar(40) NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `jenis_praktisi` varchar(20) NOT NULL,
  PRIMARY KEY (`practitioner_id`),
  KEY `kd_dokter` (`kd_dokter`),
  CONSTRAINT `mlite_satu_sehat_mapping_praktisi_ibfk_1` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_satu_sehat_response
# ------------------------------------------------------------

CREATE TABLE `mlite_satu_sehat_response` (
  `no_rawat` varchar(17) NOT NULL,
  `id_encounter` varchar(50) DEFAULT NULL,
  `id_condition` varchar(50) DEFAULT NULL,
  `id_observation_ttvnadi` varchar(50) DEFAULT NULL,
  `id_observation_ttvrespirasi` varchar(50) DEFAULT NULL,
  `id_observation_ttvsuhu` varchar(50) DEFAULT NULL,
  `id_observation_ttvspo2` varchar(50) DEFAULT NULL,
  `id_observation_ttvgcs` varchar(50) DEFAULT NULL,
  `id_observation_ttvtinggi` varchar(50) DEFAULT NULL,
  `id_observation_ttvberat` varchar(50) DEFAULT NULL,
  `id_observation_ttvperut` varchar(50) DEFAULT NULL,
  `id_observation_ttvtensi` varchar(50) DEFAULT NULL,
  `id_observation_ttvkesadaran` varchar(50) DEFAULT NULL,
  `id_procedure` varchar(50) DEFAULT NULL,
  `id_clinical_impression` varchar(50) DEFAULT NULL,
  `id_composition` varchar(50) DEFAULT NULL,
  `id_immunization` varchar(50) DEFAULT NULL,
  `id_medication_request` varchar(50) DEFAULT NULL,
  `id_medication_dispense` varchar(50) DEFAULT NULL,
  `id_medication_statement` varchar(50) DEFAULT NULL,
  `id_rad_request` varchar(50) DEFAULT NULL,
  `id_rad_specimen` varchar(50) DEFAULT NULL,
  `id_rad_observation` varchar(50) DEFAULT NULL,
  `id_rad_diagnostic` varchar(50) DEFAULT NULL,
  `id_lab_pk_request` varchar(50) DEFAULT NULL,
  `id_lab_pk_specimen` varchar(50) DEFAULT NULL,
  `id_lab_pk_observation` varchar(50) DEFAULT NULL,
  `id_lab_pk_diagnostic` varchar(50) DEFAULT NULL,
  `id_lab_pa_request` varchar(50) DEFAULT NULL,
  `id_lab_pa_specimen` varchar(50) DEFAULT NULL,
  `id_lab_pa_observation` varchar(50) DEFAULT NULL,
  `id_lab_pa_diagnostic` varchar(50) DEFAULT NULL,
  `id_lab_mb_request` varchar(50) DEFAULT NULL,
  `id_lab_mb_specimen` varchar(50) DEFAULT NULL,
  `id_lab_mb_observation` varchar(50) DEFAULT NULL,
  `id_lab_mb_diagnostic` varchar(50) DEFAULT NULL,
  `id_careplan` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`no_rawat`),
  CONSTRAINT `mlite_satu_sehat_response_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

# Dump of table mlite_satu_sehat_mapping_obat
# ------------------------------------------------------------

CREATE TABLE `mlite_satu_sehat_mapping_obat` (
  `kode_brng` varchar(15) NOT NULL DEFAULT '',
  `kode_kfa` varchar(50) DEFAULT NULL,
  `nama_kfa` varchar(100) DEFAULT NULL,
  `kode_bahan` varchar(50) DEFAULT NULL,
  `nama_bahan` varchar(100) DEFAULT NULL,
  `numerator` varchar(10) DEFAULT NULL,
  `satuan_num` varchar(10) DEFAULT NULL,
  `denominator` varchar(10) DEFAULT NULL,
  `satuan_den` varchar(10) DEFAULT NULL,
  `nama_satuan_den` varchar(10) DEFAULT NULL,
  `kode_sediaan` varchar(50) DEFAULT NULL,
  `nama_sediaan` varchar(100) DEFAULT NULL,
  `kode_route` varchar(10) DEFAULT NULL,
  `nama_route` varchar(50) DEFAULT NULL,
  `type` enum('obat','vaksin') NOT NULL,
  `id_medication` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`kode_brng`),
  CONSTRAINT `mlite_satu_sehat_mapping_obat_ibfk_1` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


# Dump of table mlite_settings
# ------------------------------------------------------------

CREATE TABLE `mlite_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(100) NOT NULL,
  `field` varchar(100) NOT NULL,
  `value` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module` (`module`,`field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `mlite_settings` WRITE;
/*!40000 ALTER TABLE `mlite_settings` DISABLE KEYS */;

INSERT INTO `mlite_settings` (`id`, `module`, `field`, `value`)
VALUES
	(1,'settings','logo','uploads/settings/logo.png'),
	(2,'settings','nama_instansi','mLITE Indonesia'),
	(3,'settings','alamat','Jl. Perintis Kemerdekaan 45'),
	(4,'settings','kota','Barabai'),
	(5,'settings','propinsi','Kalimantan Selatan'),
	(6,'settings','nomor_telepon','0812345678'),
	(7,'settings','email','info@mlite.id'),
	(8,'settings','website','https://mlite.id'),
	(9,'settings','ppk_bpjs','-'),
	(10,'settings','footer','Copyright {?=date(\"Y\")?} &copy; by drg. F. Basoro. All rights reserved.'),
	(11,'settings','homepage','main'),
	(12,'settings','wallpaper','uploads/settings/wallpaper.jpg'),
	(13,'settings','text_color','#44813e'),
	(14,'settings','igd','IGDK'),
	(15,'settings','laboratorium','-'),
	(16,'settings','pj_laboratorium','DR001'),
	(17,'settings','radiologi','-'),
	(18,'settings','pj_radiologi','DR001'),
	(19,'settings','dokter_ralan_per_dokter','false'),
	(20,'settings','cekstatusbayar','false'),
	(21,'settings','ceklimit','false'),
	(22,'settings','responsivevoice','false'),
	(23,'settings','notif_presensi','true'),
	(24,'settings','BpjsApiUrl','https://apijkn-dev.bpjs-kesehatan.go.id/vclaim-rest-dev/'),
	(25,'settings','BpjsConsID','-'),
	(26,'settings','BpjsSecretKey','-'),
	(27,'settings','BpjsUserKey','-'),
	(28,'settings','timezone','Asia/Makassar'),
	(29,'settings','theme','default'),
	(30,'settings','theme_admin','mlite'),
	(31,'settings','admin_mode','complex'),
	(32,'settings','input_kasir','tidak'),
	(33,'settings','editor','wysiwyg'),
	(34,'settings','version','5.2.0'),
	(35,'settings','update_check','0'),
	(36,'settings','update_changelog',''),
	(37,'settings','update_version','0'),
	(38,'settings','license',''),
	(39,'farmasi','deporalan','-'),
	(40,'farmasi','igd','-'),
	(41,'farmasi','deporanap','-'),
	(42,'farmasi','gudang','-'),
	(43,'wagateway','server','https://mlite.id'),
	(44,'wagateway','token','-'),
	(45,'wagateway','phonenumber','-'),
	(46,'anjungan','display_poli',''),
	(47,'anjungan','carabayar',''),
	(48,'anjungan','antrian_loket','1'),
	(49,'anjungan','antrian_cs','2'),
	(50,'anjungan','antrian_apotek','3'),
	(51,'anjungan','panggil_loket','1'),
	(52,'anjungan','panggil_loket_nomor','1'),
	(53,'anjungan','panggil_cs','1'),
	(54,'anjungan','panggil_cs_nomor','1'),
	(55,'anjungan','panggil_apotek','1'),
	(56,'anjungan','panggil_apotek_nomor','1'),
	(57,'anjungan','text_anjungan','Running text anjungan pasien mandiri.....'),
	(58,'anjungan','text_loket','Running text display antrian loket.....'),
	(59,'anjungan','text_poli','Running text display antrian poliklinik.....'),
	(60,'anjungan','text_laboratorium','Running text display antrian laboratorium.....'),
	(61,'anjungan','text_apotek','Running text display antrian apotek.....'),
	(62,'anjungan','text_farmasi','Running text display antrian farmasi.....'),
	(63,'anjungan','vidio','G4im8_n0OoI'),
	(64,'api','apam_key','qtbexUAxzqO3M8dCOo2vDMFvgYjdUEdMLVo341'),
	(65,'api','apam_status_daftar','Terdaftar'),
	(66,'api','apam_status_dilayani','Anda siap dilayani'),
	(67,'api','apam_webappsurl','http://localhost/webapps/'),
	(68,'api','apam_normpetugas','000001,000002'),
	(69,'api','apam_limit','2'),
	(70,'api','apam_smtp_host','ssl://smtp.gmail.com'),
	(71,'api','apam_smtp_port','465'),
	(72,'api','apam_smtp_username',''),
	(73,'api','apam_smtp_password',''),
	(74,'api','apam_kdpj',''),
	(75,'api','apam_kdprop',''),
	(76,'api','apam_kdkab',''),
	(77,'api','apam_kdkec',''),
	(78,'api','duitku_merchantCode',''),
	(79,'api','duitku_merchantKey',''),
	(80,'api','duitku_paymentAmount',''),
	(81,'api','duitku_paymentMethod',''),
	(82,'api','duitku_productDetails',''),
	(83,'api','duitku_expiryPeriod',''),
	(84,'api','duitku_kdpj',''),
	(85,'api','berkasdigital_key','qtbexUAxzqO3M8dCOo2vDMFvgYjdUEdMLVo341'),
	(86,'jkn_mobile','x_username','jkn'),
	(87,'jkn_mobile','x_password','mobile'),
	(88,'jkn_mobile','header_token','X-Token'),
	(89,'jkn_mobile','header_username','X-Username'),
	(90,'jkn_mobile','header_password','X-Password'),
	(91,'jkn_mobile','BpjsConsID',''),
	(92,'jkn_mobile','BpjsSecretKey',''),
	(93,'jkn_mobile','BpjsUserKey',''),
	(94,'jkn_mobile','BpjsAntrianUrl','https://apijkn-dev.bpjs-kesehatan.go.id/antreanrs_dev/'),
	(95,'jkn_mobile','kd_pj_bpjs',''),
	(96,'jkn_mobile','exclude_taskid',''),
	(97,'jkn_mobile','display',''),
	(98,'jkn_mobile','kdprop','1'),
	(99,'jkn_mobile','kdkab','1'),
	(100,'jkn_mobile','kdkec','1'),
	(101,'jkn_mobile','kdkel','1'),
	(102,'jkn_mobile','perusahaan_pasien',''),
	(103,'jkn_mobile','suku_bangsa',''),
	(104,'jkn_mobile','bahasa_pasien',''),
	(105,'jkn_mobile','cacat_fisik',''),
	(106,'keuangan','jurnal_kasir','0'),
	(107,'keuangan','akun_kredit_pendaftaran',''),
	(108,'keuangan','akun_kredit_tindakan',''),
	(109,'keuangan','akun_kredit_obat_bhp',''),
	(110,'keuangan','akun_kredit_laboratorium',''),
	(111,'keuangan','akun_kredit_radiologi',''),
	(112,'keuangan','akun_kredit_tambahan_biaya',''),
	(113,'manajemen','penjab_umum','UMU'),
	(114,'manajemen','penjab_bpjs','BPJ'),
	(115,'presensi','lat','-2.58'),
	(116,'presensi','lon','115.37'),
	(117,'presensi','distance','2'),
	(118,'presensi','helloworld','Jangan Lupa Bahagia; \nCara untuk memulai adalah berhenti berbicara dan mulai melakukan; \nWaktu yang hilang tidak akan pernah ditemukan lagi; \nKamu bisa membodohi semua orang, tetapi kamu tidak bisa membohongi pikiranmu; \nIni bukan tentang ide. Ini tentang mewujudkan ide; \nBekerja bukan hanya untuk mencari materi. Bekerja merupakan manfaat bagi banyak orang'),
	(119,'vedika','carabayar',''),
	(120,'vedika','sep',''),
	(121,'vedika','skdp',''),
	(122,'vedika','operasi',''),
	(123,'vedika','individual',''),
	(124,'vedika','billing','mlite'),
	(125,'vedika','periode','2023-01'),
	(126,'vedika','verifikasi','2023-01'),
	(127,'vedika','inacbgs_prosedur_bedah',''),
	(128,'vedika','inacbgs_prosedur_non_bedah',''),
	(129,'vedika','inacbgs_konsultasi',''),
	(130,'vedika','inacbgs_tenaga_ahli',''),
	(131,'vedika','inacbgs_keperawatan',''),
	(132,'vedika','inacbgs_penunjang',''),
	(133,'vedika','inacbgs_pelayanan_darah',''),
	(134,'vedika','inacbgs_rehabilitasi',''),
	(135,'vedika','inacbgs_rawat_intensif',''),
	(136,'vedika','eklaim_url',''),
	(137,'vedika','eklaim_key',''),
	(138,'vedika','eklaim_kelasrs','CP'),
	(139,'vedika','eklaim_payor_id','3'),
	(140,'vedika','eklaim_payor_cd','JKN'),
	(141,'vedika','eklaim_cob_cd','#'),
	(142,'orthanc','server','http://localhost:8042'),
	(143,'orthanc','username','orthanc'),
	(144,'orthanc','password','orthanc'),
	(145,'veronisa','username',''),
	(146,'veronisa','password',''),
	(147,'veronisa','obat_kronis',''),
	(148,'jkn_mobile','kirimantrian','tidak'),
	(149,'settings','keamanan','ya'),
	(150,'dokter_ralan','set_sudah','tidak'),
	(151,'settings','websocket','tidak'),
	(152,'settings','websocket_proxy',''),
	(153,'settings','username_fp',''),
	(154,'settings','password_fp',''),
	(155,'settings','username_frista',''),
	(156,'settings','password_frista',''),
	(157,'settings','billing_obat','false'),
	(158,'settings','prefix_surat','RS'),
	(159,'farmasi','keterangan_etiket',''),
	(160,'pcare','consumerUserKeyAntrol','');

/*!40000 ALTER TABLE `mlite_settings` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table mlite_subrekening
# ------------------------------------------------------------

CREATE TABLE `mlite_subrekening` (
  `kd_rek` varchar(15) NOT NULL,
  `kd_rek2` varchar(15) NOT NULL,
  PRIMARY KEY (`kd_rek2`),
  KEY `kd_rek` (`kd_rek`),
  CONSTRAINT `mlite_subrekening_ibfk_1` FOREIGN KEY (`kd_rek`) REFERENCES `mlite_rekening` (`kd_rek`) ON UPDATE CASCADE,
  CONSTRAINT `mlite_subrekening_ibfk_2` FOREIGN KEY (`kd_rek2`) REFERENCES `mlite_rekening` (`kd_rek`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_surat_rujukan
# ------------------------------------------------------------

CREATE TABLE `mlite_surat_rujukan` (
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_surat_sakit
# ------------------------------------------------------------

CREATE TABLE `mlite_surat_sakit` (
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_surat_sehat
# ------------------------------------------------------------

CREATE TABLE `mlite_surat_sehat` (
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table mlite_temporary
# ------------------------------------------------------------

CREATE TABLE `mlite_temporary` (
  `temp1` text,
  `temp2` text,
  `temp3` text,
  `temp4` text,
  `temp5` text,
  `temp6` text,
  `temp7` text,
  `temp8` text,
  `temp9` text,
  `temp10` text,
  `temp11` text,
  `temp12` text,
  `temp13` text,
  `temp14` text,
  `temp15` text,
  `temp16` text,
  `temp17` text,
  `temp18` text,
  `temp19` text,
  `temp20` text,
  `temp21` text,
  `temp22` text,
  `temp23` text,
  `temp24` text,
  `temp25` text,
  `temp26` text,
  `temp27` text,
  `temp28` text,
  `temp29` text,
  `temp30` text,
  `temp31` text,
  `temp32` text,
  `temp33` text,
  `temp34` text,
  `temp35` text,
  `temp36` text,
  `temp37` text,
  `temp38` text,
  `temp39` text,
  `temp40` text,
  `temp41` text,
  `temp42` text,
  `temp43` text,
  `temp44` text,
  `temp45` text,
  `temp46` text,
  `temp47` text,
  `temp48` text,
  `temp49` text,
  `temp50` text,
  `temp51` text,
  `temp52` text,
  `temp53` text,
  `temp54` text,
  `temp55` text,
  `temp56` text,
  `temp57` text,
  `temp58` text,
  `temp59` text,
  `temp60` text,
  `temp61` text,
  `temp62` text,
  `temp63` text,
  `temp64` text,
  `temp65` text,
  `temp66` text,
  `temp67` text,
  `temp68` text,
  `temp69` text,
  `temp70` text,
  `temp71` text,
  `temp72` text,
  `temp73` text,
  `temp74` text,
  `temp75` text,
  `temp76` text,
  `temp77` text,
  `temp78` text,
  `temp79` text,
  `temp80` text,
  `temp81` text,
  `temp82` text,
  `temp83` text,
  `temp84` text,
  `temp85` text,
  `temp86` text,
  `temp87` text,
  `temp88` text,
  `temp89` text,
  `temp90` text,
  `temp91` text,
  `temp92` text,
  `temp93` text,
  `temp94` text,
  `temp95` text,
  `temp96` text,
  `temp97` text,
  `temp98` text,
  `temp99` text,
  `temp100` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table mlite_triase
# ------------------------------------------------------------

CREATE TABLE `mlite_triase` (
  `no_rawat` varchar(17) NOT NULL,
  `tgl_kunjungan` datetime NOT NULL,
  `cara_masuk` enum('Jalan','Brankar','Kursi Roda','Digendong') NOT NULL,
  `alat_transportasi` enum('-','AGD','Sendiri','Swasta') NOT NULL,
  `alasan_kedatangan` enum('Datang Sendiri','Polisi','Rujukan','-') NOT NULL,
  `keterangan_kedatangan` varchar(100) NOT NULL,
  `macam_kasus` enum('Trauma Kecelakaan Lalu Lintas','Trauma Kecelakaan Kerja','Trauma Kasus Unit Pelayanan Anak & Perempuan','Trauma Lainnya','Non Trauma') NOT NULL,
  `tekanan_darah` varchar(8) NOT NULL,
  `nadi` varchar(3) NOT NULL,
  `pernapasan` varchar(3) NOT NULL,
  `suhu` varchar(5) NOT NULL,
  `saturasi_o2` varchar(3) NOT NULL,
  `nyeri` varchar(5) NOT NULL,
  `jenis_triase` enum('Primer','Sekunder') NOT NULL,
  `keluhan_utama` varchar(500) NOT NULL,
  `kebutuhan_khusus` enum('-','UPPA','Airborne','Dekontaminan') NOT NULL,
  `catatan` varchar(100) NOT NULL,
  `plan` enum('Ruang Resusitasi','Ruang Kritis','Zona Kuning','Zona Hijau') NOT NULL,
  `nik` varchar(20) NOT NULL,
  PRIMARY KEY (`no_rawat`) USING BTREE,
  CONSTRAINT `mlite_triase_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_triase_detail
# ------------------------------------------------------------

CREATE TABLE `mlite_triase_detail` (
  `no_rawat` varchar(17) NOT NULL,
  `skala` varchar(3) NOT NULL,
  `kode_skala` varchar(3) NOT NULL,
  PRIMARY KEY (`no_rawat`,`skala`,`kode_skala`) USING BTREE,
  CONSTRAINT `mlite_triase_detail_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_triase_pemeriksaan
# ------------------------------------------------------------

CREATE TABLE `mlite_triase_pemeriksaan` (
  `kode_pemeriksaan` varchar(3) NOT NULL,
  `nama_pemeriksaan` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`kode_pemeriksaan`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_triase_skala
# ------------------------------------------------------------

CREATE TABLE `mlite_triase_skala` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_pemeriksaan` varchar(3) NOT NULL,
  `skala` int(11) NOT NULL,
  `kode_skala` varchar(3) NOT NULL,
  `pengkajian_skala` varchar(150) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `kode_pemeriksaan` (`kode_pemeriksaan`) USING BTREE,
  CONSTRAINT `mlite_triase_skala_ibfk_1` FOREIGN KEY (`kode_pemeriksaan`) REFERENCES `mlite_triase_pemeriksaan` (`kode_pemeriksaan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table mlite_users
# ------------------------------------------------------------

CREATE TABLE `mlite_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` text,
  `fullname` text,
  `description` text,
  `password` text,
  `avatar` text,
  `email` text,
  `role` varchar(100) NOT NULL DEFAULT 'user',
  `cap` varchar(100) DEFAULT '',
  `access` varchar(500) NOT NULL DEFAULT 'dashboard',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `mlite_users` WRITE;
/*!40000 ALTER TABLE `mlite_users` DISABLE KEYS */;

INSERT INTO `mlite_users` (`id`, `username`, `fullname`, `description`, `password`, `avatar`, `email`, `role`, `cap`, `access`)
VALUES
	(1,'admin','Administrator','Admin ganteng baik hati, suka menabung dan tidak sombong.','$2y$10$pgRnDiukCbiYVqsamMM3ROWViSRqbyCCL33N8.ykBKZx0dlplXe9i','avatar6422cb573b50c.png','info@mlite.id','admin','','all'),
	(2,'DR001','dr. Ataaka Muhammad','-','$2y$10$kuf2BxvViduBpUTn.6Nxsug3AskH/PGvXTSlfCfJqK8Ayb9a0.vqC','avatar643a104444515.png','info@mlite.id','admin','','all');

/*!40000 ALTER TABLE `mlite_users` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table mlite_users_vedika
# ------------------------------------------------------------

CREATE TABLE `mlite_users_vedika` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` text,
  `password` text,
  `fullname` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table mlite_vedika
# ------------------------------------------------------------

CREATE TABLE `mlite_vedika` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date DEFAULT NULL,
  `no_rkm_medis` varchar(6) NOT NULL,
  `no_rawat` varchar(100) NOT NULL,
  `tgl_registrasi` varchar(100) NOT NULL,
  `nosep` varchar(100) NOT NULL,
  `jenis` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mlite_vedika_feedback
# ------------------------------------------------------------

CREATE TABLE `mlite_vedika_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nosep` varchar(100) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `catatan` text,
  `username` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mlite_veronisa
# ------------------------------------------------------------

CREATE TABLE `mlite_veronisa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date DEFAULT NULL,
  `no_rkm_medis` varchar(6) NOT NULL,
  `no_rawat` varchar(100) NOT NULL,
  `tgl_registrasi` varchar(100) NOT NULL,
  `nosep` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mlite_veronisa_feedback
# ------------------------------------------------------------

CREATE TABLE `mlite_veronisa_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nosep` varchar(100) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `catatan` text,
  `username` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table mutasi_berkas
# ------------------------------------------------------------

CREATE TABLE `mutasi_berkas` (
  `no_rawat` varchar(17) NOT NULL,
  `status` enum('Sudah Dikirim','Sudah Diterima','Sudah Kembali','Tidak Ada','Masuk Ranap') DEFAULT NULL,
  `dikirim` datetime DEFAULT NULL,
  `diterima` datetime DEFAULT NULL,
  `kembali` datetime DEFAULT NULL,
  `tidakada` datetime DEFAULT NULL,
  `ranap` datetime NOT NULL,
  PRIMARY KEY (`no_rawat`),
  CONSTRAINT `mutasi_berkas_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table obat_racikan
# ------------------------------------------------------------

CREATE TABLE `obat_racikan` (
  `tgl_perawatan` date NOT NULL,
  `jam` time NOT NULL,
  `no_rawat` varchar(17) NOT NULL,
  `no_racik` varchar(2) NOT NULL,
  `nama_racik` varchar(100) NOT NULL,
  `kd_racik` varchar(3) NOT NULL,
  `jml_dr` int(11) NOT NULL,
  `aturan_pakai` varchar(150) NOT NULL,
  `keterangan` varchar(50) NOT NULL,
  PRIMARY KEY (`tgl_perawatan`,`jam`,`no_rawat`,`no_racik`),
  KEY `kd_racik` (`kd_racik`),
  KEY `no_rawat` (`no_rawat`),
  KEY `no_racik` (`no_racik`),
  CONSTRAINT `obat_racikan_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
  CONSTRAINT `obat_racikan_ibfk_2` FOREIGN KEY (`kd_racik`) REFERENCES `metode_racik` (`kd_racik`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table obatbhp_ok
# ------------------------------------------------------------

CREATE TABLE `obatbhp_ok` (
  `kd_obat` varchar(15) NOT NULL,
  `nm_obat` varchar(50) NOT NULL,
  `kode_sat` char(4) NOT NULL,
  `hargasatuan` double NOT NULL,
  PRIMARY KEY (`kd_obat`),
  KEY `kode_sat` (`kode_sat`),
  KEY `nm_obat` (`nm_obat`),
  KEY `hargasatuan` (`hargasatuan`),
  CONSTRAINT `obatbhp_ok_ibfk_1` FOREIGN KEY (`kode_sat`) REFERENCES `kodesatuan` (`kode_sat`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table operasi
# ------------------------------------------------------------

CREATE TABLE `operasi` (
  `no_rawat` varchar(17) NOT NULL,
  `tgl_operasi` datetime NOT NULL,
  `jenis_anasthesi` varchar(8) NOT NULL,
  `kategori` enum('-','Khusus','Besar','Sedang','Kecil','Elektive','Emergency') DEFAULT NULL,
  `operator1` varchar(20) NOT NULL,
  `operator2` varchar(20) NOT NULL,
  `operator3` varchar(20) NOT NULL,
  `asisten_operator1` varchar(20) NOT NULL,
  `asisten_operator2` varchar(20) NOT NULL,
  `asisten_operator3` varchar(20) DEFAULT NULL,
  `instrumen` varchar(20) DEFAULT NULL,
  `dokter_anak` varchar(20) NOT NULL,
  `perawaat_resusitas` varchar(20) NOT NULL,
  `dokter_anestesi` varchar(20) NOT NULL,
  `asisten_anestesi` varchar(20) NOT NULL,
  `asisten_anestesi2` varchar(20) DEFAULT NULL,
  `bidan` varchar(20) NOT NULL,
  `bidan2` varchar(20) DEFAULT NULL,
  `bidan3` varchar(20) DEFAULT NULL,
  `perawat_luar` varchar(20) NOT NULL,
  `omloop` varchar(20) DEFAULT NULL,
  `omloop2` varchar(20) DEFAULT NULL,
  `omloop3` varchar(20) DEFAULT NULL,
  `omloop4` varchar(20) DEFAULT NULL,
  `omloop5` varchar(20) DEFAULT NULL,
  `dokter_pjanak` varchar(20) DEFAULT NULL,
  `dokter_umum` varchar(20) DEFAULT NULL,
  `kode_paket` varchar(15) NOT NULL,
  `biayaoperator1` double NOT NULL,
  `biayaoperator2` double NOT NULL,
  `biayaoperator3` double NOT NULL,
  `biayaasisten_operator1` double NOT NULL,
  `biayaasisten_operator2` double NOT NULL,
  `biayaasisten_operator3` double DEFAULT NULL,
  `biayainstrumen` double DEFAULT NULL,
  `biayadokter_anak` double NOT NULL,
  `biayaperawaat_resusitas` double NOT NULL,
  `biayadokter_anestesi` double NOT NULL,
  `biayaasisten_anestesi` double NOT NULL,
  `biayaasisten_anestesi2` double DEFAULT NULL,
  `biayabidan` double NOT NULL,
  `biayabidan2` double DEFAULT NULL,
  `biayabidan3` double DEFAULT NULL,
  `biayaperawat_luar` double NOT NULL,
  `biayaalat` double NOT NULL,
  `biayasewaok` double NOT NULL,
  `akomodasi` double DEFAULT NULL,
  `bagian_rs` double NOT NULL,
  `biaya_omloop` double DEFAULT NULL,
  `biaya_omloop2` double DEFAULT NULL,
  `biaya_omloop3` double DEFAULT NULL,
  `biaya_omloop4` double DEFAULT NULL,
  `biaya_omloop5` double DEFAULT NULL,
  `biayasarpras` double DEFAULT NULL,
  `biaya_dokter_pjanak` double DEFAULT NULL,
  `biaya_dokter_umum` double DEFAULT NULL,
  `status` enum('Ranap','Ralan') DEFAULT NULL,
  PRIMARY KEY (`no_rawat`,`tgl_operasi`,`kode_paket`) USING BTREE,
  KEY `no_rawat` (`no_rawat`),
  KEY `operator1` (`operator1`),
  KEY `operator2` (`operator2`),
  KEY `operator3` (`operator3`),
  KEY `asisten_operator1` (`asisten_operator1`),
  KEY `asisten_operator2` (`asisten_operator2`),
  KEY `dokter_anak` (`dokter_anak`),
  KEY `perawaat_resusitas` (`perawaat_resusitas`),
  KEY `dokter_anestesi` (`dokter_anestesi`),
  KEY `asisten_anestesi` (`asisten_anestesi`),
  KEY `bidan` (`bidan`),
  KEY `perawat_luar` (`perawat_luar`),
  KEY `kode_paket` (`kode_paket`),
  CONSTRAINT `operasi_ibfk_31` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
  CONSTRAINT `operasi_ibfk_32` FOREIGN KEY (`operator1`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `operasi_ibfk_33` FOREIGN KEY (`operator2`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `operasi_ibfk_34` FOREIGN KEY (`operator3`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `operasi_ibfk_35` FOREIGN KEY (`asisten_operator1`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `operasi_ibfk_36` FOREIGN KEY (`asisten_operator2`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `operasi_ibfk_38` FOREIGN KEY (`dokter_anak`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `operasi_ibfk_39` FOREIGN KEY (`perawaat_resusitas`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `operasi_ibfk_40` FOREIGN KEY (`dokter_anestesi`) REFERENCES `dokter` (`kd_dokter`) ON UPDATE CASCADE,
  CONSTRAINT `operasi_ibfk_41` FOREIGN KEY (`asisten_anestesi`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `operasi_ibfk_42` FOREIGN KEY (`bidan`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `operasi_ibfk_43` FOREIGN KEY (`perawat_luar`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `operasi_ibfk_44` FOREIGN KEY (`kode_paket`) REFERENCES `paket_operasi` (`kode_paket`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table opname
# ------------------------------------------------------------

CREATE TABLE `opname` (
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
  `no_faktur` varchar(20) NOT NULL,
  PRIMARY KEY (`kode_brng`,`tanggal`,`kd_bangsal`,`no_batch`,`no_faktur`) USING BTREE,
  KEY `kd_bangsal` (`kd_bangsal`) USING BTREE,
  KEY `stok` (`stok`) USING BTREE,
  KEY `real` (`real`) USING BTREE,
  KEY `selisih` (`selisih`) USING BTREE,
  KEY `nomihilang` (`nomihilang`) USING BTREE,
  KEY `keterangan` (`keterangan`) USING BTREE,
  KEY `kode_brng` (`kode_brng`) USING BTREE,
  CONSTRAINT `opname_ibfk_1` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `opname_ibfk_2` FOREIGN KEY (`kd_bangsal`) REFERENCES `bangsal` (`kd_bangsal`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table paket_operasi
# ------------------------------------------------------------

CREATE TABLE `paket_operasi` (
  `kode_paket` varchar(15) NOT NULL,
  `nm_perawatan` varchar(80) NOT NULL,
  `kategori` enum('Kebidanan','Operasi') DEFAULT NULL,
  `operator1` double NOT NULL,
  `operator2` double NOT NULL,
  `operator3` double NOT NULL,
  `asisten_operator1` double DEFAULT NULL,
  `asisten_operator2` double NOT NULL,
  `asisten_operator3` double DEFAULT NULL,
  `instrumen` double DEFAULT NULL,
  `dokter_anak` double NOT NULL,
  `perawaat_resusitas` double NOT NULL,
  `dokter_anestesi` double NOT NULL,
  `asisten_anestesi` double NOT NULL,
  `asisten_anestesi2` double DEFAULT NULL,
  `bidan` double NOT NULL,
  `bidan2` double DEFAULT NULL,
  `bidan3` double DEFAULT NULL,
  `perawat_luar` double NOT NULL,
  `sewa_ok` double NOT NULL,
  `alat` double NOT NULL,
  `akomodasi` double DEFAULT NULL,
  `bagian_rs` double NOT NULL,
  `omloop` double NOT NULL,
  `omloop2` double DEFAULT NULL,
  `omloop3` double DEFAULT NULL,
  `omloop4` double DEFAULT NULL,
  `omloop5` double DEFAULT NULL,
  `sarpras` double DEFAULT NULL,
  `dokter_pjanak` double DEFAULT NULL,
  `dokter_umum` double DEFAULT NULL,
  `kd_pj` char(3) DEFAULT NULL,
  `status` enum('0','1') DEFAULT NULL,
  `kelas` enum('-','Rawat Jalan','Kelas 1','Kelas 2','Kelas 3','Kelas Utama','Kelas VIP','Kelas VVIP') DEFAULT NULL,
  PRIMARY KEY (`kode_paket`),
  KEY `nm_perawatan` (`nm_perawatan`),
  KEY `operator1` (`operator1`),
  KEY `operator2` (`operator2`),
  KEY `operator3` (`operator3`),
  KEY `asisten_operator1` (`asisten_operator1`),
  KEY `asisten_operator2` (`asisten_operator2`),
  KEY `asisten_operator3` (`instrumen`),
  KEY `dokter_anak` (`dokter_anak`),
  KEY `perawat_resusitas` (`perawaat_resusitas`),
  KEY `dokter_anestasi` (`dokter_anestesi`),
  KEY `asisten_anastesi` (`asisten_anestesi`),
  KEY `bidan` (`bidan`),
  KEY `perawat_luar` (`perawat_luar`),
  KEY `sewa_ok` (`sewa_ok`),
  KEY `alat` (`alat`),
  KEY `sewa_vk` (`akomodasi`),
  KEY `bagian_rs` (`bagian_rs`),
  KEY `omloop` (`omloop`),
  KEY `kd_pj` (`kd_pj`),
  KEY `asisten_anestesi2` (`asisten_anestesi2`),
  KEY `omloop2` (`omloop2`),
  KEY `omloop3` (`omloop3`),
  KEY `omloop4` (`omloop4`),
  KEY `omloop5` (`omloop5`),
  KEY `status` (`status`),
  KEY `kategori` (`kategori`),
  KEY `bidan2` (`bidan2`),
  KEY `bidan3` (`bidan3`),
  KEY `asisten_operator3_2` (`asisten_operator3`),
  CONSTRAINT `paket_operasi_ibfk_1` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table pasien
# ------------------------------------------------------------

CREATE TABLE `pasien` (
  `no_rkm_medis` varchar(15) NOT NULL,
  `nm_pasien` varchar(40) DEFAULT NULL,
  `no_ktp` varchar(20) DEFAULT NULL,
  `jk` enum('L','P') DEFAULT NULL,
  `tmp_lahir` varchar(15) DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `nm_ibu` varchar(40) NOT NULL,
  `alamat` varchar(200) DEFAULT NULL,
  `gol_darah` enum('A','B','O','AB','-') DEFAULT NULL,
  `pekerjaan` varchar(60) DEFAULT NULL,
  `stts_nikah` enum('BELUM MENIKAH','MENIKAH','JANDA','DUDHA','JOMBLO') DEFAULT NULL,
  `agama` varchar(12) DEFAULT NULL,
  `tgl_daftar` date DEFAULT NULL,
  `no_tlp` varchar(40) DEFAULT NULL,
  `umur` varchar(30) NOT NULL,
  `pnd` enum('TS','TK','SD','SMP','SMA','SLTA/SEDERAJAT','D1','D2','D3','D4','S1','S2','S3','-') NOT NULL,
  `keluarga` enum('AYAH','IBU','ISTRI','SUAMI','SAUDARA','ANAK') DEFAULT NULL,
  `namakeluarga` varchar(50) NOT NULL,
  `kd_pj` char(3) NOT NULL,
  `no_peserta` varchar(25) DEFAULT NULL,
  `kd_kel` varchar(100) NOT NULL,
  `kd_kec` int(11) NOT NULL,
  `kd_kab` int(11) NOT NULL,
  `pekerjaanpj` varchar(35) NOT NULL,
  `alamatpj` varchar(100) NOT NULL,
  `kelurahanpj` varchar(60) NOT NULL,
  `kecamatanpj` varchar(60) NOT NULL,
  `kabupatenpj` varchar(60) NOT NULL,
  `perusahaan_pasien` varchar(8) NOT NULL,
  `suku_bangsa` int(11) NOT NULL,
  `bahasa_pasien` int(11) NOT NULL,
  `cacat_fisik` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `nip` varchar(30) NOT NULL,
  `kd_prop` int(11) NOT NULL,
  `propinsipj` varchar(30) NOT NULL,
  PRIMARY KEY (`no_rkm_medis`),
  KEY `kd_pj` (`kd_pj`),
  KEY `kd_kec` (`kd_kec`),
  KEY `kd_kab` (`kd_kab`),
  KEY `nm_pasien` (`nm_pasien`),
  KEY `alamat` (`alamat`),
  KEY `kd_kel_2` (`kd_kel`),
  KEY `no_ktp` (`no_ktp`),
  KEY `no_peserta` (`no_peserta`),
  KEY `perusahaan_pasien` (`perusahaan_pasien`) USING BTREE,
  KEY `suku_bangsa` (`suku_bangsa`) USING BTREE,
  KEY `bahasa_pasien` (`bahasa_pasien`) USING BTREE,
  KEY `cacat_fisik` (`cacat_fisik`) USING BTREE,
  KEY `kd_prop` (`kd_prop`) USING BTREE,
  CONSTRAINT `pasien_ibfk_1` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON UPDATE CASCADE,
  CONSTRAINT `pasien_ibfk_3` FOREIGN KEY (`kd_kec`) REFERENCES `kecamatan` (`kd_kec`) ON UPDATE CASCADE,
  CONSTRAINT `pasien_ibfk_4` FOREIGN KEY (`kd_kab`) REFERENCES `kabupaten` (`kd_kab`) ON UPDATE CASCADE,
  CONSTRAINT `pasien_ibfk_5` FOREIGN KEY (`perusahaan_pasien`) REFERENCES `perusahaan_pasien` (`kode_perusahaan`) ON UPDATE CASCADE,
  CONSTRAINT `pasien_ibfk_6` FOREIGN KEY (`suku_bangsa`) REFERENCES `suku_bangsa` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `pasien_ibfk_7` FOREIGN KEY (`bahasa_pasien`) REFERENCES `bahasa_pasien` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `pasien_ibfk_8` FOREIGN KEY (`cacat_fisik`) REFERENCES `cacat_fisik` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `pasien_ibfk_9` FOREIGN KEY (`kd_prop`) REFERENCES `propinsi` (`kd_prop`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table pegawai
# ------------------------------------------------------------

CREATE TABLE `pegawai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nik` varchar(20) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `jk` enum('Pria','Wanita') NOT NULL,
  `jbtn` varchar(25) NOT NULL,
  `jnj_jabatan` varchar(5) NOT NULL,
  `kode_kelompok` varchar(3) NOT NULL,
  `kode_resiko` varchar(3) NOT NULL,
  `kode_emergency` varchar(3) NOT NULL,
  `departemen` char(4) NOT NULL,
  `bidang` varchar(15) NOT NULL,
  `stts_wp` char(5) NOT NULL,
  `stts_kerja` char(3) NOT NULL,
  `npwp` varchar(15) NOT NULL,
  `pendidikan` varchar(80) NOT NULL,
  `gapok` double NOT NULL,
  `tmp_lahir` varchar(20) NOT NULL,
  `tgl_lahir` date NOT NULL,
  `alamat` varchar(60) NOT NULL,
  `kota` varchar(20) NOT NULL,
  `mulai_kerja` date NOT NULL,
  `ms_kerja` enum('<1','PT','FT>1') NOT NULL,
  `indexins` char(4) NOT NULL,
  `bpd` varchar(50) NOT NULL,
  `rekening` varchar(25) NOT NULL,
  `stts_aktif` enum('AKTIF','CUTI','KELUAR','TENAGA LUAR') NOT NULL,
  `wajibmasuk` tinyint(2) NOT NULL,
  `pengurang` double NOT NULL,
  `indek` tinyint(4) NOT NULL,
  `mulai_kontrak` date DEFAULT NULL,
  `cuti_diambil` int(11) NOT NULL,
  `dankes` double NOT NULL,
  `photo` varchar(500) DEFAULT NULL,
  `no_ktp` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nik_2` (`nik`),
  KEY `departemen` (`departemen`),
  KEY `bidang` (`bidang`),
  KEY `stts_wp` (`stts_wp`),
  KEY `stts_kerja` (`stts_kerja`),
  KEY `pendidikan` (`pendidikan`),
  KEY `indexins` (`indexins`),
  KEY `jnj_jabatan` (`jnj_jabatan`),
  KEY `bpd` (`bpd`),
  KEY `nama` (`nama`),
  KEY `jbtn` (`jbtn`),
  KEY `npwp` (`npwp`),
  KEY `dankes` (`dankes`),
  KEY `cuti_diambil` (`cuti_diambil`),
  KEY `mulai_kontrak` (`mulai_kontrak`),
  KEY `stts_aktif` (`stts_aktif`),
  KEY `tmp_lahir` (`tmp_lahir`),
  KEY `alamat` (`alamat`),
  KEY `mulai_kerja` (`mulai_kerja`),
  KEY `gapok` (`gapok`),
  KEY `kota` (`kota`),
  KEY `pengurang` (`pengurang`),
  KEY `indek` (`indek`),
  KEY `jk` (`jk`),
  KEY `ms_kerja` (`ms_kerja`),
  KEY `tgl_lahir` (`tgl_lahir`),
  KEY `rekening` (`rekening`),
  KEY `wajibmasuk` (`wajibmasuk`),
  KEY `kode_emergency` (`kode_emergency`) USING BTREE,
  KEY `kode_kelompok` (`kode_kelompok`) USING BTREE,
  KEY `kode_resiko` (`kode_resiko`) USING BTREE,
  CONSTRAINT `pegawai_ibfk_1` FOREIGN KEY (`jnj_jabatan`) REFERENCES `jnj_jabatan` (`kode`) ON UPDATE CASCADE,
  CONSTRAINT `pegawai_ibfk_10` FOREIGN KEY (`kode_kelompok`) REFERENCES `kelompok_jabatan` (`kode_kelompok`) ON UPDATE CASCADE,
  CONSTRAINT `pegawai_ibfk_11` FOREIGN KEY (`kode_resiko`) REFERENCES `resiko_kerja` (`kode_resiko`) ON UPDATE CASCADE,
  CONSTRAINT `pegawai_ibfk_2` FOREIGN KEY (`departemen`) REFERENCES `departemen` (`dep_id`) ON UPDATE CASCADE,
  CONSTRAINT `pegawai_ibfk_3` FOREIGN KEY (`bidang`) REFERENCES `bidang` (`nama`) ON UPDATE CASCADE,
  CONSTRAINT `pegawai_ibfk_4` FOREIGN KEY (`stts_wp`) REFERENCES `stts_wp` (`stts`) ON UPDATE CASCADE,
  CONSTRAINT `pegawai_ibfk_5` FOREIGN KEY (`stts_kerja`) REFERENCES `stts_kerja` (`stts`) ON UPDATE CASCADE,
  CONSTRAINT `pegawai_ibfk_6` FOREIGN KEY (`pendidikan`) REFERENCES `pendidikan` (`tingkat`) ON UPDATE CASCADE,
  CONSTRAINT `pegawai_ibfk_7` FOREIGN KEY (`indexins`) REFERENCES `departemen` (`dep_id`) ON UPDATE CASCADE,
  CONSTRAINT `pegawai_ibfk_8` FOREIGN KEY (`bpd`) REFERENCES `bank` (`namabank`) ON UPDATE CASCADE,
  CONSTRAINT `pegawai_ibfk_9` FOREIGN KEY (`kode_emergency`) REFERENCES `emergency_index` (`kode_emergency`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `pegawai` WRITE;
/*!40000 ALTER TABLE `pegawai` DISABLE KEYS */;

INSERT INTO `pegawai` (`id`, `nik`, `nama`, `jk`, `jbtn`, `jnj_jabatan`, `kode_kelompok`, `kode_resiko`, `kode_emergency`, `departemen`, `bidang`, `stts_wp`, `stts_kerja`, `npwp`, `pendidikan`, `gapok`, `tmp_lahir`, `tgl_lahir`, `alamat`, `kota`, `mulai_kerja`, `ms_kerja`, `indexins`, `bpd`, `rekening`, `stts_aktif`, `wajibmasuk`, `pengurang`, `indek`, `mulai_kontrak`, `cuti_diambil`, `dankes`, `photo`, `no_ktp`)
VALUES
	(1,'DR001','dr. Ataaka Muhammad','Pria','-','-','-','-','-','-','-','-','-','-','-',0,'Barabai','2016-06-10','-','Barabai','2019-09-18','<1','-','-','-','AKTIF',0,0,0,'2019-09-18',1,0,'-','0');

/*!40000 ALTER TABLE `pegawai` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table pemeliharaan_inventaris
# ------------------------------------------------------------

CREATE TABLE `pemeliharaan_inventaris` (
  `no_inventaris` varchar(30) NOT NULL,
  `tanggal` date NOT NULL,
  `uraian_kegiatan` varchar(255) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `pelaksana` enum('Teknisi Rumah Sakit','Teknisi Rujukan','Pihak ke III') NOT NULL,
  `biaya` double NOT NULL,
  `jenis_pemeliharaan` enum('Running Maintenance','Shut Down Maintenance','Emergency Maintenance') NOT NULL,
  PRIMARY KEY (`no_inventaris`,`tanggal`),
  KEY `nip` (`nip`),
  CONSTRAINT `pemeliharaan_inventaris_ibfk_1` FOREIGN KEY (`no_inventaris`) REFERENCES `inventaris` (`no_inventaris`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pemeliharaan_inventaris_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table pemeriksaan_ralan
# ------------------------------------------------------------

CREATE TABLE `pemeriksaan_ralan` (
  `no_rawat` varchar(17) NOT NULL,
  `tgl_perawatan` date NOT NULL,
  `jam_rawat` time NOT NULL,
  `suhu_tubuh` varchar(5) DEFAULT NULL,
  `tensi` varchar(8) NOT NULL,
  `nadi` varchar(3) DEFAULT NULL,
  `respirasi` varchar(3) DEFAULT NULL,
  `tinggi` varchar(5) DEFAULT NULL,
  `berat` varchar(5) DEFAULT NULL,
  `spo2` varchar(3) NOT NULL,
  `gcs` varchar(10) DEFAULT NULL,
  `kesadaran` enum('Compos Mentis','Somnolence','Sopor','Coma') NOT NULL,
  `keluhan` varchar(2000) DEFAULT NULL,
  `pemeriksaan` varchar(2000) DEFAULT NULL,
  `alergi` varchar(50) DEFAULT NULL,
  `lingkar_perut` varchar(5) DEFAULT NULL,
  `rtl` varchar(2000) NOT NULL,
  `penilaian` varchar(2000) NOT NULL,
  `instruksi` varchar(2000) NOT NULL,
  `evaluasi` varchar(2000) NOT NULL,
  `nip` varchar(20) NOT NULL,
  PRIMARY KEY (`no_rawat`,`tgl_perawatan`,`jam_rawat`) USING BTREE,
  KEY `no_rawat` (`no_rawat`),
  KEY `nip` (`nip`) USING BTREE,
  CONSTRAINT `pemeriksaan_ralan_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pemeriksaan_ralan_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `pegawai` (`nik`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table pemeriksaan_ranap
# ------------------------------------------------------------

CREATE TABLE `pemeriksaan_ranap` (
  `no_rawat` varchar(17) NOT NULL,
  `tgl_perawatan` date NOT NULL,
  `jam_rawat` time NOT NULL,
  `suhu_tubuh` varchar(5) DEFAULT NULL,
  `tensi` varchar(8) NOT NULL,
  `nadi` varchar(3) DEFAULT NULL,
  `respirasi` varchar(3) DEFAULT NULL,
  `tinggi` varchar(5) DEFAULT NULL,
  `berat` varchar(5) DEFAULT NULL,
  `spo2` varchar(3) NOT NULL,
  `gcs` varchar(10) DEFAULT NULL,
  `kesadaran` enum('Compos Mentis','Somnolence','Sopor','Coma') NOT NULL,
  `keluhan` varchar(2000) DEFAULT NULL,
  `pemeriksaan` varchar(2000) DEFAULT NULL,
  `alergi` varchar(50) DEFAULT NULL,
  `penilaian` varchar(2000) NOT NULL,
  `rtl` varchar(2000) NOT NULL,
  `instruksi` varchar(2000) NOT NULL,
  `evaluasi` varchar(2000) NOT NULL,
  `nip` varchar(20) NOT NULL,
  PRIMARY KEY (`no_rawat`,`tgl_perawatan`,`jam_rawat`),
  KEY `no_rawat` (`no_rawat`),
  KEY `nip` (`nip`),
  CONSTRAINT `pemeriksaan_ranap_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pemeriksaan_ranap_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `pegawai` (`nik`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table pendidikan
# ------------------------------------------------------------

CREATE TABLE `pendidikan` (
  `tingkat` varchar(80) NOT NULL,
  `indek` tinyint(4) NOT NULL,
  `gapok1` double NOT NULL,
  `kenaikan` double NOT NULL,
  `maksimal` int(11) NOT NULL,
  PRIMARY KEY (`tingkat`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `pendidikan` WRITE;
/*!40000 ALTER TABLE `pendidikan` DISABLE KEYS */;

INSERT INTO `pendidikan` (`tingkat`, `indek`, `gapok1`, `kenaikan`, `maksimal`)
VALUES
	('-',1,0,0,1);

/*!40000 ALTER TABLE `pendidikan` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table penjab
# ------------------------------------------------------------

CREATE TABLE `penjab` (
  `kd_pj` char(3) NOT NULL,
  `png_jawab` varchar(30) NOT NULL,
  `nama_perusahaan` varchar(60) NOT NULL,
  `alamat_asuransi` varchar(130) NOT NULL,
  `no_telp` varchar(40) NOT NULL,
  `attn` varchar(60) NOT NULL,
  `status` enum('0','1') NOT NULL,
  PRIMARY KEY (`kd_pj`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `penjab` WRITE;
/*!40000 ALTER TABLE `penjab` DISABLE KEYS */;

INSERT INTO `penjab` (`kd_pj`, `png_jawab`, `nama_perusahaan`, `alamat_asuransi`, `no_telp`, `attn`, `status`)
VALUES
	('-','-','-','-','0','0','1'),
	('BPJ','BPJS Kesehatan','-','-','0','0','1'),
	('UMU','Umum','-','-','0','0','1');

/*!40000 ALTER TABLE `penjab` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table penyakit
# ------------------------------------------------------------

CREATE TABLE `penyakit` (
  `kd_penyakit` varchar(10) NOT NULL,
  `nm_penyakit` varchar(250) DEFAULT NULL,
  `ciri_ciri` text,
  `keterangan` varchar(60) DEFAULT NULL,
  `kd_ktg` varchar(8) DEFAULT NULL,
  `status` enum('Menular','Tidak Menular') NOT NULL,
  PRIMARY KEY (`kd_penyakit`),
  KEY `kd_ktg` (`kd_ktg`),
  KEY `nm_penyakit` (`nm_penyakit`),
  KEY `status` (`status`),
  CONSTRAINT `penyakit_ibfk_1` FOREIGN KEY (`kd_ktg`) REFERENCES `kategori_penyakit` (`kd_ktg`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table perbaikan_inventaris
# ------------------------------------------------------------

CREATE TABLE `perbaikan_inventaris` (
  `no_permintaan` varchar(15) NOT NULL,
  `tanggal` date NOT NULL,
  `uraian_kegiatan` varchar(255) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `pelaksana` enum('Teknisi Rumah Sakit','Teknisi Rujukan','Pihak ke III') NOT NULL,
  `biaya` double NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `status` enum('Bisa Diperbaiki','Tidak Bisa Diperbaiki') NOT NULL,
  PRIMARY KEY (`no_permintaan`),
  KEY `nip` (`nip`),
  CONSTRAINT `perbaikan_inventaris_ibfk_1` FOREIGN KEY (`no_permintaan`) REFERENCES `permintaan_perbaikan_inventaris` (`no_permintaan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `perbaikan_inventaris_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table periksa_lab
# ------------------------------------------------------------

CREATE TABLE `periksa_lab` (
  `no_rawat` varchar(17) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `kd_jenis_prw` varchar(15) NOT NULL,
  `tgl_periksa` date NOT NULL,
  `jam` time NOT NULL,
  `dokter_perujuk` varchar(20) NOT NULL,
  `bagian_rs` double NOT NULL,
  `bhp` double NOT NULL,
  `tarif_perujuk` double NOT NULL,
  `tarif_tindakan_dokter` double NOT NULL,
  `tarif_tindakan_petugas` double NOT NULL,
  `kso` double DEFAULT NULL,
  `menejemen` double DEFAULT NULL,
  `biaya` double NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `status` enum('Ralan','Ranap') DEFAULT NULL,
  `kategori` enum('PA','PK','MB') NOT NULL,
  PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`tgl_periksa`,`jam`),
  KEY `nip` (`nip`),
  KEY `kd_jenis_prw` (`kd_jenis_prw`),
  KEY `kd_dokter` (`kd_dokter`),
  KEY `dokter_perujuk` (`dokter_perujuk`),
  CONSTRAINT `periksa_lab_ibfk_10` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON UPDATE CASCADE,
  CONSTRAINT `periksa_lab_ibfk_11` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_lab` (`kd_jenis_prw`) ON UPDATE CASCADE,
  CONSTRAINT `periksa_lab_ibfk_12` FOREIGN KEY (`dokter_perujuk`) REFERENCES `dokter` (`kd_dokter`) ON UPDATE CASCADE,
  CONSTRAINT `periksa_lab_ibfk_13` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON UPDATE CASCADE,
  CONSTRAINT `periksa_lab_ibfk_9` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table periksa_radiologi
# ------------------------------------------------------------

CREATE TABLE `periksa_radiologi` (
  `no_rawat` varchar(17) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `kd_jenis_prw` varchar(15) NOT NULL,
  `tgl_periksa` date NOT NULL,
  `jam` time NOT NULL,
  `dokter_perujuk` varchar(20) NOT NULL,
  `bagian_rs` double NOT NULL,
  `bhp` double NOT NULL,
  `tarif_perujuk` double NOT NULL,
  `tarif_tindakan_dokter` double NOT NULL,
  `tarif_tindakan_petugas` double NOT NULL,
  `kso` double DEFAULT NULL,
  `menejemen` double DEFAULT NULL,
  `biaya` double NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `status` enum('Ranap','Ralan') DEFAULT NULL,
  `proyeksi` varchar(50) NOT NULL,
  `kV` varchar(10) NOT NULL,
  `mAS` varchar(10) NOT NULL,
  `FFD` varchar(10) NOT NULL,
  `BSF` varchar(10) NOT NULL,
  `inak` varchar(10) NOT NULL,
  `jml_penyinaran` varchar(10) NOT NULL,
  `dosis` varchar(20) NOT NULL,
  PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`tgl_periksa`,`jam`),
  KEY `nip` (`nip`),
  KEY `kd_jenis_prw` (`kd_jenis_prw`),
  KEY `kd_dokter` (`kd_dokter`),
  KEY `dokter_perujuk` (`dokter_perujuk`),
  CONSTRAINT `periksa_radiologi_ibfk_4` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
  CONSTRAINT `periksa_radiologi_ibfk_5` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON UPDATE CASCADE,
  CONSTRAINT `periksa_radiologi_ibfk_6` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_radiologi` (`kd_jenis_prw`) ON UPDATE CASCADE,
  CONSTRAINT `periksa_radiologi_ibfk_7` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON UPDATE CASCADE,
  CONSTRAINT `periksa_radiologi_ibfk_8` FOREIGN KEY (`dokter_perujuk`) REFERENCES `dokter` (`kd_dokter`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table permintaan_detail_permintaan_lab
# ------------------------------------------------------------

CREATE TABLE `permintaan_detail_permintaan_lab` (
  `noorder` varchar(15) NOT NULL,
  `kd_jenis_prw` varchar(15) NOT NULL,
  `id_template` int(11) NOT NULL,
  `stts_bayar` enum('Sudah','Belum') DEFAULT NULL,
  PRIMARY KEY (`noorder`,`kd_jenis_prw`,`id_template`) USING BTREE,
  KEY `id_template` (`id_template`) USING BTREE,
  KEY `kd_jenis_prw` (`kd_jenis_prw`) USING BTREE,
  CONSTRAINT `permintaan_detail_permintaan_lab_ibfk_2` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_lab` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permintaan_detail_permintaan_lab_ibfk_3` FOREIGN KEY (`id_template`) REFERENCES `template_laboratorium` (`id_template`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permintaan_detail_permintaan_lab_ibfk_4` FOREIGN KEY (`noorder`) REFERENCES `permintaan_lab` (`noorder`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table permintaan_lab
# ------------------------------------------------------------

CREATE TABLE `permintaan_lab` (
  `noorder` varchar(15) NOT NULL,
  `no_rawat` varchar(17) NOT NULL,
  `tgl_permintaan` date NOT NULL,
  `jam_permintaan` time NOT NULL,
  `tgl_sampel` date NOT NULL,
  `jam_sampel` time NOT NULL,
  `tgl_hasil` date NOT NULL,
  `jam_hasil` time NOT NULL,
  `dokter_perujuk` varchar(20) NOT NULL,
  `status` enum('ralan','ranap') NOT NULL,
  `informasi_tambahan` varchar(60) NOT NULL,
  `diagnosa_klinis` varchar(80) NOT NULL,
  PRIMARY KEY (`noorder`),
  KEY `dokter_perujuk` (`dokter_perujuk`),
  KEY `no_rawat` (`no_rawat`),
  CONSTRAINT `permintaan_lab_ibfk_2` FOREIGN KEY (`dokter_perujuk`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permintaan_lab_ibfk_3` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table permintaan_pemeriksaan_lab
# ------------------------------------------------------------

CREATE TABLE `permintaan_pemeriksaan_lab` (
  `noorder` varchar(15) NOT NULL,
  `kd_jenis_prw` varchar(15) NOT NULL,
  `stts_bayar` enum('Sudah','Belum') DEFAULT NULL,
  PRIMARY KEY (`noorder`,`kd_jenis_prw`),
  KEY `kd_jenis_prw` (`kd_jenis_prw`),
  CONSTRAINT `permintaan_pemeriksaan_lab_ibfk_1` FOREIGN KEY (`noorder`) REFERENCES `permintaan_lab` (`noorder`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permintaan_pemeriksaan_lab_ibfk_2` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_lab` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table permintaan_pemeriksaan_radiologi
# ------------------------------------------------------------

CREATE TABLE `permintaan_pemeriksaan_radiologi` (
  `noorder` varchar(15) NOT NULL,
  `kd_jenis_prw` varchar(15) NOT NULL,
  `stts_bayar` enum('Sudah','Belum') DEFAULT NULL,
  PRIMARY KEY (`noorder`,`kd_jenis_prw`),
  KEY `kd_jenis_prw` (`kd_jenis_prw`),
  CONSTRAINT `permintaan_pemeriksaan_radiologi_ibfk_1` FOREIGN KEY (`noorder`) REFERENCES `permintaan_radiologi` (`noorder`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permintaan_pemeriksaan_radiologi_ibfk_2` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_radiologi` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table permintaan_perbaikan_inventaris
# ------------------------------------------------------------

CREATE TABLE `permintaan_perbaikan_inventaris` (
  `no_permintaan` varchar(15) NOT NULL,
  `no_inventaris` varchar(30) DEFAULT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL,
  `deskripsi_kerusakan` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`no_permintaan`),
  KEY `no_inventaris` (`no_inventaris`),
  KEY `nik` (`nik`),
  CONSTRAINT `permintaan_perbaikan_inventaris_ibfk_1` FOREIGN KEY (`no_inventaris`) REFERENCES `inventaris` (`no_inventaris`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permintaan_perbaikan_inventaris_ibfk_2` FOREIGN KEY (`nik`) REFERENCES `pegawai` (`nik`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table permintaan_radiologi
# ------------------------------------------------------------

CREATE TABLE `permintaan_radiologi` (
  `noorder` varchar(15) NOT NULL,
  `no_rawat` varchar(17) NOT NULL,
  `tgl_permintaan` date NOT NULL,
  `jam_permintaan` time NOT NULL,
  `tgl_sampel` date NOT NULL,
  `jam_sampel` time NOT NULL,
  `tgl_hasil` date NOT NULL,
  `jam_hasil` time NOT NULL,
  `dokter_perujuk` varchar(20) NOT NULL,
  `status` enum('ralan','ranap') NOT NULL,
  `informasi_tambahan` varchar(60) NOT NULL,
  `diagnosa_klinis` varchar(80) NOT NULL,
  PRIMARY KEY (`noorder`),
  KEY `dokter_perujuk` (`dokter_perujuk`),
  KEY `no_rawat` (`no_rawat`),
  CONSTRAINT `permintaan_radiologi_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permintaan_radiologi_ibfk_3` FOREIGN KEY (`dokter_perujuk`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table personal_pasien
# ------------------------------------------------------------

CREATE TABLE `personal_pasien` (
  `no_rkm_medis` varchar(15) NOT NULL,
  `gambar` varchar(1000) DEFAULT NULL,
  `password` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`no_rkm_medis`),
  CONSTRAINT `personal_pasien_ibfk_1` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table perusahaan_pasien
# ------------------------------------------------------------

CREATE TABLE `perusahaan_pasien` (
  `kode_perusahaan` varchar(8) NOT NULL,
  `nama_perusahaan` varchar(70) DEFAULT NULL,
  `alamat` varchar(100) DEFAULT NULL,
  `kota` varchar(40) DEFAULT NULL,
  `no_telp` varchar(27) DEFAULT NULL,
  PRIMARY KEY (`kode_perusahaan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `perusahaan_pasien` WRITE;
/*!40000 ALTER TABLE `perusahaan_pasien` DISABLE KEYS */;

INSERT INTO `perusahaan_pasien` (`kode_perusahaan`, `nama_perusahaan`, `alamat`, `kota`, `no_telp`)
VALUES
	('-','-','-','-','0');

/*!40000 ALTER TABLE `perusahaan_pasien` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table petugas
# ------------------------------------------------------------

CREATE TABLE `petugas` (
  `nip` varchar(20) NOT NULL,
  `nama` varchar(50) DEFAULT NULL,
  `jk` enum('L','P') DEFAULT NULL,
  `tmp_lahir` varchar(20) DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `gol_darah` enum('A','B','O','AB','-') DEFAULT NULL,
  `agama` varchar(12) DEFAULT NULL,
  `stts_nikah` enum('BELUM MENIKAH','MENIKAH','JANDA','DUDHA','JOMBLO') DEFAULT NULL,
  `alamat` varchar(60) DEFAULT NULL,
  `kd_jbtn` char(4) DEFAULT NULL,
  `no_telp` varchar(13) DEFAULT NULL,
  `status` enum('0','1') DEFAULT NULL,
  PRIMARY KEY (`nip`),
  KEY `kd_jbtn` (`kd_jbtn`),
  KEY `nama` (`nama`),
  KEY `nip` (`nip`),
  KEY `tmp_lahir` (`tmp_lahir`),
  KEY `tgl_lahir` (`tgl_lahir`),
  KEY `agama` (`agama`),
  KEY `stts_nikah` (`stts_nikah`),
  KEY `alamat` (`alamat`),
  CONSTRAINT `petugas_ibfk_4` FOREIGN KEY (`nip`) REFERENCES `pegawai` (`nik`) ON UPDATE CASCADE,
  CONSTRAINT `petugas_ibfk_5` FOREIGN KEY (`kd_jbtn`) REFERENCES `jabatan` (`kd_jbtn`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `petugas` WRITE;
/*!40000 ALTER TABLE `petugas` DISABLE KEYS */;

INSERT INTO `petugas` (`nip`, `nama`, `jk`, `tmp_lahir`, `tgl_lahir`, `gol_darah`, `agama`, `stts_nikah`, `alamat`, `kd_jbtn`, `no_telp`, `status`)
VALUES
	('DR001','dr. Ataaka Muhammad','L','Barabai','2020-12-01','A','Islam','MENIKAH','-','-','0','1');

/*!40000 ALTER TABLE `petugas` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table poliklinik
# ------------------------------------------------------------

CREATE TABLE `poliklinik` (
  `kd_poli` char(5) NOT NULL DEFAULT '',
  `nm_poli` varchar(50) DEFAULT NULL,
  `registrasi` double NOT NULL,
  `registrasilama` double NOT NULL,
  `status` enum('0','1') NOT NULL,
  PRIMARY KEY (`kd_poli`),
  KEY `nm_poli` (`nm_poli`),
  KEY `registrasi` (`registrasi`),
  KEY `registrasilama` (`registrasilama`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `poliklinik` WRITE;
/*!40000 ALTER TABLE `poliklinik` DISABLE KEYS */;

INSERT INTO `poliklinik` (`kd_poli`, `nm_poli`, `registrasi`, `registrasilama`, `status`)
VALUES
	('-','-',0,0,'1'),
	('IGDK','IGD',0,0,'1'),
	('UMU','Umum',0,0,'1');

/*!40000 ALTER TABLE `poliklinik` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table propinsi
# ------------------------------------------------------------

CREATE TABLE `propinsi` (
  `kd_prop` int(11) NOT NULL AUTO_INCREMENT,
  `nm_prop` varchar(30) NOT NULL,
  PRIMARY KEY (`kd_prop`),
  UNIQUE KEY `nm_prop` (`nm_prop`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `propinsi` WRITE;
/*!40000 ALTER TABLE `propinsi` DISABLE KEYS */;

INSERT INTO `propinsi` (`kd_prop`, `nm_prop`)
VALUES
	(1,'-');

/*!40000 ALTER TABLE `propinsi` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table prosedur_pasien
# ------------------------------------------------------------

CREATE TABLE `prosedur_pasien` (
  `no_rawat` varchar(17) NOT NULL,
  `kode` varchar(8) NOT NULL,
  `status` enum('Ralan','Ranap') NOT NULL,
  `prioritas` tinyint(4) NOT NULL,
  PRIMARY KEY (`no_rawat`,`kode`,`status`),
  KEY `kode` (`kode`),
  CONSTRAINT `prosedur_pasien_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `prosedur_pasien_ibfk_2` FOREIGN KEY (`kode`) REFERENCES `icd9` (`kode`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table rawat_inap_dr
# ------------------------------------------------------------

CREATE TABLE `rawat_inap_dr` (
  `no_rawat` varchar(17) NOT NULL DEFAULT '',
  `kd_jenis_prw` varchar(15) NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `tgl_perawatan` date NOT NULL DEFAULT '0000-00-00',
  `jam_rawat` time NOT NULL DEFAULT '00:00:00',
  `material` double NOT NULL,
  `bhp` double NOT NULL,
  `tarif_tindakandr` double NOT NULL,
  `kso` double DEFAULT NULL,
  `menejemen` double DEFAULT NULL,
  `biaya_rawat` double DEFAULT NULL,
  PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`kd_dokter`,`tgl_perawatan`,`jam_rawat`),
  KEY `no_rawat` (`no_rawat`),
  KEY `kd_jenis_prw` (`kd_jenis_prw`),
  KEY `kd_dokter` (`kd_dokter`),
  KEY `tgl_perawatan` (`tgl_perawatan`),
  KEY `biaya_rawat` (`biaya_rawat`),
  KEY `jam_rawat` (`jam_rawat`),
  CONSTRAINT `rawat_inap_dr_ibfk_3` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON UPDATE CASCADE,
  CONSTRAINT `rawat_inap_dr_ibfk_6` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_inap` (`kd_jenis_prw`) ON UPDATE CASCADE,
  CONSTRAINT `rawat_inap_dr_ibfk_7` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table rawat_inap_drpr
# ------------------------------------------------------------

CREATE TABLE `rawat_inap_drpr` (
  `no_rawat` varchar(17) NOT NULL DEFAULT '',
  `kd_jenis_prw` varchar(15) NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `nip` varchar(20) NOT NULL DEFAULT '',
  `tgl_perawatan` date NOT NULL DEFAULT '0000-00-00',
  `jam_rawat` time NOT NULL DEFAULT '00:00:00',
  `material` double NOT NULL,
  `bhp` double NOT NULL,
  `tarif_tindakandr` double DEFAULT NULL,
  `tarif_tindakanpr` double DEFAULT NULL,
  `kso` double DEFAULT NULL,
  `menejemen` double DEFAULT NULL,
  `biaya_rawat` double DEFAULT NULL,
  PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`kd_dokter`,`nip`,`tgl_perawatan`,`jam_rawat`),
  KEY `rawat_inap_drpr_ibfk_2` (`kd_jenis_prw`),
  KEY `rawat_inap_drpr_ibfk_3` (`kd_dokter`),
  KEY `rawat_inap_drpr_ibfk_4` (`nip`),
  CONSTRAINT `rawat_inap_drpr_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
  CONSTRAINT `rawat_inap_drpr_ibfk_2` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_inap` (`kd_jenis_prw`) ON UPDATE CASCADE,
  CONSTRAINT `rawat_inap_drpr_ibfk_3` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON UPDATE CASCADE,
  CONSTRAINT `rawat_inap_drpr_ibfk_4` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table rawat_inap_pr
# ------------------------------------------------------------

CREATE TABLE `rawat_inap_pr` (
  `no_rawat` varchar(17) NOT NULL DEFAULT '',
  `kd_jenis_prw` varchar(15) NOT NULL,
  `nip` varchar(20) NOT NULL DEFAULT '',
  `tgl_perawatan` date NOT NULL DEFAULT '0000-00-00',
  `jam_rawat` time NOT NULL DEFAULT '00:00:00',
  `material` double NOT NULL,
  `bhp` double NOT NULL,
  `tarif_tindakanpr` double NOT NULL,
  `kso` double DEFAULT NULL,
  `menejemen` double DEFAULT NULL,
  `biaya_rawat` double DEFAULT NULL,
  PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`nip`,`tgl_perawatan`,`jam_rawat`),
  KEY `no_rawat` (`no_rawat`),
  KEY `kd_jenis_prw` (`kd_jenis_prw`),
  KEY `nip` (`nip`),
  KEY `biaya_rawat` (`biaya_rawat`),
  CONSTRAINT `rawat_inap_pr_ibfk_3` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON UPDATE CASCADE,
  CONSTRAINT `rawat_inap_pr_ibfk_6` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_inap` (`kd_jenis_prw`) ON UPDATE CASCADE,
  CONSTRAINT `rawat_inap_pr_ibfk_7` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table rawat_jl_dr
# ------------------------------------------------------------

CREATE TABLE `rawat_jl_dr` (
  `no_rawat` varchar(17) NOT NULL DEFAULT '',
  `kd_jenis_prw` varchar(15) NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `tgl_perawatan` date NOT NULL,
  `jam_rawat` time NOT NULL,
  `material` double NOT NULL,
  `bhp` double NOT NULL,
  `tarif_tindakandr` double NOT NULL,
  `kso` double DEFAULT NULL,
  `menejemen` double DEFAULT NULL,
  `biaya_rawat` double DEFAULT NULL,
  `stts_bayar` enum('Sudah','Belum','Suspen') DEFAULT NULL,
  PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`kd_dokter`,`tgl_perawatan`,`jam_rawat`) USING BTREE,
  KEY `no_rawat` (`no_rawat`),
  KEY `kd_jenis_prw` (`kd_jenis_prw`),
  KEY `kd_dokter` (`kd_dokter`),
  KEY `biaya_rawat` (`biaya_rawat`),
  CONSTRAINT `rawat_jl_dr_ibfk_2` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan` (`kd_jenis_prw`) ON UPDATE CASCADE,
  CONSTRAINT `rawat_jl_dr_ibfk_3` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON UPDATE CASCADE,
  CONSTRAINT `rawat_jl_dr_ibfk_5` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table rawat_jl_drpr
# ------------------------------------------------------------

CREATE TABLE `rawat_jl_drpr` (
  `no_rawat` varchar(17) NOT NULL DEFAULT '',
  `kd_jenis_prw` varchar(15) NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `tgl_perawatan` date NOT NULL,
  `jam_rawat` time NOT NULL,
  `material` double DEFAULT NULL,
  `bhp` double NOT NULL,
  `tarif_tindakandr` double DEFAULT NULL,
  `tarif_tindakanpr` double DEFAULT NULL,
  `kso` double DEFAULT NULL,
  `menejemen` double DEFAULT NULL,
  `biaya_rawat` double DEFAULT NULL,
  `stts_bayar` enum('Sudah','Belum','Suspen') DEFAULT NULL,
  PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`kd_dokter`,`nip`,`tgl_perawatan`,`jam_rawat`) USING BTREE,
  KEY `rawat_jl_drpr_ibfk_2` (`kd_jenis_prw`),
  KEY `rawat_jl_drpr_ibfk_3` (`kd_dokter`),
  KEY `rawat_jl_drpr_ibfk_4` (`nip`),
  KEY `no_rawat` (`no_rawat`),
  CONSTRAINT `rawat_jl_drpr_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
  CONSTRAINT `rawat_jl_drpr_ibfk_2` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan` (`kd_jenis_prw`) ON UPDATE CASCADE,
  CONSTRAINT `rawat_jl_drpr_ibfk_3` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON UPDATE CASCADE,
  CONSTRAINT `rawat_jl_drpr_ibfk_4` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table rawat_jl_pr
# ------------------------------------------------------------

CREATE TABLE `rawat_jl_pr` (
  `no_rawat` varchar(17) NOT NULL DEFAULT '',
  `kd_jenis_prw` varchar(15) NOT NULL,
  `nip` varchar(20) NOT NULL DEFAULT '',
  `tgl_perawatan` date NOT NULL,
  `jam_rawat` time NOT NULL,
  `material` double NOT NULL,
  `bhp` double NOT NULL,
  `tarif_tindakanpr` double NOT NULL,
  `kso` double DEFAULT NULL,
  `menejemen` double DEFAULT NULL,
  `biaya_rawat` double DEFAULT NULL,
  `stts_bayar` enum('Sudah','Belum','Suspen') DEFAULT NULL,
  PRIMARY KEY (`no_rawat`,`kd_jenis_prw`,`nip`,`tgl_perawatan`,`jam_rawat`) USING BTREE,
  KEY `no_rawat` (`no_rawat`),
  KEY `kd_jenis_prw` (`kd_jenis_prw`),
  KEY `nip` (`nip`),
  KEY `biaya_rawat` (`biaya_rawat`),
  CONSTRAINT `rawat_jl_pr_ibfk_10` FOREIGN KEY (`nip`) REFERENCES `petugas` (`nip`) ON UPDATE CASCADE,
  CONSTRAINT `rawat_jl_pr_ibfk_8` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
  CONSTRAINT `rawat_jl_pr_ibfk_9` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan` (`kd_jenis_prw`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table reg_periksa
# ------------------------------------------------------------

CREATE TABLE `reg_periksa` (
  `no_reg` varchar(8) DEFAULT NULL,
  `no_rawat` varchar(17) NOT NULL,
  `tgl_registrasi` date DEFAULT NULL,
  `jam_reg` time DEFAULT NULL,
  `kd_dokter` varchar(20) DEFAULT NULL,
  `no_rkm_medis` varchar(15) DEFAULT NULL,
  `kd_poli` char(5) DEFAULT NULL,
  `p_jawab` varchar(100) DEFAULT NULL,
  `almt_pj` varchar(200) DEFAULT NULL,
  `hubunganpj` varchar(20) DEFAULT NULL,
  `biaya_reg` double DEFAULT NULL,
  `stts` enum('Belum','Sudah','Batal','Berkas Diterima','Dirujuk','Meninggal','Dirawat','Pulang Paksa') DEFAULT NULL,
  `stts_daftar` enum('-','Lama','Baru') NOT NULL,
  `status_lanjut` enum('Ralan','Ranap') NOT NULL,
  `kd_pj` char(3) NOT NULL,
  `umurdaftar` int(11) DEFAULT NULL,
  `sttsumur` enum('Th','Bl','Hr') DEFAULT NULL,
  `status_bayar` enum('Sudah Bayar','Belum Bayar') NOT NULL,
  `status_poli` enum('Lama','Baru') NOT NULL,
  PRIMARY KEY (`no_rawat`),
  KEY `no_rkm_medis` (`no_rkm_medis`),
  KEY `kd_poli` (`kd_poli`),
  KEY `kd_pj` (`kd_pj`),
  KEY `status_lanjut` (`status_lanjut`),
  KEY `kd_dokter` (`kd_dokter`),
  KEY `status_bayar` (`status_bayar`) USING BTREE,
  CONSTRAINT `reg_periksa_ibfk_3` FOREIGN KEY (`kd_poli`) REFERENCES `poliklinik` (`kd_poli`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reg_periksa_ibfk_4` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reg_periksa_ibfk_6` FOREIGN KEY (`kd_pj`) REFERENCES `penjab` (`kd_pj`) ON UPDATE CASCADE,
  CONSTRAINT `reg_periksa_ibfk_7` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table rekap_presensi
# ------------------------------------------------------------

CREATE TABLE `rekap_presensi` (
  `id` int(10) NOT NULL,
  `shift` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10') NOT NULL,
  `jam_datang` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `jam_pulang` datetime DEFAULT NULL,
  `status` enum('Tepat Waktu','Terlambat Toleransi','Terlambat I','Terlambat II','Tepat Waktu & PSW','Terlambat Toleransi & PSW','Terlambat I & PSW','Terlambat II & PSW') NOT NULL,
  `keterlambatan` varchar(20) NOT NULL,
  `durasi` varchar(20) DEFAULT NULL,
  `keterangan` varchar(100) NOT NULL,
  `photo` varchar(500) NOT NULL,
  PRIMARY KEY (`id`,`jam_datang`),
  KEY `id` (`id`),
  CONSTRAINT `rekap_presensi_ibfk_1` FOREIGN KEY (`id`) REFERENCES `pegawai` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table resep_dokter
# ------------------------------------------------------------

CREATE TABLE `resep_dokter` (
  `no_resep` varchar(14) DEFAULT NULL,
  `kode_brng` varchar(15) DEFAULT NULL,
  `jml` double DEFAULT NULL,
  `aturan_pakai` varchar(150) DEFAULT NULL,
  KEY `no_resep` (`no_resep`),
  KEY `kode_brng` (`kode_brng`),
  CONSTRAINT `resep_dokter_ibfk_1` FOREIGN KEY (`no_resep`) REFERENCES `resep_obat` (`no_resep`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `resep_dokter_ibfk_2` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table resep_dokter_racikan
# ------------------------------------------------------------

CREATE TABLE `resep_dokter_racikan` (
  `no_resep` varchar(14) NOT NULL,
  `no_racik` varchar(2) NOT NULL,
  `nama_racik` varchar(100) NOT NULL,
  `kd_racik` varchar(3) NOT NULL,
  `jml_dr` int(11) NOT NULL,
  `aturan_pakai` varchar(150) NOT NULL,
  `keterangan` varchar(50) NOT NULL,
  PRIMARY KEY (`no_resep`,`no_racik`),
  KEY `kd_racik` (`kd_racik`),
  CONSTRAINT `resep_dokter_racikan_ibfk_1` FOREIGN KEY (`no_resep`) REFERENCES `resep_obat` (`no_resep`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `resep_dokter_racikan_ibfk_2` FOREIGN KEY (`kd_racik`) REFERENCES `metode_racik` (`kd_racik`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table resep_dokter_racikan_detail
# ------------------------------------------------------------

CREATE TABLE `resep_dokter_racikan_detail` (
  `no_resep` varchar(14) NOT NULL,
  `no_racik` varchar(2) NOT NULL,
  `kode_brng` varchar(15) NOT NULL,
  `p1` double DEFAULT NULL,
  `p2` double DEFAULT NULL,
  `kandungan` varchar(10) DEFAULT NULL,
  `jml` double DEFAULT NULL,
  PRIMARY KEY (`no_resep`,`no_racik`,`kode_brng`),
  KEY `kode_brng` (`kode_brng`),
  CONSTRAINT `resep_dokter_racikan_detail_ibfk_1` FOREIGN KEY (`no_resep`) REFERENCES `resep_obat` (`no_resep`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `resep_dokter_racikan_detail_ibfk_2` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table resep_obat
# ------------------------------------------------------------

CREATE TABLE `resep_obat` (
  `no_resep` varchar(14) NOT NULL DEFAULT '',
  `tgl_perawatan` date DEFAULT NULL,
  `jam` time NOT NULL,
  `no_rawat` varchar(17) NOT NULL DEFAULT '',
  `kd_dokter` varchar(20) NOT NULL,
  `tgl_peresepan` date DEFAULT NULL,
  `jam_peresepan` time DEFAULT NULL,
  `status` enum('ralan','ranap') DEFAULT NULL,
  `tgl_penyerahan` date NOT NULL,
  `jam_penyerahan` time NOT NULL,
  PRIMARY KEY (`no_resep`),
  KEY `no_rawat` (`no_rawat`),
  KEY `kd_dokter` (`kd_dokter`),
  CONSTRAINT `resep_obat_ibfk_3` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
  CONSTRAINT `resep_obat_ibfk_4` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table resep_pulang
# ------------------------------------------------------------

CREATE TABLE `resep_pulang` (
  `no_rawat` varchar(17) NOT NULL,
  `kode_brng` varchar(15) NOT NULL,
  `jml_barang` double NOT NULL,
  `harga` double NOT NULL,
  `total` double NOT NULL,
  `dosis` varchar(150) NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `kd_bangsal` varchar(5) NOT NULL,
  `no_batch` varchar(20) NOT NULL,
  `no_faktur` varchar(20) NOT NULL,
  PRIMARY KEY (`no_rawat`,`kode_brng`,`tanggal`,`jam`,`no_batch`,`no_faktur`),
  KEY `kode_brng` (`kode_brng`),
  KEY `kd_bangsal` (`kd_bangsal`),
  KEY `no_rawat` (`no_rawat`),
  CONSTRAINT `resep_pulang_ibfk_2` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `resep_pulang_ibfk_3` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE,
  CONSTRAINT `resep_pulang_ibfk_4` FOREIGN KEY (`kd_bangsal`) REFERENCES `bangsal` (`kd_bangsal`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table resiko_kerja
# ------------------------------------------------------------

CREATE TABLE `resiko_kerja` (
  `kode_resiko` varchar(3) NOT NULL,
  `nama_resiko` varchar(200) DEFAULT NULL,
  `indek` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`kode_resiko`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `resiko_kerja` WRITE;
/*!40000 ALTER TABLE `resiko_kerja` DISABLE KEYS */;

INSERT INTO `resiko_kerja` (`kode_resiko`, `nama_resiko`, `indek`)
VALUES
	('-','-',1);

/*!40000 ALTER TABLE `resiko_kerja` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table resume_pasien
# ------------------------------------------------------------

CREATE TABLE `resume_pasien` (
  `no_rawat` varchar(17) NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `keluhan_utama` text NOT NULL,
  `jalannya_penyakit` text NOT NULL,
  `pemeriksaan_penunjang` text NOT NULL,
  `hasil_laborat` text NOT NULL,
  `diagnosa_utama` varchar(80) NOT NULL,
  `kd_diagnosa_utama` varchar(10) NOT NULL,
  `diagnosa_sekunder` varchar(80) NOT NULL,
  `kd_diagnosa_sekunder` varchar(10) NOT NULL,
  `diagnosa_sekunder2` varchar(80) NOT NULL,
  `kd_diagnosa_sekunder2` varchar(10) NOT NULL,
  `diagnosa_sekunder3` varchar(80) NOT NULL,
  `kd_diagnosa_sekunder3` varchar(10) NOT NULL,
  `diagnosa_sekunder4` varchar(80) NOT NULL,
  `kd_diagnosa_sekunder4` varchar(10) NOT NULL,
  `prosedur_utama` varchar(80) NOT NULL,
  `kd_prosedur_utama` varchar(8) NOT NULL,
  `prosedur_sekunder` varchar(80) NOT NULL,
  `kd_prosedur_sekunder` varchar(8) NOT NULL,
  `prosedur_sekunder2` varchar(80) NOT NULL,
  `kd_prosedur_sekunder2` varchar(8) NOT NULL,
  `prosedur_sekunder3` varchar(80) NOT NULL,
  `kd_prosedur_sekunder3` varchar(8) NOT NULL,
  `kondisi_pulang` enum('Hidup','Meninggal') NOT NULL,
  `obat_pulang` text NOT NULL,
  PRIMARY KEY (`no_rawat`),
  KEY `kd_dokter` (`kd_dokter`),
  CONSTRAINT `resume_pasien_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `resume_pasien_ibfk_2` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table resume_pasien_ranap
# ------------------------------------------------------------

CREATE TABLE `resume_pasien_ranap` (
  `no_rawat` varchar(17) NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `diagnosa_awal` varchar(100) NOT NULL,
  `alasan` varchar(100) NOT NULL,
  `keluhan_utama` text NOT NULL,
  `pemeriksaan_fisik` text NOT NULL,
  `jalannya_penyakit` text NOT NULL,
  `pemeriksaan_penunjang` text NOT NULL,
  `hasil_laborat` text NOT NULL,
  `tindakan_dan_operasi` text NOT NULL,
  `obat_di_rs` text NOT NULL,
  `diagnosa_utama` varchar(80) NOT NULL,
  `kd_diagnosa_utama` varchar(10) NOT NULL,
  `diagnosa_sekunder` varchar(80) NOT NULL,
  `kd_diagnosa_sekunder` varchar(10) NOT NULL,
  `diagnosa_sekunder2` varchar(80) NOT NULL,
  `kd_diagnosa_sekunder2` varchar(10) NOT NULL,
  `diagnosa_sekunder3` varchar(80) NOT NULL,
  `kd_diagnosa_sekunder3` varchar(10) NOT NULL,
  `diagnosa_sekunder4` varchar(80) NOT NULL,
  `kd_diagnosa_sekunder4` varchar(10) NOT NULL,
  `prosedur_utama` varchar(80) NOT NULL,
  `kd_prosedur_utama` varchar(8) NOT NULL,
  `prosedur_sekunder` varchar(80) NOT NULL,
  `kd_prosedur_sekunder` varchar(8) NOT NULL,
  `prosedur_sekunder2` varchar(80) NOT NULL,
  `kd_prosedur_sekunder2` varchar(8) NOT NULL,
  `prosedur_sekunder3` varchar(80) NOT NULL,
  `kd_prosedur_sekunder3` varchar(8) NOT NULL,
  `alergi` varchar(100) NOT NULL,
  `diet` text NOT NULL,
  `lab_belum` text NOT NULL,
  `edukasi` text NOT NULL,
  `cara_keluar` enum('Atas Izin Dokter','Pindah RS','Pulang Atas Permintaan Sendiri','Lainnya') NOT NULL,
  `ket_keluar` varchar(50) DEFAULT NULL,
  `keadaan` enum('Membaik','Sembuh','Keadaan Khusus','Meninggal') NOT NULL,
  `ket_keadaan` varchar(50) DEFAULT NULL,
  `dilanjutkan` enum('Kembali Ke RS','RS Lain','Dokter Luar','Puskesmes','Lainnya') NOT NULL,
  `ket_dilanjutkan` varchar(50) DEFAULT NULL,
  `kontrol` datetime DEFAULT NULL,
  `obat_pulang` text NOT NULL,
  PRIMARY KEY (`no_rawat`),
  KEY `kd_dokter` (`kd_dokter`),
  CONSTRAINT `resume_pasien_ranap_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `resume_pasien_ranap_ibfk_2` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table riwayat_barang_medis
# ------------------------------------------------------------

CREATE TABLE `riwayat_barang_medis` (
  `kode_brng` varchar(15) DEFAULT NULL,
  `stok_awal` double DEFAULT NULL,
  `masuk` double DEFAULT NULL,
  `keluar` double DEFAULT NULL,
  `stok_akhir` double NOT NULL,
  `posisi` enum('Pemberian Obat','Pengadaan','Penerimaan','Piutang','Retur Beli','Retur Jual','Retur Piutang','Mutasi','Opname','Resep Pulang','Retur Pasien','Stok Pasien Ranap','Pengambilan Medis','Penjualan','Stok Keluar','Hibah') DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jam` time DEFAULT NULL,
  `petugas` varchar(20) DEFAULT NULL,
  `kd_bangsal` char(5) DEFAULT NULL,
  `status` enum('Simpan','Hapus') DEFAULT NULL,
  `no_batch` varchar(20) NOT NULL,
  `no_faktur` varchar(20) NOT NULL,
  `keterangan` varchar(100) NOT NULL,
  KEY `riwayat_barang_medis_ibfk_1` (`kode_brng`) USING BTREE,
  KEY `kd_bangsal` (`kd_bangsal`) USING BTREE,
  CONSTRAINT `riwayat_barang_medis_ibfk_1` FOREIGN KEY (`kode_brng`) REFERENCES `databarang` (`kode_brng`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `riwayat_barang_medis_ibfk_2` FOREIGN KEY (`kd_bangsal`) REFERENCES `bangsal` (`kd_bangsal`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table ruang_ok
# ------------------------------------------------------------

CREATE TABLE `ruang_ok` (
  `kd_ruang_ok` varchar(3) NOT NULL,
  `nm_ruang_ok` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`kd_ruang_ok`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table set_keterlambatan
# ------------------------------------------------------------

CREATE TABLE `set_keterlambatan` (
  `toleransi` int(11) DEFAULT NULL,
  `terlambat1` int(11) DEFAULT NULL,
  `terlambat2` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table set_no_rkm_medis
# ------------------------------------------------------------

CREATE TABLE `set_no_rkm_medis` (
  `no_rkm_medis` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

LOCK TABLES `set_no_rkm_medis` WRITE;
/*!40000 ALTER TABLE `set_no_rkm_medis` DISABLE KEYS */;

INSERT INTO `set_no_rkm_medis` (`no_rkm_medis`)
VALUES
	('000000');

/*!40000 ALTER TABLE `set_no_rkm_medis` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table skdp_bpjs
# ------------------------------------------------------------

CREATE TABLE `skdp_bpjs` (
  `tahun` year(4) NOT NULL,
  `no_rkm_medis` varchar(15) DEFAULT NULL,
  `diagnosa` varchar(50) NOT NULL,
  `terapi` varchar(50) NOT NULL,
  `alasan1` varchar(50) DEFAULT NULL,
  `alasan2` varchar(50) DEFAULT NULL,
  `rtl1` varchar(50) DEFAULT NULL,
  `rtl2` varchar(50) DEFAULT NULL,
  `tanggal_datang` datetime DEFAULT NULL,
  `tanggal_rujukan` datetime NOT NULL,
  `no_antrian` varchar(6) NOT NULL,
  `kd_dokter` varchar(20) DEFAULT NULL,
  `status` enum('Menunggu','Sudah Periksa','Batal Periksa') NOT NULL,
  PRIMARY KEY (`tahun`,`no_antrian`) USING BTREE,
  KEY `no_rkm_medis` (`no_rkm_medis`) USING BTREE,
  KEY `kd_dokter` (`kd_dokter`) USING BTREE,
  CONSTRAINT `skdp_bpjs_ibfk_1` FOREIGN KEY (`no_rkm_medis`) REFERENCES `pasien` (`no_rkm_medis`) ON UPDATE CASCADE,
  CONSTRAINT `skdp_bpjs_ibfk_2` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;



# Dump of table spesialis
# ------------------------------------------------------------

CREATE TABLE `spesialis` (
  `kd_sps` char(5) NOT NULL DEFAULT '',
  `nm_sps` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`kd_sps`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `spesialis` WRITE;
/*!40000 ALTER TABLE `spesialis` DISABLE KEYS */;

INSERT INTO `spesialis` (`kd_sps`, `nm_sps`)
VALUES
	('UMUM','Dokter Umum');

/*!40000 ALTER TABLE `spesialis` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table stts_kerja
# ------------------------------------------------------------

CREATE TABLE `stts_kerja` (
  `stts` char(3) NOT NULL,
  `ktg` varchar(20) NOT NULL,
  `indek` tinyint(4) NOT NULL,
  PRIMARY KEY (`stts`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `stts_kerja` WRITE;
/*!40000 ALTER TABLE `stts_kerja` DISABLE KEYS */;

INSERT INTO `stts_kerja` (`stts`, `ktg`, `indek`)
VALUES
	('-','-',1);

/*!40000 ALTER TABLE `stts_kerja` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table stts_wp
# ------------------------------------------------------------

CREATE TABLE `stts_wp` (
  `stts` char(5) NOT NULL,
  `ktg` varchar(50) NOT NULL,
  PRIMARY KEY (`stts`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `stts_wp` WRITE;
/*!40000 ALTER TABLE `stts_wp` DISABLE KEYS */;

INSERT INTO `stts_wp` (`stts`, `ktg`)
VALUES
	('-','-');

/*!40000 ALTER TABLE `stts_wp` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table suku_bangsa
# ------------------------------------------------------------

CREATE TABLE `suku_bangsa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_suku_bangsa` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `nama_suku_bangsa` (`nama_suku_bangsa`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

LOCK TABLES `suku_bangsa` WRITE;
/*!40000 ALTER TABLE `suku_bangsa` DISABLE KEYS */;

INSERT INTO `suku_bangsa` (`id`, `nama_suku_bangsa`)
VALUES
	(1,'-');

/*!40000 ALTER TABLE `suku_bangsa` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table tambahan_biaya
# ------------------------------------------------------------

CREATE TABLE `tambahan_biaya` (
  `no_rawat` varchar(17) NOT NULL,
  `nama_biaya` varchar(60) NOT NULL,
  `besar_biaya` double NOT NULL,
  PRIMARY KEY (`no_rawat`,`nama_biaya`),
  CONSTRAINT `tambahan_biaya_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table template_laboratorium
# ------------------------------------------------------------

CREATE TABLE `template_laboratorium` (
  `kd_jenis_prw` varchar(15) NOT NULL,
  `id_template` int(11) NOT NULL AUTO_INCREMENT,
  `Pemeriksaan` varchar(200) NOT NULL,
  `satuan` varchar(20) NOT NULL,
  `nilai_rujukan_ld` varchar(30) NOT NULL,
  `nilai_rujukan_la` varchar(30) NOT NULL,
  `nilai_rujukan_pd` varchar(30) NOT NULL,
  `nilai_rujukan_pa` varchar(30) NOT NULL,
  `bagian_rs` double NOT NULL,
  `bhp` double NOT NULL,
  `bagian_perujuk` double NOT NULL,
  `bagian_dokter` double NOT NULL,
  `bagian_laborat` double NOT NULL,
  `kso` double DEFAULT NULL,
  `menejemen` double DEFAULT NULL,
  `biaya_item` double NOT NULL,
  `urut` int(4) DEFAULT NULL,
  PRIMARY KEY (`id_template`),
  KEY `kd_jenis_prw` (`kd_jenis_prw`),
  KEY `Pemeriksaan` (`Pemeriksaan`),
  KEY `satuan` (`satuan`),
  KEY `nilai_rujukan_ld` (`nilai_rujukan_ld`),
  KEY `nilai_rujukan_la` (`nilai_rujukan_la`),
  KEY `nilai_rujukan_pd` (`nilai_rujukan_pd`),
  KEY `nilai_rujukan_pa` (`nilai_rujukan_pa`),
  KEY `bagian_rs` (`bagian_rs`),
  KEY `bhp` (`bhp`),
  KEY `bagian_perujuk` (`bagian_perujuk`),
  KEY `bagian_dokter` (`bagian_dokter`),
  KEY `bagian_laborat` (`bagian_laborat`),
  KEY `kso` (`kso`),
  KEY `menejemen` (`menejemen`),
  KEY `biaya_item` (`biaya_item`),
  KEY `urut` (`urut`),
  CONSTRAINT `template_laboratorium_ibfk_1` FOREIGN KEY (`kd_jenis_prw`) REFERENCES `jns_perawatan_lab` (`kd_jenis_prw`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `template_laboratorium` WRITE;
/*!40000 ALTER TABLE `template_laboratorium` DISABLE KEYS */;

INSERT INTO `template_laboratorium` (`kd_jenis_prw`, `id_template`, `Pemeriksaan`, `satuan`, `nilai_rujukan_ld`, `nilai_rujukan_la`, `nilai_rujukan_pd`, `nilai_rujukan_pa`, `bagian_rs`, `bhp`, `bagian_perujuk`, `bagian_dokter`, `bagian_laborat`, `kso`, `menejemen`, `biaya_item`, `urut`)
VALUES
	('LAB001',1,'Leukosit','LK','10','5','10','5',0,0,0,0,0,0,0,0,1),
	('LAB001',2,'Hemoglobin','HB','20','10','20','10',0,0,0,0,0,0,0,0,2);

/*!40000 ALTER TABLE `template_laboratorium` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table temporary_presensi
# ------------------------------------------------------------

CREATE TABLE `temporary_presensi` (
  `id` int(11) NOT NULL,
  `shift` enum('Pagi','Pagi2','Pagi3','Pagi4','Pagi5','Pagi6','Pagi7','Pagi8','Pagi9','Pagi10','Siang','Siang2','Siang3','Siang4','Siang5','Siang6','Siang7','Siang8','Siang9','Siang10','Malam','Malam2','Malam3','Malam4','Malam5','Malam6','Malam7','Malam8','Malam9','Malam10','Midle Pagi1','Midle Pagi2','Midle Pagi3','Midle Pagi4','Midle Pagi5','Midle Pagi6','Midle Pagi7','Midle Pagi8','Midle Pagi9','Midle Pagi10','Midle Siang1','Midle Siang2','Midle Siang3','Midle Siang4','Midle Siang5','Midle Siang6','Midle Siang7','Midle Siang8','Midle Siang9','Midle Siang10','Midle Malam1','Midle Malam2','Midle Malam3','Midle Malam4','Midle Malam5','Midle Malam6','Midle Malam7','Midle Malam8','Midle Malam9','Midle Malam10') NOT NULL,
  `jam_datang` datetime DEFAULT NULL,
  `jam_pulang` datetime DEFAULT NULL,
  `status` enum('Tepat Waktu','Terlambat Toleransi','Terlambat I','Terlambat II','Tepat Waktu & PSW','Terlambat Toleransi & PSW','Terlambat I & PSW','Terlambat II & PSW') NOT NULL,
  `keterlambatan` varchar(20) NOT NULL,
  `durasi` varchar(20) DEFAULT NULL,
  `photo` varchar(500) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `temporary_presensi_ibfk_1` FOREIGN KEY (`id`) REFERENCES `pegawai` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table utd_donor
# ------------------------------------------------------------

CREATE TABLE `utd_donor` (
  `no_donor` varchar(15) NOT NULL,
  `no_pendonor` varchar(15) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `dinas` enum('Pagi','Siang','Sore','Malam') DEFAULT NULL,
  `tensi` varchar(7) DEFAULT NULL,
  `no_bag` int(11) DEFAULT NULL,
  `jenis_bag` enum('SB','DB','TB','QB') DEFAULT NULL,
  `jenis_donor` enum('DB','DP','DS') DEFAULT NULL,
  `tempat_aftap` enum('Dalam Gedung','Luar Gedung') DEFAULT NULL,
  `petugas_aftap` varchar(20) DEFAULT NULL,
  `hbsag` enum('Negatif','Positif') DEFAULT NULL,
  `hcv` enum('Negatif','Positif') DEFAULT NULL,
  `hiv` enum('Negatif','Positif') DEFAULT NULL,
  `spilis` enum('Negatif','Positif') DEFAULT NULL,
  `malaria` enum('Negatif','Positif') DEFAULT NULL,
  `petugas_u_saring` varchar(20) DEFAULT NULL,
  `status` enum('Aman','Cekal') DEFAULT NULL,
  PRIMARY KEY (`no_donor`),
  KEY `petugas_aftap` (`petugas_aftap`),
  KEY `petugas_u_saring` (`petugas_u_saring`),
  KEY `no_pendonor` (`no_pendonor`),
  CONSTRAINT `utd_donor_ibfk_1` FOREIGN KEY (`petugas_aftap`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `utd_donor_ibfk_2` FOREIGN KEY (`petugas_u_saring`) REFERENCES `petugas` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `utd_donor_ibfk_3` FOREIGN KEY (`no_pendonor`) REFERENCES `utd_pendonor` (`no_pendonor`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table utd_komponen_darah
# ------------------------------------------------------------

CREATE TABLE `utd_komponen_darah` (
  `kode` varchar(5) NOT NULL,
  `nama` varchar(70) DEFAULT NULL,
  `lama` smallint(6) DEFAULT NULL,
  `jasa_sarana` double DEFAULT NULL,
  `paket_bhp` double DEFAULT NULL,
  `kso` double DEFAULT NULL,
  `manajemen` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `pembatalan` double DEFAULT NULL,
  PRIMARY KEY (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table utd_pendonor
# ------------------------------------------------------------

CREATE TABLE `utd_pendonor` (
  `no_pendonor` varchar(15) NOT NULL,
  `nama` varchar(40) NOT NULL,
  `no_ktp` varchar(20) NOT NULL,
  `jk` enum('L','P') NOT NULL,
  `tmp_lahir` varchar(15) NOT NULL,
  `tgl_lahir` date NOT NULL,
  `alamat` varchar(100) NOT NULL,
  `kd_kel` int(11) NOT NULL,
  `kd_kec` int(11) NOT NULL,
  `kd_kab` int(11) NOT NULL,
  `kd_prop` int(11) NOT NULL,
  `golongan_darah` enum('A','AB','B','O') NOT NULL,
  `resus` enum('(-)','(+)') NOT NULL,
  `no_telp` varchar(40) NOT NULL,
  PRIMARY KEY (`no_pendonor`),
  KEY `kd_kec` (`kd_kec`),
  KEY `kd_kab` (`kd_kab`),
  KEY `kd_prop` (`kd_prop`),
  KEY `kd_kel` (`kd_kel`) USING BTREE,
  CONSTRAINT `utd_pendonor_ibfk_1` FOREIGN KEY (`kd_kec`) REFERENCES `kecamatan` (`kd_kec`) ON UPDATE CASCADE,
  CONSTRAINT `utd_pendonor_ibfk_2` FOREIGN KEY (`kd_kab`) REFERENCES `kabupaten` (`kd_kab`) ON UPDATE CASCADE,
  CONSTRAINT `utd_pendonor_ibfk_3` FOREIGN KEY (`kd_prop`) REFERENCES `propinsi` (`kd_prop`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table utd_stok_darah
# ------------------------------------------------------------

CREATE TABLE `utd_stok_darah` (
  `no_kantong` varchar(20) NOT NULL DEFAULT '',
  `kode_komponen` varchar(5) DEFAULT NULL,
  `golongan_darah` enum('A','AB','B','O') DEFAULT NULL,
  `resus` enum('(-)','(+)') DEFAULT NULL,
  `tanggal_aftap` date DEFAULT NULL,
  `tanggal_kadaluarsa` date DEFAULT NULL,
  `asal_darah` enum('Hibah','Beli','Produksi Sendiri') DEFAULT NULL,
  `status` enum('Ada','Diambil','Dimusnahkan') DEFAULT NULL,
  PRIMARY KEY (`no_kantong`),
  KEY `kode_komponen` (`kode_komponen`),
  CONSTRAINT `utd_stok_darah_ibfk_1` FOREIGN KEY (`kode_komponen`) REFERENCES `utd_komponen_darah` (`kode`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `mlite_set_nomor_surat` (
  `nomor_surat` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
