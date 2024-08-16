<?php
namespace Plugins\Booking_Registrasi;

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
        $disabled_menu = $this->core->loadDisabledMenu('booking_registrasi'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'tanggal_booking');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_booking_registrasi= isset_or($_POST['search_field_booking_registrasi']);
        $search_text_booking_registrasi = isset_or($_POST['search_text_booking_registrasi']);

        if ($search_text_booking_registrasi != '') {
          $where[$search_field_booking_registrasi.'[~]'] = $search_text_booking_registrasi;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('booking_registrasi', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('booking_registrasi', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('booking_registrasi', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'tanggal_booking'=>$row['tanggal_booking'],
'jam_booking'=>$row['jam_booking'],
'no_rkm_medis'=>$row['no_rkm_medis'],
'tanggal_periksa'=>$row['tanggal_periksa'],
'kd_dokter'=>$row['kd_dokter'],
'kd_poli'=>$row['kd_poli'],
'no_reg'=>$row['no_reg'],
'kd_pj'=>$row['kd_pj'],
'limit_reg'=>$row['limit_reg'],
'waktu_kunjungan'=>$row['waktu_kunjungan'],
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
          $this->core->LogQuery('booking_registrasi => postData');
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

            if($this->core->loadDisabledMenu('booking_registrasi')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $tanggal_booking = $_POST['tanggal_booking'];
$jam_booking = $_POST['jam_booking'];
$no_rkm_medis = $_POST['no_rkm_medis'];
$tanggal_periksa = $_POST['tanggal_periksa'];
$kd_dokter = $_POST['kd_dokter'];
$kd_poli = $_POST['kd_poli'];
$no_reg = $_POST['no_reg'];
$kd_pj = $_POST['kd_pj'];
$limit_reg = $_POST['limit_reg'];
$waktu_kunjungan = $_POST['waktu_kunjungan'];
$status = $_POST['status'];

            
            $result = $this->core->db->insert('booking_registrasi', [
'tanggal_booking'=>$tanggal_booking, 'jam_booking'=>$jam_booking, 'no_rkm_medis'=>$no_rkm_medis, 'tanggal_periksa'=>$tanggal_periksa, 'kd_dokter'=>$kd_dokter, 'kd_poli'=>$kd_poli, 'no_reg'=>$no_reg, 'kd_pj'=>$kd_pj, 'limit_reg'=>$limit_reg, 'waktu_kunjungan'=>$waktu_kunjungan, 'status'=>$status
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
              $this->core->LogQuery('booking_registrasi => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('booking_registrasi')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $tanggal_booking = $_POST['tanggal_booking'];
$jam_booking = $_POST['jam_booking'];
$no_rkm_medis = $_POST['no_rkm_medis'];
$tanggal_periksa = $_POST['tanggal_periksa'];
$kd_dokter = $_POST['kd_dokter'];
$kd_poli = $_POST['kd_poli'];
$no_reg = $_POST['no_reg'];
$kd_pj = $_POST['kd_pj'];
$limit_reg = $_POST['limit_reg'];
$waktu_kunjungan = $_POST['waktu_kunjungan'];
$status = $_POST['status'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('booking_registrasi', [
'tanggal_booking'=>$tanggal_booking, 'jam_booking'=>$jam_booking, 'no_rkm_medis'=>$no_rkm_medis, 'tanggal_periksa'=>$tanggal_periksa, 'kd_dokter'=>$kd_dokter, 'kd_poli'=>$kd_poli, 'no_reg'=>$no_reg, 'kd_pj'=>$kd_pj, 'limit_reg'=>$limit_reg, 'waktu_kunjungan'=>$waktu_kunjungan, 'status'=>$status
            ], [
              'tanggal_booking'=>$tanggal_booking
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
              $this->core->LogQuery('booking_registrasi => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('booking_registrasi')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $tanggal_booking= $_POST['tanggal_booking'];
            $result = $this->core->db->delete('booking_registrasi', [
              'AND' => [
                'tanggal_booking'=>$tanggal_booking
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
              $this->core->LogQuery('booking_registrasi => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('booking_registrasi')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_booking_registrasi= $_POST['search_field_booking_registrasi'];
            $search_text_booking_registrasi = $_POST['search_text_booking_registrasi'];

            if ($search_text_booking_registrasi != '') {
              $where[$search_field_booking_registrasi.'[~]'] = $search_text_booking_registrasi;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('booking_registrasi', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'tanggal_booking'=>$row['tanggal_booking'],
'jam_booking'=>$row['jam_booking'],
'no_rkm_medis'=>$row['no_rkm_medis'],
'tanggal_periksa'=>$row['tanggal_periksa'],
'kd_dokter'=>$row['kd_dokter'],
'kd_poli'=>$row['kd_poli'],
'no_reg'=>$row['no_reg'],
'kd_pj'=>$row['kd_pj'],
'limit_reg'=>$row['limit_reg'],
'waktu_kunjungan'=>$row['waktu_kunjungan'],
'status'=>$row['status']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('booking_registrasi => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($tanggal_booking)
    {

        if($this->core->loadDisabledMenu('booking_registrasi')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('booking_registrasi', '*', ['tanggal_booking' => $tanggal_booking]);

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
          $this->core->LogQuery('booking_registrasi => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($tanggal_booking)
    {

        if($this->core->loadDisabledMenu('booking_registrasi')['read'] == 'true') {
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
          $this->core->LogQuery('booking_registrasi => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'tanggal_booking' => $tanggal_booking]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('booking_registrasi', 'kd_dokter', ['GROUP' => 'kd_dokter']);
      $datasets = $this->core->db->select('booking_registrasi', ['count' => \Medoo\Medoo::raw('COUNT(<kd_dokter>)')], ['GROUP' => 'kd_dokter']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('booking_registrasi', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('booking_registrasi', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'booking_registrasi';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('booking_registrasi => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/booking_registrasi/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/booking_registrasi/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('booking_registrasi')]);
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

        $this->core->addCSS(url([ 'booking_registrasi', 'css']));
        $this->core->addJS(url([ 'booking_registrasi', 'javascript']), 'footer');
    }

}
