<?php
namespace Plugins\Perusahaan_Pasien;

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
        $disabled_menu = $this->core->loadDisabledMenu('perusahaan_pasien'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kode_perusahaan');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_perusahaan_pasien= isset_or($_POST['search_field_perusahaan_pasien']);
        $search_text_perusahaan_pasien = isset_or($_POST['search_text_perusahaan_pasien']);

        if ($search_text_perusahaan_pasien != '') {
          $where[$search_field_perusahaan_pasien.'[~]'] = $search_text_perusahaan_pasien;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('perusahaan_pasien', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('perusahaan_pasien', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('perusahaan_pasien', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kode_perusahaan'=>$row['kode_perusahaan'],
                'nama_perusahaan'=>$row['nama_perusahaan'],
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
          $this->core->LogQuery('perusahaan_pasien => postData');
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

            if($this->core->loadDisabledMenu('perusahaan_pasien')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kode_perusahaan = $_POST['kode_perusahaan'];
            $nama_perusahaan = $_POST['nama_perusahaan'];
            $alamat = $_POST['alamat'];
            $kota = $_POST['kota'];
            $no_telp = $_POST['no_telp'];
            
            $result = $this->core->db->insert('perusahaan_pasien', [
              'kode_perusahaan'=>$kode_perusahaan, 'nama_perusahaan'=>$nama_perusahaan, 'alamat'=>$alamat, 'kota'=>$kota, 'no_telp'=>$no_telp
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
              $this->core->LogQuery('perusahaan_pasien => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('perusahaan_pasien')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kode_perusahaan = $_POST['kode_perusahaan'];
            $nama_perusahaan = $_POST['nama_perusahaan'];
            $alamat = $_POST['alamat'];
            $kota = $_POST['kota'];
            $no_telp = $_POST['no_telp'];

            // BUANG FIELD PERTAMA

            $result = $this->core->db->update('perusahaan_pasien', [
              'nama_perusahaan'=>$nama_perusahaan, 'alamat'=>$alamat, 'kota'=>$kota, 'no_telp'=>$no_telp
            ], [
              'kode_perusahaan'=>$kode_perusahaan
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
              $this->core->LogQuery('perusahaan_pasien => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('perusahaan_pasien')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kode_perusahaan= $_POST['kode_perusahaan'];
            $result = $this->core->db->delete('perusahaan_pasien', [
              'AND' => [
                'kode_perusahaan'=>$kode_perusahaan
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
              $this->core->LogQuery('perusahaan_pasien => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('perusahaan_pasien')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_perusahaan_pasien= $_POST['search_field_perusahaan_pasien'];
            $search_text_perusahaan_pasien = $_POST['search_text_perusahaan_pasien'];

            if ($search_text_perusahaan_pasien != '') {
              $where[$search_field_perusahaan_pasien.'[~]'] = $search_text_perusahaan_pasien;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('perusahaan_pasien', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kode_perusahaan'=>$row['kode_perusahaan'],
                    'nama_perusahaan'=>$row['nama_perusahaan'],
                    'alamat'=>$row['alamat'],
                    'kota'=>$row['kota'],
                    'no_telp'=>$row['no_telp']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('perusahaan_pasien => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kode_perusahaan)
    {

        if($this->core->loadDisabledMenu('perusahaan_pasien')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('perusahaan_pasien', '*', ['kode_perusahaan' => $kode_perusahaan]);

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
          $this->core->LogQuery('perusahaan_pasien => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kode_perusahaan)
    {

        if($this->core->loadDisabledMenu('perusahaan_pasien')['read'] == 'true') {
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
          $this->core->LogQuery('perusahaan_pasien => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kode_perusahaan' => $kode_perusahaan]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('perusahaan_pasien', 'kota', ['GROUP' => 'kota']);
      $datasets = $this->core->db->select('perusahaan_pasien', ['count' => \Medoo\Medoo::raw('COUNT(<kota>)')], ['GROUP' => 'kota']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('perusahaan_pasien', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('perusahaan_pasien', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'perusahaan_pasien';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('perusahaan_pasien => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/perusahaan_pasien/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/perusahaan_pasien/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('perusahaan_pasien')]);
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

        $this->core->addCSS(url([ 'perusahaan_pasien', 'css']));
        $this->core->addJS(url([ 'perusahaan_pasien', 'javascript']), 'footer');
    }

}
