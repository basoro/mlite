<?php

namespace Plugins\Api;

use Systems\SiteModule;
use Systems\Lib\PHPMailer\PHPMailer;
use Systems\Lib\PHPMailer\SMTP;
use Systems\Lib\PHPMailer\Exception;

class Site extends SiteModule
{
    public function routes()
    {
        $this->route('api', 'getIndex');
        $this->route('api/apam', 'getApam');
    }

    public function getIndex()
    {
        echo $this->draw('index.html');
        exit();
    }

    public function getApam()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');
        header("Access-Control-Allow-Headers: X-Requested-With");

        $key = $this->settings->get('api.apam_key');
        $token = trim(isset($_REQUEST['token'])?$_REQUEST['token']:null);
        if($token == $key) {
          $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";
          switch($action){
            case "signin":
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $no_ktp = trim($_REQUEST['no_ktp']);
              $pasien = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->where('no_ktp', $no_ktp)->oneArray();
              if($pasien) {
                $data['state'] = 'valid';
                $data['no_rkm_medis'] = $pasien['no_rkm_medis'];
              } else {
                $data['state'] = 'invalid';
              }
              echo json_encode($data);
            break;
            case "register":
              $nama_lengkap = trim($_REQUEST['nama_lengkap']);
              $email = trim($_REQUEST['email']);
              $nomor_ktp = trim($_REQUEST['nomor_ktp']);
              $nomor_telepon = trim($_REQUEST['nomor_telepon']);
              $this->db('mlite_apamregister')->where('email', $email)->delete();
              $pasien = $this->db('mlite_apamregister')->save([
                'nama_lengkap' => $nama_lengkap,
                'email' => $email,
                'nomor_ktp' => $nomor_ktp,
                'nomor_telepon' => $nomor_telepon
              ]);
              if($this->db('pasien')->where('no_ktp', $nomor_ktp)->orWhere('email', $email)->oneArray()) {
                $data['state'] = 'duplicate';
              } else if($pasien) {
                $rand = mt_rand(100000, 999999);
                $data['state'] = 'valid';
                $data['email'] = $email;
                $data['kode_validasi'] = $rand;
                $data['time_wait'] = time();
                $this->sendRegisterEmail($email, $nama_lengkap, $rand);
              } else {
                $data['state'] = 'invalid';
              }
              echo json_encode($data);
            break;
            case "postregister":
              $results = array();
              //$_REQUEST['email'] = '000009';
              $email = trim($_REQUEST['email']);
              $sql = "SELECT * FROM mlite_apamregister WHERE email = '$email'";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results[0]);
            break;
            case "saveregister":

              unset($_POST);

              $no_rkm_medis = '000001';
              /*$max_id = $this->db('pasien')->select(['no_rkm_medis' => 'ifnull(MAX(CONVERT(RIGHT(no_rkm_medis,6),signed)),0)'])->oneArray();
              if($max_id['no_rkm_medis']) {
                $no_rkm_medis = sprintf('%06s', ($max_id['no_rkm_medis'] + 1));
              }*/

              $last_no_rm = $this->db('set_no_rkm_medis')->oneArray();
              $last_no_rm = substr($last_no_rm['no_rkm_medis'], 0, 6);
              $next_no_rm = sprintf('%06s', ($last_no_rm + 1));
              $no_rkm_medis = $next_no_rm;

              $_POST['nm_pasien'] = trim($_REQUEST['nm_pasien']);
              $_POST['email'] = trim($_REQUEST['email']);
              $_POST['no_ktp'] = trim($_REQUEST['no_ktp']);
              $_POST['no_tlp'] = trim($_REQUEST['no_tlp']);

              $_POST['no_rkm_medis'] = $no_rkm_medis;
              $_POST['jk'] = trim($_REQUEST['jk']);
              $_POST['tmp_lahir'] = '-';
              $_POST['tgl_lahir'] = trim($_REQUEST['tgl_lahir']);
              $_POST['nm_ibu'] = '-';
              $_POST['alamat'] = trim($_REQUEST['alamat']);
              $_POST['gol_darah'] = '-';
              $_POST['pekerjaan'] = '-';
              $_POST['stts_nikah'] = 'JOMBLO';
              $_POST['agama'] = '-';
              $_POST['tgl_daftar'] = date('Y-m-d');
              $_POST['umur'] = $this->hitungUmur($_POST['tgl_lahir']);
              $_POST['pnd'] = '-';
              $_POST['keluarga'] = 'AYAH';
              $_POST['namakeluarga'] = '-';
              $_POST['kd_pj'] = $this->settings->get('api.apam_kdpj');
              $_POST['no_peserta'] = '';
              $_POST['kd_kel'] = '1';
              $_POST['kd_kec'] = $this->settings->get('api.apam_kdkec');
              $_POST['kd_kab'] = $this->settings->get('api.apam_kdkab');
              $_POST['pekerjaanpj'] = '-';
              $_POST['alamatpj'] = '-';
              $_POST['kelurahanpj'] = '-';
              $_POST['kecamatanpj'] = '-';
              $_POST['kabupatenpj'] = '-';
              $_POST['perusahaan_pasien'] = '-';
              $_POST['suku_bangsa'] = '1';
              $_POST['bahasa_pasien'] = '1';
              $_POST['cacat_fisik'] = '1';
              $_POST['nip'] = '';
              $_POST['kd_prop'] = $this->settings->get('api.apam_kdprop');
              $_POST['propinsipj'] = '-';

              $query = $this->db('pasien')->save($_POST);
              if($query) {
                $check_table = $this->db()->pdo()->query("SHOW TABLES LIKE 'set_no_rkm_medis'");
                $check_table->execute();
                $check_table = $check_table->fetch();
                if($check_table) {
                  $this->core->db()->pdo()->exec("UPDATE set_no_rkm_medis SET no_rkm_medis='$_POST[no_rkm_medis]'");
                }

                $this->db('mlite_apamregister')->where('email', $_POST['email'])->delete();

                $data['state'] = 'valid';
                $data['no_rkm_medis'] = $_POST['no_rkm_medis'];

              } else {
                $data['state'] = 'invalid';
              }

              echo json_encode($data);
            break;
            case "notifikasi":
              $results = array();
              //$_REQUEST['no_rkm_medis'] = '000009';
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $sql = "SELECT * FROM mlite_notifications WHERE no_rkm_medis = '$no_rkm_medis' AND status = 'unread'";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $result = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($result as $row) {
                $row['state'] = 'valid';
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "notifikasilist":
              $results = array();
              //$_REQUEST['no_rkm_medis'] = '000009';
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $result = $this->db('mlite_notifications')
                ->where('no_rkm_medis', $no_rkm_medis)
                ->desc('id')
                ->toArray();
              foreach ($result as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "tandaisudahdibaca":
              $id = trim($_REQUEST['id']);
              $this->db('mlite_notifications')->where('id', $id)->update('status', 'read');
            break;
            case "notifbooking":
              $data = array();
              //$_REQUEST['no_rkm_medis'] = '000009';
              $date = date('Y-m-d');
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $sql = "SELECT stts FROM reg_periksa WHERE tgl_registrasi = '$date' AND no_rkm_medis = '$no_rkm_medis' AND (stts = 'Belum' OR stts = 'Berkas Diterima')";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $result = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($result as $row) {
                $results[] = $row;
              }

              if(!$result) {
                $data['state'] = 'invalid';
                echo json_encode($data);
              } else {
                if($results[0]["stts"] == 'Belum') {
                  $data['state'] = 'notifbooking';
                  $data['stts'] = $this->settings->get('api.apam_status_daftar');
                  echo json_encode($data);
                } else if($results[0]["stts"] == 'Berkas Diterima') {
                    $data['state'] = 'notifberkas';
                    $data['stts'] = $this->settings->get('api.apam_status_dilayani');
                    echo json_encode($data);
                } else {
                  $data['state'] = 'invalid';
                  echo json_encode($data);
                }
              }
            break;
            case "antrian":
              $data['state'] = 'valid';
              echo json_encode($data);
            break;
            case "booking":
              $results = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $sql = "SELECT a.tanggal_booking, a.tanggal_periksa, a.no_reg, a.status, b.nm_poli, c.nm_dokter, d.png_jawab FROM booking_registrasi a LEFT JOIN poliklinik b ON a.kd_poli = b.kd_poli LEFT JOIN dokter c ON a.kd_dokter = c.kd_dokter LEFT JOIN penjab d ON a.kd_pj = d.kd_pj WHERE a.no_rkm_medis = '$no_rkm_medis' ORDER BY a.tanggal_periksa DESC";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "lastbooking":
              $data['state'] = 'valid';
              echo json_encode($data);
            break;
            case "bookingdetail":
              $results = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $tanggal_periksa = trim($_REQUEST['tanggal_periksa']);
              $no_reg = trim($_REQUEST['no_reg']);
              $sql = "SELECT a.tanggal_booking, a.tanggal_periksa, a.no_reg, a.status, b.nm_poli, c.nm_dokter, d.png_jawab FROM booking_registrasi a LEFT JOIN poliklinik b ON a.kd_poli = b.kd_poli LEFT JOIN dokter c ON a.kd_dokter = c.kd_dokter LEFT JOIN penjab d ON a.kd_pj = d.kd_pj WHERE a.no_rkm_medis = '$no_rkm_medis' AND a.tanggal_periksa = '$tanggal_periksa' AND a.no_reg = '$no_reg'";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "kamar":
              $results = array();
              $query = $this->db()->pdo()->prepare("SELECT nama.kelas, (SELECT COUNT(*) FROM kamar WHERE kelas=nama.kelas AND statusdata='1') AS total, (SELECT COUNT(*) FROM kamar WHERE  kelas=nama.kelas AND statusdata='1' AND status='ISI') AS isi, (SELECT COUNT(*) FROM kamar WHERE  kelas=nama.kelas AND statusdata='1' AND status='KOSONG') AS kosong FROM (SELECT DISTINCT kelas FROM kamar WHERE statusdata='1') AS nama ORDER BY nama.kelas ASC");
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "dokter":
              $tanggal = @$_REQUEST['tanggal'];

              if($tanggal) {
                $getTanggal = $tanggal;
              } else {
                $getTanggal = date('Y-m-d');
              }
              $results = array();

              $hari = $this->db()->pdo()->prepare("SELECT DAYNAME('$getTanggal') AS dt");
              $hari->execute();
              $hari = $hari->fetch(\PDO::FETCH_OBJ);

              $namahari = "";
              if($hari->dt == "Sunday"){
                  $namahari = "AKHAD";
              }else if($hari->dt == "Monday"){
                  $namahari = "SENIN";
              }else if($hari->dt == "Tuesday"){
                 	$namahari = "SELASA";
              }else if($hari->dt == "Wednesday"){
                  $namahari = "RABU";
              }else if($hari->dt == "Thursday"){
                  $namahari = "KAMIS";
              }else if($hari->dt == "Friday"){
                  $namahari = "JUMAT";
              }else if($hari->dt == "Saturday"){
                  $namahari = "SABTU";
              }

              $sql = $this->db()->pdo()->prepare("SELECT dokter.nm_dokter, dokter.jk, poliklinik.nm_poli, DATE_FORMAT(jadwal.jam_mulai, '%H:%i') AS jam_mulai, DATE_FORMAT(jadwal.jam_selesai, '%H:%i') AS jam_selesai, dokter.kd_dokter FROM jadwal INNER JOIN dokter INNER JOIN poliklinik on dokter.kd_dokter=jadwal.kd_dokter AND jadwal.kd_poli=poliklinik.kd_poli WHERE jadwal.hari_kerja='$namahari'");
              $sql->execute();
              $result = $sql->fetchAll(\PDO::FETCH_ASSOC);

              if(!$result){
                $send_data['state'] = 'notfound';
                echo json_encode($send_data);
              } else {
                foreach ($result as $row) {
                  $results[] = $row;
                }
                echo json_encode($results);
              }
            break;
            case "riwayat":
              $results = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $query = $this->db()->pdo()->prepare("SELECT a.tgl_registrasi, a.no_rawat, a.no_reg, b.nm_poli, c.nm_dokter, d.png_jawab FROM reg_periksa a LEFT JOIN poliklinik b ON a.kd_poli = b.kd_poli LEFT JOIN dokter c ON a.kd_dokter = c.kd_dokter LEFT JOIN penjab d ON a.kd_pj = d.kd_pj WHERE a.no_rkm_medis = '$no_rkm_medis' AND a.stts = 'Sudah' ORDER BY a.tgl_registrasi DESC");
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "riwayatdetail":
              $results = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $tgl_registrasi = trim($_REQUEST['tgl_registrasi']);
              $no_reg = trim($_REQUEST['no_reg']);
              $query = $this->db()->pdo()->prepare("SELECT a.tgl_registrasi, a.no_rawat, a.no_reg, b.nm_poli, c.nm_dokter, d.png_jawab, e.keluhan, e.pemeriksaan, GROUP_CONCAT(DISTINCT g.nm_penyakit SEPARATOR '<br>') AS nm_penyakit, GROUP_CONCAT(DISTINCT i.nama_brng SEPARATOR '<br>') AS nama_brng, GROUP_CONCAT(CONCAT_WS(':', k.pemeriksaan, j.nilai)SEPARATOR '<br>') AS pemeriksaan_lab, GROUP_CONCAT(CONCAT_WS(':', m.nm_perawatan, n.hasil)SEPARATOR '<br>') AS hasil_radiologi, GROUP_CONCAT(DISTINCT o.lokasi_gambar SEPARATOR '<br>') AS gambar_radiologi FROM reg_periksa a LEFT JOIN poliklinik b ON a.kd_poli = b.kd_poli LEFT JOIN dokter c ON a.kd_dokter = c.kd_dokter LEFT JOIN penjab d ON a.kd_pj = d.kd_pj LEFT JOIN pemeriksaan_ralan e ON a.no_rawat = e.no_rawat LEFT JOIN diagnosa_pasien f ON a.no_rawat = f.no_rawat LEFT JOIN penyakit g ON f.kd_penyakit = g.kd_penyakit LEFT JOIN detail_pemberian_obat h ON a.no_rawat = h.no_rawat LEFT JOIN databarang i ON h.kode_brng = i.kode_brng LEFT JOIN detail_periksa_lab j ON a.no_rawat = j.no_rawat LEFT JOIN template_laboratorium k ON j.id_template = k.id_template LEFT JOIN periksa_radiologi l ON a.no_rawat = l.no_rawat LEFT JOIN jns_perawatan_radiologi m ON l.kd_jenis_prw = m.kd_jenis_prw LEFT JOIN hasil_radiologi n ON a.no_rawat = n.no_rawat LEFT JOIN gambar_radiologi o ON a.no_rawat = o.no_rawat WHERE a.no_rkm_medis = '$no_rkm_medis' AND a.tgl_registrasi = '$tgl_registrasi' AND a.no_reg = '$no_reg' GROUP BY a.no_rawat");
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "riwayatranap":
              $results = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $query = $this->db()->pdo()->prepare("SELECT reg_periksa.tgl_registrasi, reg_periksa.no_reg, dokter.nm_dokter, bangsal.nm_bangsal, penjab.png_jawab, reg_periksa.no_rawat FROM kamar_inap, reg_periksa, pasien, bangsal, kamar, penjab, dokter, dpjp_ranap WHERE kamar_inap.no_rawat = reg_periksa.no_rawat AND reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND kamar_inap.no_rawat = reg_periksa.no_rawat AND kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal AND reg_periksa.kd_pj = penjab.kd_pj AND dpjp_ranap.no_rawat = reg_periksa.no_rawat AND dpjp_ranap.kd_dokter = dokter.kd_dokter AND pasien.no_rkm_medis = '$no_rkm_medis' ORDER BY reg_periksa.tgl_registrasi DESC");
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "riwayatranapdetail":
              $results = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $tgl_registrasi = trim($_REQUEST['tgl_registrasi']);
              $no_reg = trim($_REQUEST['no_reg']);
              $sql = "SELECT
                  a.tgl_registrasi,
                  a.no_rawat,
                  a.no_reg,
                  b.nm_bangsal,
                  c.nm_dokter,
                  d.png_jawab,
                  GROUP_CONCAT(DISTINCT e.keluhan SEPARATOR '<br>') AS keluhan,
                  GROUP_CONCAT(DISTINCT e.pemeriksaan SEPARATOR '<br>') AS pemeriksaan,
                  GROUP_CONCAT(DISTINCT g.nm_penyakit SEPARATOR '<br>') AS nm_penyakit,
                  GROUP_CONCAT(DISTINCT i.nama_brng SEPARATOR '<br>') AS nama_brng,
                  GROUP_CONCAT(CONCAT_WS(':', m.pemeriksaan, l.nilai)SEPARATOR '<br>') AS pemeriksaan_lab,
                  GROUP_CONCAT(CONCAT_WS(':', o.nm_perawatan, p.hasil)SEPARATOR '<br>') AS hasil_radiologi,
                  GROUP_CONCAT(DISTINCT q.lokasi_gambar SEPARATOR '<br>') AS gambar_radiologi
                FROM reg_periksa a
                LEFT JOIN kamar_inap j ON a.no_rawat = j.no_rawat
                LEFT JOIN kamar k ON j.kd_kamar = k.kd_kamar
                LEFT JOIN bangsal b ON k.kd_bangsal = b.kd_bangsal
                LEFT JOIN dokter c ON a.kd_dokter = c.kd_dokter
                LEFT JOIN penjab d ON a.kd_pj = d.kd_pj
                LEFT JOIN pemeriksaan_ranap e ON a.no_rawat = e.no_rawat
                LEFT JOIN diagnosa_pasien f ON a.no_rawat = f.no_rawat
                LEFT JOIN penyakit g ON f.kd_penyakit = g.kd_penyakit
                LEFT JOIN detail_pemberian_obat h ON a.no_rawat = h.no_rawat
                LEFT JOIN databarang i ON h.kode_brng = i.kode_brng
                LEFT JOIN detail_periksa_lab l ON a.no_rawat = l.no_rawat
                LEFT JOIN template_laboratorium m ON l.id_template = m.id_template
                LEFT JOIN periksa_radiologi n ON a.no_rawat = n.no_rawat
                LEFT JOIN jns_perawatan_radiologi o ON n.kd_jenis_prw = o.kd_jenis_prw
                LEFT JOIN hasil_radiologi p ON a.no_rawat = p.no_rawat
                LEFT JOIN gambar_radiologi q ON a.no_rawat = q.no_rawat
                WHERE a.no_rkm_medis = '$no_rkm_medis'
                AND a.tgl_registrasi = '$tgl_registrasi'
                AND a.no_reg = '$no_reg'
                GROUP BY a.no_rawat";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "billing":
              $results = array();
              //$_REQUEST['no_rkm_medis'] = '000009';
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $query = $this->db()->pdo()->prepare("SELECT a.tgl_registrasi, a.no_rawat, a.no_reg, b.nm_poli, c.nm_dokter, d.png_jawab, e.kd_billing, e.jumlah_harus_bayar FROM reg_periksa a LEFT JOIN poliklinik b ON a.kd_poli = b.kd_poli LEFT JOIN dokter c ON a.kd_dokter = c.kd_dokter LEFT JOIN penjab d ON a.kd_pj = d.kd_pj INNER JOIN mlite_billing e ON a.no_rawat = e.no_rawat WHERE a.no_rkm_medis = '$no_rkm_medis' AND a.stts = 'Sudah' ORDER BY e.tgl_billing, e.jam_billing DESC");
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $row['total_bayar'] = number_format($row['jumlah_harus_bayar'],2,',','.');
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "profil":
              $results = array();
              //$_REQUEST['no_rkm_medis'] = '000009';
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $sql = "SELECT * FROM pasien WHERE no_rkm_medis = '$no_rkm_medis'";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $personal_pasien = $this->db('personal_pasien')->where('no_rkm_medis', $row['no_rkm_medis'])->oneArray();
                $row['foto'] = 'img/'.$row['jk'].'.png';
                if($personal_pasien) {
                  $row['foto'] = $this->settings->get('api.apam_webappsurl').'/photopasien/'.$personal_pasien['gambar'];
                }
                $results[] = $row;
              }
              echo json_encode($results[0]);
            break;
            case "jadwalklinik":
              $results = array();
              $tanggal = trim($_REQUEST['tanggal']);

              $tentukan_hari=date('D',strtotime($tanggal));
              $day = array(
                'Sun' => 'AKHAD',
                'Mon' => 'SENIN',
                'Tue' => 'SELASA',
                'Wed' => 'RABU',
                'Thu' => 'KAMIS',
                'Fri' => 'JUMAT',
                'Sat' => 'SABTU'
              );
              $hari=$day[$tentukan_hari];

              $sql = "SELECT a.kd_poli, b.nm_poli, DATE_FORMAT(a.jam_mulai, '%H:%i') AS jam_mulai, DATE_FORMAT(a.jam_selesai, '%H:%i') AS jam_selesai FROM jadwal a, poliklinik b, dokter c WHERE a.kd_poli = b.kd_poli AND a.kd_dokter = c.kd_dokter AND a.hari_kerja LIKE '%$hari%' GROUP BY b.kd_poli";

              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "jadwaldokter":
              $results = array();
              $tanggal = trim($_REQUEST['tanggal']);
              $kd_poli = trim($_REQUEST['kd_poli']);

              $tentukan_hari=date('D',strtotime($tanggal));
              $day = array(
                'Sun' => 'AKHAD',
                'Mon' => 'SENIN',
                'Tue' => 'SELASA',
                'Wed' => 'RABU',
                'Thu' => 'KAMIS',
                'Fri' => 'JUMAT',
                'Sat' => 'SABTU'
              );
              $hari=$day[$tentukan_hari];

              $sql = "SELECT a.kd_dokter, c.nm_dokter FROM jadwal a, poliklinik b, dokter c WHERE a.kd_poli = b.kd_poli AND a.kd_dokter = c.kd_dokter AND a.kd_poli = '$kd_poli' AND a.hari_kerja LIKE '%$hari%'";

              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "carabayar":
              $results = array();
              $sql = "SELECT * FROM penjab";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "daftar":
              $send_data = array();

              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $tanggal = trim($_REQUEST['tanggal']);
              $kd_poli = trim($_REQUEST['kd_poli']);
              $kd_dokter = trim($_REQUEST['kd_dokter']);
              $kd_pj = trim($_REQUEST['kd_pj']);

              $tentukan_hari=date('D',strtotime($tanggal));
              $day = array(
                'Sun' => 'AKHAD',
                'Mon' => 'SENIN',
                'Tue' => 'SELASA',
                'Wed' => 'RABU',
                'Thu' => 'KAMIS',
                'Fri' => 'JUMAT',
                'Sat' => 'SABTU'
              );
              $hari=$day[$tentukan_hari];

              $jadwal = $this->db('jadwal')->where('kd_poli', $kd_poli)->where('hari_kerja', $hari)->oneArray();

              $check_kuota = $this->db('booking_registrasi')->select(['count' => 'COUNT(DISTINCT no_reg)'])->where('kd_poli', $kd_poli)->where('tanggal_periksa', $tanggal)->oneArray();

              if($this->settings->get('settings.dokter_ralan_per_dokter') == 'true') {
                $check_kuota = $this->db('booking_registrasi')->select(['count' => 'COUNT(DISTINCT no_reg)'])->where('kd_poli', $kd_poli)->where('kd_dokter', $kd_dokter)->where('tanggal_periksa', $tanggal)->oneArray();
              }

              $curr_count = $check_kuota['count'];
              $curr_kuota = $jadwal['kuota'];
              $online = $curr_kuota / $this->settings->get('api.apam_limit');

              $check = $this->db('booking_registrasi')->where('no_rkm_medis', $no_rkm_medis)->where('tanggal_periksa', $tanggal)->oneArray();

              if($curr_count > $online) {
                $send_data['state'] = 'limit';
                echo json_encode($send_data);
              }
              else if(!$check) {
                  $mysql_date = date( 'Y-m-d' );
                  $mysql_time = date( 'H:m:s' );
                  $waktu_kunjungan = $tanggal . ' ' . $mysql_time;

                  $max_id = $this->db('booking_registrasi')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $kd_poli)->where('tanggal_periksa', $tanggal)->desc('no_reg')->limit(1)->oneArray();
                  if($this->settings->get('settings.dokter_ralan_per_dokter') == 'true') {
                    $max_id = $this->db('booking_registrasi')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $kd_poli)->where('kd_dokter', $kd_dokter)->where('tanggal_periksa', $tanggal)->desc('no_reg')->limit(1)->oneArray();
                  }
                  if(empty($max_id['no_reg'])) {
                    $max_id['no_reg'] = '000';
                  }
                  $no_reg = sprintf('%03s', ($max_id['no_reg'] + 1));

                  unset($_POST);
                  $_POST['no_rkm_medis'] = $no_rkm_medis;
                  $_POST['tanggal_periksa'] = $tanggal;
                  $_POST['kd_poli'] = $kd_poli;
                  $_POST['kd_dokter'] = $kd_dokter;
                  $_POST['kd_pj'] = $kd_pj;
                  $_POST['no_reg'] = $no_reg;
                  $_POST['tanggal_booking'] = $mysql_date;
                  $_POST['jam_booking'] = $mysql_time;
                  $_POST['waktu_kunjungan'] = $waktu_kunjungan;
                  $_POST['limit_reg'] = '1';
                  $_POST['status'] = 'Belum';

                  $this->db('booking_registrasi')->save($_POST);

                  $send_data['state'] = 'success';
                  echo json_encode($send_data);

                  $get_pasien = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
                  $get_poliklinik = $this->db('poliklinik')->where('kd_poli', $kd_poli)->oneArray();
                  if($get_pasien['no_tlp'] !='') {
                    $ch = curl_init();
                    $url = "https://wa.basoro.id/api/send-message.php";
                    curl_setopt($ch, CURLOPT_URL,$url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, "api_key=".$this->settings->get('settings.waapitoken')."&sender=".$this->settings->get('settings.waapiphonenumber')."&number=".$get_pasien['no_tlp']."&message=Terima kasih sudah melakukan pendaftaran Online di ".$this->settings->get('settings.nama_instansi').". \n\nDetail pendaftaran anda adalah, \nTanggal: ".date('Y-m-d', strtotime($waktu_kunjungan))." \nNomor Antrian: ".$no_reg." \nPoliklinik: ".$get_poliklinik['nm_poli']." \nStatus: Menunggu \n\nBawalah kartu berobat anda. \nDatanglah 30 menit sebelumnya.\n\n-------------------\nPesan WhatsApp ini dikirim otomatis oleh ".$this->settings->get('settings.nama_instansi')." \nTerima Kasih"); // Define what you want to post
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $output = curl_exec ($ch);
                    curl_close ($ch);
                  }

              }
              else{
                  $send_data['state'] = 'duplication';
                  echo json_encode($send_data);
              }
            break;
            case "sukses":
              $results = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $date = date('Y-m-d');
              $sql = "SELECT a.tanggal_booking, a.tanggal_periksa, a.no_reg, a.status, b.nm_poli, c.nm_dokter, d.png_jawab FROM booking_registrasi a LEFT JOIN poliklinik b ON a.kd_poli = b.kd_poli LEFT JOIN dokter c ON a.kd_dokter = c.kd_dokter LEFT JOIN penjab d ON a.kd_pj = d.kd_pj WHERE a.no_rkm_medis = '$no_rkm_medis' AND a.tanggal_booking = '$date' AND a.jam_booking = (SELECT MAX(ax.jam_booking) FROM booking_registrasi ax WHERE ax.tanggal_booking = a.tanggal_booking) ORDER BY a.tanggal_booking ASC LIMIT 1";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results, JSON_PRETTY_PRINT);
            break;
            case "pengaduan":
              $results = array();
              $petugas_array = explode(',', $this->settings->get('api.apam_normpetugas'));
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $sql = "SELECT a.*, b.nm_pasien, b.jk FROM mlite_pengaduan a, pasien b WHERE a.no_rkm_medis = b.no_rkm_medis";
              if(in_array($no_rkm_medis, $petugas_array)) {
                $sql .= "";
              } else {
               $sql .= " AND a.no_rkm_medis = '$no_rkm_medis'";
              }
              $sql .= " ORDER BY a.tanggal DESC";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "pengaduandetail":
              $results = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $pengaduan_id = trim($_REQUEST['pengaduan_id']);
              $sql = $this->db()->pdo()->prepare("SELECT * FROM mlite_pengaduan_detail WHERE pengaduan_id = '$pengaduan_id'");
              $sql->execute();
              $result = $sql->fetchAll(\PDO::FETCH_ASSOC);

              if(!$result) {
                $data['state'] = 'invalid';
                echo json_encode($data);
              } else {
                foreach ($result as $row) {
                  $pasien = $this->db('pasien')->where('no_rkm_medis', $row['no_rkm_medis'])->oneArray();
                  $row['nama'] = $pasien['nm_pasien'];
                  $results[] = $row;
                }
                echo json_encode($results);
              }
            break;
            case "simpanpengaduan":
              $send_data = array();
              $max_id = $this->db('mlite_pengaduan')->select(['id' => 'ifnull(MAX(CONVERT(RIGHT(id,6),signed)),0)'])->like('tanggal', ''.date('Y-m-d').'%')->oneArray();
              if(empty($max_id['id'])) {
                $max_id['id'] = '000000';
              }
              $_next_id = sprintf('%06s', ($max_id['id'] + 1));
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $message = trim($_REQUEST['message']);
              unset($_POST);
              $_POST['id'] = date('Ymd').''.$_next_id;
              $_POST['no_rkm_medis'] = $no_rkm_medis;
              $_POST['pesan'] = $message;
              $_POST['tanggal'] = date('Y-m-d H:i:s');

              $this->db('mlite_pengaduan')->save($_POST);

              $send_data['state'] = 'success';
              echo json_encode($send_data);
            break;
            case "simpanpengaduandetail":
              $send_data = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $message = trim($_REQUEST['message']);
              $pengaduan_id = trim($_REQUEST['pengaduan_id']);

              unset($_POST);
              $_POST['pengaduan_id'] = $pengaduan_id;
              $_POST['no_rkm_medis'] = $no_rkm_medis;
              $_POST['pesan'] = $message;
              $_POST['tanggal'] = date('Y-m-d H:i:s');
              $this->db('mlite_pengaduan_detail')->save($_POST);

              $send_data['state'] = 'success';
              echo json_encode($send_data);
            break;
            case "cekrujukan":
              $data['state'] = 'valid';
              echo json_encode($data);
            break;
            case "rawatjalan":
              $results = array();
              $sql = "SELECT * FROM poliklinik WHERE status = '1'";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $row['registrasi'] = number_format($row['registrasi'],2,',','.');
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "rawatinap":
              $results = array();
              $sql = "SELECT bangsal.*, kamar.* FROM bangsal, kamar WHERE kamar.statusdata = '1' AND bangsal.kd_bangsal = kamar.kd_bangsal";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $row['trf_kamar'] = number_format($row['trf_kamar'],2,',','.');
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "laboratorium":
              $results = array();
              $sql = "SELECT * FROM jns_perawatan_lab WHERE status = '1'";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "radiologi":
              $results = array();
              $sql = "SELECT * FROM jns_perawatan_radiologi WHERE status = '1'";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "hitungralan":
              //$_REQUEST['no_rkm_medis'] = '000009';
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $hitung = $this->db('reg_periksa')->select(['count' => 'COUNT(DISTINCT no_rawat)'])->where('no_rkm_medis', $no_rkm_medis)->oneArray();
              echo $hitung['count'];
            break;
            case "hitungranap":
              //$_REQUEST['no_rkm_medis'] = '000009';
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $hitung = $this->db('kamar_inap')->select(['count' => 'COUNT(DISTINCT kamar_inap.no_rawat)'])->join('reg_periksa', 'reg_periksa.no_rawat=kamar_inap.no_rawat')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
              echo $hitung['count'];
            break;
            case "layananunggulan":
              $data[] = array_column($this->db('mlite_settings')->where('module', 'website')->toArray(), 'value', 'field');
              echo json_encode($data);
            break;
            case "lastblog":
              $limit = $this->settings->get('blog.latestPostsCount');
              $results = [];
              $rows = $this->db('mlite_blog')
                      ->leftJoin('mlite_users', 'mlite_users.id = mlite_blog.user_id')
                      ->where('status', 2)
                      ->where('published_at', '<=', time())
                      ->desc('published_at')
                      ->limit($limit)
                      ->select(['mlite_blog.id', 'mlite_blog.title', 'mlite_blog.cover_photo', 'mlite_blog.published_at', 'mlite_blog.slug', 'mlite_blog.intro', 'mlite_blog.content', 'mlite_users.username', 'mlite_users.fullname'])
                      ->toArray();

              foreach ($rows as &$row) {
                  //$this->filterRecord($row);
                  $tags = $this->db('mlite_blog_tags')
                      ->leftJoin('mlite_blog_tags_relationship', 'mlite_blog_tags.id = mlite_blog_tags_relationship.tag_id')
                      ->where('mlite_blog_tags_relationship.blog_id', $row['id'])
                      ->select('name')
                      ->oneArray();
                  $row['tag'] = $tags['name'];
                  $row['tanggal'] = getDayIndonesia(date('Y-m-d', date($row['published_at']))).', '.dateIndonesia(date('Y-m-d', date($row['published_at'])));
                  $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "blog":
              $results = [];
              $rows = $this->db('mlite_blog')
                      ->leftJoin('mlite_users', 'mlite_users.id = mlite_blog.user_id')
                      ->where('status', 2)
                      ->where('published_at', '<=', time())
                      ->desc('published_at')
                      ->select(['mlite_blog.id', 'mlite_blog.title', 'mlite_blog.cover_photo', 'mlite_blog.published_at', 'mlite_blog.slug', 'mlite_blog.intro', 'mlite_blog.content', 'mlite_users.username', 'mlite_users.fullname'])
                      ->toArray();

              foreach ($rows as &$row) {
                  //$this->filterRecord($row);
                  $tags = $this->db('mlite_blog_tags')
                      ->leftJoin('mlite_blog_tags_relationship', 'mlite_blog_tags.id = mlite_blog_tags_relationship.tag_id')
                      ->where('mlite_blog_tags_relationship.blog_id', $row['id'])
                      ->select('name')
                      ->oneArray();
                  $row['tag'] = $tags['name'];
                  $row['tanggal'] = getDayIndonesia(date('Y-m-d', date($row['published_at']))).', '.dateIndonesia(date('Y-m-d', date($row['published_at'])));
                  $results[] = $row;
              }
              echo json_encode($results);
            break;
            case "blogdetail":
              $id = trim($_REQUEST['id']);
              $results = [];
              $rows = $this->db('mlite_blog')
                      ->where('id', $id)
                      ->select(['id','title','cover_photo', 'content', 'published_at'])
                      ->oneArray();
              $rows['tanggal'] = getDayIndonesia(date('Y-m-d', date($rows['published_at']))).', '.dateIndonesia(date('Y-m-d', date($rows['published_at'])));
              $results[] = $rows;
              echo json_encode($results);
            break;
            case "telemedicine":
              $tanggal = @$_REQUEST['tanggal'];

              if($tanggal) {
                $getTanggal = $tanggal;
              } else {
                $getTanggal = date('Y-m-d');
              }
              $results = array();

              $hari = $this->db()->pdo()->prepare("SELECT DAYNAME('$getTanggal') AS dt");
              $hari->execute();
              $hari = $hari->fetch(\PDO::FETCH_OBJ);

              $namahari = "";
              if($hari->dt == "Sunday"){
                  $namahari = "AKHAD";
              }else if($hari->dt == "Monday"){
                  $namahari = "SENIN";
              }else if($hari->dt == "Tuesday"){
                  $namahari = "SELASA";
              }else if($hari->dt == "Wednesday"){
                  $namahari = "RABU";
              }else if($hari->dt == "Thursday"){
                  $namahari = "KAMIS";
              }else if($hari->dt == "Friday"){
                  $namahari = "JUMAT";
              }else if($hari->dt == "Saturday"){
                  $namahari = "SABTU";
              }

              $sql = $this->db()->pdo()->prepare("SELECT dokter.nm_dokter, dokter.jk, poliklinik.nm_poli, DATE_FORMAT(jadwal.jam_mulai, '%H:%i') AS jam_mulai, DATE_FORMAT(jadwal.jam_selesai, '%H:%i') AS jam_selesai, dokter.kd_dokter, poliklinik.kd_poli FROM jadwal INNER JOIN dokter INNER JOIN poliklinik on dokter.kd_dokter=jadwal.kd_dokter AND jadwal.kd_poli=poliklinik.kd_poli WHERE jadwal.hari_kerja='$namahari'");
              $sql->execute();
              $result = $sql->fetchAll(\PDO::FETCH_ASSOC);

              if(!$result){
                $send_data['state'] = 'notfound';
                echo json_encode($send_data);
              } else {
                foreach ($result as $row) {
                  $row['biaya'] = $this->settings->get('api.duitku_paymentAmount');
                  $results[] = $row;
                }
                echo json_encode($results);
              }
            break;
            case "duitku_callback":
              $apiKey = $this->settings->get('api.duitku_merchantKey'); // from duitku // settings.duitku_merchantKey
              $merchantCode = isset($_POST['merchantCode']) ? $_POST['merchantCode'] : null;
              $amount = isset($_POST['amount']) ? $_POST['amount'] : null;
              $merchantOrderId = isset($_POST['merchantOrderId']) ? $_POST['merchantOrderId'] : null;
              $productDetail = isset($_POST['productDetail']) ? $_POST['productDetail'] : null;
              $additionalParam = isset($_POST['additionalParam']) ? $_POST['additionalParam'] : null;
              $paymentMethod = isset($_POST['paymentCode']) ? $_POST['paymentCode'] : null;
              $resultCode = isset($_POST['resultCode']) ? $_POST['resultCode'] : null;
              $merchantUserId = isset($_POST['merchantUserId']) ? $_POST['merchantUserId'] : null;
              $reference = isset($_POST['reference']) ? $_POST['reference'] : null;
              $signature = isset($_POST['signature']) ? $_POST['signature'] : null;

              if(!empty($merchantCode) && !empty($amount) && !empty($merchantOrderId) && !empty($signature)) {
                  $params = $merchantCode . $amount . $merchantOrderId . $apiKey;
                  $calcSignature = md5($params);
                  if($signature == $calcSignature) {
                      //Your code here
                  	  if($resultCode == "00") {
                  	     echo "SUCCESS"; // Save to database
                     	} else {
                         echo "FAILED"; // Please update the status to FAILED in database
                      }
                  } else {
                      throw new Exception('Bad Signature');
                  }
              }
              else
              {
                  throw new Exception('Bad Parameter');
              }
            break;
            case "telemedicinedaftar":
              $send_data = array();

              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $tanggal = trim($_REQUEST['tanggal']);
              $kd_poli = trim($_REQUEST['kd_poli']);
              $kd_dokter = trim($_REQUEST['kd_dokter']);
              $kd_pj = $this->settings->get('api.duitku_kdpj');

              $tentukan_hari=date('D',strtotime($tanggal));
              $day = array(
                'Sun' => 'AKHAD',
                'Mon' => 'SENIN',
                'Tue' => 'SELASA',
                'Wed' => 'RABU',
                'Thu' => 'KAMIS',
                'Fri' => 'JUMAT',
                'Sat' => 'SABTU'
              );
              $hari=$day[$tentukan_hari];

              $jadwal = $this->db('jadwal')->where('kd_poli', $kd_poli)->where('hari_kerja', $hari)->oneArray();

              $check_kuota = $this->db('booking_registrasi')->select(['count' => 'COUNT(DISTINCT no_reg)'])->where('kd_poli', $kd_poli)->where('tanggal_periksa', $tanggal)->oneArray();

              if($this->settings->get('settings.dokter_ralan_per_dokter') == 'true') {
                $check_kuota = $this->db('booking_registrasi')->select(['count' => 'COUNT(DISTINCT no_reg)'])->where('kd_poli', $kd_poli)->where('kd_dokter', $kd_dokter)->where('tanggal_periksa', $tanggal)->oneArray();
              }

              $curr_count = $check_kuota['count'];
              $curr_kuota = $jadwal['kuota'];
              $online = $curr_kuota / $this->settings->get('api.apam_limit');

              $check = $this->db('booking_registrasi')->where('no_rkm_medis', $no_rkm_medis)->where('tanggal_periksa', $tanggal)->oneArray();

              if($curr_count > $online) {
                $send_data['state'] = 'limit';
                echo json_encode($send_data);
              }
              else if(!$check) {
                  $mysql_date = date( 'Y-m-d' );
                  $mysql_time = date( 'H:m:s' );
                  $waktu_kunjungan = $tanggal . ' ' . $mysql_time;

                  $max_id = $this->db('booking_registrasi')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $kd_poli)->where('tanggal_periksa', $tanggal)->desc('no_reg')->limit(1)->oneArray();
                  if($this->settings->get('settings.dokter_ralan_per_dokter') == 'true') {
                    $max_id = $this->db('booking_registrasi')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $kd_poli)->where('kd_dokter', $kd_dokter)->where('tanggal_periksa', $tanggal)->desc('no_reg')->limit(1)->oneArray();
                  }
                  if(empty($max_id['no_reg'])) {
                    $max_id['no_reg'] = '000';
                  }
                  $no_reg = sprintf('%03s', ($max_id['no_reg'] + 1));

                  unset($_POST);
                  $_POST['no_rkm_medis'] = $no_rkm_medis;
                  $_POST['tanggal_periksa'] = $tanggal;
                  $_POST['kd_poli'] = $kd_poli;
                  $_POST['kd_dokter'] = $kd_dokter;
                  $_POST['kd_pj'] = $kd_pj;
                  $_POST['no_reg'] = $no_reg;
                  $_POST['tanggal_booking'] = $mysql_date;
                  $_POST['jam_booking'] = $mysql_time;
                  $_POST['waktu_kunjungan'] = $waktu_kunjungan;
                  $_POST['limit_reg'] = '1';
                  $_POST['status'] = 'Belum';

                  $this->db('booking_registrasi')->save($_POST);

                  $send_data['state'] = 'success';
                  echo json_encode($send_data);


                  $pasien = $this->db('pasien')->where('no_rkm_medis', $_REQUEST['no_rkm_medis'])->oneArray();
                  $merchantCode = $this->settings->get('api.duitku_merchantCode'); // from duitku // settings.duitku_merchantCode
                  $merchantKey = $this->settings->get('api.duitku_merchantKey'); // from duitku // settings.duitku_merchantKey
                  $paymentAmount = $this->settings->get('api.duitku_paymentAmount'); // settings.duitku_paymentAmount
                  $paymentMethod = $this->settings->get('api.duitku_paymentMethod'); // WW = duitku wallet, VC = Credit Card, MY = Mandiri Clickpay, BK = BCA KlikPay
                  $merchantOrderId = time(); // from merchant, unique
                  $productDetails = $this->settings->get('api.duitku_productDetails'); //settings.duitku_productDetails
                  $email = $pasien['email']; // your customer email
                  $phoneNumber = $pasien['no_tlp']; // your customer phone number (optional)
                  $additionalParam = ''; // optional
                  $merchantUserInfo = ''; // optional
                  $customerVaName = $pasien['nm_pasien']; // display name on bank confirmation display
                  $callbackUrl = url().'/api/apam/?action=duitku_callback&token='.$token; // url for callback
                  $returnUrl = url().'/api/apam/?action=duitku&token='.$token; // url for redirect
                  $expiryPeriod = $this->settings->get('api.duitku_expiryPeriod'); // set the expired time in minutes

                  $signature = md5($merchantCode . $merchantOrderId . $paymentAmount . $merchantKey);

                  $item1 = array(
                      'name' => $this->settings->get('api.duitku_productDetails'), //settings.duitku_productDetails
                      'price' => $this->settings->get('api.duitku_paymentAmount'), //settings.duitku_paymentAmount
                      'quantity' => 1);
                  $itemDetails = array(
                      $item1
                  );

                  $params = array(
                      'merchantCode' => $merchantCode,
                      'paymentAmount' => $paymentAmount,
                      'paymentMethod' => $paymentMethod,
                      'merchantOrderId' => $merchantOrderId,
                      'productDetails' => $productDetails,
                      'additionalParam' => $additionalParam,
                      'merchantUserInfo' => $merchantUserInfo,
              	      'customerVaName' => $customerVaName,
                      'email' => $email,
                      'phoneNumber' => $phoneNumber,
                      'itemDetails' => $itemDetails,
                      'callbackUrl' => $callbackUrl,
                      'returnUrl' => $returnUrl,
                      'signature' => $signature,
              	      'expiryPeriod' => $expiryPeriod
                  );

                  $params_string = json_encode($params);
                  $url = 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry'; // Sandbox
                  // $url = 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry'; // Production
                  $ch = curl_init();

                  curl_setopt($ch, CURLOPT_URL, $url);
                  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                  curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                      'Content-Type: application/json',
                      'Content-Length: ' . strlen($params_string))
                  );
                  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

                  //execute post
                  $request = curl_exec($ch);
                  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                  if($httpCode == 200) {
                    $result_duitku = json_decode($request, true);
                    $this->db('mlite_duitku')->save([
                      'tanggal' => $waktu_kunjungan,
                      'no_rkm_medis' => $pasien['no_rkm_medis'],
                      'paymentUrl' => $result_duitku['paymentUrl'],
                      'merchantCode' => $result_duitku['merchantCode'],
                      'reference' => $result_duitku['reference'],
                      'vaNumber' => $result_duitku['vaNumber'],
                      'amount' => $result_duitku['amount'],
                      'statusCode' => $result_duitku['statusCode'],
                      'statusMessage' => $result_duitku['statusMessage']
                    ]);
                  }

                  $get_pasien = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
                  $get_poliklinik = $this->db('poliklinik')->where('kd_poli', $kd_poli)->oneArray();
                  if($get_pasien['no_tlp'] !='') {
                    $ch = curl_init();
                    $url = "https://wa.basoro.id/api/send-message.php";
                    curl_setopt($ch, CURLOPT_URL,$url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, "api_key=".$this->settings->get('settings.waapitoken')."&sender=".$this->settings->get('settings.waapiphonenumber')."&number=".$get_pasien['no_tlp']."&message=Terima kasih sudah melakukan pendaftaran Online Telemedicine di ".$this->settings->get('settings.nama_instansi').". \n\nDetail pendaftaran Telemedicine anda adalah, \nTanggal: ".date('Y-m-d', strtotime($waktu_kunjungan))." \nNomor Antrian: ".$no_reg." \nPoliklinik: ".$get_poliklinik['nm_poli']." \nStatus: Menunggu \n\nSilahkan lakukan pembayaran dengan mengklik link berikut ".$result_duitku['paymentUrl'].".\n\n-------------------\nPesan WhatsApp ini dikirim otomatis oleh ".$this->settings->get('settings.nama_instansi')." \nTerima Kasih"); // Define what you want to post
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $output = curl_exec ($ch);
                    curl_close ($ch);
                  }

              }
              else{
                  $send_data['state'] = 'duplication';
                  echo json_encode($send_data);
              }
            break;
            case "telemedicinesukses":
              $results = array();
              $no_rkm_medis = trim($_REQUEST['no_rkm_medis']);
              $date = date('Y-m-d');
              $sql = "SELECT a.tanggal_booking, a.tanggal_periksa, a.no_reg, a.status, b.nm_poli, c.nm_dokter, d.png_jawab, a.jam_booking FROM booking_registrasi a LEFT JOIN poliklinik b ON a.kd_poli = b.kd_poli LEFT JOIN dokter c ON a.kd_dokter = c.kd_dokter LEFT JOIN penjab d ON a.kd_pj = d.kd_pj WHERE a.no_rkm_medis = '$no_rkm_medis' AND a.tanggal_booking = '$date' AND a.jam_booking = (SELECT MAX(ax.jam_booking) FROM booking_registrasi ax WHERE ax.tanggal_booking = a.tanggal_booking) ORDER BY a.tanggal_booking ASC LIMIT 1";
              $query = $this->db()->pdo()->prepare($sql);
              $query->execute();
              $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
              foreach ($rows as $row) {
                $mlite_duitku = $this->db('mlite_duitku')->where('no_rkm_medis', $no_rkm_medis)->where('tanggal', $row['tanggal_booking'].' '.$row['jam_booking'])->oneArray();
                $row['paymentUrl'] = $mlite_duitku['paymentUrl'];
                $results[] = $row;
              }
              echo json_encode($results, JSON_PRETTY_PRINT);
            break;
            default:
              echo 'Default';
            break;
          }
        } else {
        	echo 'Error';
        }
        exit();
    }

    public function hitungUmur($tanggal_lahir)
    {
      	$birthDate = new \DateTime($tanggal_lahir);
      	$today = new \DateTime("today");
      	$umur = "0 Th 0 Bl 0 Hr";
        if ($birthDate < $today) {
        	$y = $today->diff($birthDate)->y;
        	$m = $today->diff($birthDate)->m;
        	$d = $today->diff($birthDate)->d;
          $umur =  $y." Th ".$m." Bl ".$d." Hr";
        }
      	return $umur;
    }

    private function sendRegisterEmail($email, $receiver, $number)
    {
	    $mail = new PHPMailer(true);
      $temp  = @file_get_contents(MODULES."/api/email/apam.welcome.html");

      $temp  = str_replace("{SITENAME}", $this->core->settings->get('settings.nama_instansi'), $temp);
      $temp  = str_replace("{ADDRESS}", $this->core->settings->get('settings.alamat')." - ".$this->core->settings->get('settings.kota'), $temp);
      $temp  = str_replace("{TELP}", $this->core->settings->get('settings.nomor_telepon'), $temp);
      $temp  = str_replace("{NUMBER}", $number, $temp);

	    //$mail->SMTPDebug = SMTP::DEBUG_SERVER; // for detailed debug output
      $mail->isSMTP();
      $mail->Host = $this->settings->get('api.apam_smtp_host');
      $mail->SMTPAuth = true;
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = $this->settings->get('api.apam_smtp_port');

      $mail->Username = $this->settings->get('api.apam_smtp_username');
      $mail->Password = $this->settings->get('api.apam_smtp_password');

      // Sender and recipient settings
      $mail->setFrom($this->core->settings->get('settings.email'), $this->core->settings->get('settings.nama_instansi'));
      $mail->addAddress($email, $receiver);

      // Setting the email content
      $mail->IsHTML(true);
      $mail->Subject = "Verifikasi pendaftaran anda di ".$this->core->settings->get('settings.nama_instansi');
      $mail->Body = $temp;

      $mail->send();
    }

}
