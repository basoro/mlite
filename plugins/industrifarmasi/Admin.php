<?php
namespace Plugins\Industrifarmasi;

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
        $disabled_menu = $this->core->loadDisabledMenu('industrifarmasi'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kode_industri');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_industrifarmasi= isset_or($_POST['search_field_industrifarmasi']);
        $search_text_industrifarmasi = isset_or($_POST['search_text_industrifarmasi']);

        if ($search_text_industrifarmasi != '') {
          $where[$search_field_industrifarmasi.'[~]'] = $search_text_industrifarmasi;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('industrifarmasi', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('industrifarmasi', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('industrifarmasi', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kode_industri'=>$row['kode_industri'],
'nama_industri'=>$row['nama_industri'],
'alamat'=>$row['alamat'],
'kota'=>$row['kota'],
'no_telp'=>$row['no_telp']

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
          $this->core->LogQuery('industrifarmasi => postData');
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

            if($this->core->loadDisabledMenu('industrifarmasi')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kode_industri = $_POST['kode_industri'];
$nama_industri = $_POST['nama_industri'];
$alamat = $_POST['alamat'];
$kota = $_POST['kota'];
$no_telp = $_POST['no_telp'];

            
            $result = $this->core->db->insert('industrifarmasi', [
'kode_industri'=>$kode_industri, 'nama_industri'=>$nama_industri, 'alamat'=>$alamat, 'kota'=>$kota, 'no_telp'=>$no_telp
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
              $this->core->LogQuery('industrifarmasi => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('industrifarmasi')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kode_industri = $_POST['kode_industri'];
$nama_industri = $_POST['nama_industri'];
$alamat = $_POST['alamat'];
$kota = $_POST['kota'];
$no_telp = $_POST['no_telp'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('industrifarmasi', [
'kode_industri'=>$kode_industri, 'nama_industri'=>$nama_industri, 'alamat'=>$alamat, 'kota'=>$kota, 'no_telp'=>$no_telp
            ], [
              'kode_industri'=>$kode_industri
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
              $this->core->LogQuery('industrifarmasi => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('industrifarmasi')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kode_industri= $_POST['kode_industri'];
            $result = $this->core->db->delete('industrifarmasi', [
              'AND' => [
                'kode_industri'=>$kode_industri
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
              $this->core->LogQuery('industrifarmasi => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('industrifarmasi')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_industrifarmasi= $_POST['search_field_industrifarmasi'];
            $search_text_industrifarmasi = $_POST['search_text_industrifarmasi'];

            if ($search_text_industrifarmasi != '') {
              $where[$search_field_industrifarmasi.'[~]'] = $search_text_industrifarmasi;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('industrifarmasi', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kode_industri'=>$row['kode_industri'],
'nama_industri'=>$row['nama_industri'],
'alamat'=>$row['alamat'],
'kota'=>$row['kota'],
'no_telp'=>$row['no_telp']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('industrifarmasi => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kode_industri)
    {

        if($this->core->loadDisabledMenu('industrifarmasi')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('industrifarmasi', '*', ['kode_industri' => $kode_industri]);

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
          $this->core->LogQuery('industrifarmasi => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kode_industri)
    {

        if($this->core->loadDisabledMenu('industrifarmasi')['read'] == 'true') {
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
          $this->core->LogQuery('industrifarmasi => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kode_industri' => $kode_industri]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('industrifarmasi', 'kota', ['GROUP' => 'kota']);
      $datasets = $this->core->db->select('industrifarmasi', ['count' => \Medoo\Medoo::raw('COUNT(<kota>)')], ['GROUP' => 'kota']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('industrifarmasi', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('industrifarmasi', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'industrifarmasi';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('industrifarmasi => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/industrifarmasi/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/industrifarmasi/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('industrifarmasi')]);
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

        $this->core->addCSS(url([ 'industrifarmasi', 'css']));
        $this->core->addJS(url([ 'industrifarmasi', 'javascript']), 'footer');
    }

}
