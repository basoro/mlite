<?php
namespace Plugins\Aturan_Pakai;

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
        $disabled_menu = $this->core->loadDisabledMenu('aturan_pakai'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'tgl_perawatan');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_aturan_pakai= isset_or($_POST['search_field_aturan_pakai']);
        $search_text_aturan_pakai = isset_or($_POST['search_text_aturan_pakai']);

        if ($search_text_aturan_pakai != '') {
          $where[$search_field_aturan_pakai.'[~]'] = $search_text_aturan_pakai;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('aturan_pakai', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('aturan_pakai', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('aturan_pakai', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'tgl_perawatan'=>$row['tgl_perawatan'],
'jam'=>$row['jam'],
'no_rawat'=>$row['no_rawat'],
'kode_brng'=>$row['kode_brng'],
'aturan'=>$row['aturan']

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
          $this->core->LogQuery('aturan_pakai => postData');
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

            if($this->core->loadDisabledMenu('aturan_pakai')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $tgl_perawatan = $_POST['tgl_perawatan'];
$jam = $_POST['jam'];
$no_rawat = $_POST['no_rawat'];
$kode_brng = $_POST['kode_brng'];
$aturan = $_POST['aturan'];

            
            $result = $this->core->db->insert('aturan_pakai', [
'tgl_perawatan'=>$tgl_perawatan, 'jam'=>$jam, 'no_rawat'=>$no_rawat, 'kode_brng'=>$kode_brng, 'aturan'=>$aturan
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
              $this->core->LogQuery('aturan_pakai => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('aturan_pakai')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $tgl_perawatan = $_POST['tgl_perawatan'];
$jam = $_POST['jam'];
$no_rawat = $_POST['no_rawat'];
$kode_brng = $_POST['kode_brng'];
$aturan = $_POST['aturan'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('aturan_pakai', [
'tgl_perawatan'=>$tgl_perawatan, 'jam'=>$jam, 'no_rawat'=>$no_rawat, 'kode_brng'=>$kode_brng, 'aturan'=>$aturan
            ], [
              'tgl_perawatan'=>$tgl_perawatan
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
              $this->core->LogQuery('aturan_pakai => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('aturan_pakai')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $tgl_perawatan= $_POST['tgl_perawatan'];
            $result = $this->core->db->delete('aturan_pakai', [
              'AND' => [
                'tgl_perawatan'=>$tgl_perawatan
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
              $this->core->LogQuery('aturan_pakai => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('aturan_pakai')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_aturan_pakai= $_POST['search_field_aturan_pakai'];
            $search_text_aturan_pakai = $_POST['search_text_aturan_pakai'];

            if ($search_text_aturan_pakai != '') {
              $where[$search_field_aturan_pakai.'[~]'] = $search_text_aturan_pakai;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('aturan_pakai', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'tgl_perawatan'=>$row['tgl_perawatan'],
'jam'=>$row['jam'],
'no_rawat'=>$row['no_rawat'],
'kode_brng'=>$row['kode_brng'],
'aturan'=>$row['aturan']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('aturan_pakai => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($tgl_perawatan)
    {

        if($this->core->loadDisabledMenu('aturan_pakai')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('aturan_pakai', '*', ['tgl_perawatan' => $tgl_perawatan]);

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
          $this->core->LogQuery('aturan_pakai => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($tgl_perawatan)
    {

        if($this->core->loadDisabledMenu('aturan_pakai')['read'] == 'true') {
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
          $this->core->LogQuery('aturan_pakai => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'tgl_perawatan' => $tgl_perawatan]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('aturan_pakai', 'aturan', ['GROUP' => 'aturan']);
      $datasets = $this->core->db->select('aturan_pakai', ['count' => \Medoo\Medoo::raw('COUNT(<aturan>)')], ['GROUP' => 'aturan']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('aturan_pakai', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('aturan_pakai', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'aturan_pakai';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('aturan_pakai => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/aturan_pakai/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/aturan_pakai/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('aturan_pakai')]);
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

        $this->core->addCSS(url([ 'aturan_pakai', 'css']));
        $this->core->addJS(url([ 'aturan_pakai', 'javascript']), 'footer');
    }

}
