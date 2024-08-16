<?php
namespace Plugins\Jadwal;

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
        $disabled_menu = $this->core->loadDisabledMenu('jadwal'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kd_dokter');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_jadwal= isset_or($_POST['search_field_jadwal']);
        $search_text_jadwal = isset_or($_POST['search_text_jadwal']);

        if ($search_text_jadwal != '') {
          $where[$search_field_jadwal.'[~]'] = $search_text_jadwal;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('jadwal', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('jadwal', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('jadwal', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kd_dokter'=>$row['kd_dokter'],
'hari_kerja'=>$row['hari_kerja'],
'jam_mulai'=>$row['jam_mulai'],
'jam_selesai'=>$row['jam_selesai'],
'kd_poli'=>$row['kd_poli'],
'kuota'=>$row['kuota']

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
          $this->core->LogQuery('jadwal => postData');
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

            if($this->core->loadDisabledMenu('jadwal')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kd_dokter = $_POST['kd_dokter'];
$hari_kerja = $_POST['hari_kerja'];
$jam_mulai = $_POST['jam_mulai'];
$jam_selesai = $_POST['jam_selesai'];
$kd_poli = $_POST['kd_poli'];
$kuota = $_POST['kuota'];

            
            $result = $this->core->db->insert('jadwal', [
'kd_dokter'=>$kd_dokter, 'hari_kerja'=>$hari_kerja, 'jam_mulai'=>$jam_mulai, 'jam_selesai'=>$jam_selesai, 'kd_poli'=>$kd_poli, 'kuota'=>$kuota
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
              $this->core->LogQuery('jadwal => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('jadwal')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kd_dokter = $_POST['kd_dokter'];
$hari_kerja = $_POST['hari_kerja'];
$jam_mulai = $_POST['jam_mulai'];
$jam_selesai = $_POST['jam_selesai'];
$kd_poli = $_POST['kd_poli'];
$kuota = $_POST['kuota'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('jadwal', [
'kd_dokter'=>$kd_dokter, 'hari_kerja'=>$hari_kerja, 'jam_mulai'=>$jam_mulai, 'jam_selesai'=>$jam_selesai, 'kd_poli'=>$kd_poli, 'kuota'=>$kuota
            ], [
              'kd_dokter'=>$kd_dokter
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
              $this->core->LogQuery('jadwal => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('jadwal')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_dokter= $_POST['kd_dokter'];
            $result = $this->core->db->delete('jadwal', [
              'AND' => [
                'kd_dokter'=>$kd_dokter
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
              $this->core->LogQuery('jadwal => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('jadwal')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_jadwal= $_POST['search_field_jadwal'];
            $search_text_jadwal = $_POST['search_text_jadwal'];

            if ($search_text_jadwal != '') {
              $where[$search_field_jadwal.'[~]'] = $search_text_jadwal;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('jadwal', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kd_dokter'=>$row['kd_dokter'],
'hari_kerja'=>$row['hari_kerja'],
'jam_mulai'=>$row['jam_mulai'],
'jam_selesai'=>$row['jam_selesai'],
'kd_poli'=>$row['kd_poli'],
'kuota'=>$row['kuota']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('jadwal => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kd_dokter)
    {

        if($this->core->loadDisabledMenu('jadwal')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('jadwal', '*', ['kd_dokter' => $kd_dokter]);

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
          $this->core->LogQuery('jadwal => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kd_dokter)
    {

        if($this->core->loadDisabledMenu('jadwal')['read'] == 'true') {
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
          $this->core->LogQuery('jadwal => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kd_dokter' => $kd_dokter]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('jadwal', 'kd_poli', ['GROUP' => 'kd_poli']);
      $datasets = $this->core->db->select('jadwal', ['count' => \Medoo\Medoo::raw('COUNT(<kd_poli>)')], ['GROUP' => 'kd_poli']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('jadwal', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('jadwal', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'jadwal';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('jadwal => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/jadwal/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/jadwal/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('jadwal')]);
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

        $this->core->addCSS(url([ 'jadwal', 'css']));
        $this->core->addJS(url([ 'jadwal', 'javascript']), 'footer');
    }

}
