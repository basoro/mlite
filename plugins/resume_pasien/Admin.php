<?php
namespace Plugins\Resume_Pasien;

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
        $disabled_menu = $this->core->loadDisabledMenu('resume_pasien'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'no_rawat');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_resume_pasien= isset_or($_POST['search_field_resume_pasien']);
        $search_text_resume_pasien = isset_or($_POST['search_text_resume_pasien']);

        if ($search_text_resume_pasien != '') {
          $where[$search_field_resume_pasien.'[~]'] = $search_text_resume_pasien;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('resume_pasien', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('resume_pasien', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('resume_pasien', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rawat'=>$row['no_rawat'],
'kd_dokter'=>$row['kd_dokter'],
'keluhan_utama'=>$row['keluhan_utama'],
'jalannya_penyakit'=>$row['jalannya_penyakit'],
'pemeriksaan_penunjang'=>$row['pemeriksaan_penunjang'],
'hasil_laborat'=>$row['hasil_laborat'],
'diagnosa_utama'=>$row['diagnosa_utama'],
'kd_diagnosa_utama'=>$row['kd_diagnosa_utama'],
'diagnosa_sekunder'=>$row['diagnosa_sekunder'],
'kd_diagnosa_sekunder'=>$row['kd_diagnosa_sekunder'],
'diagnosa_sekunder2'=>$row['diagnosa_sekunder2'],
'kd_diagnosa_sekunder2'=>$row['kd_diagnosa_sekunder2'],
'diagnosa_sekunder3'=>$row['diagnosa_sekunder3'],
'kd_diagnosa_sekunder3'=>$row['kd_diagnosa_sekunder3'],
'diagnosa_sekunder4'=>$row['diagnosa_sekunder4'],
'kd_diagnosa_sekunder4'=>$row['kd_diagnosa_sekunder4'],
'prosedur_utama'=>$row['prosedur_utama'],
'kd_prosedur_utama'=>$row['kd_prosedur_utama'],
'prosedur_sekunder'=>$row['prosedur_sekunder'],
'kd_prosedur_sekunder'=>$row['kd_prosedur_sekunder'],
'prosedur_sekunder2'=>$row['prosedur_sekunder2'],
'kd_prosedur_sekunder2'=>$row['kd_prosedur_sekunder2'],
'prosedur_sekunder3'=>$row['prosedur_sekunder3'],
'kd_prosedur_sekunder3'=>$row['kd_prosedur_sekunder3'],
'kondisi_pulang'=>$row['kondisi_pulang'],
'obat_pulang'=>$row['obat_pulang']

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
          $this->core->LogQuery('resume_pasien => postData');
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

            if($this->core->loadDisabledMenu('resume_pasien')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_rawat = $_POST['no_rawat'];
$kd_dokter = $_POST['kd_dokter'];
$keluhan_utama = $_POST['keluhan_utama'];
$jalannya_penyakit = $_POST['jalannya_penyakit'];
$pemeriksaan_penunjang = $_POST['pemeriksaan_penunjang'];
$hasil_laborat = $_POST['hasil_laborat'];
$diagnosa_utama = $_POST['diagnosa_utama'];
$kd_diagnosa_utama = $_POST['kd_diagnosa_utama'];
$diagnosa_sekunder = $_POST['diagnosa_sekunder'];
$kd_diagnosa_sekunder = $_POST['kd_diagnosa_sekunder'];
$diagnosa_sekunder2 = $_POST['diagnosa_sekunder2'];
$kd_diagnosa_sekunder2 = $_POST['kd_diagnosa_sekunder2'];
$diagnosa_sekunder3 = $_POST['diagnosa_sekunder3'];
$kd_diagnosa_sekunder3 = $_POST['kd_diagnosa_sekunder3'];
$diagnosa_sekunder4 = $_POST['diagnosa_sekunder4'];
$kd_diagnosa_sekunder4 = $_POST['kd_diagnosa_sekunder4'];
$prosedur_utama = $_POST['prosedur_utama'];
$kd_prosedur_utama = $_POST['kd_prosedur_utama'];
$prosedur_sekunder = $_POST['prosedur_sekunder'];
$kd_prosedur_sekunder = $_POST['kd_prosedur_sekunder'];
$prosedur_sekunder2 = $_POST['prosedur_sekunder2'];
$kd_prosedur_sekunder2 = $_POST['kd_prosedur_sekunder2'];
$prosedur_sekunder3 = $_POST['prosedur_sekunder3'];
$kd_prosedur_sekunder3 = $_POST['kd_prosedur_sekunder3'];
$kondisi_pulang = $_POST['kondisi_pulang'];
$obat_pulang = $_POST['obat_pulang'];

            
            $result = $this->core->db->insert('resume_pasien', [
'no_rawat'=>$no_rawat, 'kd_dokter'=>$kd_dokter, 'keluhan_utama'=>$keluhan_utama, 'jalannya_penyakit'=>$jalannya_penyakit, 'pemeriksaan_penunjang'=>$pemeriksaan_penunjang, 'hasil_laborat'=>$hasil_laborat, 'diagnosa_utama'=>$diagnosa_utama, 'kd_diagnosa_utama'=>$kd_diagnosa_utama, 'diagnosa_sekunder'=>$diagnosa_sekunder, 'kd_diagnosa_sekunder'=>$kd_diagnosa_sekunder, 'diagnosa_sekunder2'=>$diagnosa_sekunder2, 'kd_diagnosa_sekunder2'=>$kd_diagnosa_sekunder2, 'diagnosa_sekunder3'=>$diagnosa_sekunder3, 'kd_diagnosa_sekunder3'=>$kd_diagnosa_sekunder3, 'diagnosa_sekunder4'=>$diagnosa_sekunder4, 'kd_diagnosa_sekunder4'=>$kd_diagnosa_sekunder4, 'prosedur_utama'=>$prosedur_utama, 'kd_prosedur_utama'=>$kd_prosedur_utama, 'prosedur_sekunder'=>$prosedur_sekunder, 'kd_prosedur_sekunder'=>$kd_prosedur_sekunder, 'prosedur_sekunder2'=>$prosedur_sekunder2, 'kd_prosedur_sekunder2'=>$kd_prosedur_sekunder2, 'prosedur_sekunder3'=>$prosedur_sekunder3, 'kd_prosedur_sekunder3'=>$kd_prosedur_sekunder3, 'kondisi_pulang'=>$kondisi_pulang, 'obat_pulang'=>$obat_pulang
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
              $this->core->LogQuery('resume_pasien => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('resume_pasien')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_rawat = $_POST['no_rawat'];
$kd_dokter = $_POST['kd_dokter'];
$keluhan_utama = $_POST['keluhan_utama'];
$jalannya_penyakit = $_POST['jalannya_penyakit'];
$pemeriksaan_penunjang = $_POST['pemeriksaan_penunjang'];
$hasil_laborat = $_POST['hasil_laborat'];
$diagnosa_utama = $_POST['diagnosa_utama'];
$kd_diagnosa_utama = $_POST['kd_diagnosa_utama'];
$diagnosa_sekunder = $_POST['diagnosa_sekunder'];
$kd_diagnosa_sekunder = $_POST['kd_diagnosa_sekunder'];
$diagnosa_sekunder2 = $_POST['diagnosa_sekunder2'];
$kd_diagnosa_sekunder2 = $_POST['kd_diagnosa_sekunder2'];
$diagnosa_sekunder3 = $_POST['diagnosa_sekunder3'];
$kd_diagnosa_sekunder3 = $_POST['kd_diagnosa_sekunder3'];
$diagnosa_sekunder4 = $_POST['diagnosa_sekunder4'];
$kd_diagnosa_sekunder4 = $_POST['kd_diagnosa_sekunder4'];
$prosedur_utama = $_POST['prosedur_utama'];
$kd_prosedur_utama = $_POST['kd_prosedur_utama'];
$prosedur_sekunder = $_POST['prosedur_sekunder'];
$kd_prosedur_sekunder = $_POST['kd_prosedur_sekunder'];
$prosedur_sekunder2 = $_POST['prosedur_sekunder2'];
$kd_prosedur_sekunder2 = $_POST['kd_prosedur_sekunder2'];
$prosedur_sekunder3 = $_POST['prosedur_sekunder3'];
$kd_prosedur_sekunder3 = $_POST['kd_prosedur_sekunder3'];
$kondisi_pulang = $_POST['kondisi_pulang'];
$obat_pulang = $_POST['obat_pulang'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('resume_pasien', [
'no_rawat'=>$no_rawat, 'kd_dokter'=>$kd_dokter, 'keluhan_utama'=>$keluhan_utama, 'jalannya_penyakit'=>$jalannya_penyakit, 'pemeriksaan_penunjang'=>$pemeriksaan_penunjang, 'hasil_laborat'=>$hasil_laborat, 'diagnosa_utama'=>$diagnosa_utama, 'kd_diagnosa_utama'=>$kd_diagnosa_utama, 'diagnosa_sekunder'=>$diagnosa_sekunder, 'kd_diagnosa_sekunder'=>$kd_diagnosa_sekunder, 'diagnosa_sekunder2'=>$diagnosa_sekunder2, 'kd_diagnosa_sekunder2'=>$kd_diagnosa_sekunder2, 'diagnosa_sekunder3'=>$diagnosa_sekunder3, 'kd_diagnosa_sekunder3'=>$kd_diagnosa_sekunder3, 'diagnosa_sekunder4'=>$diagnosa_sekunder4, 'kd_diagnosa_sekunder4'=>$kd_diagnosa_sekunder4, 'prosedur_utama'=>$prosedur_utama, 'kd_prosedur_utama'=>$kd_prosedur_utama, 'prosedur_sekunder'=>$prosedur_sekunder, 'kd_prosedur_sekunder'=>$kd_prosedur_sekunder, 'prosedur_sekunder2'=>$prosedur_sekunder2, 'kd_prosedur_sekunder2'=>$kd_prosedur_sekunder2, 'prosedur_sekunder3'=>$prosedur_sekunder3, 'kd_prosedur_sekunder3'=>$kd_prosedur_sekunder3, 'kondisi_pulang'=>$kondisi_pulang, 'obat_pulang'=>$obat_pulang
            ], [
              'no_rawat'=>$no_rawat
            ]);


            if (!empty($result)){
              http_response_code(200);
              $data = array(
                'code' => '200', 
                'status' => 'success', 
                'msg' => 'Data telah diubah'
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
              $this->core->LogQuery('resume_pasien => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('resume_pasien')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_rawat= $_POST['no_rawat'];
            $result = $this->core->db->delete('resume_pasien', [
              'AND' => [
                'no_rawat'=>$no_rawat
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
              $this->core->LogQuery('resume_pasien => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('resume_pasien')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_resume_pasien= $_POST['search_field_resume_pasien'];
            $search_text_resume_pasien = $_POST['search_text_resume_pasien'];

            if ($search_text_resume_pasien != '') {
              $where[$search_field_resume_pasien.'[~]'] = $search_text_resume_pasien;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('resume_pasien', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_rawat'=>$row['no_rawat'],
'kd_dokter'=>$row['kd_dokter'],
'keluhan_utama'=>$row['keluhan_utama'],
'jalannya_penyakit'=>$row['jalannya_penyakit'],
'pemeriksaan_penunjang'=>$row['pemeriksaan_penunjang'],
'hasil_laborat'=>$row['hasil_laborat'],
'diagnosa_utama'=>$row['diagnosa_utama'],
'kd_diagnosa_utama'=>$row['kd_diagnosa_utama'],
'diagnosa_sekunder'=>$row['diagnosa_sekunder'],
'kd_diagnosa_sekunder'=>$row['kd_diagnosa_sekunder'],
'diagnosa_sekunder2'=>$row['diagnosa_sekunder2'],
'kd_diagnosa_sekunder2'=>$row['kd_diagnosa_sekunder2'],
'diagnosa_sekunder3'=>$row['diagnosa_sekunder3'],
'kd_diagnosa_sekunder3'=>$row['kd_diagnosa_sekunder3'],
'diagnosa_sekunder4'=>$row['diagnosa_sekunder4'],
'kd_diagnosa_sekunder4'=>$row['kd_diagnosa_sekunder4'],
'prosedur_utama'=>$row['prosedur_utama'],
'kd_prosedur_utama'=>$row['kd_prosedur_utama'],
'prosedur_sekunder'=>$row['prosedur_sekunder'],
'kd_prosedur_sekunder'=>$row['kd_prosedur_sekunder'],
'prosedur_sekunder2'=>$row['prosedur_sekunder2'],
'kd_prosedur_sekunder2'=>$row['kd_prosedur_sekunder2'],
'prosedur_sekunder3'=>$row['prosedur_sekunder3'],
'kd_prosedur_sekunder3'=>$row['kd_prosedur_sekunder3'],
'kondisi_pulang'=>$row['kondisi_pulang'],
'obat_pulang'=>$row['obat_pulang']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('resume_pasien => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_rawat)
    {

        if($this->core->loadDisabledMenu('resume_pasien')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('resume_pasien', '*', ['no_rawat' => $no_rawat]);

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
          $this->core->LogQuery('resume_pasien => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_rawat)
    {

        if($this->core->loadDisabledMenu('resume_pasien')['read'] == 'true') {
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
          $this->core->LogQuery('resume_pasien => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_rawat' => $no_rawat]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('resume_pasien', 'kd_dokter', ['GROUP' => 'kd_dokter']);
      $datasets = $this->core->db->select('resume_pasien', ['count' => \Medoo\Medoo::raw('COUNT(<kd_dokter>)')], ['GROUP' => 'kd_dokter']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('resume_pasien', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('resume_pasien', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'resume_pasien';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('resume_pasien => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/resume_pasien/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/resume_pasien/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('resume_pasien')]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/vendor/datatables/datatables.min.css'));
        $this->core->addCSS(url('assets/css/jquery.contextMenu.css'));
        $this->core->addJS(url('assets/js/jqueryvalidation.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/xlsx.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/jspdf.min.js'), 'footer');
        $this->core->addJS(url('assets/vendor/jspdf/jspdf.plugin.autotable.min.js'), 'footer');
        $this->core->addJS(url('assets/vendor/datatables/datatables.min.js'), 'footer');
        $this->core->addJS(url('assets/js/jquery.contextMenu.js'), 'footer');

        $this->core->addCSS(url([ 'resume_pasien', 'css']));
        $this->core->addJS(url([ 'resume_pasien', 'javascript']), 'footer');
    }

}
