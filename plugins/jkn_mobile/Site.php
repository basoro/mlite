<?php

namespace Plugins\JKN_Mobile;

use Systems\SiteModule;
use Systems\Lib\BpjsService;

class Site extends SiteModule
{

    protected $consid;
    protected $secretkey;
    protected $bpjsurl;
    protected $user_key;

    public function init()
    {
        $this->consid = $this->settings->get('jkn_mobile.BpjsConsID');
        $this->secretkey = $this->settings->get('jkn_mobile.BpjsSecretKey');
        $this->bpjsurl = $this->settings->get('jkn_mobile.BpjsAntrianUrl');
        $this->user_key = $this->settings->get('jkn_mobile.BpjsUserKey');
    }

    public function routes()
    {

        $this->route('jknmobile', 'getIndex');
        $this->route('jknmobile/token', 'getToken');
        $this->route('jknmobile/antrian/ambil', 'getAmbilAntrian');
        $this->route('jknmobile/antrian/status', 'getStatusAntrian');
        $this->route('jknmobile/antrian/ambilfarmasi', 'getAmbilAntrianFarmasi');
        $this->route('jknmobile/antrian/statusfarmasi', 'getStatusAntrianFarmasi');
        $this->route('jknmobile/antrian/sisa', 'getSisaAntrian');
        $this->route('jknmobile/antrian/batal', 'getBatalAntrian');
        $this->route('jknmobile/pasien/baru', 'getPasienBaru');
        $this->route('jknmobile/pasien/checkin', 'getPasienCheckIn');
        $this->route('jknmobile/operasi/rs', 'getOperasiRS');
        $this->route('jknmobile/operasi/pasien', 'getOperasiPasien');
        $this->route('jknmobile/antrian/add', '_getAntreanAdd');
        $this->route('jknmobile/antrian/add/(:str)', '_getAntreanAdd');
        $this->route('jknmobile/antrian/updatewaktu', '_getAntreanUpdateWaktu');
        $this->route('jknmobile/antrian/updatewaktu/(:str)', '_getAntreanUpdateWaktu');
        $this->route('jknmobile/antrian/waktutunggu/(:str)/(:str)/(:str)', '_getAntreanWaktuTunggu');
        $this->route('jknmobile/antrian/tanggaltunggu/(:str)/(:str)', '_getAntreanWaktuTungguTanggal');
        $this->route('jknmobile/antrian/listtask/(:str)', '_getAntreanGetListTask');
        $this->route('jknmobile/jadwal/(:str)/(:str)', '_getJadwal');

        $this->route('jknmobile/aplicare', 'getAplicareManajemen');
        $this->route('jknmobile/aplicare/(:str)', 'getAplicare');
        $this->route('jknmobile/aplicare/(:str)/(:str)', 'getAplicare');

        /* Start Old school routing */
        $this->route('jknmobile_v2', 'getIndex');
        $this->route('jknmobile_v2/token', 'getToken');
        $this->route('jknmobile_v2/antrian/ambil', 'getAmbilAntrian');
        $this->route('jknmobile_v2/antrian/status', 'getStatusAntrian');
        $this->route('jknmobile_v2/antrian/ambilfarmasi', 'getAmbilAntrianFarmasi');
        $this->route('jknmobile_v2/antrian/statusfarmasi', 'getStatusAntrianFarmasi');
        $this->route('jknmobile_v2/antrian/sisa', 'getSisaAntrian');
        $this->route('jknmobile_v2/antrian/batal', 'getBatalAntrian');
        $this->route('jknmobile_v2/pasien/baru', 'getPasienBaru');
        $this->route('jknmobile_v2/pasien/checkin', 'getPasienCheckIn');
        $this->route('jknmobile_v2/operasi/rs', 'getOperasiRS');
        $this->route('jknmobile_v2/operasi/pasien', 'getOperasiPasien');
        $this->route('jknmobile_v2/antrian/add', '_getAntreanAdd');
        $this->route('jknmobile_v2/antrian/add/(:str)', '_getAntreanAdd');
        $this->route('jknmobile_v2/antrian/updatewaktu', '_getAntreanUpdateWaktu');
        $this->route('jknmobile_v2/antrian/updatewaktu/(:str)', '_getAntreanUpdateWaktu');
        $this->route('jknmobile_v2/antrian/waktutunggu/(:str)/(:str)/(:str)', '_getAntreanWaktuTunggu');
        $this->route('jknmobile_v2/antrian/tanggaltunggu/(:str)/(:str)', '_getAntreanWaktuTungguTanggal');
        $this->route('jknmobile_v2/antrian/listtask/(:str)', '_getAntreanGetListTask');
        $this->route('jknmobile_v2/jadwal/(:str)/(:str)', '_getJadwal');

        $this->route('jknmobile_v2/aplicare/(:str)', 'getAplicare');
        $this->route('jknmobile_v2/aplicare/(:str)/(:str)', 'getAplicare');
        /* End Old school routing */

    }

    public function getIndex()
    {
        $referensi_poli = $this->db('maping_poli_bpjs')->toArray();
        $kelas = [];
        $sql = "SELECT kamar.kelas, kamar.kd_bangsal FROM kamar JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal WHERE kamar.statusdata = '1'";
        // $sql .= " AND kamar.kelas = 'Kelas $kelas' AND kamar.kd_bangsal LIKE '%$bangsal%'";
        $sql .= " GROUP BY kamar.kd_bangsal ";
        $query = $this->db()->pdo()->prepare($sql);
        $query->execute();
        $bedlist = $query->fetchAll();
        foreach ($bedlist as $value) {
          $kelas[] = $value;
        }

        echo $this->draw('index.html', ['referensi_poli' => $referensi_poli, 'kelas' => $kelas]);
        exit();
    }

    public function getToken()
    {
        echo $this->_resultToken();
        exit();
    }

    private function _resultToken()
    {
        header("Content-Type: application/json");
        $konten = trim(file_get_contents("php://input"));
        $header = apache_request_headers();
        $response = array();
        if ($header[$this->settings->get('jkn_mobile.header_username')] == $this->settings->get('jkn_mobile.x_username') && $header[$this->settings->get('jkn_mobile.header_password')] == $this->settings->get('jkn_mobile.x_password')) {
            $response = array(
                'response' => array(
                    'token' => $this->_getToken()
                ),
                'metadata' => array(
                    'message' => 'Ok',
                    'code' => 200
                )
            );
        } else {
            $response = array(
                'metadata' => array(
                    'message' => 'Access denied',
                    'code' => 201
                )
            );
        }
        echo json_encode($response);
    }

    public function getAmbilAntrian()
    {
        echo $this->_resultAmbilAntrian();
        exit();
    }

