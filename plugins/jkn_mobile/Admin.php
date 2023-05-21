<?php

namespace Plugins\JKN_Mobile;

use Systems\AdminModule;
use Systems\Lib\BpjsService;

class Admin extends AdminModule
{

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
            'Dashboard Antrol BPJS' => 'antrol',
            'Pengaturan' => 'settings',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Katalog', 'url' => url([ADMIN, 'jkn_mobile', 'index']), 'icon' => 'tasks', 'desc' => 'Index JKN Mobile V2'],
        ['name' => 'Mapping Poliklinik', 'url' => url([ADMIN, 'jkn_mobile', 'mappingpoli']), 'icon' => 'tasks', 'desc' => 'Mapping Poliklinik JKN Mobile V2'],
        ['name' => 'Add Mapping Poliklinik', 'url' => url([ADMIN, 'jkn_mobile', 'addmappingpoli']), 'icon' => 'tasks', 'desc' => 'Add mapping poliklinik JKN Mobile V2'],
        ['name' => 'Mapping Dokter', 'url' => url([ADMIN, 'jkn_mobile', 'mappingdokter']), 'icon' => 'tasks', 'desc' => 'Mapping Dokter JKN Mobile V2'],
        ['name' => 'Add Mapping Dokter', 'url' => url([ADMIN, 'jkn_mobile', 'addmappingdokter']), 'icon' => 'tasks', 'desc' => 'Add Mapping Dokter JKN Mobile V2'],
        ['name' => 'Jadwal Dokter HFIS', 'url' => url([ADMIN, 'jkn_mobile', 'jadwaldokter']), 'icon' => 'tasks', 'desc' => 'Jadwal Dokter HFIS JKN Mobile V2'],
        ['name' => 'Data Booking Antrol', 'url' => url([ADMIN, 'jkn_mobile', 'bookingantrol']), 'icon' => 'list', 'desc' => 'Booking Antrol JKN Mobile V2'],
        ['name' => 'Task ID', 'url' => url([ADMIN, 'jkn_mobile', 'taskid']), 'icon' => 'tasks', 'desc' => 'Task ID JKN Mobile V2'],
        ['name' => 'Dashboard Antrol BPJS', 'url' => url([ADMIN, 'jkn_mobile', 'antrol']), 'icon' => 'tasks', 'desc' => 'Antrian Online BPJS'],
        ['name' => 'Pengaturan', 'url' => url([ADMIN, 'jkn_mobile', 'settings']), 'icon' => 'tasks', 'desc' => 'Pengaturan JKN Mobile V2'],
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
        return $this->draw('mappingpoli.html', ['row' => $this->core->mysql('maping_poli_bpjs')->toArray()]);
    }

    public function getAddMappingPoli()
    {
        $this->_addHeaderFiles();
        $this->assign['poliklinik'] = $this->core->mysql('poliklinik')->where('status','1')->toArray();
        return $this->draw('form.mappingpoli.html', ['row' => $this->assign]);
    }

    public function postPoliklinik_Save()
    {

        $location = url([ADMIN, 'jkn_mobile', 'addmappingpoli']);

        unset($_POST['save']);

        $query = $this->core->mysql('maping_poli_bpjs')->save([
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
        if ($this->core->mysql('maping_poli_bpjs')->where('kd_poli_rs', $id)->delete()) {
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
        return $this->draw('mappingdokter.html', ['row' => $this->core->mysql('maping_dokter_dpjpvclaim')->toArray()]);
    }


    public function getAddMappingDokter()
    {
        $this->_addHeaderFiles();
        $this->assign['dokter'] = $this->core->mysql('dokter')->where('status','1')->toArray();
        return $this->draw('form.mappingdokter.html', ['row' => $this->assign]);
    }

    public function postDokter_Save()
    {

        $location = url([ADMIN, 'jkn_mobile', 'addmappingdokter']);

        unset($_POST['save']);

        $query = $this->core->mysql('maping_dokter_dpjpvclaim')->save([
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
        if ($this->core->mysql('maping_dokter_dpjpvclaim')->where('kd_dokter', $id)->delete()) {
            $this->notify('success', 'Hapus maping poli bpjs sukses');
        } else {
            $this->notify('failure', 'Hapus maping poli bpjs gagal');
        }
        redirect(url([ADMIN, 'jkn_mobile', 'mappingdokter']));
    }

    public function getJadwalDokter()
    {
        $maping_poli_bpjs = $this->core->mysql('maping_poli_bpjs')->toArray();
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
      $query = $this->core->mysql()->pdo()->prepare("SELECT pasien.no_peserta,pasien.no_rkm_medis,pasien.no_ktp,pasien.no_tlp,reg_periksa.no_reg,reg_periksa.no_rawat,reg_periksa.tgl_registrasi,reg_periksa.kd_dokter,dokter.nm_dokter,reg_periksa.kd_poli,poliklinik.nm_poli,reg_periksa.stts_daftar,reg_periksa.no_rkm_medis
      FROM reg_periksa INNER JOIN pasien ON reg_periksa.no_rkm_medis=pasien.no_rkm_medis INNER JOIN dokter ON reg_periksa.kd_dokter=dokter.kd_dokter INNER JOIN poliklinik ON reg_periksa.kd_poli=poliklinik.kd_poli WHERE reg_periksa.tgl_registrasi='$date' AND reg_periksa.kd_poli NOT IN ('$exclude_taskid')
      ORDER BY concat(reg_periksa.tgl_registrasi,' ',reg_periksa.jam_reg)");
      $query->execute();
      $query = $query->fetchAll(\PDO::FETCH_ASSOC);;

      $rows = [];
      foreach ($query as $q) {
          $reg_periksa = $this->core->mysql('reg_periksa')->where('tgl_registrasi', $date)->where('no_rkm_medis', $q['no_rkm_medis'])->where('stts', '<>', 'Batal')->oneArray();
          $reg_periksa2 = $this->core->mysql('reg_periksa')->where('tgl_registrasi', $date)->where('no_rkm_medis', $q['no_rkm_medis'])->where('stts', 'Batal')->oneArray();
          $batal = '0000-00-00 00:00:00';
          if($reg_periksa2) {
            $batal = $q['tgl_registrasi'].' '.date('H:i:s');
          }
          $mlite_antrian_referensi = $this->core->mysql('mlite_antrian_referensi')->where('tanggal_periksa', $q['tgl_registrasi'])->where('nomor_kartu', $q['no_peserta'])->oneArray();
          if(!$mlite_antrian_referensi) {
              $mlite_antrian_referensi = $this->core->mysql('mlite_antrian_referensi')->where('tanggal_periksa', $q['tgl_registrasi'])->where('nomor_kartu', $q['no_rkm_medis'])->oneArray();
          }
          $mutasi_berkas = $this->core->mysql('mutasi_berkas')->select('dikirim')->where('no_rawat', $reg_periksa['no_rawat'])->where('dikirim', '<>', '0000-00-00 00:00:00')->oneArray();
          $mutasi_berkas2 = $this->core->mysql('mutasi_berkas')->select('diterima')->where('no_rawat', $reg_periksa['no_rawat'])->where('diterima', '<>', '0000-00-00 00:00:00')->oneArray();
          $pemeriksaan_ralan = $this->core->mysql('pemeriksaan_ralan')->select(['datajam' => 'concat(tgl_perawatan," ",jam_rawat)'])->where('no_rawat', $reg_periksa['no_rawat'])->oneArray();
          $resep_obat = $this->core->mysql('resep_obat')->select(['datajam' => 'concat(tgl_perawatan," ",jam)'])->where('no_rawat', $reg_periksa['no_rawat'])->oneArray();
          $resep_obat2 = $this->core->mysql('resep_obat')->select(['datajam' => 'concat(tgl_peresepan," ",jam_peresepan)'])->where('no_rawat', $reg_periksa['no_rawat'])->where('concat(tgl_perawatan," ",jam)', '<>', 'concat(tgl_peresepan," ",jam_peresepan)')->oneArray();

          $mlite_antrian_loket = $this->core->mysql('mlite_antrian_loket')->where('postdate', $date)->where('no_rkm_medis', $q['no_rkm_medis'])->oneArray();
          $task1 = '';
          $task2 = '';
          if($mlite_antrian_loket) {
            $task1 = $mlite_antrian_loket['postdate'].' '.$mlite_antrian_loket['start_time'];
            $task2 = $mlite_antrian_loket['postdate'].' '.$mlite_antrian_loket['end_time'];
          }
          $q['nomor_referensi'] = $mlite_antrian_referensi['nomor_referensi'];
          /*$q['task1'] = strtotime($task1) * 1000;
          $q['task2'] = strtotime($task2) * 1000;
          $q['task3'] = strtotime($mutasi_berkas['dikirim']) * 1000;
          $q['task4'] = strtotime($mutasi_berkas2['diterima']) * 1000;
          $q['task5'] = strtotime($pemeriksaan_ralan['datajam']) * 1000;
          $q['task6'] = strtotime($resep_obat['datajam']) * 1000;
          $q['task7'] = strtotime($resep_obat2['datajam']) * 1000;
          $q['task99'] = $batal;*/
          $q['task1'] = $task1;
          $q['task2'] = $task2;
          $q['task3'] = $mutasi_berkas['dikirim'];
          $q['task4'] = $mutasi_berkas2['diterima'];
          $q['task5'] = $pemeriksaan_ralan['datajam'];
          $q['task6'] = $resep_obat2['datajam'];
          $q['task7'] = $resep_obat['datajam'];
          $q['task99'] = $batal;
          $rows[] = $q;
      }

      $taskid = $rows;
      return $this->draw('taskid.html', ['taskid' => $taskid]);
    }

    public function getSettings()
    {
        $this->_addHeaderFiles();
        $this->assign['title'] = 'Pengaturan Modul JKN Mobile';
        $this->assign['propinsi'] = $this->core->mysql('propinsi')->where('kd_prop', $this->settings->get('jkn_mobile.kdprop'))->oneArray();
        $this->assign['kabupaten'] = $this->core->mysql('kabupaten')->where('kd_kab', $this->settings->get('jkn_mobile.kdkab'))->oneArray();
        $this->assign['kecamatan'] = $this->core->mysql('kecamatan')->where('kd_kec', $this->settings->get('jkn_mobile.kdkec'))->oneArray();
        $this->assign['kelurahan'] = $this->core->mysql('kelurahan')->where('kd_kel', $this->settings->get('jkn_mobile.kdkel'))->oneArray();
        $this->assign['suku_bangsa'] = $this->core->mysql('suku_bangsa')->toArray();
        $this->assign['bahasa_pasien'] = $this->core->mysql('bahasa_pasien')->toArray();
        $this->assign['cacat_fisik'] = $this->core->mysql('cacat_fisik')->toArray();
        $this->assign['perusahaan_pasien'] = $this->core->mysql('perusahaan_pasien')->toArray();
        $this->assign['poliklinik'] = $this->_getPoliklinik($this->settings->get('jkn_mobile.display'));
        $this->assign['exclude_taskid'] = $this->_getPoliklinik($this->settings->get('jkn_mobile.exclude_taskid'));
        $this->assign['penjab'] = $this->core->mysql('penjab')->where('status', '1')->toArray();

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
        $rows = $this->core->mysql('poliklinik')->toArray();

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

    public function getAjax()
    {
        header('Content-type: text/html');
        $show = isset($_GET['show']) ? $_GET['show'] : "";
        switch($show){
        	default:
          break;
        	case "propinsi":
          $propinsi = $this->core->mysql('propinsi')->toArray();
          foreach ($propinsi as $row) {
            echo '<tr class="pilihpropinsi" data-kdprop="'.$row['kd_prop'].'" data-namaprop="'.$row['nm_prop'].'">';
      			echo '<td>'.$row['kd_prop'].'</td>';
      			echo '<td>'.$row['nm_prop'].'</td>';
      			echo '</tr>';
          }
          break;
          case "kabupaten":
          $kabupaten = $this->core->mysql('kabupaten')->toArray();
          foreach ($kabupaten as $row) {
            echo '<tr class="pilihkabupaten" data-kdkab="'.$row['kd_kab'].'" data-namakab="'.$row['nm_kab'].'">';
      			echo '<td>'.$row['kd_kab'].'</td>';
      			echo '<td>'.$row['nm_kab'].'</td>';
      			echo '</tr>';
          }
          break;
          case "kecamatan":
          $kecamatan = $this->core->mysql('kecamatan')->toArray();
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

          $query = $this->core->mysql()->pdo()->prepare($sql);
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

          $data = $this->core->mysql()->pdo()->prepare($sql);
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
        return $this->draw('bookingantrol.html', ['row' => $this->core->mysql('mlite_antrian_referensi')->toArray()]);
    }

    public function getModalAntrol($noref)
    {
        $this->tpl->set('noref',$noref);
        echo $this->tpl->draw(MODULES . '/jkn_mobile/view/admin/batalantrol.html', true);
        exit();
    }

    public function postHapusAntrol()
    {
        $referensi = $this->core->mysql('mlite_antrian_referensi')->where('kodebooking', $_POST['kodebooking'])->oneArray();
        $booking_registrasi = [];
        $pasien = [];
        if($referensi) {
            $pasien = $this->core->mysql('pasien')->where('no_peserta', $referensi['nomor_kartu'])->oneArray();
            $booking_registrasi = $this->core->mysql('booking_registrasi')
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
                $batal = $this->core->mysql('booking_registrasi')->where('no_rkm_medis', $pasien['no_rkm_medis'])->where('tanggal_periksa', $referensi['tanggal_periksa'])->delete();
                if(!$this->core->mysql('booking_registrasi')->where('no_rkm_medis', $pasien['no_rkm_medis'])->where('tanggal_periksa', $referensi['tanggal_periksa'])->oneArray()){
                    $this->core->mysql('mlite_antrian_referensi_batal')->save([
                        'tanggal_batal' => date('Y-m-d'),
                        'nomor_referensi' => $referensi['nomor_referensi'],
                        'kodebooking' => $_POST['kodebooking'],
                        'keterangan' => $_POST['keterangan']
                    ]);
                    $this->core->mysql('mlite_antrian_referensi')->where('kodebooking', $_POST['kodebooking'])->delete();
                    if (!$this->core->mysql('mlite_antrian_referensi')->where('kodebooking', $_POST['kodebooking'])->oneArray()) {
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
        return $this->draw('hapusantrol.html', ['row' => $this->core->mysql('mlite_antrian_referensi')->toArray(), 'notif' => $notif]);
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
