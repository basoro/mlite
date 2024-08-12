<?php
namespace Plugins\Poliklinik;

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
        $disabled_menu = $this->core->loadDisabledMenu('poliklinik'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kd_poli');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_poliklinik= isset_or($_POST['search_field_poliklinik']);
        $search_text_poliklinik = isset_or($_POST['search_text_poliklinik']);

        if ($search_text_poliklinik != '') {
          $where[$search_field_poliklinik.'[~]'] = $search_text_poliklinik;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('poliklinik', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('poliklinik', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('poliklinik', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kd_poli'=>$row['kd_poli'],
                'nm_poli'=>$row['nm_poli'],
                'registrasi'=>$row['registrasi'],
                'registrasilama'=>$row['registrasilama'],
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
          $this->core->LogQuery('poliklinik => postData');
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

            if($this->core->loadDisabledMenu('poliklinik')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_poli = $_POST['kd_poli'];
            $nm_poli = $_POST['nm_poli'];
            $registrasi = $_POST['registrasi'];
            $registrasilama = $_POST['registrasilama'];
            $status = $_POST['status'];

            
            $result = $this->core->db->insert('poliklinik', [
              'kd_poli'=>$kd_poli, 'nm_poli'=>$nm_poli, 'registrasi'=>$registrasi, 'registrasilama'=>$registrasilama, 'status'=>$status
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
              $this->core->LogQuery('poliklinik => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('poliklinik')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_poli = $_POST['kd_poli'];
            $nm_poli = $_POST['nm_poli'];
            $registrasi = $_POST['registrasi'];
            $registrasilama = $_POST['registrasilama'];
            $status = $_POST['status'];

            // BUANG FIELD PERTAMA

            $result = $this->core->db->update('poliklinik', [
              'nm_poli'=>$nm_poli, 'registrasi'=>$registrasi, 'registrasilama'=>$registrasilama, 'status'=>$status
            ], [
              'kd_poli'=>$kd_poli
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
              $this->core->LogQuery('poliklinik => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('poliklinik')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_poli= $_POST['kd_poli'];
            $result = $this->core->db->delete('poliklinik', [
              'AND' => [
                'kd_poli'=>$kd_poli
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
              $this->core->LogQuery('poliklinik => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('poliklinik')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_poliklinik= $_POST['search_field_poliklinik'];
            $search_text_poliklinik = $_POST['search_text_poliklinik'];

            if ($search_text_poliklinik != '') {
              $where[$search_field_poliklinik.'[~]'] = $search_text_poliklinik;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('poliklinik', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kd_poli'=>$row['kd_poli'],
                    'nm_poli'=>$row['nm_poli'],
                    'registrasi'=>$row['registrasi'],
                    'registrasilama'=>$row['registrasilama'],
                    'status'=>$row['status']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('poliklinik => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kd_poli)
    {

        if($this->core->loadDisabledMenu('poliklinik')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('poliklinik', '*', ['kd_poli' => $kd_poli]);

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
          $this->core->LogQuery('poliklinik => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kd_poli)
    {

        if($this->core->loadDisabledMenu('poliklinik')['read'] == 'true') {
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
          $this->core->LogQuery('poliklinik => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kd_poli' => $kd_poli]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('poliklinik', 'status', ['GROUP' => 'status']);
      $datasets = $this->core->db->select('poliklinik', ['count' => \Medoo\Medoo::raw('COUNT(<status>)')], ['GROUP' => 'status']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('poliklinik', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('poliklinik', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'poliklinik';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('poliklinik => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/poliklinik/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/poliklinik/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('poliklinik')]);
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

        $this->core->addCSS(url([ 'poliklinik', 'css']));
        $this->core->addJS(url([ 'poliklinik', 'javascript']), 'footer');
    }

}
