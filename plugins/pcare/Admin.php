<?php
namespace Plugins\Pcare;

use Systems\AdminModule;
use Systems\Lib\PcareService;

class Admin extends AdminModule
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
  }

  public function navigation()
  {
      return [
          'Kelola'   => 'manage',
          'Diagnosa' => 'refdiagnosa',
          'Dokter' => 'refdokter',
          'Kesadaran' => 'refkesadaran',
          'Kunjungan' => 'refkunjungan',
          'Pendaftaran' => 'refpendaftaran',
          'Peserta' => 'refpeserta',
          'Poli' => 'refpoli',
          'Provider' => 'refprovider',
          'Tindakan' => 'reftindakan',
          'Status Pulang' => 'refstatuspulang',
          'Spesialis' => 'refspesialis',
          'Settings' => 'settings'
      ];
  }

  public function getManage()
  {
      $parsedown = new \Systems\Lib\Parsedown();
      $readme_file = MODULES.'/pcare/Help.md';
      $readme =  $parsedown->text($this->tpl->noParse(file_get_contents($readme_file)));
      return $this->draw('manage.html', ['readme' => $readme]);
  }

  public function getSettings()
  {
      $this->_addHeaderFiles();
      $this->assign['title'] = 'Pengaturan PCare';
      $this->assign['pcare'] = htmlspecialchars_array($this->settings('pcare'));
      return $this->draw('settings.html', ['settings' => $this->assign]);
  }

  public function postSaveSettings()
  {
      foreach ($_POST['pcare'] as $key => $val) {
          $this->settings('pcare', $key, $val);
      }
      $this->notify('success', 'Pengaturan telah disimpan');
      redirect(url([ADMIN, 'pcare', 'settings']));
  }

  public function getRefDiagnosa()
  {
      return $this->draw('diagnosa.html');
  }

  public function getDiagnosa($keyword)
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'diagnosa/'.$keyword.'/0/500';
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getRefDokter()
  {
      return $this->draw('dokter.html');
  }

  public function getDokter()
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'dokter/0/500';
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getRefKesadaran()
  {
      return $this->draw('kesadaran.html');
  }

  public function getKesadaran()
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'kesadaran/';
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getRefKunjungan()
  {
      $this->_addHeaderFiles();
      return $this->draw('kunjungan.html');
  }

  public function getKunjungan($keyword, $param)
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'kunjungan/'.$keyword.'/'.$param;
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getAddKunjungan($kdDokter, $tglDaftar, $noKartu, $kdPoli)
  {
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consumerID . $this->consumerSecret . $tStamp;

    $data = [
      /*
      'noKunjungan' => null,
      'noKartu' => $noKartu,
      'tglDaftar' => $tglDaftar,
      'kdPoli' => $kdPoli,
      'keluhan' => 'keluhan',
      'kdSadar' => '01',
      'sistole' => 120,
      'diastole' => 80,
      'beratBadan' => 50,
      'tinggiBadan' => 170,
      'respRate' => 70,
      'heartRate' => 80,
      'lingkarPerut' => 36,
      'terapi' => 'catatan',
      'kdStatusPulang' => '3',
      'tglPulang' => $tglDaftar,
      'kdDokter' => $kdDokter,
      'kdDiag1' => 'K04.1',
      'kdDiag2' => null,
      'kdDiag3' => null,
      'kdPoliRujukInternal' => null,
      'rujukLanjut' => [
          'kdppk' => null,
          'tglEstRujuk' => null,
          'subSpesialis' => [
              'kdSubSpesialis1' => null,
              'kdSarana' => null
          ],
          'khusus' => null
      ],
      'kdTacc' => -1,
      'alasanTacc' => null
      */
      'noKunjungan' => null,
      'noKartu' => '0002058271953',
      'tglDaftar' => '11-11-2022',
      'kdPoli' => '001',
      'keluhan' => 'keluhan',
      'kdSadar' => '01',
      'sistole' => 80,
      'diastole' => 80,
      'beratBadan' => 50,
      'tinggiBadan' => 170,
      'respRate' => 70,
      'heartRate' => 80,
      'lingkarPerut' => 36,
      'terapi' => 'catatan',
      'kdStatusPulang' => '3',
      'tglPulang' => '11-11-2022',
      'kdDokter' => '199714',
      'kdDiag1' => 'O82',
      'kdDiag2' => null,
      'kdDiag3' => null,
      'kdPoliRujukInternal' => null,
      'rujukLanjut' => null,
      'kdTacc' => -1,
      'alasanTacc' => null
    ];

    $data = json_encode($data);
    //echo $data;

    $url = $this->api_url . 'kunjungan';
    $output = PcareService::post($url, $data, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
    $json = json_decode($output, true);
    //echo json_encode($json);

    $code = $json['metaData']['code'];
    $message = $json['metaData']['message'];
    $stringDecrypt = stringDecrypt($key, $json['response']);
    //echo $stringDecrypt;
    $decompress = '""';
    if (!empty($stringDecrypt)) {
        $decompress = decompress($stringDecrypt);
    }
    if ($json != null) {
        echo '{
            "metaData": {
              "code": "' . $code . '",
              "message": "' . $message . '"
            },
            "response": ' . $decompress . '}';
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

  public function getEditKunjungan($noKunjungan, $noKartu, $kdSadar, $tglPulang, $kdDokter)
  {
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consumerID . $this->consumerSecret . $tStamp;

    $data = [
      'noKunjungan' => $noKunjungan,
      'noKartu' => $noKartu,
      'keluhan' => 'keluhan',
      'kdSadar' => $kdSadar,
      'sistole' => 80,
      'diastole' => 80,
      'beratBadan' => 50,
      'tinggiBadan' => 170,
      'respRate' => 70,
      'heartRate' => 80,
      'lingkarPerut' => 36,
      'terapi' => 'catatan',
      'kdStatusPulang' => '3',
      'tglPulang' => $tglPulang,
      'kdDokter' => $kdDokter,
      'kdDiag1' => 'K04.1',
      'kdDiag2' => null,
      'kdDiag3' => null,
      'kdPoliRujukInternal' => null,
      'rujukLanjut' => [
          'kdppk' => null,
          'tglEstRujuk' => null,
          'subSpesialis' => [
              'kdSubSpesialis1' => null,
              'kdSarana' => null
          ],
          'khusus' => null
      ],
      'kdTacc' => -1,
      'alasanTacc' => null
    ];

    $data = json_encode($data);
    //echo $data;

    $url = $this->api_url . 'kunjungan';
    $output = PcareService::put($url, $data, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
    $json = json_decode($output, true);
    //echo json_encode($json);

    $code = $json['metaData']['code'];
    $message = $json['metaData']['message'];
    $stringDecrypt = stringDecrypt($key, $json['response']);
    //echo $stringDecrypt;
    $decompress = '""';
    if (!empty($stringDecrypt)) {
        $decompress = decompress($stringDecrypt);
    }
    if ($json != null) {
        echo '{
            "metaData": {
              "code": "' . $code . '",
              "message": "' . $message . '"
            },
            "response": ' . $decompress . '}';
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

  public function getDelKunjungan($noKunjungan)
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'kunjungan/'.$noKunjungan;
      $output = PcareService::delete($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getRefPeserta()
  {
      $this->_addHeaderFiles();
      return $this->draw('peserta.html');
  }

  public function getPeserta($noKartu)
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'peserta/'.$noKartu;
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getByJenisKartu($jeniskartu, $nomor)
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'peserta/'.$jeniskartu.'/'.$nomor;
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getRefPoli()
  {
      return $this->draw('poli.html');
  }

  public function getPoli()
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'poli/fktp/0/500';
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getRefProvider()
  {
      return $this->draw('provider.html');
  }

  public function getProvider()
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'provider/0/500';
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getRefStatusPulang()
  {
      return $this->draw('status.pulang.html');
  }

  public function getStatusPulang($status='false')
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'statuspulang/rawatInap/'.$status;
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getRefPendaftaran()
  {
      $this->_addHeaderFiles();
      return $this->draw('pendaftaran.html');
  }

  public function getAddPendaftaran($kdProviderPeserta, $tglDaftar, $noKartu, $kdPoli)
  {
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consumerID . $this->consumerSecret . $tStamp;

    $data = [
      'kdProviderPeserta' => $kdProviderPeserta,
      'tglDaftar' => $tglDaftar,
      'noKartu' => $noKartu,
      'kdPoli' => $kdPoli,
      'keluhan' => null,
      'kunjSakit' => true,
      'sistole' => 0,
      'diastole' => 0,
      'beratBadan' => 0,
      'tinggiBadan' => 0,
      'respRate' => 0,
      'lingkarPerut' => 0,
      'heartRate' => 0,
      'rujukBalik' => 0,
      'kdTkp' => '10'
    ];

    $data = json_encode($data);
    //echo $data;

    $url = $this->api_url . 'pendaftaran';
    $output = PcareService::post($url, $data, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
    $json = json_decode($output, true);
    //echo json_encode($json);

    $code = $json['metaData']['code'];
    $message = $json['metaData']['message'];
    $stringDecrypt = stringDecrypt($key, $json['response']);
    $decompress = '""';
    if (!empty($stringDecrypt)) {
        $decompress = decompress($stringDecrypt);
    }
    if ($json != null) {
        echo '{
            "metaData": {
              "code": "' . $code . '",
              "message": "' . $message . '"
            },
            "response": ' . $decompress . '}';
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

  public function getGetPendaftaranNoUrut($noUrut, $tglDaftar)
  {
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consumerID . $this->consumerSecret . $tStamp;

    $url = $this->api_url . 'pendaftaran/noUrut/'.$noUrut.'/tglDaftar/'.$tglDaftar;
    $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
    $json = json_decode($output, true);
    //echo json_encode($json);

    $code = $json['metaData']['code'];
    $message = $json['metaData']['message'];
    $stringDecrypt = stringDecrypt($key, $json['response']);
    $decompress = '""';
    if (!empty($stringDecrypt)) {
        $decompress = decompress($stringDecrypt);
    }
    if ($json != null) {
        echo '{
            "metaData": {
              "code": "' . $code . '",
              "message": "' . $message . '"
            },
            "response": ' . $decompress . '}';
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

  public function getGetPendaftaranProvider($tglDaftar)
  {
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consumerID . $this->consumerSecret . $tStamp;

    $url = $this->api_url . 'pendaftaran/tglDaftar/'.$tglDaftar.'/0/500';
    $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
    $json = json_decode($output, true);
    //echo json_encode($json);

    $code = $json['metaData']['code'];
    $message = $json['metaData']['message'];
    $stringDecrypt = stringDecrypt($key, $json['response']);
    $decompress = '""';
    if (!empty($stringDecrypt)) {
        $decompress = decompress($stringDecrypt);
    }
    if ($json != null) {
        echo '{
            "metaData": {
              "code": "' . $code . '",
              "message": "' . $message . '"
            },
            "response": ' . $decompress . '}';
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

  public function getDelPendaftaran($noKartu, $tglDaftar, $noUrut, $kdPoli)
  {
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consumerID . $this->consumerSecret . $tStamp;

    $url = $this->api_url . 'pendaftaran/peserta/'.$noKartu.'/tglDaftar/'.$tglDaftar.'/noUrut/'.$noUrut.'/kdPoli/'.$kdPoli;
    $output = PcareService::delete($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
    $json = json_decode($output, true);
    //echo json_encode($json);

    $code = $json['metaData']['code'];
    $message = $json['metaData']['message'];
    $stringDecrypt = stringDecrypt($key, $json['response']);
    $decompress = '""';
    if (!empty($stringDecrypt)) {
        $decompress = decompress($stringDecrypt);
    }
    if ($json != null) {
        echo '{
            "metaData": {
              "code": "' . $code . '",
              "message": "' . $message . '"
            },
            "response": ' . $decompress . '}';
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

  public function getRefSpesialis()
  {
      $this->_addHeaderFiles();
      return $this->draw('spesialis.html');
  }

  public function getSpesialis()
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'spesialis/';
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getSubSpesialis($subspesialis)
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'spesialis/'.$subspesialis.'/subspesialis';
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getSarana()
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'spesialis/sarana';
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getFaskesSpesialis($subspesialis, $sarana, $date)
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'spesialis/rujuk/subspesialis/'.$subspesialis.'/sarana/'.$sarana.'/tglEstRujuk/'.$date;
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getKhusus()
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'spesialis/khusus';
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getFaskesKhusus($kodeKhusus, $subspesialis, $noKartu, $date)
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'spesialis/rujuk/khusus/'.$kodeKhusus.'/noKartu/'.$noKartu.'/tglEstRujuk/'.$date;
      if($kodeKhusus == 'THA') {
        $url = $this->api_url.'spesialis/rujuk/khusus/'.$kodeKhusus.'/subspesialis/'.$subspesialis.'/noKartu/'.$noKartu.'/tglEstRujuk/'.$date;
      }
      if($kodeKhusus == 'HEM') {
        $url = $this->api_url.'spesialis/rujuk/khusus/'.$kodeKhusus.'/subspesialis/'.$subspesialis.'/noKartu/'.$noKartu.'/tglEstRujuk/'.$date;
      }
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getRefTindakan()
  {
      $this->_addHeaderFiles();
      return $this->draw('tindakan.html');
  }

  public function getTindakanKunjungan($noKunjungan)
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'tindakan/kunjungan/'.$noKunjungan;
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getTindakanReferensi($kdTkp)
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'tindakan/kdTkp/'.$kdTkp.'/0/500';
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getAddTindakan($noKunjungan, $kdTindakan)
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $data = [
        'kdTindakanSK' => 0,
        'noKunjungan' => $noKunjungan,
        'kdTindakan' => $kdTindakan,
        'biaya' => 1000,
        'keterangan' => null,
        'hasil' => 1
      ];

      $data = json_encode($data);
      //echo $data;

      $url = $this->api_url . 'tindakan';
      $output = PcareService::post($url, $data, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getEditTindakan($kdTindakanSK, $noKunjungan, $kdTindakan)
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $data = [
        'kdTindakanSK' => $kdTindakanSK,
        'noKunjungan' => $noKunjungan,
        'kdTindakan' => $kdTindakan,
        'biaya' => 0,
        'keterangan' => null,
        'hasil' => 0
      ];

      $data = json_encode($data);
      //echo $data;

      $url = $this->api_url . 'tindakan';
      $output = PcareService::put($url, $data, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getDelTindakan($kdTindakanSK, $noKunjungan)
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'tindakan/'.$kdTindakanSK.'/kunjungan/'.$noKunjungan;
      $output = PcareService::delete($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getRefObat()
  {
      $this->_addHeaderFiles();
      return $this->draw('obat.html');
  }

  public function getObatReferensi($dpho)
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'obat/dpho/'.$dpho.'/0/500';
      $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getAddObat($noKunjungan, $kdObat, $signa1, $signa2, $jumlah)
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $data = [
        'kdObatSK' => 0,
        'noKunjungan' => $noKunjungan,
        'racikan' => false,
        'kdRacikan' => null,
        'obatDPHO' => true,
        'kdObat' => $kdObat,
        'signa1' => intval($signa1),
        'signa2' => intval($signa2),
        'jmlObat' => intval($jumlah),
        'jmlPermintaan' => 1,
        'nmObatNonDPHO' => '-'
      ];

      $data = json_encode($data);
      echo $data;

      $url = $this->api_url . 'obat/kunjungan';
      $output = PcareService::post($url, $data, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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

  public function getDelObat($kdObatSK, $noKunjungan)
  {
      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url.'obat/'.$kdObatSK.'/kunjungan/'.$noKunjungan;
      $output = PcareService::delete($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          echo '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
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


  public function getBridgingPCare($no_rkm_medis, $date)
  {
    $date = date('Y-m-d', strtotime($date));
    $pendaftaran = $this->db('reg_periksa')->where('no_rkm_medis', $no_rkm_medis)->like('tgl_registrasi', $date)->oneArray();
    $bridging_pcare = $this->db('mlite_bridging_pcare')
      ->join('pasien', 'pasien.no_rkm_medis=mlite_bridging_pcare.no_rkm_medis')
      ->where('mlite_bridging_pcare.no_rkm_medis', $no_rkm_medis)
      ->toArray();
    echo $this->draw('bridgingpcare.html', ['pasien' => $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray(), 'pemeriksaan' => $this->db('pemeriksaan_ralan')->where('no_rawat', $pendaftaran['no_rawat'])->oneArray(), 'bridging_pcare' => $bridging_pcare]);
    exit();
  }

  public function getBridgingPCarePendaftaranTampil($no_rkm_medis)
  {
    $bridging_pcare = $this->db('mlite_bridging_pcare')
      ->join('pasien', 'pasien.no_rkm_medis=mlite_bridging_pcare.no_rkm_medis')
      ->where('mlite_bridging_pcare.no_rkm_medis', $no_rkm_medis)
      ->toArray();
    echo $this->draw('bridgingpcare.pendaftaran.tampil.html', ['bridging_pcare' => $bridging_pcare]);
    exit();
  }

  public function getBridgingPCareKunjunganTampil($no_rkm_medis)
  {
    $bridging_pcare = $this->db('mlite_bridging_pcare')
      ->join('pasien', 'pasien.no_rkm_medis=mlite_bridging_pcare.no_rkm_medis')
      ->where('terapi', '<>', '')
      ->where('mlite_bridging_pcare.no_rkm_medis', $no_rkm_medis)
      ->toArray();
    echo $this->draw('bridgingpcare.kunjungan.tampil.html', ['bridging_pcare' => $bridging_pcare]);
    exit();
  }

  public function getBridgingPCareRujukanTampil($no_rkm_medis)
  {
    $bridging_pcare = $this->db('mlite_bridging_pcare')
      ->join('pasien', 'pasien.no_rkm_medis=mlite_bridging_pcare.no_rkm_medis')
      ->where('kode_faskeskhusus', '<>', '')
      ->orWhere('kode_ppk', '<>', '')
      ->where('mlite_bridging_pcare.no_rkm_medis', $no_rkm_medis)
      ->toArray();
    echo $this->draw('bridgingpcare.rujukan.tampil.html', ['bridging_pcare' => $bridging_pcare]);
    exit();
  }

  public function getBridgingPCareRujukanCetak($nomor_kunjungan)
  {
    $settings = $this->settings('pcare');
    $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));

    $bridging_pcare = $this->db('mlite_bridging_pcare')
      ->join('pasien', 'pasien.no_rkm_medis=mlite_bridging_pcare.no_rkm_medis')
      ->where('nomor_kunjungan', $nomor_kunjungan)
      ->oneArray();
    echo $this->draw('bridgingpcare.rujukan.cetak.html', ['bridging_pcare' => $bridging_pcare, 'umur' => $this->hitungUmur($bridging_pcare['tgl_lahir'])]);
    exit();
  }

  public function getBridgingPCareTindakan($nomor_kunjungan)
  {
    $this->_addHeaderFiles();
    $bridging_pcare = $this->db('mlite_bridging_pcare')
      ->join('pasien', 'pasien.no_rkm_medis=mlite_bridging_pcare.no_rkm_medis')
      ->where('nomor_kunjungan', $nomor_kunjungan)
      ->oneArray();

    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consumerID . $this->consumerSecret . $tStamp;

    $url = $this->api_url.'tindakan/kunjungan/'.$nomor_kunjungan;
    $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
    $json = json_decode($output, true);
    // echo json_encode($json);

    $code = $json['metaData']['code'];
    $message = $json['metaData']['message'];
    $decompress = '""';
    $data_tindakan = [];
    if($code == '200') {
      $stringDecrypt = stringDecrypt($key, $json['response']);
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
          $data_tindakan = json_decode($decompress,true);
      }  
    }

    $ref_tindakan = '[
      {
        "kdTindakan": "01001",
        "nmTindakan": "Rawat jalan di Poliklinik Umum / KIA - KB/Gigi setiap kali kunjungan ",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "01005",
        "nmTindakan": "Pelayanan KB : Pemasangan IUD / Implant\r\n",
        "maxTarif": 100000,
        "withValue": false
      },
      {
        "kdTindakan": "01006",
        "nmTindakan": "Pelayanan KB : Suntik\r\n",
        "maxTarif": 15000,
        "withValue": false
      },
      {
        "kdTindakan": "01023",
        "nmTindakan": "Pelayanan ANC 1 (Satu)",
        "maxTarif": 50000,
        "withValue": false
      },
      {
        "kdTindakan": "01024",
        "nmTindakan": "Pelayanan ANC 2 (Dua)",
        "maxTarif": 50000,
        "withValue": false
      },
      {
        "kdTindakan": "01025",
        "nmTindakan": "Pelayanan ANC 3 (Tiga)",
        "maxTarif": 50000,
        "withValue": false
      },
      {
        "kdTindakan": "01026",
        "nmTindakan": "Pelayanan ANC 4 (Empat)",
        "maxTarif": 50000,
        "withValue": false
      },
      {
        "kdTindakan": "01027",
        "nmTindakan": "Pelayanan PNC 1 (Satu)",
        "maxTarif": 25000,
        "withValue": false
      },
      {
        "kdTindakan": "01028",
        "nmTindakan": "Pelayanan PNC 2 (Dua)",
        "maxTarif": 25000,
        "withValue": false
      },
      {
        "kdTindakan": "01029",
        "nmTindakan": "Pelayanan PNC 3 (Tiga)",
        "maxTarif": 25000,
        "withValue": false
      },
      {
        "kdTindakan": "01030",
        "nmTindakan": "Pelayanan PNC 4 (Empat)",
        "maxTarif": 25000,
        "withValue": false
      },
      {
        "kdTindakan": "02004",
        "nmTindakan": "Pelayanan pra-rujukan pada komplikasi kebidanan dan neonatal",
        "maxTarif": 125000,
        "withValue": false
      },
      {
        "kdTindakan": "03005",
        "nmTindakan": "Perawatan Luka tanpa jahitan / ganti verban",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "03052",
        "nmTindakan": "Tampon Hidung",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "03092",
        "nmTindakan": "Pemasangan/ pengangkatan IUD oleh Bidan",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "03095",
        "nmTindakan": "Injeksi KB",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "03096",
        "nmTindakan": "Kontrol IUD",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "03113",
        "nmTindakan": "Skintest",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "03122",
        "nmTindakan": "MOP / Vasektomi",
        "maxTarif": 350000,
        "withValue": false
      },
      {
        "kdTindakan": "04002",
        "nmTindakan": "Tambalan Composite",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "04003",
        "nmTindakan": "Tambalan GIC",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "04010",
        "nmTindakan": "Kontrol Pasca Tindakan",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "04011",
        "nmTindakan": "Pencabutan gigi tetap dengan anestesi topikal",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "04014",
        "nmTindakan": "Hecting 1-3 jahitan",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "04015",
        "nmTindakan": "Buka jahitan / post pencabutan gigi dengan tindakan",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "04017",
        "nmTindakan": "Kontrol post pencabutan gigi",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "04024",
        "nmTindakan": "Kontrol Pasca Tindakan",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "09001",
        "nmTindakan": "Evakuasi medis / Ambulans Darat\r\n",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "09002",
        "nmTindakan": "Evakuasi medis / Ambulans Air\r\n",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "05022",
        "nmTindakan": "Ureum",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "05023",
        "nmTindakan": "Kreatinin",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "05049",
        "nmTindakan": "Gula Darah Puasa (GDP) - PRB/Prolanis",
        "maxTarif": 17500,
        "withValue": false
      },
      {
        "kdTindakan": "05051",
        "nmTindakan": "HbA1c - PRB/Prolanis",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "05052",
        "nmTindakan": "Microalbuminaria",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "05053",
        "nmTindakan": "Kolesterol Total",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "05054",
        "nmTindakan": "Kolesterol LDL",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "05055",
        "nmTindakan": "Kolesterol HDL",
        "maxTarif": 0,
        "withValue": false
      },
      {
        "kdTindakan": "05056",
        "nmTindakan": "Trigliserida",
        "maxTarif": 0,
        "withValue": false
      }
    ]';

    $ref_tindakan = json_decode($ref_tindakan,true);

    echo $this->draw('bridgingpcare.tindakan.html', ['bridging_pcare' => $bridging_pcare, 'data_tindakan' => $data_tindakan, 'ref_tindakan' => $ref_tindakan]);
    exit();
  }

  public function getBridgingPCareObat($nomor_kunjungan)
  {
    $this->_addHeaderFiles();
    $bridging_pcare = $this->db('mlite_bridging_pcare')
      ->join('pasien', 'pasien.no_rkm_medis=mlite_bridging_pcare.no_rkm_medis')
      ->where('nomor_kunjungan', $nomor_kunjungan)
      ->oneArray();

    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consumerID . $this->consumerSecret . $tStamp;

    $url = $this->api_url.'obat/kunjungan/'.$nomor_kunjungan;
    $output = PcareService::get($url, NULL, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
    $json = json_decode($output, true);
    //echo json_encode($json);

    $code = $json['metaData']['code'];
    $message = $json['metaData']['message'];

    $decompress = '""';
    $data_obat = [];
    if($code == '200') {
      $stringDecrypt = stringDecrypt($key, $json['response']);
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
          $data_obat = json_decode($decompress,true);
      }  
    }

    echo $this->draw('bridgingpcare.obat.html', ['bridging_pcare' => $bridging_pcare, 'data_obat' => $data_obat]);
    exit();
  }

  public function postBridgingPCareSave()
  {

    $kunjSakit = true;
    if($_POST['kunjSakit'] == 'false') {
      $kunjSakit = false;
    }

    $diagnosa2 = 'null';
    if($_POST['getDiagnosa2'] !=''){
      $diagnosa2 = strtok($_POST['getDiagnosa2'], ':');
    }

    $diagnosa3 = 'null';
    if($_POST['getDiagnosa3'] !=''){
      $diagnosa3 = strtok($_POST['getDiagnosa3'], ':');
    }

    $data = [
      'kdProviderPeserta' => $_POST['kdProviderPeserta'],
      'tglDaftar' => $_POST['tglDaftar'],
      'noKartu' => $_POST['noKartu'],
      'kdPoli' => strtok($_POST['getPoli'], ':'),
      'keluhan' => $_POST['keluhan'],
      'kunjSakit' => $kunjSakit,
      'sistole' => 0,
      'diastole' => 0,
      'beratBadan' => 0,
      'tinggiBadan' => 0,
      'respRate' => 0,
      'lingkarPerut' => 0,
      'heartRate' => 0,
      'rujukBalik' => 0,
      'kdTkp' => $_POST['kode_tkp']
    ];

    $data = json_encode($data);

    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consumerID . $this->consumerSecret . $tStamp;

    $url = $this->api_url . 'pendaftaran';
    $output = PcareService::post($url, $data, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
    $json = json_decode($output, true);
    //echo json_encode($json);

    $code = $json['metaData']['code'];
    $message = $json['metaData']['message'];
    $stringDecrypt = stringDecrypt($key, $json['response']);
    $decompress = '""';
    if (!empty($stringDecrypt)) {
        $decompress = decompress($stringDecrypt);
        //echo $decompress;
    }
    if ($json != null) {
        $data = '{
            "metaData": {
              "code": "' . $code . '",
              "message": "' . $message . '"
            },
            "response": ' . $decompress . '}';
        //echo $data;
        $data = json_decode($data, true);

        $noUrut = $data['response']['message'];

    } else {
        echo '{
            "metaData": {
              "code": "5000",
              "message": "ERROR"
            },
            "response": "ADA KESALAHAN ATAU SAMBUNGAN KE SERVER BPJS TERPUTUS."}';

    }

    if($_POST['rujukanlanjut'] == 'false') {
      $data = [
        'noKunjungan' => null,
        'noKartu' => $_POST['noKartu'],
        'tglDaftar' => $_POST['tglDaftar'],
        'kdPoli' => strtok($_POST['getPoli'], ':'),
        'keluhan' => $_POST['keluhan'],
        'kdSadar' => strtok($_POST['getKesadaran'], ':'),
        'sistole' => intval($_POST['sistole']),
        'diastole' => intval($_POST['diastole']),
        'beratBadan' => intval($_POST['berat']),
        'tinggiBadan' => intval($_POST['tinggi']),
        'respRate' => intval($_POST['respirasi']),
        'heartRate' => intval($_POST['nadi']),
        'lingkarPerut' => intval($_POST['lingkar_perut']),
        'terapi' => $_POST['terapi'],
        'kdStatusPulang' => strtok($_POST['getStatusPulang'], ':'),
        'tglPulang' => $_POST['tglPulang'],
        'kdDokter' => strtok($_POST['getDokter'], ':'),
        'kdDiag1' => strtok($_POST['getDiagnosa1'], ':'),
        'kdDiag2' => ($diagnosa2 === 'null') ? null : $diagnosa2,
        'kdDiag3' => ($diagnosa3 === 'null') ? null : $diagnosa3,
        'kdPoliRujukInternal' => null,
        'rujukLanjut' => null,
        'kdTacc' => -1,
        'alasanTacc' => null
      ];

      $data = json_encode($data);

      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url . 'kunjungan';
      $output = PcareService::post($url, $data, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
          //echo $decompress;
      }
      if ($json != null) {
          $data = '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';

          //echo $data;
          $data = json_decode($data, true);

          $noKunjungan = $data['response'][0]['message'];

      } else {
          echo '{
              "metaData": {
                "code": "5000",
                "message": "ERROR"
              },
              "response": "ADA KESALAHAN ATAU SAMBUNGAN KE SERVER BPJS TERPUTUS."}';

      }

    }

    if($_POST['rujukanlanjut'] == 'true' && $_POST['rujukankhusus'] == 'false') {
      $data = [
        'noKunjungan' => null,
        'noKartu' => $_POST['noKartu'],
        'tglDaftar' => $_POST['tglDaftar'],
        'kdPoli' => strtok($_POST['getPoli'], ':'),
        'keluhan' => $_POST['keluhan'],
        'kdSadar' => strtok($_POST['getKesadaran'], ':'),
        'sistole' => intval($_POST['sistole']),
        'diastole' => intval($_POST['diastole']),
        'beratBadan' => intval($_POST['berat']),
        'tinggiBadan' => intval($_POST['tinggi']),
        'respRate' => intval($_POST['respirasi']),
        'heartRate' => intval($_POST['nadi']),
        'lingkarPerut' => intval($_POST['lingkar_perut']),
        'terapi' => $_POST['terapi'],
        'kdStatusPulang' => strtok($_POST['getStatusPulang'], ':'),
        'tglPulang' => $_POST['tglPulang'],
        'kdDokter' => strtok($_POST['getDokter'], ':'),
        'kdDiag1' => strtok($_POST['getDiagnosa1'], ':'),
        'kdDiag2' => ($diagnosa2 === 'null') ? null : $diagnosa2,
        'kdDiag3' => ($diagnosa3 === 'null') ? null : $diagnosa3,
        'kdPoliRujukInternal' => null,
        'rujukLanjut' => [
            'kdppk' => strtok($_POST['getReferensiFaskesSpesialis'], ':'),
            'tglEstRujuk' => $_POST['tglEstRujuk'],
            'subSpesialis' => [
                'kdSubSpesialis1' => strtok($_POST['getReferensiSubSpesialis'], ':'),
                'kdSarana' => null
            ],
            'khusus' => null
        ],
        'kdTacc' => intval(strtok($_POST['getTACC'], ':')),
        'alasanTacc' => substr($_POST['alasanTacc'], strpos($_POST['alasanTacc'], ': ') + 1)
      ];

      $data = json_encode($data);

      date_default_timezone_set('UTC');
      $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
      $key = $this->consumerID . $this->consumerSecret . $tStamp;

      $url = $this->api_url . 'kunjungan';
      $output = PcareService::post($url, $data, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          $data = '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
          //echo $data;
          $data = json_decode($data, true);

          $noKunjungan = $data['response'][0]['message'];

      } else {
          echo '{
              "metaData": {
                "code": "5000",
                "message": "ERROR"
              },
              "response": "ADA KESALAHAN ATAU SAMBUNGAN KE SERVER BPJS TERPUTUS."}';

      }

    }

    if($_POST['rujukankhusus'] == 'true') {
      echo 'rujukan khusus';
      $data = [
        'noKunjungan' => null,
        'noKartu' => $_POST['noKartu'],
        'tglDaftar' => $_POST['tglDaftar'],
        'kdPoli' => null,
        'keluhan' => $_POST['keluhan'],
        'kdSadar' => strtok($_POST['getKesadaran'], ':'),
        'sistole' => 0,
        'diastole' => 0,
        'beratBadan' => 0,
        'tinggiBadan' => 0,
        'respRate' => 0,
        'heartRate' => 0,
        'lingkarPerut' => 0,
        'terapi' => $_POST['terapi'],
        'kdStatusPulang' => strtok($_POST['getStatusPulang'], ':'),
        'tglPulang' => $_POST['tglPulang'],
        'kdDokter' => strtok($_POST['getDokter'], ':'),
        'kdDiag1' => strtok($_POST['getDiagnosa1'], ':'),
        'kdDiag2' => ($diagnosa2 === 'null') ? null : $diagnosa2,
        'kdDiag3' => ($diagnosa3 === 'null') ? null : $diagnosa3,
        'kdPoliRujukInternal' => null,
        'rujukLanjut' => [
            'tglEstRujuk' => $_POST['tglEstRujuk'],
            'kdppk' => strtok($_POST['getFaskesKhusus'], ':'),
            'subSpesialis' => null,
            'khusus' => [
              'kdKhusus' => strtok($_POST['getReferensiKhusus'], ':'),
              'kdSubSpesialis' => null,
              'catatan' => $_POST['catatan']
            ]
        ],
        'kdTacc' => 0,
        'alasanTacc' => null
      ];

      $data = json_encode($data);

      $url = $this->api_url . 'kunjungan';
      $output = PcareService::post($url, $data, $this->consumerID, $this->consumerSecret, $this->consumerUserKey, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
      $json = json_decode($output, true);
      //echo json_encode($json);

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
      }
      if ($json != null) {
          $data = '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';
          //echo $data;
          $data = json_decode($data, true);

          $noKunjungan = $data['response'][0]['message'];

      } else {
          echo '{
              "metaData": {
                "code": "5000",
                "message": "ERROR"
              },
              "response": "ADA KESALAHAN ATAU SAMBUNGAN KE SERVER BPJS TERPUTUS."}';

      }
    }

    if($noUrut !="") {
      $this->db('mlite_bridging_pcare')->save([
        "no_rawat" => $_POST['id_pendaftaran'],
        "no_rkm_medis" => $_POST['id_pasien'],
        "tgl_daftar" => $_POST['tglDaftar'],
        "nomor_kunjungan" => $noKunjungan,
        "kode_provider_peserta" => $_POST['kdProviderPeserta'],
        "nomor_jaminan" => $_POST['noKartu'],
        "kode_poli" => strtok($_POST['getPoli'], ':'),
        "nama_poli" => substr($_POST['getPoli'], strpos($_POST['getPoli'], ': ') + 1),
        "kunjungan_sakit" => $_POST['kunjSakit'],
        "sistole" => $_POST['sistole'],
        "diastole" => $_POST['diastole'],
        "nadi" => $_POST['nadi'],
        "respirasi" => $_POST['respirasi'],
        "tinggi" => $_POST['tinggi'],
        "berat" => $_POST['berat'],
        "lingkar_perut" => $_POST['lingkar_perut'],
        "rujuk_balik" => "",
        "kode_tkp" => $_POST['kode_tkp'],
        "nomor_urut" => $noUrut,
        "subyektif" => $_POST['keluhan'],
        "kode_kesadaran" => strtok($_POST['getKesadaran'], ':'),
        "nama_kesadaran" => substr($_POST['getKesadaran'], strpos($_POST['getKesadaran'], ': ') + 1),
        "terapi" => $_POST['terapi'],
        "kode_status_pulang" => strtok($_POST['getStatusPulang'], ':'),
        "nama_status_pulang" => substr($_POST['getStatusPulang'], strpos($_POST['getStatusPulang'], ': ') + 1),
        "tgl_pulang" => $_POST['tglPulang'],
        "tgl_kunjungan" => $_POST['tglKunjungan'],
        "kode_dokter" => strtok($_POST['getDokter'], ':'),
        "nama_dokter" => substr($_POST['getDokter'], strpos($_POST['getDokter'], ': ') + 1),
        "kode_diagnosa1" => strtok($_POST['getDiagnosa1'], ':'),
        "nama_diagnosa1" => substr($_POST['getDiagnosa1'], strpos($_POST['getDiagnosa1'], ': ') + 1),
        "kode_diagnosa2" => strtok($_POST['getDiagnosa2'], ':'),
        "nama_diagnosa2" => substr($_POST['getDiagnosa2'], strpos($_POST['getDiagnosa2'], ': ') + 1),
        "kode_diagnosa3" => strtok($_POST['getDiagnosa3'], ':'),
        "nama_diagnosa3" => substr($_POST['getDiagnosa3'], strpos($_POST['getDiagnosa3'], ': ') + 1),
        "tgl_estimasi_rujuk" => $_POST['tglEstRujuk'],
        "kode_ppk" => strtok($_POST['getReferensiFaskesSpesialis'], ':'),
        "nama_ppk" => substr($_POST['getReferensiFaskesSpesialis'], strpos($_POST['getReferensiFaskesSpesialis'], ': ') + 1),
        "kode_spesialis" => strtok($_POST['getReferensiSpesialis'], ':'),
        "nama_spesialis" => substr($_POST['getReferensiSpesialis'], strpos($_POST['getReferensiSpesialis'], ': ') + 1),
        "kode_subspesialis" => strtok($_POST['getReferensiSubSpesialis'], ':'),
        "nama_subspesialis" => substr($_POST['getReferensiSubSpesialis'], strpos($_POST['getReferensiSubSpesialis'], ': ') + 1),
        "kode_sarana" => strtok($_POST['getReferensiSarana'], ':'),
        "nama_sarana" => substr($_POST['getReferensiSarana'], strpos($_POST['getReferensiSarana'], ': ') + 1),
        "kode_referensikhusus" => strtok($_POST['getReferensiKhusus'], ':'),
        "nama_referensikhusus" => substr($_POST['getReferensiKhusus'], strpos($_POST['getReferensiKhusus'], ': ') + 1),
        "kode_faskeskhusus" => strtok($_POST['getFaskesKhusus'], ':'),
        "nama_faskeskhusus" => substr($_POST['getFaskesKhusus'], strpos($_POST['getFaskesKhusus'], ': ') + 1),
        "catatan" => $_POST['catatan'],
        "kode_tacc" => strtok($_POST['getTACC'], ':'),
        "nama_tacc" => substr($_POST['getTACC'], strpos($_POST['getTACC'], ': ') + 1),
        "alasan_tacc" => substr($_POST['alasanTacc'], strpos($_POST['alasanTacc'], ': ') + 1),
        "id_user" => $this->core->getUserInfo('id'),
        "tgl_input" => date('Y-m-d'),
        "status_kirim" => "Terkirim"
      ]);
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

  private function _addHeaderFiles()
  {
      $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
      $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
      $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
  }

}
