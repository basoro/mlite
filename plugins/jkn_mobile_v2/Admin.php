<?php

namespace Plugins\JKN_Mobile_V2;

use Systems\AdminModule;
use Systems\Lib\BpjsService;

class Admin extends AdminModule
{

    public function init()
    {
        $this->consid = $this->settings->get('jkn_mobile_v2.BpjsConsID');
        $this->secretkey = $this->settings->get('jkn_mobile_v2.BpjsSecretKey');
        $this->bpjsurl = $this->settings->get('jkn_mobile_v2.BpjsAntrianUrl');
        $this->user_key = $this->settings->get('jkn_mobile_v2.BpjsUserKey');
    }

    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Index' => 'index',
            'Mapping Poliklinik' => 'mappingpoli',
            'Add Mapping Poliklinik' => 'addmappingpoli',
            'Mapping Dokter' => 'mappingdokter',
            'Add Mapping Dokter' => 'addmappingdokter',
            'Jadwal Dokter HFIS' => 'jadwaldokter',
            'Task ID' => 'taskid',
            'Pengaturan' => 'settings',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Index', 'url' => url([ADMIN, 'jkn_mobile_v2', 'index']), 'icon' => 'tasks', 'desc' => 'Index JKN Mobile V2'],
        ['name' => 'Mapping Poliklinik', 'url' => url([ADMIN, 'jkn_mobile_v2', 'mappingpoli']), 'icon' => 'tasks', 'desc' => 'Mapping Poliklinik JKN Mobile V2'],
        ['name' => 'Add Mapping Poliklinik', 'url' => url([ADMIN, 'jkn_mobile_v2', 'addmappingpoli']), 'icon' => 'tasks', 'desc' => 'Add mapping poliklinik JKN Mobile V2'],
        ['name' => 'Mapping Dokter', 'url' => url([ADMIN, 'jkn_mobile_v2', 'mappingdokter']), 'icon' => 'tasks', 'desc' => 'Mapping Dokter JKN Mobile V2'],
        ['name' => 'Add Mapping Dokter', 'url' => url([ADMIN, 'jkn_mobile_v2', 'addmappingdokter']), 'icon' => 'tasks', 'desc' => 'Add Mapping Dokter JKN Mobile V2'],
        ['name' => 'Jadwal Dokter HFIS', 'url' => url([ADMIN, 'jkn_mobile_v2', 'jadwaldokter']), 'icon' => 'tasks', 'desc' => 'Jadwal Dokter HFIS JKN Mobile V2'],
        ['name' => 'Task ID', 'url' => url([ADMIN, 'jkn_mobile_v2', 'taskid']), 'icon' => 'tasks', 'desc' => 'Task ID JKN Mobile V2'],
        ['name' => 'Pengaturan', 'url' => url([ADMIN, 'jkn_mobile_v2', 'settings']), 'icon' => 'tasks', 'desc' => 'Pengaturan JKN Mobile V2'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getIndex()
    {
        return $this->draw('index.html');
    }

    public function getRefPoli()
    {
        $url = $this->bpjsurl.'ref/poli';
        $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key);
        $json = json_decode($output, true);
        //echo json_encode($json);
        $code = $json['metadata']['code'];
        $message = $json['metadata']['message'];
        $stringDecrypt = stringDecrypt($this->consid, $this->secretkey, $json['response']);
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

        $location = url([ADMIN, 'jkn_mobile_v2', 'addmappingpoli']);

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
        redirect(url([ADMIN, 'jkn_mobile_v2', 'mappingpoli']));
    }

    public function getRefDokter()
    {
        $url = $this->bpjsurl.'ref/dokter';
        $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key);
        $json = json_decode($output, true);
        //echo json_encode($json);
        $code = $json['metadata']['code'];
        $message = $json['metadata']['message'];
        $stringDecrypt = stringDecrypt($this->consid, $this->secretkey, $json['response']);
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

