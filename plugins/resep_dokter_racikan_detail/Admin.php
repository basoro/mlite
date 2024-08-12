<?php
namespace Plugins\Resep_Dokter_Racikan_Detail;

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
        $disabled_menu = $this->core->loadDisabledMenu('resep_dokter_racikan_detail'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'no_resep');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_resep_dokter_racikan_detail= isset_or($_POST['search_field_resep_dokter_racikan_detail']);
        $search_text_resep_dokter_racikan_detail = isset_or($_POST['search_text_resep_dokter_racikan_detail']);

        if ($search_text_resep_dokter_racikan_detail != '') {
          $where[$search_field_resep_dokter_racikan_detail.'[~]'] = $search_text_resep_dokter_racikan_detail;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('resep_dokter_racikan_detail', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('resep_dokter_racikan_detail', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('resep_dokter_racikan_detail', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_resep'=>$row['no_resep'],
'no_racik'=>$row['no_racik'],
'kode_brng'=>$row['kode_brng'],
'p1'=>$row['p1'],
'p2'=>$row['p2'],
'kandungan'=>$row['kandungan'],
'jml'=>$row['jml']

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
          $this->core->LogQuery('resep_dokter_racikan_detail => postData');
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

            if($this->core->loadDisabledMenu('resep_dokter_racikan_detail')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_resep = $_POST['no_resep'];
$no_racik = $_POST['no_racik'];
$kode_brng = $_POST['kode_brng'];
$p1 = $_POST['p1'];
$p2 = $_POST['p2'];
$kandungan = $_POST['kandungan'];
$jml = $_POST['jml'];

            
            $result = $this->core->db->insert('resep_dokter_racikan_detail', [
'no_resep'=>$no_resep, 'no_racik'=>$no_racik, 'kode_brng'=>$kode_brng, 'p1'=>$p1, 'p2'=>$p2, 'kandungan'=>$kandungan, 'jml'=>$jml
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
              $this->core->LogQuery('resep_dokter_racikan_detail => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('resep_dokter_racikan_detail')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_resep = $_POST['no_resep'];
$no_racik = $_POST['no_racik'];
$kode_brng = $_POST['kode_brng'];
$p1 = $_POST['p1'];
$p2 = $_POST['p2'];
$kandungan = $_POST['kandungan'];
$jml = $_POST['jml'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('resep_dokter_racikan_detail', [
'no_resep'=>$no_resep, 'no_racik'=>$no_racik, 'kode_brng'=>$kode_brng, 'p1'=>$p1, 'p2'=>$p2, 'kandungan'=>$kandungan, 'jml'=>$jml
            ], [
              'no_resep'=>$no_resep
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
              $this->core->LogQuery('resep_dokter_racikan_detail => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('resep_dokter_racikan_detail')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_resep= $_POST['no_resep'];
            $result = $this->core->db->delete('resep_dokter_racikan_detail', [
              'AND' => [
                'no_resep'=>$no_resep
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
              $this->core->LogQuery('resep_dokter_racikan_detail => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('resep_dokter_racikan_detail')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_resep_dokter_racikan_detail= $_POST['search_field_resep_dokter_racikan_detail'];
            $search_text_resep_dokter_racikan_detail = $_POST['search_text_resep_dokter_racikan_detail'];

            if ($search_text_resep_dokter_racikan_detail != '') {
              $where[$search_field_resep_dokter_racikan_detail.'[~]'] = $search_text_resep_dokter_racikan_detail;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('resep_dokter_racikan_detail', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_resep'=>$row['no_resep'],
'no_racik'=>$row['no_racik'],
'kode_brng'=>$row['kode_brng'],
'p1'=>$row['p1'],
'p2'=>$row['p2'],
'kandungan'=>$row['kandungan'],
'jml'=>$row['jml']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('resep_dokter_racikan_detail => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_resep)
    {

        if($this->core->loadDisabledMenu('resep_dokter_racikan_detail')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('resep_dokter_racikan_detail', '*', ['no_resep' => $no_resep]);

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
          $this->core->LogQuery('resep_dokter_racikan_detail => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_resep)
    {

        if($this->core->loadDisabledMenu('resep_dokter_racikan_detail')['read'] == 'true') {
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
          $this->core->LogQuery('resep_dokter_racikan_detail => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_resep' => $no_resep]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('resep_dokter_racikan_detail', 'no_resep', ['GROUP' => 'no_resep']);
      $datasets = $this->core->db->select('resep_dokter_racikan_detail', ['count' => \Medoo\Medoo::raw('COUNT(<no_resep>)')], ['GROUP' => 'no_resep']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('resep_dokter_racikan_detail', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('resep_dokter_racikan_detail', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'resep_dokter_racikan_detail';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('resep_dokter_racikan_detail => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/resep_dokter_racikan_detail/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/resep_dokter_racikan_detail/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('resep_dokter_racikan_detail')]);
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

        $this->core->addCSS(url([ 'resep_dokter_racikan_detail', 'css']));
        $this->core->addJS(url([ 'resep_dokter_racikan_detail', 'javascript']), 'footer');
    }

}
