<?php
namespace Plugins\Kamar;

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
        $this->assign['bangsal'] = $this->core->db->select('bangsal', '*', ['status' => '1']);
        $this->assign['status'] = $this->core->getEnum('kamar', 'status');
        $this->assign['kelas'] = $this->core->getEnum('kamar', 'kelas');
        $this->assign['status'] = $this->core->getEnum('kamar', 'status');
        $disabled_menu = $this->core->loadDisabledMenu('kamar'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['kamar' => $this->assign, 'disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kd_kamar');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_kamar= isset_or($_POST['search_field_kamar']);
        $search_text_kamar = isset_or($_POST['search_text_kamar']);

        if ($search_text_kamar != '') {
          $where[$search_field_kamar.'[~]'] = $search_text_kamar;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('kamar', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('kamar', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('kamar', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kd_kamar'=>$row['kd_kamar'],
                'kd_bangsal'=>$row['kd_bangsal'],
                'nm_bangsal'=>$this->core->db->get('bangsal', 'nm_bangsal', ['kd_bangsal' => $row['kd_bangsal']]), 
                'trf_kamar'=>$row['trf_kamar'],
                'status'=>$row['status'],
                'kelas'=>$row['kelas'],
                'statusdata'=>$row['statusdata']

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
          $this->core->LogQuery('kamar => postData');
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

            if($this->core->loadDisabledMenu('kamar')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_kamar = $_POST['kd_kamar'];
            $kd_bangsal = $_POST['kd_bangsal'];
            $trf_kamar = $_POST['trf_kamar'];
            $status = $_POST['status'];
            $kelas = $_POST['kelas'];
            $statusdata = $_POST['statusdata'];

            
            $result = $this->core->db->insert('kamar', [
              'kd_kamar'=>$kd_kamar, 'kd_bangsal'=>$kd_bangsal, 'trf_kamar'=>$trf_kamar, 'status'=>$status, 'kelas'=>$kelas, 'statusdata'=>$statusdata
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
              $this->core->LogQuery('kamar => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('kamar')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_kamar = $_POST['kd_kamar'];
            $kd_bangsal = $_POST['kd_bangsal'];
            $trf_kamar = $_POST['trf_kamar'];
            $status = $_POST['status'];
            $kelas = $_POST['kelas'];
            $statusdata = $_POST['statusdata'];


            // BUANG FIELD PERTAMA

            $result = $this->core->db->update('kamar', [
              'kd_kamar'=>$kd_kamar, 'kd_bangsal'=>$kd_bangsal, 'trf_kamar'=>$trf_kamar, 'status'=>$status, 'kelas'=>$kelas, 'statusdata'=>$statusdata
            ], [
              'kd_kamar'=>$kd_kamar
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
              $this->core->LogQuery('kamar => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('kamar')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_kamar= $_POST['kd_kamar'];
            $result = $this->core->db->delete('kamar', [
              'AND' => [
                'kd_kamar'=>$kd_kamar
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
              $this->core->LogQuery('kamar => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('kamar')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_kamar= $_POST['search_field_kamar'];
            $search_text_kamar = $_POST['search_text_kamar'];

            if ($search_text_kamar != '') {
              $where[$search_field_kamar.'[~]'] = $search_text_kamar;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('kamar', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kd_kamar'=>$row['kd_kamar'],
                    'kd_bangsal'=>$row['kd_bangsal'],
                    'trf_kamar'=>$row['trf_kamar'],
                    'status'=>$row['status'],
                    'kelas'=>$row['kelas'],
                    'statusdata'=>$row['statusdata']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('kamar => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kd_kamar)
    {

        if($this->core->loadDisabledMenu('kamar')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('kamar', '*', ['kd_kamar' => $kd_kamar]);
        $result['nm_bangsal'] = $this->core->db->get('bangsal', 'nm_bangsal', ['kd_bangsal' => $result['kd_bangsal']]);

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
          $this->core->LogQuery('kamar => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kd_kamar)
    {

        if($this->core->loadDisabledMenu('kamar')['read'] == 'true') {
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
          $this->core->LogQuery('kamar => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kd_kamar' => $kd_kamar]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('kamar', 'kelas', ['GROUP' => 'kelas']);
      $datasets = $this->core->db->select('kamar', ['count' => \Medoo\Medoo::raw('COUNT(<kelas>)')], ['GROUP' => 'kelas']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('kamar', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('kamar', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'kamar';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('kamar => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/kamar/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/kamar/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('kamar')]);
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

        $this->core->addCSS(url([ 'kamar', 'css']));
        $this->core->addJS(url([ 'kamar', 'javascript']), 'footer');
    }

}
