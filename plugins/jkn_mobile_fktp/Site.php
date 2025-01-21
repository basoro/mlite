<?php

namespace Plugins\JKN_Mobile_FKTP;

use Systems\SiteModule;
use Systems\Lib\PcareService;

class Site extends SiteModule
{

    public function init()
    {
      $this->usernamePcare = $this->settings->get('pcare.usernamePcare');
      $this->passwordPcare = $this->settings->get('pcare.passwordPcare');
      $this->kdAplikasi = '095';
      $this->consumerID = $this->settings->get('pcare.consumerID');
      $this->consumerSecret = $this->settings->get('pcare.consumerSecret');
      $this->consumerUserKey = $this->settings->get('pcare.consumerUserKey');
      $this->api_url = $this->settings->get('pcare.PCareApiUrl');
      $this->api_url_antrol = 'https://apijkn.bpjs-kesehatan.go.id/antreanfktp/';
      if (strpos($this->api_url, 'dev') !== false) { 
        $this->api_url_antrol = 'https://apijkn-dev.bpjs-kesehatan.go.id/antreanfktp_dev/';
      }    
    }

    public function routes()
    {
        $this->route('jknmobilefktp', 'getIndex');
        $this->route('jknmobilefktp/display', 'getDisplayAntrian');
        $this->route('jknmobilefktp/apm', 'getAntrian');
        $this->route('jknmobilefktp/auth', 'getAuth');
        $this->route('jknmobilefktp/antrean', 'getAntrean');
        $this->route('jknmobilefktp/antrean/status/(:str)/(:str)', 'getStatusAntrean');
        $this->route('jknmobilefktp/antrean/sisapeserta/(:str)/(:str)/(:str)', 'getSisaAntrean');
        $this->route('jknmobilefktp/antrean/batal', 'getBatalAntrean');
        $this->route('jknmobilefktp/peserta', 'getPeserta');
        $this->route('jknmobilefktp/api', 'getApi');
        $this->route('jknmobilefktp/addantrol/(:int)', 'getAntrolAddAntrian');

    }

    public function getIndex()
    {
        $referensi_poli = $this->db('maping_poliklinik_pcare')->toArray();
        echo $this->draw('index.html', ['referensi_poli' => $referensi_poli]);
        exit();
    }

    public function getAntrian()
    {
        $powered = 'Powered by <a href="https://mlite.id/">mLITE.id</a>';
        $fktp = $this->settings->get('settings.nama_instansi');
        $poliklinik = $this->db('poliklinik')->toArray();
        echo $this->draw('antrian.html', ['powered' => $powered, 'fktp' => $fktp]);
        exit();
    }

    public function getDisplayAntrian()
    {
        $powered = 'Powered by <a href="https://mlite.id/">mLITE.id</a>';
        $fktp = $this->settings->get('settings.nama_instansi');

        $tanggal = date('Y-m-d');
        $antrean_periksa = $this->db('reg_periksa')->select('no_reg')->select('kd_poli')->where('stts', 'Berkas Diterima')->where('tgl_registrasi', $tanggal)->desc('no_reg')->oneArray();
        $antrean_selesai = $this->db('reg_periksa')->select('no_reg')->where('stts', 'Sudah')->where('tgl_registrasi', $tanggal)->toArray();
        $antrean = $this->db('reg_periksa')->select('no_reg')->where('tgl_registrasi', $tanggal)->toArray();
        $sisa_antrean = count($antrean) - isset_or($antrean_periksa['no_reg'], '0');
        $total = count($antrean);
        
        $kode = '';

        $poli = $this->db('maping_poliklinik_pcare')->like('kd_poli_rs', isset_or($antrean_periksa['kd_poli']))->oneArray();
        
        if(isset_or($poli['kd_poli_pcare']) == '001') {
            $kode_antrean = 'A';
        }
        if(isset_or($poli['kd_poli_pcare']) == '002') {
            $kode_antrean = 'B';
        }
        if(isset_or($poli['kd_poli_pcare']) == '003') {
            $kode_antrean = 'C';
        }
        
        $antrian_panggil = isset_or($kode_antrean, '0') . '-' .isset_or($antrean_periksa['no_reg'], '0');
        $total_antrean = isset_or($total, '0');
        $antrean = $antrian_panggil;
        $sisa_antrean = isset_or($sisa_antrean, '0');

        $settings = $this->settings('settings');
        $vidio_anjungan = isset_or($settings['vidio_anjungan']);
        $running_text = isset_or($settings['running_text']);
        
        echo $this->draw('display.html', ['powered' => $powered, 'fktp' => $fktp, 'total_antrean' => $total_antrean, 'sisa_antrean' => $sisa_antrean, 'antrean' => $antrean, 'running_text' => $running_text, 'vidio_anjungan' => $vidio_anjungan]);
        exit();
    }

