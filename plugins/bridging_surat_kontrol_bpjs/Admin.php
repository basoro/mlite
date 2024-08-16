<?php
namespace Plugins\Bridging_Surat_Kontrol_Bpjs;

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
        $disabled_menu = $this->core->loadDisabledMenu('bridging_surat_kontrol_bpjs'); 
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
        $search_field_bridging_surat_kontrol_bpjs= isset_or($_POST['search_field_bridging_surat_kontrol_bpjs']);
        $search_text_bridging_surat_kontrol_bpjs = isset_or($_POST['search_text_bridging_surat_kontrol_bpjs']);

        if ($search_text_bridging_surat_kontrol_bpjs != '') {
          $where[$search_field_bridging_surat_kontrol_bpjs.'[~]'] = $search_text_bridging_surat_kontrol_bpjs;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('bridging_surat_kontrol_bpjs', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('bridging_surat_kontrol_bpjs', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('bridging_surat_kontrol_bpjs', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_sep'=>$row['no_sep'],
'tgl_surat'=>$row['tgl_surat'],
'no_surat'=>$row['no_surat'],
'tgl_rencana'=>$row['tgl_rencana'],
'kd_dokter_bpjs'=>$row['kd_dokter_bpjs'],
'nm_dokter_bpjs'=>$row['nm_dokter_bpjs'],
'kd_poli_bpjs'=>$row['kd_poli_bpjs'],
'nm_poli_bpjs'=>$row['nm_poli_bpjs']

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
          $this->core->LogQuery('bridging_surat_kontrol_bpjs => postData');
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

            if($this->core->loadDisabledMenu('bridging_surat_kontrol_bpjs')['create'] == 'true') {
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
$tgl_surat = $_POST['tgl_surat'];
$no_surat = $_POST['no_surat'];
$tgl_rencana = $_POST['tgl_rencana'];
$kd_dokter_bpjs = $_POST['kd_dokter_bpjs'];
$nm_dokter_bpjs = $_POST['nm_dokter_bpjs'];
$kd_poli_bpjs = $_POST['kd_poli_bpjs'];
$nm_poli_bpjs = $_POST['nm_poli_bpjs'];

            
            $result = $this->core->db->insert('bridging_surat_kontrol_bpjs', [
'no_sep'=>$no_sep, 'tgl_surat'=>$tgl_surat, 'no_surat'=>$no_surat, 'tgl_rencana'=>$tgl_rencana, 'kd_dokter_bpjs'=>$kd_dokter_bpjs, 'nm_dokter_bpjs'=>$nm_dokter_bpjs, 'kd_poli_bpjs'=>$kd_poli_bpjs, 'nm_poli_bpjs'=>$nm_poli_bpjs
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
              $this->core->LogQuery('bridging_surat_kontrol_bpjs => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('bridging_surat_kontrol_bpjs')['update'] == 'true') {
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
$tgl_surat = $_POST['tgl_surat'];
$no_surat = $_POST['no_surat'];
$tgl_rencana = $_POST['tgl_rencana'];
$kd_dokter_bpjs = $_POST['kd_dokter_bpjs'];
$nm_dokter_bpjs = $_POST['nm_dokter_bpjs'];
$kd_poli_bpjs = $_POST['kd_poli_bpjs'];
$nm_poli_bpjs = $_POST['nm_poli_bpjs'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('bridging_surat_kontrol_bpjs', [
'no_sep'=>$no_sep, 'tgl_surat'=>$tgl_surat, 'no_surat'=>$no_surat, 'tgl_rencana'=>$tgl_rencana, 'kd_dokter_bpjs'=>$kd_dokter_bpjs, 'nm_dokter_bpjs'=>$nm_dokter_bpjs, 'kd_poli_bpjs'=>$kd_poli_bpjs, 'nm_poli_bpjs'=>$nm_poli_bpjs
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
              $this->core->LogQuery('bridging_surat_kontrol_bpjs => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('bridging_surat_kontrol_bpjs')['delete'] == 'true') {
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
            $result = $this->core->db->delete('bridging_surat_kontrol_bpjs', [
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
              $this->core->LogQuery('bridging_surat_kontrol_bpjs => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('bridging_surat_kontrol_bpjs')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_bridging_surat_kontrol_bpjs= $_POST['search_field_bridging_surat_kontrol_bpjs'];
            $search_text_bridging_surat_kontrol_bpjs = $_POST['search_text_bridging_surat_kontrol_bpjs'];

            if ($search_text_bridging_surat_kontrol_bpjs != '') {
              $where[$search_field_bridging_surat_kontrol_bpjs.'[~]'] = $search_text_bridging_surat_kontrol_bpjs;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('bridging_surat_kontrol_bpjs', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_sep'=>$row['no_sep'],
'tgl_surat'=>$row['tgl_surat'],
'no_surat'=>$row['no_surat'],
'tgl_rencana'=>$row['tgl_rencana'],
'kd_dokter_bpjs'=>$row['kd_dokter_bpjs'],
'nm_dokter_bpjs'=>$row['nm_dokter_bpjs'],
'kd_poli_bpjs'=>$row['kd_poli_bpjs'],
'nm_poli_bpjs'=>$row['nm_poli_bpjs']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('bridging_surat_kontrol_bpjs => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_sep)
    {

        if($this->core->loadDisabledMenu('bridging_surat_kontrol_bpjs')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('bridging_surat_kontrol_bpjs', '*', ['no_sep' => $no_sep]);

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
          $this->core->LogQuery('bridging_surat_kontrol_bpjs => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_sep)
    {

        if($this->core->loadDisabledMenu('bridging_surat_kontrol_bpjs')['read'] == 'true') {
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
          $this->core->LogQuery('bridging_surat_kontrol_bpjs => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_sep' => $no_sep]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('bridging_surat_kontrol_bpjs', 'kd_dokter_bpjs', ['GROUP' => 'kd_dokter_bpjs']);
      $datasets = $this->core->db->select('bridging_surat_kontrol_bpjs', ['count' => \Medoo\Medoo::raw('COUNT(<kd_dokter_bpjs>)')], ['GROUP' => 'kd_dokter_bpjs']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('bridging_surat_kontrol_bpjs', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('bridging_surat_kontrol_bpjs', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'bridging_surat_kontrol_bpjs';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('bridging_surat_kontrol_bpjs => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/bridging_surat_kontrol_bpjs/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/bridging_surat_kontrol_bpjs/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('bridging_surat_kontrol_bpjs')]);
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

        $this->core->addCSS(url([ 'bridging_surat_kontrol_bpjs', 'css']));
        $this->core->addJS(url([ 'bridging_surat_kontrol_bpjs', 'javascript']), 'footer');
    }

}
