<?php
namespace Plugins\Obat_Racikan;

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
        $disabled_menu = $this->core->loadDisabledMenu('obat_racikan'); 
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
        $search_field_obat_racikan= isset_or($_POST['search_field_obat_racikan']);
        $search_text_obat_racikan = isset_or($_POST['search_text_obat_racikan']);

        if ($search_text_obat_racikan != '') {
          $where[$search_field_obat_racikan.'[~]'] = $search_text_obat_racikan;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('obat_racikan', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('obat_racikan', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('obat_racikan', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'tgl_perawatan'=>$row['tgl_perawatan'],
'jam'=>$row['jam'],
'no_rawat'=>$row['no_rawat'],
'no_racik'=>$row['no_racik'],
'nama_racik'=>$row['nama_racik'],
'kd_racik'=>$row['kd_racik'],
'jml_dr'=>$row['jml_dr'],
'aturan_pakai'=>$row['aturan_pakai'],
'keterangan'=>$row['keterangan']

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
          $this->core->LogQuery('obat_racikan => postData');
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

            if($this->core->loadDisabledMenu('obat_racikan')['create'] == 'true') {
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
$no_racik = $_POST['no_racik'];
$nama_racik = $_POST['nama_racik'];
$kd_racik = $_POST['kd_racik'];
$jml_dr = $_POST['jml_dr'];
$aturan_pakai = $_POST['aturan_pakai'];
$keterangan = $_POST['keterangan'];

            
            $result = $this->core->db->insert('obat_racikan', [
'tgl_perawatan'=>$tgl_perawatan, 'jam'=>$jam, 'no_rawat'=>$no_rawat, 'no_racik'=>$no_racik, 'nama_racik'=>$nama_racik, 'kd_racik'=>$kd_racik, 'jml_dr'=>$jml_dr, 'aturan_pakai'=>$aturan_pakai, 'keterangan'=>$keterangan
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
              $this->core->LogQuery('obat_racikan => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('obat_racikan')['update'] == 'true') {
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
$no_racik = $_POST['no_racik'];
$nama_racik = $_POST['nama_racik'];
$kd_racik = $_POST['kd_racik'];
$jml_dr = $_POST['jml_dr'];
$aturan_pakai = $_POST['aturan_pakai'];
$keterangan = $_POST['keterangan'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('obat_racikan', [
'tgl_perawatan'=>$tgl_perawatan, 'jam'=>$jam, 'no_rawat'=>$no_rawat, 'no_racik'=>$no_racik, 'nama_racik'=>$nama_racik, 'kd_racik'=>$kd_racik, 'jml_dr'=>$jml_dr, 'aturan_pakai'=>$aturan_pakai, 'keterangan'=>$keterangan
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
              $this->core->LogQuery('obat_racikan => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('obat_racikan')['delete'] == 'true') {
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
            $result = $this->core->db->delete('obat_racikan', [
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
              $this->core->LogQuery('obat_racikan => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('obat_racikan')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_obat_racikan= $_POST['search_field_obat_racikan'];
            $search_text_obat_racikan = $_POST['search_text_obat_racikan'];

            if ($search_text_obat_racikan != '') {
              $where[$search_field_obat_racikan.'[~]'] = $search_text_obat_racikan;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('obat_racikan', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'tgl_perawatan'=>$row['tgl_perawatan'],
'jam'=>$row['jam'],
'no_rawat'=>$row['no_rawat'],
'no_racik'=>$row['no_racik'],
'nama_racik'=>$row['nama_racik'],
'kd_racik'=>$row['kd_racik'],
'jml_dr'=>$row['jml_dr'],
'aturan_pakai'=>$row['aturan_pakai'],
'keterangan'=>$row['keterangan']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('obat_racikan => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($tgl_perawatan)
    {

        if($this->core->loadDisabledMenu('obat_racikan')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('obat_racikan', '*', ['tgl_perawatan' => $tgl_perawatan]);

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
          $this->core->LogQuery('obat_racikan => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($tgl_perawatan)
    {

        if($this->core->loadDisabledMenu('obat_racikan')['read'] == 'true') {
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
          $this->core->LogQuery('obat_racikan => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'tgl_perawatan' => $tgl_perawatan]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('obat_racikan', 'kd_racik', ['GROUP' => 'kd_racik']);
      $datasets = $this->core->db->select('obat_racikan', ['count' => \Medoo\Medoo::raw('COUNT(<kd_racik>)')], ['GROUP' => 'kd_racik']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('obat_racikan', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('obat_racikan', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'obat_racikan';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('obat_racikan => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/obat_racikan/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/obat_racikan/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('obat_racikan')]);
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

        $this->core->addCSS(url([ 'obat_racikan', 'css']));
        $this->core->addJS(url([ 'obat_racikan', 'javascript']), 'footer');
    }

}
