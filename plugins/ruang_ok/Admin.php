<?php
namespace Plugins\Ruang_Ok;

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
        $disabled_menu = $this->core->loadDisabledMenu('ruang_ok'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kd_ruang_ok');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_ruang_ok= isset_or($_POST['search_field_ruang_ok']);
        $search_text_ruang_ok = isset_or($_POST['search_text_ruang_ok']);

        if ($search_text_ruang_ok != '') {
          $where[$search_field_ruang_ok.'[~]'] = $search_text_ruang_ok;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('ruang_ok', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('ruang_ok', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('ruang_ok', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kd_ruang_ok'=>$row['kd_ruang_ok'],
                'nm_ruang_ok'=>$row['nm_ruang_ok']

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
          $this->core->LogQuery('ruang_ok => postData');
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

            if($this->core->loadDisabledMenu('ruang_ok')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kd_ruang_ok = $_POST['kd_ruang_ok'];
        $nm_ruang_ok = $_POST['nm_ruang_ok'];

            
            $result = $this->core->db->insert('ruang_ok', [
                'kd_ruang_ok'=>$kd_ruang_ok, 'nm_ruang_ok'=>$nm_ruang_ok
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
              $this->core->LogQuery('ruang_ok => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('ruang_ok')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kd_ruang_ok = $_POST['kd_ruang_ok'];
        $nm_ruang_ok = $_POST['nm_ruang_ok'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('ruang_ok', [
              'nm_ruang_ok'=>$nm_ruang_ok
            ], [
              'kd_ruang_ok'=>$kd_ruang_ok
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
              $this->core->LogQuery('ruang_ok => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('ruang_ok')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_ruang_ok= $_POST['kd_ruang_ok'];
            $result = $this->core->db->delete('ruang_ok', [
              'AND' => [
                'kd_ruang_ok'=>$kd_ruang_ok
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
              $this->core->LogQuery('ruang_ok => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('ruang_ok')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_ruang_ok= $_POST['search_field_ruang_ok'];
            $search_text_ruang_ok = $_POST['search_text_ruang_ok'];

            if ($search_text_ruang_ok != '') {
              $where[$search_field_ruang_ok.'[~]'] = $search_text_ruang_ok;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('ruang_ok', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kd_ruang_ok'=>$row['kd_ruang_ok'],
                    'nm_ruang_ok'=>$row['nm_ruang_ok']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('ruang_ok => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kd_ruang_ok)
    {

        if($this->core->loadDisabledMenu('ruang_ok')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('ruang_ok', '*', ['kd_ruang_ok' => $kd_ruang_ok]);

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
          $this->core->LogQuery('ruang_ok => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kd_ruang_ok)
    {

        if($this->core->loadDisabledMenu('ruang_ok')['read'] == 'true') {
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
          $this->core->LogQuery('ruang_ok => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kd_ruang_ok' => $kd_ruang_ok]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('ruang_ok', 'nm_ruang_ok', ['GROUP' => 'nm_ruang_ok']);
      $datasets = $this->core->db->select('ruang_ok', ['count' => \Medoo\Medoo::raw('COUNT(<nm_ruang_ok>)')], ['GROUP' => 'nm_ruang_ok']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('ruang_ok', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('ruang_ok', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'ruang_ok';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('ruang_ok => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/ruang_ok/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/ruang_ok/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('ruang_ok')]);
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

        $this->core->addCSS(url([ 'ruang_ok', 'css']));
        $this->core->addJS(url([ 'ruang_ok', 'javascript']), 'footer');
    }

}