        $location = url([ADMIN, 'jkn_mobile_v2', 'addmappingdokter']);

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
        redirect(url([ADMIN, 'jkn_mobile_v2', 'mappingdokter']));
    }

    public function getJadwalDokter()
    {
        return $this->draw('jadwaldokter.html');
    }

    public function getTaskID()
    {
      $date = date('Y-m-d');
      //$date = '2022-01-20';
      $query = $this->db()->pdo()->prepare("SELECT pasien.no_peserta,pasien.no_rkm_medis,pasien.no_ktp,pasien.no_tlp,reg_periksa.no_reg,reg_periksa.no_rawat,reg_periksa.tgl_registrasi,reg_periksa.kd_dokter,dokter.nm_dokter,reg_periksa.kd_poli,poliklinik.nm_poli,reg_periksa.stts_daftar,reg_periksa.no_rkm_medis
      FROM reg_periksa INNER JOIN pasien ON reg_periksa.no_rkm_medis=pasien.no_rkm_medis INNER JOIN dokter ON reg_periksa.kd_dokter=dokter.kd_dokter INNER JOIN poliklinik ON reg_periksa.kd_poli=poliklinik.kd_poli WHERE reg_periksa.tgl_registrasi='$date' AND reg_periksa.kd_poli !='IGDK'
      ORDER BY concat(reg_periksa.tgl_registrasi,' ',reg_periksa.jam_reg)");
      $query->execute();
      $query = $query->fetchAll(\PDO::FETCH_ASSOC);;

      foreach ($query as $q) {
          $reg_periksa = $this->db('reg_periksa')->where('tgl_registrasi', $date)->where('no_rkm_medis', $q['no_rkm_medis'])->oneArray();
          $mlite_antrian_referensi = $this->db('mlite_antrian_referensi')->where('tanggal_periksa', $q['tgl_registrasi'])->where('nomor_kartu', $q['no_peserta'])->oneArray();
          if(!$mlite_antrian_referensi) {
              $mlite_antrian_referensi = $this->db('mlite_antrian_referensi')->where('tanggal_periksa', $q['tgl_registrasi'])->where('nomor_kartu', $q['no_rkm_medis'])->oneArray();
          }
          $mutasi_berkas = $this->db('mutasi_berkas')->select('dikirim')->where('no_rawat', $reg_periksa['no_rawat'])->where('dikirim', '<>', '0000-00-00 00:00:00')->oneArray();
          $pemeriksaan_ralan = $this->db('pemeriksaan_ralan')->select(['datajam' => 'concat(tgl_perawatan," ",jam_rawat)'])->where('no_rawat', $reg_periksa['no_rawat'])->oneArray();
          $resep_obat = $this->db('resep_obat')->select(['datajam' => 'concat(tgl_peresepan," ",jam_peresepan)'])->where('no_rawat', $reg_periksa['no_rawat'])->oneArray();
          $resep_obat2 = $this->db('resep_obat')->select(['datajam' => 'concat(tgl_perawatan," ",jam)'])->where('no_rawat', $reg_periksa['no_rawat'])->where('concat(tgl_perawatan," ",jam)', '<>', 'concat(tgl_peresepan," ",jam_peresepan)')->oneArray();

          $q['nomor_referensi'] = $mlite_antrian_referensi['nomor_referensi'];
          $q['task1'] = '0';
          $q['task2'] = '0';
          $q['task3'] = strtotime($mutasi_berkas['dikirim']) * 1000;
          $q['task4'] = strtotime($mutasi_berkas['diterima']) * 1000;
          $q['task5'] = strtotime($pemeriksaan_ralan['datajam']) * 1000;
          $q['task6'] = strtotime($resep_obat['datajam']) * 1000;
          $q['task7'] = strtotime($resep_obat2['datajam']) * 1000;
          $q['task99'] = strtotime(date('Y-m-d h:i:s')) * 1000;
          $rows[] = $q;
      }

      $taskid = $rows;
      return $this->draw('taskid.html', ['taskid' => $taskid]);
    }

    public function getSettings()
    {
        $this->_addHeaderFiles();
        $this->assign['title'] = 'Pengaturan Modul JKN Mobile';
        $this->assign['propinsi'] = $this->db('propinsi')->where('kd_prop', $this->settings->get('jkn_mobile_v2.kdprop'))->oneArray();
        $this->assign['kabupaten'] = $this->db('kabupaten')->where('kd_kab', $this->settings->get('jkn_mobile_v2.kdkab'))->oneArray();
        $this->assign['kecamatan'] = $this->db('kecamatan')->where('kd_kec', $this->settings->get('jkn_mobile_v2.kdkec'))->oneArray();
        $this->assign['kelurahan'] = $this->db('kelurahan')->where('kd_kel', $this->settings->get('jkn_mobile_v2.kdkel'))->oneArray();
        $this->assign['suku_bangsa'] = $this->db('suku_bangsa')->toArray();
        $this->assign['bahasa_pasien'] = $this->db('bahasa_pasien')->toArray();
        $this->assign['cacat_fisik'] = $this->db('cacat_fisik')->toArray();
        $this->assign['perusahaan_pasien'] = $this->db('perusahaan_pasien')->toArray();
        $this->assign['poliklinik'] = $this->_getPoliklinik($this->settings->get('jkn_mobile_v2.display'));

        $this->assign['jkn_mobile_v2'] = htmlspecialchars_array($this->settings('jkn_mobile_v2'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        $_POST['jkn_mobile_v2']['display'] = implode(',', $_POST['jkn_mobile_v2']['display']);
        foreach ($_POST['jkn_mobile_v2'] as $key => $val) {
            $this->settings('jkn_mobile_v2', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'jkn_mobile_v2', 'settings']));
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

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/jkn_mobile_v2/js/admin/jkn_mobile_v2.js');
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

        // MODULE SCRIPTS
        $this->core->addJS(url([ADMIN, 'jkn_mobile_v2', 'javascript']), 'footer');
    }

}