    private function _resultAmbilAntrian()
    {
        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
        $key = $this->consid.$this->secretkey.$tStamp;

        date_default_timezone_set($this->settings->get('settings.timezone'));

        header("Content-Type: application/json");
        $header = apache_request_headers();
        $konten = trim(file_get_contents("php://input"));
        $decode = json_decode($konten, true);
        $response = array();
        if($header[$this->settings->get('jkn_mobile.header_token')] == false) {
            $response = array(
                'metadata' => array(
                    'message' => 'Token expired',
                    'code' => 201
                )
            );
            http_response_code(201);
        } else if ($header[$this->settings->get('jkn_mobile.header_token')] == $this->_getToken() && $header[$this->settings->get('jkn_mobile.header_username')] == $this->settings->get('jkn_mobile.x_username')) {

            $tanggal=$decode['tanggalperiksa'];
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
            $cek_rujukan = $this->db('bridging_sep')->where('no_rujukan', $decode['nomorreferensi'])->group('tglrujukan')->oneArray();

            $h1 = date('Y-m-d');
            $_h1 = date('d-m-Y', strtotime($h1));
            $h7 = strtotime('+8 days', strtotime(date('Y-m-d'))) ;
            if(!empty($cek_rujukan['tglrujukan'])) {
              $h7 = strtotime('+90 days', strtotime($cek_rujukan['tglrujukan']));
            }
            $h7 = date('Y-m-d', $h7);
            $_h7 = date('d-m-Y', strtotime($h7));

            $data_pasien = $this->db('pasien')->where('no_peserta', $decode['nomorkartu'])->oneArray();
            $poli = $this->db('maping_poli_bpjs')->where('kd_poli_bpjs', $decode['kodepoli'])->oneArray();
            $dokter = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter_bpjs', $decode['kodedokter'])->oneArray();

            if(strtotime($decode['tanggalperiksa']) == strtotime(date('Y-m-d'))) {
              $cek_kuota = $this->db()->pdo()->prepare("SELECT jadwal.kuota - (SELECT COUNT(reg_periksa.tgl_registrasi)
              FROM reg_periksa WHERE reg_periksa.tgl_registrasi='$decode[tanggalperiksa]'
              AND reg_periksa.kd_dokter=jadwal.kd_dokter) as sisa_kuota, jadwal.kd_dokter, jadwal.kd_poli, jadwal.jam_mulai as jam_mulai, poliklinik.nm_poli, dokter.nm_dokter, jadwal.kuota
              FROM jadwal INNER JOIN maping_poli_bpjs ON maping_poli_bpjs.kd_poli_rs=jadwal.kd_poli INNER JOIN poliklinik ON poliklinik.kd_poli=jadwal.kd_poli INNER JOIN dokter ON dokter.kd_dokter=jadwal.kd_dokter
              WHERE jadwal.hari_kerja='$hari' AND maping_poli_bpjs.kd_poli_bpjs='$decode[kodepoli]' GROUP BY jadwal.kd_dokter HAVING sisa_kuota > 0 ORDER BY sisa_kuota DESC LIMIT 1");
            } else {
              $cek_kuota = $this->db()->pdo()->prepare("SELECT jadwal.kuota - (SELECT COUNT(booking_registrasi.tanggal_periksa)
              FROM booking_registrasi WHERE booking_registrasi.tanggal_periksa='$decode[tanggalperiksa]'
              AND booking_registrasi.kd_dokter=jadwal.kd_dokter) as sisa_kuota, jadwal.kd_dokter, jadwal.kd_poli, jadwal.jam_mulai as jam_mulai, poliklinik.nm_poli, dokter.nm_dokter, jadwal.kuota
              FROM jadwal INNER JOIN maping_poli_bpjs ON maping_poli_bpjs.kd_poli_rs=jadwal.kd_poli INNER JOIN poliklinik ON poliklinik.kd_poli=jadwal.kd_poli INNER JOIN dokter ON dokter.kd_dokter=jadwal.kd_dokter
              WHERE jadwal.hari_kerja='$hari' AND maping_poli_bpjs.kd_poli_bpjs='$decode[kodepoli]' GROUP BY jadwal.kd_dokter HAVING sisa_kuota > 0 ORDER BY sisa_kuota DESC LIMIT 1");
            }

            $cek_kuota->execute();
            $cek_kuota = $cek_kuota->fetch();
            $jadwal = $this->db('jadwal')
                ->join('maping_dokter_dpjpvclaim', 'maping_dokter_dpjpvclaim.kd_dokter=jadwal.kd_dokter')
                ->where('maping_dokter_dpjpvclaim.kd_dokter_bpjs', $decode['kodedokter'])
                ->where('hari_kerja', $hari)
                ->where('jam_mulai', strtok($decode['jampraktek'], '-').':00')
                ->where('jam_selesai', substr($decode['jampraktek'], strpos($decode['jampraktek'], "-") + 1).':00')
                ->oneArray();

            $cek_referensi = $this->db('mlite_antrian_referensi')->where('nomor_referensi', $decode['nomorreferensi'])->where('tanggal_periksa', $decode['tanggalperiksa'])->oneArray();
            $cek_referensi_noka = $this->db('mlite_antrian_referensi')->where('nomor_kartu', $decode['nomorkartu'])->where('tanggal_periksa', $decode['tanggalperiksa'])->oneArray();

            if(!empty($cek_referensi['tanggal_periksa'])) {
               $errors[] = 'Anda sudah terdaftar dalam antrian menggunakan nomor rujukan yang sama ditanggal '.$decode['tanggalperiksa'];
            }
            if(!empty($cek_referensi_noka['tanggal_periksa'])) {
               $errors[] = 'Anda sudah terdaftar dalam antrian ditanggal '.$cek_referensi_noka['tanggal_periksa'].'. Silahkan pilih tanggal lain.';
            }
            if(empty($decode['nomorkartu'])) {
               $errors[] = 'Nomor kartu tidak boleh kosong';
            }
            if (!empty($decode['nomorkartu']) && mb_strlen($decode['nomorkartu'], 'UTF-8') < 13){
               $errors[] = 'Nomor kartu harus 13 digit';
            }
            if (!empty($decode['nomorkartu']) && !ctype_digit($decode['nomorkartu']) ){
               $errors[] = 'Nomor kartu harus mengandung angka saja!!';
            }
            if(empty($decode['nik'])) {
               $errors[] = 'Nomor kartu tidak boleh kosong';
            }
            if(!empty($decode['nik']) && mb_strlen($decode['nik'], 'UTF-8') < 16){
               $errors[] = 'Nomor KTP harus 16 digiti atau format tidak sesuai';
            }
            if (!empty($decode['nik']) && !ctype_digit($decode['nik']) ){
               $errors[] = 'Nomor kartu harus mengandung angka saja!!';
            }
            if(empty($decode['tanggalperiksa'])) {
               $errors[] = 'Anda belum memilih tanggal periksa';
            }
            if(!empty($decode['tanggalperiksa']) && $decode['tanggalperiksa'] < $h1 || $decode['tanggalperiksa'] > $h7) {
               $errors[] = 'Tanggal periksa bisa dilakukan tanggal '.$_h1.' hingga tanggal '.$_h7;
            }
            if (!empty($decode['tanggalperiksa']) && !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$decode['tanggalperiksa'])) {
               $errors[] = 'Format tanggal periksa tidak sesuai';
            }
            if(!empty($decode['tanggalperiksa']) && $decode['tanggalperiksa'] == $cek_referensi['tanggal_periksa']) {
               $errors[] = 'Anda sudah terdaftar dalam antrian ditanggal '.$decode['tanggalperiksa'];
            }
            if(empty($decode['kodepoli'])) {
               $errors[] = 'Kode poli tidak boleh kosong';
            }
            if(!empty($decode['kodepoli']) && $poli == 0) {
               $errors[] = 'Kode poli tidak ditemukan';
            }
            if(empty($decode['kodedokter'])) {
               $errors[] = 'Kode dokter tidak boleh kosong';
            }
            if(!empty($decode['kodedokter']) && $dokter == 0) {
               $errors[] = 'Kode dokter tidak ditemukan';
            }
            if(!empty($decode['jeniskunjungan']) && $decode['jeniskunjungan'] < 1 || $decode['jeniskunjungan'] > 4) {
               $errors[] = 'Jenis kunjungan tidak ditemukan';
            }

            if(!empty($errors)) {
                foreach($errors as $error) {
                    $response = array(
                        'metadata' => array(
                            'message' => $this->_getErrors($error),
                            'code' => 201
                        )
                    );
                };
                http_response_code(201);
            } else {

                if (isset($cek_kuota['sisa_kuota']) > 0) {
                    if(!$data_pasien) {
                        $response = array(
                            'metadata' => array(
                                'message' =>  'Data pasien ini tidak ditemukan',
                                'code' => 202
                            )
                        );
                        http_response_code(202);
                    } else {
                        // Get antrian poli
                        $no_reg = $this->core->setNoBooking($dokter['kd_dokter'], $decode['tanggalperiksa'], $poli['kd_poli_rs']);
                        $no_urut_reg = substr($no_reg, 0, 3);
                        $minutes = $no_urut_reg * 10;
                        $cek_kuota['jam_mulai'] = date('H:i:s',strtotime('+'.$minutes.' minutes',strtotime($cek_kuota['jam_mulai'])));
                        $keterangan = 'Peserta harap datang 30 menit lebih awal.';

                        if(strtotime($decode['tanggalperiksa']) == strtotime(date('Y-m-d'))) {

                          $no_reg = $this->core->setNoReg($jadwal['kd_dokter'], $jadwal['kd_poli']);

                          $cek_stts_daftar = $this->db('reg_periksa')->where('no_rkm_medis', $data_pasien['no_rkm_medis'])->count();
                          $_POST['stts_daftar'] = 'Baru';
                          if($cek_stts_daftar > 0) {
                            $_POST['stts_daftar'] = 'Lama';
                          }

                          $biaya_reg = $this->db('poliklinik')->where('kd_poli', $jadwal['kd_poli'])->oneArray();
                          $_POST['biaya_reg'] = $biaya_reg['registrasi'];
                          if($_POST['stts_daftar'] == 'Lama') {
                            $_POST['biaya_reg'] = $biaya_reg['registrasilama'];
                          }

                          $cek_status_poli = $this->db('reg_periksa')->where('no_rkm_medis', $data_pasien['no_rkm_medis'])->where('kd_poli', $jadwal['kd_poli'])->count();
                          $_POST['status_poli'] = 'Baru';
                          if($cek_status_poli > 0) {
                            $_POST['status_poli'] = 'Lama';
                          }

                          // set umur
                          $tanggal = new \DateTime($this->core->getPasienInfo('tgl_lahir', $data_pasien['no_rkm_medis']));
                          $today = new \DateTime(date('Y-m-d'));
                          $y = $today->diff($tanggal)->y;
                          $m = $today->diff($tanggal)->m;
                          $d = $today->diff($tanggal)->d;

                          $umur="0";
                          $sttsumur="Th";
                          if($y>0){
                              $umur=$y;
                              $sttsumur="Th";
                          }else if($y==0){
                              if($m>0){
                                  $umur=$m;
                                  $sttsumur="Bl";
                              }else if($m==0){
                                  $umur=$d;
                                  $sttsumur="Hr";
                              }
                          }

                          $insert = $this->db('reg_periksa')
                          ->save([
                            'no_reg' => $no_reg,
                            'no_rawat' => $this->core->setNoRawat(date('Y-m-d')),
                            'tgl_registrasi' => date('Y-m-d'),
                            'jam_reg' => date('H:i:s'),
                            'kd_dokter' => $jadwal['kd_dokter'],
                            'no_rkm_medis' => $data_pasien['no_rkm_medis'],
                            'kd_poli' => $jadwal['kd_poli'],
                            'p_jawab' => $this->core->getPasienInfo('namakeluarga', $data_pasien['no_rkm_medis']),
                            'almt_pj' => $this->core->getPasienInfo('alamatpj', $data_pasien['no_rkm_medis']),
                            'hubunganpj' => $this->core->getPasienInfo('keluarga', $data_pasien['no_rkm_medis']),
                            'biaya_reg' => $_POST['biaya_reg'],
                            'stts' => 'Belum',
                            'stts_daftar' => $_POST['stts_daftar'],
                            'status_lanjut' => 'Ralan',
                            'kd_pj' => $this->settings->get('jkn_mobile.kd_pj_bpjs'),
                            'umurdaftar' => $umur,
                            'sttsumur' => $sttsumur,
                            'status_bayar' => 'Belum Bayar',
                            'status_poli' => $_POST['status_poli']
                          ]);
                          if($insert) {
                            $query = $this->db('booking_registrasi')->save([
                                'tanggal_booking' => date('Y-m-d'),
                                'jam_booking' => date('H:i:s'),
                                'no_rkm_medis' => $data_pasien['no_rkm_medis'],
                                'tanggal_periksa' => $decode['tanggalperiksa'],
                                'kd_dokter' => $jadwal['kd_dokter'],
                                'kd_poli' => $jadwal['kd_poli'],
                                'no_reg' => $no_reg,
                                'kd_pj' => $this->settings->get('jkn_mobile.kd_pj_bpjs'),
                                'limit_reg' => 1,
                                'waktu_kunjungan' => $decode['tanggalperiksa'].' '.$cek_kuota['jam_mulai'],
                                'status' => 'Terdaftar'
                            ]);
                          }
                        } else {
                          $query = $this->db('booking_registrasi')->save([
                              'tanggal_booking' => date('Y-m-d'),
                              'jam_booking' => date('H:i:s'),
                              'no_rkm_medis' => $data_pasien['no_rkm_medis'],
                              'tanggal_periksa' => $decode['tanggalperiksa'],
                              'kd_dokter' => $jadwal['kd_dokter'],
                              'kd_poli' => $jadwal['kd_poli'],
                              'no_reg' => $no_reg,
                              'kd_pj' => $this->settings->get('jkn_mobile.kd_pj_bpjs'),
                              'limit_reg' => 1,
                              'waktu_kunjungan' => $decode['tanggalperiksa'].' '.$cek_kuota['jam_mulai'],
                              'status' => 'Belum'
                          ]);
                        }
                        if ($query) {
                            $kodebooking = $this->settings->get('settings.ppk_bpjs').''.date('Ymdhis').''.$decode['kodepoli'].''.$no_reg.'MJKN';
                            $response = array(
                                'response' => array(
                                    'nomorantrean' => $decode['kodepoli'].'-'.$no_reg,
                                    'angkaantrean' => $no_reg,
                                    'kodebooking' => $kodebooking,
                                    'pasienbaru'=>0,
                                    'norm' => $data_pasien['no_rkm_medis'],
                                    'namapoli' => $cek_kuota['nm_poli'],
                                    'namadokter' => $jadwal['nm_dokter_bpjs'],
                                    'estimasidilayani' => strtotime($decode['tanggalperiksa'].' '.$cek_kuota['jam_mulai']) * 1000,
                                    'sisakuotajkn' => ($cek_kuota['sisa_kuota']-1),
                                    'kuotajkn' => intval($cek_kuota['kuota']),
                                    'sisakuotanonjkn' => ($cek_kuota['sisa_kuota']-1),
                                    'kuotanonjkn' => intval($cek_kuota['kuota']),
                                    'keterangan' => $keterangan
                                ),
                                'metadata' => array(
                                    'message' => 'Ok',
                                    'code' => 200
                                )
                            );
                            http_response_code(200);

                            if(!empty($decode['nomorreferensi'])) {
                              $this->db('mlite_antrian_referensi')->save([
                                  'tanggal_periksa' => $decode['tanggalperiksa'],
                                  'no_rkm_medis' => $data_pasien['no_rkm_medis'],
                                  'nomor_kartu' => $decode['nomorkartu'],
                                  'nomor_referensi' => $decode['nomorreferensi'],
                                  'kodebooking' => $kodebooking,
                                  'jenis_kunjungan' => $decode['jeniskunjungan'],
                                  'status_kirim' => 'Belum',
                                  'keterangan' => ''
                              ]);
                            }
                        } else {
                            $response = array(
                                'metadata' => array(
                                    'message' => "Maaf Terjadi Kesalahan, Hubungi layanan pelanggang Rumah Sakit..",
                                    'code' => 201
                                )
                            );
                        }
                    }
                } else {
                    $response = array(
                        'metadata' => array(
                            'message' => "Jadwal tidak tersedia atau kuota penuh! Silahkan pilih tanggal lain!",
                            'code' => 201
                        )
                    );
                }
            }
        } else {
            $response = array(
                'metadata' => array(
                    'message' => 'Access denied',
                    'code' => 201
                )
            );
            http_response_code(201);
        }
        echo json_encode($response);
    }

    public function getStatusAntrian()
    {
        echo $this->_resultStatusAntrian();
        exit();
    }

    private function _resultStatusAntrian()
    {
        header("Content-Type: application/json");
        $header = apache_request_headers();
        $konten = trim(file_get_contents("php://input"));
        $decode = json_decode($konten, true);
        $response = array();

        $tentukan_hari=date('D',strtotime($decode['tanggalperiksa']));
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

        $kdpoli = $this->db('maping_poli_bpjs')->where('kd_poli_bpjs', $decode['kodepoli'])->oneArray();
        $kddokter = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter_bpjs', $decode['kodedokter'])->oneArray();
        if($header[$this->settings->get('jkn_mobile.header_token')] == false) {
            $response = array(
                'metadata' => array(
                    'message' => 'Token expired',
                    'code' => 201
                )
            );
            http_response_code(201);
        } else if ($header[$this->settings->get('jkn_mobile.header_token')] == $this->_getToken() && $header[$this->settings->get('jkn_mobile.header_username')] == $this->settings->get('jkn_mobile.x_username')) {
            if(!$this->db('maping_poli_bpjs')->where('kd_poli_bpjs', $decode['kodepoli'])->oneArray()){
                $response = array(
                    'metadata' => array(
                        'message' => 'Poli Tidak Ditemukan',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(!$this->db('maping_dokter_dpjpvclaim')->where('kd_dokter_bpjs', $decode['kodedokter'])->oneArray()){
                $response = array(
                    'metadata' => array(
                        'message' => 'Dokter Tidak Ditemukan',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$decode['tanggalperiksa'])){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Tanggal Tidak Sesuai, format yang benar adalah yyyy-mm-dd',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(!$this->db('jadwal')
                ->join('maping_dokter_dpjpvclaim', 'maping_dokter_dpjpvclaim.kd_dokter=jadwal.kd_dokter')
                ->where('maping_dokter_dpjpvclaim.kd_dokter_bpjs', $decode['kodedokter'])
                ->where('hari_kerja', $hari)
                ->where('jam_mulai', strtok($decode['jampraktek'], '-').':00')
                ->where('jam_selesai', substr($decode['jampraktek'], strpos($decode['jampraktek'], "-") + 1).':00')
                ->oneArray()){
                $response = array(
                    'metadata' => array(
                        'message' => 'Jadwal Praktek Tidak Ditemukan Pada Hari '.$hari.' Tanggal '.$decode['tanggalperiksa'],
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strtotime(date("Y-m-d")) > strtotime($decode['tanggalperiksa'])){
                $response = array(
                    'metadata' => array(
                        'message' => 'Tanggal Periksa Tidak Berlaku Mundur',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else{
                $kuota = $this->db()->pdo()->prepare("SELECT jadwal.kuota - (SELECT COUNT(booking_registrasi.tanggal_periksa) FROM booking_registrasi WHERE booking_registrasi.tanggal_periksa='$decode[tanggalperiksa]' AND booking_registrasi.kd_dokter=jadwal.kd_dokter) as sisa_kuota, jadwal.kd_dokter, jadwal.kd_poli, jadwal.jam_mulai as jam_mulai, poliklinik.nm_poli, dokter.nm_dokter, jadwal.kuota FROM jadwal INNER JOIN maping_poli_bpjs ON maping_poli_bpjs.kd_poli_rs=jadwal.kd_poli INNER JOIN poliklinik ON poliklinik.kd_poli=jadwal.kd_poli INNER JOIN dokter ON dokter.kd_dokter=jadwal.kd_dokter WHERE jadwal.hari_kerja='$hari' AND maping_poli_bpjs.kd_poli_bpjs='$decode[kodepoli]' GROUP BY jadwal.kd_dokter HAVING sisa_kuota > 0 ORDER BY sisa_kuota DESC LIMIT 1");
                $kuota->execute();
                $kuota = $kuota->fetch();

                $max_antrian = $this->db()->pdo()->prepare("SELECT booking_registrasi.no_reg FROM booking_registrasi WHERE booking_registrasi.status='Belum' AND booking_registrasi.kd_dokter='$kddokter[kd_dokter]' AND booking_registrasi.kd_poli='$kdpoli[kd_poli_rs]' AND booking_registrasi.tanggal_periksa='$decode[tanggalperiksa]' ORDER BY CONVERT(RIGHT(booking_registrasi.no_reg,3),signed) LIMIT 1 ");

                if($decode['tanggalperiksa'] == date('Y-m-d')) {
                  $max_antrian = $this->db()->pdo()->prepare("SELECT reg_periksa.no_reg FROM reg_periksa WHERE reg_periksa.stts='Belum' AND reg_periksa.kd_dokter='$kddokter[kd_dokter]' AND reg_periksa.kd_poli='$kdpoli[kd_poli_rs]' AND reg_periksa.tgl_registrasi='$decode[tanggalperiksa]' ORDER BY CONVERT(RIGHT(reg_periksa.no_reg,3),signed) LIMIT 1 ");
                }
                $max_antrian->execute();
                $max_antrian = $max_antrian->fetch();

                if(strtotime($decode['tanggalperiksa']) == strtotime(date('Y-m-d'))) {
                  $data = $this->db()->pdo()->prepare("SELECT poliklinik.nm_poli,COUNT(reg_periksa.kd_poli) as total_antrean,dokter.nm_dokter,
                      IFNULL(SUM(CASE WHEN reg_periksa.stts ='Belum' THEN 1 ELSE 0 END),0) as sisa_antrean,
                      ('Datanglah Minimal 30 Menit, jika no antrian anda terlewat, silakan konfirmasi ke layanan pelanggan, Terima Kasih ..') as keterangan
                      FROM reg_periksa INNER JOIN poliklinik ON poliklinik.kd_poli=reg_periksa.kd_poli INNER JOIN dokter ON reg_periksa.kd_dokter=dokter.kd_dokter
                      WHERE reg_periksa.tgl_registrasi='$decode[tanggalperiksa]' AND reg_periksa.kd_poli='$kdpoli[kd_poli_rs]' and reg_periksa.kd_dokter='$kddokter[kd_dokter]'");
                } else {
                  $data = $this->db()->pdo()->prepare("SELECT poliklinik.nm_poli,COUNT(booking_registrasi.kd_poli) as total_antrean,dokter.nm_dokter,
                      IFNULL(SUM(CASE WHEN booking_registrasi.status ='Belum' THEN 1 ELSE 0 END),0) as sisa_antrean,
                      ('Datanglah Minimal 30 Menit, jika no antrian anda terlewat, silakan konfirmasi ke bagian layanan pelanggan, Terima Kasih ..') as keterangan
                      FROM booking_registrasi INNER JOIN poliklinik ON poliklinik.kd_poli=booking_registrasi.kd_poli INNER JOIN dokter ON booking_registrasi.kd_dokter=dokter.kd_dokter
                      WHERE booking_registrasi.tanggal_periksa='$decode[tanggalperiksa]' AND booking_registrasi.kd_poli='$kdpoli[kd_poli_rs]' and booking_registrasi.kd_dokter='$kddokter[kd_dokter]'");
                }
                $data->execute();
                $data = $data->fetch();

                if ($data['sisa_antrean'] >0) {
                    $response = array(
                        'response' => array(
                            'namapoli' => $data['nm_poli'],
                            'namadokter' => $data['nm_dokter'],
                            'totalantrean' => $data['total_antrean'],
                            'sisaantrean' => ($data['sisa_antrean']>0?$data['sisa_antrean']:0),
                            'antreanpanggil' => "A-".$max_antrian['no_reg'],
                            'sisakuotajkn' => ($kuota['kuota']-$data['total_antrean']),
                            'kuotajkn' => intval($kuota['kuota']),
                            'sisakuotanonjkn' => ($kuota['kuota']-$data['total_antrean']),
                            'kuotanonjkn' => intval($kuota['kuota']),
                            'keterangan' => $data['keterangan']
                        ),
                        'metadata' => array(
                            'message' => 'Ok',
                            'code' => 200
                        )
                    );
                    http_response_code(200);
                } else {
                    $response = array(
                        'metadata' => array(
                            'message' => 'Maaf belum ada antrian ditanggal ' . $decode['tanggalperiksa'],
                             'code' => 201
                        )
                    );
                    http_response_code(201);
                }
            }
        } else {
            $response = array(
                'metadata' => array(
                    'message' => 'Access denied',
                    'code' => 201
                )
            );
        }
        echo json_encode($response);
    }

    public function getAmbilAntrianFarmasi()
    {
        echo $this->_resultAmbilAntrianFarmasi();
        exit();
    }

    private function _resultAmbilAntrianFarmasi()
    {
        header("Content-Type: application/json");
        $header = apache_request_headers();
        $konten = trim(file_get_contents("php://input"));
        $decode = json_decode($konten, true);
        $response = array();
        if($header[$this->settings->get('jkn_mobile.header_token')] == false) {
            $response = array(
                'metadata' => array(
                    'message' => 'Token expired',
                    'code' => 201
                )
            );
            http_response_code(201);
        } else if ($header[$this->settings->get('jkn_mobile.header_token')] == $this->_getToken() && $header[$this->settings->get('jkn_mobile.header_username')] == $this->settings->get('jkn_mobile.x_username')) {
            if(empty($decode['kodebooking'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Kode Booking tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['kodebooking'],"'")||strpos($decode['kodebooking'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Kode Booking salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else{
                $referensi = $this->db('mlite_antrian_referensi')->where('kodebooking', $decode['kodebooking'])->oneArray();
                $booking_registrasi = [];
                $pasien = [];
                if($referensi) {
                  $pasien = $this->db('pasien')->where('no_peserta', $referensi['nomor_kartu'])->oneArray();
                  $booking_registrasi = $this->db('booking_registrasi')
                    ->where('no_rkm_medis', $pasien['no_rkm_medis'])
                    ->where('tanggal_periksa', $referensi['tanggal_periksa'])
                    ->oneArray();
                }
                if(!$booking_registrasi) {
                    $response = array(
                        'metadata' => array(
                            'message' => 'Data Booking tidak ditemukan',
                            'code' => 201
                        )
                    );
                    http_response_code(201);
                }else{
                    if($booking_registrasi['status']=='Batal'){
                        $response = array(
                            'metadata' => array(
                                'message' => 'Booking Anda Sudah Dibatalkan.',
                                'code' => 201
                            )
                        );
                        http_response_code(201);
                    }else if($booking_registrasi['status']=='Belum'){
                        $response = array(
                            'metadata' => array(
                                'message' => 'Anda Belum Melakukan Checkin.',
                                'code' => 201
                            )
                        );
                        http_response_code(201);
                    }else if($booking_registrasi['status']=='Terdaftar'){

                        $reg_periksa = $this->db('reg_periksa')->where('no_rkm_medis', $pasien['no_rkm_medis'])->where('tgl_registrasi', $booking_registrasi['tanggal_periksa'])->oneArray();
                        $resep = $this->db('resep_obat')->where('no_rawat', $reg_periksa['no_rawat'])->where('status', 'ralan')->oneArray();

                        $mlite_antrian_loket = $this->db('mlite_antrian_loket')
                        ->select([
                            'count' => 'COUNT(DISTINCT noantrian)',
                        ])
                        ->where('type', 'Apotek')
                        ->where('postdate', $booking_registrasi['tanggal_periksa'])
                        ->oneArray();

                        $get_mlite_antrian_loket = $this->db('mlite_antrian_loket')->where('type', 'Apotek')->where('postdate', $booking_registrasi['tanggal_periksa'])->where('no_rkm_medis', $pasien['no_rkm_medis'])->oneArray();

                        if(!$resep){
                            $response = array(
                                'metadata' => array(
                                    'message' => 'Anda tidak memiliki resep dari dokter yang anda tuju, silahkan konfirmasi petugas poli',
                                    'code' => 201
                                )
                            );
                            http_response_code(201);
                        }else{
                            $resep_racikan = $this->db('resep_dokter_racikan')->where('no_resep', $resep['no_resep'])->oneArray();
                            $jenis_resep = 'Non Racikan';
                            if($resep_racikan) {
                              $jenis_resep = 'Racikan';
                            }
                            $response = array(
                                'response' => array(
                                    'jenisresep' => $jenis_resep,
                                    'nomorantrean' => $get_mlite_antrian_loket['noantrian'],
                                    'keterangan' => "Resep dibuat secara elektronik di poli"
                                ),
                                'metadata' => array(
                                    'message' => 'Ok',
                                    'code' => 200
                                )
                            );
                            http_response_code(200);
                        }
                    }
                }
            }
        } else {
            $response = array(
                'metadata' => array(
                    'message' => 'Access denied',
                    'code' => 201
                )
            );
            http_response_code(201);
        }
        echo json_encode($response);
    }

    public function getStatusAntrianFarmasi()
    {
        echo $this->_resultStatusAntrianFarmasi();
        exit();
    }

    private function _resultStatusAntrianFarmasi()
    {
        header("Content-Type: application/json");
        $header = apache_request_headers();
        $konten = trim(file_get_contents("php://input"));
        $decode = json_decode($konten, true);
        $response = array();
        if($header[$this->settings->get('jkn_mobile.header_token')] == false) {
            $response = array(
                'metadata' => array(
                    'message' => 'Token expired',
                    'code' => 201
                )
            );
            http_response_code(201);
        } else if ($header[$this->settings->get('jkn_mobile.header_token')] == $this->_getToken() && $header[$this->settings->get('jkn_mobile.header_username')] == $this->settings->get('jkn_mobile.x_username')) {
            if(empty($decode['kodebooking'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Kode Booking tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['kodebooking'],"'")||strpos($decode['kodebooking'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Kode Booking salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else{
                $referensi = $this->db('mlite_antrian_referensi')->where('kodebooking', $decode['kodebooking'])->oneArray();
                $booking_registrasi = [];
                $pasien = [];
                if($referensi) {
                  $pasien = $this->db('pasien')->where('no_peserta', $referensi['nomor_kartu'])->oneArray();
                  $booking_registrasi = $this->db('booking_registrasi')
                    ->where('no_rkm_medis', $pasien['no_rkm_medis'])
                    ->where('tanggal_periksa', $referensi['tanggal_periksa'])
                    ->oneArray();
                }
                if(!$booking_registrasi) {
                    $response = array(
                        'metadata' => array(
                            'message' => 'Data Booking tidak ditemukan',
                            'code' => 201
                        )
                    );
                    http_response_code(201);
                }else{
                    if($booking_registrasi['status']=='Batal'){
                        $response = array(
                            'metadata' => array(
                                'message' => 'Booking Anda Sudah Dibatalkan.',
                                'code' => 201
                            )
                        );
                        http_response_code(201);
                    }else if($booking_registrasi['status']=='Belum'){
                        $response = array(
                            'metadata' => array(
                                'message' => 'Anda Belum Melakukan Checkin.',
                                'code' => 201
                            )
                        );
                        http_response_code(201);
                    }else if($booking_registrasi['status']=='Terdaftar'){

                        $reg_periksa = $this->db('reg_periksa')->where('no_rkm_medis', $pasien['no_rkm_medis'])->where('tgl_registrasi', $booking_registrasi['tanggal_periksa'])->oneArray();
                        $resep = $this->db('resep_obat')->where('no_rawat', $reg_periksa['no_rawat'])->where('status', 'ralan')->oneArray();

                        $mlite_antrian_loket = $this->db('mlite_antrian_loket')
                        ->select([
                            'count' => 'COUNT(DISTINCT noantrian)',
                        ])
                        ->where('type', 'Apotek')
                        ->where('postdate', $booking_registrasi['tanggal_periksa'])
                        ->oneArray();

                        $mlite_antrian_loket_sisaantrean = $this->db('mlite_antrian_loket')
                        ->select([
                            'count' => 'COUNT(DISTINCT noantrian)',
                        ])
                        ->where('type', 'Apotek')
                        ->where('postdate', $booking_registrasi['tanggal_periksa'])
                        ->where('end_time', '<>', '00:00:00')
                        ->oneArray();

                        $get_mlite_antrian_loket = $this->db('mlite_antrian_loket')->where('type', 'Apotek')->where('postdate', $booking_registrasi['tanggal_periksa'])->where('no_rkm_medis', $pasien['no_rkm_medis'])->oneArray();

                        if(!$resep){
                            $response = array(
                                'metadata' => array(
                                    'message' => 'Anda tidak memiliki resep dari dokter yang anda tuju, silahkan konfirmasi petugas poli',
                                    'code' => 201
                                )
                            );
                            http_response_code(201);
                        }else{
                            $resep_racikan = $this->db('resep_dokter_racikan')->where('no_resep', $resep['no_resep'])->oneArray();
                            $jenis_resep = 'Non Racikan';
                            if($resep_racikan) {
                              $jenis_resep = 'Racikan';
                            }

                            $response = array(
                                'response' => array(
                                    'jenisresep' => $jenis_resep,
                                    'totalantrean' => $mlite_antrian_loket['count'],
                                    'sisaantrean' => $mlite_antrian_loket_sisaantrean['count'],
                                    'antreanpanggil' => $this->settings->get('anjungan.panggil_apotek_nomor')-1,
                                    'keterangan' => ""
                                ),
                                'metadata' => array(
                                    'message' => 'Ok',
                                    'code' => 200
                                )
                            );

                            http_response_code(200);
                        }
                    }
                }
            }
        } else {
            $response = array(
                'metadata' => array(
                    'message' => 'Access denied',
                    'code' => 201
                )
            );
            http_response_code(201);
        }
        echo json_encode($response);
    }

    public function getSisaAntrian()
    {
        echo $this->_resultSisaAntrian();
        exit();
    }

    private function _resultSisaAntrian()
    {
        header("Content-Type: application/json");
        $header = apache_request_headers();
        $konten = trim(file_get_contents("php://input"));
        $decode = json_decode($konten, true);
        $response = array();
        if($header[$this->settings->get('jkn_mobile.header_token')] == false) {
            $response = array(
                'metadata' => array(
                    'message' => 'Token expired',
                    'code' => 201
                )
            );
            http_response_code(201);
        } else if ($header[$this->settings->get('jkn_mobile.header_token')] == $this->_getToken() && $header[$this->settings->get('jkn_mobile.header_username')] == $this->settings->get('jkn_mobile.x_username')) {
            if(empty($decode['kodebooking'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Kode Booking tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['kodebooking'],"'")||strpos($decode['kodebooking'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Kode Booking salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else{
                $referensi = $this->db('mlite_antrian_referensi')->where('kodebooking', $decode['kodebooking'])->oneArray();
                $booking_registrasi = [];
                $pasien = [];
                if($referensi) {
                  $pasien = $this->db('pasien')->where('no_peserta', $referensi['nomor_kartu'])->oneArray();
                  $booking_registrasi = $this->db('booking_registrasi')
                    ->where('no_rkm_medis', $pasien['no_rkm_medis'])
                    ->where('tanggal_periksa', $referensi['tanggal_periksa'])
                    ->oneArray();
                }
                if(!$booking_registrasi) {
                    $response = array(
                        'metadata' => array(
                            'message' => 'Data Booking tidak ditemukan',
                            'code' => 201
                        )
                    );
                    http_response_code(201);
                }else{
                    if($booking_registrasi['status']=='Belum'){
                        $response = array(
                            'metadata' => array(
                                'message' => 'Anda belum melakukan checkin, Silahkan checkin terlebih dahulu',
                                'code' => 201
                            )
                        );
                        http_response_code(201);
                    }else if($booking_registrasi['status']=='Batal'){
                        $response = array(
                            'metadata' => array(
                                'message' => 'Data Booking Sudah Dibatalkan',
                                'code' => 201
                            )
                        );
                        http_response_code(201);
                    }else if($booking_registrasi['status']=='Terdaftar'){
                        $noreg = $this->db('reg_periksa')->where('no_rkm_medis', $pasien['no_rkm_medis'])->where('tgl_registrasi', $booking_registrasi['tanggal_periksa'])->oneArray();

                        $max_antrian = $this->db()->pdo()->prepare("SELECT reg_periksa.no_reg FROM reg_periksa WHERE reg_periksa.stts='Belum' AND reg_periksa.kd_dokter='$booking_registrasi[kd_dokter]' AND reg_periksa.kd_poli='$booking_registrasi[kd_poli]' AND reg_periksa.tgl_registrasi='$booking_registrasi[tanggal_periksa]' ORDER BY CONVERT(RIGHT(reg_periksa.no_reg,3),signed) LIMIT 1 ");
                        $max_antrian->execute();
                        $max_antrian = $max_antrian->fetch();

                        $data = $this->db()->pdo()->prepare("SELECT reg_periksa.kd_poli,poliklinik.nm_poli,dokter.nm_dokter,
                            reg_periksa.no_reg,COUNT(reg_periksa.no_rawat) as total_antrean,
                            IFNULL(SUM(CASE WHEN reg_periksa.stts ='Belum' THEN 1 ELSE 0 END),0) as sisa_antrean
                            FROM reg_periksa INNER JOIN poliklinik ON poliklinik.kd_poli=reg_periksa.kd_poli
                            INNER JOIN dokter ON dokter.kd_dokter=reg_periksa.kd_dokter
                            WHERE reg_periksa.kd_dokter='$booking_registrasi[kd_dokter]' and reg_periksa.kd_poli='$booking_registrasi[kd_poli]'and reg_periksa.tgl_registrasi='$booking_registrasi[tanggal_periksa]'");
                        $data->execute();
                        $data = $data->fetch();

                        if ($data['nm_poli'] != '') {
                            $response = array(
                                'response' => array(
                                    'nomorantrean' => "A-".$noreg['no_reg'],
                                    'namapoli' => $data['nm_poli'],
                                    'namadokter' => $data['nm_dokter'],
                                    'sisaantrean' => ($data['sisa_antrean']>0?$data['sisa_antrean']:0),
                                    'antreanpanggil' => "A-".$max_antrian['no_reg'],
                                    'waktutunggu' => (($data['sisa_antrean']*10)*1000),
                                    'keterangan' => "Datanglah Minimal 30 Menit, jika no antrian anda terlewat, silakan konfirmasi ke layanan pelanggan, Terima Kasih .."
                                ),
                                'metadata' => array(
                                    'message' => 'Ok',
                                    'code' => 200
                                )
                            );
                            http_response_code(200);
                        } else {
                            $response = array(
                                'metadata' => array(
                                    'message' => 'Antrean Tidak Ditemukan !',
                                    'code' => 201
                                )
                            );
                            http_response_code(201);
                        }
                    }
                }
            }
        } else {
            $response = array(
                'metadata' => array(
                    'message' => 'Access denied',
                    'code' => 201
                )
            );
            http_response_code(201);
        }
        echo json_encode($response);
    }

    public function getBatalAntrian()
    {
        echo $this->_resultBatalAntrian();
        exit();
    }

    private function _resultBatalAntrian()
    {
        header("Content-Type: application/json");
        $header = apache_request_headers();
        $konten = trim(file_get_contents("php://input"));
        $decode = json_decode($konten, true);
        $response = array();
        if($header[$this->settings->get('jkn_mobile.header_token')] == false) {
            $response = array(
                'metadata' => array(
                    'message' => 'Token expired',
                    'code' => 201
                )
            );
            http_response_code(201);
        } else if ($header[$this->settings->get('jkn_mobile.header_token')] == $this->_getToken() && $header[$this->settings->get('jkn_mobile.header_username')] == $this->settings->get('jkn_mobile.x_username')) {
            if(empty($decode['kodebooking'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Kode Booking tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['kodebooking'],"'")||strpos($decode['kodebooking'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Kode Booking salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(empty($decode['keterangan'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Keterangan tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['keterangan'],"'")||strpos($decode['keterangan'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Keterangan salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else{
                $referensi = $this->db('mlite_antrian_referensi')->where('kodebooking', $decode['kodebooking'])->oneArray();
                $booking_registrasi = [];
                $pasien = [];
                if($referensi) {
                  $pasien = $this->db('pasien')->where('no_peserta', $referensi['nomor_kartu'])->oneArray();
                  $booking_registrasi = $this->db('booking_registrasi')
                    ->where('no_rkm_medis', $pasien['no_rkm_medis'])
                    ->where('tanggal_periksa', $referensi['tanggal_periksa'])
                    ->oneArray();
                }
                if(!$booking_registrasi) {
                    $response = array(
                        'metadata' => array(
                            'message' => 'Data Booking tidak ditemukan',
                            'code' => 201
                        )
                    );
                    http_response_code(201);
                }else{
                    if(strtotime(date("Y-m-d")) > strtotime($booking_registrasi['tanggal_periksa'])){
                        $response = array(
                            'metadata' => array(
                                'message' => 'Pembatalan Antrean tidak berlaku mundur',
                                'code' => 201
                            )
                        );
                        http_response_code(201);
                    }else if($booking_registrasi['status']=='Terdaftar'){
                        $response = array(
                            'metadata' => array(
                                'message' => 'Anda Sudah Checkin, Pendaftaran Tidak Bisa Dibatalkan',
                                'code' => 201
                            )
                        );
                        http_response_code(201);
                    }else if($booking_registrasi['status']=='Belum'){
                        $batal = $this->db('booking_registrasi')->where('no_rkm_medis', $pasien['no_rkm_medis'])->where('tanggal_periksa', $referensi['tanggal_periksa'])->delete();
                        if(!$this->db('booking_registrasi')->where('no_rkm_medis', $pasien['no_rkm_medis'])->where('tanggal_periksa', $referensi['tanggal_periksa'])->oneArray()){
                            $response = array(
                                'metadata' => array(
                                    'message' => 'Ok',
                                    'code' => 200
                                )
                            );
                            $this->db('mlite_antrian_referensi_batal')->save([
                                'tanggal_batal' => date('Y-m-d'),
                                'nomor_referensi' => $referensi['nomor_referensi'],
                                'kodebooking' => $decode['kodebooking'],
                                'keterangan' => $decode['keterangan']
                            ]);
                            $this->db('mlite_antrian_referensi')->where('kodebooking', $decode['kodebooking'])->delete();
                            http_response_code(200);
                        }else{
                            $response = array(
                                'metadata' => array(
                                    'message' => "Maaf Terjadi Kesalahan, Hubungi Admnistrator..",
                                    'code' => 201
                                )
                            );
                            http_response_code(201);
                        }
                    }
                }
            }
        } else {
            $response = array(
                'metadata' => array(
                    'message' => 'Access denied',
                    'code' => 201
                )
            );
            http_response_code(201);
        }
        echo json_encode($response);
    }

    public function getPasienBaru()
    {
        echo $this->_resultPasienBaru();
        exit();
    }

    private function _resultPasienBaru()
    {
        header("Content-Type: application/json");
        $header = apache_request_headers();
        $konten = trim(file_get_contents("php://input"));
        $decode = json_decode($konten, true);
        $response = array();
        if($header[$this->settings->get('jkn_mobile.header_token')] == false) {
            $response = array(
                'metadata' => array(
                    'message' => 'Token expired',
                    'code' => 201
                )
            );
            http_response_code(201);
        } else if ($header[$this->settings->get('jkn_mobile.header_token')] == $this->_getToken() && $header[$this->settings->get('jkn_mobile.header_username')] == $this->settings->get('jkn_mobile.x_username')) {
            if (empty($decode['nomorkartu'])){
                $response = array(
                    'metadata' => array(
                        'message' => 'Nomor Kartu tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if (mb_strlen($decode['nomorkartu'], 'UTF-8') <> 13){
                $response = array(
                    'metadata' => array(
                        'message' => 'Nomor Kartu harus 13 digit',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if (!preg_match("/^[0-9]{13}$/",$decode['nomorkartu'])){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Nomor Kartu tidak sesuai',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }elseif (empty($decode['nik'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'NIK tidak boleh kosong ',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }elseif (strlen($decode['nik']) <> 16) {
                $response = array(
                    'metadata' => array(
                        'message' => 'NIK harus 16 digit ',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if (!preg_match("/^[0-9]{16}$/",$decode['nik'])){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format NIK tidak sesuai',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }elseif (empty($decode['nomorkk'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Nomor KK tidak boleh kosong ',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }elseif (strlen($decode['nomorkk']) <> 16) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Nomor KK harus 16 digit ',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if (!preg_match("/^[0-9]{16}$/",$decode['nomorkk'])){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Nomor KK tidak sesuai',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(empty($decode['nama'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Nama tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['nama'],"'")||strpos($decode['nama'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Nama salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(empty($decode['jeniskelamin'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Jenis Kelamin tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['jeniskelamin'],"'")||strpos($decode['jeniskelamin'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Jenis Kelamin tidak ditemukan',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(!(($decode['jeniskelamin']=="L")||($decode['jeniskelamin']=="P"))){
                $response = array(
                    'metadata' => array(
                        'message' => 'Jenis Kelmain tidak ditemukan',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(empty($decode['tanggallahir'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Tanggal Lahir tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$decode['tanggallahir'])){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Tanggal Lahir tidak sesuai, format yang benar adalah yyyy-mm-dd',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(empty($decode['nohp'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'No.HP tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['nohp'],"'")||strpos($decode['nohp'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format No.HP salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(empty($decode['alamat'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Alamat tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['alamat'],"'")||strpos($decode['alamat'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Alamat salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(empty($decode['kodeprop'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Kode Propinsi tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['kodeprop'],"'")||strpos($decode['kodeprop'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Kode Propinsi salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(empty($decode['namaprop'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Nama Propinsi tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['namaprop'],"'")||strpos($decode['namaprop'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Nama Propinsi salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(empty($decode['kodedati2'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Kode Dati II tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['kodedati2'],"'")||strpos($decode['kodedati2'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Kode Dati II salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(empty($decode['namadati2'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Nama Dati II tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['namadati2'],"'")||strpos($decode['namadati2'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Nama Dati II salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(empty($decode['kodekec'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Kode Kecamatan tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['kodekec'],"'")||strpos($decode['kodekec'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Kode Kecamatan salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(empty($decode['namakec'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Nama Kecamatan tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['namakec'],"'")||strpos($decode['namakec'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Nama Kecamatan salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(empty($decode['kodekel'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Kode Kelurahan tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['kodekel'],"'")||strpos($decode['kodekel'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Kode Kelurahan salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(empty($decode['namakel'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Nama Kelurahan tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['namakel'],"'")||strpos($decode['namakel'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Nama Kelurahan salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(empty($decode['rw'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'RW tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['rw'],"'")||strpos($decode['rw'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format RW salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(empty($decode['rt'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'RT tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['rt'],"'")||strpos($decode['rt'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format RT salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else{
                if($this->db('pasien')->where('no_peserta', $decode['nomorkartu'])->oneArray()) {
                    $response = array(
                        'metadata' => array(
                            'message' => 'Data pasien ini sudah terdaftar',
                            'code' => 201
                        )
                    );
                    http_response_code(201);
                } else {
                    $date = date('Y-m-d');

                    $_POST['no_rkm_medis'] = $this->core->setNoRM();
                    $_POST['nm_pasien'] = $decode['nama'];
                    $_POST['no_ktp'] = $decode['nik'];
                    $_POST['jk'] = $decode['jeniskelamin'];
                    $_POST['tmp_lahir'] = '-';
                    $_POST['tgl_lahir'] = $decode['tanggallahir'];
                    $_POST['nm_ibu'] = '-';
                    $_POST['alamat'] = $decode['alamat'];
                    $_POST['gol_darah'] = '-';
                    $_POST['pekerjaan'] = '-';
                    $_POST['stts_nikah'] = 'JOMBLO';
                    $_POST['agama'] = '-';
                    $_POST['tgl_daftar'] = $date;
                    $_POST['no_tlp'] = $decode['nohp'];
                    $_POST['umur'] = $this->_setUmur($decode['tanggallahir']);;
                    $_POST['pnd'] = '-';
                    $_POST['keluarga'] = 'AYAH';
                    $_POST['namakeluarga'] = '-';
                    $_POST['kd_pj'] = 'BPJ';
                    $_POST['no_peserta'] = $decode['nomorkartu'];
                    $_POST['kd_kel'] = $this->settings->get('jkn_mobile.kdkel');
                    $_POST['kd_kec'] = $this->settings->get('jkn_mobile.kdkec');
                    $_POST['kd_kab'] = $this->settings->get('jkn_mobile.kdkab');
                    $_POST['pekerjaanpj'] = '-';
                    $_POST['alamatpj'] = '-';
                    $_POST['kelurahanpj'] = '-';
                    $_POST['kecamatanpj'] = '-';
                    $_POST['kabupatenpj'] = '-';
                    $_POST['perusahaan_pasien'] = $this->settings->get('jkn_mobile.perusahaan_pasien');
                    $_POST['suku_bangsa'] = $this->settings->get('jkn_mobile.suku_bangsa');
                    $_POST['bahasa_pasien'] = $this->settings->get('jkn_mobile.bahasa_pasien');
                    $_POST['cacat_fisik'] = $this->settings->get('jkn_mobile.cacat_fisik');
                    $_POST['email'] = '';
                    $_POST['nip'] = '';
                    $_POST['kd_prop'] = $this->settings->get('jkn_mobile.kdprop');
                    $_POST['propinsipj'] = '-';

                    $query = $this->db('pasien')->save($_POST);

                    if($query) {
                        $this->db()->pdo()->exec("UPDATE set_no_rkm_medis SET no_rkm_medis='$_POST[no_rkm_medis]'");
                    }

                    $pasien = $this->db('pasien')->where('no_peserta', $decode['nomorkartu'])->oneArray();
                    $response = array(
                        'response' => array(
                            'norm' => $_POST['no_rkm_medis']
                        ),
                        'metadata' => array(
                            'message' => 'Pasien berhasil mendapatkann nomor RM, silahkan lanjutkan ke booking. Pasien tidak perlu ke admisi.',
                            'code' => 200
                        )
                    );
                    http_response_code(200);
                }
            }
        } else {
            $response = array(
                'metadata' => array(
                    'message' => 'Access denied',
                    'code' => 201
                )
            );
            http_response_code(201);
        }
        echo json_encode($response);
    }

    public function getPasienCheckIn()
    {
        echo $this->_resultPasienCheckIn();
        exit();
    }

    private function _resultPasienCheckIn()
    {
        header("Content-Type: application/json");
        $header = apache_request_headers();
        $konten = trim(file_get_contents("php://input"));
        $decode = json_decode($konten, true);
        $response = array();
        if($header[$this->settings->get('jkn_mobile.header_token')] == false) {
            $response = array(
                'metadata' => array(
                    'message' => 'Token expired',
                    'code' => 201
                )
            );
            http_response_code(201);
        } else if ($header[$this->settings->get('jkn_mobile.header_token')] == $this->_getToken() && $header[$this->settings->get('jkn_mobile.header_username')] == $this->settings->get('jkn_mobile.x_username')) {
            $tanggal=date("Y-m-d", ($decode['waktu']/1000));
            $jam = date("H:i:s",($decode['waktu']/1000));
            if(empty($decode['kodebooking'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Kode Booking tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strpos($decode['kodebooking'],"'")||strpos($decode['kodebooking'],"\\")){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Kode Booking salah',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }elseif(empty($decode['waktu'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Waktu tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$tanggal)){
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Tanggal Checkin tidak sesuai, format yang benar adalah yyyy-mm-dd',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else if(strtotime(date("Y-m-d")) > strtotime($tanggal)){
                $response = array(
                    'metadata' => array(
                        'message' => 'Waktu Checkin tidak berlaku mundur',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else{
                $referensi = $this->db('mlite_antrian_referensi')->where('kodebooking', $decode['kodebooking'])->oneArray();
                $booking_registrasi = [];
                $pasien = [];
                if($referensi) {
                  $pasien = $this->db('pasien')->where('no_peserta', $referensi['nomor_kartu'])->oneArray();
                  $booking_registrasi = $this->db('booking_registrasi')
                    ->where('no_rkm_medis', $pasien['no_rkm_medis'])
                    ->where('tanggal_periksa', $referensi['tanggal_periksa'])
                    ->oneArray();
                }
                if(!$booking_registrasi) {
                    $response = array(
                        'metadata' => array(
                            'message' => 'Data Booking tidak ditemukan',
                            'code' => 201
                        )
                    );
                    http_response_code(201);
                }else{
                    if($booking_registrasi['status']=='Terdaftar'){
                        $response = array(
                            'metadata' => array(
                                'message' => 'Anda Sudah Checkin',
                                'code' => 201
                            )
                        );
                        http_response_code(201);
                    }else if($booking_registrasi['status']=='Belum'){
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

                        $cekjam = $this->db("jadwal")->where("kd_poli",$booking_registrasi['kd_poli'])
                        ->where('kd_dokter',$booking_registrasi['kd_dokter'])->where('hari_kerja',$hari)->oneArray();
                        $interval = $this->db()->pdo()->prepare("SELECT (TO_DAYS('$booking_registrasi[tanggal_periksa]')-TO_DAYS('$tanggal'))");
                        $interval->execute();
                        $interval = $interval->fetch();

                        if($interval[0]<=0){
                            if (strtotime($jam) >= strtotime($cekjam['jam_selesai'])) {
                                # code...
                                $response = array(
                                    'metadata' => array(
                                        'message' => 'Chekin Anda sudah expired, maksimal 1 hari sebelum tanggal periksa dan 1 jam sebelum jam pelayanan berakhir. Silahkan konfirmasi ke layanan pelanggan',
                                        'code' => 201
                                    )
                                );
                                http_response_code(201);
                            } else {
                                $cek_stts_daftar = $this->db('reg_periksa')->where('no_rkm_medis', $booking_registrasi['no_rkm_medis'])->count();
                                $_POST['stts_daftar'] = 'Baru';
                                if($cek_stts_daftar > 0) {
                                $_POST['stts_daftar'] = 'Lama';
                                }

                                $biaya_reg = $this->db('poliklinik')->where('kd_poli', $booking_registrasi['kd_poli'])->oneArray();
                                $_POST['biaya_reg'] = $biaya_reg['registrasi'];
                                if($_POST['stts_daftar'] == 'Lama') {
                                $_POST['biaya_reg'] = $biaya_reg['registrasilama'];
                                }

                                $cek_status_poli = $this->db('reg_periksa')->where('no_rkm_medis', $booking_registrasi['no_rkm_medis'])->where('kd_poli', $booking_registrasi['kd_poli'])->count();
                                $_POST['status_poli'] = 'Baru';
                                if($cek_status_poli > 0) {
                                $_POST['status_poli'] = 'Lama';
                                }

                                // set umur
                                $tanggal = new \DateTime($this->core->getPasienInfo('tgl_lahir', $booking_registrasi['no_rkm_medis']));
                                $today = new \DateTime(date('Y-m-d'));
                                $y = $today->diff($tanggal)->y;
                                $m = $today->diff($tanggal)->m;
                                $d = $today->diff($tanggal)->d;

                                $umur="0";
                                $sttsumur="Th";
                                if($y>0){
                                    $umur=$y;
                                    $sttsumur="Th";
                                }else if($y==0){
                                    if($m>0){
                                        $umur=$m;
                                        $sttsumur="Bl";
                                    }else if($m==0){
                                        $umur=$d;
                                        $sttsumur="Hr";
                                    }
                                }

                                $tanggalupdate=date("Y-m-d H:i:s", ($decode['waktu']/1000));
                                $update = $this->db('booking_registrasi')->where('no_rkm_medis', $pasien['no_rkm_medis'])->where('tanggal_periksa', $referensi['tanggal_periksa'])->update(['status' => 'Terdaftar']);
                                $insert = $this->db('reg_periksa')
                                ->save([
                                    'no_reg' => $booking_registrasi['no_reg'],
                                    'no_rawat' => $this->core->setNoRawat($booking_registrasi['tanggal_periksa']),
                                    'tgl_registrasi' => $booking_registrasi['tanggal_periksa'],
                                    'jam_reg' => date('H:i:s'),
                                    'kd_dokter' => $booking_registrasi['kd_dokter'],
                                    'no_rkm_medis' => $booking_registrasi['no_rkm_medis'],
                                    'kd_poli' => $booking_registrasi['kd_poli'],
                                    'p_jawab' => $this->core->getPasienInfo('namakeluarga', $booking_registrasi['no_rkm_medis']),
                                    'almt_pj' => $this->core->getPasienInfo('alamatpj', $booking_registrasi['no_rkm_medis']),
                                    'hubunganpj' => $this->core->getPasienInfo('keluarga', $booking_registrasi['no_rkm_medis']),
                                    'biaya_reg' => $_POST['biaya_reg'],
                                    'stts' => 'Belum',
                                    'stts_daftar' => $_POST['stts_daftar'],
                                    'status_lanjut' => 'Ralan',
                                    'kd_pj' => $booking_registrasi['kd_pj'],
                                    'umurdaftar' => $umur,
                                    'sttsumur' => $sttsumur,
                                    'status_bayar' => 'Belum Bayar',
                                    'status_poli' => $_POST['status_poli']
                                ]);
                                if($insert){
                                    $response = array(
                                        'metadata' => array(
                                            'message' => 'Ok 1',
                                            'code' => 200
                                        )
                                    );
                                    http_response_code(200);
                                }else{
                                    $response = array(
                                        'metadata' => array(
                                            'message' => "Maaf terjadi kesalahan, hubungi Admnistrator..",
                                            'code' => 401
                                        )
                                    );
                                    http_response_code(401);
                                }
                            }
                        }else{
                            $cek_stts_daftar = $this->db('reg_periksa')->where('no_rkm_medis', $booking_registrasi['no_rkm_medis'])->count();
                            $_POST['stts_daftar'] = 'Baru';
                            if($cek_stts_daftar > 0) {
                              $_POST['stts_daftar'] = 'Lama';
                            }

                            $biaya_reg = $this->db('poliklinik')->where('kd_poli', $booking_registrasi['kd_poli'])->oneArray();
                            $_POST['biaya_reg'] = $biaya_reg['registrasi'];
                            if($_POST['stts_daftar'] == 'Lama') {
                              $_POST['biaya_reg'] = $biaya_reg['registrasilama'];
                            }

                            $cek_status_poli = $this->db('reg_periksa')->where('no_rkm_medis', $booking_registrasi['no_rkm_medis'])->where('kd_poli', $booking_registrasi['kd_poli'])->count();
                            $_POST['status_poli'] = 'Baru';
                            if($cek_status_poli > 0) {
                              $_POST['status_poli'] = 'Lama';
                            }

                            // set umur
                            $tanggal = new \DateTime($this->core->getPasienInfo('tgl_lahir', $booking_registrasi['no_rkm_medis']));
                            $today = new \DateTime(date('Y-m-d'));
                            $y = $today->diff($tanggal)->y;
                            $m = $today->diff($tanggal)->m;
                            $d = $today->diff($tanggal)->d;

                            $umur="0";
                            $sttsumur="Th";
                            if($y>0){
                                $umur=$y;
                                $sttsumur="Th";
                            }else if($y==0){
                                if($m>0){
                                    $umur=$m;
                                    $sttsumur="Bl";
                                }else if($m==0){
                                    $umur=$d;
                                    $sttsumur="Hr";
                                }
                            }

                            $tanggalupdate=date("Y-m-d H:i:s", ($decode['waktu']/1000));
                            $update = $this->db('booking_registrasi')->where('no_rkm_medis', $pasien['no_rkm_medis'])->where('tanggal_periksa', $referensi['tanggal_periksa'])->update(['status' => 'Terdaftar']);
                            $insert = $this->db('reg_periksa')
                              ->save([
                                'no_reg' => $booking_registrasi['no_reg'],
                                'no_rawat' => $this->core->setNoRawat($booking_registrasi['tanggal_periksa']),
                                'tgl_registrasi' => $booking_registrasi['tanggal_periksa'],
                                'jam_reg' => date('H:i:s'),
                                'kd_dokter' => $booking_registrasi['kd_dokter'],
                                'no_rkm_medis' => $booking_registrasi['no_rkm_medis'],
                                'kd_poli' => $booking_registrasi['kd_poli'],
                                'p_jawab' => $this->core->getPasienInfo('namakeluarga', $booking_registrasi['no_rkm_medis']),
                                'almt_pj' => $this->core->getPasienInfo('alamatpj', $booking_registrasi['no_rkm_medis']),
                                'hubunganpj' => $this->core->getPasienInfo('keluarga', $booking_registrasi['no_rkm_medis']),
                                'biaya_reg' => $_POST['biaya_reg'],
                                'stts' => 'Belum',
                                'stts_daftar' => $_POST['stts_daftar'],
                                'status_lanjut' => 'Ralan',
                                'kd_pj' => $booking_registrasi['kd_pj'],
                                'umurdaftar' => $umur,
                                'sttsumur' => $sttsumur,
                                'status_bayar' => 'Belum Bayar',
                                'status_poli' => $_POST['status_poli']
                              ]);
                            if($insert){
                                $response = array(
                                    'metadata' => array(
                                        'message' => 'Ok 2',
                                        'code' => 200
                                    )
                                );
                                http_response_code(200);
                            }else{
                                $response = array(
                                    'metadata' => array(
                                        'message' => "Maaf terjadi kesalahan, hubungi Admnistrator..",
                                        'code' => 401
                                    )
                                );
                                http_response_code(401);
                            }
                        }
                    }
                }
            }
        } else {
            $response = array(
                'metadata' => array(
                    'message' => 'Access denied',
                    'code' => 201
                )
            );
            http_response_code(201);
        }
        echo json_encode($response);
    }

    public function getOperasiRS()
    {
        echo $this->_resultOperasiRS();
        exit();
    }

    private function _resultOperasiRS()
    {
        header("Content-Type: application/json");
        $header = apache_request_headers();
        $konten = trim(file_get_contents("php://input"));
        $decode = json_decode($konten, true);
        $response = array();
        if($header[$this->settings->get('jkn_mobile.header_token')] == false) {
            $response = array(
                'metadata' => array(
                    'message' => 'Token expired',
                    'code' => 201
                )
            );
            http_response_code(201);
        } else if ($header[$this->settings->get('jkn_mobile.header_token')] == $this->_getToken() && $header[$this->settings->get('jkn_mobile.header_username')] == $this->settings->get('jkn_mobile.x_username')) {
            $data = array();
            $sql = $this->db()->pdo()->prepare("SELECT booking_operasi.no_rawat AS kodebooking, booking_operasi.tanggal AS tanggaloperasi, paket_operasi.nm_perawatan AS jenistindakan,  maping_poli_bpjs.kd_poli_bpjs AS kodepoli, poliklinik.nm_poli AS namapoli, booking_operasi.status AS terlaksana, pasien.no_peserta AS nopeserta FROM pasien, booking_operasi, paket_operasi, reg_periksa, jadwal, poliklinik, maping_poli_bpjs WHERE booking_operasi.no_rawat = reg_periksa.no_rawat AND pasien.no_rkm_medis = reg_periksa.no_rkm_medis AND booking_operasi.kode_paket = paket_operasi.kode_paket AND booking_operasi.kd_dokter = jadwal.kd_dokter AND jadwal.kd_poli = poliklinik.kd_poli AND jadwal.kd_poli=maping_poli_bpjs.kd_poli_rs AND booking_operasi.tanggal BETWEEN '$decode[tanggalawal]' AND '$decode[tanggalakhir]' GROUP BY booking_operasi.no_rawat");
            $sql->execute();
            $sql = $sql->fetchAll();

            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$decode['tanggalawal'])) {
               $errors[] = 'Format tanggal awal jadwal operasi tidak sesuai';
            }
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$decode['tanggalakhir'])) {
               $errors[] = 'Format tanggal akhir jadwal operasi tidak sesuai';
            }
            if ($decode['tanggalawal'] > $decode['tanggalakhir']) {
              $errors[] = 'Format tanggal awal harus lebih kecil dari tanggal akhir';
            }
            if(!empty($errors)) {
                foreach($errors as $error) {
                    $response = array(
                        'metadata' => array(
                            'message' => $this->_getErrors($error),
                            'code' => 201
                        )
                    );
                    http_response_code(201);
                }
            } else {
                if (count($sql) > 0) {
                    foreach ($sql as $data) {
                        if($data['terlaksana'] == 'Menunggu') {
                          $data['terlaksana'] = 0;
                        } else {
                          $data['terlaksana'] = 1;
                        }
                        $data_array[] = array(
                                'kodebooking' => $data['kodebooking'],
                                'tanggaloperasi' => $data['tanggaloperasi'],
                                'jenistindakan' => $data['jenistindakan'],
                                'kodepoli' => $data['kodepoli'],
                                'namapoli' => $data['namapoli'],
                                'terlaksana' => $data['terlaksana'],
                                'nopeserta' => $data['nopeserta'],
                                'lastupdate' => strtotime(date('Y-m-d H:i:s')) * 1000
                        );
                    }
                    $response = array(
                        'response' => array(
                            'list' => (
                                $data_array
                            )
                        ),
                        'metadata' => array(
                            'message' => 'Ok',
                            'code' => 200
                        )
                    );
                    http_response_code(200);
                } else {
                    $response = array(
                        'metadata' => array(
                            'message' => 'Maaf tidak ada daftar booking operasi.',
                            'code' => 201
                        )
                    );
                    http_response_code(201);
                }
            }
        } else {
            $response = array(
                'metadata' => array(
                    'message' => 'Access denied',
                    'code' => 201
                )
            );
            http_response_code(201);
        }
        echo json_encode($response);
    }

    public function getOperasiPasien()
    {
        echo $this->_resultOperasiPasien();
        exit();
    }

    private function _resultOperasiPasien()
    {
        header("Content-Type: application/json");
        $header = apache_request_headers();
        $konten = trim(file_get_contents("php://input"));
        $decode = json_decode($konten, true);
        $response = array();
        if($header[$this->settings->get('jkn_mobile.header_token')] == false) {
            $response = array(
                'metadata' => array(
                    'message' => 'Token expired',
                    'code' => 201
                )
            );
            http_response_code(201);
        } else if ($header[$this->settings->get('jkn_mobile.header_token')] == $this->_getToken() && $header[$this->settings->get('jkn_mobile.header_username')] == $this->settings->get('jkn_mobile.x_username')) {
            $data = array();
            $cek_nopeserta = $this->db('pasien')->where('no_peserta', $decode['nopeserta'])->oneArray();
            $sql = $this->db()->pdo()->prepare("SELECT booking_operasi.no_rawat AS kodebooking, booking_operasi.tanggal AS tanggaloperasi, paket_operasi.nm_perawatan AS jenistindakan, maping_poli_bpjs.kd_poli_bpjs AS kodepoli, poliklinik.nm_poli AS namapoli, booking_operasi.status AS terlaksana FROM pasien, booking_operasi, paket_operasi, reg_periksa, jadwal, poliklinik, maping_poli_bpjs WHERE booking_operasi.no_rawat = reg_periksa.no_rawat AND pasien.no_rkm_medis = reg_periksa.no_rkm_medis AND booking_operasi.kode_paket = paket_operasi.kode_paket AND booking_operasi.kd_dokter = jadwal.kd_dokter AND jadwal.kd_poli = poliklinik.kd_poli AND jadwal.kd_poli=maping_poli_bpjs.kd_poli_rs AND pasien.no_peserta = '$decode[nopeserta]'  GROUP BY booking_operasi.no_rawat");
            $sql->execute();
            $sql = $sql->fetchAll();

            if($cek_nopeserta == 0) {
               $errors[] = 'Nomor peserta tidak ditemukan';
            }
            if (!empty($decode['nopeserta']) && !ctype_digit($decode['nopeserta']) ){
               $errors[] = 'Nomor kartu harus mengandung angka saja!!';
            }
            if (!empty($decode['nomorkartu']) && mb_strlen($decode['nomorkartu'], 'UTF-8') < 13){
               $errors[] = 'Nomor kartu harus 13 digit';
            }
            if(!empty($errors)) {
                foreach($errors as $error) {
                    $response = array(
                        'metadata' => array(
                            'message' => $this->_getErrors($error),
                            'code' => 201
                        )
                    );
                    http_response_code(201);
                }
            } else {
                if ($decode['nopeserta'] != '') {
                    $data_array = [];
                    foreach ($sql as $data) {
                        if($data['terlaksana'] == 'Menunggu') {
                          $data['terlaksana'] = 0;
                        } else {
                          $data['terlaksana'] = 1;
                        }
                        $data_array[] = array(
                                'kodebooking' => $data['kodebooking'],
                                'tanggaloperasi' => $data['tanggaloperasi'],
                                'jenistindakan' => $data['jenistindakan'],
                                'kodepoli' => $data['kodepoli'],
                                'namapoli' => $data['namapoli'],
                                'terlaksana' => $data['terlaksana']
                        );
                    }
                    $response = array(
                        'response' => array(
                            'list' => (
                                $data_array
                            )
                        ),
                        'metadata' => array(
                            'message' => 'Ok',
                            'code' => 200
                        )
                    );
                    http_response_code(200);
                } else {
                    $response = array(
                        'metadata' => array(
                            'message' => 'Maaf tidak ada daftar booking operasi.',
                            'code' => 201
                        )
                    );
                    http_response_code(201);
                }
            }
        } else {
            $response = array(
                'metadata' => array(
                    'message' => 'Access denied',
                    'code' => 201
                )
            );
            http_response_code(201);
        }
        echo json_encode($response);
    }

    public function _getJadwal($kodepoli, $tanggal)
    {
        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
        $key = $this->consid.$this->secretkey.$tStamp;
        date_default_timezone_set($this->settings->get('settings.timezone'));

        $url = $this->bpjsurl.'jadwaldokter/kodepoli/'.$kodepoli.'/tanggal/'.$tanggal;
        $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
        $json = json_decode($output, true);
        $code = $json['metadata']['code'];
        $message = $json['metadata']['message'];
        $stringDecrypt = stringDecrypt($key, $json['response']);
        $decompress = '""';
        if(!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
        }
        if($json != null) {
          echo '{
                  "metaData": {
                      "code": "'.$code.'",
                      "message": "'.$message.'"
                  },
                  "response": '.$decompress.'}';
        } else {
          echo '{
                  "metaData": {
                      "code": "5000",
                      "message": "ERROR"
                  },
                  "response": "ADA KESALAHAN ATAU SAMBUNGAN KE SERVER BPJS TERPUTUS."}';
        }
        exit();
    }

    public function _getUpdateJadwal($data = [])
    {
        $data = [
            'kodepoli' => 'ANA',
            'kodesubspesialis' => '1',
            'kodedokter' => '123456',
            'jadwal' => [
              array(
                'hari' => '1',
                'buka' => '08:00',
                'tutup' => '12:00'
              ),
              array(
                'hari' => '2',
                'buka' => '08:00',
                'tutup' => '12:00'
              ),
              array(
                'hari' => '3',
                'buka' => '08:00',
                'tutup' => '12:00'
              ),
              array(
                'hari' => '4',
                'buka' => '08:00',
                'tutup' => '12:00'
              ),
              array(
                'hari' => '5',
                'buka' => '08:00',
                'tutup' => '12:00'
              ),
              array(
                'hari' => '6',
                'buka' => '08:00',
                'tutup' => '12:00'
              ),
            ]
        ];

        $data = json_encode($data);
        $url = $this->bpjsurl.'updatejadwaldokter';
        $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, NULL);
        $json = json_decode($output, true);
        echo json_encode($json);
        exit();
    }

    public function _getAntreanAdd()
    {
        echo '
        <head>
        <meta http-equiv="refresh" content="30">
        </head>
        <title>Tambah antrian BPJS</title>
        </head>
        <body>
        <form action="" method="">
          Pilih tanggal: <input type="date" name="tgl">
          <input type="submit" value="Go">
        </form>
        ';
        $date = date('Y-m-d');
      	$perpage = 10;
        if(isset($_GET['tgl']) && $_GET['tgl'] !='') {
          $date = $_GET['tgl'];
        }

        $exclude_taskid = str_replace(",","','", $this->settings->get('jkn_mobile.exclude_taskid'));
        $checkAntrian = $this->db('mlite_antrian_referensi')->where('tanggal_periksa', $date)->where('status_kirim','Belum')->limit(10)->toArray();
        if (!$checkAntrian) {
          echo 'Disini '.'<br>';
          $exclude_taskid = str_replace(",","','", $this->settings->get('jkn_mobile.exclude_taskid'));
          $sqlquere = "SELECT pasien.no_peserta,pasien.no_rkm_medis,reg_periksa.no_reg,reg_periksa.no_rawat,reg_periksa.tgl_registrasi,reg_periksa.kd_poli,reg_periksa.kd_pj
            FROM reg_periksa INNER JOIN pasien ON reg_periksa.no_rkm_medis=pasien.no_rkm_medis INNER JOIN dokter ON reg_periksa.kd_dokter=dokter.kd_dokter INNER JOIN poliklinik ON reg_periksa.kd_poli=poliklinik.kd_poli WHERE reg_periksa.tgl_registrasi='$date' AND reg_periksa.kd_poli NOT IN ('$exclude_taskid') AND reg_periksa.stts NOT IN ('Batal','Dirujuk','Dirawat')
            ORDER BY concat(reg_periksa.tgl_registrasi,' ',reg_periksa.jam_reg)";
          $query = $this->db()->pdo()->prepare($sqlquere);
          $query->execute();
          $query = $query->fetchAll(\PDO::FETCH_ASSOC);

          foreach ($query as $q) {
            echo 'Load Data '.$q['no_rkm_medis'].'<br>';
            $checkPJ = $this->db('mlite_antrian_referensi')->where('tanggal_periksa',  $date)->where('no_rkm_medis', $q['no_rkm_medis'])->oneArray();
            if(!$checkPJ) {
              $checkPJ = $this->db('mlite_antrian_referensi')->where('tanggal_periksa',  $date)->where('nomor_kartu', $q['no_peserta'])->oneArray();
            }
            if(!$checkPJ) {
              echo $q['no_rkm_medis'].' '.$q['no_peserta'].' Tidak Ada Data di mlite_antrian_ref di tanggal: '.$date.'<br>';
              $maping_poli_bpjs = $this->db('maping_poli_bpjs')->where('kd_poli_rs', $q['kd_poli'])->oneArray();
              $jenispasien = 'NON JKN';
              $jeniskunjungan = 3;
              if($q['kd_pj'] == $this->settings->get('jkn_mobile.kd_pj_bpjs')) {
                $jenispasien = 'JKN';
              }

              $nomorreferensi = convertNorawat($q['no_rawat']).''.$maping_poli_bpjs['kd_poli_bpjs'].''.$q['no_reg'];
              if($jenispasien == 'JKN') {
                $bridging_sep = $this->db('bridging_sep')->where('no_rawat', $q['no_rawat'])->oneArray();
                $nomorreferensi = $bridging_sep['no_rujukan'];
              }

              $kode_ppk  = $this->settings->get('settings.ppk_bpjs');
              $kodebooking = $kode_ppk.convertNorawat($q['no_rawat']).''.$maping_poli_bpjs['kd_poli_bpjs'].''.$q['no_reg'];

              echo 'code...<br>';

              $this->db('mlite_antrian_referensi')->save([
                'tanggal_periksa' => $q['tgl_registrasi'],
                'no_rkm_medis' => $q['no_rkm_medis'],
                'nomor_kartu' => $q['no_peserta'],
                'nomor_referensi' => $nomorreferensi,
                'kodebooking' => $kodebooking,
                'jenis_kunjungan' => $jeniskunjungan,
                'status_kirim' => 'Belum'
              ]);
              echo $nomorreferensi.' <br>';

              $checkRM = $this->db('mlite_antrian_referensi')->where('no_rkm_medis', $q['no_rkm_medis'])->where('kodebooking', $kodebooking)->oneArray();
              if ($checkRM) {
                echo 'Berhasil Simpan '.$q['no_rkm_medis'].'<br>';
              }
            }
            echo '-----------------------------------------------------<br>';

          }
        }

        echo 'Menjalankan WS tambah antrian<br>';
        echo '-------------------------------------<br>';

        $tentukan_hari=date('D',strtotime($date));
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
        if($checkAntrian){
          foreach ($checkAntrian as $q) {
            $reg_periksa = $this->db('reg_periksa')->where('tgl_registrasi', $date)->where('no_rkm_medis', $q['no_rkm_medis'])->oneArray();
            $pasien = $this->db('pasien')->where('no_rkm_medis', $q['no_rkm_medis'])->oneArray();
            $maping_dokter_dpjpvclaim = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $reg_periksa['kd_dokter'])->oneArray();
            $maping_poli_bpjs = $this->db('maping_poli_bpjs')->where('kd_poli_rs', $reg_periksa['kd_poli'])->oneArray();
            $jadwaldokter = $this->db('jadwal')->where('kd_dokter', $reg_periksa['kd_dokter'])->where('kd_poli', $reg_periksa['kd_poli'])->where('hari_kerja', $hari)->oneArray();
            $no_urut_reg = substr($reg_periksa['no_reg'], 0, 3);
            $minutes = $no_urut_reg * 10;
            $cek_kouta['jam_mulai'] = date('H:i:s',strtotime('+'.$minutes.' minutes',strtotime($jadwaldokter['jam_mulai'])));
            $jenispasien = 'NON JKN';
            if($reg_periksa['kd_pj'] == $this->settings->get('jkn_mobile.kd_pj_bpjs')) {
              $jenispasien = 'JKN';
            }
            $pasienbaru = '1';
            if($reg_periksa['stts_daftar'] == 'Lama') {
              $pasienbaru = '0';
            }

            $referensi = $this->db('mlite_antrian_referensi')->where('tanggal_periksa', $date)->where('nomor_kartu', $q['no_peserta'])->oneArray();
            if($jenispasien == 'NON JKN') {
              $referensi = $this->db('mlite_antrian_referensi')->where('tanggal_periksa', $date)->where('nomor_kartu', $q['no_rkm_medis'])->oneArray();
            }

            $nomorkartu = $q['nomor_kartu'];
            if($jenispasien == 'NON JKN') {
              $nomorkartu = '';
            }

            $nik = $pasien['no_ktp'];
            if($jenispasien == 'NON JKN') {
              $nik = '';
            }

            $nohp = $pasien['no_tlp'];
            if(empty($pasien['no_tlp'])) {
              $nohp = '0000000000';
            }
            if($jenispasien == 'NON JKN') {
              $nohp = '';
            }

            $kode_ppk  = $this->settings->get('settings.ppk_bpjs');
            $kodebooking = $kode_ppk.convertNorawat($q['no_rawat']).''.$reg_periksa['no_reg'];
            $nomorreferensi = '';

            $data = [
              'kodebooking' => $q['kodebooking'],
              'jenispasien' => $jenispasien,
              'nomorkartu' => $nomorkartu,
              'nik' => $nik,
              'nohp' => $nohp,
              'kodepoli' => $maping_poli_bpjs['kd_poli_bpjs'],
              'namapoli' => $maping_poli_bpjs['nm_poli_bpjs'],
              'pasienbaru' => $pasienbaru,
              'norm' => $q['no_rkm_medis'],
              'tanggalperiksa' => $reg_periksa['tgl_registrasi'],
              'kodedokter' => $maping_dokter_dpjpvclaim['kd_dokter_bpjs'],
              'namadokter' => $maping_dokter_dpjpvclaim['nm_dokter_bpjs'],
              'jampraktek' => substr($jadwaldokter['jam_mulai'],0,5).'-'.substr($jadwaldokter['jam_selesai'],0,5),
              'jeniskunjungan' => $q['jenis_kunjungan'],
              'nomorreferensi' => $q['nomor_referensi'],
              'nomorantrean' => $maping_poli_bpjs['kd_poli_bpjs'].'-'.$reg_periksa['no_reg'],
              'angkaantrean' => $reg_periksa['no_reg'],
              'estimasidilayani' => strtotime($reg_periksa['tgl_registrasi'].' '.$cek_kouta['jam_mulai']) * 1000,
              'sisakuotajkn' => $jadwaldokter['kuota']-ltrim($reg_periksa['no_reg'],'0'),
              'kuotajkn' => intval($jadwaldokter['kuota']),
              'sisakuotanonjkn' => $jadwaldokter['kuota']-ltrim($reg_periksa['no_reg'],'0'),
              'kuotanonjkn' => intval($jadwaldokter['kuota']),
              'keterangan' => 'Peserta harap 30 menit lebih awal guna pencatatan administrasi.'
            ];
            echo 'Request:<br>';
            echo "<pre>".print_r($data,true)."</pre>";
            $data = json_encode($data);
            $url = $this->bpjsurl.'antrean/add';
            $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, NULL);
            $data = json_decode($output, true);
            echo 'Response:<br>';
            echo json_encode($data)." ";
            echo $data['metadata']['code'];
            if($data['metadata']['code'] == 200 || $data['metadata']['code'] == 208){

              $resep_obat = $this->db('resep_obat')
              ->where('tgl_peresepan', $q['tgl_registrasi'])
              ->where('no_rawat', $q['no_rawat'])
              ->where('status', 'ralan')
              ->oneArray();

              $jenisresep = 'Non Racikan';

              $resep_dokter_racikan = $this->db('resep_dokter_racikan')->where('no_resep', $resep_obat['no_resep'])->oneArray();
              if(!empty($resep_dokter_racikan)) {
                $jenisresep = 'Racikan';
              }

              $mlite_antrian_loket = $this->db('mlite_antrian_loket')
                ->where('type', 'Apotek')
                ->where('postdate', $q['tgl_registrasi'])
                ->where('no_rkm_medis', $q['no_rkm_medis'])
                ->where('end_time', '<>', '00:00:00')
                ->oneArray();

              $data_antrian_farmasi = [
                'kodebooking' => $q['kodebooking'],
                'jenisresep' => $jenisresep,
                'nomorantrean' => $mlite_antrian_loket['noantrian'],
                'keterangan' => 'Resep dibuat secara elektronik di poli.'
              ];

              echo 'Request:<br>';
              echo "<pre>".print_r($data_antrian_farmasi,true)."</pre>";
              $data_antrian_farmasi = json_encode($data_antrian_farmasi);
              $url = $this->bpjsurl.'antrean/farmasi/add';
              $output_farmasi = BpjsService::post($url, $data_antrian_farmasi, $this->consid, $this->secretkey, $this->user_key, NULL);
              $data_farmasi = json_decode($output_farmasi, true);
              echo 'Response:<br>';
              if(isset($data_farmasi['metadata']['code']) == 200 || isset($data_farmasi['metadata']['code']) == 208){
                echo 'Sukses kirim antrian farmasi';
              } else {
                echo 'Gagal kirim antrian farmasi. '.$data_farmasi['metadata']['code'].': '.$data_farmasi['metadata']['message'];
              }

              if($jenispasien == 'JKN') {
                if(!$this->db('mlite_antrian_referensi')->where('tanggal_periksa', $reg_periksa['tgl_registrasi'])->where('nomor_kartu', $nomorkartu)->oneArray()) {
                $this->db('mlite_antrian_referensi')->save([
                  'tanggal_periksa' => $q['tgl_registrasi'],
                  'nomor_kartu' => $q['no_peserta'],
                  'nomor_referensi' => $nomorreferensi,
                  'kodebooking' => $kodebooking,
                  'jenis_kunjungan' => $jeniskunjungan,
                  'status_kirim' => 'Sudah',
                  'keterangan' => $data['metadata']['message']
                ]);
                } else {
                  $this->db('mlite_antrian_referensi')->where('nomor_referensi', $q['nomor_referensi'])->save([
                  'status_kirim' => 'Sudah',
                  'keterangan' => $data['metadata']['message']
                  ]);
                }
              }
              if($jenispasien == 'NON JKN') {
                if(!$this->db('mlite_antrian_referensi')->where('tanggal_periksa', $reg_periksa['tgl_registrasi'])->where('no_rkm_medis', $q['no_rkm_medis'])->oneArray()) {
                $this->db('mlite_antrian_referensi')->save([
                  'tanggal_periksa' => $q['tgl_registrasi'],
                  'nomor_kartu' => $q['no_rkm_medis'],
                  'nomor_referensi' => convertNorawat($q['no_rawat']).''.$maping_poli_bpjs['kd_poli_bpjs'].''.$reg_periksa['no_reg'],
                  'kodebooking' => $kodebooking,
                  'jenis_kunjungan' => $jeniskunjungan,
                  'status_kirim' => 'Sudah',
                  'keterangan' => $data['metadata']['message']
                ]);
                } else {
                  $this->db('mlite_antrian_referensi')->where('nomor_referensi', $q['nomor_referensi'])->save([
                    'status_kirim' => 'Sudah',
                    'keterangan' => $data['metadata']['message']
                  ]);
                }
              }
            }
            if($data['metadata']['code'] == 201){
              if($jenispasien == 'JKN') {
                if(!$this->db('mlite_antrian_referensi')->where('tanggal_periksa', $reg_periksa['tgl_registrasi'])->where('nomor_kartu', $nomorkartu)->oneArray()) {
                  $this->db('mlite_antrian_referensi')->save([
                    'tanggal_periksa' => $q['tgl_registrasi'],
                    'nomor_kartu' => $q['no_peserta'],
                    'nomor_referensi' => $nomorreferensi,
                    'kodebooking' => $kodebooking,
                    'jenis_kunjungan' => $jeniskunjungan,
                    'status_kirim' => 'Gagal',
                    'keterangan' => $data['metadata']['message']
                  ]);
                } else {
                  $this->db('mlite_antrian_referensi')->where('nomor_referensi', $q['nomor_referensi'])->save([
                    'status_kirim' => 'Gagal',
                    'keterangan' => $data['metadata']['message']
                  ]);
                }
              }
              if($jenispasien == 'NON JKN') {
                if(!$this->db('mlite_antrian_referensi')->where('tanggal_periksa', $reg_periksa['tgl_registrasi'])->where('no_rkm_medis', $q['no_rkm_medis'])->oneArray()) {
                  $this->db('mlite_antrian_referensi')->save([
                    'tanggal_periksa' => $q['tgl_registrasi'],
                    'nomor_kartu' => $q['no_rkm_medis'],
                    'nomor_referensi' => $q['nomor_referensi'],
                    'kodebooking' => $kodebooking,
                    'jenis_kunjungan' => $jeniskunjungan,
                    'status_kirim' => 'Gagal',
                    'keterangan' => $data['metadata']['message']
                  ]);
                } else {
                  $this->db('mlite_antrian_referensi')->where('nomor_referensi', $q['nomor_referensi'])->save([
                    'status_kirim' => 'Gagal',
                    'keterangan' => $data['metadata']['message']
                  ]);
                }
              }
            }
            echo '<br>-------------------------------------<br><br>';
          }
        }
        echo '
        </body>
        </html>
        ';
        exit();
    }

    public function _getAntreanBatal()
    {
        echo '
        <head>
         <meta http-equiv="refresh" content="30">
        </head>
        <title>Batal Antrian BPJS</title>
        </head>
        <body>
        <form action="" method="">
          Pilih tanggal: <input type="date" name="tgl">
          <input type="submit" value="Go">
        </form>
        ';

        $date = date('Y-m-d');
        if(isset($_GET['tgl']) && $_GET['tgl'] !='') {
          $date = $_GET['tgl'];
        }

        $query = $this->db('mlite_antrian_referensi_batal')
          ->where('tanggal_batal', $date)
          ->toArray();

        echo 'Menjalankan WS batal antrian Mobile JKN BPJS<br>';
        echo '-------------------------------------<br>';

        foreach ($query as $q) {
            $data = [
                'kodebooking' => $q['nomor_referensi'],
                'keterangan' => $q['keterangan']
            ];
            $data = json_encode($data);
            echo 'Request:<br>';
            echo $data;

            echo '<br>';
            $url = $this->bpjsurl.'antrean/batal';
            $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, NULL);
            $json = json_decode($output, true);
            echo 'Response:<br>';
            echo json_encode($json);

            echo '<br>-------------------------------------<br><br>';
        }

        echo '
        </body>
        </html>
        ';

        exit();
    }

    public function _getAntreanUpdateWaktu()
    {
        echo '
        <head>
         <meta http-equiv="refresh" content="30">
        </head>
        <title>Update Waktu Antrian BPJS</title>
        </head>
        <body>
        <form action="" method="">
          Pilih tanggal: <input type="date" name="tgl">
          <input type="submit" value="Go">
        </form>
        ';

        $date = date('Y-m-d');
        if(isset($_GET['tgl']) && $_GET['tgl'] !='') {
          $date = $_GET['tgl'];
        }

      	$perpage = 5;

        $query = $this->db('mlite_antrian_referensi')
          ->where('tanggal_periksa', $date)
          ->where('status_kirim', 'Sudah')
          ->limit($perpage)
          ->toArray();

        echo 'Menjalankan WS taskid (1) mulai tunggu admisi<br>';
        echo '-------------------------------------<br>';

        foreach ($query as $q) {
            if(!$this->db('mlite_antrian_referensi_taskid')->where('tanggal_periksa', $date)->where('nomor_referensi', $q['nomor_referensi'])->where('taskid', 1)->oneArray()) {
                $pasien = $this->db('pasien')->where('no_peserta', $q['nomor_kartu'])->oneArray();
                $q['no_rkm_medis'] = $q['nomor_kartu'];
                if($pasien) {
                  $q['no_rkm_medis'] = $pasien['no_rkm_medis'];
                }
                $reg_periksa = $this->db('reg_periksa')->where('tgl_registrasi', $date)->where('no_rkm_medis', $q['no_rkm_medis'])->oneArray();
                $mlite_antrian_loket = $this->db('mlite_antrian_loket')->where('no_rkm_medis', $reg_periksa['no_rkm_medis'])->where('postdate', $date)->oneArray();
                if($mlite_antrian_loket){
                    date_default_timezone_set($this->settings->get('settings.timezone'));
                    $data = [
                        'kodebooking' => $q['kodebooking'],
                        'taskid' => 1,
                        'waktu' => strtotime($mlite_antrian_loket['postdate'].' '.$mlite_antrian_loket['start_time']) * 1000,
                        'jenisresep' => ''
                    ];
                    $data = json_encode($data);
                    echo 'Request:<br>';
                    echo $data;

                    echo '<br>';
                    $url = $this->bpjsurl.'antrean/updatewaktu';
                    $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, NULL);
                    $json = json_decode($output, true);
                    echo 'Response:<br>';
                    echo json_encode($json);
                    if(isset($json['metadata']['code']) == 200){
                      $this->db('mlite_antrian_referensi_taskid')
                      ->save([
                        'tanggal_periksa' => $date,
                        'nomor_referensi' => $q['nomor_referensi'],
                        'taskid' => 1,
                        'waktu' => strtotime($mlite_antrian_loket['postdate'].' '.$mlite_antrian_loket['start_time']) * 1000,
                        'status' => 'Sudah',
                        'keterangan' => 'Mulai tunggu admisi.'
                      ]);
                    }
                    echo '<br>-------------------------------------<br><br>';
                }
            }
        }

        echo 'Menjalankan WS taskid (2) mulai pelayanan admisi<br>';
        echo '-------------------------------------<br>';

        foreach ($query as $q) {
            if(!$this->db('mlite_antrian_referensi_taskid')->where('tanggal_periksa', $date)->where('nomor_referensi', $q['nomor_referensi'])->where('taskid', 2)->oneArray()) {
                $pasien = $this->db('pasien')->where('no_peserta', $q['nomor_kartu'])->oneArray();
                $q['no_rkm_medis'] = $q['nomor_kartu'];
                if($pasien) {
                  $q['no_rkm_medis'] = $pasien['no_rkm_medis'];
                }
                $reg_periksa = $this->db('reg_periksa')->where('tgl_registrasi', $date)->where('no_rkm_medis', $q['no_rkm_medis'])->oneArray();
                $mlite_antrian_loket = $this->db('mlite_antrian_loket')->where('no_rkm_medis', $reg_periksa['no_rkm_medis'])->where('postdate', $date)->oneArray();
                if($mlite_antrian_loket){
                    date_default_timezone_set($this->settings->get('settings.timezone'));
                    $data = [
                        'kodebooking' => $q['kodebooking'],
                        'taskid' => 2,
                        'waktu' => strtotime($mlite_antrian_loket['end_time']) * 1000,
                        'jenisresep' => ''
                    ];
                    $data = json_encode($data);
                    echo 'Request:<br>';
                    echo $data;

                    echo '<br>';
                    $url = $this->bpjsurl.'antrean/updatewaktu';
                    $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, NULL);
                    $json = json_decode($output, true);
                    echo 'Response:<br>';
                    echo json_encode($json);
                    if(isset($json['metadata']['code']) == 200){
                      $this->db('mlite_antrian_referensi_taskid')
                      ->save([
                        'tanggal_periksa' => $date,
                        'nomor_referensi' => $q['nomor_referensi'],
                        'taskid' => 2,
                        'waktu' => strtotime($mlite_antrian_loket['postdate'].' '.$mlite_antrian_loket['end_time']) * 1000,
                        'status' => 'Sudah',
                        'keterangan' => 'Mulai pelayanan admisi.'
                      ]);
                    }
                    echo '<br>-------------------------------------<br><br>';
                }
            }
        }

        echo 'Menjalankan WS taskid (3) mulai tunggu poli<br>';
        echo '-------------------------------------<br>';

        foreach ($query as $q) {
            if(!$this->db('mlite_antrian_referensi_taskid')->where('tanggal_periksa', $date)->where('nomor_referensi', $q['nomor_referensi'])->where('taskid', 3)->oneArray()) {
                $pasien = $this->db('pasien')->where('no_peserta', $q['nomor_kartu'])->oneArray();
                $q['no_rkm_medis'] = $q['nomor_kartu'];
                if($pasien) {
                  $q['no_rkm_medis'] = $pasien['no_rkm_medis'];
                }
                $reg_periksa = $this->db('reg_periksa')->where('tgl_registrasi', $date)->where('no_rkm_medis', $q['no_rkm_medis'])->oneArray();
                $mutasi_berkas = $this->db('mutasi_berkas')->select('dikirim')->where('no_rawat', $reg_periksa['no_rawat'])->where('dikirim', '<>', '0000-00-00 00:00:00')->oneArray();
                if($mutasi_berkas){
                    date_default_timezone_set($this->settings->get('settings.timezone'));
                    $data = [
                        'kodebooking' => $q['kodebooking'],
                        'taskid' => 3,
                        'waktu' => strtotime($mutasi_berkas['dikirim']) * 1000,
                        'jenisresep' => ''
                    ];
                    $data = json_encode($data);
                    echo 'Request:<br>';
                    echo $data;

                    echo '<br>';
                    $url = $this->bpjsurl.'antrean/updatewaktu';
                    $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, NULL);
                    $json = json_decode($output, true);
                    echo 'Response:<br>';
                    echo json_encode($json);
                    if(isset($json['metadata']['code']) == 200){
                      $this->db('mlite_antrian_referensi_taskid')
                      ->save([
                        'tanggal_periksa' => $date,
                        'nomor_referensi' => $q['nomor_referensi'],
                        'taskid' => 3,
                        'waktu' => strtotime($mutasi_berkas['dikirim']) * 1000,
                        'status' => 'Sudah',
                        'keterangan' => 'Selesai pelayanan admisi atau mulai tunggu poli.'
                      ]);
                    }
                    echo '<br>-------------------------------------<br><br>';
                }
            }
        }

        echo 'Menjalankan WS taskid (4) mulai pelayanan poli<br>';
        echo '-------------------------------------<br>';

        foreach ($query as $q) {
            if(!$this->db('mlite_antrian_referensi_taskid')->where('tanggal_periksa', $date)->where('nomor_referensi', $q['nomor_referensi'])->where('taskid', 4)->oneArray()) {
                $pasien = $this->db('pasien')->where('no_peserta', $q['nomor_kartu'])->oneArray();
                $q['no_rkm_medis'] = $q['nomor_kartu'];
                if($pasien) {
                  $q['no_rkm_medis'] = $pasien['no_rkm_medis'];
                }
                $reg_periksa = $this->db('reg_periksa')->where('tgl_registrasi', $date)->where('no_rkm_medis', $q['no_rkm_medis'])->oneArray();
                $mutasi_berkas = $this->db('mutasi_berkas')->select('diterima')->where('no_rawat', $reg_periksa['no_rawat'])->where('diterima', '<>', '0000-00-00 00:00:00')->oneArray();
                if($mutasi_berkas){
                    date_default_timezone_set($this->settings->get('settings.timezone'));
                    $data = [
                        'kodebooking' => $q['kodebooking'],
                        'taskid' => 4,
                        'waktu' => strtotime($mutasi_berkas['diterima']) * 1000,
                        'jenisresep' => ''
                    ];
                    $data = json_encode($data);
                    echo 'Request:<br>';
                    echo $data;

                    echo '<br>';
                    $url = $this->bpjsurl.'antrean/updatewaktu';
                    $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, NULL);
                    $json = json_decode($output, true);
                    echo 'Response:<br>';
                    echo json_encode($json);
                    if(isset($json['metadata']['code']) == 200){
                      $this->db('mlite_antrian_referensi_taskid')
                      ->save([
                        'tanggal_periksa' => $date,
                        'nomor_referensi' => $q['nomor_referensi'],
                        'taskid' => 4,
                        'waktu' => strtotime($mutasi_berkas['diterima']) * 1000,
                        'status' => 'Sudah',
                        'keterangan' => 'Mulai pelayanan poli.'
                      ]);
                    }
                    echo '<br>-------------------------------------<br><br>';
                }
            }
        }

        echo 'Menjalankan WS taskid (5) selesai pelayanan poli<br>';
        echo '-------------------------------------<br>';

        foreach ($query as $q) {
            if(!$this->db('mlite_antrian_referensi_taskid')->where('tanggal_periksa', $date)->where('nomor_referensi', $q['nomor_referensi'])->where('taskid', 5)->oneArray()) {
                $pasien = $this->db('pasien')->where('no_peserta', $q['nomor_kartu'])->oneArray();
                $q['no_rkm_medis'] = $q['nomor_kartu'];
                if($pasien) {
                  $q['no_rkm_medis'] = $pasien['no_rkm_medis'];
                }
                $reg_periksa = $this->db('reg_periksa')->where('tgl_registrasi', $date)->where('no_rkm_medis', $q['no_rkm_medis'])->oneArray();
                $pemeriksaan_ralan = $this->db('pemeriksaan_ralan')->select(['datajam' => 'concat(tgl_perawatan," ",jam_rawat)'])->where('no_rawat', $reg_periksa['no_rawat'])->oneArray();
                if($pemeriksaan_ralan){
                    date_default_timezone_set($this->settings->get('settings.timezone'));
                    $data = [
                        'kodebooking' => $q['kodebooking'],
                        'taskid' => 5,
                        'waktu' => strtotime($pemeriksaan_ralan['datajam']) * 1000,
                        'jenisresep' => 'Tidak ada'
                    ];
                    $data = json_encode($data);
                    echo 'Request:<br>';
                    echo $data;

                    echo '<br>';
                    $url = $this->bpjsurl.'antrean/updatewaktu';
                    $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, NULL);
                    $json = json_decode($output, true);
                    echo 'Response:<br>';
                    echo json_encode($json);
                    if(isset($json['metadata']['code']) == 200){
                      $this->db('mlite_antrian_referensi_taskid')
                      ->save([
                        'tanggal_periksa' => $date,
                        'nomor_referensi' => $q['nomor_referensi'],
                        'taskid' => 5,
                        'waktu' => strtotime($pemeriksaan_ralan['datajam']) * 1000,
                        'status' => 'Sudah',
                        'keterangan' => 'Selesai pelayanan poli.'
                      ]);
                    }
                    echo '<br>-------------------------------------<br><br>';
                }
            }
        }

        echo 'Menjalankan WS taskid (6) permintaan resep poli<br>';
        echo '-------------------------------------<br>';

        foreach ($query as $q) {
            if(!$this->db('mlite_antrian_referensi_taskid')->where('tanggal_periksa', $date)->where('nomor_referensi', $q['nomor_referensi'])->where('taskid', 6)->oneArray()) {
                $pasien = $this->db('pasien')->where('no_peserta', $q['nomor_kartu'])->oneArray();
                $q['no_rkm_medis'] = $q['nomor_kartu'];
                if($pasien) {
                  $q['no_rkm_medis'] = $pasien['no_rkm_medis'];
                }
                $reg_periksa = $this->db('reg_periksa')->where('tgl_registrasi', $date)->where('no_rkm_medis', $q['no_rkm_medis'])->oneArray();
                $resep_obat = $this->db('resep_obat')->select(['datajam' => 'concat(tgl_peresepan," ",jam_peresepan)', 'no_resep' => 'no_resep'])->where('no_rawat', $reg_periksa['no_rawat'])->oneArray();
                $jenisresep = 'Non racikan';
                $resep_dokter_racikan = $this->db('resep_dokter_racikan')->where('no_resep', $resep_obat['no_resep'])->oneArray();
                if(!empty($resep_dokter_racikan)) {
                  $jenisresep = 'Racikan';
                }

                if($resep_obat){
                    date_default_timezone_set($this->settings->get('settings.timezone'));
                    $data = [
                        'kodebooking' => $q['kodebooking'],
                        'taskid' => 6,
                        'waktu' => strtotime($resep_obat['datajam']) * 1000,
                        'jenisresep' => $jenisresep
                    ];
                    $data = json_encode($data);
                    echo 'Request:<br>';
                    echo $data;

                    echo '<br>';
                    $url = $this->bpjsurl.'antrean/updatewaktu';
                    $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, NULL);
                    $json = json_decode($output, true);
                    echo 'Response:<br>';
                    echo json_encode($json);
                    if(isset($json['metadata']['code']) == 200){
                      $this->db('mlite_antrian_referensi_taskid')
                      ->save([
                        'tanggal_periksa' => $date,
                        'nomor_referensi' => $q['nomor_referensi'],
                        'taskid' => 6,
                        'waktu' => strtotime($resep_obat['datajam']) * 1000,
                        'status' => 'Sudah',
                        'keterangan' => 'Mulai pelayanan apotek. '
                      ]);
                    }
                    echo '<br>-------------------------------------<br><br>';
                }
            }
        }

        echo 'Menjalankan WS taskid (7) validasi resep poli<br>';
        echo '-------------------------------------<br>';

        foreach ($query as $q) {
            if(!$this->db('mlite_antrian_referensi_taskid')->where('tanggal_periksa', $date)->where('nomor_referensi', $q['nomor_referensi'])->where('taskid', 7)->oneArray()) {
                $pasien = $this->db('pasien')->where('no_peserta', $q['nomor_kartu'])->oneArray();
                $q['no_rkm_medis'] = $q['nomor_kartu'];
                if($pasien) {
                  $q['no_rkm_medis'] = $pasien['no_rkm_medis'];
                }
                $reg_periksa = $this->db('reg_periksa')->where('tgl_registrasi', $date)->where('no_rkm_medis', $q['no_rkm_medis'])->oneArray();
                $resep_obat = $this->db('resep_obat')->select(['datajam' => 'concat(tgl_perawatan," ",jam)', 'no_resep' => 'no_resep'])->where('no_rawat', $reg_periksa['no_rawat'])->where('concat(tgl_perawatan," ",jam)', '<>', 'concat(tgl_peresepan," ",jam_peresepan)')->oneArray();
                $jenisresep = 'Non racikan';
                $resep_dokter_racikan = $this->db('resep_dokter_racikan')->where('no_resep', $resep_obat['no_resep'])->oneArray();
                if(!empty($resep_dokter_racikan)) {
                  $jenisresep = 'Racikan';
                }

                if($resep_obat){
                    date_default_timezone_set($this->settings->get('settings.timezone'));
                    $data = [
                        'kodebooking' => $q['kodebooking'],
                        'taskid' => 7,
                        'waktu' => strtotime($resep_obat['datajam']) * 1000,
                        'jenisresep' => ''
                    ];
                    $data = json_encode($data);
                    echo 'Request:<br>';
                    echo $data;

                    echo '<br>';
                    $url = $this->bpjsurl.'antrean/updatewaktu';
                    $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, NULL);
                    $json = json_decode($output, true);
                    echo 'Response:<br>';
                    echo json_encode($json);
                    if(isset($json['metadata']['code']) == 200){
                      $this->db('mlite_antrian_referensi_taskid')
                      ->save([
                        'tanggal_periksa' => $date,
                        'nomor_referensi' => $q['nomor_referensi'],
                        'taskid' => 7,
                        'waktu' => strtotime($resep_obat['datajam']) * 1000,
                        'status' => 'Sudah',
                        'keterangan' => 'Selesai pelayanan apotek.'
                      ]);
                    }
                    echo '<br>-------------------------------------<br><br>';
                }
            }
        }

        echo 'Menjalankan WS taskid (99) batal pelayanan poli<br>';
        echo '-------------------------------------<br>';

        foreach ($query as $q) {
            if(!$this->db('mlite_antrian_referensi_taskid')->where('tanggal_periksa', $date)->where('nomor_referensi', $q['nomor_referensi'])->where('taskid', 99)->oneArray()) {
                $pasien = $this->db('pasien')->where('no_peserta', $q['nomor_kartu'])->oneArray();
                $q['no_rkm_medis'] = $q['nomor_kartu'];
                if($pasien) {
                  $q['no_rkm_medis'] = $pasien['no_rkm_medis'];
                }
                $reg_periksa = $this->db('reg_periksa')->where('tgl_registrasi', $date)->where('no_rkm_medis', $q['no_rkm_medis'])->where('stts', 'Batal')->oneArray();
                if($reg_periksa){
                    $data = [
                        'kodebooking' => $q['kodebooking'],
                        'taskid' => 99,
                        'waktu' => strtotime(date('Y-m-d H:i:s')) * 1000
                    ];
                    $data = json_encode($data);
                    echo 'Request:<br>';
                    echo $data;

                    echo '<br>';
                    $url = $this->bpjsurl.'antrean/updatewaktu';
                    $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, NULL);
                    $json = json_decode($output, true);
                    echo 'Response:<br>';
                    echo json_encode($json);
                    if(isset($json['metadata']['code']) == 200){
                      $this->db('mlite_antrian_referensi_taskid')
                      ->save([
                        'tanggal_periksa' => $date,
                        'nomor_referensi' => $q['nomor_referensi'],
                        'taskid' => 99,
                        'waktu' => strtotime(date('Y-m-d H:i:s')) * 1000,
                        'status' => 'Sudah',
                        'keterangan' => 'Batal antrian.'
                      ]);
                    }
                    echo '<br>-------------------------------------<br><br>';
                }
            }
        }

        echo '
        </body>
        </html>
        ';

        exit();
    }

    public function _getAntreanGetListTask()
    {
        $slug = parseURL();
        $data = [
            'kodebooking' => $slug['3']
        ];

        $data = json_encode($data);
        $url = $this->bpjsurl.'antrean/getlisttask';
        $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, NULL);
        $json = json_decode($output, true);
        echo json_encode($json);
        exit();
    }

    public function _getAntreanWaktuTunggu()
    {
        $slug = parseURL();
        $url = $this->bpjsurl.'dashboard/waktutunggu/bulan/'.$slug[3].'/tahun/'.$slug[4].'/waktu/'.$slug[5];
        $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, NULL);
        $json = json_decode($output, true);
        echo json_encode($json);
        exit();
    }

    public function _getAntreanWaktuTungguTanggal()
    {
        $slug = parseURL();
        $url = $this->bpjsurl.'dashboard/waktutunggu/tanggal/'.$slug[3].'/waktu/'.$slug[4];
        $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, NULL);
        $json = json_decode($output, true);
        echo json_encode($json);
        exit();
    }

    public function getAplicareManajemen()
    {
        exit();
    }

    public function getAplicare()
    {
        $slug = parseURL();
        echo $this->_resultBed($slug[2],isset_or($slug[3],''));
        //slug[2] => kelas BPJS , slug[3] => kode ruang / kode bangsal
        exit();
    }

    private function checkBed($kelas,$bangsal = ''){
        $bed = array();
        $sql = "SELECT bangsal.nm_bangsal ,kamar.kd_bangsal, COUNT(kamar.kd_kamar) as jml , SUM(IF(kamar.status = 'ISI',1,0)) as isi , SUM(IF(kamar.status = 'KOSONG',1,0)) as kosong  FROM kamar JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal WHERE kamar.statusdata = '1'";
        $sql .= " AND kamar.kelas = 'Kelas $kelas' AND kamar.kd_bangsal LIKE '%$bangsal%'";
        $sql .= " GROUP BY kamar.kd_bangsal ";
        $query = $this->db()->pdo()->prepare($sql);
        $query->execute();
        $bedlist = $query->fetchAll();
        foreach ($bedlist as $value) {
            switch ($kelas) {
                case '1':
                    $value['kelas'] = 'KL1';
                    break;
                case '2':
                    $value['kelas'] = 'KL2';
                    break;
                case '3':
                    $value['kelas'] = 'KL3';
                    break;
                case 'vip':
                    $value['kelas'] = 'VIP';
                    break;
                case 'icu':
                    $value['kelas'] = 'ICU';
                    break;
                case 'nicu':
                    $value['kelas'] = 'NIC';
                    break;
                case 'picu':
                    $value['kelas'] = 'PIC';
                    break;
                case 'hcu':
                    $value['kelas'] = 'HCU';
                    break;

                default:
                    # code...
                    break;
            }
            $bed[] = $value;
        }
        return $bed;
    }

    private function _resultBed($slug,$slug2 = '')
    {
        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
        $kode_ppk  = $this->settings->get('settings.ppk_bpjs');
        $BpjsApiUrl = parse_url($this->settings->get('settings.BpjsApiUrl'));
        $url = $BpjsApiUrl['scheme'].'://'.$BpjsApiUrl['host'];
        if ($slug == 'listkamar') {
            $url .= "/aplicaresws/rest/bed/read/".$kode_ppk."/1/100";
            $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
            echo $output;
        }
        if ($slug == 'delkamar') {
            $whatIWant = substr($slug2, strpos($slug2, "-") + 1);
            $first = strtok($slug2, '-');
            $url .= "/aplicaresws/rest/bed/delete/".$kode_ppk;
            $beds = array('kodekelas'=>$first,'koderuang'=>$whatIWant);
            $data = array(
                'kodekelas'=>$beds['kodekelas'],
                'koderuang'=>$beds['koderuang']
            );
            $postdata = json_encode($data);
            $output = BpjsService::postAplicare($url, $postdata, $this->consid, $this->secretkey, $this->user_key, $tStamp);
            echo $output;
            echo '<br>';
        }
        if ($slug == 'addkamar') {
            $whatIWant = substr($slug2, strpos($slug2, "-") + 1);
            $first = strtok($slug2, '-');
            $bed = $this->checkBed($first,$whatIWant);
            $url .= "/aplicaresws/rest/bed/create/".$kode_ppk;
            foreach ($bed as $value) {
                $data = array(
                    'kodekelas'=>$value['kelas'],
                    'koderuang'=>$value['kd_bangsal'],
                    'namaruang'=>$value['nm_bangsal'],
                    'kapasitas'=>$value['jml'],
                    'tersedia'=>$value['kosong'],
                    'tersediapria'=>"0",
                    'tersediawanita'=>"0",
                    'tersediapriawanita'=>$value['kosong']
                );
                $postdata = json_encode($data);
                $output = BpjsService::postAplicare($url, $postdata, $this->consid, $this->secretkey, $this->user_key, $tStamp);
                echo $output;
            }
        }
        if ($slug != 'listkamar') {
            $bed = $this->checkBed($slug);
            $url .= "/aplicaresws/rest/bed/update/".$kode_ppk;
            foreach ($bed as $value) {
                $data = array(
                    'kodekelas'=>$value['kelas'],
                    'koderuang'=>$value['kd_bangsal'],
                    'namaruang'=>$value['nm_bangsal'],
                    'kapasitas'=>$value['jml'],
                    'tersedia'=>$value['kosong'],
                    'tersediapria'=>"0",
                    'tersediawanita'=>"0",
                    'tersediapriawanita'=>$value['kosong']
                );
                $postdata = json_encode($data);
                $output = BpjsService::postAplicare($url, $postdata, $this->consid, $this->secretkey, $this->user_key, $tStamp);
                echo $output;
            }
        }
        exit();
    }

    private function _getToken()
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(['username' => $this->settings->get('jkn_mobile.x_username'), 'password' => $this->settings->get('jkn_mobile.x_password'), 'date' => strtotime(date('Y-m-d')) * 1000]);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'abC123!', true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        return $jwt;
    }

    private function _getErrors($error)
    {
        $errors = $error;
        return $errors;
    }

    private function _setUmur($tanggal)
    {
        list($cY, $cm, $cd) = explode('-', date('Y-m-d'));
        list($Y, $m, $d) = explode('-', date('Y-m-d', strtotime($tanggal)));
        $umur = $cY - $Y;
        return $umur;
    }

    private function is_jwt_valid($jwt, $secret = 'abC123!') {
        // split the jwt
        $tokenParts = explode('.', $jwt);
        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signature_provided = $tokenParts[2];

        // check the expiration time - note this will cause an error if there is no 'exp' claim in the jwt
        $expiration = json_decode($payload)->exp;
        $is_token_expired = ($expiration - time()) < 0;

        // build a signature based on the header and payload using the secret
        $base64_url_header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64_url_payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64_url_header . "." . $base64_url_payload, $secret , true);
        $base64_url_signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        // verify it matches the signature provided in the jwt
        $is_signature_valid = ($base64_url_signature === $signature_provided);

        if ($is_token_expired || !$is_signature_valid) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
}
