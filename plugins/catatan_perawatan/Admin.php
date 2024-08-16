<?php
namespace Plugins\Catatan_Perawatan;

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
        $disabled_menu = $this->core->loadDisabledMenu('catatan_perawatan'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'tanggal');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_catatan_perawatan= isset_or($_POST['search_field_catatan_perawatan']);
        $search_text_catatan_perawatan = isset_or($_POST['search_text_catatan_perawatan']);

        if ($search_text_catatan_perawatan != '') {
          $where[$search_field_catatan_perawatan.'[~]'] = $search_text_catatan_perawatan;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('catatan_perawatan', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('catatan_perawatan', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('catatan_perawatan', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'tanggal'=>$row['tanggal'],
'jam'=>$row['jam'],
'no_rawat'=>$row['no_rawat'],
'kd_dokter'=>$row['kd_dokter'],
'catatan'=>$row['catatan']

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
          $this->core->LogQuery('catatan_perawatan => postData');
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

            if($this->core->loadDisabledMenu('catatan_perawatan')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $tanggal = $_POST['tanggal'];
$jam = $_POST['jam'];
$no_rawat = $_POST['no_rawat'];
$kd_dokter = $_POST['kd_dokter'];
$catatan = $_POST['catatan'];

            
            $result = $this->core->db->insert('catatan_perawatan', [
'tanggal'=>$tanggal, 'jam'=>$jam, 'no_rawat'=>$no_rawat, 'kd_dokter'=>$kd_dokter, 'catatan'=>$catatan
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
              $this->core->LogQuery('catatan_perawatan => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('catatan_perawatan')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $tanggal = $_POST['tanggal'];
$jam = $_POST['jam'];
$no_rawat = $_POST['no_rawat'];
$kd_dokter = $_POST['kd_dokter'];
$catatan = $_POST['catatan'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('catatan_perawatan', [
'tanggal'=>$tanggal, 'jam'=>$jam, 'no_rawat'=>$no_rawat, 'kd_dokter'=>$kd_dokter, 'catatan'=>$catatan
            ], [
              'tanggal'=>$tanggal
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
              $this->core->LogQuery('catatan_perawatan => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('catatan_perawatan')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $tanggal= $_POST['tanggal'];
            $result = $this->core->db->delete('catatan_perawatan', [
              'AND' => [
                'tanggal'=>$tanggal
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
              $this->core->LogQuery('catatan_perawatan => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('catatan_perawatan')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_catatan_perawatan= $_POST['search_field_catatan_perawatan'];
            $search_text_catatan_perawatan = $_POST['search_text_catatan_perawatan'];

            if ($search_text_catatan_perawatan != '') {
              $where[$search_field_catatan_perawatan.'[~]'] = $search_text_catatan_perawatan;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('catatan_perawatan', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'tanggal'=>$row['tanggal'],
'jam'=>$row['jam'],
'no_rawat'=>$row['no_rawat'],
'kd_dokter'=>$row['kd_dokter'],
'catatan'=>$row['catatan']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('catatan_perawatan => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($tanggal)
    {

        if($this->core->loadDisabledMenu('catatan_perawatan')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('catatan_perawatan', '*', ['tanggal' => $tanggal]);

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
          $this->core->LogQuery('catatan_perawatan => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($tanggal)
    {

        if($this->core->loadDisabledMenu('catatan_perawatan')['read'] == 'true') {
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
          $this->core->LogQuery('catatan_perawatan => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'tanggal' => $tanggal]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('catatan_perawatan', 'kd_dokter', ['GROUP' => 'kd_dokter']);
      $datasets = $this->core->db->select('catatan_perawatan', ['count' => \Medoo\Medoo::raw('COUNT(<kd_dokter>)')], ['GROUP' => 'kd_dokter']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('catatan_perawatan', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('catatan_perawatan', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'catatan_perawatan';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('catatan_perawatan => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/catatan_perawatan/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/catatan_perawatan/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('catatan_perawatan')]);
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

        $this->core->addCSS(url([ 'catatan_perawatan', 'css']));
        $this->core->addJS(url([ 'catatan_perawatan', 'javascript']), 'footer');
    }

}
