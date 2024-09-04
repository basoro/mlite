<?php
namespace Plugins\Icd9;

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
        $disabled_menu = $this->core->loadDisabledMenu('icd9'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kode');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_icd9= isset_or($_POST['search_field_icd9']);
        $search_text_icd9 = isset_or($_POST['search_text_icd9']);

        if ($search_text_icd9 != '') {
          $where[$search_field_icd9.'[~]'] = $search_text_icd9;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('icd9', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('icd9', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('icd9', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kode'=>$row['kode'],
'deskripsi_panjang'=>$row['deskripsi_panjang'],
'deskripsi_pendek'=>$row['deskripsi_pendek']

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
          $this->core->LogQuery('icd9 => postData');
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

            if($this->core->loadDisabledMenu('icd9')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kode = $_POST['kode'];
$deskripsi_panjang = $_POST['deskripsi_panjang'];
$deskripsi_pendek = $_POST['deskripsi_pendek'];

            
            $result = $this->core->db->insert('icd9', [
'kode'=>$kode, 'deskripsi_panjang'=>$deskripsi_panjang, 'deskripsi_pendek'=>$deskripsi_pendek
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
              $this->core->LogQuery('icd9 => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('icd9')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kode = $_POST['kode'];
$deskripsi_panjang = $_POST['deskripsi_panjang'];
$deskripsi_pendek = $_POST['deskripsi_pendek'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('icd9', [
'kode'=>$kode, 'deskripsi_panjang'=>$deskripsi_panjang, 'deskripsi_pendek'=>$deskripsi_pendek
            ], [
              'kode'=>$kode
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
              $this->core->LogQuery('icd9 => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('icd9')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kode= $_POST['kode'];
            $result = $this->core->db->delete('icd9', [
              'AND' => [
                'kode'=>$kode
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
              $this->core->LogQuery('icd9 => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('icd9')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_icd9= $_POST['search_field_icd9'];
            $search_text_icd9 = $_POST['search_text_icd9'];

            if ($search_text_icd9 != '') {
              $where[$search_field_icd9.'[~]'] = $search_text_icd9;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('icd9', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kode'=>$row['kode'],
'deskripsi_panjang'=>$row['deskripsi_panjang'],
'deskripsi_pendek'=>$row['deskripsi_pendek']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('icd9 => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kode)
    {

        if($this->core->loadDisabledMenu('icd9')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('icd9', '*', ['kode' => $kode]);

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
          $this->core->LogQuery('icd9 => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kode)
    {

        if($this->core->loadDisabledMenu('icd9')['read'] == 'true') {
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
          $this->core->LogQuery('icd9 => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kode' => $kode]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('icd9', 'deskripsi_pendek', ['GROUP' => 'deskripsi_pendek']);
      $datasets = $this->core->db->select('icd9', ['count' => \Medoo\Medoo::raw('COUNT(<deskripsi_pendek>)')], ['GROUP' => 'deskripsi_pendek']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('icd9', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('icd9', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'icd9';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('icd9 => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getImport()
    {
      $fileName = 'https://basoro.id/downloads/icd9cm.csv';
      echo '['.date('d-m-Y H:i:s').'][info] --- Mengimpor file csv'."<br>";

      $csvData = file_get_contents($fileName);
      if($csvData) {
        echo '['.date('d-m-Y H:i:s').'][info] Berkas ditemukan'."<br>";
      } else {
        echo '['.date('d-m-Y H:i:s').'][error] File '.$filename.' tidak ditemukan'."<br>";
        exit();
      }

      $lines = explode(PHP_EOL, $csvData);
      $array = array();
      foreach ($lines as $line) {
          $array[] = str_getcsv($line);
      }

      foreach ($array as $data){   
        $kode = $data[0];
        $nama = isset_or($data[1], '');
        $value_query[] = "('".$kode."','".str_replace("'","\'",$nama)."','')";
      }
      $str = implode(",", $value_query);
      echo '['.date('d-m-Y H:i:s').'][info] Memasukkan data'."<br>";
      $result = $this->core->db->query("INSERT INTO icd9 (kode, deskripsi_panjang, deskripsi_pendek) VALUES $str ON DUPLICATE KEY UPDATE kode=VALUES(kode)");
      if($result) {
        echo '['.date('d-m-Y H:i:s').'][info] Impor selesai'."<br>";
      } else {
        echo '['.date('d-m-Y H:i:s').'][error] kesalahan selama import : <pre>'.json_encode($str, JSON_PRETTY_PRINT).''."</pre><br>";
        exit();
      }
      
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/icd9/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/icd9/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('icd9')]);
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

        $this->core->addCSS(url([ 'icd9', 'css']));
        $this->core->addJS(url([ 'icd9', 'javascript']), 'footer');
    }

}
