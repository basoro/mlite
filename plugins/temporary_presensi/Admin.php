<?php
namespace Plugins\Temporary_Presensi;

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
        $disabled_menu = $this->core->loadDisabledMenu('temporary_presensi'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
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
        $search_field_temporary_presensi= isset_or($_POST['search_field_temporary_presensi']);
        $search_text_temporary_presensi = isset_or($_POST['search_text_temporary_presensi']);

        if ($search_text_temporary_presensi != '') {
          $where[$search_field_temporary_presensi.'[~]'] = $search_text_temporary_presensi;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('temporary_presensi', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('temporary_presensi', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('temporary_presensi', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'id'=>$row['id'],
'shift'=>$row['shift'],
'jam_datang'=>$row['jam_datang'],
'jam_pulang'=>$row['jam_pulang'],
'status'=>$row['status'],
'keterlambatan'=>$row['keterlambatan'],
'durasi'=>$row['durasi'],
'photo'=>$row['photo']

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
          $this->core->LogQuery('temporary_presensi => postData');
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

            if($this->core->loadDisabledMenu('temporary_presensi')['create'] == 'true') {
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
$shift = $_POST['shift'];
$jam_datang = $_POST['jam_datang'];
$jam_pulang = $_POST['jam_pulang'];
$status = $_POST['status'];
$keterlambatan = $_POST['keterlambatan'];
$durasi = $_POST['durasi'];
$photo = $_POST['photo'];

            
            $result = $this->core->db->insert('temporary_presensi', [
'id'=>$id, 'shift'=>$shift, 'jam_datang'=>$jam_datang, 'jam_pulang'=>$jam_pulang, 'status'=>$status, 'keterlambatan'=>$keterlambatan, 'durasi'=>$durasi, 'photo'=>$photo
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
              $this->core->LogQuery('temporary_presensi => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('temporary_presensi')['update'] == 'true') {
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
$shift = $_POST['shift'];
$jam_datang = $_POST['jam_datang'];
$jam_pulang = $_POST['jam_pulang'];
$status = $_POST['status'];
$keterlambatan = $_POST['keterlambatan'];
$durasi = $_POST['durasi'];
$photo = $_POST['photo'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('temporary_presensi', [
'id'=>$id, 'shift'=>$shift, 'jam_datang'=>$jam_datang, 'jam_pulang'=>$jam_pulang, 'status'=>$status, 'keterlambatan'=>$keterlambatan, 'durasi'=>$durasi, 'photo'=>$photo
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
            } else {
              http_response_code(201);
              $data = array(
                'code' => '201', 
                'status' => 'error', 
                'msg' => $this->core->db->errorInfo[2]
              );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('temporary_presensi => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('temporary_presensi')['delete'] == 'true') {
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
            $result = $this->core->db->delete('temporary_presensi', [
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
              $this->core->LogQuery('temporary_presensi => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('temporary_presensi')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_temporary_presensi= $_POST['search_field_temporary_presensi'];
            $search_text_temporary_presensi = $_POST['search_text_temporary_presensi'];

            if ($search_text_temporary_presensi != '') {
              $where[$search_field_temporary_presensi.'[~]'] = $search_text_temporary_presensi;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('temporary_presensi', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'id'=>$row['id'],
'shift'=>$row['shift'],
'jam_datang'=>$row['jam_datang'],
'jam_pulang'=>$row['jam_pulang'],
'status'=>$row['status'],
'keterlambatan'=>$row['keterlambatan'],
'durasi'=>$row['durasi'],
'photo'=>$row['photo']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('temporary_presensi => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($id)
    {

        if($this->core->loadDisabledMenu('temporary_presensi')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('temporary_presensi', '*', ['id' => $id]);

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
          $this->core->LogQuery('temporary_presensi => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($id)
    {

        if($this->core->loadDisabledMenu('temporary_presensi')['read'] == 'true') {
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
          $this->core->LogQuery('temporary_presensi => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'id' => $id]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('temporary_presensi', 'status', ['GROUP' => 'status']);
      $datasets = $this->core->db->select('temporary_presensi', ['count' => \Medoo\Medoo::raw('COUNT(<status>)')], ['GROUP' => 'status']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('temporary_presensi', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('temporary_presensi', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'temporary_presensi';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('temporary_presensi => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/temporary_presensi/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/temporary_presensi/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('temporary_presensi')]);
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

        $this->core->addCSS(url([ 'temporary_presensi', 'css']));
        $this->core->addJS(url([ 'temporary_presensi', 'javascript']), 'footer');
    }

}
