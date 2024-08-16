<?php
namespace Plugins\Jadwal_Pegawai;

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
        $disabled_menu = $this->core->loadDisabledMenu('jadwal_pegawai'); 
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
        $search_field_jadwal_pegawai= isset_or($_POST['search_field_jadwal_pegawai']);
        $search_text_jadwal_pegawai = isset_or($_POST['search_text_jadwal_pegawai']);

        if ($search_text_jadwal_pegawai != '') {
          $where[$search_field_jadwal_pegawai.'[~]'] = $search_text_jadwal_pegawai;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('jadwal_pegawai', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('jadwal_pegawai', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('jadwal_pegawai', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'id'=>$row['id'],
'tahun'=>$row['tahun'],
'bulan'=>$row['bulan'],
'h1'=>$row['h1'],
'h2'=>$row['h2'],
'h3'=>$row['h3'],
'h4'=>$row['h4'],
'h5'=>$row['h5'],
'h6'=>$row['h6'],
'h7'=>$row['h7'],
'h8'=>$row['h8'],
'h9'=>$row['h9'],
'h10'=>$row['h10'],
'h11'=>$row['h11'],
'h12'=>$row['h12'],
'h13'=>$row['h13'],
'h14'=>$row['h14'],
'h15'=>$row['h15'],
'h16'=>$row['h16'],
'h17'=>$row['h17'],
'h18'=>$row['h18'],
'h19'=>$row['h19'],
'h20'=>$row['h20'],
'h21'=>$row['h21'],
'h22'=>$row['h22'],
'h23'=>$row['h23'],
'h24'=>$row['h24'],
'h25'=>$row['h25'],
'h26'=>$row['h26'],
'h27'=>$row['h27'],
'h28'=>$row['h28'],
'h29'=>$row['h29'],
'h30'=>$row['h30'],
'h31'=>$row['h31']

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
          $this->core->LogQuery('jadwal_pegawai => postData');
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

            if($this->core->loadDisabledMenu('jadwal_pegawai')['create'] == 'true') {
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
$tahun = $_POST['tahun'];
$bulan = $_POST['bulan'];
$h1 = $_POST['h1'];
$h2 = $_POST['h2'];
$h3 = $_POST['h3'];
$h4 = $_POST['h4'];
$h5 = $_POST['h5'];
$h6 = $_POST['h6'];
$h7 = $_POST['h7'];
$h8 = $_POST['h8'];
$h9 = $_POST['h9'];
$h10 = $_POST['h10'];
$h11 = $_POST['h11'];
$h12 = $_POST['h12'];
$h13 = $_POST['h13'];
$h14 = $_POST['h14'];
$h15 = $_POST['h15'];
$h16 = $_POST['h16'];
$h17 = $_POST['h17'];
$h18 = $_POST['h18'];
$h19 = $_POST['h19'];
$h20 = $_POST['h20'];
$h21 = $_POST['h21'];
$h22 = $_POST['h22'];
$h23 = $_POST['h23'];
$h24 = $_POST['h24'];
$h25 = $_POST['h25'];
$h26 = $_POST['h26'];
$h27 = $_POST['h27'];
$h28 = $_POST['h28'];
$h29 = $_POST['h29'];
$h30 = $_POST['h30'];
$h31 = $_POST['h31'];

            
            $result = $this->core->db->insert('jadwal_pegawai', [
'id'=>$id, 'tahun'=>$tahun, 'bulan'=>$bulan, 'h1'=>$h1, 'h2'=>$h2, 'h3'=>$h3, 'h4'=>$h4, 'h5'=>$h5, 'h6'=>$h6, 'h7'=>$h7, 'h8'=>$h8, 'h9'=>$h9, 'h10'=>$h10, 'h11'=>$h11, 'h12'=>$h12, 'h13'=>$h13, 'h14'=>$h14, 'h15'=>$h15, 'h16'=>$h16, 'h17'=>$h17, 'h18'=>$h18, 'h19'=>$h19, 'h20'=>$h20, 'h21'=>$h21, 'h22'=>$h22, 'h23'=>$h23, 'h24'=>$h24, 'h25'=>$h25, 'h26'=>$h26, 'h27'=>$h27, 'h28'=>$h28, 'h29'=>$h29, 'h30'=>$h30, 'h31'=>$h31
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
              $this->core->LogQuery('jadwal_pegawai => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('jadwal_pegawai')['update'] == 'true') {
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
$tahun = $_POST['tahun'];
$bulan = $_POST['bulan'];
$h1 = $_POST['h1'];
$h2 = $_POST['h2'];
$h3 = $_POST['h3'];
$h4 = $_POST['h4'];
$h5 = $_POST['h5'];
$h6 = $_POST['h6'];
$h7 = $_POST['h7'];
$h8 = $_POST['h8'];
$h9 = $_POST['h9'];
$h10 = $_POST['h10'];
$h11 = $_POST['h11'];
$h12 = $_POST['h12'];
$h13 = $_POST['h13'];
$h14 = $_POST['h14'];
$h15 = $_POST['h15'];
$h16 = $_POST['h16'];
$h17 = $_POST['h17'];
$h18 = $_POST['h18'];
$h19 = $_POST['h19'];
$h20 = $_POST['h20'];
$h21 = $_POST['h21'];
$h22 = $_POST['h22'];
$h23 = $_POST['h23'];
$h24 = $_POST['h24'];
$h25 = $_POST['h25'];
$h26 = $_POST['h26'];
$h27 = $_POST['h27'];
$h28 = $_POST['h28'];
$h29 = $_POST['h29'];
$h30 = $_POST['h30'];
$h31 = $_POST['h31'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('jadwal_pegawai', [
'id'=>$id, 'tahun'=>$tahun, 'bulan'=>$bulan, 'h1'=>$h1, 'h2'=>$h2, 'h3'=>$h3, 'h4'=>$h4, 'h5'=>$h5, 'h6'=>$h6, 'h7'=>$h7, 'h8'=>$h8, 'h9'=>$h9, 'h10'=>$h10, 'h11'=>$h11, 'h12'=>$h12, 'h13'=>$h13, 'h14'=>$h14, 'h15'=>$h15, 'h16'=>$h16, 'h17'=>$h17, 'h18'=>$h18, 'h19'=>$h19, 'h20'=>$h20, 'h21'=>$h21, 'h22'=>$h22, 'h23'=>$h23, 'h24'=>$h24, 'h25'=>$h25, 'h26'=>$h26, 'h27'=>$h27, 'h28'=>$h28, 'h29'=>$h29, 'h30'=>$h30, 'h31'=>$h31
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
              $this->core->LogQuery('jadwal_pegawai => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('jadwal_pegawai')['delete'] == 'true') {
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
            $result = $this->core->db->delete('jadwal_pegawai', [
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
              $this->core->LogQuery('jadwal_pegawai => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('jadwal_pegawai')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_jadwal_pegawai= $_POST['search_field_jadwal_pegawai'];
            $search_text_jadwal_pegawai = $_POST['search_text_jadwal_pegawai'];

            if ($search_text_jadwal_pegawai != '') {
              $where[$search_field_jadwal_pegawai.'[~]'] = $search_text_jadwal_pegawai;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('jadwal_pegawai', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'id'=>$row['id'],
'tahun'=>$row['tahun'],
'bulan'=>$row['bulan'],
'h1'=>$row['h1'],
'h2'=>$row['h2'],
'h3'=>$row['h3'],
'h4'=>$row['h4'],
'h5'=>$row['h5'],
'h6'=>$row['h6'],
'h7'=>$row['h7'],
'h8'=>$row['h8'],
'h9'=>$row['h9'],
'h10'=>$row['h10'],
'h11'=>$row['h11'],
'h12'=>$row['h12'],
'h13'=>$row['h13'],
'h14'=>$row['h14'],
'h15'=>$row['h15'],
'h16'=>$row['h16'],
'h17'=>$row['h17'],
'h18'=>$row['h18'],
'h19'=>$row['h19'],
'h20'=>$row['h20'],
'h21'=>$row['h21'],
'h22'=>$row['h22'],
'h23'=>$row['h23'],
'h24'=>$row['h24'],
'h25'=>$row['h25'],
'h26'=>$row['h26'],
'h27'=>$row['h27'],
'h28'=>$row['h28'],
'h29'=>$row['h29'],
'h30'=>$row['h30'],
'h31'=>$row['h31']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('jadwal_pegawai => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($id)
    {

        if($this->core->loadDisabledMenu('jadwal_pegawai')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('jadwal_pegawai', '*', ['id' => $id]);

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
          $this->core->LogQuery('jadwal_pegawai => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($id)
    {

        if($this->core->loadDisabledMenu('jadwal_pegawai')['read'] == 'true') {
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
          $this->core->LogQuery('jadwal_pegawai => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'id' => $id]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('jadwal_pegawai', 'tahun', ['GROUP' => 'tahun']);
      $datasets = $this->core->db->select('jadwal_pegawai', ['count' => \Medoo\Medoo::raw('COUNT(<tahun>)')], ['GROUP' => 'tahun']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('jadwal_pegawai', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('jadwal_pegawai', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'jadwal_pegawai';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('jadwal_pegawai => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/jadwal_pegawai/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/jadwal_pegawai/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('jadwal_pegawai')]);
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

        $this->core->addCSS(url([ 'jadwal_pegawai', 'css']));
        $this->core->addJS(url([ 'jadwal_pegawai', 'javascript']), 'footer');
    }

}
