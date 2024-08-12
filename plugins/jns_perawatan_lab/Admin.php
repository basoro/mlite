<?php
namespace Plugins\Jns_Perawatan_Lab;

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
        $this->assign['penjab'] = $this->core->db->select('penjab', '*', ['status' => '1']);
        $this->assign['kelas'] = $this->core->getEnum('jns_perawatan_lab', 'kelas');
        $this->assign['kategori'] = $this->core->getEnum('jns_perawatan_lab', 'kategori');
        $disabled_menu = $this->core->loadDisabledMenu('jns_perawatan_lab'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['jns_perawatan_lab' => $this->assign, 'disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kd_jenis_prw');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_jns_perawatan_lab= isset_or($_POST['search_field_jns_perawatan_lab']);
        $search_text_jns_perawatan_lab = isset_or($_POST['search_text_jns_perawatan_lab']);

        if ($search_text_jns_perawatan_lab != '') {
          $where[$search_field_jns_perawatan_lab.'[~]'] = $search_text_jns_perawatan_lab;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('jns_perawatan_lab', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('jns_perawatan_lab', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('jns_perawatan_lab', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kd_jenis_prw'=>$row['kd_jenis_prw'],
'nm_perawatan'=>$row['nm_perawatan'],
'bagian_rs'=>$row['bagian_rs'],
'bhp'=>$row['bhp'],
'tarif_perujuk'=>$row['tarif_perujuk'],
'tarif_tindakan_dokter'=>$row['tarif_tindakan_dokter'],
'tarif_tindakan_petugas'=>$row['tarif_tindakan_petugas'],
'kso'=>$row['kso'],
'menejemen'=>$row['menejemen'],
'total_byr'=>$row['total_byr'],
'kd_pj'=>$row['kd_pj'],
'status'=>$row['status'],
'kelas'=>$row['kelas'],
'kategori'=>$row['kategori']

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
          $this->core->LogQuery('jns_perawatan_lab => postData');
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

            if($this->core->loadDisabledMenu('jns_perawatan_lab')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kd_jenis_prw = $_POST['kd_jenis_prw'];
$nm_perawatan = $_POST['nm_perawatan'];
$bagian_rs = $_POST['bagian_rs'];
$bhp = $_POST['bhp'];
$tarif_perujuk = $_POST['tarif_perujuk'];
$tarif_tindakan_dokter = $_POST['tarif_tindakan_dokter'];
$tarif_tindakan_petugas = $_POST['tarif_tindakan_petugas'];
$kso = $_POST['kso'];
$menejemen = $_POST['menejemen'];
$total_byr = $_POST['total_byr'];
$kd_pj = $_POST['kd_pj'];
$status = $_POST['status'];
$kelas = $_POST['kelas'];
$kategori = $_POST['kategori'];

            
            $result = $this->core->db->insert('jns_perawatan_lab', [
'kd_jenis_prw'=>$kd_jenis_prw, 'nm_perawatan'=>$nm_perawatan, 'bagian_rs'=>$bagian_rs, 'bhp'=>$bhp, 'tarif_perujuk'=>$tarif_perujuk, 'tarif_tindakan_dokter'=>$tarif_tindakan_dokter, 'tarif_tindakan_petugas'=>$tarif_tindakan_petugas, 'kso'=>$kso, 'menejemen'=>$menejemen, 'total_byr'=>$total_byr, 'kd_pj'=>$kd_pj, 'status'=>$status, 'kelas'=>$kelas, 'kategori'=>$kategori
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
              $this->core->LogQuery('jns_perawatan_lab => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('jns_perawatan_lab')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kd_jenis_prw = $_POST['kd_jenis_prw'];
$nm_perawatan = $_POST['nm_perawatan'];
$bagian_rs = $_POST['bagian_rs'];
$bhp = $_POST['bhp'];
$tarif_perujuk = $_POST['tarif_perujuk'];
$tarif_tindakan_dokter = $_POST['tarif_tindakan_dokter'];
$tarif_tindakan_petugas = $_POST['tarif_tindakan_petugas'];
$kso = $_POST['kso'];
$menejemen = $_POST['menejemen'];
$total_byr = $_POST['total_byr'];
$kd_pj = $_POST['kd_pj'];
$status = $_POST['status'];
$kelas = $_POST['kelas'];
$kategori = $_POST['kategori'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('jns_perawatan_lab', [
'kd_jenis_prw'=>$kd_jenis_prw, 'nm_perawatan'=>$nm_perawatan, 'bagian_rs'=>$bagian_rs, 'bhp'=>$bhp, 'tarif_perujuk'=>$tarif_perujuk, 'tarif_tindakan_dokter'=>$tarif_tindakan_dokter, 'tarif_tindakan_petugas'=>$tarif_tindakan_petugas, 'kso'=>$kso, 'menejemen'=>$menejemen, 'total_byr'=>$total_byr, 'kd_pj'=>$kd_pj, 'status'=>$status, 'kelas'=>$kelas, 'kategori'=>$kategori
            ], [
              'kd_jenis_prw'=>$kd_jenis_prw
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
              $this->core->LogQuery('jns_perawatan_lab => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('jns_perawatan_lab')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kd_jenis_prw= $_POST['kd_jenis_prw'];
            $result = $this->core->db->delete('jns_perawatan_lab', [
              'AND' => [
                'kd_jenis_prw'=>$kd_jenis_prw
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
              $this->core->LogQuery('jns_perawatan_lab => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('jns_perawatan_lab')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_jns_perawatan_lab= $_POST['search_field_jns_perawatan_lab'];
            $search_text_jns_perawatan_lab = $_POST['search_text_jns_perawatan_lab'];

            if ($search_text_jns_perawatan_lab != '') {
              $where[$search_field_jns_perawatan_lab.'[~]'] = $search_text_jns_perawatan_lab;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('jns_perawatan_lab', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kd_jenis_prw'=>$row['kd_jenis_prw'],
'nm_perawatan'=>$row['nm_perawatan'],
'bagian_rs'=>$row['bagian_rs'],
'bhp'=>$row['bhp'],
'tarif_perujuk'=>$row['tarif_perujuk'],
'tarif_tindakan_dokter'=>$row['tarif_tindakan_dokter'],
'tarif_tindakan_petugas'=>$row['tarif_tindakan_petugas'],
'kso'=>$row['kso'],
'menejemen'=>$row['menejemen'],
'total_byr'=>$row['total_byr'],
'kd_pj'=>$row['kd_pj'],
'status'=>$row['status'],
'kelas'=>$row['kelas'],
'kategori'=>$row['kategori']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('jns_perawatan_lab => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kd_jenis_prw)
    {

        if($this->core->loadDisabledMenu('jns_perawatan_lab')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('jns_perawatan_lab', '*', ['kd_jenis_prw' => $kd_jenis_prw]);

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
          $this->core->LogQuery('jns_perawatan_lab => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kd_jenis_prw)
    {

        if($this->core->loadDisabledMenu('jns_perawatan_lab')['read'] == 'true') {
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
          $this->core->LogQuery('jns_perawatan_lab => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kd_jenis_prw' => $kd_jenis_prw]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('jns_perawatan_lab', 'kelas', ['GROUP' => 'kelas']);
      $datasets = $this->core->db->select('jns_perawatan_lab', ['count' => \Medoo\Medoo::raw('COUNT(<kelas>)')], ['GROUP' => 'kelas']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('jns_perawatan_lab', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('jns_perawatan_lab', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'jns_perawatan_lab';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('jns_perawatan_lab => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/jns_perawatan_lab/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/jns_perawatan_lab/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('jns_perawatan_lab')]);
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

        $this->core->addCSS(url([ 'jns_perawatan_lab', 'css']));
        $this->core->addJS(url([ 'jns_perawatan_lab', 'javascript']), 'footer');
    }

}