    public function getApi()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');
        header("Access-Control-Allow-Headers: X-Requested-With");

        // $key = $this->settings->get('pendaftaran.api_key');
        $key = 'rahasia';
        $token = trim(isset($_REQUEST['token'])? $_REQUEST['token'] : null);
        if($token == $key) {
          $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";
          switch($action){
            case "simpanpendaftaran":
              $send_data = array();
              $no_rkm_medis = trim($_REQUEST['norm']);
              $kd_poli = trim($_REQUEST['kd_poli']);
              $kd_pj = trim($_REQUEST['kd_pj']);
              unset($_POST);

              $date = date('Y-m-d');

              $tentukan_hari=date('D',strtotime(date('Y-m-d')));
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
              
              $_POST['kd_pj']      = $kd_pj;
              $_POST['kd_poli']    = $kd_poli;
              
              $jadwal = $this->db('jadwal')->where('hari_kerja', $hari)->where('kd_poli', $_POST['kd_poli'])->oneArray();
              $dokter = $this->db('dokter')->oneArray();

              $_POST['kd_dokter']  = isset_or($jadwal['kd_dokter'], $dokter['kd_dokter']);
              $_POST['no_reg']     = $this->core->setNoReg($_POST['kd_dokter'], $_POST['kd_poli']);
              $_POST['hubunganpj'] = $this->core->getPasienInfo('keluarga', $no_rkm_medis);
              $_POST['almt_pj']    = $this->core->getPasienInfo('alamat', $no_rkm_medis);
              $_POST['p_jawab']    = $this->core->getPasienInfo('namakeluarga', $no_rkm_medis);
              $_POST['stts']       = 'Belum';

              $cek_stts_daftar = $this->db('reg_periksa')->where('no_rkm_medis', $no_rkm_medis)->count();
              $_POST['stts_daftar'] = 'Baru';
              if($cek_stts_daftar > 0) {
                $_POST['stts_daftar'] = 'Lama';
              }

              $biaya_reg = $this->db('poliklinik')->where('kd_poli', $_POST['kd_poli'])->oneArray();
              $_POST['biaya_reg'] = $biaya_reg['registrasi'];
              if($_POST['stts_daftar'] == 'Lama') {
                $_POST['biaya_reg'] = $biaya_reg['registrasilama'];
              }

              $cek_status_poli = $this->db('reg_periksa')->where('no_rkm_medis', $no_rkm_medis)->where('kd_poli', $_POST['kd_poli'])->count();
              $_POST['status_poli'] = 'Baru';
              if($cek_status_poli > 0) {
                $_POST['status_poli'] = 'Lama';
              }

              $tanggal = new \DateTime($this->core->getPasienInfo('tgl_lahir', $no_rkm_medis));
              $today = new \DateTime($date);
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

              $_POST['tgl_registrasi'] = $date;
              $_POST['no_rkm_medis'] = $no_rkm_medis;

              $_POST['umurdaftar'] = $umur;
              $_POST['sttsumur'] = $sttsumur;
              $_POST['status_lanjut']   = 'Ralan';
              $_POST['status_bayar']    = 'Belum Bayar';
              $_POST['no_rawat'] = $this->core->setNoRawat($date);
              $_POST['jam_reg'] = date('H:i:s');

              $result = $this->db('reg_periksa')->save($_POST);

              if($result) {
                if($_POST['kd_pj'] == 'BPJ') {
                    $this->getAntrolAddAntrian($no_rkm_medis, $kd_poli, $kd_pj);
                    // $send_data['state'] = 'success';
                } else {
                    $send_data['state'] = 'success';
                }
              } else {
                $send_data['state'] = 'error';
              }

              echo json_encode($send_data);
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

    public function getAntrolAddAntrian($noRm, $kdPoli, $kdPj)
    {
        $maping_poliklinik_pcare = $this->db('maping_poliklinik_pcare')->where('kd_poli_rs', $kdPoli)->oneArray(); 

        if($maping_poliklinik_pcare['kd_poli_pcare'] == '001') {
            $kode_antrean = 'A';
        }
  
        if($maping_poliklinik_pcare['kd_poli_pcare'] == '002') {
            $kode_antrean = 'B';
        }

        $nama_poli = $maping_poliklinik_pcare['nm_poli_pcare'];
        
        $date = date('Y-m-d');
  
        $antrean = $this->db('reg_periksa')->select('no_reg')->where('tgl_registrasi', $date)->toArray();
        $_no_antrian = '0';
        if(count($antrean) != '0'){
          $_no_antrian = count($antrean);
        }
        $no_antrian = $_no_antrian + 1;
  
        $pasien = $this->db('pasien')->where('no_rkm_medis', $noRm)->oneArray();
        $noKartu = isset_or($pasien['no_peserta'], '');
        $nik = isset_or($pasien['no_ktp'], '');
        $noHp = isset_or($pasien['no_tlp'], '08820'.$noRm);
  
        $tentukan_hari=date('D',strtotime(date('Y-m-d')));
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
        
        $_POST['kd_pj']      = $kdPj;
        $_POST['kd_poli']    = $kdPoli;
        
        $jadwal = $this->db('jadwal')->where('hari_kerja', $hari)->where('kd_poli', $kdPoli)->oneArray();        
        $dokter = $this->db('dokter')->oneArray();
        $kd_dokter = isset_or($jadwal['kd_dokter'], $dokter['kd_dokter']);

        $maping_dokter_pcare = $this->db('maping_dokter_pcare')->where('kd_dokter', $kd_dokter)->oneArray();

        $jampraktek = $jadwal['jam_mulai'].'-'.$jadwal['jam_selesai'];

        $data = [
          'nomorkartu' => $noKartu,
          'nik' => $nik,
          'nohp' => $noHp,
          'kodepoli' => $maping_poliklinik_pcare['kd_poli_pcare'],
          'namapoli' => $nama_poli,
          'norm' => $noRm,
          'tanggalperiksa' => $date,
          'kodedokter' => $maping_dokter_pcare['kd_dokter_pcare'], 
          'namadokter' => $maping_dokter_pcare['nm_dokter_pcare'], 
          'jampraktek' => isset_or($jampraktek, '07:00-23:00'),
          'nomorantrean' => $kode_antrean.'-'.$no_antrian,
          'angkaantrean' => $no_antrian,
          'keterangan' => ""
        ];
    
        $data = json_encode($data);
        // echo $data;

        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
        $key = $this->consumerID . $this->consumerSecret . $tStamp;

        $url = $this->api_url_antrol.'antrean/add';
        $output = PcareService::post($url, $data, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
        $json = json_decode($output, true);
        // echo json_encode($json);
  
        $code = $json['metadata']['code'];
        $message = $json['metadata']['message'];
        $stringDecrypt = stringDecrypt($key, isset_or($json['response']));
        $decompress = '""';
        if (!empty($stringDecrypt)) {
            $decompress = decompress($stringDecrypt);
        }
        if ($json != null) {
            echo '{
                "state": "sukses", 
                "metaData": {
                  "code": "' . $code . '",
                  "message": "' . $message . '"
                },
                "response": ' . $decompress . '}';
        } else {
            echo '{
                "state": "gagal", 
                "metaData": {
                  "code": "5000",
                  "message": "ERROR"
                },
                "response": "ADA KESALAHAN ATAU SAMBUNGAN KE SERVER BPJS TERPUTUS."}';
        }
  
        exit();
    }

    public function getAuth()
    {
        echo $this->_resultAuth();
        exit();
    }

    private function _resultAuth()
    {
        header("Content-Type: application/json");
        $header = apache_request_headers();
        $response = array();
        if (isset($header[$this->settings->get('jkn_mobile_fktp.header_username')]) && isset($header[$this->settings->get('jkn_mobile_fktp.header_password')]) && $header[$this->settings->get('jkn_mobile_fktp.header_username')] == $this->settings->get('jkn_mobile_fktp.username') && $header[$this->settings->get('jkn_mobile_fktp.header_password')] == $this->settings->get('jkn_mobile_fktp.password')) {
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

    public function getAntrean()
    {
        echo $this->_resultAntrean();
        exit();
    }

    private function _resultAntrean()
    {
        header("Content-Type: application/json");
        $header = apache_request_headers();
        $konten = trim(file_get_contents("php://input"));
        $decode = json_decode($konten, true);
        $response = array();
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

        if ($header[$this->settings->get('jkn_mobile_fktp.header_username')] == $this->settings->get('jkn_mobile_fktp.username') && $header[$this->settings->get('jkn_mobile_fktp.header')] == $this->_getToken()) {

            if (empty($decode['nomorkartu'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Maaf Nomor kartu masih kosong',
                        'code' => 201
                    )
                );
            }

            elseif (!empty($decode['nomorkartu']) && mb_strlen($decode['nomorkartu'], 'UTF-8') <> 13) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Nomor kartu harus 13 digit',
                        'code' => 201
                    )
                );
            }

            elseif (!empty($decode['nomorkartu']) && !preg_match("/^[0-9]{13}$/",$decode['nomorkartu'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Nomor Kartu Tidak Sesuai',
                        'code' => 201
                    )
                );                
            }

            elseif (empty($decode['nik'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Maaf Nomor Induk Kependudukan masih kosong',
                        'code' => 201
                    )
                );
            }

            elseif (!empty($decode['nik']) && strlen($decode['nik']) < 15) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Maaf Nomor Induk Kependudukan kurang dari 16 Digit',
                        'code' => 201
                    )
                );
            }

            elseif (!empty($decode['nik']) && !preg_match("/^[0-9]{16}$/",$decode['nik'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Nomor Induk Kependudukan Tidak Sesuai',
                        'code' => 201
                    )
                );                
            }

            elseif (empty($decode['nohp'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Maaf Nomor HP tidak boleh kosong',
                        'code' => 201
                    )
                );
            }

            elseif (strpos($decode['nohp'],"'")||strpos($decode['nohp'],"\\")) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Maaf Format Nomor HP Tidak Sesuai',
                        'code' => 201
                    )
                );
            }

            elseif (empty($decode['kodepoli'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Maaf Poli Tujuan Anda Masih Kosong',
                        'code' => 201
                    )
                );
            }

            elseif (strpos($decode['kodepoli'],"'")||strpos($decode['kodepoli'],"\\")) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Maaf Poli Tujuan Tidak Ditemukan',
                        'code' => 201
                    )
                );
            }

            elseif (empty($decode['kodedokter'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Maaf Dokter Tujuan Anda Masih Kosong',
                        'code' => 201
                    )
                );
            }

            elseif (strpos($decode['kodedokter'],"'")||strpos($decode['kodedokter'],"\\")) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Maaf Dokter Tujuan Tidak Ditemukan',
                        'code' => 201
                    )
                );
            }            

            elseif (empty($decode['tanggalperiksa'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Maaf Tanggal Periksa Anda Masih Kosong',
                        'code' => 201
                    )
                );
            }

            elseif (!empty($decode['tanggalperiksa']) && !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$decode['tanggalperiksa'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Format Tanggal Tidak Sesuai, format yang benar adalah yyyy-mm-dd',
                        'code' => 201
                    )
                );            
            }

            elseif (!empty($decode['tanggalperiksa']) && date("Y-m-d")>$decode['tanggalperiksa']) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Maaf Tanggal Periksa Tidak Berlaku Mundur',
                        'code' => 201
                    )
                );            
            }
    
            elseif (empty($decode['jampraktek'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Maaf Jam Praktek tidak boleh kosong',
                        'code' => 201
                    )
                );
            }

            elseif (strpos($decode['jampraktek'],"'")||strpos($decode['jampraktek'],"\\")) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Maaf Jam Praktek tidak ditemukan',
                        'code' => 201
                    )
                );
            }            

            elseif (empty($decode['keluhan'])) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Maaf Keluhan tidak boleh kosong',
                        'code' => 201
                    )
                );
            }

            elseif (strpos($decode['keluhan'],"'")||strpos($decode['keluhan'],"\\")) {
                $response = array(
                    'metadata' => array(
                        'message' => 'Maaf Keluhan tidak sesuai format',
                        'code' => 201
                    )
                );
            }     

            elseif (!empty($decode['nomorkartu']) && !empty($decode['nik']) && !empty($decode['kodepoli']) && !empty($decode['tanggalperiksa'])) {
                $data_pasien = $this->db('pasien')->where('no_peserta', $decode['nomorkartu'])->oneArray();
                $poli = $this->db('maping_poliklinik_pcare')->where('kd_poli_pcare', $decode['kodepoli'])->oneArray();
                $cek_kouta = $this->db()->pdo()->prepare("SELECT jadwal.kuota - (SELECT COUNT(reg_periksa.tgl_registrasi) FROM reg_periksa WHERE reg_periksa.tgl_registrasi='$decode[tanggalperiksa]' AND reg_periksa.kd_dokter=jadwal.kd_dokter) as sisa_kouta, jadwal.kd_dokter, jadwal.kd_poli, jadwal.jam_mulai as jam_mulai, poliklinik.nm_poli, dokter.nm_dokter FROM jadwal INNER JOIN maping_poliklinik_pcare ON maping_poliklinik_pcare.kd_poli_rs=jadwal.kd_poli INNER JOIN poliklinik ON poliklinik.kd_poli=jadwal.kd_poli INNER JOIN dokter ON dokter.kd_dokter=jadwal.kd_dokter WHERE jadwal.hari_kerja='$hari' AND maping_poliklinik_pcare.kd_poli_pcare='$decode[kodepoli]' GROUP BY jadwal.kd_dokter HAVING sisa_kouta > 0 ORDER BY sisa_kouta DESC LIMIT 1");
                $cek_kouta->execute();
                $cek_kouta = $cek_kouta->fetch();

                $cek_pendaftaran = $this->db('reg_periksa')->where('no_rkm_medis', $data_pasien['no_rkm_medis'])->where('tgl_registrasi', date('Y-m-d'))->oneArray();

                if (empty($cek_kouta['sisa_kouta'])) {
                    $response = array(
                        'metadata' => array(
                            'message' => 'Pendaftaran ke Poli Ini Sedang Tutup',
                            'code' => 201
                        )
                    );  
                }

                elseif($cek_pendaftaran) {
                    $response = array(
                        'metadata' => array(
                            'message' =>  "Nomor Antrean Hanya Dapat Diambil 1 Kali Pada Tanggal dan Poli Yang Sama",
                            'code' => 201
                        )
                    );                     
                }
                                
                elseif (!empty($cek_kouta['sisa_kouta']) && $cek_kouta['sisa_kouta'] > 0) {
                    if ($data_pasien['no_ktp'] != '') {
                        $no_reg_akhir = $this->db()->pdo()->prepare("SELECT max(no_reg) FROM reg_periksa WHERE kd_poli='$poli[kd_poli_rs]' and tgl_registrasi='$decode[tanggalperiksa]'");
                        $no_reg_akhir->execute();
                        $no_reg_akhir = $no_reg_akhir->fetch();
                        if(empty($no_reg_akhir[0])) {
                          $no_reg_akhir[0] = '000';
                        }
                        $no_urut_reg = substr($no_reg_akhir['0'], 0, 3);
                        $no_reg = sprintf('%03s', ($no_urut_reg + 1));
                        $jenisantrean = 2;
                        $minutes = $no_urut_reg * 10;
                        $cek_kouta['jam_mulai'] = date('H:i:s',strtotime('+'.$minutes.' minutes',strtotime($cek_kouta['jam_mulai'])));

                        $cek_stts_daftar = $this->db('reg_periksa')->where('no_rkm_medis', $data_pasien['no_rkm_medis'])->count();
                        $_POST['stts_daftar'] = 'Baru';
                        if($cek_stts_daftar > 0) {
                        $_POST['stts_daftar'] = 'Lama';
                        }

                        $biaya_reg = $this->db('poliklinik')->where('kd_poli', $cek_kouta['kd_poli'])->oneArray();
                        $_POST['biaya_reg'] = $biaya_reg['registrasi'];
                        if($_POST['stts_daftar'] == 'Lama') {
                        $_POST['biaya_reg'] = $biaya_reg['registrasilama'];
                        }

                        $cek_status_poli = $this->db('reg_periksa')->where('no_rkm_medis', $data_pasien['no_rkm_medis'])->where('kd_poli', $cek_kouta['kd_poli'])->count();
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


                        $query = $this->db('reg_periksa')
                        ->save([
                            'no_reg' => $no_reg,
                            'no_rawat' => $this->core->setNoRawat($decode['tanggalperiksa']),
                            'tgl_registrasi' => $decode['tanggalperiksa'],
                            'jam_reg' => date('H:i:s'),
                            'kd_dokter' => $cek_kouta['kd_dokter'],
                            'no_rkm_medis' => $data_pasien['no_rkm_medis'],
                            'kd_poli' => $cek_kouta['kd_poli'],
                            'p_jawab' => $this->core->getPasienInfo('namakeluarga', $data_pasien['no_rkm_medis']),
                            'almt_pj' => $this->core->getPasienInfo('alamatpj', $data_pasien['no_rkm_medis']),
                            'hubunganpj' => $this->core->getPasienInfo('keluarga', $data_pasien['no_rkm_medis']),
                            'biaya_reg' => $_POST['biaya_reg'],
                            'stts' => 'Belum',
                            'stts_daftar' => $_POST['stts_daftar'],
                            'status_lanjut' => 'Ralan',
                            'kd_pj' => $this->core->getPasienInfo('kd_pj', $data_pasien['no_rkm_medis']),
                            'umurdaftar' => $umur,
                            'sttsumur' => $sttsumur,
                            'status_bayar' => 'Belum Bayar',
                            'status_poli' => $_POST['status_poli']
                        ]);


                        if($decode['kodepoli'] == '001') {
                            $kode_antrean = 'A';
                        }
                
                        if($decode['kodepoli'] == '002') {
                            $kode_antrean = 'B';
                        }    

                        if($decode['kodepoli'] == '003') {
                            $kode_antrean = 'C';
                        }    

                        if ($query) {
                            $response = array(
                                'response' => array(
                                    'nomorantrean' => $kode_antrean.'-'.$no_reg,
                                    'angkaantrean' => $no_reg,
                                    'namapoli' => $cek_kouta['nm_poli'],
                                    'sisaantrean' => $cek_kouta['sisa_kouta'] - 1,
                                    'antreanpanggil' => $kode_antrean.'-'.isset_or($no_reg, '0'),
                                    'keterangan' => 'Datang 30 Menit sebelum pelayanan, Konfirmasi kehadiran dibagian pendaftaran dengan menunjukan bukti pendaftaran melalui Mobile JKN, Terima Kasih..'
                                ),
                                'metadata' => array(
                                    'message' => 'Ok',
                                    'code' => 200
                                )
                            );
                        } else {
                            $response = array(
                                'metadata' => array(
                                    'message' => "Maaf Terjadi Kesalahan, Hubungi Admnistrator..",
                                    'code' => 401
                                )
                            );
                        }
                    } elseif ($data_pasien['no_ktp'] == '') {
                        $response = array(
                            'metadata' => array(
                                'message' => "Pasien tidak ditemukan/belum terdaftar di " . $this->core->getSettings('nama_instansi'),
                                'code' => 201 // Gunakan kode 202 jika bisa mendaftaran pasien baru
                            )
                        );
                    }
                } else {
                    $response = array(
                        'metadata' => array(
                            'message' => "Maaf kouta antrian tidak tersedia atau Habis ! Mohon pilih tanggal lain!",
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
        }
        echo json_encode($response);
    }

    public function getStatusAntrean()
    {
        echo $this->_resultStatusAntrean();
        exit();
    }

    private function _resultStatusAntrean()
    {
        header("Content-Type: application/json");
        $slug = parseURL();
        //print_r($slug);
        if(count($slug) == 4) {$n = 0;}
        if(count($slug) == 5) {$n = 1;}
        if(count($slug) == 6) {$n = 2;}
        if(count($slug) == 7) {$n = 3;}
        $header = apache_request_headers();
        $response = array();

        $tanggal = $slug[(3+$n)];
        $kode_poli = $slug[(2+$n)];

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$tanggal)) {
            $response = array(
                'metadata' => array(
                    'message' => 'Format Tanggal Tidak Sesuai, format yang benar adalah yyyy-mm-dd',
                    'code' => 201
                )
            );            
        }
        elseif (date("Y-m-d")>$tanggal) {
            $response = array(
                'metadata' => array(
                    'message' => 'Tanggal Periksa Tidak Berlaku Mundur',
                    'code' => 201
                )
            );            
        }        
        elseif ($slug[(1+$n)] == 'status' && $header[$this->settings->get('jkn_mobile_fktp.header')] == $this->_getToken() && $header[$this->settings->get('jkn_mobile_fktp.header_username')] == $this->settings->get('jkn_mobile_fktp.username')) {
            $data = $this->db('reg_periksa')
                ->select('poliklinik.nm_poli')
                ->select(['total_antrean' => 'COUNT(DISTINCT reg_periksa.no_rawat)'])
                ->select(['sisa_antrean' => 'SUM(CASE WHEN reg_periksa.stts=\'Belum\' THEN 1 ELSE 0 END)'])
                ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
                ->join('maping_poliklinik_pcare', 'maping_poliklinik_pcare.kd_poli_rs = reg_periksa.kd_poli')
                ->where('reg_periksa.tgl_registrasi', $slug[(3+$n)])
                ->where('maping_poliklinik_pcare.kd_poli_pcare', $slug[(2+$n)])
                ->oneArray();
            $get_no_reg = $this->db('reg_periksa')
                ->select('reg_periksa.no_reg')
                ->join('maping_poliklinik_pcare', 'maping_poliklinik_pcare.kd_poli_pcare = reg_periksa.kd_poli')
                ->where('reg_periksa.tgl_registrasi', $slug[(3+$n)])
                ->where('maping_poliklinik_pcare.kd_poli_pcare', $slug[(2+$n)])
                ->where('reg_periksa.stts', 'Berkas Diterima')
                ->limit(1)
                ->oneArray();

            $data['antrean_panggil'] = '000';
            if(!empty($get_no_reg['no_reg'])) {
               $data['antrean_panggil'] = $get_no_reg['no_reg'];
            }

            if($slug[(2+$n)] == '001') {
                $kode_antrean = 'A';
            }
    
            if($slug[(2+$n)] == '002') {
                $kode_antrean = 'B';
            } 

            if ($data['nm_poli'] != '') {
                $response = array(
                    'response' => array(
                        'namapoli' => $data['nm_poli'],
                        'totalantrean' => $data['total_antrean'],
                        'sisaantrean' => $data['sisa_antrean'],
                        'antreanpanggil' => $kode_antrean.'-'.$data['antrean_panggil'],
                        'keterangan' => 'Datanglah Minimal 30 Menit, jika no antrian anda terlewat, silakan konfirmasi ke bagian Pendaftaran atau Perawat Poli, Terima Kasih.'
                    ),
                    'metadata' => array(
                        'message' => 'Ok',
                        'code' => 200
                    )
                );
            } else {
                $response = array(
                    'metadata' => array(
                        'message' => 'Maaf belum Ada Antrian ditanggal ' . $slug[(3+$n)],
                        'code' => 201
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

    public function getSisaAntrean()
    {
        echo $this->_resultSisaAntrean();
        exit();
    }

    private function _resultSisaAntrean()
    {
      header("Content-Type: application/json");
      $slug = parseURL();
      //print_r($slug);
      if(count($slug) == 5) {$n = 0;}
      if(count($slug) == 6) {$n = 1;}
      if(count($slug) == 7) {$n = 2;}
      $header = apache_request_headers();
      $response = array();
      if ($slug[(1+$n)] == 'sisapeserta' && $header[$this->settings->get('jkn_mobile_fktp.header')] == $this->_getToken() && $header[$this->settings->get('jkn_mobile_fktp.header_username')] == $this->settings->get('jkn_mobile_fktp.username')) {
        $data = $this->db('reg_periksa')
            ->select('poliklinik.nm_poli')
            ->select(['total_antrean' => 'COUNT(DISTINCT reg_periksa.no_rawat)'])
            ->select(['sisa_antrean' => 'SUM(CASE WHEN reg_periksa.stts=\'Belum\' THEN 1 ELSE 0 END)'])
            ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
            ->join('maping_poliklinik_pcare', 'maping_poliklinik_pcare.kd_poli_rs = reg_periksa.kd_poli')
            ->where('reg_periksa.tgl_registrasi', $slug[(4+$n)])
            ->where('maping_poliklinik_pcare.kd_poli_pcare', $slug[(3+$n)])
            ->oneArray();
        $get_no_reg = $this->db('reg_periksa')
            ->select('reg_periksa.no_reg')
            ->join('maping_poliklinik_pcare', 'maping_poliklinik_pcare.kd_poli_pcare = reg_periksa.kd_poli')
            ->where('reg_periksa.tgl_registrasi', $slug[(4+$n)])
            ->where('maping_poliklinik_pcare.kd_poli_pcare', $slug[(3+$n)])
            ->where('reg_periksa.stts', 'Berkas Diterima')
            ->limit(1)
            ->oneArray();

          $data['antrean_panggil'] = '000';
          if(!empty($get_no_reg['no_reg'])) {
             $data['antrean_panggil'] = $get_no_reg['no_reg'];
          }

          if($slug[(3+$n)] == '001') {
            $kode_antrean = 'A';
          }

          if($slug[(3+$n)] == '002') {
            $kode_antrean = 'B';
          } 

          $pasien = $this->db('pasien')->where('no_peserta',  $slug[(2+$n)])->oneArray();
          
          if (!$this->db('reg_periksa')->where('no_rkm_medis', $pasien['no_rkm_medis'])->where('tgl_registrasi', $slug[(4+$n)])->oneArray()) {
            $response = array(
                'metadata' => array(
                    'message' => 'Maaf anda belum ambil antrian ditanggal ' . $slug[(4+$n)],
                    'code' => 201
                )
            );
          } elseif ($data) {
              $response = array(
                  'response' => array(
                      'nomorantrean' => $data['total_antrean'],
                      'namapoli' => $data['nm_poli'],
                      'sisaantrean' => $data['sisa_antrean'],
                      'antreanpanggil' => $kode_antrean.'-'.$data['antrean_panggil'],
                      'keterangan' => 'Datanglah Minimal 30 Menit, jika no antrian anda terlewat, silakan konfirmasi ke bagian Pendaftaran atau Perawat Poli, Terima Kasih.'
                  ),
                  'metadata' => array(
                      'message' => 'Ok',
                      'code' => 200
                  )
              );
          } else {
              $response = array(
                  'metadata' => array(
                      'message' => 'Maaf belum Ada Antrian ditanggal ' . $slug[(4+$n)],
                      'code' => 201
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

    public function getBatalAntrean()
    {
        echo $this->_resultBatalAntrean();
        exit();
    }

    private function _resultBatalAntrean()
    {
        header("Content-Type: application/json");
        $header = apache_request_headers();
        $konten = trim(file_get_contents("php://input"));
        $decode = json_decode($konten, true);
        $response = array();
        if ($header[$this->settings->get('jkn_mobile_fktp.header')] == $this->_getToken() && $header[$this->settings->get('jkn_mobile_fktp.header_username')] == $this->settings->get('jkn_mobile_fktp.username')) {
            if (!empty($decode['nomorkartu']) && !empty($decode['kodepoli']) && !empty($decode['tanggalperiksa'])) {
                $data_pasien = $this->db('pasien')->where('no_peserta', $decode['nomorkartu'])->oneArray();
                $poli = $this->db('maping_poliklinik_pcare')->where('kd_poli_pcare', $decode['kodepoli'])->oneArray();
                $cek_sudah = $this->db('reg_periksa')
                    ->where('no_rkm_medis', $data_pasien['no_rkm_medis'])
                    ->where('kd_poli', $poli['kd_poli_rs'])
                    ->where('tgl_registrasi', $decode['tanggalperiksa'])
                    ->where('stts', 'Sudah')
                    ->oneArray();
                $cek_belum = $this->db('reg_periksa')
                    ->where('no_rkm_medis', $data_pasien['no_rkm_medis'])
                    ->where('kd_poli', $poli['kd_poli_rs'])
                    ->where('tgl_registrasi', $decode['tanggalperiksa'])
                    ->where('stts', 'Belum')
                    ->oneArray();

                if (date("Y-m-d")>$decode['tanggalperiksa']) {
                    $response = array(
                        'metadata' => array(
                            'message' => 'Antrean tidak ditemukan',
                            'code' => 201
                        )
                    );            
                }

                elseif ($cek_sudah) {
                    $response = array(
                        'metadata' => array(
                            'message' => 'Pasien sudah dilayani, antrean tidak dapat dibatalkan!',
                            'code' => 201
                        )
                    );
                }   

                elseif ($cek_belum) {
                    $query = $this->db('reg_periksa')
                        ->where('no_rkm_medis', $data_pasien['no_rkm_medis'])
                        ->where('kd_poli', $poli['kd_poli_rs'])
                        ->where('tgl_registrasi', $decode['tanggalperiksa'])
                        ->save('stts', 'Batal');
                    if ($query) {
                        $response = array(
                            'metadata' => array(
                                'message' => 'Ok',
                                'code' => 200
                            )
                        );
                    } else {
                        $response = array(
                            'metadata' => array(
                                'message' => "Maaf Terjadi kesalahan, Silahkan hubungi Administrator!",
                                'code' => 401
                            )
                        );
                    }
                } else  {
                    $response = array(
                        'metadata' => array(
                            'message' => 'Pembatalan antrean tidak dapat diproses!',
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
        }
        echo json_encode($response);
    }

    public function getPeserta()
    {
        echo $this->_resultPeserta();
        exit();
    }

    private function _resultPeserta()
    {
        header("Content-Type: application/json");
        $header = apache_request_headers();
        $konten = trim(file_get_contents("php://input"));
        $decode = json_decode($konten, true);
        $response = array();
        if($header[$this->settings->get('jkn_mobile_fktp.header')] == false) {
            $response = array(
                'metadata' => array(
                    'message' => 'Token expired',
                    'code' => 201
                )
            );
            http_response_code(201);

        } else if ($header[$this->settings->get('jkn_mobile_fktp.header_username')] == $this->settings->get('jkn_mobile_fktp.username') && $header[$this->settings->get('jkn_mobile_fktp.header')] == $this->_getToken()) {
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
            }else if(empty(isset($decode['rw']))) { 
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
            }else if(empty(isset($decode['rt']))) { 
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
                    $_POST['kd_kel'] = $this->settings->get('jkn_mobile_fktp.kdkel');
                    $_POST['kd_kec'] = $this->settings->get('jkn_mobile_fktp.kdkec');
                    $_POST['kd_kab'] = $this->settings->get('jkn_mobile_fktp.kdkab');
                    $_POST['pekerjaanpj'] = '-';
                    $_POST['alamatpj'] = '-';
                    $_POST['kelurahanpj'] = '-';
                    $_POST['kecamatanpj'] = '-';
                    $_POST['kabupatenpj'] = '-';
                    $_POST['perusahaan_pasien'] = $this->settings->get('jkn_mobile_fktp.perusahaan_pasien');
                    $_POST['suku_bangsa'] = $this->settings->get('jkn_mobile_fktp.suku_bangsa');
                    $_POST['bahasa_pasien'] = $this->settings->get('jkn_mobile_fktp.bahasa_pasien');
                    $_POST['cacat_fisik'] = $this->settings->get('jkn_mobile_fktp.cacat_fisik');
                    $_POST['email'] = '';
                    $_POST['nip'] = '';
                    $_POST['kd_prop'] = $this->settings->get('jkn_mobile_fktp.kdprop');
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
                            'message' => 'Pasien berhasil mendapatkan nomor RM, silahkan lanjutkan ke booking.',
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

    private function _setUmur($tanggal)
    {
        list($cY, $cm, $cd) = explode('-', date('Y-m-d'));
        list($Y, $m, $d) = explode('-', date('Y-m-d', strtotime($tanggal)));
        $umur = $cY - $Y;
        return $umur;
    }

    private function _getToken()
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(['username' => $this->settings->get('jkn_mobile_fktp.username'), 'password' => $this->settings->get('jkn_mobile_fktp.password'), 'date' => strtotime(date('Y-m-d')) * 1000]);
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

}
