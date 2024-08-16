<?php
namespace Plugins\Inventaris_Peminjaman;

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
        $disabled_menu = $this->core->loadDisabledMenu('inventaris_peminjaman'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'peminjam');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_inventaris_peminjaman= isset_or($_POST['search_field_inventaris_peminjaman']);
        $search_text_inventaris_peminjaman = isset_or($_POST['search_text_inventaris_peminjaman']);

        if ($search_text_inventaris_peminjaman != '') {
          $where[$search_field_inventaris_peminjaman.'[~]'] = $search_text_inventaris_peminjaman;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('inventaris_peminjaman', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('inventaris_peminjaman', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('inventaris_peminjaman', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'peminjam'=>$row['peminjam'],
'tlp'=>$row['tlp'],
'no_inventaris'=>$row['no_inventaris'],
'tgl_pinjam'=>$row['tgl_pinjam'],
'tgl_kembali'=>$row['tgl_kembali'],
'nip'=>$row['nip'],
'status_pinjam'=>$row['status_pinjam']

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
          $this->core->LogQuery('inventaris_peminjaman => postData');
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

            if($this->core->loadDisabledMenu('inventaris_peminjaman')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $peminjam = $_POST['peminjam'];
$tlp = $_POST['tlp'];
$no_inventaris = $_POST['no_inventaris'];
$tgl_pinjam = $_POST['tgl_pinjam'];
$tgl_kembali = $_POST['tgl_kembali'];
$nip = $_POST['nip'];
$status_pinjam = $_POST['status_pinjam'];

            
            $result = $this->core->db->insert('inventaris_peminjaman', [
'peminjam'=>$peminjam, 'tlp'=>$tlp, 'no_inventaris'=>$no_inventaris, 'tgl_pinjam'=>$tgl_pinjam, 'tgl_kembali'=>$tgl_kembali, 'nip'=>$nip, 'status_pinjam'=>$status_pinjam
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
              $this->core->LogQuery('inventaris_peminjaman => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('inventaris_peminjaman')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $peminjam = $_POST['peminjam'];
$tlp = $_POST['tlp'];
$no_inventaris = $_POST['no_inventaris'];
$tgl_pinjam = $_POST['tgl_pinjam'];
$tgl_kembali = $_POST['tgl_kembali'];
$nip = $_POST['nip'];
$status_pinjam = $_POST['status_pinjam'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('inventaris_peminjaman', [
'peminjam'=>$peminjam, 'tlp'=>$tlp, 'no_inventaris'=>$no_inventaris, 'tgl_pinjam'=>$tgl_pinjam, 'tgl_kembali'=>$tgl_kembali, 'nip'=>$nip, 'status_pinjam'=>$status_pinjam
            ], [
              'peminjam'=>$peminjam
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
              $this->core->LogQuery('inventaris_peminjaman => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('inventaris_peminjaman')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $peminjam= $_POST['peminjam'];
            $result = $this->core->db->delete('inventaris_peminjaman', [
              'AND' => [
                'peminjam'=>$peminjam
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
              $this->core->LogQuery('inventaris_peminjaman => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('inventaris_peminjaman')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_inventaris_peminjaman= $_POST['search_field_inventaris_peminjaman'];
            $search_text_inventaris_peminjaman = $_POST['search_text_inventaris_peminjaman'];

            if ($search_text_inventaris_peminjaman != '') {
              $where[$search_field_inventaris_peminjaman.'[~]'] = $search_text_inventaris_peminjaman;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('inventaris_peminjaman', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'peminjam'=>$row['peminjam'],
'tlp'=>$row['tlp'],
'no_inventaris'=>$row['no_inventaris'],
'tgl_pinjam'=>$row['tgl_pinjam'],
'tgl_kembali'=>$row['tgl_kembali'],
'nip'=>$row['nip'],
'status_pinjam'=>$row['status_pinjam']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('inventaris_peminjaman => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($peminjam)
    {

        if($this->core->loadDisabledMenu('inventaris_peminjaman')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('inventaris_peminjaman', '*', ['peminjam' => $peminjam]);

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
          $this->core->LogQuery('inventaris_peminjaman => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($peminjam)
    {

        if($this->core->loadDisabledMenu('inventaris_peminjaman')['read'] == 'true') {
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
          $this->core->LogQuery('inventaris_peminjaman => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'peminjam' => $peminjam]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('inventaris_peminjaman', 'status_pinjam', ['GROUP' => 'status_pinjam']);
      $datasets = $this->core->db->select('inventaris_peminjaman', ['count' => \Medoo\Medoo::raw('COUNT(<status_pinjam>)')], ['GROUP' => 'status_pinjam']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('inventaris_peminjaman', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('inventaris_peminjaman', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'inventaris_peminjaman';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('inventaris_peminjaman => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/inventaris_peminjaman/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/inventaris_peminjaman/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('inventaris_peminjaman')]);
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

        $this->core->addCSS(url([ 'inventaris_peminjaman', 'css']));
        $this->core->addJS(url([ 'inventaris_peminjaman', 'javascript']), 'footer');
    }

}
