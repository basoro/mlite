<?php
namespace Plugins\Personal_Pasien;

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
        $disabled_menu = $this->core->loadDisabledMenu('personal_pasien'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'no_rkm_medis');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_personal_pasien= isset_or($_POST['search_field_personal_pasien']);
        $search_text_personal_pasien = isset_or($_POST['search_text_personal_pasien']);

        if ($search_text_personal_pasien != '') {
          $where[$search_field_personal_pasien.'[~]'] = $search_text_personal_pasien;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('personal_pasien', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('personal_pasien', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('personal_pasien', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rkm_medis'=>$row['no_rkm_medis'],
'gambar'=>$row['gambar'],
'password'=>$row['password']

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
          $this->core->LogQuery('personal_pasien => postData');
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

            if($this->core->loadDisabledMenu('personal_pasien')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_rkm_medis = $_POST['no_rkm_medis'];
$gambar = $_POST['gambar'];
$password = $_POST['password'];

            
            $result = $this->core->db->insert('personal_pasien', [
'no_rkm_medis'=>$no_rkm_medis, 'gambar'=>$gambar, 'password'=>$password
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
              $this->core->LogQuery('personal_pasien => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('personal_pasien')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_rkm_medis = $_POST['no_rkm_medis'];
$gambar = $_POST['gambar'];
$password = $_POST['password'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('personal_pasien', [
'no_rkm_medis'=>$no_rkm_medis, 'gambar'=>$gambar, 'password'=>$password
            ], [
              'no_rkm_medis'=>$no_rkm_medis
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
              $this->core->LogQuery('personal_pasien => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('personal_pasien')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_rkm_medis= $_POST['no_rkm_medis'];
            $result = $this->core->db->delete('personal_pasien', [
              'AND' => [
                'no_rkm_medis'=>$no_rkm_medis
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
              $this->core->LogQuery('personal_pasien => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('personal_pasien')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_personal_pasien= $_POST['search_field_personal_pasien'];
            $search_text_personal_pasien = $_POST['search_text_personal_pasien'];

            if ($search_text_personal_pasien != '') {
              $where[$search_field_personal_pasien.'[~]'] = $search_text_personal_pasien;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('personal_pasien', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_rkm_medis'=>$row['no_rkm_medis'],
'gambar'=>$row['gambar'],
'password'=>$row['password']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('personal_pasien => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_rkm_medis)
    {

        if($this->core->loadDisabledMenu('personal_pasien')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('personal_pasien', '*', ['no_rkm_medis' => $no_rkm_medis]);

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
          $this->core->LogQuery('personal_pasien => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_rkm_medis)
    {

        if($this->core->loadDisabledMenu('personal_pasien')['read'] == 'true') {
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
          $this->core->LogQuery('personal_pasien => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_rkm_medis' => $no_rkm_medis]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('personal_pasien', 'no_rkm_medis', ['GROUP' => 'no_rkm_medis']);
      $datasets = $this->core->db->select('personal_pasien', ['count' => \Medoo\Medoo::raw('COUNT(<no_rkm_medis>)')], ['GROUP' => 'no_rkm_medis']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('personal_pasien', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('personal_pasien', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'personal_pasien';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('personal_pasien => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/personal_pasien/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/personal_pasien/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('personal_pasien')]);
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

        $this->core->addCSS(url([ 'personal_pasien', 'css']));
        $this->core->addJS(url([ 'personal_pasien', 'javascript']), 'footer');
    }

}
