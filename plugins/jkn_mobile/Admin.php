<?php

namespace Plugins\JKN_Mobile;

use Systems\AdminModule;
use Systems\Lib\BpjsService;

class Admin extends AdminModule
{

    protected $consid;
    protected $secretkey;
    protected $bpjsurl;
    protected $user_key;
    protected $assign;

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
            'Kelola' => 'manage',
            'Katalog' => 'index',
            'Mapping Poliklinik' => 'mappingpoli',
            'Add Mapping Poliklinik' => 'addmappingpoli',
            'Mapping Dokter' => 'mappingdokter',
            'Add Mapping Dokter' => 'addmappingdokter',
            'Jadwal Dokter HFIS' => 'jadwaldokter',
            'Data Booking Antrol' => 'bookingantrol',
            'Task ID' => 'taskid',
            'Quality Rate' => 'qrantrol',
            'Dashboard Antrol BPJS' => 'antrol',
            'Pengaturan' => 'settings',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Katalog', 'url' => url([ADMIN, 'jkn_mobile', 'index']), 'icon' => 'tasks', 'desc' => 'Index JKN Mobile'],
        ['name' => 'Mapping Poliklinik', 'url' => url([ADMIN, 'jkn_mobile', 'mappingpoli']), 'icon' => 'tasks', 'desc' => 'Mapping Poliklinik JKN Mobile'],
        ['name' => 'Add Mapping Poliklinik', 'url' => url([ADMIN, 'jkn_mobile', 'addmappingpoli']), 'icon' => 'tasks', 'desc' => 'Add mapping poliklinik JKN Mobile'],
        ['name' => 'Mapping Dokter', 'url' => url([ADMIN, 'jkn_mobile', 'mappingdokter']), 'icon' => 'tasks', 'desc' => 'Mapping Dokter JKN Mobile'],
        ['name' => 'Add Mapping Dokter', 'url' => url([ADMIN, 'jkn_mobile', 'addmappingdokter']), 'icon' => 'tasks', 'desc' => 'Add Mapping Dokter JKN Mobile'],
        ['name' => 'Jadwal Dokter HFIS', 'url' => url([ADMIN, 'jkn_mobile', 'jadwaldokter']), 'icon' => 'tasks', 'desc' => 'Jadwal Dokter HFIS JKN Mobile'],
        ['name' => 'Data Booking Antrol', 'url' => url([ADMIN, 'jkn_mobile', 'bookingantrol']), 'icon' => 'list', 'desc' => 'Booking Antrol JKN Mobile'],
        ['name' => 'Task ID', 'url' => url([ADMIN, 'jkn_mobile', 'taskid']), 'icon' => 'tasks', 'desc' => 'Task ID JKN Mobile'],
        ['name' => 'Quality Rate', 'url' => url([ADMIN, 'jkn_mobile', 'qrantrol']), 'icon' => 'tasks', 'desc' => 'Quality Rate Antrian Online BPJS'],
        ['name' => 'Dashboard Antrol BPJS', 'url' => url([ADMIN, 'jkn_mobile', 'antrol']), 'icon' => 'tasks', 'desc' => 'Antrian Online BPJS'],
        ['name' => 'Pengaturan', 'url' => url([ADMIN, 'jkn_mobile', 'settings']), 'icon' => 'tasks', 'desc' => 'Pengaturan JKN Mobile'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getIndex()
    {
        return $this->draw('index.html');
    }

    public function getRefPoli()
    {
        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
        $key = $this->consid.$this->secretkey.$tStamp;

        $url = $this->bpjsurl.'ref/poli';
        $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
        $json = json_decode($output, true);
        //echo json_encode($json);
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

    public function getMappingPoli()
    {
        $this->_addHeaderFiles();
        return $this->draw('mappingpoli.html', ['row' => $this->db('maping_poli_bpjs')->toArray()]);
    }

    public function getAddMappingPoli()
    {
        $this->_addHeaderFiles();
        $this->assign['poliklinik'] = $this->db('poliklinik')->where('status','1')->toArray();
        return $this->draw('form.mappingpoli.html', ['row' => $this->assign]);
    }

    public function postPoliklinik_Save()
    {

        $location = url([ADMIN, 'jkn_mobile', 'addmappingpoli']);

        unset($_POST['save']);

        $query = $this->db('maping_poli_bpjs')->save([
            'kd_poli_rs' => $_POST['kd_poli_rs'],
            'kd_poli_bpjs' => $_POST['poli_kode'],
            'nm_poli_bpjs' => $_POST['poli_nama']
        ]);

        if ($query) {
            $this->notify('success', 'Simpan maping poli bpjs sukes');
        } else {
            $this->notify('failure', 'Simpan maping poli bpjs gagal');
        }

        redirect($location, $_POST);
    }

    public function getPoliklinik_Delete($id)
    {
        if ($this->db('maping_poli_bpjs')->where('kd_poli_rs', $id)->delete()) {
            $this->notify('success', 'Hapus maping poli bpjs sukses');
        } else {
            $this->notify('failure', 'Hapus maping poli bpjs gagal');
        }
        redirect(url([ADMIN, 'jkn_mobile', 'mappingpoli']));
    }

    public function getRefDokter()
    {
        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
        $key = $this->consid.$this->secretkey.$tStamp;

        $url = $this->bpjsurl.'ref/dokter';
        $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
        $json = json_decode($output, true);
        //echo json_encode($json);
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

    public function getMappingDokter()
    {
        $this->_addHeaderFiles();
        return $this->draw('mappingdokter.html', ['row' => $this->db('maping_dokter_dpjpvclaim')->toArray()]);
    }


    public function getAddMappingDokter()
    {
        $this->_addHeaderFiles();
        $this->assign['dokter'] = $this->db('dokter')->where('status','1')->toArray();
        return $this->draw('form.mappingdokter.html', ['row' => $this->assign]);
    }

    public function postDokter_Save()
    {

        $location = url([ADMIN, 'jkn_mobile', 'addmappingdokter']);

        unset($_POST['save']);

        $query = $this->db('maping_dokter_dpjpvclaim')->save([
            'kd_dokter' => $_POST['kd_dokter'],
            'kd_dokter_bpjs' => $_POST['dokter_kode'],
            'nm_dokter_bpjs' => $_POST['dokter_nama']
        ]);

        if ($query) {
            $this->notify('success', 'Simpan maping poli bpjs sukes');
        } else {
            $this->notify('failure', 'Simpan maping poli bpjs gagal');
        }

        redirect($location, $_POST);
    }

    public function getDokter_Delete($id)
    {
        if ($this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $id)->delete()) {
            $this->notify('success', 'Hapus maping poli bpjs sukses');
        } else {
            $this->notify('failure', 'Hapus maping poli bpjs gagal');
        }
        redirect(url([ADMIN, 'jkn_mobile', 'mappingdokter']));
    }

    public function getJadwalDokter()
    {
        $maping_poli_bpjs = $this->db('maping_poli_bpjs')->toArray();
        foreach ($maping_poli_bpjs as $value) {
          $_POST['kodepoli'] = $value['kd_poli_bpjs'];
          $kodepoli = $_POST['kodepoli'];
          $_POST['tanggal'] = date('Y-m-d');
          $tanggal = $_POST['tanggal'];
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
          $response = [];
          if($json['metadata']['code'] == '200') {
            $response = $decompress;
          }
        }
        //echo $response;
        $response = json_decode($response, true);
        $this->assign['list'] = $response;
        return $this->draw('jadwaldokter.html', ['row' => $this->assign]);
    }

    public function anyTaskID()
    {
      $this->_addHeaderFiles();
      $this->getCssCard();
      $date = date('Y-m-d');
      if(isset($_POST['periode_antrol']) && $_POST['periode_antrol'] !='')
        $date = $_POST['periode_antrol'];
      //$date = '2022-01-20';
      $exclude_taskid = str_replace(",","','", $this->settings->get('jkn_mobile.exclude_taskid'));
      $query = $this->db()->pdo()->prepare("SELECT pasien.no_peserta,pasien.no_rkm_medis,pasien.no_ktp,pasien.no_tlp,reg_periksa.no_reg,reg_periksa.no_rawat,reg_periksa.tgl_registrasi,reg_periksa.kd_dokter,dokter.nm_dokter,reg_periksa.kd_poli,poliklinik.nm_poli,reg_periksa.stts_daftar,reg_periksa.no_rkm_medis
      FROM reg_periksa INNER JOIN pasien ON reg_periksa.no_rkm_medis=pasien.no_rkm_medis INNER JOIN dokter ON reg_periksa.kd_dokter=dokter.kd_dokter INNER JOIN poliklinik ON reg_periksa.kd_poli=poliklinik.kd_poli WHERE reg_periksa.tgl_registrasi='$date' AND reg_periksa.kd_poli NOT IN ('$exclude_taskid')
      ORDER BY concat(reg_periksa.tgl_registrasi,' ',reg_periksa.jam_reg)");
      $query->execute();
      $query = $query->fetchAll(\PDO::FETCH_ASSOC);;

      $rows = [];
      foreach ($query as $q) {
          $reg_periksa = $this->db('reg_periksa')->where('tgl_registrasi', $date)->where('no_rkm_medis', $q['no_rkm_medis'])->where('stts', '<>', 'Batal')->oneArray();
          $reg_periksa2 = $this->db('reg_periksa')->where('tgl_registrasi', $date)->where('no_rkm_medis', $q['no_rkm_medis'])->where('stts', 'Batal')->oneArray();
          $batal = '0000-00-00 00:00:00';
          if($reg_periksa2) {
            $batal = $q['tgl_registrasi'].' '.date('H:i:s');
          }
          $mlite_antrian_referensi = $this->db('mlite_antrian_referensi')->where('tanggal_periksa', $q['tgl_registrasi'])->where('nomor_kartu', $q['no_peserta'])->oneArray();
          if(!$mlite_antrian_referensi) {
              $mlite_antrian_referensi = $this->db('mlite_antrian_referensi')->where('tanggal_periksa', $q['tgl_registrasi'])->where('no_rkm_medis', $q['no_rkm_medis'])->oneArray();
          }
          $mutasi_berkas = $this->db('mutasi_berkas')->select('dikirim')->where('no_rawat', $reg_periksa['no_rawat'])->where('dikirim', '<>', '0000-00-00 00:00:00')->oneArray();
          $mutasi_berkas2 = $this->db('mutasi_berkas')->select('diterima')->where('no_rawat', $reg_periksa['no_rawat'])->where('diterima', '<>', '0000-00-00 00:00:00')->oneArray();
          $pemeriksaan_ralan = $this->db('pemeriksaan_ralan')->select(['datajam' => 'concat(tgl_perawatan," ",jam_rawat)'])->where('no_rawat', $reg_periksa['no_rawat'])->oneArray();
          $resep_obat = $this->db('resep_obat')->select(['datajam' => 'concat(tgl_perawatan," ",jam)'])->where('no_rawat', $reg_periksa['no_rawat'])->oneArray();
          $resep_obat2 = $this->db('resep_obat')->select(['datajam' => 'concat(tgl_peresepan," ",jam_peresepan)'])->where('no_rawat', $reg_periksa['no_rawat'])->where('concat(tgl_perawatan," ",jam)', '<>', 'concat(tgl_peresepan," ",jam_peresepan)')->oneArray();

          $mlite_antrian_loket = $this->db('mlite_antrian_loket')->where('postdate', $date)->where('no_rkm_medis', $q['no_rkm_medis'])->oneArray();
          $task1 = '';
          $task2 = '';
          if($mlite_antrian_loket) {
            $task1 = $mlite_antrian_loket['postdate'].' '.$mlite_antrian_loket['start_time'];
            $task2 = $mlite_antrian_loket['postdate'].' '.$mlite_antrian_loket['end_time'];
          }
          $q['nomor_referensi'] = isset_or($mlite_antrian_referensi['nomor_referensi'], '');
          $q['status_kirim'] = isset_or($mlite_antrian_referensi['status_kirim'], '');
          /*$q['task1'] = strtotime($task1) * 1000;
          $q['task2'] = strtotime($task2) * 1000;
          $q['task3'] = strtotime($mutasi_berkas['dikirim']) * 1000;
          $q['task4'] = strtotime($mutasi_berkas2['diterima']) * 1000;
          $q['task5'] = strtotime($pemeriksaan_ralan['datajam']) * 1000;
          $q['task6'] = strtotime($resep_obat['datajam']) * 1000;
          $q['task7'] = strtotime($resep_obat2['datajam']) * 1000;
          $q['task99'] = $batal;*/

          $taskid1['waktu'] = '';
          $taskid2['waktu'] = '';
          $taskid3['waktu'] = '';
          $taskid4['waktu'] = '';
          $taskid5['waktu'] = '';
          $taskid6['waktu'] = '';
          $taskid7['waktu'] = '';

          if(!empty($q['nomor_referensi'])) {
            $taskid1 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $q['nomor_referensi'])->where('taskid', '1')->oneArray();
            $q['task1'] = date('Y-m-d H:i:s', isset_or($taskid1['waktu']) / 1000 );

            $taskid2 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $q['nomor_referensi'])->where('taskid', '2')->oneArray();
            $q['task2'] = date('Y-m-d H:i:s', isset_or($taskid2['waktu']) / 1000 );

            $taskid3 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $q['nomor_referensi'])->where('taskid', '3')->oneArray();
            $q['task3'] = date('Y-m-d H:i:s', isset_or($taskid3['waktu']) / 1000 );

            $taskid4 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $q['nomor_referensi'])->where('taskid', '4')->oneArray();
            $q['task4'] = date('Y-m-d H:i:s', isset_or($taskid4['waktu']) / 1000 );

            $taskid5 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $q['nomor_referensi'])->where('taskid', '5')->oneArray();
            $q['task5'] = date('Y-m-d H:i:s', isset_or($taskid5['waktu']) / 1000 );

            $taskid6 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $q['nomor_referensi'])->where('taskid', '6')->oneArray();
            $q['task6'] = date('Y-m-d H:i:s', isset_or($taskid6['waktu']) / 1000 );

