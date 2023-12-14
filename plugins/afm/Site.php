<?php

namespace Plugins\Afm;

use Systems\SiteModule;

class Site extends SiteModule
{
  public function routes()
  {
    $this->route('afm', 'getIndex');
    $this->route('afm/caripasien', '_getCariPasien');
    $this->route('afm/caribio', '_getBio');
    $this->route('afm/carireg', '_getRegPasien');
    $this->route('afm/carifing', '_getUserPassFinger');
    $this->route('afm/carisep', '_getSepPatient');
  }

  public function getIndex()
  {
    echo $this->draw('index.html');
    exit();
  }

  public function _getCariPasien()
  {
    header("Content-Type: application/json");
    $header = apache_request_headers();
    $date = date('Y-m-d');
    $konten = trim(file_get_contents("php://input"));
    $decode = json_decode($konten, true);
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
    if($header[$this->settings->get('afm.x_header_token')] == false) {
        $response = array(
            'metadata' => array(
                'message' => 'Token kadaluarsa',
                'code' => 201
            )
        );
        http_response_code(201);
    } else if ($header[$this->settings->get('afm.x_header_token')] != $this->settings->get('afm.afm_token')){
    	$response = array(
            'metadata' => array(
                'message' => 'Token tidak sesuai',
                'code' => 201
            )
        );
        http_response_code(201);
    } else if ($header[$this->settings->get('afm.x_header_token')] == $this->settings->get('afm.afm_token')){
      $cari = isset_or($decode['nomor_kartu'],'');
      $field = isset_or($decode['kolom'], 'no_rkm_medis');
      $sql = "SELECT no_peserta , no_rkm_medis , no_ktp , no_tlp FROM pasien WHERE " . $field . " = '" . $cari . "'";
      $query = $this->db()->pdo()->prepare($sql);
      $query->execute();
      $cek_bookings = $query->fetch();
      if (!is_array($cek_bookings)) {
        $cek_bookings = [];
      }
      $list = array(
        'no_peserta' => isset_or($cek_bookings['no_peserta'],''),
        'no_rkm_medis' => isset_or($cek_bookings['no_rkm_medis'],''),
        'no_ktp' => isset_or($cek_bookings['no_ktp'],''),
        'no_tlp' => isset_or($cek_bookings['no_tlp'],''),
      );
      $response = array(
          'metadata' => array(
              'message' => "Ok",
              'code' => 200
          ),
          'response' => $list
      );
      http_response_code(200);
    }
    echo json_encode($response,true);
    exit();
  }

  public function _getBio()
  {
    header("Content-Type: application/json");
    $header = apache_request_headers();
    $date = date('Y-m-d');
    $konten = trim(file_get_contents("php://input"));
    $decode = json_decode($konten, true);
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
    if($header[$this->settings->get('afm.x_header_token')] == false) {
        $response = array(
            'metadata' => array(
                'message' => 'Token kadaluarsa',
                'code' => 201
            )
        );
        http_response_code(201);
    } else if ($header[$this->settings->get('afm.x_header_token')] != $this->settings->get('afm.afm_token')){
    	$response = array(
            'metadata' => array(
                'message' => 'Token tidak sesuai',
                'code' => 201
            )
        );
        http_response_code(201);
    } else if ($header[$this->settings->get('afm.x_header_token')] == $this->settings->get('afm.afm_token')){
      $list = array(
        'logo' => $this->settings->get('settings.logo'),
        'nama_instansi' => $this->settings->get('settings.nama_instansi'),
        'no_ktp' => ''
      );
      $response = array(
          'metadata' => array(
              'message' => "Ok",
              'code' => 200
          ),
          'response' => $list
      );
      http_response_code(200);
    }
    echo json_encode($response,true);
    exit();
  }

