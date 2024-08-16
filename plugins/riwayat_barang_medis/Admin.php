<?php
namespace Plugins\Riwayat_Barang_Medis;

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
        $disabled_menu = $this->core->loadDisabledMenu('riwayat_barang_medis'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kode_brng');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_riwayat_barang_medis= isset_or($_POST['search_field_riwayat_barang_medis']);
        $search_text_riwayat_barang_medis = isset_or($_POST['search_text_riwayat_barang_medis']);

        if ($search_text_riwayat_barang_medis != '') {
          $where[$search_field_riwayat_barang_medis.'[~]'] = $search_text_riwayat_barang_medis;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('riwayat_barang_medis', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('riwayat_barang_medis', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('riwayat_barang_medis', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kode_brng'=>$row['kode_brng'],
'stok_awal'=>$row['stok_awal'],
'masuk'=>$row['masuk'],
'keluar'=>$row['keluar'],
'stok_akhir'=>$row['stok_akhir'],
'posisi'=>$row['posisi'],
'tanggal'=>$row['tanggal'],
'jam'=>$row['jam'],
'petugas'=>$row['petugas'],
'kd_bangsal'=>$row['kd_bangsal'],
'status'=>$row['status'],
'no_batch'=>$row['no_batch'],
'no_faktur'=>$row['no_faktur'],
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
          $this->core->LogQuery('riwayat_barang_medis => postData');
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

            if($this->core->loadDisabledMenu('riwayat_barang_medis')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kode_brng = $_POST['kode_brng'];
$stok_awal = $_POST['stok_awal'];
$masuk = $_POST['masuk'];
$keluar = $_POST['keluar'];
$stok_akhir = $_POST['stok_akhir'];
$posisi = $_POST['posisi'];
$tanggal = $_POST['tanggal'];
$jam = $_POST['jam'];
$petugas = $_POST['petugas'];
$kd_bangsal = $_POST['kd_bangsal'];
$status = $_POST['status'];
$no_batch = $_POST['no_batch'];
$no_faktur = $_POST['no_faktur'];
$keterangan = $_POST['keterangan'];

            
            $result = $this->core->db->insert('riwayat_barang_medis', [
'kode_brng'=>$kode_brng, 'stok_awal'=>$stok_awal, 'masuk'=>$masuk, 'keluar'=>$keluar, 'stok_akhir'=>$stok_akhir, 'posisi'=>$posisi, 'tanggal'=>$tanggal, 'jam'=>$jam, 'petugas'=>$petugas, 'kd_bangsal'=>$kd_bangsal, 'status'=>$status, 'no_batch'=>$no_batch, 'no_faktur'=>$no_faktur, 'keterangan'=>$keterangan
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
              $this->core->LogQuery('riwayat_barang_medis => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('riwayat_barang_medis')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kode_brng = $_POST['kode_brng'];
$stok_awal = $_POST['stok_awal'];
$masuk = $_POST['masuk'];
$keluar = $_POST['keluar'];
$stok_akhir = $_POST['stok_akhir'];
$posisi = $_POST['posisi'];
$tanggal = $_POST['tanggal'];
$jam = $_POST['jam'];
$petugas = $_POST['petugas'];
$kd_bangsal = $_POST['kd_bangsal'];
$status = $_POST['status'];
$no_batch = $_POST['no_batch'];
$no_faktur = $_POST['no_faktur'];
$keterangan = $_POST['keterangan'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('riwayat_barang_medis', [
'kode_brng'=>$kode_brng, 'stok_awal'=>$stok_awal, 'masuk'=>$masuk, 'keluar'=>$keluar, 'stok_akhir'=>$stok_akhir, 'posisi'=>$posisi, 'tanggal'=>$tanggal, 'jam'=>$jam, 'petugas'=>$petugas, 'kd_bangsal'=>$kd_bangsal, 'status'=>$status, 'no_batch'=>$no_batch, 'no_faktur'=>$no_faktur, 'keterangan'=>$keterangan
            ], [
              'kode_brng'=>$kode_brng
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
              $this->core->LogQuery('riwayat_barang_medis => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('riwayat_barang_medis')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kode_brng= $_POST['kode_brng'];
            $result = $this->core->db->delete('riwayat_barang_medis', [
              'AND' => [
                'kode_brng'=>$kode_brng
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
              $this->core->LogQuery('riwayat_barang_medis => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('riwayat_barang_medis')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_riwayat_barang_medis= $_POST['search_field_riwayat_barang_medis'];
            $search_text_riwayat_barang_medis = $_POST['search_text_riwayat_barang_medis'];

            if ($search_text_riwayat_barang_medis != '') {
              $where[$search_field_riwayat_barang_medis.'[~]'] = $search_text_riwayat_barang_medis;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('riwayat_barang_medis', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kode_brng'=>$row['kode_brng'],
'stok_awal'=>$row['stok_awal'],
'masuk'=>$row['masuk'],
'keluar'=>$row['keluar'],
'stok_akhir'=>$row['stok_akhir'],
'posisi'=>$row['posisi'],
'tanggal'=>$row['tanggal'],
'jam'=>$row['jam'],
'petugas'=>$row['petugas'],
'kd_bangsal'=>$row['kd_bangsal'],
'status'=>$row['status'],
'no_batch'=>$row['no_batch'],
'no_faktur'=>$row['no_faktur'],
'keterangan'=>$row['keterangan']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('riwayat_barang_medis => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kode_brng)
    {

        if($this->core->loadDisabledMenu('riwayat_barang_medis')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('riwayat_barang_medis', '*', ['kode_brng' => $kode_brng]);

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
          $this->core->LogQuery('riwayat_barang_medis => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kode_brng)
    {

        if($this->core->loadDisabledMenu('riwayat_barang_medis')['read'] == 'true') {
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
          $this->core->LogQuery('riwayat_barang_medis => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kode_brng' => $kode_brng]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('riwayat_barang_medis', 'kd_bangsal', ['GROUP' => 'kd_bangsal']);
      $datasets = $this->core->db->select('riwayat_barang_medis', ['count' => \Medoo\Medoo::raw('COUNT(<kd_bangsal>)')], ['GROUP' => 'kd_bangsal']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('riwayat_barang_medis', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('riwayat_barang_medis', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'riwayat_barang_medis';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('riwayat_barang_medis => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/riwayat_barang_medis/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/riwayat_barang_medis/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('riwayat_barang_medis')]);
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

        $this->core->addCSS(url([ 'riwayat_barang_medis', 'css']));
        $this->core->addJS(url([ 'riwayat_barang_medis', 'javascript']), 'footer');
    }

}
