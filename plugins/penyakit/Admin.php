<?php
namespace Plugins\Penyakit;

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
        $disabled_menu = $this->core->loadDisabledMenu('penyakit'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kd_penyakit');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_penyakit= isset_or($_POST['search_field_penyakit']);
        $search_text_penyakit = isset_or($_POST['search_text_penyakit']);

        if ($search_text_penyakit != '') {
          $where[$search_field_penyakit.'[~]'] = $search_text_penyakit;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('penyakit', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('penyakit', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('penyakit', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kd_penyakit'=>$row['kd_penyakit'],
                'nm_penyakit'=>$row['nm_penyakit'],
                'ciri_ciri'=>$row['ciri_ciri'],
                'keterangan'=>$row['keterangan'],
                'kd_ktg'=>$row['kd_ktg'],
                'status'=>$row['status']
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
          $this->core->LogQuery('penyakit => postData');
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

            if($this->core->loadDisabledMenu('penyakit')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_penyakit = $_POST['kd_penyakit'];
            $nm_penyakit = $_POST['nm_penyakit'];
            $ciri_ciri = $_POST['ciri_ciri'];
            $keterangan = $_POST['keterangan'];
            $kd_ktg = $_POST['kd_ktg'];
            $status = $_POST['status'];
            
            $result = $this->core->db->insert('penyakit', [
              'kd_penyakit'=>$kd_penyakit, 'nm_penyakit'=>$nm_penyakit, 'ciri_ciri'=>$ciri_ciri, 'keterangan'=>$keterangan, 'kd_ktg'=>$kd_ktg, 'status'=>$status
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
              $this->core->LogQuery('penyakit => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('penyakit')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_penyakit = $_POST['kd_penyakit'];
            $nm_penyakit = $_POST['nm_penyakit'];
            $ciri_ciri = $_POST['ciri_ciri'];
            $keterangan = $_POST['keterangan'];
            $kd_ktg = $_POST['kd_ktg'];
            $status = $_POST['status'];


            // BUANG FIELD PERTAMA

            $result = $this->core->db->update('penyakit', [
              'nm_penyakit'=>$nm_penyakit, 'ciri_ciri'=>$ciri_ciri, 'keterangan'=>$keterangan, 'kd_ktg'=>$kd_ktg, 'status'=>$status
            ], [
              'kd_penyakit'=>$kd_penyakit
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
              $this->core->LogQuery('penyakit => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('penyakit')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_penyakit= $_POST['kd_penyakit'];
            $result = $this->core->db->delete('penyakit', [
              'AND' => [
                'kd_penyakit'=>$kd_penyakit
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
              $this->core->LogQuery('penyakit => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('penyakit')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_penyakit= $_POST['search_field_penyakit'];
            $search_text_penyakit = $_POST['search_text_penyakit'];

            if ($search_text_penyakit != '') {
              $where[$search_field_penyakit.'[~]'] = $search_text_penyakit;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('penyakit', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kd_penyakit'=>$row['kd_penyakit'],
                    'nm_penyakit'=>$row['nm_penyakit'],
                    'ciri_ciri'=>$row['ciri_ciri'],
                    'keterangan'=>$row['keterangan'],
                    'kd_ktg'=>$row['kd_ktg'],
                    'status'=>$row['status']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('penyakit => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kd_penyakit)
    {

        if($this->core->loadDisabledMenu('penyakit')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('penyakit', '*', ['kd_penyakit' => $kd_penyakit]);

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
          $this->core->LogQuery('penyakit => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kd_penyakit)
    {

        if($this->core->loadDisabledMenu('penyakit')['read'] == 'true') {
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
          $this->core->LogQuery('penyakit => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kd_penyakit' => $kd_penyakit]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('penyakit', 'status', ['GROUP' => 'status']);
      $datasets = $this->core->db->select('penyakit', ['count' => \Medoo\Medoo::raw('COUNT(<status>)')], ['GROUP' => 'status']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('penyakit', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('penyakit', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'penyakit';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('penyakit => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getImport()
    {
      $fileName = 'https://basoro.id/downloads/icd10.csv';
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
        $nama = str_replace('"','',$nama);
        $value_query[] = "('".$kode."','".str_replace("'","\'",$nama)."','','','-','Tidak Menular')";
      }
      $str = implode(",", $value_query);
      echo '['.date('d-m-Y H:i:s').'][info] Memasukkan data'."<br>";
      $result = $this->core->db->query("INSERT INTO penyakit (kd_penyakit, nm_penyakit, ciri_ciri, keterangan, kd_ktg, status) VALUES $str ON DUPLICATE KEY UPDATE kd_penyakit=VALUES(kd_penyakit)");
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
        echo $this->draw(MODULES.'/penyakit/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/penyakit/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('penyakit')]);
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

        $this->core->addCSS(url([ 'penyakit', 'css']));
        $this->core->addJS(url([ 'penyakit', 'javascript']), 'footer');
    }

}
