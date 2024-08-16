<?php
namespace Plugins\Maping_Poliklinik_Pcare;

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
        $disabled_menu = $this->core->loadDisabledMenu('maping_poliklinik_pcare'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kd_poli_rs');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_maping_poliklinik_pcare= isset_or($_POST['search_field_maping_poliklinik_pcare']);
        $search_text_maping_poliklinik_pcare = isset_or($_POST['search_text_maping_poliklinik_pcare']);

        if ($search_text_maping_poliklinik_pcare != '') {
          $where[$search_field_maping_poliklinik_pcare.'[~]'] = $search_text_maping_poliklinik_pcare;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('maping_poliklinik_pcare', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('maping_poliklinik_pcare', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('maping_poliklinik_pcare', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kd_poli_rs'=>$row['kd_poli_rs'],
'kd_poli_pcare'=>$row['kd_poli_pcare'],
'nm_poli_pcare'=>$row['nm_poli_pcare']

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
          $this->core->LogQuery('maping_poliklinik_pcare => postData');
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

            if($this->core->loadDisabledMenu('maping_poliklinik_pcare')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kd_poli_rs = $_POST['kd_poli_rs'];
$kd_poli_pcare = $_POST['kd_poli_pcare'];
$nm_poli_pcare = $_POST['nm_poli_pcare'];

            
            $result = $this->core->db->insert('maping_poliklinik_pcare', [
'kd_poli_rs'=>$kd_poli_rs, 'kd_poli_pcare'=>$kd_poli_pcare, 'nm_poli_pcare'=>$nm_poli_pcare
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
              $this->core->LogQuery('maping_poliklinik_pcare => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('maping_poliklinik_pcare')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kd_poli_rs = $_POST['kd_poli_rs'];
$kd_poli_pcare = $_POST['kd_poli_pcare'];
$nm_poli_pcare = $_POST['nm_poli_pcare'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('maping_poliklinik_pcare', [
'kd_poli_rs'=>$kd_poli_rs, 'kd_poli_pcare'=>$kd_poli_pcare, 'nm_poli_pcare'=>$nm_poli_pcare
            ], [
              'kd_poli_rs'=>$kd_poli_rs
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
              $this->core->LogQuery('maping_poliklinik_pcare => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('maping_poliklinik_pcare')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_poli_rs= $_POST['kd_poli_rs'];
            $result = $this->core->db->delete('maping_poliklinik_pcare', [
              'AND' => [
                'kd_poli_rs'=>$kd_poli_rs
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
              $this->core->LogQuery('maping_poliklinik_pcare => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('maping_poliklinik_pcare')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_maping_poliklinik_pcare= $_POST['search_field_maping_poliklinik_pcare'];
            $search_text_maping_poliklinik_pcare = $_POST['search_text_maping_poliklinik_pcare'];

            if ($search_text_maping_poliklinik_pcare != '') {
              $where[$search_field_maping_poliklinik_pcare.'[~]'] = $search_text_maping_poliklinik_pcare;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('maping_poliklinik_pcare', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kd_poli_rs'=>$row['kd_poli_rs'],
'kd_poli_pcare'=>$row['kd_poli_pcare'],
'nm_poli_pcare'=>$row['nm_poli_pcare']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('maping_poliklinik_pcare => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kd_poli_rs)
    {

        if($this->core->loadDisabledMenu('maping_poliklinik_pcare')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('maping_poliklinik_pcare', '*', ['kd_poli_rs' => $kd_poli_rs]);

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
          $this->core->LogQuery('maping_poliklinik_pcare => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kd_poli_rs)
    {

        if($this->core->loadDisabledMenu('maping_poliklinik_pcare')['read'] == 'true') {
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
          $this->core->LogQuery('maping_poliklinik_pcare => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kd_poli_rs' => $kd_poli_rs]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('maping_poliklinik_pcare', 'kd_poli_pcare', ['GROUP' => 'kd_poli_pcare']);
      $datasets = $this->core->db->select('maping_poliklinik_pcare', ['count' => \Medoo\Medoo::raw('COUNT(<kd_poli_pcare>)')], ['GROUP' => 'kd_poli_pcare']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('maping_poliklinik_pcare', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('maping_poliklinik_pcare', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'maping_poliklinik_pcare';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('maping_poliklinik_pcare => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/maping_poliklinik_pcare/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/maping_poliklinik_pcare/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('maping_poliklinik_pcare')]);
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

        $this->core->addCSS(url([ 'maping_poliklinik_pcare', 'css']));
        $this->core->addJS(url([ 'maping_poliklinik_pcare', 'javascript']), 'footer');
    }

}
