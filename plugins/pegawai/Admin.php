<?php
namespace Plugins\Pegawai;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
        ];
    }

    public function getManage()
    {
        $this->_addHeaderFiles();
        $this->assign['jnj_jabatan'] = $this->core->db->select('jnj_jabatan', '*');
        $this->assign['kelompok_jabatan'] = $this->core->db->select('kelompok_jabatan', '*');
        $this->assign['resiko_kerja'] = $this->core->db->select('resiko_kerja', '*');
        $this->assign['departemen'] = $this->core->db->select('departemen', '*');
        $this->assign['bidang'] = $this->core->db->select('bidang', '*');
        $this->assign['stts_wp'] = $this->core->db->select('stts_wp', '*');
        $this->assign['stts_kerja'] = $this->core->db->select('stts_kerja', '*');
        $this->assign['pendidikan'] = $this->core->db->select('pendidikan', '*');
        $this->assign['bank'] = $this->core->db->select('bank', '*');
        $this->assign['emergency_index'] = $this->core->db->select('emergency_index', '*');
        $this->assign['ms_kerja'] = $this->core->getEnum('pegawai', 'ms_kerja');
        $this->assign['stts_aktif'] = $this->core->getEnum('pegawai', 'stts_aktif');

        $disabled_menu = $this->core->loadDisabledMenu('pegawai'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['pegawai' => $this->assign, 'disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'id');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_pegawai= isset_or($_POST['search_field_pegawai']);
        $search_text_pegawai = isset_or($_POST['search_text_pegawai']);

        if ($search_text_pegawai != '') {
          $where[$search_field_pegawai.'[~]'] = $search_text_pegawai;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('pegawai', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('pegawai', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('pegawai', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'id'=>$row['id'],
                'nik'=>$row['nik'],
                'nama'=>$row['nama'],
                'jk'=>$row['jk'],
                'jbtn'=>$row['jbtn'],
                'jnj_jabatan'=>$row['jnj_jabatan'],
                'kode_kelompok'=>isset_or($row['kode_kelompok']),
                'kode_resiko'=>isset_or($row['kode_resiko']),
                'kode_emergency'=>isset_or($row['kode_emergency']),
                'departemen'=>$row['departemen'],
                'bidang'=>$row['bidang'],
                'stts_wp'=>$row['stts_wp'],
                'stts_kerja'=>$row['stts_kerja'],
                'npwp'=>$row['npwp'],
                'pendidikan'=>$row['pendidikan'],
                'gapok'=>$row['gapok'],
                'tmp_lahir'=>$row['tmp_lahir'],
                'tgl_lahir'=>$row['tgl_lahir'],
                'alamat'=>$row['alamat'],
                'kota'=>$row['kota'],
                'mulai_kerja'=>$row['mulai_kerja'],
                'ms_kerja'=>$row['ms_kerja'],
                'indexins'=>$row['indexins'],
                'bpd'=>$row['bpd'],
                'rekening'=>$row['rekening'],
                'stts_aktif'=>$row['stts_aktif'],
                'wajibmasuk'=>$row['wajibmasuk'],
                'pengurang'=>$row['pengurang'],
                'indek'=>$row['indek'],
                'mulai_kontrak'=>$row['mulai_kontrak'],
                'cuti_diambil'=>$row['cuti_diambil'],
                'dankes'=>$row['dankes'],
                'photo'=>$row['photo'],
                'no_ktp'=>$row['no_ktp']
            );
        }

        ## Response
        http_response_code(200);
        $response = array(
            "draw" => intval($draw), 
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        );

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('pegawai => postData');
        }

        echo json_encode($response);
        exit();
    }

    public function postAksi()
    {
        if(isset($_POST['typeact'])){ 
            $act = $_POST['typeact']; 
        }else{ 
            $act = ''; 
        }

        if ($act=='add') {

            if($this->core->loadDisabledMenu('pegawai')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $id = $_POST['id'];
            $nik = $_POST['nik'];
            $nama = $_POST['nama'];
            $jk = $_POST['jk'];
            $jbtn = $_POST['jbtn'];
            $jnj_jabatan = $_POST['jnj_jabatan'];
            $kode_kelompok = $_POST['kode_kelompok'];
            $kode_resiko = $_POST['kode_resiko'];
            $kode_emergency = $_POST['kode_emergency'];
            $departemen = $_POST['departemen'];
            $bidang = $_POST['bidang'];
            $stts_wp = $_POST['stts_wp'];
            $stts_kerja = $_POST['stts_kerja'];
            $npwp = $_POST['npwp'];
            $pendidikan = $_POST['pendidikan'];
            $gapok = $_POST['gapok'];
            $tmp_lahir = $_POST['tmp_lahir'];
            $tgl_lahir = $_POST['tgl_lahir'];
            $alamat = $_POST['alamat'];
            $kota = $_POST['kota'];
            $mulai_kerja = $_POST['mulai_kerja'];
            $ms_kerja = $_POST['ms_kerja'];
            $indexins = $_POST['indexins'];
            $bpd = $_POST['bpd'];
            $rekening = $_POST['rekening'];
            $stts_aktif = $_POST['stts_aktif'];
            $wajibmasuk = $_POST['wajibmasuk'];
            $pengurang = $_POST['pengurang'];
            $indek = $_POST['indek'];
            $mulai_kontrak = $_POST['mulai_kontrak'];
            $cuti_diambil = $_POST['cuti_diambil'];
            $dankes = $_POST['dankes'];
            $photo = $_POST['photo'];
            $no_ktp = $_POST['no_ktp'];
            
            $result = $this->core->db->insert('pegawai', [
              'id'=>$id, 'nik'=>$nik, 'nama'=>$nama, 'jk'=>$jk, 'jbtn'=>$jbtn, 'jnj_jabatan'=>$jnj_jabatan, 'kode_kelompok'=>$kode_kelompok, 'kode_resiko'=>$kode_resiko, 'kode_emergency'=>$kode_emergency, 'departemen'=>$departemen, 'bidang'=>$bidang, 'stts_wp'=>$stts_wp, 'stts_kerja'=>$stts_kerja, 'npwp'=>$npwp, 'pendidikan'=>$pendidikan, 'gapok'=>$gapok, 'tmp_lahir'=>$tmp_lahir, 'tgl_lahir'=>$tgl_lahir, 'alamat'=>$alamat, 'kota'=>$kota, 'mulai_kerja'=>$mulai_kerja, 'ms_kerja'=>$ms_kerja, 'indexins'=>$indexins, 'bpd'=>$bpd, 'rekening'=>$rekening, 'stts_aktif'=>$stts_aktif, 'wajibmasuk'=>$wajibmasuk, 'pengurang'=>$pengurang, 'indek'=>$indek, 'mulai_kontrak'=>$mulai_kontrak, 'cuti_diambil'=>$cuti_diambil, 'dankes'=>$dankes, 'photo'=>$photo, 'no_ktp'=>$no_ktp
            ]);


            if (!empty($result)){
              http_response_code(200);
              $data = array(
                'code' => '200', 
                'status' => 'success', 
                'msg' => 'Data telah ditambah'
              );
            } else {
              http_response_code(201);
              $data = array(
                'code' => '201', 
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('pegawai => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('pegawai')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $id = $_POST['id'];
            $nik = $_POST['nik'];
            $nama = $_POST['nama'];
            $jk = $_POST['jk'];
            $jbtn = $_POST['jbtn'];
            $jnj_jabatan = $_POST['jnj_jabatan'];
            $kode_kelompok = $_POST['kode_kelompok'];
            $kode_resiko = $_POST['kode_resiko'];
            $kode_emergency = $_POST['kode_emergency'];
            $departemen = $_POST['departemen'];
            $bidang = $_POST['bidang'];
            $stts_wp = $_POST['stts_wp'];
            $stts_kerja = $_POST['stts_kerja'];
            $npwp = $_POST['npwp'];
            $pendidikan = $_POST['pendidikan'];
            $gapok = $_POST['gapok'];
            $tmp_lahir = $_POST['tmp_lahir'];
            $tgl_lahir = $_POST['tgl_lahir'];
            $alamat = $_POST['alamat'];
            $kota = $_POST['kota'];
            $mulai_kerja = $_POST['mulai_kerja'];
            $ms_kerja = $_POST['ms_kerja'];
            $indexins = $_POST['indexins'];
            $bpd = $_POST['bpd'];
            $rekening = $_POST['rekening'];
            $stts_aktif = $_POST['stts_aktif'];
            $wajibmasuk = $_POST['wajibmasuk'];
            $pengurang = $_POST['pengurang'];
            $indek = $_POST['indek'];
            $mulai_kontrak = $_POST['mulai_kontrak'];
            $cuti_diambil = $_POST['cuti_diambil'];
            $dankes = $_POST['dankes'];
            $no_ktp = $_POST['no_ktp'];

            $fileToUpload = $_FILES['fileToUpload']['tmp_name'];

            $pegawai = $this->core->db->get('pegawai', '*', ['id' => $id]);

            $img = new \Systems\Lib\Image;
            if ($img->load($fileToUpload)) {
                if ($img->getInfos('width') < $img->getInfos('height')) {
                    $img->crop(0, 0, $img->getInfos('width'), $img->getInfos('width'));
                } else {
                    $img->crop(0, 0, $img->getInfos('height'), $img->getInfos('height'));
                }

                if ($img->getInfos('width') > 512) {
                    $img->resize(512, 512);
                }

                $photo = uniqid('photo').".".$img->getInfos('type');
            } else {
                $photo = $pegawai['photo'];
            }

            // BUANG FIELD PERTAMA

            $result = $this->core->db->update('pegawai', [
              'nama'=>$nama, 'jk'=>$jk, 'jbtn'=>$jbtn, 'jnj_jabatan'=>$jnj_jabatan, 'kode_kelompok'=>$kode_kelompok, 'kode_resiko'=>$kode_resiko, 'kode_emergency'=>$kode_emergency, 'departemen'=>$departemen, 'bidang'=>$bidang, 'stts_wp'=>$stts_wp, 'stts_kerja'=>$stts_kerja, 'npwp'=>$npwp, 'pendidikan'=>$pendidikan, 'gapok'=>$gapok, 'tmp_lahir'=>$tmp_lahir, 'tgl_lahir'=>$tgl_lahir, 'alamat'=>$alamat, 'kota'=>$kota, 'mulai_kerja'=>$mulai_kerja, 'ms_kerja'=>$ms_kerja, 'indexins'=>$indexins, 'bpd'=>$bpd, 'rekening'=>$rekening, 'stts_aktif'=>$stts_aktif, 'wajibmasuk'=>$wajibmasuk, 'pengurang'=>$pengurang, 'indek'=>$indek, 'mulai_kontrak'=>$mulai_kontrak, 'cuti_diambil'=>$cuti_diambil, 'dankes'=>$dankes, 'photo'=>$photo, 'no_ktp'=>$no_ktp
            ], [
              'id'=>$id
            ]);


            if (!empty($result)){
              http_response_code(200);
              $data = array(
                'code' => '200', 
                'status' => 'success', 
                'msg' => 'Data telah diubah'
              );
              if (isset($img) && $img->getInfos('width')) {
                  if (isset($pegawai)) {
                      unlink(UPLOADS."/pegawai/".$pegawai['photo']);
                  }
                  $img->save(UPLOADS."/pegawai/".$photo);
              }
            } else {
              http_response_code(201);
              $data = array(
                'code' => '201', 
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('pegawai => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('pegawai')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $id= $_POST['id'];
            $result = $this->core->db->delete('pegawai', [
              'AND' => [
                'id'=>$id
              ]
            ]);

            if (!empty($result)){
              http_response_code(200);
              $data = array(
                'code' => '200', 
                'status' => 'success', 
                'msg' => 'Data telah dihapus'
              );
            } else {
              http_response_code(201);
              $data = array(
                'code' => '201', 
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('pegawai => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('pegawai')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_pegawai= $_POST['search_field_pegawai'];
            $search_text_pegawai = $_POST['search_text_pegawai'];

            if ($search_text_pegawai != '') {
              $where[$search_field_pegawai.'[~]'] = $search_text_pegawai;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('pegawai', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'id'=>$row['id'],
                    'nik'=>$row['nik'],
                    'nama'=>$row['nama'],
                    'jk'=>$row['jk'],
                    'jbtn'=>$row['jbtn'],
                    'jnj_jabatan'=>$row['jnj_jabatan'],
                    'kode_kelompok'=>$row['kode_kelompok'],
                    'kode_resiko'=>$row['kode_resiko'],
                    'kode_emergency'=>$row['kode_emergency'],
                    'departemen'=>$row['departemen'],
                    'bidang'=>$row['bidang'],
                    'stts_wp'=>$row['stts_wp'],
                    'stts_kerja'=>$row['stts_kerja'],
                    'npwp'=>$row['npwp'],
                    'pendidikan'=>$row['pendidikan'],
                    'gapok'=>$row['gapok'],
                    'tmp_lahir'=>$row['tmp_lahir'],
                    'tgl_lahir'=>$row['tgl_lahir'],
                    'alamat'=>$row['alamat'],
                    'kota'=>$row['kota'],
                    'mulai_kerja'=>$row['mulai_kerja'],
                    'ms_kerja'=>$row['ms_kerja'],
                    'indexins'=>$row['indexins'],
                    'bpd'=>$row['bpd'],
                    'rekening'=>$row['rekening'],
                    'stts_aktif'=>$row['stts_aktif'],
                    'wajibmasuk'=>$row['wajibmasuk'],
                    'pengurang'=>$row['pengurang'],
                    'indek'=>$row['indek'],
                    'mulai_kontrak'=>$row['mulai_kontrak'],
                    'cuti_diambil'=>$row['cuti_diambil'],
                    'dankes'=>$row['dankes'],
                    'photo'=>$row['photo'],
                    'no_ktp'=>$row['no_ktp']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('pegawai => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($nik)
    {

        if($this->core->loadDisabledMenu('pegawai')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('pegawai', '*', ['nik' => $nik]);

        if (!empty($result)){
          http_response_code(200);
          $data = array(
            'code' => '200', 
            'status' => 'success', 
            'msg' => $result
          );
        } else {
          http_response_code(201);
          $data = array(
            'code' => '201', 
            'status' => 'error', 
            'msg' => 'Data tidak ditemukan'
          );
        }

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('pegawai => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($nik)
    {

        if($this->core->loadDisabledMenu('pegawai')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $settings =  $this->settings('settings');

        if($this->settings('settings', 'logquery') == true) {
          $this->core->LogQuery('pegawai => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'nik' => $nik]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('pegawai', 'pendidikan', ['GROUP' => 'pendidikan']);
      $datasets = $this->core->db->select('pegawai', ['count' => \Medoo\Medoo::raw('COUNT(<pendidikan>)')], ['GROUP' => 'pendidikan']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('pegawai', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('pegawai', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'pegawai';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('pegawai => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/pegawai/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/pegawai/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('pegawai')]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/vendor/datatables/datatables.min.css'));
        $this->core->addCSS(url('assets/vendor/daterange/daterange.css'));
        $this->core->addCSS(url('assets/css/jquery.contextMenu.css'));
        $this->core->addJS(url('assets/js/jqueryvalidation.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/xlsx.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/jspdf.min.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/jspdf.plugin.autotable.min.js'), 'footer');
        $this->core->addJS(url('assets/vendor/datatables/datatables.min.js'), 'footer');
        $this->core->addJS(url('assets/vendor/daterange/daterange.js'), 'footer');
        $this->core->addJS(url('assets/js/jquery.contextMenu.js'), 'footer');

        $this->core->addCSS(url([ 'pegawai', 'css']));
        $this->core->addJS(url([ 'pegawai', 'javascript']), 'footer');
    }

}
