<?php
namespace Plugins\Jam_Jaga;

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
        $disabled_menu = $this->core->loadDisabledMenu('jam_jaga'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'no_id');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_jam_jaga= isset_or($_POST['search_field_jam_jaga']);
        $search_text_jam_jaga = isset_or($_POST['search_text_jam_jaga']);

        if ($search_text_jam_jaga != '') {
          $where[$search_field_jam_jaga.'[~]'] = $search_text_jam_jaga;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('jam_jaga', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('jam_jaga', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('jam_jaga', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_id'=>$row['no_id'],
'dep_id'=>$row['dep_id'],
'shift'=>$row['shift'],
'jam_masuk'=>$row['jam_masuk'],
'jam_pulang'=>$row['jam_pulang']

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
          $this->core->LogQuery('jam_jaga => postData');
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

            if($this->core->loadDisabledMenu('jam_jaga')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_id = $_POST['no_id'];
$dep_id = $_POST['dep_id'];
$shift = $_POST['shift'];
$jam_masuk = $_POST['jam_masuk'];
$jam_pulang = $_POST['jam_pulang'];

            
            $result = $this->core->db->insert('jam_jaga', [
'no_id'=>$no_id, 'dep_id'=>$dep_id, 'shift'=>$shift, 'jam_masuk'=>$jam_masuk, 'jam_pulang'=>$jam_pulang
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
              $this->core->LogQuery('jam_jaga => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('jam_jaga')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_id = $_POST['no_id'];
$dep_id = $_POST['dep_id'];
$shift = $_POST['shift'];
$jam_masuk = $_POST['jam_masuk'];
$jam_pulang = $_POST['jam_pulang'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('jam_jaga', [
'no_id'=>$no_id, 'dep_id'=>$dep_id, 'shift'=>$shift, 'jam_masuk'=>$jam_masuk, 'jam_pulang'=>$jam_pulang
            ], [
              'no_id'=>$no_id
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
              $this->core->LogQuery('jam_jaga => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('jam_jaga')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_id= $_POST['no_id'];
            $result = $this->core->db->delete('jam_jaga', [
              'AND' => [
                'no_id'=>$no_id
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
              $this->core->LogQuery('jam_jaga => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('jam_jaga')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_jam_jaga= $_POST['search_field_jam_jaga'];
            $search_text_jam_jaga = $_POST['search_text_jam_jaga'];

            if ($search_text_jam_jaga != '') {
              $where[$search_field_jam_jaga.'[~]'] = $search_text_jam_jaga;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('jam_jaga', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_id'=>$row['no_id'],
'dep_id'=>$row['dep_id'],
'shift'=>$row['shift'],
'jam_masuk'=>$row['jam_masuk'],
'jam_pulang'=>$row['jam_pulang']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('jam_jaga => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_id)
    {

        if($this->core->loadDisabledMenu('jam_jaga')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('jam_jaga', '*', ['no_id' => $no_id]);

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
          $this->core->LogQuery('jam_jaga => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_id)
    {

        if($this->core->loadDisabledMenu('jam_jaga')['read'] == 'true') {
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
          $this->core->LogQuery('jam_jaga => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_id' => $no_id]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('jam_jaga', 'dep_id', ['GROUP' => 'dep_id']);
      $datasets = $this->core->db->select('jam_jaga', ['count' => \Medoo\Medoo::raw('COUNT(<dep_id>)')], ['GROUP' => 'dep_id']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('jam_jaga', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('jam_jaga', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'jam_jaga';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('jam_jaga => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/jam_jaga/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/jam_jaga/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('jam_jaga')]);
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

        $this->core->addCSS(url([ 'jam_jaga', 'css']));
        $this->core->addJS(url([ 'jam_jaga', 'javascript']), 'footer');
    }

}
