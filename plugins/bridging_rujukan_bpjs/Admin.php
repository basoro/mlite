<?php
namespace Plugins\Bridging_Rujukan_Bpjs;

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
        $disabled_menu = $this->core->loadDisabledMenu('bridging_rujukan_bpjs'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'no_sep');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_bridging_rujukan_bpjs= isset_or($_POST['search_field_bridging_rujukan_bpjs']);
        $search_text_bridging_rujukan_bpjs = isset_or($_POST['search_text_bridging_rujukan_bpjs']);

        if ($search_text_bridging_rujukan_bpjs != '') {
          $where[$search_field_bridging_rujukan_bpjs.'[~]'] = $search_text_bridging_rujukan_bpjs;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('bridging_rujukan_bpjs', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('bridging_rujukan_bpjs', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('bridging_rujukan_bpjs', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_sep'=>$row['no_sep'],
'tglRujukan'=>$row['tglRujukan'],
'tglRencanaKunjungan'=>$row['tglRencanaKunjungan'],
'ppkDirujuk'=>$row['ppkDirujuk'],
'nm_ppkDirujuk'=>$row['nm_ppkDirujuk'],
'jnsPelayanan'=>$row['jnsPelayanan'],
'catatan'=>$row['catatan'],
'diagRujukan'=>$row['diagRujukan'],
'nama_diagRujukan'=>$row['nama_diagRujukan'],
'tipeRujukan'=>$row['tipeRujukan'],
'poliRujukan'=>$row['poliRujukan'],
'nama_poliRujukan'=>$row['nama_poliRujukan'],
'no_rujukan'=>$row['no_rujukan'],
'user'=>$row['user']

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
          $this->core->LogQuery('bridging_rujukan_bpjs => postData');
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

            if($this->core->loadDisabledMenu('bridging_rujukan_bpjs')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_sep = $_POST['no_sep'];
$tglRujukan = $_POST['tglRujukan'];
$tglRencanaKunjungan = $_POST['tglRencanaKunjungan'];
$ppkDirujuk = $_POST['ppkDirujuk'];
$nm_ppkDirujuk = $_POST['nm_ppkDirujuk'];
$jnsPelayanan = $_POST['jnsPelayanan'];
$catatan = $_POST['catatan'];
$diagRujukan = $_POST['diagRujukan'];
$nama_diagRujukan = $_POST['nama_diagRujukan'];
$tipeRujukan = $_POST['tipeRujukan'];
$poliRujukan = $_POST['poliRujukan'];
$nama_poliRujukan = $_POST['nama_poliRujukan'];
$no_rujukan = $_POST['no_rujukan'];
$user = $_POST['user'];

            
            $result = $this->core->db->insert('bridging_rujukan_bpjs', [
'no_sep'=>$no_sep, 'tglRujukan'=>$tglRujukan, 'tglRencanaKunjungan'=>$tglRencanaKunjungan, 'ppkDirujuk'=>$ppkDirujuk, 'nm_ppkDirujuk'=>$nm_ppkDirujuk, 'jnsPelayanan'=>$jnsPelayanan, 'catatan'=>$catatan, 'diagRujukan'=>$diagRujukan, 'nama_diagRujukan'=>$nama_diagRujukan, 'tipeRujukan'=>$tipeRujukan, 'poliRujukan'=>$poliRujukan, 'nama_poliRujukan'=>$nama_poliRujukan, 'no_rujukan'=>$no_rujukan, 'user'=>$user
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
              $this->core->LogQuery('bridging_rujukan_bpjs => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('bridging_rujukan_bpjs')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_sep = $_POST['no_sep'];
$tglRujukan = $_POST['tglRujukan'];
$tglRencanaKunjungan = $_POST['tglRencanaKunjungan'];
$ppkDirujuk = $_POST['ppkDirujuk'];
$nm_ppkDirujuk = $_POST['nm_ppkDirujuk'];
$jnsPelayanan = $_POST['jnsPelayanan'];
$catatan = $_POST['catatan'];
$diagRujukan = $_POST['diagRujukan'];
$nama_diagRujukan = $_POST['nama_diagRujukan'];
$tipeRujukan = $_POST['tipeRujukan'];
$poliRujukan = $_POST['poliRujukan'];
$nama_poliRujukan = $_POST['nama_poliRujukan'];
$no_rujukan = $_POST['no_rujukan'];
$user = $_POST['user'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('bridging_rujukan_bpjs', [
'no_sep'=>$no_sep, 'tglRujukan'=>$tglRujukan, 'tglRencanaKunjungan'=>$tglRencanaKunjungan, 'ppkDirujuk'=>$ppkDirujuk, 'nm_ppkDirujuk'=>$nm_ppkDirujuk, 'jnsPelayanan'=>$jnsPelayanan, 'catatan'=>$catatan, 'diagRujukan'=>$diagRujukan, 'nama_diagRujukan'=>$nama_diagRujukan, 'tipeRujukan'=>$tipeRujukan, 'poliRujukan'=>$poliRujukan, 'nama_poliRujukan'=>$nama_poliRujukan, 'no_rujukan'=>$no_rujukan, 'user'=>$user
            ], [
              'no_sep'=>$no_sep
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
              $this->core->LogQuery('bridging_rujukan_bpjs => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('bridging_rujukan_bpjs')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_sep= $_POST['no_sep'];
            $result = $this->core->db->delete('bridging_rujukan_bpjs', [
              'AND' => [
                'no_sep'=>$no_sep
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
              $this->core->LogQuery('bridging_rujukan_bpjs => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('bridging_rujukan_bpjs')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_bridging_rujukan_bpjs= $_POST['search_field_bridging_rujukan_bpjs'];
            $search_text_bridging_rujukan_bpjs = $_POST['search_text_bridging_rujukan_bpjs'];

            if ($search_text_bridging_rujukan_bpjs != '') {
              $where[$search_field_bridging_rujukan_bpjs.'[~]'] = $search_text_bridging_rujukan_bpjs;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('bridging_rujukan_bpjs', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_sep'=>$row['no_sep'],
'tglRujukan'=>$row['tglRujukan'],
'tglRencanaKunjungan'=>$row['tglRencanaKunjungan'],
'ppkDirujuk'=>$row['ppkDirujuk'],
'nm_ppkDirujuk'=>$row['nm_ppkDirujuk'],
'jnsPelayanan'=>$row['jnsPelayanan'],
'catatan'=>$row['catatan'],
'diagRujukan'=>$row['diagRujukan'],
'nama_diagRujukan'=>$row['nama_diagRujukan'],
'tipeRujukan'=>$row['tipeRujukan'],
'poliRujukan'=>$row['poliRujukan'],
'nama_poliRujukan'=>$row['nama_poliRujukan'],
'no_rujukan'=>$row['no_rujukan'],
'user'=>$row['user']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('bridging_rujukan_bpjs => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_sep)
    {

        if($this->core->loadDisabledMenu('bridging_rujukan_bpjs')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('bridging_rujukan_bpjs', '*', ['no_sep' => $no_sep]);

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
          $this->core->LogQuery('bridging_rujukan_bpjs => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_sep)
    {

        if($this->core->loadDisabledMenu('bridging_rujukan_bpjs')['read'] == 'true') {
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
          $this->core->LogQuery('bridging_rujukan_bpjs => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_sep' => $no_sep]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('bridging_rujukan_bpjs', 'jnsPelayanan', ['GROUP' => 'jnsPelayanan']);
      $datasets = $this->core->db->select('bridging_rujukan_bpjs', ['count' => \Medoo\Medoo::raw('COUNT(<jnsPelayanan>)')], ['GROUP' => 'jnsPelayanan']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('bridging_rujukan_bpjs', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('bridging_rujukan_bpjs', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'bridging_rujukan_bpjs';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('bridging_rujukan_bpjs => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/bridging_rujukan_bpjs/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/bridging_rujukan_bpjs/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('bridging_rujukan_bpjs')]);
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

        $this->core->addCSS(url([ 'bridging_rujukan_bpjs', 'css']));
        $this->core->addJS(url([ 'bridging_rujukan_bpjs', 'javascript']), 'footer');
    }

}
