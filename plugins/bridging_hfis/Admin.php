<?php

namespace Plugins\Bridging_Hfis;

use Systems\AdminModule;
use Systems\Lib\BpjsService;

class Admin extends AdminModule
{

    protected $consid;
    protected $secretkey;
    protected $user_key;
    protected $bpjsurl;

  public function init()
  {
    $this->consid = $this->settings->get('jkn_mobile.BpjsConsID');
    $this->secretkey = $this->settings->get('jkn_mobile.BpjsSecretKey');
    $this->bpjsurl = $this->settings->get('jkn_mobile.BpjsAntrianUrl');
    $this->user_key = $this->settings->get('jkn_mobile.BpjsUserKey');
  }

  public function navigation()
  {
    return [
      'Manage' => 'manage',
    ];
  }
  public function getManage()
  {
    $sub_modules = [
      ['name' => 'Bridging Update HFIS', 'url' => url([ADMIN, 'bridging_hfis', 'updatehfis']), 'icon' => 'cubes', 'desc' => 'Bridging HFIS dari SIMRS ke BPJS'],
      ['name' => 'Bridging Lihat HFIS', 'url' => url([ADMIN, 'bridging_hfis', 'lihathfis']), 'icon' => 'cubes', 'desc' => 'Bridging HFIS dari SIMRS ke BPJS'],
      ['name' => 'Jadwal Dokter', 'url' => url([ADMIN, 'bridging_hfis', 'jadwaldokter']), 'icon' => 'cubes', 'desc' => 'Bridging HFIS dari SIMRS ke BPJS'],
    ];
    return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
  }

  public function getUpdateHfis()
  {
    $this->_addHeaderFiles();
    $dokter = $this->db('maping_dokter_dpjpvclaim')->toArray();
    $poli = $this->db('maping_poli_bpjs')->group('kd_poli_bpjs')->toArray();
    return $this->draw('update.hfis.html', ['dokter' => $dokter, 'poli' => $poli]);
  }

  public function postUpdateBridgeHfis()
  {
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    if ($data !== null) {
      // Access the data and perform operations
      $kd_dokter = $data['kd_dokter'];
      $kd_poli = $data['kd_poli'];

      $kole = [];
      foreach ($data['jadwal'] as $value) {
        $hari = strtok($value['hari'], '|');
        $kole[] = array('hari' => $hari, 'buka' => $value['buka'], 'tutup' => $value['tutup']);
      };
      $jsonbody = array(
        'kodepoli' => $kd_poli,
        'kodesubspesialis' => $kd_poli,
        'kodedokter' => $kd_dokter,
        'jadwal' => $kole
      );
      // echo json_encode($jsonbody);
      $dataJson = json_encode($jsonbody);
      $ret = $this->postUpdateJadwalHfis($dataJson);
      $message = substr($ret, strpos($ret, "|") + 1);
      $code = strtok($ret, '|');
      if ($code == '200') {
        foreach ($data['jadwal'] as $value) {
          $dokterrs = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter_bpjs', $kd_dokter)->oneArray();
          $jenis = substr($value['hari'], strpos($value['hari'], "|") + 1);
          if ($jenis == '') {
            $jenis = 'pagi';
          }
          $polirs = $this->db('maping_poli_bpjs')->join('poliklinik', 'maping_poli_bpjs.kd_poli_rs = poliklinik.kd_poli')->where('maping_poli_bpjs.kd_poli_bpjs', $kd_poli)->like('poliklinik.nm_poli', '%'.$jenis)->oneArray();
          $hari = strtok($value['hari'], '|');
          $day = array(
            '0' => 'AKHAD',
            '1' => 'SENIN',
            '2' => 'SELASA',
            '3' => 'RABU',
            '4' => 'KAMIS',
            '5' => 'JUMAT',
            '6' => 'SABTU'
          );
          $namahari = $day[$hari];
          if ($value['buka'] == '' && $value['tutup'] == '') {
            $checkDulu = $this->db('jadwal')->where('kd_dokter', $dokterrs['kd_dokter'])->where('hari_kerja', $namahari)->where('kd_poli', $polirs['kd_poli'])->oneArray();
            if ($checkDulu) {
              $this->db('jadwal')->where('kd_dokter', $dokterrs['kd_dokter'])->where('hari_kerja', $namahari)->where('kd_poli', $polirs['kd_poli'])->delete();
            }
          } else {
            $checkJadwal = $this->db('jadwal')->where('kd_dokter', $dokterrs['kd_dokter'])->where('hari_kerja', $namahari)->where('kd_poli', $polirs['kd_poli'])->oneArray();
            if ($checkJadwal) {
              $this->db('jadwal')->where('kd_dokter', $dokterrs['kd_dokter'])->where('hari_kerja', $namahari)->where('kd_poli', $polirs['kd_poli'])->update([
                'jam_mulai' => $value['buka'],
                'jam_selesai' => $value['tutup'],
                'kuota' => $value['kuota'],
              ]);
            } else {
              $this->db('jadwal')->save([
                'kd_dokter' => $dokterrs['kd_dokter'],
                'hari_kerja' => $namahari,
                'kd_poli' => $polirs['kd_poli'],
                'jam_mulai' => $value['buka'],
                'jam_selesai' => $value['tutup'],
                'kuota' => $value['kuota'],
              ]);
            }
          }
        };
        // http_response_code(200);
        echo 'Berhasil Simpan';
      } else {
        // http_response_code(400);
        echo $ret;
      }
    } else {
      http_response_code(400);
      echo "Invalid JSON data";
    }
    exit();
  }

