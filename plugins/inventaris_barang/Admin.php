<?php
namespace Plugins\Inventaris_Barang;

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
        $disabled_menu = $this->core->loadDisabledMenu('inventaris_barang'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kode_barang');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_inventaris_barang= isset_or($_POST['search_field_inventaris_barang']);
        $search_text_inventaris_barang = isset_or($_POST['search_text_inventaris_barang']);

        if ($search_text_inventaris_barang != '') {
          $where[$search_field_inventaris_barang.'[~]'] = $search_text_inventaris_barang;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('inventaris_barang', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('inventaris_barang', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('inventaris_barang', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kode_barang'=>$row['kode_barang'],
'nama_barang'=>$row['nama_barang'],
'jml_barang'=>$row['jml_barang'],
'kode_produsen'=>$row['kode_produsen'],
'id_merk'=>$row['id_merk'],
'thn_produksi'=>$row['thn_produksi'],
'isbn'=>$row['isbn'],
'id_kategori'=>$row['id_kategori'],
'id_jenis'=>$row['id_jenis']

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
          $this->core->LogQuery('inventaris_barang => postData');
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

            if($this->core->loadDisabledMenu('inventaris_barang')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kode_barang = $_POST['kode_barang'];
$nama_barang = $_POST['nama_barang'];
$jml_barang = $_POST['jml_barang'];
$kode_produsen = $_POST['kode_produsen'];
$id_merk = $_POST['id_merk'];
$thn_produksi = $_POST['thn_produksi'];
$isbn = $_POST['isbn'];
$id_kategori = $_POST['id_kategori'];
$id_jenis = $_POST['id_jenis'];

            
            $result = $this->core->db->insert('inventaris_barang', [
'kode_barang'=>$kode_barang, 'nama_barang'=>$nama_barang, 'jml_barang'=>$jml_barang, 'kode_produsen'=>$kode_produsen, 'id_merk'=>$id_merk, 'thn_produksi'=>$thn_produksi, 'isbn'=>$isbn, 'id_kategori'=>$id_kategori, 'id_jenis'=>$id_jenis
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
              $this->core->LogQuery('inventaris_barang => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('inventaris_barang')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kode_barang = $_POST['kode_barang'];
$nama_barang = $_POST['nama_barang'];
$jml_barang = $_POST['jml_barang'];
$kode_produsen = $_POST['kode_produsen'];
$id_merk = $_POST['id_merk'];
$thn_produksi = $_POST['thn_produksi'];
$isbn = $_POST['isbn'];
$id_kategori = $_POST['id_kategori'];
$id_jenis = $_POST['id_jenis'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('inventaris_barang', [
'kode_barang'=>$kode_barang, 'nama_barang'=>$nama_barang, 'jml_barang'=>$jml_barang, 'kode_produsen'=>$kode_produsen, 'id_merk'=>$id_merk, 'thn_produksi'=>$thn_produksi, 'isbn'=>$isbn, 'id_kategori'=>$id_kategori, 'id_jenis'=>$id_jenis
            ], [
              'kode_barang'=>$kode_barang
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
              $this->core->LogQuery('inventaris_barang => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('inventaris_barang')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kode_barang= $_POST['kode_barang'];
            $result = $this->core->db->delete('inventaris_barang', [
              'AND' => [
                'kode_barang'=>$kode_barang
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
              $this->core->LogQuery('inventaris_barang => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('inventaris_barang')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_inventaris_barang= $_POST['search_field_inventaris_barang'];
            $search_text_inventaris_barang = $_POST['search_text_inventaris_barang'];

            if ($search_text_inventaris_barang != '') {
              $where[$search_field_inventaris_barang.'[~]'] = $search_text_inventaris_barang;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('inventaris_barang', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kode_barang'=>$row['kode_barang'],
'nama_barang'=>$row['nama_barang'],
'jml_barang'=>$row['jml_barang'],
'kode_produsen'=>$row['kode_produsen'],
'id_merk'=>$row['id_merk'],
'thn_produksi'=>$row['thn_produksi'],
'isbn'=>$row['isbn'],
'id_kategori'=>$row['id_kategori'],
'id_jenis'=>$row['id_jenis']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('inventaris_barang => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kode_barang)
    {

        if($this->core->loadDisabledMenu('inventaris_barang')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('inventaris_barang', '*', ['kode_barang' => $kode_barang]);

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
          $this->core->LogQuery('inventaris_barang => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kode_barang)
    {

        if($this->core->loadDisabledMenu('inventaris_barang')['read'] == 'true') {
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
          $this->core->LogQuery('inventaris_barang => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kode_barang' => $kode_barang]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('inventaris_barang', 'thn_produksi', ['GROUP' => 'thn_produksi']);
      $datasets = $this->core->db->select('inventaris_barang', ['count' => \Medoo\Medoo::raw('COUNT(<thn_produksi>)')], ['GROUP' => 'thn_produksi']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('inventaris_barang', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('inventaris_barang', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'inventaris_barang';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('inventaris_barang => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/inventaris_barang/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/inventaris_barang/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('inventaris_barang')]);
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

        $this->core->addCSS(url([ 'inventaris_barang', 'css']));
        $this->core->addJS(url([ 'inventaris_barang', 'javascript']), 'footer');
    }

}