  public function _getRegPasien()
  {
    header("Content-Type: application/json");
    $header = apache_request_headers();
    $date = date('Y-m-d');
    $konten = trim(file_get_contents("php://input"));
    $decode = json_decode($konten, true);
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
    if($header[$this->settings->get('afm.x_header_token')] == false) {
        $response = array(
            'metadata' => array(
                'message' => 'Token kadaluarsa',
                'code' => 201
            )
        );
        http_response_code(201);
    } else if ($header[$this->settings->get('afm.x_header_token')] != $this->settings->get('afm.afm_token')){
    	$response = array(
            'metadata' => array(
                'message' => 'Token tidak sesuai',
                'code' => 201
            )
        );
        http_response_code(201);
    } else if ($header[$this->settings->get('afm.x_header_token')] == $this->settings->get('afm.afm_token')){
      $cari = $decode['nomor_kartu'];
      $reg = $this->db('reg_periksa')->where('no_rkm_medis',$cari)->where('tgl_registrasi',$date)->oneArray();
      if ($reg['no_rawat']) {
        $list = array(
          'no_rawat' => $reg['no_rawat'],
        );
        $response = array(
            'metadata' => array(
                'message' => "Ok",
                'code' => 200
            ),
            'response' => $list
        );
        http_response_code(200);
      } else {
        $list = array(
          'no_rawat' => 'Belum Terdaftar',
        );
        $response = array(
            'metadata' => array(
                'message' => "Ok",
                'code' => 200
            ),
            'response' => $list
        );
        http_response_code(200);
      }
    }
    echo json_encode($response,true);
    exit();
  }

  public function _getUserPassFinger()
  {
    header("Content-Type: application/json");
    $header = apache_request_headers();
    $date = date('Y-m-d');
    $konten = trim(file_get_contents("php://input"));
    $decode = json_decode($konten, true);
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
    if($header[$this->settings->get('afm.x_header_token')] == false) {
        $response = array(
            'metadata' => array(
                'message' => 'Token kadaluarsa',
                'code' => 201
            )
        );
        http_response_code(201);
    } else if ($header[$this->settings->get('afm.x_header_token')] != $this->settings->get('afm.afm_token')){
    	$response = array(
            'metadata' => array(
                'message' => 'Token tidak sesuai',
                'code' => 201
            )
        );
        http_response_code(201);
    } else if ($header[$this->settings->get('afm.x_header_token')] == $this->settings->get('afm.afm_token')){
      $list = array(
        'user' => $this->settings->get('afm.username_finger'),
        'pass' => $this->settings->get('afm.password_finger')
      );
      $response = array(
          'metadata' => array(
              'message' => "Ok",
              'code' => 200
          ),
          'response' => $list
      );
      http_response_code(200);
    }
    echo json_encode($response,true);
    exit();
  }

  public function _getSepPatient()
  {
    header("Content-Type: application/json");
    $header = apache_request_headers();
    $date = date('Y-m-d');
    $konten = trim(file_get_contents("php://input"));
    $decode = json_decode($konten, true);
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
    if($header[$this->settings->get('afm.x_header_token')] == false) {
        $response = array(
            'metadata' => array(
                'message' => 'Token kadaluarsa',
                'code' => 201
            )
        );
        http_response_code(201);
    } else if ($header[$this->settings->get('afm.x_header_token')] != $this->settings->get('afm.afm_token')){
    	$response = array(
            'metadata' => array(
                'message' => 'Token tidak sesuai',
                'code' => 201
            )
        );
        http_response_code(201);
    } else if ($header[$this->settings->get('afm.x_header_token')] == $this->settings->get('afm.afm_token')){
      $cari = $decode['nomor_kartu'];
      $cekSep = $this->db('bridging_sep')->where('no_kartu',$cari)->where('tglsep',$date)->oneArray();
      if ($cekSep['no_rawat']) {
        $list = array(
          'no_sep' => $cekSep['no_sep'],
        );
        $response = array(
            'metadata' => array(
                'message' => "Ok",
                'code' => 200
            ),
            'response' => $list
        );
        http_response_code(200);
      } else {
        $list = array(
          'no_sep' => 'empty',
        );
        $response = array(
            'metadata' => array(
                'message' => "Ok",
                'code' => 200
            ),
            'response' => $list
        );
        http_response_code(200);
      }
    }
    echo json_encode($response,true);
    exit();
  }
}
