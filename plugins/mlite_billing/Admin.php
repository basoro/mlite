<?php
namespace Plugins\Mlite_Billing;

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
        $disabled_menu = $this->core->loadDisabledMenu('mlite_billing'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'id_billing');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_mlite_billing= isset_or($_POST['search_field_mlite_billing']);
        $search_text_mlite_billing = isset_or($_POST['search_text_mlite_billing']);

        if ($search_text_mlite_billing != '') {
          $where[$search_field_mlite_billing.'[~]'] = $search_text_mlite_billing;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('mlite_billing', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('mlite_billing', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('mlite_billing', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'id_billing'=>$row['id_billing'],
'kd_billing'=>$row['kd_billing'],
'no_rawat'=>$row['no_rawat'],
'jumlah_total'=>$row['jumlah_total'],
'potongan'=>$row['potongan'],
'jumlah_harus_bayar'=>$row['jumlah_harus_bayar'],
'jumlah_bayar'=>$row['jumlah_bayar'],
'tgl_billing'=>$row['tgl_billing'],
'jam_billing'=>$row['jam_billing'],
'id_user'=>$row['id_user'],
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
          $this->core->LogQuery('mlite_billing => postData');
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

            if($this->core->loadDisabledMenu('mlite_billing')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $_POST['id_billing'] = NULL;
        $_POST['kd_billing'] = 'IRJ';
        $_POST['jumlah_total'] = '0';
        $_POST['potongan'] = '0';
        $_POST['jumlah_harus_bayar'] = '0';
        $_POST['jumlah_bayar'] = '0';
        $_POST['tgl_billing'] = date('Y-m-d');
        $_POST['jam_billing'] = date('H:i:s');
        $_POST['id_user'] = $_SESSION['mlite_user'];
        $_POST['keterangan'] = 'keterangan';

        

        $id_billing = $_POST['id_billing'];
        $kd_billing = $_POST['kd_billing'];
        $no_rawat = $_POST['no_rawat'];
        $jumlah_total = $_POST['jumlah_total'];
        $potongan = $_POST['potongan'];
        $jumlah_harus_bayar = $_POST['jumlah_harus_bayar'];
        $jumlah_bayar = $_POST['jumlah_bayar'];
        $tgl_billing = $_POST['tgl_billing'];
        $jam_billing = $_POST['jam_billing'];
        $id_user = $_POST['id_user'];
        $keterangan = $_POST['keterangan'];

            
            $result = $this->core->db->insert('mlite_billing', [
              'id_billing'=>$id_billing, 'kd_billing'=>$kd_billing, 'no_rawat'=>$no_rawat, 'jumlah_total'=>$jumlah_total, 'potongan'=>$potongan, 'jumlah_harus_bayar'=>$jumlah_harus_bayar, 'jumlah_bayar'=>$jumlah_bayar, 'tgl_billing'=>$tgl_billing, 'jam_billing'=>$jam_billing, 'id_user'=>$id_user, 'keterangan'=>$keterangan
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
              $this->core->LogQuery('mlite_billing => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('mlite_billing')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $id_billing = $_POST['id_billing'];
$kd_billing = $_POST['kd_billing'];
$no_rawat = $_POST['no_rawat'];
$jumlah_total = $_POST['jumlah_total'];
$potongan = $_POST['potongan'];
$jumlah_harus_bayar = $_POST['jumlah_harus_bayar'];
$jumlah_bayar = $_POST['jumlah_bayar'];
$tgl_billing = $_POST['tgl_billing'];
$jam_billing = $_POST['jam_billing'];
$id_user = $_POST['id_user'];
$keterangan = $_POST['keterangan'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('mlite_billing', [
'id_billing'=>$id_billing, 'kd_billing'=>$kd_billing, 'no_rawat'=>$no_rawat, 'jumlah_total'=>$jumlah_total, 'potongan'=>$potongan, 'jumlah_harus_bayar'=>$jumlah_harus_bayar, 'jumlah_bayar'=>$jumlah_bayar, 'tgl_billing'=>$tgl_billing, 'jam_billing'=>$jam_billing, 'id_user'=>$id_user, 'keterangan'=>$keterangan
            ], [
              'id_billing'=>$id_billing
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
              $this->core->LogQuery('mlite_billing => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('mlite_billing')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $id_billing= $_POST['id_billing'];
            $result = $this->core->db->delete('mlite_billing', [
              'AND' => [
                'id_billing'=>$id_billing
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
              $this->core->LogQuery('mlite_billing => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('mlite_billing')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_mlite_billing= $_POST['search_field_mlite_billing'];
            $search_text_mlite_billing = $_POST['search_text_mlite_billing'];

            if ($search_text_mlite_billing != '') {
              $where[$search_field_mlite_billing.'[~]'] = $search_text_mlite_billing;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('mlite_billing', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'id_billing'=>$row['id_billing'],
'kd_billing'=>$row['kd_billing'],
'no_rawat'=>$row['no_rawat'],
'jumlah_total'=>$row['jumlah_total'],
'potongan'=>$row['potongan'],
'jumlah_harus_bayar'=>$row['jumlah_harus_bayar'],
'jumlah_bayar'=>$row['jumlah_bayar'],
'tgl_billing'=>$row['tgl_billing'],
'jam_billing'=>$row['jam_billing'],
'id_user'=>$row['id_user'],
'keterangan'=>$row['keterangan']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('mlite_billing => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($id_billing)
    {

        if($this->core->loadDisabledMenu('mlite_billing')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('mlite_billing', '*', ['id_billing' => $id_billing]);

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
          $this->core->LogQuery('mlite_billing => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($id_billing)
    {

        if($this->core->loadDisabledMenu('mlite_billing')['read'] == 'true') {
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
          $this->core->LogQuery('mlite_billing => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'id_billing' => $id_billing]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('mlite_billing', 'id_user', ['GROUP' => 'id_user']);
      $datasets = $this->core->db->select('mlite_billing', ['count' => \Medoo\Medoo::raw('COUNT(<id_user>)')], ['GROUP' => 'id_user']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('mlite_billing', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('mlite_billing', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'mlite_billing';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('mlite_billing => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/mlite_billing/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/mlite_billing/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('mlite_billing')]);
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

        $this->core->addCSS(url([ 'mlite_billing', 'css']));
        $this->core->addJS(url([ 'mlite_billing', 'javascript']), 'footer');
    }

}
