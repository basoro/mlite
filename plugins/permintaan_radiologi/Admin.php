<?php
namespace Plugins\Permintaan_Radiologi;

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
        $disabled_menu = $this->core->loadDisabledMenu('permintaan_radiologi'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'noorder');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_permintaan_radiologi= isset_or($_POST['search_field_permintaan_radiologi']);
        $search_text_permintaan_radiologi = isset_or($_POST['search_text_permintaan_radiologi']);

        if ($search_text_permintaan_radiologi != '') {
          $where[$search_field_permintaan_radiologi.'[~]'] = $search_text_permintaan_radiologi;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('permintaan_radiologi', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('permintaan_radiologi', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('permintaan_radiologi', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'noorder'=>$row['noorder'],
'no_rawat'=>$row['no_rawat'],
'tgl_permintaan'=>$row['tgl_permintaan'],
'jam_permintaan'=>$row['jam_permintaan'],
'tgl_sampel'=>$row['tgl_sampel'],
'jam_sampel'=>$row['jam_sampel'],
'tgl_hasil'=>$row['tgl_hasil'],
'jam_hasil'=>$row['jam_hasil'],
'dokter_perujuk'=>$row['dokter_perujuk'],
'status'=>$row['status'],
'informasi_tambahan'=>$row['informasi_tambahan'],
'diagnosa_klinis'=>$row['diagnosa_klinis']

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
          $this->core->LogQuery('permintaan_radiologi => postData');
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

            if($this->core->loadDisabledMenu('permintaan_radiologi')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $noorder = $_POST['noorder'];
$no_rawat = $_POST['no_rawat'];
$tgl_permintaan = $_POST['tgl_permintaan'];
$jam_permintaan = $_POST['jam_permintaan'];
$tgl_sampel = $_POST['tgl_sampel'];
$jam_sampel = $_POST['jam_sampel'];
$tgl_hasil = $_POST['tgl_hasil'];
$jam_hasil = $_POST['jam_hasil'];
$dokter_perujuk = $_POST['dokter_perujuk'];
$status = $_POST['status'];
$informasi_tambahan = $_POST['informasi_tambahan'];
$diagnosa_klinis = $_POST['diagnosa_klinis'];

            
            $result = $this->core->db->insert('permintaan_radiologi', [
'noorder'=>$noorder, 'no_rawat'=>$no_rawat, 'tgl_permintaan'=>$tgl_permintaan, 'jam_permintaan'=>$jam_permintaan, 'tgl_sampel'=>$tgl_sampel, 'jam_sampel'=>$jam_sampel, 'tgl_hasil'=>$tgl_hasil, 'jam_hasil'=>$jam_hasil, 'dokter_perujuk'=>$dokter_perujuk, 'status'=>$status, 'informasi_tambahan'=>$informasi_tambahan, 'diagnosa_klinis'=>$diagnosa_klinis
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
              $this->core->LogQuery('permintaan_radiologi => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('permintaan_radiologi')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $noorder = $_POST['noorder'];
$no_rawat = $_POST['no_rawat'];
$tgl_permintaan = $_POST['tgl_permintaan'];
$jam_permintaan = $_POST['jam_permintaan'];
$tgl_sampel = $_POST['tgl_sampel'];
$jam_sampel = $_POST['jam_sampel'];
$tgl_hasil = $_POST['tgl_hasil'];
$jam_hasil = $_POST['jam_hasil'];
$dokter_perujuk = $_POST['dokter_perujuk'];
$status = $_POST['status'];
$informasi_tambahan = $_POST['informasi_tambahan'];
$diagnosa_klinis = $_POST['diagnosa_klinis'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('permintaan_radiologi', [
'noorder'=>$noorder, 'no_rawat'=>$no_rawat, 'tgl_permintaan'=>$tgl_permintaan, 'jam_permintaan'=>$jam_permintaan, 'tgl_sampel'=>$tgl_sampel, 'jam_sampel'=>$jam_sampel, 'tgl_hasil'=>$tgl_hasil, 'jam_hasil'=>$jam_hasil, 'dokter_perujuk'=>$dokter_perujuk, 'status'=>$status, 'informasi_tambahan'=>$informasi_tambahan, 'diagnosa_klinis'=>$diagnosa_klinis
            ], [
              'noorder'=>$noorder
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
              $this->core->LogQuery('permintaan_radiologi => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('permintaan_radiologi')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $noorder= $_POST['noorder'];
            $result = $this->core->db->delete('permintaan_radiologi', [
              'AND' => [
                'noorder'=>$noorder
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
              $this->core->LogQuery('permintaan_radiologi => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('permintaan_radiologi')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_permintaan_radiologi= $_POST['search_field_permintaan_radiologi'];
            $search_text_permintaan_radiologi = $_POST['search_text_permintaan_radiologi'];

            if ($search_text_permintaan_radiologi != '') {
              $where[$search_field_permintaan_radiologi.'[~]'] = $search_text_permintaan_radiologi;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('permintaan_radiologi', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'noorder'=>$row['noorder'],
'no_rawat'=>$row['no_rawat'],
'tgl_permintaan'=>$row['tgl_permintaan'],
'jam_permintaan'=>$row['jam_permintaan'],
'tgl_sampel'=>$row['tgl_sampel'],
'jam_sampel'=>$row['jam_sampel'],
'tgl_hasil'=>$row['tgl_hasil'],
'jam_hasil'=>$row['jam_hasil'],
'dokter_perujuk'=>$row['dokter_perujuk'],
'status'=>$row['status'],
'informasi_tambahan'=>$row['informasi_tambahan'],
'diagnosa_klinis'=>$row['diagnosa_klinis']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('permintaan_radiologi => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($noorder)
    {

        if($this->core->loadDisabledMenu('permintaan_radiologi')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('permintaan_radiologi', '*', ['noorder' => $noorder]);

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
          $this->core->LogQuery('permintaan_radiologi => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($noorder)
    {

        if($this->core->loadDisabledMenu('permintaan_radiologi')['read'] == 'true') {
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
          $this->core->LogQuery('permintaan_radiologi => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'noorder' => $noorder]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('permintaan_radiologi', 'dokter_perujuk', ['GROUP' => 'dokter_perujuk']);
      $datasets = $this->core->db->select('permintaan_radiologi', ['count' => \Medoo\Medoo::raw('COUNT(<dokter_perujuk>)')], ['GROUP' => 'dokter_perujuk']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('permintaan_radiologi', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('permintaan_radiologi', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'permintaan_radiologi';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('permintaan_radiologi => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/permintaan_radiologi/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/permintaan_radiologi/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('permintaan_radiologi')]);
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

        $this->core->addCSS(url([ 'permintaan_radiologi', 'css']));
        $this->core->addJS(url([ 'permintaan_radiologi', 'javascript']), 'footer');
    }

}
