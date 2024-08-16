<?php
namespace Plugins\Skdp_Bpjs;

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
        $disabled_menu = $this->core->loadDisabledMenu('skdp_bpjs'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'tahun');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_skdp_bpjs= isset_or($_POST['search_field_skdp_bpjs']);
        $search_text_skdp_bpjs = isset_or($_POST['search_text_skdp_bpjs']);

        if ($search_text_skdp_bpjs != '') {
          $where[$search_field_skdp_bpjs.'[~]'] = $search_text_skdp_bpjs;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('skdp_bpjs', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('skdp_bpjs', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('skdp_bpjs', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'tahun'=>$row['tahun'],
'no_rkm_medis'=>$row['no_rkm_medis'],
'diagnosa'=>$row['diagnosa'],
'terapi'=>$row['terapi'],
'alasan1'=>$row['alasan1'],
'alasan2'=>$row['alasan2'],
'rtl1'=>$row['rtl1'],
'rtl2'=>$row['rtl2'],
'tanggal_datang'=>$row['tanggal_datang'],
'tanggal_rujukan'=>$row['tanggal_rujukan'],
'no_antrian'=>$row['no_antrian'],
'kd_dokter'=>$row['kd_dokter'],
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
          $this->core->LogQuery('skdp_bpjs => postData');
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

            if($this->core->loadDisabledMenu('skdp_bpjs')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $tahun = $_POST['tahun'];
$no_rkm_medis = $_POST['no_rkm_medis'];
$diagnosa = $_POST['diagnosa'];
$terapi = $_POST['terapi'];
$alasan1 = $_POST['alasan1'];
$alasan2 = $_POST['alasan2'];
$rtl1 = $_POST['rtl1'];
$rtl2 = $_POST['rtl2'];
$tanggal_datang = $_POST['tanggal_datang'];
$tanggal_rujukan = $_POST['tanggal_rujukan'];
$no_antrian = $_POST['no_antrian'];
$kd_dokter = $_POST['kd_dokter'];
$status = $_POST['status'];

            
            $result = $this->core->db->insert('skdp_bpjs', [
'tahun'=>$tahun, 'no_rkm_medis'=>$no_rkm_medis, 'diagnosa'=>$diagnosa, 'terapi'=>$terapi, 'alasan1'=>$alasan1, 'alasan2'=>$alasan2, 'rtl1'=>$rtl1, 'rtl2'=>$rtl2, 'tanggal_datang'=>$tanggal_datang, 'tanggal_rujukan'=>$tanggal_rujukan, 'no_antrian'=>$no_antrian, 'kd_dokter'=>$kd_dokter, 'status'=>$status
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
              $this->core->LogQuery('skdp_bpjs => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('skdp_bpjs')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $tahun = $_POST['tahun'];
$no_rkm_medis = $_POST['no_rkm_medis'];
$diagnosa = $_POST['diagnosa'];
$terapi = $_POST['terapi'];
$alasan1 = $_POST['alasan1'];
$alasan2 = $_POST['alasan2'];
$rtl1 = $_POST['rtl1'];
$rtl2 = $_POST['rtl2'];
$tanggal_datang = $_POST['tanggal_datang'];
$tanggal_rujukan = $_POST['tanggal_rujukan'];
$no_antrian = $_POST['no_antrian'];
$kd_dokter = $_POST['kd_dokter'];
$status = $_POST['status'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('skdp_bpjs', [
'tahun'=>$tahun, 'no_rkm_medis'=>$no_rkm_medis, 'diagnosa'=>$diagnosa, 'terapi'=>$terapi, 'alasan1'=>$alasan1, 'alasan2'=>$alasan2, 'rtl1'=>$rtl1, 'rtl2'=>$rtl2, 'tanggal_datang'=>$tanggal_datang, 'tanggal_rujukan'=>$tanggal_rujukan, 'no_antrian'=>$no_antrian, 'kd_dokter'=>$kd_dokter, 'status'=>$status
            ], [
              'tahun'=>$tahun
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
              $this->core->LogQuery('skdp_bpjs => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('skdp_bpjs')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $tahun= $_POST['tahun'];
            $result = $this->core->db->delete('skdp_bpjs', [
              'AND' => [
                'tahun'=>$tahun
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
              $this->core->LogQuery('skdp_bpjs => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('skdp_bpjs')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_skdp_bpjs= $_POST['search_field_skdp_bpjs'];
            $search_text_skdp_bpjs = $_POST['search_text_skdp_bpjs'];

            if ($search_text_skdp_bpjs != '') {
              $where[$search_field_skdp_bpjs.'[~]'] = $search_text_skdp_bpjs;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('skdp_bpjs', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'tahun'=>$row['tahun'],
'no_rkm_medis'=>$row['no_rkm_medis'],
'diagnosa'=>$row['diagnosa'],
'terapi'=>$row['terapi'],
'alasan1'=>$row['alasan1'],
'alasan2'=>$row['alasan2'],
'rtl1'=>$row['rtl1'],
'rtl2'=>$row['rtl2'],
'tanggal_datang'=>$row['tanggal_datang'],
'tanggal_rujukan'=>$row['tanggal_rujukan'],
'no_antrian'=>$row['no_antrian'],
'kd_dokter'=>$row['kd_dokter'],
'status'=>$row['status']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('skdp_bpjs => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($tahun)
    {

        if($this->core->loadDisabledMenu('skdp_bpjs')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('skdp_bpjs', '*', ['tahun' => $tahun]);

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
          $this->core->LogQuery('skdp_bpjs => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($tahun)
    {

        if($this->core->loadDisabledMenu('skdp_bpjs')['read'] == 'true') {
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
          $this->core->LogQuery('skdp_bpjs => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'tahun' => $tahun]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('skdp_bpjs', 'tahun', ['GROUP' => 'tahun']);
      $datasets = $this->core->db->select('skdp_bpjs', ['count' => \Medoo\Medoo::raw('COUNT(<tahun>)')], ['GROUP' => 'tahun']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('skdp_bpjs', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('skdp_bpjs', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'skdp_bpjs';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('skdp_bpjs => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/skdp_bpjs/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/skdp_bpjs/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('skdp_bpjs')]);
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

        $this->core->addCSS(url([ 'skdp_bpjs', 'css']));
        $this->core->addJS(url([ 'skdp_bpjs', 'javascript']), 'footer');
    }

}
