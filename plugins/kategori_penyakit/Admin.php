<?php
namespace Plugins\Kategori_Penyakit;

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
        $disabled_menu = $this->core->loadDisabledMenu('kategori_penyakit'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kd_ktg');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_kategori_penyakit= isset_or($_POST['search_field_kategori_penyakit']);
        $search_text_kategori_penyakit = isset_or($_POST['search_text_kategori_penyakit']);

        if ($search_text_kategori_penyakit != '') {
          $where[$search_field_kategori_penyakit.'[~]'] = $search_text_kategori_penyakit;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('kategori_penyakit', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('kategori_penyakit', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('kategori_penyakit', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kd_ktg'=>$row['kd_ktg'],
'nm_kategori'=>$row['nm_kategori'],
'ciri_umum'=>$row['ciri_umum']

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
          $this->core->LogQuery('kategori_penyakit => postData');
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

            if($this->core->loadDisabledMenu('kategori_penyakit')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kd_ktg = $_POST['kd_ktg'];
$nm_kategori = $_POST['nm_kategori'];
$ciri_umum = $_POST['ciri_umum'];

            
            $result = $this->core->db->insert('kategori_penyakit', [
'kd_ktg'=>$kd_ktg, 'nm_kategori'=>$nm_kategori, 'ciri_umum'=>$ciri_umum
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
              $this->core->LogQuery('kategori_penyakit => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('kategori_penyakit')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kd_ktg = $_POST['kd_ktg'];
$nm_kategori = $_POST['nm_kategori'];
$ciri_umum = $_POST['ciri_umum'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('kategori_penyakit', [
'kd_ktg'=>$kd_ktg, 'nm_kategori'=>$nm_kategori, 'ciri_umum'=>$ciri_umum
            ], [
              'kd_ktg'=>$kd_ktg
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
              $this->core->LogQuery('kategori_penyakit => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('kategori_penyakit')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_ktg= $_POST['kd_ktg'];
            $result = $this->core->db->delete('kategori_penyakit', [
              'AND' => [
                'kd_ktg'=>$kd_ktg
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
              $this->core->LogQuery('kategori_penyakit => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('kategori_penyakit')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_kategori_penyakit= $_POST['search_field_kategori_penyakit'];
            $search_text_kategori_penyakit = $_POST['search_text_kategori_penyakit'];

            if ($search_text_kategori_penyakit != '') {
              $where[$search_field_kategori_penyakit.'[~]'] = $search_text_kategori_penyakit;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('kategori_penyakit', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kd_ktg'=>$row['kd_ktg'],
'nm_kategori'=>$row['nm_kategori'],
'ciri_umum'=>$row['ciri_umum']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('kategori_penyakit => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kd_ktg)
    {

        if($this->core->loadDisabledMenu('kategori_penyakit')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('kategori_penyakit', '*', ['kd_ktg' => $kd_ktg]);

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
          $this->core->LogQuery('kategori_penyakit => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kd_ktg)
    {

        if($this->core->loadDisabledMenu('kategori_penyakit')['read'] == 'true') {
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
          $this->core->LogQuery('kategori_penyakit => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kd_ktg' => $kd_ktg]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('kategori_penyakit', 'ciri_umum', ['GROUP' => 'ciri_umum']);
      $datasets = $this->core->db->select('kategori_penyakit', ['count' => \Medoo\Medoo::raw('COUNT(<ciri_umum>)')], ['GROUP' => 'ciri_umum']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('kategori_penyakit', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('kategori_penyakit', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'kategori_penyakit';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('kategori_penyakit => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/kategori_penyakit/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/kategori_penyakit/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('kategori_penyakit')]);
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

        $this->core->addCSS(url([ 'kategori_penyakit', 'css']));
        $this->core->addJS(url([ 'kategori_penyakit', 'javascript']), 'footer');
    }

}
