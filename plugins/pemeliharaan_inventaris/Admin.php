<?php
namespace Plugins\Pemeliharaan_Inventaris;

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
        $disabled_menu = $this->core->loadDisabledMenu('pemeliharaan_inventaris'); 
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
        $search_field_pemeliharaan_inventaris= isset_or($_POST['search_field_pemeliharaan_inventaris']);
        $search_text_pemeliharaan_inventaris = isset_or($_POST['search_text_pemeliharaan_inventaris']);

        if ($search_text_pemeliharaan_inventaris != '') {
          $where[$search_field_pemeliharaan_inventaris.'[~]'] = $search_text_pemeliharaan_inventaris;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('pemeliharaan_inventaris', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('pemeliharaan_inventaris', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('pemeliharaan_inventaris', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_inventaris'=>$row['no_inventaris'],
'tanggal'=>$row['tanggal'],
'uraian_kegiatan'=>$row['uraian_kegiatan'],
'nip'=>$row['nip'],
'pelaksana'=>$row['pelaksana'],
'biaya'=>$row['biaya'],
'jenis_pemeliharaan'=>$row['jenis_pemeliharaan']

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
          $this->core->LogQuery('pemeliharaan_inventaris => postData');
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

            if($this->core->loadDisabledMenu('pemeliharaan_inventaris')['create'] == 'true') {
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
$tanggal = $_POST['tanggal'];
$uraian_kegiatan = $_POST['uraian_kegiatan'];
$nip = $_POST['nip'];
$pelaksana = $_POST['pelaksana'];
$biaya = $_POST['biaya'];
$jenis_pemeliharaan = $_POST['jenis_pemeliharaan'];

            
            $result = $this->core->db->insert('pemeliharaan_inventaris', [
'no_inventaris'=>$no_inventaris, 'tanggal'=>$tanggal, 'uraian_kegiatan'=>$uraian_kegiatan, 'nip'=>$nip, 'pelaksana'=>$pelaksana, 'biaya'=>$biaya, 'jenis_pemeliharaan'=>$jenis_pemeliharaan
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
              $this->core->LogQuery('pemeliharaan_inventaris => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('pemeliharaan_inventaris')['update'] == 'true') {
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
$tanggal = $_POST['tanggal'];
$uraian_kegiatan = $_POST['uraian_kegiatan'];
$nip = $_POST['nip'];
$pelaksana = $_POST['pelaksana'];
$biaya = $_POST['biaya'];
$jenis_pemeliharaan = $_POST['jenis_pemeliharaan'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('pemeliharaan_inventaris', [
'no_inventaris'=>$no_inventaris, 'tanggal'=>$tanggal, 'uraian_kegiatan'=>$uraian_kegiatan, 'nip'=>$nip, 'pelaksana'=>$pelaksana, 'biaya'=>$biaya, 'jenis_pemeliharaan'=>$jenis_pemeliharaan
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
              $this->core->LogQuery('pemeliharaan_inventaris => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('pemeliharaan_inventaris')['delete'] == 'true') {
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
            $result = $this->core->db->delete('pemeliharaan_inventaris', [
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
              $this->core->LogQuery('pemeliharaan_inventaris => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('pemeliharaan_inventaris')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_pemeliharaan_inventaris= $_POST['search_field_pemeliharaan_inventaris'];
            $search_text_pemeliharaan_inventaris = $_POST['search_text_pemeliharaan_inventaris'];

            if ($search_text_pemeliharaan_inventaris != '') {
              $where[$search_field_pemeliharaan_inventaris.'[~]'] = $search_text_pemeliharaan_inventaris;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('pemeliharaan_inventaris', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_inventaris'=>$row['no_inventaris'],
'tanggal'=>$row['tanggal'],
'uraian_kegiatan'=>$row['uraian_kegiatan'],
'nip'=>$row['nip'],
'pelaksana'=>$row['pelaksana'],
'biaya'=>$row['biaya'],
'jenis_pemeliharaan'=>$row['jenis_pemeliharaan']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('pemeliharaan_inventaris => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_inventaris)
    {

        if($this->core->loadDisabledMenu('pemeliharaan_inventaris')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('pemeliharaan_inventaris', '*', ['no_inventaris' => $no_inventaris]);

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
          $this->core->LogQuery('pemeliharaan_inventaris => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_inventaris)
    {

        if($this->core->loadDisabledMenu('pemeliharaan_inventaris')['read'] == 'true') {
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
          $this->core->LogQuery('pemeliharaan_inventaris => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_inventaris' => $no_inventaris]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('pemeliharaan_inventaris', 'nip', ['GROUP' => 'nip']);
      $datasets = $this->core->db->select('pemeliharaan_inventaris', ['count' => \Medoo\Medoo::raw('COUNT(<nip>)')], ['GROUP' => 'nip']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('pemeliharaan_inventaris', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('pemeliharaan_inventaris', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'pemeliharaan_inventaris';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('pemeliharaan_inventaris => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/pemeliharaan_inventaris/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/pemeliharaan_inventaris/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('pemeliharaan_inventaris')]);
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

        $this->core->addCSS(url([ 'pemeliharaan_inventaris', 'css']));
        $this->core->addJS(url([ 'pemeliharaan_inventaris', 'javascript']), 'footer');
    }

}
