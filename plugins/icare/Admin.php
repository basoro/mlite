<?php

namespace Plugins\Icare;

use Systems\AdminModule;
use Systems\Lib\BpjsService;
use Systems\Lib\PcareService;
use LZCompressor\LZString;

class Admin extends AdminModule
{
  private $consid;
  private $secretkey;
  private $user_key;
  private $api_url;
  private $api_url_pcare;
  private $usernameICare;
  private $passwordICare;
  private $kdAplikasi;

  public function init()
  {
    $this->consid = $this->settings->get('icare.consid');
    $this->secretkey = $this->settings->get('icare.secretkey');
    $this->user_key = $this->settings->get('icare.userkey');
    $this->api_url = $this->settings->get('icare.url');
    $this->api_url_pcare = $this->settings->get('icare.urlPCare');
    $this->usernameICare = $this->settings->get('icare.usernameICare');
    $this->passwordICare = $this->settings->get('icare.passwordICare');
    $this->kdAplikasi = '095';
  }

  public function navigation()
  {
      return [
          'Kelola'   => 'manage',
      ];
  }

  public function getManage()
  {
    $icare['url'] = $this->settings->get('icare.url');
    $icare['urlPCare'] = $this->settings->get('icare.urlPCare');
    $icare['consid'] = $this->settings->get('icare.consid');
    $icare['secretkey'] = $this->settings->get('icare.secretkey');
    $icare['userkey'] = $this->settings->get('icare.userkey');
    $icare['usernameICare'] = $this->settings->get('icare.usernameICare');
    $icare['passwordICare'] = $this->settings->get('icare.passwordICare');
    return $this->draw('manage.html', ['icare' => htmlspecialchars_array($icare)]);
  }

  public function postSaveSettings()
  {
      if ($this->core->getUserInfo('role') != 'admin') {
          $this->notify('failure', 'Anda tidak memiliki hak akses untuk halaman ini.');
          redirect(url([ADMIN, 'icare', 'manage']));
      }
      foreach ($_POST['icare'] as $key => $val) {
          $this->settings('icare', $key, $val);
      }

      $this->notify('success', 'Pengaturan telah disimpan');
      redirect(url([ADMIN, 'icare', 'manage']));
  }

  public function getRiwayat($no_rawat, $status = null)
  {

    $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis',revertNoRawat($no_rawat));
    if($status == 'fktp') {
      $dokterMap = $this->db('maping_dokter_pcare')
        ->where('kd_dokter', $this->core->getRegPeriksaInfo('kd_dokter', revertNoRawat($no_rawat)))
        ->oneArray();
      $kd_dokter_bpjs = (string) ($dokterMap['kd_dokter_pcare'] ?? '0');
    } else {
      $dokterMap = $this->db('maping_dokter_dpjpvclaim')
        ->where('kd_dokter', $this->core->getRegPeriksaInfo('kd_dokter', revertNoRawat($no_rawat)))
        ->oneArray();
      $kd_dokter_bpjs = (string) ($dokterMap['kd_dokter_bpjs'] ?? '0');
    }
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    if($status == 'fktp') {
      $key = $this->settings->get('pcare.consumerID') . $this->settings->get('pcare.consumerSecret') . $tStamp;
    } else {
      $key = $this->consid . $this->secretkey . $tStamp;
    }
    $data = [
      'param' => $this->core->getPasienInfo('no_peserta',$no_rkm_medis),
      'kodedokter' => intval($kd_dokter_bpjs)
    ];
    $data = json_encode($data);
    $signature = hash_hmac('sha256', $this->consid."&".$tStamp, $this->secretkey, true);
    $encodedSignature = base64_encode($signature);
    if($status == 'fktp') {
      $url = $this->api_url_pcare;
      $output = PcareService::postIcare($url, $data, $this->settings->get('pcare.consumerID'),  $this->settings->get('pcare.consumerSecret'),  $this->settings->get('pcare.consumerUserKey'), $this->usernameICare, $this->passwordICare, $this->kdAplikasi);      
    } else {
      $url = $this->api_url;
      $output = BpjsService::postAplicare($url, $data, $this->consid, $this->secretkey, $this->user_key, $tStamp);
    }
    $json = json_decode($output, true);
    // echo $output;
    if (!is_array($json)) {
      $json = [];
    }

    $meta = $json['metaData'] ?? [];
    $code = (string) ($meta['code'] ?? '');
    $message = $code !== '' ? $code : 'Gagal mengambil riwayat iCare.';
    $riwayat = ['url' => ''];

    if ($code === '200') {
      $response = $json['response'] ?? '';
      $stringDecrypt = $response !== '' ? stringDecrypt($key, $response) : '';
      if ($stringDecrypt !== '') {
        $decompress = \LZCompressor\LZString::decompressFromEncodedURIComponent($stringDecrypt);
        $parsed = json_decode((string) $decompress, true);
        if (is_array($parsed)) {
          $riwayat = $parsed;
        }
      }
    } else {
      $message = (string) ($meta['message'] ?? $message);
    }

    $urlRiwayat = (string) ($riwayat['url'] ?? '');
    echo $this->draw('riwayat.html', [
      'url' => htmlspecialchars($urlRiwayat, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
      'message' => htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
    ]);
    exit();
  }

}