  function postUpdateJadwalHfis($data)
  {
    $url = $this->bpjsurl . 'jadwaldokter/updatejadwaldokter';
    $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, NULL);
    $json = json_decode($output, true);
    $code = $json['metadata']['code'];
    $message = $json['metadata']['message'];
    return $code.'|'.$message;
  }

  public function getLihatHfis()
  {
    $poli = $this->db('maping_poli_bpjs')->group('kd_poli_bpjs')->toArray();
    return $this->draw('jadwal.hfis.html', ['poli' => $poli]);
  }

  public function anyHfis()
  {
    $kodepoli = $_POST['poli'];
    $tanggal = $_POST['tgl'];
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consid . $this->secretkey . $tStamp;
    date_default_timezone_set($this->settings->get('settings.timezone'));

    $url = $this->bpjsurl . 'jadwaldokter/kodepoli/' . $kodepoli . '/tanggal/' . $tanggal;
    $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
    $json = json_decode($output, true);
    $code = $json['metadata']['code'];
    $message = $json['metadata']['message'];
    $stringDecrypt = stringDecrypt($key, $json['response']);
    $decompress = '""';
    if (!empty($stringDecrypt)) {
      $decompress = \LZCompressor\LZString::decompressFromEncodedURIComponent(($stringDecrypt));
    }
    // $response = [];
    if ($json['metadata']['code'] == '200') {
      $response = $decompress;
    }
    $response = json_decode($response, true);
    echo json_encode($response);
    exit();
  }

  public function getJadwalDokter()
  {
    $poli = $this->db('poliklinik')->where('status', '1')->toArray();
    $dokter = $this->db('dokter')->where('status', '1')->toArray();
    return $this->draw('jadwal.dokter.html', ['poli' => $poli, 'dokter' => $dokter]);
  }

  public function postListJadwalDokter()
  {
    if ($_POST['tgl'] == '') {
      $bulan = date('m');
      $tahun = date('Y');
    } else {
      $bulan = substr($_POST['tgl'], strpos($_POST['tgl'], "-") + 1);
      $tahun = strtok($_POST['tgl'], '-');
    }
    if ($_POST['poli'] == '-') {
      $jadwal = $this->db('jadwal_dokter')->where('kd_dokter', $_POST['dr'])->where('tahun', $tahun)->where('bulan', $bulan)->oneArray();
    } else {
      $jadwal = $this->db('jadwal_dokter')->where('kd_dokter', $_POST['dr'])->where('kd_poli', $_POST['poli'])->where('tahun', $tahun)->where('bulan', $bulan)->oneArray();
    }
    if ($jadwal) {
      $jadwal['nm_dokter'] = $this->core->getDokterInfo('nm_dokter', $jadwal['kd_dokter']);
      $jadwal['nm_poli'] = $this->core->getPoliklinikInfo('nm_poli', $jadwal['kd_poli']);
      $jadwal['code'] = '200';
      echo json_encode($jadwal);
    } else {
      $notif = ['code' => '404'];
      echo json_encode($notif);
    }
    exit();
  }

  public function getAllDate()
  {
    $list = array();
    $month = 12;
    $year = 2014;

    $days = cal_days_in_month( 0, 05,2024);
    for ($d = 1; $d <= $days; $d++) {
      $time = mktime(12, 0, 0, $month, $d, $year);
      if (date('m', $time) == $month)
        $list[] = date('Y-m-d-D', $time);
    }
  }

  public function getJavascript()
  {
    header('Content-type: text/javascript');
    echo $this->draw(MODULES . '/bridging_hfis/js/admin/scripts.js');
    exit();
  }

  private function _addHeaderFiles()
  {
    $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
    $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
    $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
    $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
    $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
    $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
    $this->core->addJS(url([ADMIN, 'bridging_hfis', 'javascript']), 'footer');
  }
}
