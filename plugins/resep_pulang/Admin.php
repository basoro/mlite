<?php
namespace Plugins\Resep_Pulang;

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
        $disabled_menu = $this->core->loadDisabledMenu('resep_pulang'); 
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
        $search_field_resep_pulang= isset_or($_POST['search_field_resep_pulang']);
        $search_text_resep_pulang = isset_or($_POST['search_text_resep_pulang']);

        if ($search_text_resep_pulang != '') {
          $where[$search_field_resep_pulang.'[~]'] = $search_text_resep_pulang;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('resep_pulang', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('resep_pulang', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('resep_pulang', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rawat'=>$row['no_rawat'],
'kode_brng'=>$row['kode_brng'],
'jml_barang'=>$row['jml_barang'],
'harga'=>$row['harga'],
'total'=>$row['total'],
'dosis'=>$row['dosis'],
'tanggal'=>$row['tanggal'],
'jam'=>$row['jam'],
'kd_bangsal'=>$row['kd_bangsal'],
'no_batch'=>$row['no_batch'],
'no_faktur'=>$row['no_faktur']

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
          $this->core->LogQuery('resep_pulang => postData');
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

            if($this->core->loadDisabledMenu('resep_pulang')['create'] == 'true') {
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
$kode_brng = $_POST['kode_brng'];
$jml_barang = $_POST['jml_barang'];
$harga = $_POST['harga'];
$total = $_POST['total'];
$dosis = $_POST['dosis'];
$tanggal = $_POST['tanggal'];
$jam = $_POST['jam'];
$kd_bangsal = $_POST['kd_bangsal'];
$no_batch = $_POST['no_batch'];
$no_faktur = $_POST['no_faktur'];

            
            $result = $this->core->db->insert('resep_pulang', [
'no_rawat'=>$no_rawat, 'kode_brng'=>$kode_brng, 'jml_barang'=>$jml_barang, 'harga'=>$harga, 'total'=>$total, 'dosis'=>$dosis, 'tanggal'=>$tanggal, 'jam'=>$jam, 'kd_bangsal'=>$kd_bangsal, 'no_batch'=>$no_batch, 'no_faktur'=>$no_faktur
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
              $this->core->LogQuery('resep_pulang => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('resep_pulang')['update'] == 'true') {
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
$kode_brng = $_POST['kode_brng'];
$jml_barang = $_POST['jml_barang'];
$harga = $_POST['harga'];
$total = $_POST['total'];
$dosis = $_POST['dosis'];
$tanggal = $_POST['tanggal'];
$jam = $_POST['jam'];
$kd_bangsal = $_POST['kd_bangsal'];
$no_batch = $_POST['no_batch'];
$no_faktur = $_POST['no_faktur'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('resep_pulang', [
'no_rawat'=>$no_rawat, 'kode_brng'=>$kode_brng, 'jml_barang'=>$jml_barang, 'harga'=>$harga, 'total'=>$total, 'dosis'=>$dosis, 'tanggal'=>$tanggal, 'jam'=>$jam, 'kd_bangsal'=>$kd_bangsal, 'no_batch'=>$no_batch, 'no_faktur'=>$no_faktur
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
              $this->core->LogQuery('resep_pulang => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('resep_pulang')['delete'] == 'true') {
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
            $result = $this->core->db->delete('resep_pulang', [
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
              $this->core->LogQuery('resep_pulang => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('resep_pulang')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_resep_pulang= $_POST['search_field_resep_pulang'];
            $search_text_resep_pulang = $_POST['search_text_resep_pulang'];

            if ($search_text_resep_pulang != '') {
              $where[$search_field_resep_pulang.'[~]'] = $search_text_resep_pulang;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('resep_pulang', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_rawat'=>$row['no_rawat'],
'kode_brng'=>$row['kode_brng'],
'jml_barang'=>$row['jml_barang'],
'harga'=>$row['harga'],
'total'=>$row['total'],
'dosis'=>$row['dosis'],
'tanggal'=>$row['tanggal'],
'jam'=>$row['jam'],
'kd_bangsal'=>$row['kd_bangsal'],
'no_batch'=>$row['no_batch'],
'no_faktur'=>$row['no_faktur']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('resep_pulang => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_rawat)
    {

        if($this->core->loadDisabledMenu('resep_pulang')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('resep_pulang', '*', ['no_rawat' => $no_rawat]);

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
          $this->core->LogQuery('resep_pulang => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_rawat)
    {

        if($this->core->loadDisabledMenu('resep_pulang')['read'] == 'true') {
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
          $this->core->LogQuery('resep_pulang => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_rawat' => $no_rawat]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('resep_pulang', 'kd_bangsal', ['GROUP' => 'kd_bangsal']);
      $datasets = $this->core->db->select('resep_pulang', ['count' => \Medoo\Medoo::raw('COUNT(<kd_bangsal>)')], ['GROUP' => 'kd_bangsal']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('resep_pulang', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('resep_pulang', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'resep_pulang';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('resep_pulang => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/resep_pulang/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/resep_pulang/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('resep_pulang')]);
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

        $this->core->addCSS(url([ 'resep_pulang', 'css']));
        $this->core->addJS(url([ 'resep_pulang', 'javascript']), 'footer');
    }

}
