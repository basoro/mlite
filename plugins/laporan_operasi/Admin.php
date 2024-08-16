<?php
namespace Plugins\Laporan_Operasi;

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
        $disabled_menu = $this->core->loadDisabledMenu('laporan_operasi'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'no_rawat');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_laporan_operasi= isset_or($_POST['search_field_laporan_operasi']);
        $search_text_laporan_operasi = isset_or($_POST['search_text_laporan_operasi']);

        if ($search_text_laporan_operasi != '') {
          $where[$search_field_laporan_operasi.'[~]'] = $search_text_laporan_operasi;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('laporan_operasi', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('laporan_operasi', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('laporan_operasi', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rawat'=>$row['no_rawat'],
'tanggal'=>$row['tanggal'],
'diagnosa_preop'=>$row['diagnosa_preop'],
'diagnosa_postop'=>$row['diagnosa_postop'],
'jaringan_dieksekusi'=>$row['jaringan_dieksekusi'],
'selesaioperasi'=>$row['selesaioperasi'],
'permintaan_pa'=>$row['permintaan_pa'],
'laporan_operasi'=>$row['laporan_operasi']

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
          $this->core->LogQuery('laporan_operasi => postData');
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

            if($this->core->loadDisabledMenu('laporan_operasi')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_rawat = $_POST['no_rawat'];
$tanggal = $_POST['tanggal'];
$diagnosa_preop = $_POST['diagnosa_preop'];
$diagnosa_postop = $_POST['diagnosa_postop'];
$jaringan_dieksekusi = $_POST['jaringan_dieksekusi'];
$selesaioperasi = $_POST['selesaioperasi'];
$permintaan_pa = $_POST['permintaan_pa'];
$laporan_operasi = $_POST['laporan_operasi'];

            
            $result = $this->core->db->insert('laporan_operasi', [
'no_rawat'=>$no_rawat, 'tanggal'=>$tanggal, 'diagnosa_preop'=>$diagnosa_preop, 'diagnosa_postop'=>$diagnosa_postop, 'jaringan_dieksekusi'=>$jaringan_dieksekusi, 'selesaioperasi'=>$selesaioperasi, 'permintaan_pa'=>$permintaan_pa, 'laporan_operasi'=>$laporan_operasi
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
              $this->core->LogQuery('laporan_operasi => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('laporan_operasi')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_rawat = $_POST['no_rawat'];
$tanggal = $_POST['tanggal'];
$diagnosa_preop = $_POST['diagnosa_preop'];
$diagnosa_postop = $_POST['diagnosa_postop'];
$jaringan_dieksekusi = $_POST['jaringan_dieksekusi'];
$selesaioperasi = $_POST['selesaioperasi'];
$permintaan_pa = $_POST['permintaan_pa'];
$laporan_operasi = $_POST['laporan_operasi'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('laporan_operasi', [
'no_rawat'=>$no_rawat, 'tanggal'=>$tanggal, 'diagnosa_preop'=>$diagnosa_preop, 'diagnosa_postop'=>$diagnosa_postop, 'jaringan_dieksekusi'=>$jaringan_dieksekusi, 'selesaioperasi'=>$selesaioperasi, 'permintaan_pa'=>$permintaan_pa, 'laporan_operasi'=>$laporan_operasi
            ], [
              'no_rawat'=>$no_rawat
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
              $this->core->LogQuery('laporan_operasi => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('laporan_operasi')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_rawat= $_POST['no_rawat'];
            $result = $this->core->db->delete('laporan_operasi', [
              'AND' => [
                'no_rawat'=>$no_rawat
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
              $this->core->LogQuery('laporan_operasi => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('laporan_operasi')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_laporan_operasi= $_POST['search_field_laporan_operasi'];
            $search_text_laporan_operasi = $_POST['search_text_laporan_operasi'];

            if ($search_text_laporan_operasi != '') {
              $where[$search_field_laporan_operasi.'[~]'] = $search_text_laporan_operasi;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('laporan_operasi', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_rawat'=>$row['no_rawat'],
'tanggal'=>$row['tanggal'],
'diagnosa_preop'=>$row['diagnosa_preop'],
'diagnosa_postop'=>$row['diagnosa_postop'],
'jaringan_dieksekusi'=>$row['jaringan_dieksekusi'],
'selesaioperasi'=>$row['selesaioperasi'],
'permintaan_pa'=>$row['permintaan_pa'],
'laporan_operasi'=>$row['laporan_operasi']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('laporan_operasi => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_rawat)
    {

        if($this->core->loadDisabledMenu('laporan_operasi')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('laporan_operasi', '*', ['no_rawat' => $no_rawat]);

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
          $this->core->LogQuery('laporan_operasi => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_rawat)
    {

        if($this->core->loadDisabledMenu('laporan_operasi')['read'] == 'true') {
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
          $this->core->LogQuery('laporan_operasi => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_rawat' => $no_rawat]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('laporan_operasi', 'diagnosa_preop', ['GROUP' => 'diagnosa_preop']);
      $datasets = $this->core->db->select('laporan_operasi', ['count' => \Medoo\Medoo::raw('COUNT(<diagnosa_preop>)')], ['GROUP' => 'diagnosa_preop']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('laporan_operasi', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('laporan_operasi', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'laporan_operasi';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('laporan_operasi => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/laporan_operasi/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/laporan_operasi/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('laporan_operasi')]);
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

        $this->core->addCSS(url([ 'laporan_operasi', 'css']));
        $this->core->addJS(url([ 'laporan_operasi', 'javascript']), 'footer');
    }

}
