<?php
namespace Plugins\Periksa_Lab;

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
        $disabled_menu = $this->core->loadDisabledMenu('periksa_lab'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'no_rawat');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_periksa_lab= isset_or($_POST['search_field_periksa_lab']);
        $search_text_periksa_lab = isset_or($_POST['search_text_periksa_lab']);

        if ($search_text_periksa_lab != '') {
          $where[$search_field_periksa_lab.'[~]'] = $search_text_periksa_lab;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('periksa_lab', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('periksa_lab', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('periksa_lab', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'no_rawat'=>$row['no_rawat'],
'nip'=>$row['nip'],
'kd_jenis_prw'=>$row['kd_jenis_prw'],
'tgl_periksa'=>$row['tgl_periksa'],
'jam'=>$row['jam'],
'dokter_perujuk'=>$row['dokter_perujuk'],
'bagian_rs'=>$row['bagian_rs'],
'bhp'=>$row['bhp'],
'tarif_perujuk'=>$row['tarif_perujuk'],
'tarif_tindakan_dokter'=>$row['tarif_tindakan_dokter'],
'tarif_tindakan_petugas'=>$row['tarif_tindakan_petugas'],
'kso'=>$row['kso'],
'menejemen'=>$row['menejemen'],
'biaya'=>$row['biaya'],
'kd_dokter'=>$row['kd_dokter'],
'status'=>$row['status'],
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
          $this->core->LogQuery('periksa_lab => postData');
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

            if($this->core->loadDisabledMenu('periksa_lab')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_rawat = $_POST['no_rawat'];
$nip = $_POST['nip'];
$kd_jenis_prw = $_POST['kd_jenis_prw'];
$tgl_periksa = $_POST['tgl_periksa'];
$jam = $_POST['jam'];
$dokter_perujuk = $_POST['dokter_perujuk'];
$bagian_rs = $_POST['bagian_rs'];
$bhp = $_POST['bhp'];
$tarif_perujuk = $_POST['tarif_perujuk'];
$tarif_tindakan_dokter = $_POST['tarif_tindakan_dokter'];
$tarif_tindakan_petugas = $_POST['tarif_tindakan_petugas'];
$kso = $_POST['kso'];
$menejemen = $_POST['menejemen'];
$biaya = $_POST['biaya'];
$kd_dokter = $_POST['kd_dokter'];
$status = $_POST['status'];
$kategori = $_POST['kategori'];

            
            $result = $this->core->db->insert('periksa_lab', [
'no_rawat'=>$no_rawat, 'nip'=>$nip, 'kd_jenis_prw'=>$kd_jenis_prw, 'tgl_periksa'=>$tgl_periksa, 'jam'=>$jam, 'dokter_perujuk'=>$dokter_perujuk, 'bagian_rs'=>$bagian_rs, 'bhp'=>$bhp, 'tarif_perujuk'=>$tarif_perujuk, 'tarif_tindakan_dokter'=>$tarif_tindakan_dokter, 'tarif_tindakan_petugas'=>$tarif_tindakan_petugas, 'kso'=>$kso, 'menejemen'=>$menejemen, 'biaya'=>$biaya, 'kd_dokter'=>$kd_dokter, 'status'=>$status, 'kategori'=>$kategori
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
              $this->core->LogQuery('periksa_lab => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('periksa_lab')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $no_rawat = $_POST['no_rawat'];
$nip = $_POST['nip'];
$kd_jenis_prw = $_POST['kd_jenis_prw'];
$tgl_periksa = $_POST['tgl_periksa'];
$jam = $_POST['jam'];
$dokter_perujuk = $_POST['dokter_perujuk'];
$bagian_rs = $_POST['bagian_rs'];
$bhp = $_POST['bhp'];
$tarif_perujuk = $_POST['tarif_perujuk'];
$tarif_tindakan_dokter = $_POST['tarif_tindakan_dokter'];
$tarif_tindakan_petugas = $_POST['tarif_tindakan_petugas'];
$kso = $_POST['kso'];
$menejemen = $_POST['menejemen'];
$biaya = $_POST['biaya'];
$kd_dokter = $_POST['kd_dokter'];
$status = $_POST['status'];
$kategori = $_POST['kategori'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('periksa_lab', [
'no_rawat'=>$no_rawat, 'nip'=>$nip, 'kd_jenis_prw'=>$kd_jenis_prw, 'tgl_periksa'=>$tgl_periksa, 'jam'=>$jam, 'dokter_perujuk'=>$dokter_perujuk, 'bagian_rs'=>$bagian_rs, 'bhp'=>$bhp, 'tarif_perujuk'=>$tarif_perujuk, 'tarif_tindakan_dokter'=>$tarif_tindakan_dokter, 'tarif_tindakan_petugas'=>$tarif_tindakan_petugas, 'kso'=>$kso, 'menejemen'=>$menejemen, 'biaya'=>$biaya, 'kd_dokter'=>$kd_dokter, 'status'=>$status, 'kategori'=>$kategori
            ], [
              'no_rawat'=>$no_rawat
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
              $this->core->LogQuery('periksa_lab => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('periksa_lab')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $no_rawat= $_POST['no_rawat'];
            $result = $this->core->db->delete('periksa_lab', [
              'AND' => [
                'no_rawat'=>$no_rawat
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
              $this->core->LogQuery('periksa_lab => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('periksa_lab')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_periksa_lab= $_POST['search_field_periksa_lab'];
            $search_text_periksa_lab = $_POST['search_text_periksa_lab'];

            if ($search_text_periksa_lab != '') {
              $where[$search_field_periksa_lab.'[~]'] = $search_text_periksa_lab;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('periksa_lab', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'no_rawat'=>$row['no_rawat'],
'nip'=>$row['nip'],
'kd_jenis_prw'=>$row['kd_jenis_prw'],
'tgl_periksa'=>$row['tgl_periksa'],
'jam'=>$row['jam'],
'dokter_perujuk'=>$row['dokter_perujuk'],
'bagian_rs'=>$row['bagian_rs'],
'bhp'=>$row['bhp'],
'tarif_perujuk'=>$row['tarif_perujuk'],
'tarif_tindakan_dokter'=>$row['tarif_tindakan_dokter'],
'tarif_tindakan_petugas'=>$row['tarif_tindakan_petugas'],
'kso'=>$row['kso'],
'menejemen'=>$row['menejemen'],
'biaya'=>$row['biaya'],
'kd_dokter'=>$row['kd_dokter'],
'status'=>$row['status'],
'kategori'=>$row['kategori']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('periksa_lab => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($no_rawat)
    {

        if($this->core->loadDisabledMenu('periksa_lab')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('periksa_lab', '*', ['no_rawat' => $no_rawat]);

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
          $this->core->LogQuery('periksa_lab => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($no_rawat)
    {

        if($this->core->loadDisabledMenu('periksa_lab')['read'] == 'true') {
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
          $this->core->LogQuery('periksa_lab => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'no_rawat' => $no_rawat]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('periksa_lab', 'nip', ['GROUP' => 'nip']);
      $datasets = $this->core->db->select('periksa_lab', ['count' => \Medoo\Medoo::raw('COUNT(<nip>)')], ['GROUP' => 'nip']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('periksa_lab', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('periksa_lab', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'periksa_lab';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('periksa_lab => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/periksa_lab/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/periksa_lab/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('periksa_lab')]);
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

        $this->core->addCSS(url([ 'periksa_lab', 'css']));
        $this->core->addJS(url([ 'periksa_lab', 'javascript']), 'footer');
    }

}
