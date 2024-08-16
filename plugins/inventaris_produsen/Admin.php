<?php
namespace Plugins\Inventaris_Produsen;

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
        $disabled_menu = $this->core->loadDisabledMenu('inventaris_produsen'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kode_produsen');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_inventaris_produsen= isset_or($_POST['search_field_inventaris_produsen']);
        $search_text_inventaris_produsen = isset_or($_POST['search_text_inventaris_produsen']);

        if ($search_text_inventaris_produsen != '') {
          $where[$search_field_inventaris_produsen.'[~]'] = $search_text_inventaris_produsen;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('inventaris_produsen', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('inventaris_produsen', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('inventaris_produsen', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kode_produsen'=>$row['kode_produsen'],
'nama_produsen'=>$row['nama_produsen'],
'alamat_produsen'=>$row['alamat_produsen'],
'no_telp'=>$row['no_telp'],
'email'=>$row['email'],
'website_produsen'=>$row['website_produsen']

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
          $this->core->LogQuery('inventaris_produsen => postData');
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

            if($this->core->loadDisabledMenu('inventaris_produsen')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kode_produsen = $_POST['kode_produsen'];
$nama_produsen = $_POST['nama_produsen'];
$alamat_produsen = $_POST['alamat_produsen'];
$no_telp = $_POST['no_telp'];
$email = $_POST['email'];
$website_produsen = $_POST['website_produsen'];

            
            $result = $this->core->db->insert('inventaris_produsen', [
'kode_produsen'=>$kode_produsen, 'nama_produsen'=>$nama_produsen, 'alamat_produsen'=>$alamat_produsen, 'no_telp'=>$no_telp, 'email'=>$email, 'website_produsen'=>$website_produsen
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
              $this->core->LogQuery('inventaris_produsen => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('inventaris_produsen')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kode_produsen = $_POST['kode_produsen'];
$nama_produsen = $_POST['nama_produsen'];
$alamat_produsen = $_POST['alamat_produsen'];
$no_telp = $_POST['no_telp'];
$email = $_POST['email'];
$website_produsen = $_POST['website_produsen'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('inventaris_produsen', [
'kode_produsen'=>$kode_produsen, 'nama_produsen'=>$nama_produsen, 'alamat_produsen'=>$alamat_produsen, 'no_telp'=>$no_telp, 'email'=>$email, 'website_produsen'=>$website_produsen
            ], [
              'kode_produsen'=>$kode_produsen
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
              $this->core->LogQuery('inventaris_produsen => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('inventaris_produsen')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kode_produsen= $_POST['kode_produsen'];
            $result = $this->core->db->delete('inventaris_produsen', [
              'AND' => [
                'kode_produsen'=>$kode_produsen
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
              $this->core->LogQuery('inventaris_produsen => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('inventaris_produsen')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_inventaris_produsen= $_POST['search_field_inventaris_produsen'];
            $search_text_inventaris_produsen = $_POST['search_text_inventaris_produsen'];

            if ($search_text_inventaris_produsen != '') {
              $where[$search_field_inventaris_produsen.'[~]'] = $search_text_inventaris_produsen;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('inventaris_produsen', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kode_produsen'=>$row['kode_produsen'],
'nama_produsen'=>$row['nama_produsen'],
'alamat_produsen'=>$row['alamat_produsen'],
'no_telp'=>$row['no_telp'],
'email'=>$row['email'],
'website_produsen'=>$row['website_produsen']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('inventaris_produsen => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kode_produsen)
    {

        if($this->core->loadDisabledMenu('inventaris_produsen')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('inventaris_produsen', '*', ['kode_produsen' => $kode_produsen]);

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
          $this->core->LogQuery('inventaris_produsen => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kode_produsen)
    {

        if($this->core->loadDisabledMenu('inventaris_produsen')['read'] == 'true') {
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
          $this->core->LogQuery('inventaris_produsen => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kode_produsen' => $kode_produsen]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('inventaris_produsen', 'alamat_produsen', ['GROUP' => 'alamat_produsen']);
      $datasets = $this->core->db->select('inventaris_produsen', ['count' => \Medoo\Medoo::raw('COUNT(<alamat_produsen>)')], ['GROUP' => 'alamat_produsen']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('inventaris_produsen', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('inventaris_produsen', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'inventaris_produsen';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('inventaris_produsen => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/inventaris_produsen/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/inventaris_produsen/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('inventaris_produsen')]);
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

        $this->core->addCSS(url([ 'inventaris_produsen', 'css']));
        $this->core->addJS(url([ 'inventaris_produsen', 'javascript']), 'footer');
    }

}
