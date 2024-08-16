<?php
namespace Plugins\Utd_Stok_Darah;

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
        $disabled_menu = $this->core->loadDisabledMenu('utd_stok_darah'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'no_kantong');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_utd_stok_darah= isset_or($_POST['search_field_utd_stok_darah']);
        $search_text_utd_stok_darah = isset_or($_POST['search_text_utd_stok_darah']);

        if ($search_text_utd_stok_darah != '') {
          $where[$search_field_utd_stok_darah.'[~]'] = $search_text_utd_stok_darah;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('utd_stok_darah', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('utd_stok_darah', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('utd_stok_darah', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_kantong'=>$row['no_kantong'],
'kode_komponen'=>$row['kode_komponen'],
'golongan_darah'=>$row['golongan_darah'],
'resus'=>$row['resus'],
'tanggal_aftap'=>$row['tanggal_aftap'],
'tanggal_kadaluarsa'=>$row['tanggal_kadaluarsa'],
'asal_darah'=>$row['asal_darah'],
'status'=>$row['status']

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
          $this->core->LogQuery('utd_stok_darah => postData');
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

            if($this->core->loadDisabledMenu('utd_stok_darah')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_kantong = $_POST['no_kantong'];
$kode_komponen = $_POST['kode_komponen'];
$golongan_darah = $_POST['golongan_darah'];
$resus = $_POST['resus'];
$tanggal_aftap = $_POST['tanggal_aftap'];
$tanggal_kadaluarsa = $_POST['tanggal_kadaluarsa'];
$asal_darah = $_POST['asal_darah'];
$status = $_POST['status'];

            
            $result = $this->core->db->insert('utd_stok_darah', [
'no_kantong'=>$no_kantong, 'kode_komponen'=>$kode_komponen, 'golongan_darah'=>$golongan_darah, 'resus'=>$resus, 'tanggal_aftap'=>$tanggal_aftap, 'tanggal_kadaluarsa'=>$tanggal_kadaluarsa, 'asal_darah'=>$asal_darah, 'status'=>$status
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
              $this->core->LogQuery('utd_stok_darah => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('utd_stok_darah')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_kantong = $_POST['no_kantong'];
$kode_komponen = $_POST['kode_komponen'];
$golongan_darah = $_POST['golongan_darah'];
$resus = $_POST['resus'];
$tanggal_aftap = $_POST['tanggal_aftap'];
$tanggal_kadaluarsa = $_POST['tanggal_kadaluarsa'];
$asal_darah = $_POST['asal_darah'];
$status = $_POST['status'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('utd_stok_darah', [
'no_kantong'=>$no_kantong, 'kode_komponen'=>$kode_komponen, 'golongan_darah'=>$golongan_darah, 'resus'=>$resus, 'tanggal_aftap'=>$tanggal_aftap, 'tanggal_kadaluarsa'=>$tanggal_kadaluarsa, 'asal_darah'=>$asal_darah, 'status'=>$status
            ], [
              'no_kantong'=>$no_kantong
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
              $this->core->LogQuery('utd_stok_darah => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('utd_stok_darah')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_kantong= $_POST['no_kantong'];
            $result = $this->core->db->delete('utd_stok_darah', [
              'AND' => [
                'no_kantong'=>$no_kantong
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
              $this->core->LogQuery('utd_stok_darah => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('utd_stok_darah')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_utd_stok_darah= $_POST['search_field_utd_stok_darah'];
            $search_text_utd_stok_darah = $_POST['search_text_utd_stok_darah'];

            if ($search_text_utd_stok_darah != '') {
              $where[$search_field_utd_stok_darah.'[~]'] = $search_text_utd_stok_darah;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('utd_stok_darah', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_kantong'=>$row['no_kantong'],
'kode_komponen'=>$row['kode_komponen'],
'golongan_darah'=>$row['golongan_darah'],
'resus'=>$row['resus'],
'tanggal_aftap'=>$row['tanggal_aftap'],
'tanggal_kadaluarsa'=>$row['tanggal_kadaluarsa'],
'asal_darah'=>$row['asal_darah'],
'status'=>$row['status']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('utd_stok_darah => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_kantong)
    {

        if($this->core->loadDisabledMenu('utd_stok_darah')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('utd_stok_darah', '*', ['no_kantong' => $no_kantong]);

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
          $this->core->LogQuery('utd_stok_darah => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_kantong)
    {

        if($this->core->loadDisabledMenu('utd_stok_darah')['read'] == 'true') {
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
          $this->core->LogQuery('utd_stok_darah => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_kantong' => $no_kantong]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('utd_stok_darah', 'golongan_darah', ['GROUP' => 'golongan_darah']);
      $datasets = $this->core->db->select('utd_stok_darah', ['count' => \Medoo\Medoo::raw('COUNT(<golongan_darah>)')], ['GROUP' => 'golongan_darah']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('utd_stok_darah', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('utd_stok_darah', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'utd_stok_darah';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('utd_stok_darah => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/utd_stok_darah/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/utd_stok_darah/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('utd_stok_darah')]);
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

        $this->core->addCSS(url([ 'utd_stok_darah', 'css']));
        $this->core->addJS(url([ 'utd_stok_darah', 'javascript']), 'footer');
    }

}
