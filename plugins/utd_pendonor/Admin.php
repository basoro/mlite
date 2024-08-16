<?php
namespace Plugins\Utd_Pendonor;

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
        $disabled_menu = $this->core->loadDisabledMenu('utd_pendonor'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'no_pendonor');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_utd_pendonor= isset_or($_POST['search_field_utd_pendonor']);
        $search_text_utd_pendonor = isset_or($_POST['search_text_utd_pendonor']);

        if ($search_text_utd_pendonor != '') {
          $where[$search_field_utd_pendonor.'[~]'] = $search_text_utd_pendonor;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('utd_pendonor', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('utd_pendonor', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('utd_pendonor', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_pendonor'=>$row['no_pendonor'],
'nama'=>$row['nama'],
'no_ktp'=>$row['no_ktp'],
'jk'=>$row['jk'],
'tmp_lahir'=>$row['tmp_lahir'],
'tgl_lahir'=>$row['tgl_lahir'],
'alamat'=>$row['alamat'],
'kd_kel'=>$row['kd_kel'],
'kd_kec'=>$row['kd_kec'],
'kd_kab'=>$row['kd_kab'],
'kd_prop'=>$row['kd_prop'],
'golongan_darah'=>$row['golongan_darah'],
'resus'=>$row['resus'],
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
          $this->core->LogQuery('utd_pendonor => postData');
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

            if($this->core->loadDisabledMenu('utd_pendonor')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_pendonor = $_POST['no_pendonor'];
$nama = $_POST['nama'];
$no_ktp = $_POST['no_ktp'];
$jk = $_POST['jk'];
$tmp_lahir = $_POST['tmp_lahir'];
$tgl_lahir = $_POST['tgl_lahir'];
$alamat = $_POST['alamat'];
$kd_kel = $_POST['kd_kel'];
$kd_kec = $_POST['kd_kec'];
$kd_kab = $_POST['kd_kab'];
$kd_prop = $_POST['kd_prop'];
$golongan_darah = $_POST['golongan_darah'];
$resus = $_POST['resus'];
$no_telp = $_POST['no_telp'];

            
            $result = $this->core->db->insert('utd_pendonor', [
'no_pendonor'=>$no_pendonor, 'nama'=>$nama, 'no_ktp'=>$no_ktp, 'jk'=>$jk, 'tmp_lahir'=>$tmp_lahir, 'tgl_lahir'=>$tgl_lahir, 'alamat'=>$alamat, 'kd_kel'=>$kd_kel, 'kd_kec'=>$kd_kec, 'kd_kab'=>$kd_kab, 'kd_prop'=>$kd_prop, 'golongan_darah'=>$golongan_darah, 'resus'=>$resus, 'no_telp'=>$no_telp
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
              $this->core->LogQuery('utd_pendonor => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('utd_pendonor')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_pendonor = $_POST['no_pendonor'];
$nama = $_POST['nama'];
$no_ktp = $_POST['no_ktp'];
$jk = $_POST['jk'];
$tmp_lahir = $_POST['tmp_lahir'];
$tgl_lahir = $_POST['tgl_lahir'];
$alamat = $_POST['alamat'];
$kd_kel = $_POST['kd_kel'];
$kd_kec = $_POST['kd_kec'];
$kd_kab = $_POST['kd_kab'];
$kd_prop = $_POST['kd_prop'];
$golongan_darah = $_POST['golongan_darah'];
$resus = $_POST['resus'];
$no_telp = $_POST['no_telp'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('utd_pendonor', [
'no_pendonor'=>$no_pendonor, 'nama'=>$nama, 'no_ktp'=>$no_ktp, 'jk'=>$jk, 'tmp_lahir'=>$tmp_lahir, 'tgl_lahir'=>$tgl_lahir, 'alamat'=>$alamat, 'kd_kel'=>$kd_kel, 'kd_kec'=>$kd_kec, 'kd_kab'=>$kd_kab, 'kd_prop'=>$kd_prop, 'golongan_darah'=>$golongan_darah, 'resus'=>$resus, 'no_telp'=>$no_telp
            ], [
              'no_pendonor'=>$no_pendonor
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
              $this->core->LogQuery('utd_pendonor => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('utd_pendonor')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_pendonor= $_POST['no_pendonor'];
            $result = $this->core->db->delete('utd_pendonor', [
              'AND' => [
                'no_pendonor'=>$no_pendonor
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
              $this->core->LogQuery('utd_pendonor => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('utd_pendonor')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_utd_pendonor= $_POST['search_field_utd_pendonor'];
            $search_text_utd_pendonor = $_POST['search_text_utd_pendonor'];

            if ($search_text_utd_pendonor != '') {
              $where[$search_field_utd_pendonor.'[~]'] = $search_text_utd_pendonor;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('utd_pendonor', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_pendonor'=>$row['no_pendonor'],
'nama'=>$row['nama'],
'no_ktp'=>$row['no_ktp'],
'jk'=>$row['jk'],
'tmp_lahir'=>$row['tmp_lahir'],
'tgl_lahir'=>$row['tgl_lahir'],
'alamat'=>$row['alamat'],
'kd_kel'=>$row['kd_kel'],
'kd_kec'=>$row['kd_kec'],
'kd_kab'=>$row['kd_kab'],
'kd_prop'=>$row['kd_prop'],
'golongan_darah'=>$row['golongan_darah'],
'resus'=>$row['resus'],
'no_telp'=>$row['no_telp']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('utd_pendonor => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_pendonor)
    {

        if($this->core->loadDisabledMenu('utd_pendonor')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('utd_pendonor', '*', ['no_pendonor' => $no_pendonor]);

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
          $this->core->LogQuery('utd_pendonor => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_pendonor)
    {

        if($this->core->loadDisabledMenu('utd_pendonor')['read'] == 'true') {
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
          $this->core->LogQuery('utd_pendonor => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_pendonor' => $no_pendonor]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('utd_pendonor', 'golongan_darah', ['GROUP' => 'golongan_darah']);
      $datasets = $this->core->db->select('utd_pendonor', ['count' => \Medoo\Medoo::raw('COUNT(<golongan_darah>)')], ['GROUP' => 'golongan_darah']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('utd_pendonor', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('utd_pendonor', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'utd_pendonor';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('utd_pendonor => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/utd_pendonor/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/utd_pendonor/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('utd_pendonor')]);
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

        $this->core->addCSS(url([ 'utd_pendonor', 'css']));
        $this->core->addJS(url([ 'utd_pendonor', 'javascript']), 'footer');
    }

}
