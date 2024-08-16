<?php
namespace Plugins\Inventaris_Jenis;

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
        $disabled_menu = $this->core->loadDisabledMenu('inventaris_jenis'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'id_jenis');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_inventaris_jenis= isset_or($_POST['search_field_inventaris_jenis']);
        $search_text_inventaris_jenis = isset_or($_POST['search_text_inventaris_jenis']);

        if ($search_text_inventaris_jenis != '') {
          $where[$search_field_inventaris_jenis.'[~]'] = $search_text_inventaris_jenis;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('inventaris_jenis', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('inventaris_jenis', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('inventaris_jenis', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'id_jenis'=>$row['id_jenis'],
'nama_jenis'=>$row['nama_jenis']

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
          $this->core->LogQuery('inventaris_jenis => postData');
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

            if($this->core->loadDisabledMenu('inventaris_jenis')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $id_jenis = $_POST['id_jenis'];
$nama_jenis = $_POST['nama_jenis'];

            
            $result = $this->core->db->insert('inventaris_jenis', [
'id_jenis'=>$id_jenis, 'nama_jenis'=>$nama_jenis
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
              $this->core->LogQuery('inventaris_jenis => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('inventaris_jenis')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $id_jenis = $_POST['id_jenis'];
$nama_jenis = $_POST['nama_jenis'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('inventaris_jenis', [
'id_jenis'=>$id_jenis, 'nama_jenis'=>$nama_jenis
            ], [
              'id_jenis'=>$id_jenis
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
              $this->core->LogQuery('inventaris_jenis => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('inventaris_jenis')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $id_jenis= $_POST['id_jenis'];
            $result = $this->core->db->delete('inventaris_jenis', [
              'AND' => [
                'id_jenis'=>$id_jenis
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
              $this->core->LogQuery('inventaris_jenis => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('inventaris_jenis')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_inventaris_jenis= $_POST['search_field_inventaris_jenis'];
            $search_text_inventaris_jenis = $_POST['search_text_inventaris_jenis'];

            if ($search_text_inventaris_jenis != '') {
              $where[$search_field_inventaris_jenis.'[~]'] = $search_text_inventaris_jenis;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('inventaris_jenis', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'id_jenis'=>$row['id_jenis'],
'nama_jenis'=>$row['nama_jenis']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('inventaris_jenis => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($id_jenis)
    {

        if($this->core->loadDisabledMenu('inventaris_jenis')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('inventaris_jenis', '*', ['id_jenis' => $id_jenis]);

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
          $this->core->LogQuery('inventaris_jenis => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($id_jenis)
    {

        if($this->core->loadDisabledMenu('inventaris_jenis')['read'] == 'true') {
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
          $this->core->LogQuery('inventaris_jenis => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'id_jenis' => $id_jenis]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('inventaris_jenis', 'nama_jenis', ['GROUP' => 'nama_jenis']);
      $datasets = $this->core->db->select('inventaris_jenis', ['count' => \Medoo\Medoo::raw('COUNT(<nama_jenis>)')], ['GROUP' => 'nama_jenis']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('inventaris_jenis', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('inventaris_jenis', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'inventaris_jenis';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('inventaris_jenis => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/inventaris_jenis/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/inventaris_jenis/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('inventaris_jenis')]);
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

        $this->core->addCSS(url([ 'inventaris_jenis', 'css']));
        $this->core->addJS(url([ 'inventaris_jenis', 'javascript']), 'footer');
    }

}
