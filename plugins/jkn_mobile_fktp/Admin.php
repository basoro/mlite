<?php

namespace Plugins\JKN_Mobile_FKTP;

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
      $this->consumerUserKeyAntrol = $this->settings->get('pcare.consumerUserKeyAntrol');
      $this->api_url = $this->settings->get('pcare.PCareApiUrl');
      $this->api_url_antrol = 'https://apijkn.bpjs-kesehatan.go.id/antreanfktp/';
      $this->api_url_icare = 'https://apijkn.bpjs-kesehatan.go.id/wsIHS/api/pcare/validate';
      if (strpos($this->api_url, 'dev') !== false) { 
        $this->api_url_antrol = 'https://apijkn-dev.bpjs-kesehatan.go.id/antreanfktp_dev/';
        $this->api_url_icare = 'https://apijkn-dev.bpjs-kesehatan.go.id/ihs_dev/api/pcare/validate';
      }  
    }
 
    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Katalog' => 'index',
            'Mapping Poli' => 'mappingpoli',
            'Mapping Dokter' => 'mappingdokter',
            'Pengaturan' => 'settings',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Katalog', 'url' => url([ADMIN, 'jkn_mobile_fktp', 'index']), 'icon' => 'cube', 'desc' => 'Katalog antrian pcare'],
        ['name' => 'Mapping Poli', 'url' => url([ADMIN, 'jkn_mobile_fktp', 'mappingpoli']), 'icon' => 'cube', 'desc' => 'Mapping poli pcare'],
        ['name' => 'Mapping Dokter', 'url' => url([ADMIN, 'jkn_mobile_fktp', 'mappingdokter']), 'icon' => 'cube', 'desc' => 'Mapping dokter pcare'],
        ['name' => 'Pengaturan', 'url' => url([ADMIN, 'jkn_mobile_fktp', 'settings']), 'icon' => 'cube', 'desc' => 'Pengaturan antrian pcare'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getIndex()
    {
        return $this->draw('index.html');
    }

    public function getSettings()
    {
        $this->_addHeaderFiles();
        $this->assign['title'] = 'Pengaturan Modul JKN Mobile FKTP';
        $this->assign['propinsi'] = $this->db('propinsi')->where('kd_prop', $this->settings->get('jkn_mobile_fktp.kdprop'))->oneArray();
        $this->assign['kabupaten'] = $this->db('kabupaten')->where('kd_kab', $this->settings->get('jkn_mobile_fktp.kdkab'))->oneArray();
        $this->assign['kecamatan'] = $this->db('kecamatan')->where('kd_kec', $this->settings->get('jkn_mobile_fktp.kdkec'))->oneArray();
        $this->assign['kelurahan'] = $this->db('kelurahan')->where('kd_kel', $this->settings->get('jkn_mobile_fktp.kdkel'))->oneArray();
        $this->assign['suku_bangsa'] = $this->db('suku_bangsa')->toArray();
        $this->assign['bahasa_pasien'] = $this->db('bahasa_pasien')->toArray();
        $this->assign['cacat_fisik'] = $this->db('cacat_fisik')->toArray();
        $this->assign['perusahaan_pasien'] = $this->db('perusahaan_pasien')->toArray();
        $this->assign['penjab'] = $this->db('penjab')->where('status', '1')->toArray();
        $this->assign['poliklinik'] = $this->_getPoliklinik($this->settings->get('jkn_mobile_fktp.display'));
        $this->assign['jkn_mobile_fktp'] = htmlspecialchars_array($this->settings('jkn_mobile_fktp'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    private function _getPoliklinik($kd_poli = null)
    {
        $result = [];
        $rows = $this->db('poliklinik')->toArray();

        if (!$kd_poli) {
            $kd_poliArray = [];
        } else {
            $kd_poliArray = explode(',', $kd_poli);
        }

        foreach ($rows as $row) {
            if (empty($kd_poliArray)) {
                $attr = '';
            } else {
                if (in_array($row['kd_poli'], $kd_poliArray)) {
                    $attr = 'selected';
                } else {
                    $attr = '';
                }
            }
            $result[] = ['kd_poli' => $row['kd_poli'], 'nm_poli' => $row['nm_poli'], 'attr' => $attr];
        }
        return $result;
    }

    public function postSaveSettings()
    {
        $_POST['jkn_mobile_fktp']['display'] = implode(',', $_POST['jkn_mobile_fktp']['display']);
        foreach ($_POST['jkn_mobile_fktp'] as $key => $val) {
            $this->settings('jkn_mobile_fktp', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'jkn_mobile_fktp', 'settings']));
    }

    // Mapping  polisection
    public function getMappingPoli()
    {
        $this->core->addJS(url([ADMIN, 'jkn_mobile_fktp', 'mappingpolijs']), 'footer');

        $totalRecords = $this->db('maping_poliklinik_pcare')
          ->select('kd_poli_rs')
          ->toArray();
        $jumlah_data    = count($totalRecords);
        $offset         = 10;
        $jml_halaman    = ceil($jumlah_data / $offset);
        $halaman    = 1;

        $mappingpoli = $this->db('maping_poliklinik_pcare')
          ->desc('kd_poli_rs')
          ->limit(10)
          ->toArray();
        return $this->draw('mappingpoli.html', [
          'mappingpoli' => $mappingpoli,
          'halaman' => $halaman,
          'jumlah_data' => $jumlah_data,
          'jml_halaman' => $jml_halaman
        ]);

    }

    public function anyMappingPoliDisplay()
    {
        $this->core->addJS(url([ADMIN, 'jkn_mobile_fktp', 'mappingpolijs']), 'footer');

        $perpage = '10';
        $totalRecords = $this->db('maping_poliklinik_pcare')
          ->select('kd_poli_rs')
          ->toArray();
        $jumlah_data    = count($totalRecords);
        $offset         = 10;
        $jml_halaman    = ceil($jumlah_data / $offset);
        $halaman    = 1;

        if(isset($_POST['cari'])) {
          $mappingpoli = $this->db('maping_poliklinik_pcare')
            ->like('kd_poli_pcare', '%'.$_POST['cari'].'%')
            ->orLike('nm_poli_pcare', '%'.$_POST['cari'].'%')
            ->desc('kd_poli_rs')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($mappingpoli);
          $jml_halaman = ceil($jumlah_data / $offset);
        }elseif(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $mappingpoli = $this->db('maping_poliklinik_pcare')
            ->desc('kd_poli_rs')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $halaman = $_POST['halaman'];
        }else{
          $mappingpoli = $this->db('maping_poliklinik_pcare')
            ->desc('kd_poli_rs')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
        }

        echo $this->draw('mappingpoli.display.html', [
          'mappingpoli' => $mappingpoli,
          'halaman' => $halaman,
          'jumlah_data' => $jumlah_data,
          'jml_halaman' => $jml_halaman
        ]);

        exit();
    }

    public function anyMappingPoliForm()
    {
      $poliklinik = $this->db('poliklinik')->toArray();
      if (isset($_POST['kd_poli_rs'])){
        $mappingpoli = $this->db('maping_poliklinik_pcare')->where('kd_poli_rs', $_POST['kd_poli_rs'])->oneArray();
        echo $this->draw('mappingpoli.form.html', ['poliklinik' => $poliklinik, 'mappingpoli' => $mappingpoli]);
      } else {
        $mappingpoli = [
          'kd_poli_rs' => '',
          'kd_poli_pcare' => '',
          'nm_poli_pcare' => ''
        ];
        echo $this->draw('mappingpoli.form.html', ['poliklinik' => $poliklinik, 'mappingpoli' => $mappingpoli]);
      }
      exit();
    }

    public function postMappingPoliSave()
    {
      $kd_poli_pcare = strtok($_POST['getPoli'], ':');
      $nm_poli_pcare = substr($_POST['getPoli'], strpos($_POST['getPoli'], ': ') + 1);
      if (!$this->db('maping_poliklinik_pcare')->where('kd_poli_rs', $_POST['kd_poli_rs'])->oneArray()) {
        $query = $this->db('maping_poliklinik_pcare')->save([
          'kd_poli_rs' => $_POST['kd_poli_rs'],
          'kd_poli_pcare' => $kd_poli_pcare,
          'nm_poli_pcare' => $nm_poli_pcare
        ]);
      } else {
        $query = $this->db('maping_poliklinik_pcare')->where('kd_poli_rs', $_POST['kd_poli_rs'])->save([
          'kd_poli_pcare' => $kd_poli_pcare,
          'nm_poli_pcare' => $nm_poli_pcare
        ]);
      }
      exit();
    }

    public function postMappingPoliHapus()
    {
      $this->db('maping_poliklinik_pcare')->where('kd_poli_rs', $_POST['kd_poli_rs'])->delete();
      exit();
    }

    public function getMappingPoliJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/jkn_mobile_fktp/js/admin/mappingpoli.js');
        exit();
    }
    // End mappingpoli section

    // Mapping dokter section
    public function getMappingDokter()
    {
        $this->core->addJS(url([ADMIN, 'jkn_mobile_fktp', 'mappingdokterjs']), 'footer');

        $totalRecords = $this->db('maping_dokter_pcare')
          ->select('kd_dokter')
          ->toArray();
        $jumlah_data    = count($totalRecords);
        $offset         = 10;
        $jml_halaman    = ceil($jumlah_data / $offset);
        $halaman    = 1;

        $mappingdokter = $this->db('maping_dokter_pcare')
          ->desc('kd_dokter')
          ->limit(10)
          ->toArray();
        return $this->draw('mappingdokter.html', [
          'mappingdokter' => $mappingdokter,
          'halaman' => $halaman,
          'jumlah_data' => $jumlah_data,
          'jml_halaman' => $jml_halaman
        ]);

    }

    public function anyMappingDokterDisplay()
    {
        $this->core->addJS(url([ADMIN, 'jkn_mobile_fktp', 'mappingdokterjs']), 'footer');

        $perpage = '10';
        $totalRecords = $this->db('maping_dokter_pcare')
          ->select('kd_dokter')
          ->toArray();
        $jumlah_data    = count($totalRecords);
        $offset         = 10;
        $jml_halaman    = ceil($jumlah_data / $offset);
        $halaman    = 1;

        if(isset($_POST['cari'])) {
          $mappingdokter = $this->db('maping_dokter_pcare')
            ->like('kd_dokter_pcare', '%'.$_POST['cari'].'%')
            ->orLike('nm_dokter_pcare', '%'.$_POST['cari'].'%')
            ->desc('kd_dokter')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($mappingdokter);
          $jml_halaman = ceil($jumlah_data / $offset);
        }elseif(isset($_POST['halaman'])){
          $offset     = (($_POST['halaman'] - 1) * $perpage);
          $mappingdokter = $this->db('maping_dokter_pcare')
            ->desc('kd_dokter')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $halaman = $_POST['halaman'];
        }else{
          $mappingdokter = $this->db('maping_dokter_pcare')
            ->desc('kd_dokter')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
        }

        echo $this->draw('mappingdokter.display.html', [
          'mappingdokter' => $mappingdokter,
          'halaman' => $halaman,
          'jumlah_data' => $jumlah_data,
          'jml_halaman' => $jml_halaman
        ]);

        exit();
    }

    public function anyMappingDokterForm()
    {
      $dokter = $this->db('dokter')->toArray();
      if (isset($_POST['kd_dokter'])){
        $mappingdokter = $this->db('maping_dokter_pcare')->where('kd_dokter', $_POST['kd_dokter'])->oneArray();
        echo $this->draw('mappingdokter.form.html', ['dokter' => $dokter, 'mappingdokter' => $mappingdokter]);
      } else {
        $mappingdokter = [
          'kd_dokter' => '',
          'kd_dokter_pcare' => '',
          'nm_dokter_pcare' => ''
        ];
        echo $this->draw('mappingdokter.form.html', ['dokter' => $dokter, 'mappingdokter' => $mappingdokter]);
      }
      exit();
    }

    public function postMappingDokterSave()
    {
      $kd_dokter_pcare = strtok($_POST['getDokter'], ':');
      $nm_dokter_pcare = substr($_POST['getDokter'], strpos($_POST['getDokter'], ': ') + 1);
      if (!$this->db('maping_dokter_pcare')->where('kd_dokter', $_POST['kd_dokter'])->oneArray()) {
        $query = $this->db('maping_dokter_pcare')->save([
          'kd_dokter' => $_POST['kd_dokter'],
          'kd_dokter_pcare' => $kd_dokter_pcare,
          'nm_dokter_pcare' => $nm_dokter_pcare
        ]);
      } else {
        $query = $this->db('maping_dokter_pcare')->where('kd_dokter', $_POST['kd_dokter'])->save([
          'kd_dokter_pcare' => $kd_dokter_pcare,
          'nm_dokter_pcare' => $nm_dokter_pcare
        ]);
      }
      exit();
    }

    public function postMappingDokterHapus()
    {
      $this->db('maping_dokter_pcare')->where('kd_dokter', $_POST['kd_dokter'])->delete();
      exit();
    }

    public function getMappingDokterJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/jkn_mobile_fktp/js/admin/mappingdokter.js');
        exit();
    }
    // End mappingdokter section

    public function getAntrolAddAntrian($noRm)
    {
        $date = date('Y-m-d');
 
        $reg_periksa = $this->db('reg_periksa')->where('no_rkm_medis', $noRm)->where('tgl_registrasi', $date)->oneArray();

        if(preg_match('/\bUmum\b/', $this->core->getPoliklinikInfo('nm_poli', $reg_periksa['kd_poli']))) {
            $kode_antrean = 'A';
        }
  
        if(preg_match('/\bGigi\b/', $this->core->getPoliklinikInfo('nm_poli', $reg_periksa['kd_poli']))) {
            $kode_antrean = 'B';
        }

        if(preg_match('/\bKIA\b/', $this->core->getPoliklinikInfo('nm_poli', $reg_periksa['kd_poli']))) {
          $kode_antrean = 'C';
        }
 
        $no_antrian = ltrim($reg_periksa['no_reg'], '0');
  
        $maping_poliklinik_pcare = $this->db('maping_poliklinik_pcare')->where('kd_poli_rs', $reg_periksa['kd_poli'])->oneArray();
        $kdPoli = $maping_poliklinik_pcare['kd_poli_pcare'];
        $nmPoli = $maping_poliklinik_pcare['nm_poli_pcare'];

        $maping_dokter_pcare = $this->db('maping_dokter_pcare')->where('kd_dokter', $reg_periksa['kd_dokter'])->oneArray();
        $kdDokter = $maping_dokter_pcare['kd_dokter_pcare'];
        $nmDokter = $maping_dokter_pcare['nm_dokter_pcare'];

        $pasien = $this->db('pasien')->where('no_rkm_medis', $noRm)->oneArray();
        $noKartu = isset_or($pasien['no_peserta'], '');
        $nik = isset_or($pasien['no_ktp'], '');
        $noHp = isset_or($pasien['no_tlp'], '08820'.$noRm);
  
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

        $jadwal = $this->db('jadwal')->where('kd_dokter', $reg_periksa['kd_dokter'])->where('kd_poli', $reg_periksa['kd_poli'])->where('hari_kerja', $hari)->oneArray();        
        $jampraktek = date('H:i', strtotime($jadwal['jam_mulai'])).'-'.date('H:i', strtotime($jadwal['jam_selesai']));

        $data = [
          'nomorkartu' => $noKartu,
          'nik' => $nik,
          'nohp' => $noHp,
          'kodepoli' => $kdPoli,
          'namapoli' => $nmPoli,
          'norm' => $noRm,
          'tanggalperiksa' => $date,
          'kodedokter' => $kdDokter, 
          'namadokter' => $nmDokter,
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
        $output = PcareService::post($url, $data, $this->consumerID, $this->consumerSecret, $this->consumerUserKeyAntrol, $this->usernamePcare, $this->passwordPcare, $this->kdAplikasi);
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

    public function getAntrolPanggilAntrian($noRm)
    {
  
        $date = date('Y-m-d');

        $reg_periksa = $this->db('reg_periksa')->where('no_rkm_medis', $noRm)->where('tgl_registrasi', $date)->oneArray();
        $maping_poliklinik_pcare = $this->db('maping_poliklinik_pcare')->where('kd_poli_rs', $reg_periksa['kd_poli'])->oneArray();
        $kdPoli = $maping_poliklinik_pcare['kd_poli_pcare'];
        $nmPoli = $maping_poliklinik_pcare['nm_poli_pcare'];

        $pasien = $this->db('pasien')->where('no_rkm_medis', $noRm)->oneArray();
        $noKartu = isset_or($pasien['no_peserta'], '');
  
        $data = [
          'tanggalperiksa' => $date,
          'kodepoli' => $kdPoli,
          'nomorkartu' => $noKartu,
          'status' => 1,
          'waktu' => strtotime(date('Y-m-d H:i:s'))*1000
        ];
    
        $data = json_encode($data);
        // echo $data;
  
        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
        $key = $this->consumerID . $this->consumerSecret . $tStamp;

        $url = $this->api_url_antrol.'antrean/panggil';
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
  
    public function getAntrolBatalAntrian($noRm)
    {
        $date = date('Y-m-d');

        $reg_periksa = $this->db('reg_periksa')->where('no_rkm_medis', $noRm)->where('tgl_registrasi', $date)->oneArray();
        $maping_poliklinik_pcare = $this->db('maping_poliklinik_pcare')->where('kd_poli_rs', $reg_periksa['kd_poli'])->oneArray();
        $kdPoli = $maping_poliklinik_pcare['kd_poli_pcare'];
        $nmPoli = $maping_poliklinik_pcare['nm_poli_pcare'];

        $pasien = $this->db('pasien')->where('no_rkm_medis', $noRm)->oneArray();
        $noKartu = isset_or($pasien['no_peserta'], '');
  
        $data = [
          'tanggalperiksa' => $date,
          'kodepoli' => $kdPoli,
          'nomorkartu' => $noKartu,
          'alasan' => 'Terjadi perubahan jadwal dokter'
        ];
    
        $data = json_encode($data);
        echo $data;
  
        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
        $key = $this->consumerID . $this->consumerSecret . $tStamp;

        $url = $this->api_url_antrol.'antrean/batal';
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

    public function getAjax()
    {
        header('Content-type: text/html');
        $show = isset($_GET['show']) ? $_GET['show'] : "";
        switch($show){
        	default:
          break;
        	case "propinsi":
          $propinsi = $this->db('propinsi')->toArray();
          foreach ($propinsi as $row) {
            echo '<tr class="pilihpropinsi" data-kdprop="'.$row['kd_prop'].'" data-namaprop="'.$row['nm_prop'].'">';
      			echo '<td>'.$row['kd_prop'].'</td>';
      			echo '<td>'.$row['nm_prop'].'</td>';
      			echo '</tr>';
          }
          break;
          case "kabupaten":
          $kabupaten = $this->db('kabupaten')->toArray();
          foreach ($kabupaten as $row) {
            echo '<tr class="pilihkabupaten" data-kdkab="'.$row['kd_kab'].'" data-namakab="'.$row['nm_kab'].'">';
      			echo '<td>'.$row['kd_kab'].'</td>';
      			echo '<td>'.$row['nm_kab'].'</td>';
      			echo '</tr>';
          }
          break;
          case "kecamatan":
          $kecamatan = $this->db('kecamatan')->toArray();
          foreach ($kecamatan as $row) {
            echo '<tr class="pilihkecamatan" data-kdkec="'.$row['kd_kec'].'" data-namakec="'.$row['nm_kec'].'">';
      			echo '<td>'.$row['kd_kec'].'</td>';
      			echo '<td>'.$row['nm_kec'].'</td>';
      			echo '</tr>';
          }
          break;
          case "kelurahan":
          // Alternative SQL join in Datatables
          $id_table = 'kd_kel';
          $columns = array(
                       'kd_kel',
                       'nm_kel'
                     );
          //$action = '"Test" as action';
          // gunakan join disini
          $from = 'kelurahan';

          $id_table = $id_table != '' ? $id_table . ',' : '';
          // custom SQL
          $sql = "SELECT {$id_table} ".implode(',', $columns)." FROM {$from}";

          // search
          if (isset($_GET['search']['value']) && $_GET['search']['value'] != '') {
              $search = $_GET['search']['value'];
              $where  = '';
              // create parameter pencarian kesemua kolom yang tertulis
              // di $columns
              for ($i=0; $i < count($columns); $i++) {
                  $where .= $columns[$i] . ' LIKE "%'.$search.'%"';

                  // agar tidak menambahkan 'OR' diakhir Looping
                  if ($i < count($columns)-1) {
                      $where .= ' OR ';
                  }
              }

              $sql .= ' WHERE ' . $where;
          }

          //SORT Kolom
          $sortColumn = isset($_GET['order'][0]['column']) ? $_GET['order'][0]['column'] : 0;
          $sortDir    = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'asc';

          $sortColumn = $columns[$sortColumn];

          $sql .= " ORDER BY {$sortColumn} {$sortDir}";

          $query = $this->db()->pdo()->prepare($sql);
          $query->execute();
          $query = $query->fetchAll();

          // var_dump($sql);
          //$count = $database->query($sql);
          // hitung semua data
          $totaldata = count($query);

          // memberi Limit
          $start  = isset($_GET['start']) ? $_GET['start'] : 0;
          $length = isset($_GET['length']) ? $_GET['length'] : 10;


          $sql .= " LIMIT {$start}, {$length}";

          $data = $this->db()->pdo()->prepare($sql);
          $data->execute();
          $data = $data->fetchAll();

          // create json format
          $datatable['draw']            = isset($_GET['draw']) ? $_GET['draw'] : 1;
          $datatable['recordsTotal']    = $totaldata;
          $datatable['recordsFiltered'] = $totaldata;
          $datatable['data']            = array();

          foreach ($data as $row) {

              $fields = array();
              $fields['0'] = $row['kd_kel'];
              $fields['1'] = '<span class="pilihkelurahan" data-kdkel="'.$row['kd_kel'].'" data-namakel="'.$row['nm_kel'].'">'.$row['nm_kel'].'</span>';
              $datatable['data'][] = $fields;

          }

          echo json_encode($datatable);

          break;

        }
        exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

    }

}
