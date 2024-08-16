<?php
namespace Plugins\Gudangbarang;

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
        $disabled_menu = $this->core->loadDisabledMenu('gudangbarang'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kode_brng');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_gudangbarang= isset_or($_POST['search_field_gudangbarang']);
        $search_text_gudangbarang = isset_or($_POST['search_text_gudangbarang']);

        if ($search_text_gudangbarang != '') {
          $where[$search_field_gudangbarang.'[~]'] = $search_text_gudangbarang;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('gudangbarang', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('gudangbarang', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('gudangbarang', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kode_brng'=>$row['kode_brng'],
'kd_bangsal'=>$row['kd_bangsal'],
'stok'=>$row['stok'],
'no_batch'=>$row['no_batch'],
'no_faktur'=>$row['no_faktur']

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
          $this->core->LogQuery('gudangbarang => postData');
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

            if($this->core->loadDisabledMenu('gudangbarang')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kode_brng = $_POST['kode_brng'];
$kd_bangsal = $_POST['kd_bangsal'];
$stok = $_POST['stok'];
$no_batch = $_POST['no_batch'];
$no_faktur = $_POST['no_faktur'];

            
            $result = $this->core->db->insert('gudangbarang', [
'kode_brng'=>$kode_brng, 'kd_bangsal'=>$kd_bangsal, 'stok'=>$stok, 'no_batch'=>$no_batch, 'no_faktur'=>$no_faktur
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
              $this->core->LogQuery('gudangbarang => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('gudangbarang')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kode_brng = $_POST['kode_brng'];
$kd_bangsal = $_POST['kd_bangsal'];
$stok = $_POST['stok'];
$no_batch = $_POST['no_batch'];
$no_faktur = $_POST['no_faktur'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('gudangbarang', [
'kode_brng'=>$kode_brng, 'kd_bangsal'=>$kd_bangsal, 'stok'=>$stok, 'no_batch'=>$no_batch, 'no_faktur'=>$no_faktur
            ], [
              'kode_brng'=>$kode_brng
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
              $this->core->LogQuery('gudangbarang => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('gudangbarang')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kode_brng= $_POST['kode_brng'];
            $result = $this->core->db->delete('gudangbarang', [
              'AND' => [
                'kode_brng'=>$kode_brng
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
              $this->core->LogQuery('gudangbarang => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('gudangbarang')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_gudangbarang= $_POST['search_field_gudangbarang'];
            $search_text_gudangbarang = $_POST['search_text_gudangbarang'];

            if ($search_text_gudangbarang != '') {
              $where[$search_field_gudangbarang.'[~]'] = $search_text_gudangbarang;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('gudangbarang', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kode_brng'=>$row['kode_brng'],
'kd_bangsal'=>$row['kd_bangsal'],
'stok'=>$row['stok'],
'no_batch'=>$row['no_batch'],
'no_faktur'=>$row['no_faktur']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('gudangbarang => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kode_brng)
    {

        if($this->core->loadDisabledMenu('gudangbarang')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('gudangbarang', '*', ['kode_brng' => $kode_brng]);

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
          $this->core->LogQuery('gudangbarang => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kode_brng)
    {

        if($this->core->loadDisabledMenu('gudangbarang')['read'] == 'true') {
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
          $this->core->LogQuery('gudangbarang => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kode_brng' => $kode_brng]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('gudangbarang', 'kd_bangsal', ['GROUP' => 'kd_bangsal']);
      $datasets = $this->core->db->select('gudangbarang', ['count' => \Medoo\Medoo::raw('COUNT(<kd_bangsal>)')], ['GROUP' => 'kd_bangsal']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('gudangbarang', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('gudangbarang', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'gudangbarang';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('gudangbarang => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/gudangbarang/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/gudangbarang/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('gudangbarang')]);
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

        $this->core->addCSS(url([ 'gudangbarang', 'css']));
        $this->core->addJS(url([ 'gudangbarang', 'javascript']), 'footer');
    }

}
