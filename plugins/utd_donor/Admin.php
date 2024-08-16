<?php
namespace Plugins\Utd_Donor;

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
        $disabled_menu = $this->core->loadDisabledMenu('utd_donor'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'no_donor');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_utd_donor= isset_or($_POST['search_field_utd_donor']);
        $search_text_utd_donor = isset_or($_POST['search_text_utd_donor']);

        if ($search_text_utd_donor != '') {
          $where[$search_field_utd_donor.'[~]'] = $search_text_utd_donor;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('utd_donor', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('utd_donor', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('utd_donor', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_donor'=>$row['no_donor'],
'no_pendonor'=>$row['no_pendonor'],
'tanggal'=>$row['tanggal'],
'dinas'=>$row['dinas'],
'tensi'=>$row['tensi'],
'no_bag'=>$row['no_bag'],
'jenis_bag'=>$row['jenis_bag'],
'jenis_donor'=>$row['jenis_donor'],
'tempat_aftap'=>$row['tempat_aftap'],
'petugas_aftap'=>$row['petugas_aftap'],
'hbsag'=>$row['hbsag'],
'hcv'=>$row['hcv'],
'hiv'=>$row['hiv'],
'spilis'=>$row['spilis'],
'malaria'=>$row['malaria'],
'petugas_u_saring'=>$row['petugas_u_saring'],
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
          $this->core->LogQuery('utd_donor => postData');
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

            if($this->core->loadDisabledMenu('utd_donor')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_donor = $_POST['no_donor'];
$no_pendonor = $_POST['no_pendonor'];
$tanggal = $_POST['tanggal'];
$dinas = $_POST['dinas'];
$tensi = $_POST['tensi'];
$no_bag = $_POST['no_bag'];
$jenis_bag = $_POST['jenis_bag'];
$jenis_donor = $_POST['jenis_donor'];
$tempat_aftap = $_POST['tempat_aftap'];
$petugas_aftap = $_POST['petugas_aftap'];
$hbsag = $_POST['hbsag'];
$hcv = $_POST['hcv'];
$hiv = $_POST['hiv'];
$spilis = $_POST['spilis'];
$malaria = $_POST['malaria'];
$petugas_u_saring = $_POST['petugas_u_saring'];
$status = $_POST['status'];

            
            $result = $this->core->db->insert('utd_donor', [
'no_donor'=>$no_donor, 'no_pendonor'=>$no_pendonor, 'tanggal'=>$tanggal, 'dinas'=>$dinas, 'tensi'=>$tensi, 'no_bag'=>$no_bag, 'jenis_bag'=>$jenis_bag, 'jenis_donor'=>$jenis_donor, 'tempat_aftap'=>$tempat_aftap, 'petugas_aftap'=>$petugas_aftap, 'hbsag'=>$hbsag, 'hcv'=>$hcv, 'hiv'=>$hiv, 'spilis'=>$spilis, 'malaria'=>$malaria, 'petugas_u_saring'=>$petugas_u_saring, 'status'=>$status
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
              $this->core->LogQuery('utd_donor => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('utd_donor')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_donor = $_POST['no_donor'];
$no_pendonor = $_POST['no_pendonor'];
$tanggal = $_POST['tanggal'];
$dinas = $_POST['dinas'];
$tensi = $_POST['tensi'];
$no_bag = $_POST['no_bag'];
$jenis_bag = $_POST['jenis_bag'];
$jenis_donor = $_POST['jenis_donor'];
$tempat_aftap = $_POST['tempat_aftap'];
$petugas_aftap = $_POST['petugas_aftap'];
$hbsag = $_POST['hbsag'];
$hcv = $_POST['hcv'];
$hiv = $_POST['hiv'];
$spilis = $_POST['spilis'];
$malaria = $_POST['malaria'];
$petugas_u_saring = $_POST['petugas_u_saring'];
$status = $_POST['status'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('utd_donor', [
'no_donor'=>$no_donor, 'no_pendonor'=>$no_pendonor, 'tanggal'=>$tanggal, 'dinas'=>$dinas, 'tensi'=>$tensi, 'no_bag'=>$no_bag, 'jenis_bag'=>$jenis_bag, 'jenis_donor'=>$jenis_donor, 'tempat_aftap'=>$tempat_aftap, 'petugas_aftap'=>$petugas_aftap, 'hbsag'=>$hbsag, 'hcv'=>$hcv, 'hiv'=>$hiv, 'spilis'=>$spilis, 'malaria'=>$malaria, 'petugas_u_saring'=>$petugas_u_saring, 'status'=>$status
            ], [
              'no_donor'=>$no_donor
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
              $this->core->LogQuery('utd_donor => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('utd_donor')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_donor= $_POST['no_donor'];
            $result = $this->core->db->delete('utd_donor', [
              'AND' => [
                'no_donor'=>$no_donor
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
              $this->core->LogQuery('utd_donor => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('utd_donor')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_utd_donor= $_POST['search_field_utd_donor'];
            $search_text_utd_donor = $_POST['search_text_utd_donor'];

            if ($search_text_utd_donor != '') {
              $where[$search_field_utd_donor.'[~]'] = $search_text_utd_donor;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('utd_donor', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_donor'=>$row['no_donor'],
'no_pendonor'=>$row['no_pendonor'],
'tanggal'=>$row['tanggal'],
'dinas'=>$row['dinas'],
'tensi'=>$row['tensi'],
'no_bag'=>$row['no_bag'],
'jenis_bag'=>$row['jenis_bag'],
'jenis_donor'=>$row['jenis_donor'],
'tempat_aftap'=>$row['tempat_aftap'],
'petugas_aftap'=>$row['petugas_aftap'],
'hbsag'=>$row['hbsag'],
'hcv'=>$row['hcv'],
'hiv'=>$row['hiv'],
'spilis'=>$row['spilis'],
'malaria'=>$row['malaria'],
'petugas_u_saring'=>$row['petugas_u_saring'],
'status'=>$row['status']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('utd_donor => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_donor)
    {

        if($this->core->loadDisabledMenu('utd_donor')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('utd_donor', '*', ['no_donor' => $no_donor]);

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
          $this->core->LogQuery('utd_donor => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_donor)
    {

        if($this->core->loadDisabledMenu('utd_donor')['read'] == 'true') {
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
          $this->core->LogQuery('utd_donor => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_donor' => $no_donor]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('utd_donor', 'petugas_aftap', ['GROUP' => 'petugas_aftap']);
      $datasets = $this->core->db->select('utd_donor', ['count' => \Medoo\Medoo::raw('COUNT(<petugas_aftap>)')], ['GROUP' => 'petugas_aftap']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('utd_donor', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('utd_donor', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'utd_donor';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('utd_donor => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/utd_donor/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/utd_donor/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('utd_donor')]);
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

        $this->core->addCSS(url([ 'utd_donor', 'css']));
        $this->core->addJS(url([ 'utd_donor', 'javascript']), 'footer');
    }

}
