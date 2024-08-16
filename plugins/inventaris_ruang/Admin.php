<?php
namespace Plugins\Inventaris_Ruang;

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
        $disabled_menu = $this->core->loadDisabledMenu('inventaris_ruang'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'id_ruang');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_inventaris_ruang= isset_or($_POST['search_field_inventaris_ruang']);
        $search_text_inventaris_ruang = isset_or($_POST['search_text_inventaris_ruang']);

        if ($search_text_inventaris_ruang != '') {
          $where[$search_field_inventaris_ruang.'[~]'] = $search_text_inventaris_ruang;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('inventaris_ruang', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('inventaris_ruang', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('inventaris_ruang', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'id_ruang'=>$row['id_ruang'],
'nama_ruang'=>$row['nama_ruang']

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
          $this->core->LogQuery('inventaris_ruang => postData');
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

            if($this->core->loadDisabledMenu('inventaris_ruang')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $id_ruang = $_POST['id_ruang'];
$nama_ruang = $_POST['nama_ruang'];

            
            $result = $this->core->db->insert('inventaris_ruang', [
'id_ruang'=>$id_ruang, 'nama_ruang'=>$nama_ruang
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
              $this->core->LogQuery('inventaris_ruang => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('inventaris_ruang')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $id_ruang = $_POST['id_ruang'];
$nama_ruang = $_POST['nama_ruang'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('inventaris_ruang', [
'id_ruang'=>$id_ruang, 'nama_ruang'=>$nama_ruang
            ], [
              'id_ruang'=>$id_ruang
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
              $this->core->LogQuery('inventaris_ruang => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('inventaris_ruang')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $id_ruang= $_POST['id_ruang'];
            $result = $this->core->db->delete('inventaris_ruang', [
              'AND' => [
                'id_ruang'=>$id_ruang
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
              $this->core->LogQuery('inventaris_ruang => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('inventaris_ruang')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_inventaris_ruang= $_POST['search_field_inventaris_ruang'];
            $search_text_inventaris_ruang = $_POST['search_text_inventaris_ruang'];

            if ($search_text_inventaris_ruang != '') {
              $where[$search_field_inventaris_ruang.'[~]'] = $search_text_inventaris_ruang;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('inventaris_ruang', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'id_ruang'=>$row['id_ruang'],
'nama_ruang'=>$row['nama_ruang']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('inventaris_ruang => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($id_ruang)
    {

        if($this->core->loadDisabledMenu('inventaris_ruang')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('inventaris_ruang', '*', ['id_ruang' => $id_ruang]);

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
          $this->core->LogQuery('inventaris_ruang => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($id_ruang)
    {

        if($this->core->loadDisabledMenu('inventaris_ruang')['read'] == 'true') {
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
          $this->core->LogQuery('inventaris_ruang => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'id_ruang' => $id_ruang]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('inventaris_ruang', 'nama_ruang', ['GROUP' => 'nama_ruang']);
      $datasets = $this->core->db->select('inventaris_ruang', ['count' => \Medoo\Medoo::raw('COUNT(<nama_ruang>)')], ['GROUP' => 'nama_ruang']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('inventaris_ruang', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('inventaris_ruang', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'inventaris_ruang';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('inventaris_ruang => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/inventaris_ruang/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/inventaris_ruang/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('inventaris_ruang')]);
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

        $this->core->addCSS(url([ 'inventaris_ruang', 'css']));
        $this->core->addJS(url([ 'inventaris_ruang', 'javascript']), 'footer');
    }

}
