<?php
namespace Plugins\Inventaris;

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
        $disabled_menu = $this->core->loadDisabledMenu('inventaris'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'no_inventaris');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_inventaris= isset_or($_POST['search_field_inventaris']);
        $search_text_inventaris = isset_or($_POST['search_text_inventaris']);

        if ($search_text_inventaris != '') {
          $where[$search_field_inventaris.'[~]'] = $search_text_inventaris;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('inventaris', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('inventaris', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('inventaris', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_inventaris'=>$row['no_inventaris'],
'kode_barang'=>$row['kode_barang'],
'asal_barang'=>$row['asal_barang'],
'tgl_pengadaan'=>$row['tgl_pengadaan'],
'harga'=>$row['harga'],
'status_barang'=>$row['status_barang'],
'id_ruang'=>$row['id_ruang'],
'no_rak'=>$row['no_rak'],
'no_box'=>$row['no_box']

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
          $this->core->LogQuery('inventaris => postData');
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

            if($this->core->loadDisabledMenu('inventaris')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_inventaris = $_POST['no_inventaris'];
$kode_barang = $_POST['kode_barang'];
$asal_barang = $_POST['asal_barang'];
$tgl_pengadaan = $_POST['tgl_pengadaan'];
$harga = $_POST['harga'];
$status_barang = $_POST['status_barang'];
$id_ruang = $_POST['id_ruang'];
$no_rak = $_POST['no_rak'];
$no_box = $_POST['no_box'];

            
            $result = $this->core->db->insert('inventaris', [
'no_inventaris'=>$no_inventaris, 'kode_barang'=>$kode_barang, 'asal_barang'=>$asal_barang, 'tgl_pengadaan'=>$tgl_pengadaan, 'harga'=>$harga, 'status_barang'=>$status_barang, 'id_ruang'=>$id_ruang, 'no_rak'=>$no_rak, 'no_box'=>$no_box
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
              $this->core->LogQuery('inventaris => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('inventaris')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_inventaris = $_POST['no_inventaris'];
$kode_barang = $_POST['kode_barang'];
$asal_barang = $_POST['asal_barang'];
$tgl_pengadaan = $_POST['tgl_pengadaan'];
$harga = $_POST['harga'];
$status_barang = $_POST['status_barang'];
$id_ruang = $_POST['id_ruang'];
$no_rak = $_POST['no_rak'];
$no_box = $_POST['no_box'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('inventaris', [
'no_inventaris'=>$no_inventaris, 'kode_barang'=>$kode_barang, 'asal_barang'=>$asal_barang, 'tgl_pengadaan'=>$tgl_pengadaan, 'harga'=>$harga, 'status_barang'=>$status_barang, 'id_ruang'=>$id_ruang, 'no_rak'=>$no_rak, 'no_box'=>$no_box
            ], [
              'no_inventaris'=>$no_inventaris
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
              $this->core->LogQuery('inventaris => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('inventaris')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_inventaris= $_POST['no_inventaris'];
            $result = $this->core->db->delete('inventaris', [
              'AND' => [
                'no_inventaris'=>$no_inventaris
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
              $this->core->LogQuery('inventaris => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('inventaris')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_inventaris= $_POST['search_field_inventaris'];
            $search_text_inventaris = $_POST['search_text_inventaris'];

            if ($search_text_inventaris != '') {
              $where[$search_field_inventaris.'[~]'] = $search_text_inventaris;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('inventaris', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_inventaris'=>$row['no_inventaris'],
'kode_barang'=>$row['kode_barang'],
'asal_barang'=>$row['asal_barang'],
'tgl_pengadaan'=>$row['tgl_pengadaan'],
'harga'=>$row['harga'],
'status_barang'=>$row['status_barang'],
'id_ruang'=>$row['id_ruang'],
'no_rak'=>$row['no_rak'],
'no_box'=>$row['no_box']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('inventaris => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_inventaris)
    {

        if($this->core->loadDisabledMenu('inventaris')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('inventaris', '*', ['no_inventaris' => $no_inventaris]);

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
          $this->core->LogQuery('inventaris => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_inventaris)
    {

        if($this->core->loadDisabledMenu('inventaris')['read'] == 'true') {
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
          $this->core->LogQuery('inventaris => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_inventaris' => $no_inventaris]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('inventaris', 'status_barang', ['GROUP' => 'status_barang']);
      $datasets = $this->core->db->select('inventaris', ['count' => \Medoo\Medoo::raw('COUNT(<status_barang>)')], ['GROUP' => 'status_barang']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('inventaris', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('inventaris', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'inventaris';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('inventaris => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/inventaris/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/inventaris/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('inventaris')]);
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

        $this->core->addCSS(url([ 'inventaris', 'css']));
        $this->core->addJS(url([ 'inventaris', 'javascript']), 'footer');
    }

}
