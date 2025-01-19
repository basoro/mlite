<?php

namespace Plugins\Icare;

use Systems\AdminModule;
use Systems\Lib\BpjsService;
use LZCompressor\LZString;

class Admin extends AdminModule
{

  public function init()
  {
    $this->consid = $this->settings->get('icare.consid');
    $this->secretkey = $this->settings->get('icare.secretkey');
    $this->user_key = $this->settings->get('icare.userkey');
    $this->api_url = $this->settings->get('icare.url');
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
    $icare['consid'] = $this->settings->get('icare.consid');
    $icare['secretkey'] = $this->settings->get('icare.secretkey');
    $icare['userkey'] = $this->settings->get('icare.userkey');
    return $this->draw('manage.html', ['icare' => $icare]);
  }

  public function postSaveSettings()
  {
      foreach ($_POST['icare'] as $key => $val) {
          $this->settings('icare', $key, $val);
      }

      $icare['url'] = $this->settings->get('icare.url');
      $icare['consid'] = $this->settings->get('icare.username');
      $icare['secretkey'] = $this->settings->get('icare.secretkey');
      $icare['userkey'] = $this->settings->get('icare.userkey');
      $this->notify('success', 'Pengaturan telah disimpan');
      redirect(url([ADMIN, 'icare', 'manage']));
  }

  public function getRiwayat($no_rawat, $status = null)
  {

    $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis',revertNoRawat($no_rawat));
    if($status == 'fktp') {
      $kd_dokter_bpjs = $this->db('maping_dokter_pcare')->where('kd_dokter', $this->core->getRegPeriksaInfo('kd_dokter',revertNoRawat($no_rawat)))->oneArray()['kd_dokter_pcare'];
    } else {
      $kd_dokter_bpjs = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $this->core->getRegPeriksaInfo('kd_dokter',revertNoRawat($no_rawat)))->oneArray()['kd_dokter_bpjs'];
    }
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consid . $this->secretkey . $tStamp;
    $data = [
      'param' => $this->core->getPasienInfo('no_peserta',$no_rkm_medis),
      'kodedokter' => intval($kd_dokter_bpjs)
    ];
    $data = json_encode($data);
    $signature = hash_hmac('sha256', $this->consid."&".$tStamp, $this->secretkey, true);
    $encodedSignature = base64_encode($signature);
    $url = $this->api_url;
    $output = BpjsService::postAplicare($url, $data, $this->consid, $this->secretkey, $this->user_key, $tStamp);
    $json = json_decode($output, true);
    $riwayat['url'] = '';
    if($json['metaData']['code'] == '200') {
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
        $decompress = \LZCompressor\LZString::decompressFromEncodedURIComponent(($stringDecrypt));
        $riwayat = json_decode($decompress, true);
      }
      $message = $json['metaData']['code'];
    } else {
      $message = $json['metaData']['message'];
    }
    echo $this->draw('riwayat.html', ['url' => $riwayat['url'], 'message' => $message]);
    exit();
  }

}
