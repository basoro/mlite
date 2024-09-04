<?php
namespace Plugins\Mlite_Antrian_Referensi;

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
        $disabled_menu = $this->core->loadDisabledMenu('mlite_antrian_referensi'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'tanggal_periksa');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_mlite_antrian_referensi= isset_or($_POST['search_field_mlite_antrian_referensi']);
        $search_text_mlite_antrian_referensi = isset_or($_POST['search_text_mlite_antrian_referensi']);

        if ($search_text_mlite_antrian_referensi != '') {
          $where[$search_field_mlite_antrian_referensi.'[~]'] = $search_text_mlite_antrian_referensi;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('mlite_antrian_referensi', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('mlite_antrian_referensi', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('mlite_antrian_referensi', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'tanggal_periksa'=>$row['tanggal_periksa'],
'no_rkm_medis'=>$row['no_rkm_medis'],
'nomor_kartu'=>$row['nomor_kartu'],
'nomor_referensi'=>$row['nomor_referensi'],
'kodebooking'=>$row['kodebooking'],
'jenis_kunjungan'=>$row['jenis_kunjungan'],
'status_kirim'=>$row['status_kirim'],
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
          $this->core->LogQuery('mlite_antrian_referensi => postData');
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

            if($this->core->loadDisabledMenu('mlite_antrian_referensi')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $tanggal_periksa = $_POST['tanggal_periksa'];
$no_rkm_medis = $_POST['no_rkm_medis'];
$nomor_kartu = $_POST['nomor_kartu'];
$nomor_referensi = $_POST['nomor_referensi'];
$kodebooking = $_POST['kodebooking'];
$jenis_kunjungan = $_POST['jenis_kunjungan'];
$status_kirim = $_POST['status_kirim'];
$keterangan = $_POST['keterangan'];

            
            $result = $this->core->db->insert('mlite_antrian_referensi', [
'tanggal_periksa'=>$tanggal_periksa, 'no_rkm_medis'=>$no_rkm_medis, 'nomor_kartu'=>$nomor_kartu, 'nomor_referensi'=>$nomor_referensi, 'kodebooking'=>$kodebooking, 'jenis_kunjungan'=>$jenis_kunjungan, 'status_kirim'=>$status_kirim, 'keterangan'=>$keterangan
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
              $this->core->LogQuery('mlite_antrian_referensi => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('mlite_antrian_referensi')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $tanggal_periksa = $_POST['tanggal_periksa'];
$no_rkm_medis = $_POST['no_rkm_medis'];
$nomor_kartu = $_POST['nomor_kartu'];
$nomor_referensi = $_POST['nomor_referensi'];
$kodebooking = $_POST['kodebooking'];
$jenis_kunjungan = $_POST['jenis_kunjungan'];
$status_kirim = $_POST['status_kirim'];
$keterangan = $_POST['keterangan'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('mlite_antrian_referensi', [
'tanggal_periksa'=>$tanggal_periksa, 'no_rkm_medis'=>$no_rkm_medis, 'nomor_kartu'=>$nomor_kartu, 'nomor_referensi'=>$nomor_referensi, 'kodebooking'=>$kodebooking, 'jenis_kunjungan'=>$jenis_kunjungan, 'status_kirim'=>$status_kirim, 'keterangan'=>$keterangan
            ], [
              'tanggal_periksa'=>$tanggal_periksa
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
              $this->core->LogQuery('mlite_antrian_referensi => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('mlite_antrian_referensi')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kodebooking= $_POST['kodebooking'];
            $result = $this->core->db->delete('mlite_antrian_referensi', [
              'AND' => [
                'kodebooking'=>$kodebooking
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
              $this->core->LogQuery('mlite_antrian_referensi => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('mlite_antrian_referensi')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_mlite_antrian_referensi= $_POST['search_field_mlite_antrian_referensi'];
            $search_text_mlite_antrian_referensi = $_POST['search_text_mlite_antrian_referensi'];

            if ($search_text_mlite_antrian_referensi != '') {
              $where[$search_field_mlite_antrian_referensi.'[~]'] = $search_text_mlite_antrian_referensi;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('mlite_antrian_referensi', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'tanggal_periksa'=>$row['tanggal_periksa'],
'no_rkm_medis'=>$row['no_rkm_medis'],
'nomor_kartu'=>$row['nomor_kartu'],
'nomor_referensi'=>$row['nomor_referensi'],
'kodebooking'=>$row['kodebooking'],
'jenis_kunjungan'=>$row['jenis_kunjungan'],
'status_kirim'=>$row['status_kirim'],
'keterangan'=>$row['keterangan']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('mlite_antrian_referensi => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($tanggal_periksa)
    {

        if($this->core->loadDisabledMenu('mlite_antrian_referensi')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('mlite_antrian_referensi', '*', ['tanggal_periksa' => $tanggal_periksa]);

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
          $this->core->LogQuery('mlite_antrian_referensi => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($tanggal_periksa)
    {

        if($this->core->loadDisabledMenu('mlite_antrian_referensi')['read'] == 'true') {
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
          $this->core->LogQuery('mlite_antrian_referensi => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'tanggal_periksa' => $tanggal_periksa]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('mlite_antrian_referensi', 'jenis_kunjungan', ['GROUP' => 'jenis_kunjungan']);
      $datasets = $this->core->db->select('mlite_antrian_referensi', ['count' => \Medoo\Medoo::raw('COUNT(<jenis_kunjungan>)')], ['GROUP' => 'jenis_kunjungan']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('mlite_antrian_referensi', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('mlite_antrian_referensi', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'mlite_antrian_referensi';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('mlite_antrian_referensi => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/mlite_antrian_referensi/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/mlite_antrian_referensi/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('mlite_antrian_referensi')]);
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

        $this->core->addCSS(url([ 'mlite_antrian_referensi', 'css']));
        $this->core->addJS(url([ 'mlite_antrian_referensi', 'javascript']), 'footer');
    }

}
