<?php
namespace Plugins\Kelompok_Jabatan;

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
        $disabled_menu = $this->core->loadDisabledMenu('kelompok_jabatan'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kode_kelompok');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_kelompok_jabatan= isset_or($_POST['search_field_kelompok_jabatan']);
        $search_text_kelompok_jabatan = isset_or($_POST['search_text_kelompok_jabatan']);

        if ($search_text_kelompok_jabatan != '') {
          $where[$search_field_kelompok_jabatan.'[~]'] = $search_text_kelompok_jabatan;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('kelompok_jabatan', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('kelompok_jabatan', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('kelompok_jabatan', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kode_kelompok'=>$row['kode_kelompok'],
'nama_kelompok'=>$row['nama_kelompok'],
'indek'=>$row['indek']

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
          $this->core->LogQuery('kelompok_jabatan => postData');
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

            if($this->core->loadDisabledMenu('kelompok_jabatan')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kode_kelompok = $_POST['kode_kelompok'];
$nama_kelompok = $_POST['nama_kelompok'];
$indek = $_POST['indek'];

            
            $result = $this->core->db->insert('kelompok_jabatan', [
'kode_kelompok'=>$kode_kelompok, 'nama_kelompok'=>$nama_kelompok, 'indek'=>$indek
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
              $this->core->LogQuery('kelompok_jabatan => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('kelompok_jabatan')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kode_kelompok = $_POST['kode_kelompok'];
$nama_kelompok = $_POST['nama_kelompok'];
$indek = $_POST['indek'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('kelompok_jabatan', [
'kode_kelompok'=>$kode_kelompok, 'nama_kelompok'=>$nama_kelompok, 'indek'=>$indek
            ], [
              'kode_kelompok'=>$kode_kelompok
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
              $this->core->LogQuery('kelompok_jabatan => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('kelompok_jabatan')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kode_kelompok= $_POST['kode_kelompok'];
            $result = $this->core->db->delete('kelompok_jabatan', [
              'AND' => [
                'kode_kelompok'=>$kode_kelompok
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
              $this->core->LogQuery('kelompok_jabatan => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('kelompok_jabatan')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_kelompok_jabatan= $_POST['search_field_kelompok_jabatan'];
            $search_text_kelompok_jabatan = $_POST['search_text_kelompok_jabatan'];

            if ($search_text_kelompok_jabatan != '') {
              $where[$search_field_kelompok_jabatan.'[~]'] = $search_text_kelompok_jabatan;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('kelompok_jabatan', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kode_kelompok'=>$row['kode_kelompok'],
'nama_kelompok'=>$row['nama_kelompok'],
'indek'=>$row['indek']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('kelompok_jabatan => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kode_kelompok)
    {

        if($this->core->loadDisabledMenu('kelompok_jabatan')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('kelompok_jabatan', '*', ['kode_kelompok' => $kode_kelompok]);

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
          $this->core->LogQuery('kelompok_jabatan => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kode_kelompok)
    {

        if($this->core->loadDisabledMenu('kelompok_jabatan')['read'] == 'true') {
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
          $this->core->LogQuery('kelompok_jabatan => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kode_kelompok' => $kode_kelompok]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('kelompok_jabatan', 'nama_kelompok', ['GROUP' => 'nama_kelompok']);
      $datasets = $this->core->db->select('kelompok_jabatan', ['count' => \Medoo\Medoo::raw('COUNT(<nama_kelompok>)')], ['GROUP' => 'nama_kelompok']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('kelompok_jabatan', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('kelompok_jabatan', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'kelompok_jabatan';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('kelompok_jabatan => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/kelompok_jabatan/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/kelompok_jabatan/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('kelompok_jabatan')]);
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

        $this->core->addCSS(url([ 'kelompok_jabatan', 'css']));
        $this->core->addJS(url([ 'kelompok_jabatan', 'javascript']), 'footer');
    }

}
