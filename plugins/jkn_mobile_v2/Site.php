<?php

namespace Plugins\JKN_Mobile_V2;

use Systems\SiteModule;
use Systems\Lib\BpjsService;

class Site extends SiteModule
{

    public function init()
    {
      $this->consid = $this->settings->get('jkn_mobile_v2.BpjsConsID');
      $this->secretkey = $this->settings->get('jkn_mobile_v2.BpjsSecretKey');
      $this->bpjsurl = $this->settings->get('jkn_mobile_v2.BpjsAntrianUrl');
    }

    public function routes()
    {
        $this->route('jknmobile_v2', 'getIndex');
        $this->route('jknmobile_v2/token', 'getToken');
        $this->route('jknmobile_v2/antrian/status', 'getStatusAntrian');
        $this->route('jknmobile_v2/antrian/sisa', 'getSisaAntrian');
        $this->route('jknmobile_v2/antrian/batal', 'getBatalAntrian');
        $this->route('jknmobile_v2/antrian/ambil', 'getAmbilAntrian');
        $this->route('jknmobile_v2/pasien/baru', 'getPasienBaru');
        $this->route('jknmobile_v2/pasien/checkin', 'getPasienCheckIn');
        $this->route('jknmobile_v2/operasi/rs', 'getOperasiRS');
        $this->route('jknmobile_v2/operasi/pasien', 'getOperasiPasien');
        $this->route('jknmobile_v2/jadwal/(:str)/(:str)', '_getJadwal');
    }

