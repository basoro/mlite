<?php
namespace Plugins\Paket_Operasi;

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
        $disabled_menu = $this->core->loadDisabledMenu('paket_operasi'); 
        foreach ($disabled_menu as &$row) { 
          if ($row == "true" ) $row = "disabled"; 
        } 
        unset($row);
        return $this->draw('manage.html', ['disabled_menu' => $disabled_menu]);
    }

    public function postData()
    {
        $column_name = isset_or($_POST['column_name'], 'kode_paket');
        $column_order = isset_or($_POST['column_order'], 'asc');
        $draw = isset_or($_POST['draw'], '0');
        $row1 = isset_or($_POST['start'], '0');
        $rowperpage = isset_or($_POST['length'], '10'); // Rows display per page
        $columnIndex = isset_or($_POST['order'][0]['column']); // Column index
        $columnName = isset_or($_POST['columns'][$columnIndex]['data'], $column_name); // Column name
        $columnSortOrder = isset_or($_POST['order'][0]['dir'], $column_order); // asc or desc
        $searchValue = isset_or($_POST['search']['value']); // Search value

        ## Custom Field value
        $search_field_paket_operasi= isset_or($_POST['search_field_paket_operasi']);
        $search_text_paket_operasi = isset_or($_POST['search_text_paket_operasi']);

        if ($search_text_paket_operasi != '') {
          $where[$search_field_paket_operasi.'[~]'] = $search_text_paket_operasi;
          $where = ["AND" => $where];
        } else {
          $where = [];
        }

        ## Total number of records without filtering
        $totalRecords = $this->core->db->count('paket_operasi', '*');

        ## Total number of records with filtering
        $totalRecordwithFilter = $this->core->db->count('paket_operasi', '*', $where);

        ## Fetch records
        $where['ORDER'] = [$columnName => strtoupper($columnSortOrder)];
        $where['LIMIT'] = [$row1, $rowperpage];
        $result = $this->core->db->select('paket_operasi', '*', $where);

        $data = array();
        foreach($result as $row) {
            $data[] = array(
                'kode_paket'=>$row['kode_paket'],
'nm_perawatan'=>$row['nm_perawatan'],
'kategori'=>$row['kategori'],
'operator1'=>$row['operator1'],
'operator2'=>$row['operator2'],
'operator3'=>$row['operator3'],
'asisten_operator1'=>$row['asisten_operator1'],
'asisten_operator2'=>$row['asisten_operator2'],
'asisten_operator3'=>$row['asisten_operator3'],
'instrumen'=>$row['instrumen'],
'dokter_anak'=>$row['dokter_anak'],
'perawaat_resusitas'=>$row['perawaat_resusitas'],
'dokter_anestesi'=>$row['dokter_anestesi'],
'asisten_anestesi'=>$row['asisten_anestesi'],
'asisten_anestesi2'=>$row['asisten_anestesi2'],
'bidan'=>$row['bidan'],
'bidan2'=>$row['bidan2'],
'bidan3'=>$row['bidan3'],
'perawat_luar'=>$row['perawat_luar'],
'sewa_ok'=>$row['sewa_ok'],
'alat'=>$row['alat'],
'akomodasi'=>$row['akomodasi'],
'bagian_rs'=>$row['bagian_rs'],
'omloop'=>$row['omloop'],
'omloop2'=>$row['omloop2'],
'omloop3'=>$row['omloop3'],
'omloop4'=>$row['omloop4'],
'omloop5'=>$row['omloop5'],
'sarpras'=>$row['sarpras'],
'dokter_pjanak'=>$row['dokter_pjanak'],
'dokter_umum'=>$row['dokter_umum'],
'kd_pj'=>$row['kd_pj'],
'status'=>$row['status'],
'kelas'=>$row['kelas']

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
          $this->core->LogQuery('paket_operasi => postData');
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

            if($this->core->loadDisabledMenu('paket_operasi')['create'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kode_paket = $_POST['kode_paket'];
$nm_perawatan = $_POST['nm_perawatan'];
$kategori = $_POST['kategori'];
$operator1 = $_POST['operator1'];
$operator2 = $_POST['operator2'];
$operator3 = $_POST['operator3'];
$asisten_operator1 = $_POST['asisten_operator1'];
$asisten_operator2 = $_POST['asisten_operator2'];
$asisten_operator3 = $_POST['asisten_operator3'];
$instrumen = $_POST['instrumen'];
$dokter_anak = $_POST['dokter_anak'];
$perawaat_resusitas = $_POST['perawaat_resusitas'];
$dokter_anestesi = $_POST['dokter_anestesi'];
$asisten_anestesi = $_POST['asisten_anestesi'];
$asisten_anestesi2 = $_POST['asisten_anestesi2'];
$bidan = $_POST['bidan'];
$bidan2 = $_POST['bidan2'];
$bidan3 = $_POST['bidan3'];
$perawat_luar = $_POST['perawat_luar'];
$sewa_ok = $_POST['sewa_ok'];
$alat = $_POST['alat'];
$akomodasi = $_POST['akomodasi'];
$bagian_rs = $_POST['bagian_rs'];
$omloop = $_POST['omloop'];
$omloop2 = $_POST['omloop2'];
$omloop3 = $_POST['omloop3'];
$omloop4 = $_POST['omloop4'];
$omloop5 = $_POST['omloop5'];
$sarpras = $_POST['sarpras'];
$dokter_pjanak = $_POST['dokter_pjanak'];
$dokter_umum = $_POST['dokter_umum'];
$kd_pj = $_POST['kd_pj'];
$status = $_POST['status'];
$kelas = $_POST['kelas'];

            
            $result = $this->core->db->insert('paket_operasi', [
'kode_paket'=>$kode_paket, 'nm_perawatan'=>$nm_perawatan, 'kategori'=>$kategori, 'operator1'=>$operator1, 'operator2'=>$operator2, 'operator3'=>$operator3, 'asisten_operator1'=>$asisten_operator1, 'asisten_operator2'=>$asisten_operator2, 'asisten_operator3'=>$asisten_operator3, 'instrumen'=>$instrumen, 'dokter_anak'=>$dokter_anak, 'perawaat_resusitas'=>$perawaat_resusitas, 'dokter_anestesi'=>$dokter_anestesi, 'asisten_anestesi'=>$asisten_anestesi, 'asisten_anestesi2'=>$asisten_anestesi2, 'bidan'=>$bidan, 'bidan2'=>$bidan2, 'bidan3'=>$bidan3, 'perawat_luar'=>$perawat_luar, 'sewa_ok'=>$sewa_ok, 'alat'=>$alat, 'akomodasi'=>$akomodasi, 'bagian_rs'=>$bagian_rs, 'omloop'=>$omloop, 'omloop2'=>$omloop2, 'omloop3'=>$omloop3, 'omloop4'=>$omloop4, 'omloop5'=>$omloop5, 'sarpras'=>$sarpras, 'dokter_pjanak'=>$dokter_pjanak, 'dokter_umum'=>$dokter_umum, 'kd_pj'=>$kd_pj, 'status'=>$status, 'kelas'=>$kelas
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
              $this->core->LogQuery('paket_operasi => postAksi => add');
            }

            echo json_encode($data);    
        }
        if ($act=="edit") {

            if($this->core->loadDisabledMenu('paket_operasi')['update'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

        $kode_paket = $_POST['kode_paket'];
$nm_perawatan = $_POST['nm_perawatan'];
$kategori = $_POST['kategori'];
$operator1 = $_POST['operator1'];
$operator2 = $_POST['operator2'];
$operator3 = $_POST['operator3'];
$asisten_operator1 = $_POST['asisten_operator1'];
$asisten_operator2 = $_POST['asisten_operator2'];
$asisten_operator3 = $_POST['asisten_operator3'];
$instrumen = $_POST['instrumen'];
$dokter_anak = $_POST['dokter_anak'];
$perawaat_resusitas = $_POST['perawaat_resusitas'];
$dokter_anestesi = $_POST['dokter_anestesi'];
$asisten_anestesi = $_POST['asisten_anestesi'];
$asisten_anestesi2 = $_POST['asisten_anestesi2'];
$bidan = $_POST['bidan'];
$bidan2 = $_POST['bidan2'];
$bidan3 = $_POST['bidan3'];
$perawat_luar = $_POST['perawat_luar'];
$sewa_ok = $_POST['sewa_ok'];
$alat = $_POST['alat'];
$akomodasi = $_POST['akomodasi'];
$bagian_rs = $_POST['bagian_rs'];
$omloop = $_POST['omloop'];
$omloop2 = $_POST['omloop2'];
$omloop3 = $_POST['omloop3'];
$omloop4 = $_POST['omloop4'];
$omloop5 = $_POST['omloop5'];
$sarpras = $_POST['sarpras'];
$dokter_pjanak = $_POST['dokter_pjanak'];
$dokter_umum = $_POST['dokter_umum'];
$kd_pj = $_POST['kd_pj'];
$status = $_POST['status'];
$kelas = $_POST['kelas'];


        // BUANG FIELD PERTAMA

            $result = $this->core->db->update('paket_operasi', [
'kode_paket'=>$kode_paket, 'nm_perawatan'=>$nm_perawatan, 'kategori'=>$kategori, 'operator1'=>$operator1, 'operator2'=>$operator2, 'operator3'=>$operator3, 'asisten_operator1'=>$asisten_operator1, 'asisten_operator2'=>$asisten_operator2, 'asisten_operator3'=>$asisten_operator3, 'instrumen'=>$instrumen, 'dokter_anak'=>$dokter_anak, 'perawaat_resusitas'=>$perawaat_resusitas, 'dokter_anestesi'=>$dokter_anestesi, 'asisten_anestesi'=>$asisten_anestesi, 'asisten_anestesi2'=>$asisten_anestesi2, 'bidan'=>$bidan, 'bidan2'=>$bidan2, 'bidan3'=>$bidan3, 'perawat_luar'=>$perawat_luar, 'sewa_ok'=>$sewa_ok, 'alat'=>$alat, 'akomodasi'=>$akomodasi, 'bagian_rs'=>$bagian_rs, 'omloop'=>$omloop, 'omloop2'=>$omloop2, 'omloop3'=>$omloop3, 'omloop4'=>$omloop4, 'omloop5'=>$omloop5, 'sarpras'=>$sarpras, 'dokter_pjanak'=>$dokter_pjanak, 'dokter_umum'=>$dokter_umum, 'kd_pj'=>$kd_pj, 'status'=>$status, 'kelas'=>$kelas
            ], [
              'kode_paket'=>$kode_paket
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
              $this->core->LogQuery('paket_operasi => postAksi => edit');
            }

            echo json_encode($data);             
        }

        if ($act=="del") {

            if($this->core->loadDisabledMenu('paket_operasi')['delete'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $kode_paket= $_POST['kode_paket'];
            $result = $this->core->db->delete('paket_operasi', [
              'AND' => [
                'kode_paket'=>$kode_paket
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
              $this->core->LogQuery('paket_operasi => postAksi => del');
            }

            echo json_encode($data);                    
        }

        if ($act=="lihat") {

            if($this->core->loadDisabledMenu('paket_operasi')['read'] == 'true') {
              http_response_code(403);
              $data = array(
                'code' => '403', 
                'status' => 'error', 
                'msg' => 'Maaf, akses dibatasi!'
              );
              echo json_encode($data);    
              exit();
            }

            $search_field_paket_operasi= $_POST['search_field_paket_operasi'];
            $search_text_paket_operasi = $_POST['search_text_paket_operasi'];

            if ($search_text_paket_operasi != '') {
              $where[$search_field_paket_operasi.'[~]'] = $search_text_paket_operasi;
              $where = ["AND" => $where];
            } else {
              $where = [];
            }

            ## Fetch records
            $result = $this->core->db->select('paket_operasi', '*', $where);

            $data = array();
            foreach($result as $row) {
                $data[] = array(
                    'kode_paket'=>$row['kode_paket'],
'nm_perawatan'=>$row['nm_perawatan'],
'kategori'=>$row['kategori'],
'operator1'=>$row['operator1'],
'operator2'=>$row['operator2'],
'operator3'=>$row['operator3'],
'asisten_operator1'=>$row['asisten_operator1'],
'asisten_operator2'=>$row['asisten_operator2'],
'asisten_operator3'=>$row['asisten_operator3'],
'instrumen'=>$row['instrumen'],
'dokter_anak'=>$row['dokter_anak'],
'perawaat_resusitas'=>$row['perawaat_resusitas'],
'dokter_anestesi'=>$row['dokter_anestesi'],
'asisten_anestesi'=>$row['asisten_anestesi'],
'asisten_anestesi2'=>$row['asisten_anestesi2'],
'bidan'=>$row['bidan'],
'bidan2'=>$row['bidan2'],
'bidan3'=>$row['bidan3'],
'perawat_luar'=>$row['perawat_luar'],
'sewa_ok'=>$row['sewa_ok'],
'alat'=>$row['alat'],
'akomodasi'=>$row['akomodasi'],
'bagian_rs'=>$row['bagian_rs'],
'omloop'=>$row['omloop'],
'omloop2'=>$row['omloop2'],
'omloop3'=>$row['omloop3'],
'omloop4'=>$row['omloop4'],
'omloop5'=>$row['omloop5'],
'sarpras'=>$row['sarpras'],
'dokter_pjanak'=>$row['dokter_pjanak'],
'dokter_umum'=>$row['dokter_umum'],
'kd_pj'=>$row['kd_pj'],
'status'=>$row['status'],
'kelas'=>$row['kelas']
                );
            }

            if($this->settings('settings', 'logquery') == true) {
              $this->core->LogQuery('paket_operasi => postAksi => lihat');
            }
            
            echo json_encode($data);
        }
        exit();
    }

    public function getRead($kode_paket)
    {

        if($this->core->loadDisabledMenu('paket_operasi')['read'] == 'true') {
          http_response_code(403);
          $data = array(
            'code' => '403', 
            'status' => 'error', 
            'msg' => 'Maaf, akses dibatasi!'
          );
          echo json_encode($data);    
          exit();
        }

        $result =  $this->core->db->get('paket_operasi', '*', ['kode_paket' => $kode_paket]);

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
          $this->core->LogQuery('paket_operasi => getRead');
        }

        echo json_encode($data);        
        exit();
    }

    public function getDetail($kode_paket)
    {

        if($this->core->loadDisabledMenu('paket_operasi')['read'] == 'true') {
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
          $this->core->LogQuery('paket_operasi => getDetail');
        }

        echo $this->draw('detail.html', ['settings' => $settings, 'kode_paket' => $kode_paket]);
        exit();
    }

    public function getChart($type = '', $column = '')
    {
      if($type == ''){
        $type = 'pie';
      }

      $labels = $this->core->db->select('paket_operasi', 'kelas', ['GROUP' => 'kelas']);
      $datasets = $this->core->db->select('paket_operasi', ['count' => \Medoo\Medoo::raw('COUNT(<kelas>)')], ['GROUP' => 'kelas']);

      if(isset_or($column)) {
        $labels = $this->core->db->select('paket_operasi', ''.$column.'', ['GROUP' => ''.$column.'']);
        $datasets = $this->core->db->select('paket_operasi', ['count' => \Medoo\Medoo::raw('COUNT(<'.$column.'>)')], ['GROUP' => ''.$column.'']);          
      }

      $database = DBNAME;
      $nama_table = 'paket_operasi';

      $get_table = $this->core->db->pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$nama_table'");
	    $get_table->execute();
	    $result = $get_table->fetchAll();

      if($this->settings('settings', 'logquery') == true) {
        $this->core->LogQuery('paket_operasi => getChart');
      }

      echo $this->draw('chart.html', ['type' => $type, 'column' => $result, 'labels' => json_encode($labels), 'datasets' => json_encode(array_column($datasets, 'count'))]);
      exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/paket_operasi/css/styles.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/paket_operasi/js/scripts.js', ['settings' => $settings, 'disabled_menu' => $this->core->loadDisabledMenu('paket_operasi')]);
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

        $this->core->addCSS(url([ 'paket_operasi', 'css']));
        $this->core->addJS(url([ 'paket_operasi', 'javascript']), 'footer');
    }

}
