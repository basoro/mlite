<?php

namespace Plugins\Icare;

use Systems\AdminModule;
use Systems\Lib\BpjsService;

class Admin extends AdminModule
{

  public function init()
  {
    $this->consid = $this->settings->get('settings.BpjsConsID');
    $this->secretkey = $this->settings->get('settings.BpjsSecretKey');
    $this->user_key = $this->settings->get('settings.BpjsUserKey');
    $this->api_url = 'https://apijkn.bpjs-kesehatan.go.id/wsihs/api/rs/validate';
  }

  public function navigation()
  {
      return [
          'Kelola'   => 'manage',
      ];
  }

  public function getManage()
  {
    return $this->draw('manage.html');
  }

  public function getRiwayat($no_rawat)
  {

    $bridging_sep = $this->core->mysql('bridging_sep')->where('no_rawat', revertNoRawat($no_rawat))->oneArray();

    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consid . $this->secretkey . $tStamp;
    $data = [
      'param' => $bridging_sep['no_kartu'],
      'kodedokter' => intval($bridging_sep['kddpjppelayanan'])
    ];
    $data = json_encode($data);
    $signature = hash_hmac('sha256', $this->consid."&".$tStamp, $this->secretkey, true);
    $encodedSignature = base64_encode($signature);
    $url = $this->api_url;
    $output = BpjsService::postAplicare($url, $data, $this->consid, $this->secretkey, $this->user_key, $tStamp);
    $json = json_decode($output, true);
    if($json['metaData']['code'] == '200') {
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
        $decompress = decompress($stringDecrypt);
      }
    }
    $riwayat = json_decode($decompress, true);
    echo $this->draw('riwayat.html', ['url' => $riwayat['url']]);
    exit();
  }

}