    public function getIndex()
    {
        $referensi_poli = $this->db('maping_poli_bpjs')->toArray();
        echo $this->draw('index.html', ['referensi_poli' => $referensi_poli]);
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
        if ($header['X-Username'] == $this->settings->get('jkn_mobile_v2.x_username') && $header['X-Password'] == $this->settings->get('jkn_mobile_v2.x_password')) {
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

        if ($header[$this->settings->get('jkn_mobile_v2.header_token')] == $this->_getToken() && $header['X-Username'] == $this->settings->get('jkn_mobile_v2.x_username')) {
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
            }else if(!$this->db('jadwal')
                ->join('maping_dokter_dpjpvclaim', 'maping_dokter_dpjpvclaim.kd_dokter=jadwal.kd_dokter')
                ->where('maping_dokter_dpjpvclaim.kd_dokter_bpjs', $decode['kodedokter'])
                ->where('hari_kerja', $hari)
                ->where('jam_mulai', strtok($decode['jampraktek'], '-').':00')
                ->where('jam_selesai', substr($decode['jampraktek'], strpos($decode['jampraktek'], "-") + 1).':00')
                ->oneArray()){
                $response = array(
                    'metadata' => array(
                        'message' => 'Jadwal Praktek Tidak Ditemukan '.$hari,
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
            }else if(date("Y-m-d")>$decode['tanggalperiksa']){
                $response = array(
                    'metadata' => array(
                        'message' => 'Tanggal Periksa Tidak Berlaku',
                        'code' => 201
                    )
                );
                http_response_code(201);
            }else{
                $response = array(
                    'response' => array(
                        'namapoli' => 'Gigi',
                        'namadokter' => 'drg. Faisol Basoro',
                        'totalantrean' => 15,
                        'sisaantrean' => 5,
                        'antreanpanggil' => 'A5',
                        'sisakuotajkn' => 15,
                        'kuotajkn' => 20,
                        'sisakuotanonjkn' => 15,
                        'kuotanonjkn' => 20,
                        'keterangan' => ''
                    ),
                    'metadata' => array(
                        'message' => 'Ok',
                        'code' => 200
                    )
                );
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

        if ($header[$this->settings->get('jkn_mobile_v2.header_token')] == $this->_getToken() && $header['X-Username'] == $this->settings->get('jkn_mobile_v2.x_username')) {
            if($decode['kodebooking'] == ''){
                $response = array(
                    'metadata' => array(
                        'message' => 'Kode booking tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            } else if(!$this->db('booking_registrasi')->where('no_reg', $decode['kodebooking'])->oneArray()) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Kode Booking Tidak Ditemukan',
                        'code' => 201
                    )
                );
                http_response_code(201);
            } else {
                $response = array(
                    'response' => array(
                        'nomorantrean' => 'A5',
                        'namapoli' => 'Gigi',
                        'namadokter' => 'drg. Faisol Basoro',
                        'sisaantrean' => 12,
                        'antreanpanggil' => 'A8',
                        'waktutunggu' => 9000,
                        'keterangan' => ''
                    ),
                    'metadata' => array(
                        'message' => 'Ok',
                        'code' => 200
                    )
                );
                http_response_code(200);
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

        if ($header[$this->settings->get('jkn_mobile_v2.header_token')] == $this->_getToken() && $header['X-Username'] == $this->settings->get('jkn_mobile_v2.x_username')) {
            if($decode['kodebooking'] == ''){
                $response = array(
                    'metadata' => array(
                        'message' => 'Kode booking tidak boleh kosong',
                        'code' => 201
                    )
                );
                http_response_code(201);
            } else {
                $response = array(
                    'metadata' => array(
                        'message' => 'Ok',
                        'code' => 200
                    )
                );
                http_response_code(200);
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

    public function getAmbilAntrian()
    {
        echo $this->_resultAmbilAntrian();
        exit();
    }

    private function _resultAmbilAntrian()
    {
        header("Content-Type: application/json");
        $header = apache_request_headers();
        $konten = trim(file_get_contents("php://input"));
        $decode = json_decode($konten, true);
        $response = array();

        if ($header[$this->settings->get('jkn_mobile_v2.header_token')] == $this->_getToken() && $header['X-Username'] == $this->settings->get('jkn_mobile_v2.x_username')) {

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

            $h1 = strtotime('+1 days' , strtotime(date('Y-m-d'))) ;
            $h1 = date('Y-m-d', $h1);
            $_h1 = date('d-m-Y', strtotime($h1));
            if($cek_rujukan > 0) {
              $h7 = strtotime('+90 days', strtotime($cek_rujukan['tglrujukan']));
            } else {
              $h7 = strtotime('+7 days', strtotime(date('Y-m-d'))) ;
            }
            $h7 = date('Y-m-d', $h7);
            $_h7 = date('d-m-Y', strtotime($h7));

            $data_pasien = $this->db('pasien')->where('no_peserta', $decode['nomorkartu'])->oneArray();
            $poli = $this->db('maping_poli_bpjs')->where('kd_poli_bpjs', $decode['kodepoli'])->oneArray();
            $cek_kouta = $this->db()->pdo()->prepare("SELECT jadwal.kuota - (SELECT COUNT(booking_registrasi.tanggal_periksa) FROM booking_registrasi WHERE booking_registrasi.tanggal_periksa='$decode[tanggalperiksa]' AND booking_registrasi.kd_dokter=jadwal.kd_dokter) as sisa_kouta, jadwal.kd_dokter, jadwal.kd_poli, jadwal.jam_mulai as jam_mulai, poliklinik.nm_poli, dokter.nm_dokter FROM jadwal INNER JOIN maping_poli_bpjs ON maping_poli_bpjs.kd_poli_rs=jadwal.kd_poli INNER JOIN poliklinik ON poliklinik.kd_poli=jadwal.kd_poli INNER JOIN dokter ON dokter.kd_dokter=jadwal.kd_dokter WHERE jadwal.hari_kerja='$hari' AND maping_poli_bpjs.kd_poli_bpjs='$decode[kodepoli]' GROUP BY jadwal.kd_dokter HAVING sisa_kouta > 0 ORDER BY sisa_kouta DESC LIMIT 1");
            $cek_kouta->execute();
            $cek_kouta = $cek_kouta->fetch();

            $cek_referensi = $this->db('mlite_antrian_referensi')->where('nomor_referensi', $decode['nomorreferensi'])->oneArray();
            $cek_referensi_noka = $this->db('mlite_antrian_referensi')->where('nomor_kartu', $decode['nomorkartu'])->where('tanggal_periksa', $decode['tanggalperiksa'])->oneArray();

            if($cek_referensi > 0) {
               $errors[] = 'Anda sudah terdaftar dalam antrian menggunakan nomor rujukan yang sama ditanggal '.$decode['tanggalperiksa'];
            }
            if($cek_referensi_noka > 0) {
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
            if (!empty($decode['tanggalperiksa']) && !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$decode['tanggalperiksa'])) {
               $errors[] = 'Format tanggal periksa tidak sesuai';
            }
            if(!empty($decode['tanggalperiksa']) && $decode['tanggalperiksa'] < $h1 || $decode['tanggalperiksa'] > $h7) {
               $errors[] = 'Tanggal periksa bisa dilakukan tanggal '.$_h1.' hingga tanggal '.$_h7;
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
            if(!empty($decode['kodedokter']) && $poli == 0) {
               $errors[] = 'Kode dokter tidak ditemukan';
            }
            if(empty($decode['nomorreferensi'])) {
               $errors[] = 'Nomor rujukan kosong atau tidak ditemukan';
            }
            if(empty($decode['nomorreferensi'])) {
               $errors[] = 'Nomor rujukan kosong atau tidak ditemukan';
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

                if ($cek_kouta['sisa_kouta'] > 0) {
                    if(!$data_pasien) {

                        $date = date('Y-m-d');
                        $url = $this->settings->get('settings.BpjsApiUrl').'Peserta/nokartu/'.$decode['nomorkartu'].'/tglSEP/'.$date;
                        $consid = $this->settings->get('settings.BpjsConsID');
                        $secretkey = $this->settings->get('settings.BpjsSecretKey');
                        $output = BpjsService::get($url, NULL, $consid, $secretkey);
                        $output = json_decode($output, true);

                        $_POST['no_rkm_medis'] = $this->core->setNoRM();
                        $_POST['nm_pasien'] = $output['response']['peserta']['nama'];
                        $_POST['no_ktp'] = $output['response']['peserta']['nik'];
                        $_POST['jk'] = $output['response']['peserta']['sex'];
                        $_POST['tmp_lahir'] = '-';
                        $_POST['tgl_lahir'] = $output['response']['peserta']['tglLahir'];
                        $_POST['nm_ibu'] = '-';
                        $_POST['alamat'] = '-';
                        $_POST['gol_darah'] = '-';
                        $_POST['pekerjaan'] = $output['response']['peserta']['jenisPeserta']['keterangan'];
                        $_POST['stts_nikah'] = 'JOMBLO';
                        $_POST['agama'] = '-';
                        $_POST['tgl_daftar'] = $date;
                        $_POST['no_tlp'] = $output['response']['peserta']['mr']['noTelepon'];
                        $_POST['umur'] = $this->_setUmur($output['response']['peserta']['tglLahir']);;
                        $_POST['pnd'] = '-';
                        $_POST['keluarga'] = 'AYAH';
                        $_POST['namakeluarga'] = '-';
                        $_POST['kd_pj'] = 'BPJ';
                        $_POST['no_peserta'] = $output['response']['peserta']['noKartu'];
                        $_POST['kd_kel'] = $this->settings->get('jkn_mobile_v2.kdkel');
                        $_POST['kd_kec'] = $this->settings->get('jkn_mobile_v2.kdkec');
                        $_POST['kd_kab'] = $this->settings->get('jkn_mobile_v2.kdkab');
                        $_POST['pekerjaanpj'] = '-';
                        $_POST['alamatpj'] = '-';
                        $_POST['kelurahanpj'] = '-';
                        $_POST['kecamatanpj'] = '-';
                        $_POST['kabupatenpj'] = '-';
                        $_POST['perusahaan_pasien'] = $this->settings->get('jkn_mobile_v2.perusahaan_pasien');
                        $_POST['suku_bangsa'] = $this->settings->get('jkn_mobile_v2.suku_bangsa');
                        $_POST['bahasa_pasien'] = $this->settings->get('jkn_mobile_v2.bahasa_pasien');
                        $_POST['cacat_fisik'] = $this->settings->get('jkn_mobile_v2.cacat_fisik');
                        $_POST['email'] = '';
                        $_POST['nip'] = '';
                        $_POST['kd_prop'] = $this->settings->get('jkn_mobile_v2.kdprop');
                        $_POST['propinsipj'] = '-';

                        $query = $this->db('pasien')->save($_POST);

                        if($query) {
                            $this->core->db()->pdo()->exec("UPDATE set_no_rkm_medis SET no_rkm_medis='$_POST[no_rkm_medis]'");
                            // Get antrian poli
                            $no_reg_akhir = $this->db()->pdo()->prepare("SELECT max(no_reg) FROM booking_registrasi WHERE kd_poli='$poli[kd_poli_rs]' and tanggal_periksa='$decode[tanggalperiksa]'");
                            $no_reg_akhir->execute();
                            $no_reg_akhir = $no_reg_akhir->fetch();
                            if(empty($no_reg_akhir['0'])) {
                                $no_reg_akhir['0'] = '000';
                            }
                            $no_urut_reg = substr($no_reg_akhir['0'], 0, 3);
                            $no_reg = sprintf('%03s', ($no_urut_reg + 1));
                            $jenisantrean = 2;
                            $minutes = $no_urut_reg * 10;
                            $cek_kouta['jam_mulai'] = date('H:i:s',strtotime('+'.$minutes.' minutes',strtotime($cek_kouta['jam_mulai'])));
                            $keterangan = 'Peserta harap datang 60 menit lebih awal guna pencatatan administrasi.';
                            $message = 'Pasien Baru';
                            $code = 202;

                            $this->db('mlite_antrian_loket')->save([
                              'kd' => NULL,
                              'type' => 'Loket',
                              'noantrian' => $no_reg,
                              'postdate' => $decode['tanggalperiksa'],
                              'start_time' => $cek_kouta['jam_mulai'],
                              'end_time' => '00:00:01'
                            ]);

                            $query = $this->db('booking_registrasi')->save([
                                'tanggal_booking' => date('Y-m-d'),
                                'jam_booking' => date('H:i:s'),
                                'no_rkm_medis' => $_POST['no_rkm_medis'],
                                'tanggal_periksa' => $decode['tanggalperiksa'],
                                'kd_dokter' => $cek_kouta['kd_dokter'],
                                'kd_poli' => $cek_kouta['kd_poli'],
                                'no_reg' => $no_reg,
                                'kd_pj' => 'BPJ',
                                'limit_reg' => 1,
                                'waktu_kunjungan' => $decode['tanggalperiksa'].' '.$cek_kouta['jam_mulai'],
                                'status' => 'Belum'
                            ]);
                        }
                    } else {
                        // Get antrian poli
                        $no_reg_akhir = $this->db()->pdo()->prepare("SELECT max(no_reg) FROM booking_registrasi WHERE kd_poli='$poli[kd_poli_rs]' and tanggal_periksa='$decode[tanggalperiksa]'");
                        $no_reg_akhir->execute();
                        $no_reg_akhir = $no_reg_akhir->fetch();
                        $no_urut_reg = substr($no_reg_akhir['0'], 0, 3);
                        $no_reg = sprintf('%03s', ($no_urut_reg + 1));
                        $jenisantrean = 2;
                        $minutes = $no_urut_reg * 10;
                        $cek_kouta['jam_mulai'] = date('H:i:s',strtotime('+'.$minutes.' minutes',strtotime($cek_kouta['jam_mulai'])));
                        $keterangan = 'Peserta harap datang 30 menit lebih awal.';
                        $message = 'Sukses';
                        $code = 201;
                        $query = $this->db('booking_registrasi')->save([
                            'tanggal_booking' => date('Y-m-d'),
                            'jam_booking' => date('H:i:s'),
                            'no_rkm_medis' => $data_pasien['no_rkm_medis'],
                            'tanggal_periksa' => $decode['tanggalperiksa'],
                            'kd_dokter' => $cek_kouta['kd_dokter'],
                            'kd_poli' => $cek_kouta['kd_poli'],
                            'no_reg' => $no_reg,
                            'kd_pj' => 'BPJ',
                            'limit_reg' => 1,
                            'waktu_kunjungan' => $decode['tanggalperiksa'].' '.$cek_kouta['jam_mulai'],
                            'status' => 'Belum'
                        ]);
                    }
                    if ($query) {
                        $response = array(
                            'response' => array(
                                'nomorantrean' => 'A'.$no_reg,
                                'angkaantrean' => 12,
                                'kodebooking' => 'A'.$no_reg,
                                'norm' => '123345',
                                'namapoli' => $cek_kouta['nm_poli'],
                                'namadokter' => $cek_kouta['nm_dokter'],
                                'estimasidilayani' => strtotime($decode['tanggalperiksa'].' '.$cek_kouta['jam_mulai']) * 1000,
                                'sisakuotajkn' => 5,
                                'kuotajkn' => 30,
                                'sisakuotanonjkn' => 5,
                                'kuotanonjkn' => 30,
                                'keterangan' => $keterangan
                            ),
                            'metadata' => array(
                                'message' => $message,
                                'code' => $code
                            )
                        );
                        http_response_code(200);

                        if(!empty($decode['nomorreferensi'])) {
                          $this->db('mlite_antrian_referensi')->save([
                              'tanggal_periksa' => $decode['tanggalperiksa'],
                              'nomor_kartu' => $decode['nomorkartu'],
                              'nomor_referensi' => $decode['nomorreferensi']
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

        if ($header[$this->settings->get('jkn_mobile_v2.header_token')] == $this->_getToken() && $header['X-Username'] == $this->settings->get('jkn_mobile_v2.x_username')) {
            if($decode['nomorkartu'] == ''){
                $response = array(
                    'metadata' => array(
                        'message' => 'Nomor kartu tidak boleh kosong',
                        'code' => 201
                    )
                );
            } else {
                $response = array(
                    'response' => array(
                        'norm' => '123456',
                    ),
                    'metadata' => array(
                        'message' => 'Harap datang ke admisi untuk melengkapi data rekam medis',
                        'code' => 200
                    )
                );
                http_response_code(200);
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

        if ($header[$this->settings->get('jkn_mobile_v2.header_token')] == $this->_getToken() && $header['X-Username'] == $this->settings->get('jkn_mobile_v2.x_username')) {
            if($decode['kodebooking'] == ''){
                $response = array(
                    'metadata' => array(
                        'message' => 'Kode booking todak boleh kosong',
                        'code' => 201
                    )
                );
            } else {
                $response = array(
                    'response' => array(
                        'norm' => '123456',
                    ),
                    'metadata' => array(
                        'message' => 'Harap datang ke admisi untuk melengkapi data rekam medis',
                        'code' => 200
                    )
                );
                http_response_code(200);
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

        if ($header[$this->settings->get('jkn_mobile_v2.header_token')] == $this->_getToken() && $header['X-Username'] == $this->settings->get('jkn_mobile_v2.x_username')) {
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

        if ($header[$this->settings->get('jkn_mobile_v2.header_token')] == $this->_getToken() && $header['X-Username'] == $this->settings->get('jkn_mobile_v2.x_username')) {
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
      $url = $this->bpjsurl.'jadwaldokter/kodepoli/'.$kodepoli.'/tanggal/'.$tanggal;
      $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey);
      $json = json_decode($output, true);
      echo json_encode($json);
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
      $output = BpjsService::post($url, $data, $this->consid, $this->secretkey);
      $json = json_decode($output, true);
      echo json_encode($json);
      exit();
    }

    public function _getAntreanAdd($data = [])
    {
      $data = [
          'kodebooking' => '16032021A001',
          'jenispasien' => 'JKN',
          'nomorkartu' => '00012345678',
          'nik' => '3212345678987654',
          'nohp' => '085635228888',
          'kodepoli' => 'ANA',
          'namapoli' => 'Anak',
          'norm' => '123345',
          'tanggalperiksa' => '2021-03-28',
          'kodedokter' => 12345,
          'namadokter' => 'Dr. Hendra',
          'jampraktek' => '08:00-16:00',
          'jeniskunjungan' => 1,
          'nomorreferensi' => '0001R0040116A000001',
          'nomorantrean' => 'A-12',
          'angkaantrean' => 12,
          'estimasidilayani' => 1615869169000,
          'sisakuotajkn' => 5,
          'kuotajkn' => 30,
          'sisakuotanonjkn' => 5,
          'kuotanonjkn' => 30,
          'keterangan' => 'Peserta harap 30 menit lebih awal guna pencatatan administrasi.'
      ];

      $data = json_encode($data);
      $url = $this->bpjsurl.'antrean/add';
      $output = BpjsService::post($url, $data, $this->consid, $this->secretkey);
      $json = json_decode($output, true);
      echo json_encode($json);
      exit();
    }

    public function _getAntreanBatal($data = [])
    {
      $data = [
          'kodebooking' => '16032021A001',
          'keterangan' => 'Terjadi perubahan jadwal dokter, silahkan daftar kembali'
      ];

      $data = json_encode($data);
      $url = $this->bpjsurl.'antrean/batal';
      $output = BpjsService::post($url, $data, $this->consid, $this->secretkey);
      $json = json_decode($output, true);
      echo json_encode($json);
      exit();
    }

    public function _getAntreanUpdateWaktu($data = [])
    {
      $data = [
          'kodebooking' => '16032021A001',
          'taskid' => '1',
          'waktu' => '1616559330000'
      ];

      $data = json_encode($data);
      $url = $this->bpjsurl.'antrean/updatewaktu';
      $output = BpjsService::post($url, $data, $this->consid, $this->secretkey);
      $json = json_decode($output, true);
      echo json_encode($json);
      exit();
    }

    public function _getAntreanGetListTask($data = [])
    {
      $data = [
          'kodebooking' => '16032021A001'
      ];

      $data = json_encode($data);
      $url = $this->bpjsurl.'antrean/getlisttask';
      $output = BpjsService::post($url, $data, $this->consid, $this->secretkey);
      $json = json_decode($output, true);
      echo json_encode($json);
      exit();
    }

    private function _getToken()
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(['username' => $this->settings->get('jkn_mobile_v2.x_username'), 'password' => $this->settings->get('jkn_mobile_v2.x_password'), 'date' => strtotime(date('Y-m-d')) * 1000]);
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

}