            $taskid7 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $q['nomor_referensi'])->where('taskid', '7')->oneArray();
            $q['task7'] = date('Y-m-d H:i:s', isset_or($taskid7['waktu']) / 1000 );
          }

          if($taskid1['waktu'] == '') {
            $q['task1'] = $task1;
          }
          if($taskid2['waktu'] == '') {
            $q['task2'] = $task2;
          }
          if($taskid3['waktu'] == '') {
            $q['task3'] = isset_or($mutasi_berkas['dikirim']);
          }
          if($taskid4['waktu'] == '') {
            $q['task4'] = isset_or($mutasi_berkas2['diterima']);
          }
          if($taskid5['waktu'] == '') {
            $q['task5'] = isset_or($pemeriksaan_ralan['datajam']);
          }
          if($taskid6['waktu'] == '') {
            $q['task6'] = isset_or($resep_obat2['datajam']);
          }
          if($taskid7['waktu'] == '') {
            $q['task7'] = isset_or($resep_obat['datajam']);
          }
          $q['task99'] = $batal;
          $rows[] = $q;
      }

      $taskid = $rows;
      return $this->draw('taskid.html', ['taskid' => $taskid]);
    }

    public function getTaskIDInput($no_rawat)
    {

      $no_rawat = revertNorawat($no_rawat);
      $pasien = $this->db('reg_periksa')
        ->select([
          'no_rawat' => 'no_rawat',
          'no_rkm_medis' => 'reg_periksa.no_rkm_medis',
          'nm_pasien' => 'nm_pasien',
          'kd_pj' => 'reg_periksa.kd_pj',
          'tgl_registrasi' => 'tgl_registrasi',
          'jam_reg' => 'jam_reg'
        ])
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->where('no_rawat', $no_rawat)
        ->oneArray();
      $mlite_antrian_referensi = $this->db('mlite_antrian_referensi')->where('no_rkm_medis', $pasien['no_rkm_medis'])->where('tanggal_periksa', $pasien['tgl_registrasi'])->oneArray();
      $nomor_referensi = '';
      $kode_booking = '';
      if($mlite_antrian_referensi) {
        $nomor_referensi = $mlite_antrian_referensi['nomor_referensi'];
        $kode_booking = $mlite_antrian_referensi['kodebooking'];
      }

      $datetime_reg_periksa = $pasien['tgl_registrasi'].' '.$pasien['jam_reg'];

      $taskid1 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $nomor_referensi)->where('taskid', '1')->oneArray();
      $taskid['1'] = date('Y-m-d H:i:s', isset_or($taskid1['waktu'], strtotime($datetime_reg_periksa) * 1000) / 1000 );

      $taskid2 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $nomor_referensi)->where('taskid', '2')->oneArray();
      $taskid['2'] = date('Y-m-d H:i:s', isset_or($taskid2['waktu'], strtotime($datetime_reg_periksa) * 1000) / 1000 );

      $taskid3 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $nomor_referensi)->where('taskid', '3')->oneArray();
      $taskid['3'] = date('Y-m-d H:i:s', isset_or($taskid3['waktu'], strtotime($datetime_reg_periksa) * 1000) / 1000 );

      $taskid4 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $nomor_referensi)->where('taskid', '4')->oneArray();
      $taskid['4'] = date('Y-m-d H:i:s', isset_or($taskid4['waktu'], strtotime($datetime_reg_periksa) * 1000) / 1000 );

      $taskid5 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $nomor_referensi)->where('taskid', '5')->oneArray();
      $taskid['5'] = date('Y-m-d H:i:s', isset_or($taskid5['waktu'], strtotime($datetime_reg_periksa) * 1000) / 1000 );

      $taskid6 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $nomor_referensi)->where('taskid', '6')->oneArray();
      $taskid['6'] = date('Y-m-d H:i:s', isset_or($taskid6['waktu'], strtotime($datetime_reg_periksa) * 1000) / 1000 );

      $taskid7 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $nomor_referensi)->where('taskid', '7')->oneArray();
      $taskid['7'] = date('Y-m-d H:i:s', isset_or($taskid7['waktu'], strtotime($datetime_reg_periksa) * 1000) / 1000 );

      $mlite_antrian_loket = $this->db('mlite_antrian_loket')->where('no_rkm_medis', $pasien['no_rkm_medis'])->where('postdate', $pasien['tgl_registrasi'])->oneArray();
      $berkas_dikirim = $this->db('mutasi_berkas')->select('dikirim')->where('no_rawat', $pasien['no_rawat'])->where('dikirim', '<>', '0000-00-00 00:00:00')->oneArray();
      $berkas_diterima = $this->db('mutasi_berkas')->select('diterima')->where('no_rawat', $pasien['no_rawat'])->where('diterima', '<>', '0000-00-00 00:00:00')->oneArray();
      $pemeriksaan_ralan = $this->db('pemeriksaan_ralan')->select(['datajam' => 'concat(tgl_perawatan," ",jam_rawat)'])->where('no_rawat', $pasien['no_rawat'])->oneArray();
      $resep_obat = $this->db('resep_obat')->select(['datajam' => 'concat(tgl_peresepan," ",jam_peresepan)', 'datajam2' => 'concat(tgl_perawatan," ",jam)', 'no_resep' => 'no_resep'])->where('no_rawat', $pasien['no_rawat'])->oneArray();

      $jenisresep = 'Non racikan';
      if($resep_obat) {
        $resep_dokter_racikan = $this->db('resep_dokter_racikan')->where('no_resep', $resep_obat['no_resep'])->oneArray();
        if(!empty($resep_dokter_racikan)) {
          $jenisresep = 'Racikan';
        }
      }

      if($taskid1['waktu'] == '') {
        $taskid['1'] = isset_or($mlite_antrian_loket['postdate'], $pasien['tgl_registrasi']).' '.isset_or($mlite_antrian_loket['start_time'], $pasien['jam_reg']);
      }
      if($taskid2['waktu'] == '') {
        $taskid['2'] = isset_or($mlite_antrian_loket['postdate'], $pasien['tgl_registrasi']).' '.isset_or($mlite_antrian_loket['end_time'], $pasien['jam_reg']);
      }
      if($taskid3['waktu'] == '') {
        $taskid['3'] = isset_or($berkas_dikirim['dikirim'], $datetime_reg_periksa);
      }
      if($taskid4['waktu'] == '') {
        $taskid['4'] = isset_or($berkas_diterima['diterima'], $datetime_reg_periksa);
      }
      if($taskid5['waktu'] == '') {
        $taskid['5'] = isset_or($pemeriksaan_ralan['datajam'], $datetime_reg_periksa);
      }
      if($taskid6['waktu'] == '') {
        $taskid['6'] = isset_or($resep_obat['datajam'], $datetime_reg_periksa);
      }
      if($taskid7['waktu'] == '') {
        $taskid['7'] = isset_or($resep_obat['datajam2'], $datetime_reg_periksa);
      }
      $status_antrol = $this->db('mlite_antrian_referensi')->where('nomor_referensi', $nomor_referensi)->where('status_kirim', 'Sudah')->oneArray();
      $status_kirim_antrol = 'Belum';
      if($status_antrol) {
        $status_kirim_antrol = $status_antrol['status_kirim'];
      }
      echo $this->draw('taskid.input.html', ['pasien' => $pasien, 'taskid' => $taskid, 'nomor_referensi' => $nomor_referensi, 'kode_booking' => $kode_booking, 'status_kirim_antrol' => $status_kirim_antrol]);
      exit();
    }

    public function postSaveTaskIDInput()
    {
      $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $_POST['nomor_referensi'])->delete();

      if(!empty($_POST['taskid1'])) {
        $this->db('mlite_antrian_referensi_taskid')
        ->save([
          'tanggal_periksa' => $_POST['tgl_registrasi'],
          'nomor_referensi' => $_POST['nomor_referensi'],
          'taskid' => 1,
          'waktu' => strtotime($_POST['taskid1']) * 1000,
          'status' => 'Belum',
          'keterangan' => 'Mulai tunggu admisi.'
        ]);
      }

      if(!empty($_POST['taskid2'])) {
        $this->db('mlite_antrian_referensi_taskid')
        ->save([
          'tanggal_periksa' => $_POST['tgl_registrasi'],
          'nomor_referensi' => $_POST['nomor_referensi'],
          'taskid' => 2,
          'waktu' => strtotime($_POST['taskid2']) * 1000,
          'status' => 'Belum',
          'keterangan' => 'Mulai pelayanan admisi.'
        ]);
      }

      if(!empty($_POST['taskid3'])) {
        $this->db('mlite_antrian_referensi_taskid')
        ->save([
          'tanggal_periksa' => $_POST['tgl_registrasi'],
          'nomor_referensi' => $_POST['nomor_referensi'],
          'taskid' => 3,
          'waktu' => strtotime($_POST['taskid3']) * 1000,
          'status' => 'Belum',
          'keterangan' => 'Selesai pelayanan admisi atau mulai tunggu poli.'
        ]);
      }

      if(!empty($_POST['taskid4'])) {
        $this->db('mlite_antrian_referensi_taskid')
        ->save([
          'tanggal_periksa' => $_POST['tgl_registrasi'],
          'nomor_referensi' => $_POST['nomor_referensi'],
          'taskid' => 4,
          'waktu' => strtotime($_POST['taskid4']) * 1000,
          'status' => 'Belum',
          'keterangan' => 'Mulai pelayanan poli.'
        ]);
      }

      if(!empty($_POST['taskid5'])) {
        $this->db('mlite_antrian_referensi_taskid')
        ->save([
          'tanggal_periksa' => $_POST['tgl_registrasi'],
          'nomor_referensi' => $_POST['nomor_referensi'],
          'taskid' => 5,
          'waktu' => strtotime($_POST['taskid5']) * 1000,
          'status' => 'Belum',
          'keterangan' => 'Selesai pelayanan poli.'
        ]);
      }

      if(!empty($_POST['taskid6'])) {
        $this->db('mlite_antrian_referensi_taskid')
        ->save([
          'tanggal_periksa' => $_POST['tgl_registrasi'],
          'nomor_referensi' => $_POST['nomor_referensi'],
          'taskid' => 6,
          'waktu' => strtotime($_POST['taskid6']) * 1000,
          'status' => 'Belum',
          'keterangan' => 'Mulai pelayanan apotek.'
        ]);
      }

      if(!empty($_POST['taskid7'])) {
        $this->db('mlite_antrian_referensi_taskid')
        ->save([
          'tanggal_periksa' => $_POST['tgl_registrasi'],
          'nomor_referensi' => $_POST['nomor_referensi'],
          'taskid' => 7,
          'waktu' => strtotime($_POST['taskid7']) * 1000,
          'status' => 'Belum',
          'keterangan' => 'Selesai pelayanan apotek.'
        ]);
      }

      redirect(url([ADMIN, 'jkn_mobile', 'taskid']));
    }

    public function getKirimAntrian($no_rawat)
    {

      $no_rawat = revertNorawat($no_rawat);
      $reg_periksa = $this->db('reg_periksa')->where('no_rawat', $no_rawat)
        ->oneArray();
      $pasien = $this->db('pasien')->where('no_rkm_medis', $reg_periksa['no_rkm_medis'])->oneArray();
      $date = $reg_periksa['tgl_registrasi'];
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

      $maping_dokter_dpjpvclaim = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $reg_periksa['kd_dokter'])->oneArray();
      $maping_poli_bpjs = $this->db('maping_poli_bpjs')->where('kd_poli_rs', $reg_periksa['kd_poli'])->oneArray();
      $jadwaldokter = $this->db('jadwal')->where('kd_dokter', $reg_periksa['kd_dokter'])->where('kd_poli', $reg_periksa['kd_poli'])->where('hari_kerja', $hari)->oneArray();
      $no_urut_reg = substr($reg_periksa['no_reg'], 0, 3);
      $minutes = $no_urut_reg * 10;
      $cek_kuota['jam_mulai'] = date('H:i:s',strtotime('+'.$minutes.' minutes',strtotime($jadwaldokter['jam_mulai'])));
      $jenispasien = 'NON JKN';
      if($reg_periksa['kd_pj'] == $this->settings->get('jkn_mobile.kd_pj_bpjs')) {
        $jenispasien = 'JKN';
      }
      $pasienbaru = '1';
      if($reg_periksa['stts_daftar'] == 'Lama') {
        $pasienbaru = '0';
      }

      $nomorkartu = $pasien['no_peserta'];
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

      $jeniskunjungan = 3;
      $kodebooking = $this->settings->get('settings.ppk_bpjs').''.convertNorawat($reg_periksa['no_rawat']).''.$maping_poli_bpjs['kd_poli_bpjs'].''.$reg_periksa['no_reg'];
      $nomorreferensi = $this->settings->get('settings.ppk_bpjs').''.convertNorawat($reg_periksa['no_rawat']).''.$reg_periksa['no_reg'];

      if($jenispasien == 'JKN') {
          $bridging_sep = $this->db('bridging_sep')->where('no_rawat', $reg_periksa['no_rawat'])->oneArray();
          $nomorreferensi = $bridging_sep['no_rujukan'];
          if(!$bridging_sep) {
            $bridging_sep_internal = $this->db('bridging_sep_internal')->where('no_rawat', $reg_periksa['no_rawat'])->oneArray();
            $nomorreferensi = $bridging_sep_internal['no_rujukan'];
          }
      }

      $data = [
          'kodebooking' => $kodebooking,
          'jenispasien' => $jenispasien,
          'nomorkartu' => $nomorkartu,
          'nik' => $nik,
          'nohp' => $nohp,
          'kodepoli' => $maping_poli_bpjs['kd_poli_bpjs'],
          'namapoli' => $maping_poli_bpjs['nm_poli_bpjs'],
          'pasienbaru' => $pasienbaru,
          'norm' => $reg_periksa['no_rkm_medis'],
          'tanggalperiksa' => $reg_periksa['tgl_registrasi'],
          'kodedokter' => $maping_dokter_dpjpvclaim['kd_dokter_bpjs'],
          'namadokter' => $maping_dokter_dpjpvclaim['nm_dokter_bpjs'],
          'jampraktek' => substr($jadwaldokter['jam_mulai'],0,5).'-'.substr($jadwaldokter['jam_selesai'],0,5),
          'jeniskunjungan' => $jeniskunjungan,
          'nomorreferensi' => $nomorreferensi,
          'nomorantrean' => $maping_poli_bpjs['kd_poli_bpjs'].'-'.$reg_periksa['no_reg'],
          'angkaantrean' => $reg_periksa['no_reg'],
          'estimasidilayani' => strtotime($reg_periksa['tgl_registrasi'].' '.$cek_kuota['jam_mulai']) * 1000,
          'sisakuotajkn' => $jadwaldokter['kuota']-ltrim($reg_periksa['no_reg'],'0'),
          'kuotajkn' => intval($jadwaldokter['kuota']),
          'sisakuotanonjkn' => $jadwaldokter['kuota']-ltrim($reg_periksa['no_reg'],'0'),
          'kuotanonjkn' => intval($jadwaldokter['kuota']),
          'keterangan' => 'Peserta harap 30 menit lebih awal guna pencatatan administrasi.'
      ];
      $data = json_encode($data);
      $url = $this->bpjsurl.'antrean/add';
      $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, NULL);
      $data = json_decode($output, true);
      if($data['metadata']['code'] == 200 || $data['metadata']['code'] == 208) {
        $response = [
            'nomor_referensi' =>  $nomorreferensi,
            'kode_booking' => $kodebooking
        ];
        echo json_encode($response);
        $this->db('mlite_antrian_referensi')->save([
            'tanggal_periksa' => $reg_periksa['tgl_registrasi'],
            'no_rkm_medis' => $reg_periksa['no_rkm_medis'],
            'nomor_kartu' => $nomorkartu,
            'nomor_referensi' => $nomorreferensi,
            'kodebooking' => $kodebooking,
            'jenis_kunjungan' => $jeniskunjungan,
            'status_kirim' => 'Sudah',
            'keterangan' => $data['metadata']['code'].': '.$data['metadata']['message']
        ]);
      } else {
        echo $data['metadata']['code'].': '.$data['metadata']['message'];
      }
      exit();
    }

    public function getUpdateWaktu($nomor_referensi, $kode_booking, $versi)
    {

      $mlite_antrian_referensi = $this->db('mlite_antrian_referensi')->where('nomor_referensi', $nomor_referensi)->where('kodebooking', $kode_booking)->oneArray();
      $reg_periksa = $this->db('reg_periksa')
        ->where('tgl_registrasi', $mlite_antrian_referensi['tanggal_periksa'])
        ->where('no_rkm_medis', $mlite_antrian_referensi['no_rkm_medis'])
        ->oneArray();

      $resep_obat = $this->db('resep_obat')->select(['datajam' => 'concat(tgl_peresepan," ",jam_peresepan)', 'no_resep' => 'no_resep'])->where('no_rawat', $reg_periksa['no_rawat'])->oneArray();
      $jenisresep = 'Non racikan';
      if($resep_obat) {
        $resep_dokter_racikan = $this->db('resep_dokter_racikan')->where('no_resep', $resep_obat['no_resep'])->oneArray();
        if(!empty($resep_dokter_racikan)) {
          $jenisresep = 'Racikan';
        }
      }

      $taskid1 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $nomor_referensi)->where('taskid', '1')->oneArray();
      $taskid2 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $nomor_referensi)->where('taskid', '2')->oneArray();
      $taskid3 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $nomor_referensi)->where('taskid', '3')->oneArray();
      $taskid4 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $nomor_referensi)->where('taskid', '4')->oneArray();
      $taskid5 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $nomor_referensi)->where('taskid', '5')->oneArray();
      $taskid6 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $nomor_referensi)->where('taskid', '6')->oneArray();
      $taskid7 = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi', $nomor_referensi)->where('taskid', '7')->oneArray();

      echo 'Menjalankan WS taskid (1) mulai tunggu admisi<br>';
      echo '-------------------------------------<br>';
      $data = [];
      if($versi == 'v2') {
        $data1 = [
            'kodebooking' => $mlite_antrian_referensi['kodebooking'],
            'taskid' => 1,
            'waktu' => $taskid1['waktu']
        ];
      }
      if($versi == 'v3') {
        $data1 = [
            'kodebooking' => $mlite_antrian_referensi['kodebooking'],
            'taskid' => 1,
            'waktu' => $taskid1['waktu'],
            'jenisresep' => 'Tidak ada'
        ];
      }
      $data1 = json_encode($data1);
      echo 'Request:<br>';
      echo $data1;
      echo '<br>';
      $url = $this->bpjsurl.'antrean/updatewaktu';
      $output1 = BpjsService::post($url, $data1, $this->consid, $this->secretkey, $this->user_key, NULL);
      $json1 = json_decode($output1, true);
      echo 'Response:<br>';
      echo json_encode($json1);
      if(isset($json1['metadata']['code']) == 200){
        $this->db('mlite_antrian_referensi_taskid')
        ->where('nomor_referensi', $nomor_referensi)
        ->where('taskid', 1)
        ->save([
          'status' => 'Sudah',
          'keterangan' => 'Mulai tunggu admisi.'
        ]);
      } else {
        $this->db('mlite_antrian_referensi_taskid')
        ->where('nomor_referensi', $nomor_referensi)
        ->where('taskid', 1)
        ->save([
          'keterangan' => $json1['metadata']['code'].' : '.$json1['metadata']['message']
        ]);
      }
      echo '<br>-------------------------------------<br><br>';

      echo 'Menjalankan WS taskid (2) mulai pelayanan admisi<br>';
      echo '-------------------------------------<br>';
      $data = [];
      if($versi == 'v2') {
        $data2 = [
            'kodebooking' => $mlite_antrian_referensi['kodebooking'],
            'taskid' => 2,
            'waktu' => $taskid2['waktu']
        ];
      }
      if($versi == 'v3') {
        $data2 = [
            'kodebooking' => $mlite_antrian_referensi['kodebooking'],
            'taskid' => 2,
            'waktu' => $taskid2['waktu'],
            'jenisresep' => 'Tidak ada'
        ];
      }
      $data2 = json_encode($data2);
      echo 'Request:<br>';
      echo $data2;
      echo '<br>';
      $url = $this->bpjsurl.'antrean/updatewaktu';
      $output2 = BpjsService::post($url, $data2, $this->consid, $this->secretkey, $this->user_key, NULL);
      $json2 = json_decode($output2, true);
      echo 'Response:<br>';
      echo json_encode($json2);
      if(isset($json2['metadata']['code']) == 200){
        $this->db('mlite_antrian_referensi_taskid')
        ->where('nomor_referensi', $nomor_referensi)
        ->where('taskid', 2)
        ->save([
          'status' => 'Sudah',
          'keterangan' => 'Mulai pelayanan admisi.'
        ]);
      } else {
        $this->db('mlite_antrian_referensi_taskid')
        ->where('nomor_referensi', $nomor_referensi)
        ->where('taskid', 2)
        ->save([
          'keterangan' => $json2['metadata']['code'].' : '.$json2['metadata']['message']
        ]);
      }
      echo '<br>-------------------------------------<br><br>';

      echo 'Menjalankan WS taskid (3) mulai tunggu poli<br>';
      echo '-------------------------------------<br>';
      $data = [];
      if($versi == 'v2') {
        $data3 = [
            'kodebooking' => $mlite_antrian_referensi['kodebooking'],
            'taskid' => 3,
            'waktu' => $taskid3['waktu']
        ];
      }
      if($versi == 'v3') {
        $data3 = [
            'kodebooking' => $mlite_antrian_referensi['kodebooking'],
            'taskid' => 3,
            'waktu' => $taskid3['waktu'],
            'jenisresep' => 'Tidak ada'
        ];
      }
      $data3 = json_encode($data3);
      echo 'Request:<br>';
      echo $data3;
      echo '<br>';
      $url = $this->bpjsurl.'antrean/updatewaktu';
      $output3 = BpjsService::post($url, $data3, $this->consid, $this->secretkey, $this->user_key, NULL);
      $json3 = json_decode($output3, true);
      echo 'Response:<br>';
      echo json_encode($json3);
      if(isset($json3['metadata']['code']) == 200){
        $this->db('mlite_antrian_referensi_taskid')
        ->where('nomor_referensi', $nomor_referensi)
        ->where('taskid', 3)
        ->save([
          'status' => 'Sudah',
          'keterangan' => 'Mulai tunggu poli.'
        ]);
      } else {
        $this->db('mlite_antrian_referensi_taskid')
        ->where('nomor_referensi', $nomor_referensi)
        ->where('taskid', 3)
        ->save([
          'keterangan' => $json3['metadata']['code'].' : '.$json3['metadata']['message']
        ]);
      }
      echo '<br>-------------------------------------<br><br>';

      echo 'Menjalankan WS taskid (4) mulai pelayanan poli<br>';
      echo '-------------------------------------<br>';
      $data = [];
      if($versi == 'v2') {
        $data4 = [
            'kodebooking' => $mlite_antrian_referensi['kodebooking'],
            'taskid' => 4,
            'waktu' => $taskid4['waktu']
        ];
      }
      if($versi == 'v3') {
        $data4 = [
            'kodebooking' => $mlite_antrian_referensi['kodebooking'],
            'taskid' => 4,
            'waktu' => $taskid4['waktu'],
            'jenisresep' => 'Tidak ada'
        ];
      }
      $data4 = json_encode($data4);
      echo 'Request:<br>';
      echo $data4;
      echo '<br>';
      $url = $this->bpjsurl.'antrean/updatewaktu';
      $output4 = BpjsService::post($url, $data4, $this->consid, $this->secretkey, $this->user_key, NULL);
      $json4 = json_decode($output4, true);
      echo 'Response:<br>';
      echo json_encode($json4);
      if(isset($json4['metadata']['code']) == 200){
        $this->db('mlite_antrian_referensi_taskid')
        ->where('nomor_referensi', $nomor_referensi)
        ->where('taskid', 4)
        ->save([
          'status' => 'Sudah',
          'keterangan' => 'Mulai pelayanan poli.'
        ]);
      } else {
        $this->db('mlite_antrian_referensi_taskid')
        ->where('nomor_referensi', $nomor_referensi)
        ->where('taskid', 4)
        ->save([
          'keterangan' => $json4['metadata']['code'].' : '.$json4['metadata']['message']
        ]);
      }
      echo '<br>-------------------------------------<br><br>';

      echo 'Menjalankan WS taskid (5) selesai pelayanan poli<br>';
      echo '-------------------------------------<br>';
      $data = [];
      if($versi == 'v2') {
        $data5 = [
            'kodebooking' => $mlite_antrian_referensi['kodebooking'],
            'taskid' => 5,
            'waktu' => $taskid5['waktu']
        ];
      }
      if($versi == 'v3') {
        $data5 = [
            'kodebooking' => $mlite_antrian_referensi['kodebooking'],
            'taskid' => 5,
            'waktu' => $taskid5['waktu'],
            'jenisresep' => $jenisresep
        ];
      }
      $data5 = json_encode($data5);
      echo 'Request:<br>';
      echo $data5;
      echo '<br>';
      $url = $this->bpjsurl.'antrean/updatewaktu';
      $output5 = BpjsService::post($url, $data5, $this->consid, $this->secretkey, $this->user_key, NULL);
      $json5 = json_decode($output5, true);
      echo 'Response:<br>';
      echo json_encode($json5);
      if(isset($json5['metadata']['code']) == 200){
        $this->db('mlite_antrian_referensi_taskid')
        ->where('nomor_referensi', $nomor_referensi)
        ->where('taskid', 5)
        ->save([
          'status' => 'Sudah',
          'keterangan' => 'Selesai pelayanan poli.'
        ]);
      } else {
        $this->db('mlite_antrian_referensi_taskid')
        ->where('nomor_referensi', $nomor_referensi)
        ->where('taskid', 5)
        ->save([
          'keterangan' => $json5['metadata']['code'].' : '.$json5['metadata']['message']
        ]);
      }
      echo '<br>-------------------------------------<br><br>';

      echo 'Menjalankan WS taskid (6) permintaan resep apotek<br>';
      echo '-------------------------------------<br>';
      $data = [];
      if($versi == 'v2') {
        $data6 = [
            'kodebooking' => $mlite_antrian_referensi['kodebooking'],
            'taskid' => 6,
            'waktu' => $taskid6['waktu']
        ];
      }
      if($versi == 'v3') {
        $data6 = [
            'kodebooking' => $mlite_antrian_referensi['kodebooking'],
            'taskid' => 6,
            'waktu' => $taskid6['waktu'],
            'jenisresep' => $jenisresep
        ];
      }
      $data6 = json_encode($data6);
      echo 'Request:<br>';
      echo $data6;
      echo '<br>';
      $url = $this->bpjsurl.'antrean/updatewaktu';
      $output6 = BpjsService::post($url, $data6, $this->consid, $this->secretkey, $this->user_key, NULL);
      $json6 = json_decode($output6, true);
      echo 'Response:<br>';
      echo json_encode($json6);
      if(isset($json6['metadata']['code']) == 200){
        $this->db('mlite_antrian_referensi_taskid')
        ->where('nomor_referensi', $nomor_referensi)
        ->where('taskid', 6)
        ->save([
          'status' => 'Sudah',
          'keterangan' => 'Mulai pelayanan apotek.'
        ]);
      } else {
        $this->db('mlite_antrian_referensi_taskid')
        ->where('nomor_referensi', $nomor_referensi)
        ->where('taskid', 6)
        ->save([
          'keterangan' => $json6['metadata']['code'].' : '.$json6['metadata']['message']
        ]);
      }
      echo '<br>-------------------------------------<br><br>';

      echo 'Menjalankan WS taskid (7) selesai pelayanan apotek<br>';
      echo '-------------------------------------<br>';
      $data = [];
      if($versi == 'v2') {
        $data7 = [
            'kodebooking' => $mlite_antrian_referensi['kodebooking'],
            'taskid' => 7,
            'waktu' => $taskid7['waktu']
        ];
      }
      if($versi == 'v3') {
        $data7 = [
            'kodebooking' => $mlite_antrian_referensi['kodebooking'],
            'taskid' => 7,
            'waktu' => $taskid7['waktu'],
            'jenisresep' => $jenisresep
        ];
      }
      $data7 = json_encode($data7);
      echo 'Request:<br>';
      echo $data7;
      echo '<br>';
      $url = $this->bpjsurl.'antrean/updatewaktu';
      $output7 = BpjsService::post($url, $data7, $this->consid, $this->secretkey, $this->user_key, NULL);
      $json7 = json_decode($output7, true);
      echo 'Response:<br>';
      echo json_encode($json7);
      if(isset($json7['metadata']['code']) == 200){
        $this->db('mlite_antrian_referensi_taskid')
        ->where('nomor_referensi', $nomor_referensi)
        ->where('taskid', 7)
        ->save([
          'status' => 'Sudah',
          'keterangan' => 'Selesai pelayanan apotek.'
        ]);
      } else {
        $this->db('mlite_antrian_referensi_taskid')
        ->where('nomor_referensi', $nomor_referensi)
        ->where('taskid', 7)
        ->save([
          'keterangan' => $json7['metadata']['code'].' : '.$json7['metadata']['message']
        ]);
      }
      echo '<br>-------------------------------------<br><br>';

      exit();
    }

    public function getSettings()
    {
        $this->_addHeaderFiles();
        $this->assign['title'] = 'Pengaturan Modul JKN Mobile';
        $this->assign['propinsi'] = $this->db('propinsi')->where('kd_prop', $this->settings->get('jkn_mobile.kdprop'))->oneArray();
        $this->assign['kabupaten'] = $this->db('kabupaten')->where('kd_kab', $this->settings->get('jkn_mobile.kdkab'))->oneArray();
        $this->assign['kecamatan'] = $this->db('kecamatan')->where('kd_kec', $this->settings->get('jkn_mobile.kdkec'))->oneArray();
        $this->assign['kelurahan'] = $this->db('kelurahan')->where('kd_kel', $this->settings->get('jkn_mobile.kdkel'))->oneArray();
        $this->assign['suku_bangsa'] = $this->db('suku_bangsa')->toArray();
        $this->assign['bahasa_pasien'] = $this->db('bahasa_pasien')->toArray();
        $this->assign['cacat_fisik'] = $this->db('cacat_fisik')->toArray();
        $this->assign['perusahaan_pasien'] = $this->db('perusahaan_pasien')->toArray();
        $this->assign['poliklinik'] = $this->_getPoliklinik($this->settings->get('jkn_mobile.display'));
        $this->assign['exclude_taskid'] = $this->_getPoliklinik($this->settings->get('jkn_mobile.exclude_taskid'));
        $this->assign['penjab'] = $this->db('penjab')->where('status', '1')->toArray();

        $this->assign['jkn_mobile'] = htmlspecialchars_array($this->settings('jkn_mobile'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        $_POST['jkn_mobile']['display'] = implode(',', $_POST['jkn_mobile']['display']);
        $_POST['jkn_mobile']['exclude_taskid'] = implode(',', $_POST['jkn_mobile']['exclude_taskid']);
        foreach ($_POST['jkn_mobile'] as $key => $val) {
            $this->settings('jkn_mobile', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'jkn_mobile', 'settings']));
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

    public function anyAntrol()
    {
        $this->getCssCard();
        $tgl_kunjungan = date('Y-m-d');
        $bulan = substr($tgl_kunjungan, 5, 2);
        $tahun = substr($tgl_kunjungan, 0, 4);
        $tanggal = substr($tgl_kunjungan, 8, 2);
        $depanUrlTanggal = $this->bpjsurl . 'dashboard/waktutunggu/tanggal/';
        $depanUrlBulan = $this->bpjsurl . 'dashboard/waktutunggu/bulan/';
        if (isset($_POST['periode'])) {
            $waktu = $_POST['waktu'];
            $tgl_kunjungan = $_POST['periode'];
            $tgl_kunjungan = preg_replace('/\s+/', '', $tgl_kunjungan);
            $bulan = substr($tgl_kunjungan, 5, 2);
            $tahun = substr($tgl_kunjungan, 0, 4);
            $tanggal = substr($tgl_kunjungan, 8, 2);
            if ($_POST['rute'] == 'tanggal') {
                $url = $depanUrlTanggal . $tahun . '-' . $bulan . '-' . $tanggal . '/waktu/' . $waktu;
            } else {
                $url = $depanUrlBulan . $bulan . '/tahun/' . $tahun . '/waktu/' . $waktu;
            }
            $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, NULL);
            $json = json_decode($output, true);
            $response = [];
            if($json['metadata']['code'] == '200') {
              $response = $json['response']['list'];
            }
            $this->assign['list'] = $response;

            echo $this->draw('antrol.display.html', ['row' => $this->assign]);
        } else {
            $url = $depanUrlTanggal . $tahun . '-' . $bulan . '-' . $tanggal . '/waktu/server';
            $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, NULL);
            $json = json_decode($output, true);
            $response = [];
            if($json['metadata']['code'] == '200') {
              $response = $json['response']['list'];
            }
            $this->assign['list'] = $response;

            return $this->draw('antrol.html', ['row' => $this->assign]);
        }
        exit();
    }

    public function getQrAntrol(){
        $this->_addHeaderFiles();
        $this->getCssCard();
        $this->core->addJS(url(BASE_DIR.'/assets/jscripts/Chart.bundle.min.js'));
        if (isset($_GET['tgl'])) {
            $tanggal = $_GET['tgl'];
          } else {
            $tanggal = date('Y-m-d');
          }
        $sql = "SELECT * FROM bridging_sep WHERE tglsep = '$tanggal' AND jnspelayanan = '2' AND kdpolitujuan NOT IN ('IGD','HDL')";
        $query = $this->db()->pdo()->prepare($sql);
        $query->execute();
        $sep_terbit = $query->fetchAll();
        $jml_sep = 1;
        $jml_antrol = 1;
        $taskid1 = 0;
        $taskid2 = 0;
        $taskid3 = 0;
        $taskid4 = 0;
        $taskid5 = 0;
        $taskid6 = 0;
        $taskid7 = 0;
        foreach ($sep_terbit as $valuex) {
            $nomor_referensi = '';
            $cari_antrol = $this->db('mlite_antrian_referensi')->where('nomor_referensi',$valuex['no_rujukan'])->where('tanggal_periksa',$tanggal)->where('kodebooking','!=','')->where('status_kirim','Sudah')->oneArray();
            if (!$cari_antrol) {
                if ($valuex['noskdp'] != '') {
                    $cari_antrol_kontrol = $this->db('mlite_antrian_referensi')->where('nomor_referensi',$valuex['noskdp'])->where('tanggal_periksa',$tanggal)->where('kodebooking','!=','')->where('status_kirim','Sudah')->oneArray();
                    if ($cari_antrol_kontrol) {
                        $nomor_referensi = $cari_antrol_kontrol['kodebooking'];
                        $jml_antrol = $jml_antrol + 1;
                    }
                }
            } else {
                $nomor_referensi = $cari_antrol['nomor_referensi'];
                $jml_antrol = $jml_antrol + 1;
            }
            $task = $this->db('mlite_antrian_referensi_taskid')->where('nomor_referensi',$nomor_referensi)->where('status','Sudah')->toArray();
            foreach ($task as $value) {
                switch ($value['taskid']) {
                    case '1':
                        $taskid1++;
                        break;
                    case '2':
                        $taskid2++;
                        break;
                    case '3':
                        $taskid3++;
                        break;
                    case '4':
                        $taskid4++;
                        break;
                    case '5':
                        $taskid5++;
                        break;
                    case '6':
                        $taskid6++;
                        break;
                    case '7':
                        $taskid7++;
                        break;

                    default:
                        break;
                }
            }
            $jml_sep = $jml_sep + 1;
        }
        $qr_manual = $jml_antrol / $jml_sep * 100;
        $stats['tanggal'] = dateIndonesia($tanggal);
        $stats['jml_sep'] = $jml_sep;
        $stats['jml_antrol'] = $jml_antrol;
        $stats['qr_manual'] = number_format($qr_manual,2);
        $stats['taskid1'] = $taskid1;
        $stats['taskid2'] = $taskid2;
        $stats['taskid3'] = $taskid3;
        $stats['taskid4'] = $taskid4;
        $stats['taskid5'] = $taskid5;
        $stats['taskid6'] = $taskid6;
        $stats['taskid7'] = $taskid7;
        return $this->draw('manage.qurate.html', ['stats' => $stats]);
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

    public function getBookingAntrol()
    {
        $this->_addHeaderFiles();
        return $this->draw('bookingantrol.html', ['row' => $this->db('mlite_antrian_referensi')->toArray()]);
    }

    public function getModalAntrol($noref)
    {
        $this->tpl->set('noref',$noref);
        echo $this->tpl->draw(MODULES . '/jkn_mobile/view/admin/batalantrol.html', true);
        exit();
    }

    public function postHapusAntrol()
    {
        $referensi = $this->db('mlite_antrian_referensi')->where('kodebooking', $_POST['kodebooking'])->oneArray();
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
            $notif = 'Data Booking tidak ditemukan';
        }else{
            if(date("Y-m-d")>$booking_registrasi['tanggal_periksa']){
                $notif = 'Pembatalan Antrean tidak berlaku mundur';
            }else if($booking_registrasi['status']=='Terdaftar'){
                $notif = 'Pasien Sudah Checkin, Pendaftaran Tidak Bisa Dibatalkan';
            }else if($booking_registrasi['status']=='Belum'){
                $batal = $this->db('booking_registrasi')->where('no_rkm_medis', $pasien['no_rkm_medis'])->where('tanggal_periksa', $referensi['tanggal_periksa'])->delete();
                if(!$this->db('booking_registrasi')->where('no_rkm_medis', $pasien['no_rkm_medis'])->where('tanggal_periksa', $referensi['tanggal_periksa'])->oneArray()){
                    $this->db('mlite_antrian_referensi_batal')->save([
                        'tanggal_batal' => date('Y-m-d'),
                        'nomor_referensi' => $referensi['nomor_referensi'],
                        'kodebooking' => $_POST['kodebooking'],
                        'keterangan' => $_POST['keterangan']
                    ]);
                    $this->db('mlite_antrian_referensi')->where('kodebooking', $_POST['kodebooking'])->delete();
                    if (!$this->db('mlite_antrian_referensi')->where('kodebooking', $_POST['kodebooking'])->oneArray()) {
                        date_default_timezone_set('UTC');
                        $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
                        $key = $this->consid.$this->secretkey.$tStamp;

                        $data = [
                            'kodebooking' => $_POST['kodebooking'],
                            'keterangan' => $_POST['keterangan']
                        ];

                        $data = json_encode($data);
                        $url = $this->bpjsurl.'antrean/batal';
                        $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, $tStamp);
                        $json = json_decode($output, true);
                        if ($json == NULL) {
                            $notif = 'Data Booking di JKN Mobile Tidak Ada!<br>Berhasil Dibatalkan di SIMRS';
                        } else if ($json['metadata']['code'] == 200) {
                            $notif = 'Berhasil Dibatalkan di JKN Mobile';
                        }
                    }
                }else{
                    $notif = 'Maaf Terjadi Kesalahan, Hubungi Admnistrator..';
                }
            }
        }
        //exit();
        return $this->draw('hapusantrol.html', ['row' => $this->db('mlite_antrian_referensi')->toArray(), 'notif' => $notif]);
    }

    public function postSaveTaskID1()
    {

    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/jkn_mobile/js/admin/jkn_mobile.js');
        exit();
    }

    public function getCssCard()
    {
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
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

        // MODULE SCRIPTS
        $this->core->addJS(url([ADMIN, 'jkn_mobile', 'javascript']), 'footer');
    }

}
