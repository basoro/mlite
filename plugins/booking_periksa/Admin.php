<?php
namespace Plugins\Booking_Periksa;

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
        $disabled_menu = $this->core->loadDisabledMenu('booking_periksa'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'no_booking');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_booking_periksa= isset_or($_POST['search_field_booking_periksa']);
        $search_text_booking_periksa = isset_or($_POST['search_text_booking_periksa']);

        if ($search_text_booking_periksa != '') {
          $where[$search_field_booking_periksa.'[~]'] = $search_text_booking_periksa;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('booking_periksa', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('booking_periksa', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('booking_periksa', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_booking'=>$row['no_booking'],
'tanggal'=>$row['tanggal'],
'nama'=>$row['nama'],
'alamat'=>$row['alamat'],
'no_telp'=>$row['no_telp'],
'email'=>$row['email'],
'kd_poli'=>$row['kd_poli'],
'tambahan_pesan'=>$row['tambahan_pesan'],
'status'=>$row['status'],
'tanggal_booking'=>$row['tanggal_booking']

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
          $this->core->LogQuery('booking_periksa => postData');
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

            if($this->core->loadDisabledMenu('booking_periksa')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_booking = $_POST['no_booking'];
$tanggal = $_POST['tanggal'];
$nama = $_POST['nama'];
$alamat = $_POST['alamat'];
$no_telp = $_POST['no_telp'];
$email = $_POST['email'];
$kd_poli = $_POST['kd_poli'];
$tambahan_pesan = $_POST['tambahan_pesan'];
$status = $_POST['status'];
$tanggal_booking = $_POST['tanggal_booking'];

            
            $result = $this->core->db->insert('booking_periksa', [
'no_booking'=>$no_booking, 'tanggal'=>$tanggal, 'nama'=>$nama, 'alamat'=>$alamat, 'no_telp'=>$no_telp, 'email'=>$email, 'kd_poli'=>$kd_poli, 'tambahan_pesan'=>$tambahan_pesan, 'status'=>$status, 'tanggal_booking'=>$tanggal_booking
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
              $this->core->LogQuery('booking_periksa => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('booking_periksa')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_booking = $_POST['no_booking'];
$tanggal = $_POST['tanggal'];
$nama = $_POST['nama'];
$alamat = $_POST['alamat'];
$no_telp = $_POST['no_telp'];
$email = $_POST['email'];
$kd_poli = $_POST['kd_poli'];
$tambahan_pesan = $_POST['tambahan_pesan'];
$status = $_POST['status'];
$tanggal_booking = $_POST['tanggal_booking'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('booking_periksa', [
'no_booking'=>$no_booking, 'tanggal'=>$tanggal, 'nama'=>$nama, 'alamat'=>$alamat, 'no_telp'=>$no_telp, 'email'=>$email, 'kd_poli'=>$kd_poli, 'tambahan_pesan'=>$tambahan_pesan, 'status'=>$status, 'tanggal_booking'=>$tanggal_booking
            ], [
              'no_booking'=>$no_booking
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
              $this->core->LogQuery('booking_periksa => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('booking_periksa')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_booking= $_POST['no_booking'];
            $result = $this->core->db->delete('booking_periksa', [
              'AND' => [
                'no_booking'=>$no_booking
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
              $this->core->LogQuery('booking_periksa => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('booking_periksa')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_booking_periksa= $_POST['search_field_booking_periksa'];
            $search_text_booking_periksa = $_POST['search_text_booking_periksa'];

            if ($search_text_booking_periksa != '') {
              $where[$search_field_booking_periksa.'[~]'] = $search_text_booking_periksa;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('booking_periksa', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_booking'=>$row['no_booking'],
'tanggal'=>$row['tanggal'],
'nama'=>$row['nama'],
'alamat'=>$row['alamat'],
'no_telp'=>$row['no_telp'],
'email'=>$row['email'],
'kd_poli'=>$row['kd_poli'],
'tambahan_pesan'=>$row['tambahan_pesan'],
'status'=>$row['status'],
'tanggal_booking'=>$row['tanggal_booking']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('booking_periksa => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_booking)
    {

        if($this->core->loadDisabledMenu('booking_periksa')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('booking_periksa', '*', ['no_booking' => $no_booking]);

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
          $this->core->LogQuery('booking_periksa => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_booking)
    {

        if($this->core->loadDisabledMenu('booking_periksa')['read'] == 'true') {
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
          $this->core->LogQuery('booking_periksa => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_booking' => $no_booking]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('booking_periksa', 'kd_poli', ['GROUP' => 'kd_poli']);
      $datasets = $this->core->db->select('booking_periksa', ['count' => \Medoo\Medoo::raw('COUNT(<kd_poli>)')], ['GROUP' => 'kd_poli']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('booking_periksa', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('booking_periksa', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'booking_periksa';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('booking_periksa => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/booking_periksa/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/booking_periksa/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('booking_periksa')]);
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

        $this->core->addCSS(url([ 'booking_periksa', 'css']));
        $this->core->addJS(url([ 'booking_periksa', 'javascript']), 'footer');
    }

}
