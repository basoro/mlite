<?php
namespace Plugins\Pemeriksaan_Ranap;

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
        $disabled_menu = $this->core->loadDisabledMenu('pemeriksaan_ranap'); 
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
        $search_field_pemeriksaan_ranap= isset_or($_POST['search_field_pemeriksaan_ranap']);
        $search_text_pemeriksaan_ranap = isset_or($_POST['search_text_pemeriksaan_ranap']);

        if ($search_text_pemeriksaan_ranap != '') {
          $where[$search_field_pemeriksaan_ranap.'[~]'] = $search_text_pemeriksaan_ranap;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('pemeriksaan_ranap', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('pemeriksaan_ranap', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('pemeriksaan_ranap', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rawat'=>$row['no_rawat'],
'tgl_perawatan'=>$row['tgl_perawatan'],
'jam_rawat'=>$row['jam_rawat'],
'suhu_tubuh'=>$row['suhu_tubuh'],
'tensi'=>$row['tensi'],
'nadi'=>$row['nadi'],
'respirasi'=>$row['respirasi'],
'tinggi'=>$row['tinggi'],
'berat'=>$row['berat'],
'spo2'=>$row['spo2'],
'gcs'=>$row['gcs'],
'kesadaran'=>$row['kesadaran'],
'keluhan'=>$row['keluhan'],
'pemeriksaan'=>$row['pemeriksaan'],
'alergi'=>$row['alergi'],
'penilaian'=>$row['penilaian'],
'rtl'=>$row['rtl'],
'instruksi'=>$row['instruksi'],
'evaluasi'=>$row['evaluasi'],
'nip'=>$row['nip']

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
          $this->core->LogQuery('pemeriksaan_ranap => postData');
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

            if($this->core->loadDisabledMenu('pemeriksaan_ranap')['create'] == 'true') {
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
$tgl_perawatan = $_POST['tgl_perawatan'];
$jam_rawat = $_POST['jam_rawat'];
$suhu_tubuh = $_POST['suhu_tubuh'];
$tensi = $_POST['tensi'];
$nadi = $_POST['nadi'];
$respirasi = $_POST['respirasi'];
$tinggi = $_POST['tinggi'];
$berat = $_POST['berat'];
$spo2 = $_POST['spo2'];
$gcs = $_POST['gcs'];
$kesadaran = $_POST['kesadaran'];
$keluhan = $_POST['keluhan'];
$pemeriksaan = $_POST['pemeriksaan'];
$alergi = $_POST['alergi'];
$penilaian = $_POST['penilaian'];
$rtl = $_POST['rtl'];
$instruksi = $_POST['instruksi'];
$evaluasi = $_POST['evaluasi'];
$nip = $_POST['nip'];

            
            $result = $this->core->db->insert('pemeriksaan_ranap', [
'no_rawat'=>$no_rawat, 'tgl_perawatan'=>$tgl_perawatan, 'jam_rawat'=>$jam_rawat, 'suhu_tubuh'=>$suhu_tubuh, 'tensi'=>$tensi, 'nadi'=>$nadi, 'respirasi'=>$respirasi, 'tinggi'=>$tinggi, 'berat'=>$berat, 'spo2'=>$spo2, 'gcs'=>$gcs, 'kesadaran'=>$kesadaran, 'keluhan'=>$keluhan, 'pemeriksaan'=>$pemeriksaan, 'alergi'=>$alergi, 'penilaian'=>$penilaian, 'rtl'=>$rtl, 'instruksi'=>$instruksi, 'evaluasi'=>$evaluasi, 'nip'=>$nip
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
              $this->core->LogQuery('pemeriksaan_ranap => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('pemeriksaan_ranap')['update'] == 'true') {
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
$tgl_perawatan = $_POST['tgl_perawatan'];
$jam_rawat = $_POST['jam_rawat'];
$suhu_tubuh = $_POST['suhu_tubuh'];
$tensi = $_POST['tensi'];
$nadi = $_POST['nadi'];
$respirasi = $_POST['respirasi'];
$tinggi = $_POST['tinggi'];
$berat = $_POST['berat'];
$spo2 = $_POST['spo2'];
$gcs = $_POST['gcs'];
$kesadaran = $_POST['kesadaran'];
$keluhan = $_POST['keluhan'];
$pemeriksaan = $_POST['pemeriksaan'];
$alergi = $_POST['alergi'];
$penilaian = $_POST['penilaian'];
$rtl = $_POST['rtl'];
$instruksi = $_POST['instruksi'];
$evaluasi = $_POST['evaluasi'];
$nip = $_POST['nip'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('pemeriksaan_ranap', [
'no_rawat'=>$no_rawat, 'tgl_perawatan'=>$tgl_perawatan, 'jam_rawat'=>$jam_rawat, 'suhu_tubuh'=>$suhu_tubuh, 'tensi'=>$tensi, 'nadi'=>$nadi, 'respirasi'=>$respirasi, 'tinggi'=>$tinggi, 'berat'=>$berat, 'spo2'=>$spo2, 'gcs'=>$gcs, 'kesadaran'=>$kesadaran, 'keluhan'=>$keluhan, 'pemeriksaan'=>$pemeriksaan, 'alergi'=>$alergi, 'penilaian'=>$penilaian, 'rtl'=>$rtl, 'instruksi'=>$instruksi, 'evaluasi'=>$evaluasi, 'nip'=>$nip
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
              $this->core->LogQuery('pemeriksaan_ranap => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('pemeriksaan_ranap')['delete'] == 'true') {
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
            $result = $this->core->db->delete('pemeriksaan_ranap', [
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
              $this->core->LogQuery('pemeriksaan_ranap => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('pemeriksaan_ranap')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_pemeriksaan_ranap= $_POST['search_field_pemeriksaan_ranap'];
            $search_text_pemeriksaan_ranap = $_POST['search_text_pemeriksaan_ranap'];

            if ($search_text_pemeriksaan_ranap != '') {
              $where[$search_field_pemeriksaan_ranap.'[~]'] = $search_text_pemeriksaan_ranap;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('pemeriksaan_ranap', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_rawat'=>$row['no_rawat'],
'tgl_perawatan'=>$row['tgl_perawatan'],
'jam_rawat'=>$row['jam_rawat'],
'suhu_tubuh'=>$row['suhu_tubuh'],
'tensi'=>$row['tensi'],
'nadi'=>$row['nadi'],
'respirasi'=>$row['respirasi'],
'tinggi'=>$row['tinggi'],
'berat'=>$row['berat'],
'spo2'=>$row['spo2'],
'gcs'=>$row['gcs'],
'kesadaran'=>$row['kesadaran'],
'keluhan'=>$row['keluhan'],
'pemeriksaan'=>$row['pemeriksaan'],
'alergi'=>$row['alergi'],
'penilaian'=>$row['penilaian'],
'rtl'=>$row['rtl'],
'instruksi'=>$row['instruksi'],
'evaluasi'=>$row['evaluasi'],
'nip'=>$row['nip']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('pemeriksaan_ranap => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_rawat)
    {

        if($this->core->loadDisabledMenu('pemeriksaan_ranap')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('pemeriksaan_ranap', '*', ['no_rawat' => $no_rawat]);

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
          $this->core->LogQuery('pemeriksaan_ranap => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_rawat)
    {

        if($this->core->loadDisabledMenu('pemeriksaan_ranap')['read'] == 'true') {
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
          $this->core->LogQuery('pemeriksaan_ranap => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_rawat' => $no_rawat]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('pemeriksaan_ranap', 'kesadaran', ['GROUP' => 'kesadaran']);
      $datasets = $this->core->db->select('pemeriksaan_ranap', ['count' => \Medoo\Medoo::raw('COUNT(<kesadaran>)')], ['GROUP' => 'kesadaran']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('pemeriksaan_ranap', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('pemeriksaan_ranap', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'pemeriksaan_ranap';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('pemeriksaan_ranap => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/pemeriksaan_ranap/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/pemeriksaan_ranap/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('pemeriksaan_ranap')]);
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

        $this->core->addCSS(url([ 'pemeriksaan_ranap', 'css']));
        $this->core->addJS(url([ 'pemeriksaan_ranap', 'javascript']), 'footer');
    }

}
